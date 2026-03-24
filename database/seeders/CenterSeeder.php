<?php

namespace Database\Seeders;

use App\Models\Center;
use App\Models\Halaqa;
use App\Models\User;
use Illuminate\Database\Seeder;

class CenterSeeder extends Seeder
{
    public function run(): void
    {
        // مركز افتراضي يجمع كل البيانات الحالية
        $center = Center::firstOrCreate(
            ['name' => 'المركز الرئيسي'],
            ['is_active' => true]
        );

        // ربط كل الحلقات الموجودة بالمركز الافتراضي
        Halaqa::whereNull('center_id')->update(['center_id' => $center->id]);

        // ربط الأدمن الافتراضي بالمركز
        $admin = User::where('email', 'admin@quran.local')->first();
        if ($admin) {
            $admin->update(['center_id' => $center->id]);
        }

        // ربط كل المحافظين الحاليين بالمركز الافتراضي
        User::role('muhafidh')->whereNull('center_id')->each(function ($user) use ($center) {
            $user->update(['center_id' => $center->id]);
        });
    }
}
