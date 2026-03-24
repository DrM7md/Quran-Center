<?php

namespace App\Livewire\Parent;

use App\Models\Student;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ParentStudentDetail extends Component
{
    use WithPagination;

    public Student $student;
    public string $tab = 'memorizations';

    public function mount(Student $student): void
    {
        $isLinked = auth()->user()
            ->guardianStudents()
            ->where('students.id', $student->id)
            ->exists();

        if (! $isLinked) {
            abort(403);
        }

        $this->student = $student;
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $student = $this->student->load(['halaqa.center']);

        $memorizations = $this->student
            ->memorizations()
            ->with('surah')
            ->latest('heard_at')
            ->paginate(15);

        $absences = $this->student
            ->absences()
            ->latest('date')
            ->paginate(15);

        // ── Progress tab data ─────────────────────────────────────
        $allNew    = $this->student->memorizations()->where('type', 'new')->with('surah')->get();
        $allReview = $this->student->memorizations()->where('type', 'review')->with('surah')->get();

        // Calculate unique memorized ayahs per surah (merge overlapping ranges)
        [$totalAyahsNew, $surahsData] = $this->calcNewProgress($allNew);

        $totalQuranAyahs  = 6236;
        $progressPct      = $totalAyahsNew > 0
            ? round($totalAyahsNew / $totalQuranAyahs * 100, 1) : 0;
        $juzCompleted     = round($totalAyahsNew / ($totalQuranAyahs / 30), 1);
        $lastNewMem       = $allNew->sortByDesc('heard_at')->first();

        // Detect memorization direction from the earliest session's surah
        $firstNew  = $allNew->sortBy('heard_at')->first();
        $direction = null;
        if ($firstNew?->surah) {
            $direction = $firstNew->surah->number <= 40 ? 'top_down' : 'bottom_up';
        }

        // Review stats
        $last30RevAyahs = $allReview
            ->filter(fn($m) => $m->heard_at->gte(now()->subDays(30)))
            ->sum(fn($m) => $m->to_ayah - $m->from_ayah + 1);
        $totalRevAyahs = $allReview->sum(fn($m) => $m->to_ayah - $m->from_ayah + 1);
        $lastReview    = $allReview->sortByDesc('heard_at')->first();

        // Monthly activity chart – last 6 months
        $arabicMonths = [
            'يناير','فبراير','مارس','أبريل','مايو','يونيو',
            'يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر',
        ];
        $months = collect(range(5, 0))->map(function ($i) use ($allNew, $allReview, $arabicMonths) {
            $month = now()->subMonths($i);
            $key   = $month->format('Y-m');
            return [
                'label'    => $arabicMonths[$month->month - 1],
                'newAyahs' => $allNew
                    ->filter(fn($m) => $m->heard_at->format('Y-m') === $key)
                    ->sum(fn($m) => $m->to_ayah - $m->from_ayah + 1),
                'revAyahs' => $allReview
                    ->filter(fn($m) => $m->heard_at->format('Y-m') === $key)
                    ->sum(fn($m) => $m->to_ayah - $m->from_ayah + 1),
            ];
        });
        $maxMonthAyahs     = max($months->max('newAyahs'), $months->max('revAyahs'), 1);
        $circleCircumf     = round(2 * M_PI * 38, 2); // SVG circle r=38

        return view('livewire.parent.parent-student-detail', compact(
            'student', 'memorizations', 'absences',
            'totalAyahsNew', 'progressPct', 'juzCompleted', 'lastNewMem', 'direction', 'surahsData',
            'last30RevAyahs', 'totalRevAyahs', 'lastReview',
            'months', 'maxMonthAyahs', 'circleCircumf',
        ))->layout('components.layouts.parent', ['header' => $student->name]);
    }

    // ── Helpers ──────────────────────────────────────────────────

    /**
     * Given a collection of 'new' memorization records,
     * compute per-surah progress (merging overlapping ayah ranges)
     * and return the grand total of unique ayahs memorized.
     */
    private function calcNewProgress(Collection $newMems): array
    {
        $totalAyahs = 0;
        $surahsData = [];

        foreach ($newMems->groupBy('surah_id') as $records) {
            $surah = $records->first()->surah;
            if (! $surah) { continue; }

            // Sort ranges by start ayah then merge overlapping ones
            $ranges = $records
                ->map(fn($r) => [$r->from_ayah, $r->to_ayah])
                ->sortBy(fn($r) => $r[0])
                ->values()
                ->toArray();

            $merged = [];
            foreach ($ranges as [$from, $to]) {
                if (! empty($merged) && $from <= $merged[count($merged) - 1][1] + 1) {
                    $merged[count($merged) - 1][1] = max($merged[count($merged) - 1][1], $to);
                } else {
                    $merged[] = [$from, $to];
                }
            }

            $memorized   = (int) array_sum(array_map(fn($r) => $r[1] - $r[0] + 1, $merged));
            $totalAyahs += $memorized;

            $surahsData[] = [
                'number'    => $surah->number,
                'name'      => $surah->name,
                'memorized' => $memorized,
                'total'     => $surah->ayahs_count,
                'pct'       => min(100, (int) round($memorized / max(1, $surah->ayahs_count) * 100)),
            ];
        }

        usort($surahsData, fn($a, $b) => $a['number'] <=> $b['number']);

        return [$totalAyahs, $surahsData];
    }
}
