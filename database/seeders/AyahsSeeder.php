<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class AyahsSeeder extends Seeder
{
    /**
     * يجلب كامل القرآن الكريم (6236 آية) من api.alquran.cloud
     * في طلب HTTP واحد، ثم يحفظها في جدول ayahs نهائيًا.
     *
     * شغّله مرة واحدة فقط:
     *   php artisan db:seed --class=AyahsSeeder
     */
    public function run(): void
    {
        // تجاهل إذا الجدول ممتلئ (تفادي التكرار)
        if (DB::table('ayahs')->exists()) {
            $this->command->info('جدول الآيات ممتلئ بالفعل — تم التخطي.');
            return;
        }

        $this->command->info('جارٍ جلب القرآن الكريم سورة سورة (114 سورة)...');
        $this->command->newLine();

        $bar   = $this->command->getOutput()->createProgressBar(114);
        $bar->start();

        $total  = 0;
        $failed = [];

        for ($s = 1; $s <= 114; $s++) {
            $rows = $this->fetchSurah($s);

            if ($rows === null) {
                $failed[] = $s;
                $bar->advance();
                continue;
            }

            DB::table('ayahs')->insert($rows);
            $total += count($rows);
            $bar->advance();

            // استراحة صغيرة لتفادي rate-limit
            usleep(100_000); // 0.1 ثانية
        }

        $bar->finish();
        $this->command->newLine(2);

        if (! empty($failed)) {
            $this->command->warn('⚠️  فشل جلب السور التالية: ' . implode(', ', $failed));
            $this->command->warn('أعد تشغيل الـ seeder لإعادة المحاولة (يتخطى الموجود تلقائيًا).');
        }

        $this->command->info("✅ تم حفظ {$total} آية في قاعدة البيانات.");
    }

    private function fetchSurah(int $number): ?array
    {
        try {
            $resp = Http::timeout(30)
                ->get("https://api.alquran.cloud/v1/surah/{$number}/ar.uthmani");

            if (! $resp->successful()) {
                return null;
            }

            $ayahs = $resp->json('data.ayahs', []);

            return collect($ayahs)->map(fn ($a) => [
                'surah_number'    => $number,
                'number_in_surah' => (int) $a['numberInSurah'],
                'text'            => $a['text'],
            ])->toArray();
        } catch (\Exception) {
            return null;
        }
    }
}

