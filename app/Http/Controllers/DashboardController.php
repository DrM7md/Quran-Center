<?php

namespace App\Http\Controllers;

use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Student;
use App\Models\User;
use App\Models\Memorization;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $today = now()->toDateString();
        [$absTodayCount, $todayAbsences] = $this->absenceStats($user, $today);

        $stats = $this->buildStats($user, $today);

        return view('livewire.dashboard.dashboard-page', array_merge($stats, [
            'absTodayCount' => $absTodayCount,
            'todayAbsences' => $todayAbsences,
        ]));
    }

    private function buildStats($user, string $today): array
    {
        if ($user->isSuperAdmin()) {
            return $this->superAdminStats($today);
        }

        if ($user->isCenterAdmin()) {
            return $this->centerAdminStats($user, $today);
        }

        return $this->muhafidStats($user, $today);
    }

    private function superAdminStats(string $today): array
    {
        return [
            'centersCount'   => Center::count(),
            'studentsCount'  => Student::count(),
            'halaqatCount'   => Halaqa::count(),
            'teachersCount'  => User::role('muhafidh')->count(),
            'memCount'       => Memorization::count(),
            'memTodayCount'  => Memorization::whereDate('heard_at', $today)->count(),
            'quranCompleted'    => $this->quranCompletedCount(),
            'completedStudents' => $this->quranCompletedStudents(),
            'centersData'       => $this->centersWithStats(),
            'topStudents'    => $this->topStudents(5),
            'monthlyTrend'   => $this->globalMonthlyTrend(),
            'monthStar'      => $this->monthStar(),
        ];
    }

    private function centersWithStats(): Collection
    {
        $hasAbsences     = Schema::hasTable('absences') && class_exists(\App\Models\Absence::class);
        $totalQuranAyahs = 6236;
        $currentMonth    = now()->format('Y-m');

        // Bulk: student_id → center_id mapping
        $studentsByCenter = DB::table('students')
            ->join('halaqas', 'students.halaqa_id', '=', 'halaqas.id')
            ->select('students.id as student_id', 'halaqas.center_id')
            ->get()
            ->groupBy('center_id')
            ->map(fn($rows) => $rows->pluck('student_id'));

        $allStudentIds = $studentsByCenter->flatten()->toArray();

        // Bulk: total new ayahs per student
        $ayahsByStudent = empty($allStudentIds) ? collect() : DB::table('memorizations')
            ->selectRaw('student_id, SUM(to_ayah - from_ayah + 1) as total_ayahs')
            ->where('type', 'new')
            ->whereIn('student_id', $allStudentIds)
            ->groupBy('student_id')
            ->get()->keyBy('student_id');

        // Bulk: total memorization records per student (all types)
        $memsCountByStudent = empty($allStudentIds) ? collect() : DB::table('memorizations')
            ->selectRaw('student_id, COUNT(*) as cnt')
            ->whereIn('student_id', $allStudentIds)
            ->groupBy('student_id')
            ->get()->keyBy('student_id');

        // Bulk: this-month new ayahs per student
        $monthAyahsByStudent = empty($allStudentIds) ? collect() : DB::table('memorizations')
            ->selectRaw('student_id, SUM(to_ayah - from_ayah + 1) as month_ayahs')
            ->where('type', 'new')
            ->whereRaw("DATE_FORMAT(heard_at, '%Y-%m') = ?", [$currentMonth])
            ->whereIn('student_id', $allStudentIds)
            ->groupBy('student_id')
            ->get()->keyBy('student_id');

        // Bulk: absences per student
        $absencesByStudent = collect();
        if ($hasAbsences) {
            $absencesByStudent = empty($allStudentIds) ? collect() : DB::table('absences')
                ->selectRaw('student_id, COUNT(*) as cnt')
                ->whereIn('student_id', $allStudentIds)
                ->groupBy('student_id')
                ->get()->keyBy('student_id');
        }

        return Center::query()
            ->withCount('halaqas')
            ->withCount(['users as muhafidhs_count' => fn($q) => $q->role('muhafidh')])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(function ($center) use (
                $studentsByCenter, $ayahsByStudent, $memsCountByStudent,
                $monthAyahsByStudent, $absencesByStudent, $totalQuranAyahs
            ) {
                $studentIds    = $studentsByCenter->get($center->id, collect())->toArray();
                $studentsCount = count($studentIds);
                $totalAyahs    = 0;
                $completers    = 0;
                $monthAyahs    = 0;
                $memsCount     = 0;
                $absCount      = 0;

                foreach ($studentIds as $sid) {
                    $st = (int) ($ayahsByStudent->get($sid)?->total_ayahs ?? 0);
                    $totalAyahs += $st;
                    if ($st >= $totalQuranAyahs) {
                        $completers++;
                    }
                    $monthAyahs += (int) ($monthAyahsByStudent->get($sid)?->month_ayahs ?? 0);
                    $memsCount  += (int) ($memsCountByStudent->get($sid)?->cnt ?? 0);
                    $absCount   += (int) ($absencesByStudent->get($sid)?->cnt ?? 0);
                }

                $center->students_count   = $studentsCount;
                $center->mems_count       = $memsCount;
                $center->absences_count   = $absCount;
                $center->avg_progress     = $studentsCount > 0
                    ? round($totalAyahs / ($studentsCount * $totalQuranAyahs) * 100, 1)
                    : 0.0;
                $center->completers_count = $completers;
                $center->month_ayahs      = $monthAyahs;

                return $center;
            });
    }

    private function quranCompletedCount(): int
    {
        return (int) DB::table('memorizations')
            ->select('student_id')
            ->where('type', 'new')
            ->groupBy('student_id')
            ->havingRaw('SUM(to_ayah - from_ayah + 1) >= ?', [6236])
            ->get()
            ->count();
    }

    private function quranCompletedStudents(): Collection
    {
        $completedIds = DB::table('memorizations')
            ->select('student_id')
            ->where('type', 'new')
            ->groupBy('student_id')
            ->havingRaw('SUM(to_ayah - from_ayah + 1) >= ?', [6236])
            ->pluck('student_id')
            ->toArray();

        if (empty($completedIds)) {
            return collect();
        }

        return Student::whereIn('id', $completedIds)
            ->select('id', 'name', 'age', 'phone', 'halaqa_id')
            ->with([
                'halaqa:id,name,center_id',
                'halaqa.center:id,name',
            ])
            ->orderBy('name')
            ->get();
    }

    private function topStudents(int $limit = 5): Collection
    {
        return DB::table('memorizations')
            ->join('students', 'memorizations.student_id', '=', 'students.id')
            ->join('halaqas', 'students.halaqa_id', '=', 'halaqas.id')
            ->join('centers', 'halaqas.center_id', '=', 'centers.id')
            ->selectRaw('
                students.id,
                students.name,
                centers.name as center_name,
                SUM(memorizations.to_ayah - memorizations.from_ayah + 1) as total_ayahs
            ')
            ->where('memorizations.type', 'new')
            ->groupBy('students.id', 'students.name', 'centers.name')
            ->orderByDesc('total_ayahs')
            ->limit($limit)
            ->get();
    }

    private function globalMonthlyTrend(): array
    {
        $arabicMonths = [
            'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
            'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر',
        ];

        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month    = now()->subMonths($i);
            $key      = $month->format('Y-m');
            $months[] = [
                'label' => $arabicMonths[$month->month - 1],
                'ayahs' => (int) DB::table('memorizations')
                    ->where('type', 'new')
                    ->whereRaw("DATE_FORMAT(heard_at, '%Y-%m') = ?", [$key])
                    ->sum(DB::raw('to_ayah - from_ayah + 1')),
            ];
        }

        return $months;
    }

    private function monthStar(): ?array
    {
        $currentMonth = now()->format('Y-m');

        $row = DB::table('memorizations')
            ->join('students', 'memorizations.student_id', '=', 'students.id')
            ->join('halaqas', 'students.halaqa_id', '=', 'halaqas.id')
            ->join('centers', 'halaqas.center_id', '=', 'centers.id')
            ->selectRaw('
                students.name,
                centers.name as center_name,
                SUM(memorizations.to_ayah - memorizations.from_ayah + 1) as month_ayahs
            ')
            ->where('memorizations.type', 'new')
            ->whereRaw("DATE_FORMAT(memorizations.heard_at, '%Y-%m') = ?", [$currentMonth])
            ->groupBy('students.id', 'students.name', 'centers.name')
            ->orderByDesc('month_ayahs')
            ->first();

        if (! $row) {
            return null;
        }

        return [
            'name'        => $row->name,
            'center_name' => $row->center_name,
            'month_ayahs' => (int) $row->month_ayahs,
        ];
    }

    private function centerAdminStats($user, string $today): array
    {
        $centerId   = $user->center_id;
        $halaqaIds  = $centerId ? Halaqa::where('center_id', $centerId)->pluck('id') : collect();
        $studentIds = $halaqaIds->isNotEmpty()
            ? Student::whereIn('halaqa_id', $halaqaIds)->pluck('id')
            : collect();

        return [
            'studentsCount'    => $studentIds->count(),
            'halaqatCount'     => $halaqaIds->count(),
            'teachersCount'    => $centerId ? User::role('muhafidh')->where('center_id', $centerId)->count() : 0,
            'memCount'         => $studentIds->isNotEmpty() ? Memorization::whereIn('student_id', $studentIds)->count() : 0,
            'memTodayCount'    => $studentIds->isNotEmpty() ? Memorization::whereIn('student_id', $studentIds)->whereDate('heard_at', $today)->count() : 0,
            'monthAyahs'       => $this->monthAyahs($studentIds->isNotEmpty() ? $studentIds->toArray() : []),
            'inactiveStudents' => $this->inactiveStudents($studentIds->isNotEmpty() ? $studentIds->toArray() : []),
            'latestMems'       => $this->latestMems($studentIds->isNotEmpty() ? $studentIds : null),
        ];
    }

    private function muhafidStats($user, string $today): array
    {
        // الحلقات المتاحة للمحفظ عبر جدول pivot (halaqa_user)
        $halaqaIds = $user->accessibleHalaqaIds();

        if (empty($halaqaIds)) {
            return [
                'studentsCount'    => 0,
                'halaqatCount'     => 0,
                'teachersCount'    => null,
                'memCount'         => 0,
                'memTodayCount'    => 0,
                'monthAyahs'       => 0,
                'inactiveStudents' => collect(),
                'latestMems'       => collect(),
            ];
        }

        $studentIds = Student::whereIn('halaqa_id', $halaqaIds)->pluck('id')->toArray();

        return [
            'studentsCount'    => count($studentIds),
            'halaqatCount'     => count($halaqaIds),
            'teachersCount'    => null,
            'memCount'         => empty($studentIds) ? 0 : Memorization::whereIn('student_id', $studentIds)->count(),
            'memTodayCount'    => empty($studentIds) ? 0 : Memorization::whereIn('student_id', $studentIds)->whereDate('heard_at', $today)->count(),
            'monthAyahs'       => $this->monthAyahs($studentIds),
            'inactiveStudents' => $this->inactiveStudents($studentIds),
            'latestMems'       => $this->latestMems(
                Student::whereIn('halaqa_id', $halaqaIds)->select('id')
            ),
        ];
    }

    private function monthAyahs(array $studentIds): int
    {
        if (empty($studentIds)) {
            return 0;
        }

        return (int) Memorization::whereIn('student_id', $studentIds)
            ->where('type', 'new')
            ->whereRaw("DATE_FORMAT(heard_at, '%Y-%m') = ?", [now()->format('Y-m')])
            ->sum(\Illuminate\Support\Facades\DB::raw('to_ayah - from_ayah + 1'));
    }

    private function inactiveStudents(array $studentIds, int $limit = 6): Collection
    {
        if (empty($studentIds)) {
            return collect();
        }

        $cutoff = now()->subDays(14)->toDateString();

        $activeIds = Memorization::whereIn('student_id', $studentIds)
            ->where('type', 'new')
            ->where('heard_at', '>=', $cutoff)
            ->pluck('student_id')
            ->unique()
            ->toArray();

        $inactiveIds = array_values(array_diff($studentIds, $activeIds));

        if (empty($inactiveIds)) {
            return collect();
        }

        return Student::whereIn('id', $inactiveIds)
            ->select('id', 'name', 'halaqa_id')
            ->with('halaqa:id,name')
            ->addSelect(\Illuminate\Support\Facades\DB::raw(
                '(SELECT MAX(heard_at) FROM memorizations WHERE student_id = students.id AND type = "new") as last_session'
            ))
            ->orderByRaw('last_session IS NULL DESC, last_session ASC')
            ->limit($limit)
            ->get();
    }

    private function latestMems($filterIds = null): Collection
    {
        $q = Memorization::query()
            ->with(['student:id,name', 'surah:id,name', 'muhafidh:id,name'])
            ->orderByDesc('heard_at')
            ->orderByDesc('id')
            ->limit(10);

        if ($filterIds !== null) {
            $q->whereIn('student_id', $filterIds);
        }

        return $q->get();
    }

    private function absenceStats($user, string $today): array
    {
        if (!Schema::hasTable('absences') || !class_exists(\App\Models\Absence::class)) {
            return [0, collect()];
        }

        $q = \App\Models\Absence::query();

        if (!$user->isAdmin()) {
            $halaqaId = (int) ($user->halaqa_id ?? 0);
            if ($halaqaId === 0) {
                return [0, collect()];
            }
            $studentIds = Student::where('halaqa_id', $halaqaId)->select('id');
            $q->whereIn('student_id', $studentIds);
        } elseif ($user->isCenterAdmin() && $user->center_id) {
            $halaqaIds  = Halaqa::where('center_id', $user->center_id)->pluck('id');
            $studentIds = Student::whereIn('halaqa_id', $halaqaIds)->pluck('id');
            $q->whereIn('student_id', $studentIds);
        }

        $count    = (clone $q)->whereDate('date', $today)->count();
        $absences = $q->with(['student:id,name'])->whereDate('date', $today)->latest('date')->limit(8)->get();

        return [$count, $absences];
    }
}
