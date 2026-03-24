<?php

namespace App\Livewire\Halaqas;

use App\Models\Halaqa;
use App\Models\Student;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class HalaqasPage extends Component
{
    public string $search = '';

    public bool $showModal = false;
    public bool $isEdit = false;
    public ?int $editingId = null;

    // form
    public string $name = '';
    public bool $is_active = true;

    // المحفظ الأساسي (pivot)
    public ?int $muhafidh_id = null;

    // delete
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

// cover (تغطية الحلقة)
public bool $showCoverModal = false;
public ?int $coverHalaqaId = null;
public string $coverHalaqaName = '';
public array $cover_muhafidh_ids = []; // محفظين تغطية (متعددة)

// students modal
public bool $showStudentsModal = false;
public ?int $studentsHalaqaId = null;
public string $studentsHalaqaName = '';
public string $studentsSearch = '';
public array $studentsList = []; // فقط أسماء الطلاب



    public function render()
    {
        $user = auth()->user();

        $halaqas = Halaqa::query()
            ->with(['muhafidhs:id,name'])
            ->withCount('students')
            ->when($user->isCenterAdmin() && $user->center_id, fn($q) => $q->where('center_id', $user->center_id))
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('muhafidhs', fn($qq) => $qq->where('name', 'like', '%' . $this->search . '%'));
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(function ($h) {
                $today = now()->toDateString();

                // ✅ اسم المحفظ الأساسي من pivot
                $primary = $h->muhafidhs->firstWhere(fn($u) => (int) $u->pivot->is_primary === 1);
                $h->primary_muhafidh_name = $primary?->name;

// ✅ محفظين التغطية (غير الأساسي)
$covers = $h->muhafidhs->filter(fn($u) => (int) $u->pivot->is_primary !== 1);
$h->cover_count = $covers->count();
$h->cover_names = $covers->pluck('name')->values()->toArray();



                // ✅ غياب اليوم (حسب طلاب الحلقة)
                $absencesToday = 0;
                if (class_exists(\App\Models\Absence::class)) {
                    $absencesToday = \App\Models\Absence::query()
                        ->whereDate('date', $today)
                        ->whereIn('student_id', Student::where('halaqa_id', $h->id)->select('id'))
                        ->count();
                }

                $h->absences_today = $absencesToday;
                return $h;
            });

        $muhafidhs = User::role('muhafidh')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('livewire.halaqas.halaqas-page', [
            'halaqas' => $halaqas,
            'muhafidhs' => $muhafidhs,
        ])->layout('components.layouts.app', ['header' => 'الحلقات']);
    }

    public function updatedSearch(): void
    {
        // cards بدون pagination
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $h = Halaqa::with('muhafidhs')->findOrFail($id);

        $this->isEdit = true;
        $this->editingId = $h->id;

        $this->name = $h->name;
        $this->is_active = (bool) $h->is_active;

        // ✅ جلب المحفظ الأساسي من pivot
        $primaryId = $h->muhafidhs()
            ->wherePivot('is_primary', true)
            ->value('users.id');

        $this->muhafidh_id = $primaryId ? (int) $primaryId : null;

        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'muhafidh_id' => ['nullable', 'exists:users,id'],
        ], [
            'name.required' => 'اكتب اسم الحلقة.',
        ]);

        DB::beginTransaction();

        try {
            // ✅ حفظ بيانات الحلقة
            if ($this->isEdit && $this->editingId) {
                $h = Halaqa::findOrFail($this->editingId);
                $h->update([
                    'name' => $data['name'],
                    'is_active' => $data['is_active'],
                ]);
            } else {
                $h = Halaqa::create([
                    'name' => $data['name'],
                    'is_active' => $data['is_active'],
                ]);
            }

            // ✅ تحديث المحفظ الأساسي عبر pivot:
            // 1) تصفير is_primary لكل من هو مربوط بهذه الحلقة
            DB::table('halaqa_user')
                ->where('halaqa_id', $h->id)
                ->update(['is_primary' => 0]);

            // 2) لو اخترنا محفظ أساسي
            if (!empty($data['muhafidh_id'])) {
                $id = (int) $data['muhafidh_id'];

                // تأكد أنه محفظ (احتياط)
                abort_unless(User::role('muhafidh')->whereKey($id)->exists(), 422);

                // اربطه لو مو مربوط
                $h->muhafidhs()->syncWithoutDetaching([$id]);

                // خله الأساسي
                $h->muhafidhs()->updateExistingPivot($id, [
                    'is_primary' => 1,
                    'starts_at' => now(),
                    'ends_at' => null,
                ]);
            }

            DB::commit();

            session()->flash('success', $this->isEdit ? 'تم تعديل الحلقة ✅' : 'تم إضافة الحلقة ✅');
            $this->closeModal();

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }


public function openCover(int $halaqaId): void
{
    $h = Halaqa::with('muhafidhs')->findOrFail($halaqaId);

    $this->coverHalaqaId = $h->id;
    $this->coverHalaqaName = $h->name;

    // جب المحفظ الأساسي
    $primaryId = $h->muhafidhs()
        ->wherePivot('is_primary', true)
        ->value('users.id');

    // ✅ جب محفظين التغطية (استثنِ الأساسي)
    $this->cover_muhafidh_ids = $h->muhafidhs
        ->filter(fn($u) => (int) $u->pivot->is_primary !== 1)
        ->pluck('id')
        ->map(fn($v) => (int) $v)
        ->values()
        ->toArray();

    // احتياط: لا نسمح بإضافة الأساسي ضمن التغطية
    if ($primaryId) {
        $this->cover_muhafidh_ids = array_values(array_filter(
            $this->cover_muhafidh_ids,
            fn($id) => $id !== (int)$primaryId
        ));
    }

    $this->showCoverModal = true;
}

public function saveCover(): void
{
    if (!$this->coverHalaqaId) return;

    $data = $this->validate([
        'cover_muhafidh_ids' => ['array'],
        'cover_muhafidh_ids.*' => ['exists:users,id'],
    ]);

    $h = Halaqa::with('muhafidhs')->findOrFail($this->coverHalaqaId);

    // ✅ المحفظ الأساسي (لا نلمسه)
    $primaryId = $h->muhafidhs()
        ->wherePivot('is_primary', true)
        ->value('users.id');

    $selected = collect($data['cover_muhafidh_ids'] ?? [])
        ->map(fn($v) => (int)$v)
        ->filter()
        ->unique()
        ->values()
        ->toArray();

    // لا نسمح للأٍاسي يدخل ضمن التغطية
    if ($primaryId) {
        $selected = array_values(array_filter($selected, fn($id) => $id !== (int)$primaryId));
    }

    // تأكد أنهم فعلاً محفظين
    $countMuhafidhs = User::role('muhafidh')->whereIn('id', $selected)->count();
    abort_unless($countMuhafidhs === count($selected), 422);

    DB::beginTransaction();
    try {
        $existing = DB::table('halaqa_user')
            ->where('halaqa_id', $h->id)
            ->pluck('user_id')
            ->map(fn($v) => (int)$v)
            ->toArray();

        $keep = [];
        if ($primaryId) $keep[] = (int)$primaryId;

        $desired = array_values(array_unique(array_merge($keep, $selected)));

        // ✅ نحذف التغطيات اللي انشالت (لكن ما نحذف الأساسي)
        $toDetach = array_values(array_diff($existing, $desired));
        if (!empty($toDetach)) {
            $h->muhafidhs()->detach($toDetach);
        }

        // ✅ نضيف التغطيات الجديدة
        $toAttach = array_values(array_diff($selected, $existing));
        if (!empty($toAttach)) {
            $attachData = [];
            foreach ($toAttach as $uid) {
                $attachData[$uid] = [
                    'is_primary' => 0,
                    'starts_at' => now(),
                    'ends_at' => null,
                ];
            }
            $h->muhafidhs()->attach($attachData);
        }

        // ✅ نضمن أن كل المختارين is_primary=0
        foreach ($selected as $uid) {
            $h->muhafidhs()->updateExistingPivot($uid, [
                'is_primary' => 0,
                'starts_at' => now(),
                'ends_at' => null,
            ]);
        }

        DB::commit();

        session()->flash('success', 'تم تحديث التغطية ✅');
        $this->closeCoverModal();

    } catch (\Throwable $e) {
        DB::rollBack();
        throw $e;
    }
}


public function openStudents(int $halaqaId): void
{
    $h = Halaqa::findOrFail($halaqaId);

    $this->studentsHalaqaId = $h->id;
    $this->studentsHalaqaName = $h->name;
    $this->studentsSearch = '';

    $this->loadStudentsList();

    $this->showStudentsModal = true;
}

public function updatedStudentsSearch(): void
{
    // تحديث القائمة عند البحث
    if ($this->showStudentsModal) {
        $this->loadStudentsList();
    }
}

private function loadStudentsList(): void
{
    if (!$this->studentsHalaqaId) {
        $this->studentsList = [];
        return;
    }

    $this->studentsList = Student::query()
        ->where('halaqa_id', $this->studentsHalaqaId)
        ->when($this->studentsSearch !== '', function ($q) {
            $q->where('name', 'like', '%' . $this->studentsSearch . '%');
        })
        ->orderBy('name')
        ->pluck('name')
        ->toArray();
}

public function closeStudentsModal(): void
{
    $this->showStudentsModal = false;
    $this->studentsHalaqaId = null;
    $this->studentsHalaqaName = '';
    $this->studentsSearch = '';
    $this->studentsList = [];
}



public function closeCoverModal(): void
{
    $this->showCoverModal = false;
    $this->coverHalaqaId = null;
    $this->coverHalaqaName = '';
    $this->cover_muhafidh_ids = [];
}

public function clearCover(): void
{
    // يمسح التحديد فقط (بدون حفظ)
    $this->cover_muhafidh_ids = [];
}


    public function delete(): void
    {
        if (!$this->deleteId) return;

        $h = Halaqa::findOrFail($this->deleteId);

        // ✅ حماية: لا تحذف حلقة فيها طلاب
        $studentsCount = Student::where('halaqa_id', $h->id)->count();
        if ($studentsCount > 0) {
            session()->flash('success', 'لا يمكن حذف الحلقة لأنها تحتوي طلاب. انقلهم أولاً.');
            $this->closeDeleteModal();
            return;
        }

        DB::beginTransaction();

        try {
            // ✅ فك الربط من pivot
            DB::table('halaqa_user')->where('halaqa_id', $h->id)->delete();

            // ✅ حذف الحلقة
            $h->delete();

            DB::commit();

            session()->flash('success', 'تم حذف الحلقة ✅');
            $this->closeDeleteModal();

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
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
        $this->reset(['name', 'is_active', 'muhafidh_id', 'editingId', 'isEdit']);
        $this->is_active = true;
    }
}
