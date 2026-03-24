<?php

namespace App\Livewire\Absences;

use App\Models\Absence;
use App\Models\Student;
use Livewire\Component;

class AbsencesPage extends Component
{
    public string $date;
    public string $search = '';
public string $tab = 'all'; // all | absents

    /** @var array<int> */
    public array $absentIds = []; // student ids الغايبين في التاريخ المحدد

    public function mount(): void
    {
        $this->date = now()->toDateString();
        $this->loadAbsentIds();
    }

    public function updatedDate(): void
    {
            $this->tab = 'all';
        $this->loadAbsentIds();
    }

    public function updatedSearch(): void
    {
        // ما يحتاج شيء، بس خلّها موجودة لو بنطوّر لاحقًا
    }

    public function render()
    {
        $user = auth()->user();

        $students = Student::query()
            ->visibleTo($user) // ✅ الأدمن الكل، المحفظ حلقته
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->with('halaqa:id,name')
            ->orderBy('name')
            ->get(['id','name','phone','halaqa_id']);

        $total = $students->count();
        $absents = count($this->absentIds);

        return view('livewire.absences.absences-page', [
            'students' => $students,
            'total' => $total,
            'absents' => $absents,
        ])->layout('components.layouts.app', ['header' => 'الغياب']);
    }

    public function setAbsent(int $studentId, $checked): void
    {
        $checked = filter_var($checked, FILTER_VALIDATE_BOOLEAN);

        // ✅ تأكد الطالب ضمن صلاحيات المستخدم
        $allowed = Student::query()
            ->visibleTo(auth()->user())
            ->whereKey($studentId)
            ->exists();

        abort_unless($allowed, 403);

        if ($checked) {
            // ✅ نضيف الغياب (إذا ما كان موجود)
            Absence::query()->firstOrCreate([
                'student_id' => $studentId,
                'date' => $this->date,
            ]);
        } else {
            // ✅ نحذف الغياب (يعني حضر)
            Absence::query()
                ->where('student_id', $studentId)
                ->whereDate('date', $this->date)
                ->delete();
        }

        $this->loadAbsentIds();
        session()->flash('success', 'تم تحديث الغياب ✅');
    }

    private function loadAbsentIds(): void
    {
        $user = auth()->user();

        // نجيب IDs الطلاب المسموحين للمستخدم (عشان الفلترة)
        $studentIds = Student::query()
            ->visibleTo($user)
            ->pluck('id');

        $this->absentIds = Absence::query()
            ->whereDate('date', $this->date)
            ->whereIn('student_id', $studentIds)
            ->pluck('student_id')
            ->map(fn($v) => (int) $v)
            ->toArray();
    }
}
