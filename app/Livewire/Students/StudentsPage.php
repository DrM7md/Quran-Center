<?php

namespace App\Livewire\Students;

use App\Models\Halaqa;
use App\Models\Student;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class StudentsPage extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public bool $showModal = false;
    public bool $isEdit = false;

    public ?int $editingId = null;

    // Form fields
    public string $name = '';
    public ?int $age = null;
    public ?string $phone = null;
    public ?int $halaqa_id = null;
    public bool $is_active = true;

    // Permissions UX
    public bool $halaqaLocked = false;

    // Delete confirm
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    // =========================
    // ✅ View Modal (عرض الطالب)
    // =========================
    public bool $showViewModal = false;
    public string $viewTab = 'absences'; // absences | hifdh
    public ?int $viewStudentId = null;

    public array $viewStudent = [];   // بيانات الطالب (مصفوفة)
    public array $viewAbsences = [];  // تواريخ الغياب
    public array $viewHifdh = [];     // سجلات الحفظ

    public function mount(): void
    {
        // لو كان محفظ: قفل اختيار الحلقة على حلقته
        if (!$this->isAdmin()) {
$this->halaqa_id = auth()->user()->primary_halaqa_id();


            $this->halaqaLocked = true;
        }
    }

    public function render()
    {
        $user = auth()->user();

        $students = Student::query()
            ->visibleTo($user)
            ->with('halaqa:id,name')
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        $halaqas = $this->isAdmin()
            ? Halaqa::query()->select('id','name')->orderBy('name')->get()
            : Halaqa::query()->select('id','name')->where('id', $user->primary_halaqa_id)->get();

        return view('livewire.students.students-page', [
            'students' => $students,
            'halaqas' => $halaqas,
        ])->layout('components.layouts.app', [
            'header' => 'الطلاب'
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->editingId = null;

        if (!$this->isAdmin()) {
            $this->halaqa_id = auth()->user()->halaqa_id;
            $this->halaqaLocked = true;
        }

        $this->showModal = true;
    }


    
    public function openEdit(int $id): void
    {
        $student = Student::query()->findOrFail($id);
        $this->authorizeStudent($student);

        $this->isEdit = true;
        $this->editingId = $student->id;

        $this->name = $student->name;
        $this->age = $student->age;
        $this->phone = $student->phone;
        $this->halaqa_id = $student->halaqa_id;
        $this->is_active = (bool) $student->is_active;

        // المحفظ لا يغيّر الحلقة
        $this->halaqaLocked = !$this->isAdmin();

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate($this->rules(), $this->messages());

        // المحفظ: افرض حلقته حتى لو حاول يغير من devtools
        if (!$this->isAdmin()) {
     $data['halaqa_id'] = auth()->user()->primary_halaqa_id;

        }

        if ($this->isEdit && $this->editingId) {
            $student = Student::query()->findOrFail($this->editingId);
            $this->authorizeStudent($student);
            $student->update($data);
            session()->flash('success', 'تم تعديل الطالب بنجاح ✅');
        } else {
            Student::create($data);
            session()->flash('success', 'تم إضافة الطالب بنجاح ✅');
        }

        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $student = Student::query()->findOrFail($id);
        $this->authorizeStudent($student);

        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (!$this->deleteId) return;

        $student = Student::query()->findOrFail($this->deleteId);
        $this->authorizeStudent($student);

        $student->delete();

        $this->showDeleteModal = false;
        $this->deleteId = null;

        session()->flash('success', 'تم حذف الطالب ✅');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    private function resetForm(): void
    {
        $this->reset([
            'name', 'age', 'phone', 'is_active',
            'editingId', 'isEdit',
        ]);

        // خلي halaqa_id ما تنمسح للمحفظ
        if ($this->isAdmin()) {
            $this->halaqa_id = null;
            $this->halaqaLocked = false;
        } else {
            $this->halaqa_id = auth()->user()->halaqa_id;
            $this->halaqaLocked = true;
        }

        $this->is_active = true;
    }

    // =========================
    // ✅ عرض الطالب (الغياب + الحفظ)
    // =========================
    public function openView(int $id): void
    {
        $student = Student::query()->with('halaqa:id,name')->findOrFail($id);
        $this->authorizeStudent($student);

        $this->viewStudentId = $student->id;
        $this->viewTab = 'absences';

        $this->viewStudent = [
            'id' => $student->id,
            'name' => $student->name,
            'age' => $student->age,
            'phone' => $student->phone,
            'halaqa' => $student->halaqa?->name,
            'is_active' => (bool) $student->is_active,
        ];

        // ✅ الغياب
        $this->viewAbsences = [];
        if (class_exists(\App\Models\Absence::class)) {
            $this->viewAbsences = \App\Models\Absence::query()
                ->where('student_id', $student->id)
                ->orderByDesc('date')
                ->get(['date', 'notes'])
                ->map(fn($a) => [
                    'date' => (string) $a->date,
                    'notes' => $a->notes,
                ])
                ->toArray();
        }

        // ✅ سجل الحفظ (يدعم أكثر من اسم مودل)
        $this->viewHifdh = [];
        $hifdhModel = $this->resolveHifdhModel();

        if ($hifdhModel) {
            $query = $hifdhModel::query()->where('student_id', $student->id)->orderByDesc('created_at');

            // لو فيه علاقة surah نحاول نستخدمها
            try {
                $query->with('surah:id,name,number');
            } catch (\Throwable $e) {
                // تجاهل لو العلاقة غير موجودة
            }

            $this->viewHifdh = $query
                ->limit(300)
                ->get()
                ->map(function ($r) {
                    $surahName = null;
                    $surahNo = null;

                    if (isset($r->surah)) {
                        $surahName = $r->surah->name ?? null;
                        $surahNo = $r->surah->number ?? null;
                    }

                    return [
                        'date' => method_exists($r, 'getAttribute') ? optional($r->created_at)->format('Y-m-d') : null,
                       'type'   => $this->typeLabel($r->type ?? null),
                        'surah' => $surahName ?? ($r->surah_name ?? '—'),
                        'surah_no' => $surahNo,
                        'from' => (int) ($r->from_ayah ?? $r->from ?? 0),
                        'to' => (int) ($r->to_ayah ?? $r->to ?? 0),
                        'rating' => $this->ratingLabel($r->rating ?? null),
                        'notes' => $r->notes ?? null,
                    ];
                })
                ->toArray();
        }

        $this->showViewModal = true;
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->viewTab = 'absences';
        $this->viewStudentId = null;

        $this->viewStudent = [];
        $this->viewAbsences = [];
        $this->viewHifdh = [];
    }

    private function resolveHifdhModel(): ?string
    {
        $candidates = [
            \App\Models\HifdhRecord::class,
            \App\Models\Hifdh::class,
            \App\Models\Memorization::class,
        ];

        foreach ($candidates as $cls) {
            if (class_exists($cls)) return $cls;
        }

        return null;
    }

    private function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'age' => ['nullable','integer','min:3','max:40'],
            'phone' => ['nullable','string','max:30'],
            'halaqa_id' => ['required', Rule::exists('halaqas','id')],
            'is_active' => ['boolean'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'اكتب اسم الطالب.',
            'halaqa_id.required' => 'اختر الحلقة.',
            'halaqa_id.exists' => 'الحلقة المختارة غير صحيحة.',
            'age.integer' => 'العمر يجب أن يكون رقمًا.',
        ];
    }

    private function isAdmin(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

private function authorizeStudent(Student $student): void
{
    if ($this->isAdmin()) return;

    $userHalaqaId = auth()->user()->primary_halaqa_id;

    abort_if(!$userHalaqaId, 403); // احتياط: لو ما عنده حلقة أساسية أصلاً
    abort_if($student->halaqa_id !== (int) $userHalaqaId, 403);
}

private function typeLabel(?string $v): string
{
    return match ($v) {
        'new'    => 'حفظ جديد',
        'review' => 'مراجعة',
        default  => '—',
    };
}

private function ratingLabel(?string $v): string
{
    return match ($v) {
        'excellent'  => 'ممتاز',
        'very_good'  => 'جيد جدًا',
        'good'       => 'جيد',
        'weak'       => 'ضعيف',
        'redo'       => 'يحتاج إعادة',
        default      => '—',
    };
}


}
