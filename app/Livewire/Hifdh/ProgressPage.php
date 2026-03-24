<?php

namespace App\Livewire\Hifdh;

use App\Models\Halaqa;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ProgressPage extends Component
{
    public string $search       = '';
    public ?int $filterHalaqaId = null;
    public string $sortBy       = 'progress'; // progress | name | last_session | inactive

    // Student detail modal
    public ?int $detailStudentId = null;
    public bool $showDetailModal = false;
    public array $detailData     = [];

    // Monthly goal editing
    public string $goalInput = '';

    public function updatedSearch(): void       { $this->detailStudentId = null; }
    public function updatedFilterHalaqaId(): void { $this->detailStudentId = null; }

    public function openDetail(int $studentId): void
    {
        $this->detailStudentId = $studentId;
        $this->detailData      = $this->buildDetailData($studentId);
        $this->goalInput       = (string) ($this->detailData['monthlyTarget'] ?? '');
        $this->showDetailModal = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailModal = false;
        $this->detailStudentId = null;
        $this->detailData      = [];
        $this->goalInput       = '';
    }

    public function saveGoal(): void
    {
        if (! $this->detailStudentId) {
            return;
        }

        $target = $this->goalInput !== '' ? max(1, (int) $this->goalInput) : null;

        Student::where('id', $this->detailStudentId)->update(['monthly_target_ayahs' => $target]);

        // Refresh detail data
        $this->detailData = $this->buildDetailData($this->detailStudentId);
        $this->goalInput  = (string) ($target ?? '');
    }

    public function render()
    {
        $user = auth()->user();

        // ── Build base student query ──────────────────────────────
        $studentQuery = Student::query()
            ->select('id', 'name', 'halaqa_id', 'monthly_target_ayahs')
            ->with('halaqa:id,name');

        if ($user->hasRole(['admin', 'super-admin'])) {
            if ($user->isCenterAdmin() && $user->center_id) {
                $studentQuery->whereHas('halaqa', fn($q) => $q->where('center_id', $user->center_id));
            }
        } else {
            $halaqaIds = $this->accessibleHalaqaIds();
            empty($halaqaIds)
                ? $studentQuery->whereRaw('1=0')
                : $studentQuery->whereIn('halaqa_id', $halaqaIds);
        }

        if ($this->filterHalaqaId) {
            $studentQuery->where('halaqa_id', $this->filterHalaqaId);
        }

        if ($this->search !== '') {
            $studentQuery->where('name', 'like', '%' . $this->search . '%');
        }

        $allStudents = $studentQuery->orderBy('name')->get();
        $studentIds  = $allStudents->pluck('id')->toArray();

        // ── Aggregate stats via SQL (fast, one query per type) ────
        $totalQuranAyahs = 6236;
        $thirtyDaysAgo   = now()->subDays(30)->toDateString();

        $newStats = empty($studentIds) ? collect() : DB::table('memorizations')
            ->selectRaw('
                student_id,
                SUM(to_ayah - from_ayah + 1) AS total_ayahs,
                MAX(heard_at)                AS last_session,
                COUNT(*)                     AS sessions_count,
                SUM(CASE WHEN heard_at >= ? THEN (to_ayah - from_ayah + 1) ELSE 0 END) AS month_ayahs
            ', [$thirtyDaysAgo])
            ->where('type', 'new')
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $reviewStats = empty($studentIds) ? collect() : DB::table('memorizations')
            ->selectRaw('
                student_id,
                SUM(CASE WHEN heard_at >= ? THEN (to_ayah - from_ayah + 1) ELSE 0 END) AS rev30_ayahs,
                MAX(heard_at) AS last_review
            ', [$thirtyDaysAgo])
            ->where('type', 'review')
            ->whereIn('student_id', $studentIds)
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        // ── Build rows with computed fields ───────────────────────
        $rows = $allStudents->map(function ($student) use ($newStats, $reviewStats, $totalQuranAyahs, $thirtyDaysAgo) {
            $new    = $newStats->get($student->id);
            $review = $reviewStats->get($student->id);

            $totalAyahs  = (int) ($new->total_ayahs ?? 0);
            $pct         = $totalAyahs > 0 ? round($totalAyahs / $totalQuranAyahs * 100, 1) : 0;
            $juz         = round($totalAyahs / ($totalQuranAyahs / 30), 1);
            $lastSession = $new->last_session ?? null;
            $isActive    = $lastSession && $lastSession >= $thirtyDaysAgo;
            $monthAyahs  = (int) ($new->month_ayahs ?? 0);
            $rev30       = (int) ($review->rev30_ayahs ?? 0);

            return [
                'id'           => $student->id,
                'name'         => $student->name,
                'halaqa'       => $student->halaqa?->name ?? '—',
                'totalAyahs'   => $totalAyahs,
                'pct'          => $pct,
                'juz'          => $juz,
                'lastSession'  => $lastSession,
                'isActive'     => $isActive,
                'started'      => $totalAyahs > 0,
                'monthAyahs'   => $monthAyahs,
                'rev30'        => $rev30,
                'monthTarget'  => $student->monthly_target_ayahs,
            ];
        });

        // ── Sort ──────────────────────────────────────────────────
        $rows = match ($this->sortBy) {
            'name'         => $rows->sortBy('name', SORT_STRING),
            'last_session' => $rows->sortByDesc('lastSession'),
            'inactive'     => $rows->sortBy(fn($r) => $r['lastSession'] ?? '0000-00-00'),
            default        => $rows->sortByDesc('totalAyahs'),
        };

        // ── Summary stats (before pagination) ────────────────────
        $totalStudents  = $rows->count();
        $activeStudents = $rows->where('isActive', true)->count();
        $startedCount   = $rows->where('started', true)->count();
        $avgPct         = $startedCount > 0 ? round($rows->where('started', true)->avg('pct'), 1) : 0;
        $monthTotal     = $rows->sum('monthAyahs');

        $halaqas = $this->loadHalaqas();
        $isAdmin = $user->hasRole(['admin', 'super-admin']);

        return view('livewire.hifdh.progress-page', compact(
            'rows', 'totalStudents', 'activeStudents', 'avgPct', 'monthTotal',
            'halaqas', 'isAdmin',
        ))->layout('components.layouts.app', ['header' => 'تقدم الحفظ']);
    }

    // ── Detail Modal ──────────────────────────────────────────────

    private function buildDetailData(int $studentId): array
    {
        $student = Student::select('id', 'name', 'halaqa_id', 'monthly_target_ayahs')
            ->with('halaqa:id,name')
            ->find($studentId);
        if (! $student) {
            return [];
        }

        // Per-surah new memorization
        $allNew = DB::table('memorizations')
            ->join('surahs', 'memorizations.surah_id', '=', 'surahs.id')
            ->select(
                'memorizations.surah_id',
                'surahs.name as surah_name',
                'surahs.number as surah_number',
                'surahs.ayahs_count',
                'memorizations.from_ayah',
                'memorizations.to_ayah',
            )
            ->where('memorizations.student_id', $studentId)
            ->where('memorizations.type', 'new')
            ->orderBy('surahs.number')
            ->orderBy('memorizations.from_ayah')
            ->get();

        $surahsData = [];
        $totalAyahs = 0;

        foreach ($allNew->groupBy('surah_id') as $records) {
            $first  = $records->first();
            $ranges = $records->map(fn($r) => [$r->from_ayah, $r->to_ayah])
                ->sortBy(fn($r) => $r[0])->values()->toArray();

            $merged = [];
            foreach ($ranges as [$from, $to]) {
                if (! empty($merged) && $from <= $merged[count($merged) - 1][1] + 1) {
                    $merged[count($merged) - 1][1] = max($merged[count($merged) - 1][1], $to);
                } else {
                    $merged[] = [$from, $to];
                }
            }

            $memorized  = (int) array_sum(array_map(fn($r) => $r[1] - $r[0] + 1, $merged));
            $totalAyahs += $memorized;

            $surahsData[] = [
                'number'    => $first->surah_number,
                'name'      => $first->surah_name,
                'memorized' => $memorized,
                'total'     => $first->ayahs_count,
                'pct'       => min(100, (int) round($memorized / max(1, $first->ayahs_count) * 100)),
            ];
        }

        $totalQuranAyahs = 6236;
        $pct             = $totalAyahs > 0 ? round($totalAyahs / $totalQuranAyahs * 100, 1) : 0;
        $juz             = round($totalAyahs / ($totalQuranAyahs / 30), 1);
        $circleCircumf   = round(2 * M_PI * 38, 2);

        // Monthly activity (last 6 months)
        $arabicMonths = [
            'يناير','فبراير','مارس','أبريل','مايو','يونيو',
            'يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر',
        ];
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month    = now()->subMonths($i);
            $key      = $month->format('Y-m');
            $months[] = [
                'label' => $arabicMonths[$month->month - 1],
                'new'   => (int) DB::table('memorizations')
                    ->where('student_id', $studentId)->where('type', 'new')
                    ->whereRaw("DATE_FORMAT(heard_at, '%Y-%m') = ?", [$key])
                    ->sum(DB::raw('to_ayah - from_ayah + 1')),
                'rev'   => (int) DB::table('memorizations')
                    ->where('student_id', $studentId)->where('type', 'review')
                    ->whereRaw("DATE_FORMAT(heard_at, '%Y-%m') = ?", [$key])
                    ->sum(DB::raw('to_ayah - from_ayah + 1')),
            ];
        }

        $maxMonth = max(max(array_column($months, 'new')), max(array_column($months, 'rev')), 1);

        // Review totals
        $reviewTotal = (int) DB::table('memorizations')
            ->where('student_id', $studentId)->where('type', 'review')
            ->sum(DB::raw('to_ayah - from_ayah + 1'));

        $rev30 = (int) DB::table('memorizations')
            ->where('student_id', $studentId)->where('type', 'review')
            ->where('heard_at', '>=', now()->subDays(30)->toDateString())
            ->sum(DB::raw('to_ayah - from_ayah + 1'));

        // Last new session date
        $lastSession = DB::table('memorizations')
            ->where('student_id', $studentId)->where('type', 'new')
            ->max('heard_at');

        // Direction
        $firstSessionSurahNumber = DB::table('memorizations')
            ->join('surahs', 'memorizations.surah_id', '=', 'surahs.id')
            ->where('memorizations.student_id', $studentId)
            ->where('memorizations.type', 'new')
            ->orderBy('memorizations.heard_at')
            ->value('surahs.number');

        $direction = null;
        if ($firstSessionSurahNumber !== null) {
            $direction = $firstSessionSurahNumber <= 40 ? 'top_down' : 'bottom_up';
        }

        $monthlyTarget = $student->monthly_target_ayahs;

        // month ayahs (current month new memorization for this student)
        $currentMonth = now()->format('Y-m');
        $monthAyahsForStudent = (int) DB::table('memorizations')
            ->where('student_id', $studentId)
            ->where('type', 'new')
            ->whereRaw("DATE_FORMAT(heard_at, '%Y-%m') = ?", [$currentMonth])
            ->sum(DB::raw('to_ayah - from_ayah + 1'));

        return compact(
            'student', 'totalAyahs', 'pct', 'juz', 'circleCircumf',
            'surahsData', 'direction', 'lastSession',
            'months', 'maxMonth',
            'reviewTotal', 'rev30',
            'monthlyTarget', 'monthAyahsForStudent',
        );
    }

    // ── Helpers ───────────────────────────────────────────────────

    private function loadHalaqas(): \Illuminate\Support\Collection
    {
        $user = auth()->user();

        if ($user->hasRole(['admin', 'super-admin'])) {
            return Halaqa::query()
                ->select('id', 'name')
                ->when($user->isCenterAdmin() && $user->center_id,
                    fn($q) => $q->where('center_id', $user->center_id))
                ->orderBy('name')
                ->get();
        }

        $ids = $this->accessibleHalaqaIds();

        return empty($ids)
            ? collect()
            : Halaqa::whereIn('id', $ids)->orderBy('name')->get(['id', 'name']);
    }

    private function accessibleHalaqaIds(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        return method_exists($user, 'accessibleHalaqaIds')
            ? $user->accessibleHalaqaIds()
            : $user->halaqas()->pluck('halaqas.id')->map(fn($v) => (int) $v)->toArray();
    }
}
