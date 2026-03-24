<?php

namespace App\Livewire\Centers;

use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Student;
use App\Models\User;
use App\Models\Memorization;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class CenterDetailPage extends Component
{
    public Center $center;
    public string $searchStudents = '';
    public string $activeTab = 'overview';

    public function mount(Center $center): void
    {
        $this->center = $center;
    }

    public function render()
    {
        $centerId = $this->center->id;
        $today    = now()->toDateString();

        // الحلقات
        $halaqas = Halaqa::where('center_id', $centerId)
            ->withCount('students')
            ->orderBy('name')
            ->get();

        // الطلاب
        $students = Student::whereHas('halaqa', fn($q) => $q->where('center_id', $centerId))
            ->with('halaqa:id,name')
            ->when($this->searchStudents, fn($q) => $q->where('name', 'like', '%' . $this->searchStudents . '%'))
            ->orderBy('name')
            ->get();

        // المحفظون
        $muhafidhs = User::role('muhafidh')
            ->where('center_id', $centerId)
            ->withCount('halaqas')
            ->orderBy('name')
            ->get();

        // أدمن المركز
        $admin = User::role('admin')
            ->where('center_id', $centerId)
            ->select('id', 'name', 'email')
            ->first();

        // آخر سجلات التسميع
        $latestMems = Memorization::query()
            ->with(['student:id,name', 'surah:id,name', 'muhafidh:id,name'])
            ->whereHas('student.halaqa', fn($q) => $q->where('center_id', $centerId))
            ->orderByDesc('heard_at')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        // الغياب
        $absences = collect();
        $absTodayCount = 0;
        if (Schema::hasTable('absences') && class_exists(\App\Models\Absence::class)) {
            $studentIds = Student::whereHas('halaqa', fn($q) => $q->where('center_id', $centerId))->pluck('id');
            $absences = \App\Models\Absence::whereIn('student_id', $studentIds)
                ->with('student:id,name')
                ->orderByDesc('date')
                ->limit(20)
                ->get();
            $absTodayCount = $absences->where('date', $today)->count();
        }

        // إحصاءات سريعة
        $memTodayCount = Memorization::whereHas('student.halaqa', fn($q) => $q->where('center_id', $centerId))
            ->whereDate('heard_at', $today)
            ->count();

        return view('livewire.centers.center-detail-page', compact(
            'halaqas', 'students', 'muhafidhs', 'admin',
            'latestMems', 'absences', 'absTodayCount', 'memTodayCount'
        ))->layout('components.layouts.app', ['header' => $this->center->name]);
    }
}
