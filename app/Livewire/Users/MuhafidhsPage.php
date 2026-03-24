<?php

namespace App\Livewire\Users;

use App\Models\Halaqa;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class MuhafidhsPage extends Component
{
    public string $search = '';

    public bool $showModal = false;
    public bool $isEdit = false;
    public ?int $editingId = null;

    // form
    public string $name = '';
    public string $email = '';
    public string $password = '';

    // Assignments
    public ?int $primary_halaqa_id = null;
    public array $extra_halaqa_ids = []; // حلقات إضافية (تغطية)

    // delete
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public function render()
    {
        $user = auth()->user();

        $muhafidhs = User::query()
            ->role('muhafidh')
            ->with(['halaqas:id,name'])
            ->when($user->isCenterAdmin() && $user->center_id, fn($q) => $q->where('center_id', $user->center_id))
            ->when($this->search !== '', function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%");
            })
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                $u->halaqas_count = $u->halaqas->count();
                $u->primary_halaqa_name = optional(
                    $u->halaqas->firstWhere(fn($h) => (int)$h->pivot->is_primary === 1)
                )->name;
                return $u;
            });

        $halaqas = Halaqa::query()
            ->select('id','name')
            ->when($user->isCenterAdmin() && $user->center_id, fn($q) => $q->where('center_id', $user->center_id))
            ->orderBy('name')
            ->get();

        return view('livewire.users.muhafidhs-page', [
            'muhafidhs' => $muhafidhs,
            'halaqas' => $halaqas,
        ])->layout('components.layouts.app', ['header' => 'المحفظون']);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->editingId = null;
        $this->showModal = true;

        // كلمة مرور افتراضية (تقدر تغيرها)
        $this->password = '123456';
    }

    public function openEdit(int $id): void
    {
        $u = User::role('muhafidh')->with('halaqas')->findOrFail($id);

        $this->isEdit = true;
        $this->editingId = $u->id;

        $this->name = $u->name;
        $this->email = $u->email;
        $this->password = '';

        $primary = $u->halaqas->firstWhere(fn($h) => (int)$h->pivot->is_primary === 1);
        $this->primary_halaqa_id = $primary?->id;

        $this->extra_halaqa_ids = $u->halaqas
            ->filter(fn($h) => (int)$h->pivot->is_primary !== 1)
            ->pluck('id')
            ->map(fn($v)=>(int)$v)
            ->values()
            ->toArray();

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users','email')->ignore($this->editingId)],
            'primary_halaqa_id' => ['nullable','exists:halaqas,id'],
            'extra_halaqa_ids' => ['array'],
            'extra_halaqa_ids.*' => ['exists:halaqas,id'],
        ];

        // password: required on create, optional on edit
        if ($this->isEdit) {
            $rules['password'] = ['nullable','string','min:6'];
        } else {
            $rules['password'] = ['required','string','min:6'];
        }

        $data = $this->validate($rules);

        if ($this->isEdit && $this->editingId) {
            $u = User::findOrFail($this->editingId);
            $u->name = $this->name;
            $u->email = $this->email;
            if (!empty($this->password)) {
                $u->password = Hash::make($this->password);
            }
            $u->save();
        } else {
            $creator = auth()->user();
            $u = User::create([
                'name'      => $this->name,
                'email'     => $this->email,
                'password'  => Hash::make($this->password),
                'center_id' => $creator->isCenterAdmin() ? $creator->center_id : null,
            ]);
            $u->assignRole('muhafidh');
        }

        // ✅ تجهيز sync للحلقات
        $primaryId = $this->primary_halaqa_id ? (int)$this->primary_halaqa_id : null;

        $extraIds = collect($this->extra_halaqa_ids)
            ->map(fn($v)=>(int)$v)
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // لا تكرر الأساسي داخل الإضافي
        $extraIds = array_values(array_filter($extraIds, fn($id) => $primaryId ? $id !== $primaryId : true));

        $sync = [];

        if ($primaryId) {
            $sync[$primaryId] = ['is_primary' => true, 'starts_at' => now(), 'ends_at' => null];
        }

        foreach ($extraIds as $id) {
            $sync[$id] = ['is_primary' => false, 'starts_at' => now(), 'ends_at' => null];
        }

        $u->halaqas()->sync($sync);

        session()->flash('success', $this->isEdit ? 'تم تعديل المحفظ ✅' : 'تم إضافة المحفظ ✅');
        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (!$this->deleteId) return;

        $u = User::role('muhafidh')->findOrFail($this->deleteId);

        // نفك الربط مع الحلقات قبل الحذف
        $u->halaqas()->detach();

        $u->delete();

        session()->flash('success', 'تم حذف المحفظ ✅');
        $this->closeDeleteModal();
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
        $this->reset(['name','email','password','primary_halaqa_id','extra_halaqa_ids','isEdit','editingId']);
        $this->extra_halaqa_ids = [];
    }
}
