<?php

namespace App\Livewire\Centers;

use App\Models\Center;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CentersPage extends Component
{
    public string $search = '';

    public bool $showModal = false;
    public bool $isEdit = false;
    public ?int $editingId = null;

    // form
    public string $name = '';
    public string $location = '';
    public bool $isActive = true;

    // أدمن المركز
    public string $adminName = '';
    public string $adminEmail = '';
    public string $adminPassword = '';

    // delete
    public bool $showDeleteModal = false;
    public ?int $deleteId = null;

    public function render()
    {
        $centers = Center::query()
            ->withCount(['halaqas', 'users'])
            ->when($this->search !== '', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(function ($c) {
                $c->admin = User::role('admin')
                    ->where('center_id', $c->id)
                    ->select('id', 'name', 'email')
                    ->first();
                return $c;
            });

        return view('livewire.centers.centers-page', compact('centers'))
            ->layout('components.layouts.app', ['header' => 'المراكز']);
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
        $center = Center::findOrFail($id);
        $admin  = User::role('admin')->where('center_id', $id)->first();

        $this->isEdit    = true;
        $this->editingId = $center->id;
        $this->name      = $center->name;
        $this->location  = $center->location ?? '';
        $this->isActive  = (bool) $center->is_active;

        $this->adminName     = $admin?->name ?? '';
        $this->adminEmail    = $admin?->email ?? '';
        $this->adminPassword = '';

        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'isActive' => ['boolean'],
            'adminName'  => ['required', 'string', 'max:255'],
            'adminEmail' => ['required', 'email', 'max:255'],
        ];

        if ($this->isEdit) {
            $existingAdmin = User::role('admin')->where('center_id', $this->editingId)->first();
            $ignoreId = $existingAdmin?->id;
            $rules['adminEmail'][]    = Rule::unique('users', 'email')->ignore($ignoreId);
            $rules['adminPassword']   = ['nullable', 'string', 'min:6'];
        } else {
            $rules['adminEmail'][]    = Rule::unique('users', 'email');
            $rules['adminPassword']   = ['required', 'string', 'min:6'];
        }

        $this->validate($rules, [
            'name.required'         => 'اكتب اسم المركز.',
            'adminName.required'    => 'اكتب اسم الأدمن.',
            'adminEmail.required'   => 'اكتب البريد الإلكتروني.',
            'adminEmail.unique'     => 'البريد الإلكتروني مستخدم مسبقاً.',
            'adminPassword.required'=> 'كلمة المرور مطلوبة.',
            'adminPassword.min'     => 'كلمة المرور 6 أحرف على الأقل.',
        ]);

        if ($this->isEdit && $this->editingId) {
            $center = Center::findOrFail($this->editingId);
            $center->update(['name' => $this->name, 'location' => $this->location ?: null, 'is_active' => $this->isActive]);

            $admin = User::role('admin')->where('center_id', $center->id)->first();

            if ($admin) {
                $admin->name  = $this->adminName;
                $admin->email = $this->adminEmail;
                if (!empty($this->adminPassword)) {
                    $admin->password = Hash::make($this->adminPassword);
                }
                $admin->save();
            } else {
                $this->createAdminForCenter($center);
            }
        } else {
            $center = Center::create(['name' => $this->name, 'location' => $this->location ?: null, 'is_active' => $this->isActive]);
            $this->createAdminForCenter($center);
        }

        session()->flash('success', $this->isEdit ? 'تم تعديل المركز ✅' : 'تم إضافة المركز ✅');
        $this->closeModal();
    }

    private function createAdminForCenter(Center $center): void
    {
        $admin = User::create([
            'name'      => $this->adminName,
            'email'     => $this->adminEmail,
            'password'  => Hash::make($this->adminPassword),
            'center_id' => $center->id,
        ]);
        $admin->assignRole('admin');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if (!$this->deleteId) {
            return;
        }

        $center = Center::withCount('halaqas')->findOrFail($this->deleteId);

        if ($center->halaqas_count > 0) {
            session()->flash('error', 'لا يمكن حذف المركز لأنه يحتوي حلقات. انقلها أولاً.');
            $this->closeDeleteModal();
            return;
        }

        // إلغاء ربط المستخدمين بالمركز قبل الحذف
        User::where('center_id', $center->id)->update(['center_id' => null]);

        $center->delete();

        session()->flash('success', 'تم حذف المركز ✅');
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
        $this->reset(['name', 'location', 'isActive', 'adminName', 'adminEmail', 'adminPassword', 'editingId', 'isEdit']);
        $this->isActive = true;
    }
}
