<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{


private function isSuperAdminRole(Role $role): bool
{
    return $role->name === 'super-admin';
}

private function currentUserIsSuperAdmin(): bool
{
    return auth()->check() && auth()->user()->hasRole('super-admin');
}




    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $roles = Role::query()
            ->when($q !== '', fn($qr) => $qr->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
            return explode('.', $p->name)[0] ?? 'other';
        });

        return view('app.roles.index', compact('roles', 'permissions', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:50', 'regex:/^[a-z0-9\-_]+$/i', 'unique:roles,name'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);

        $role->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', 'تم إنشاء الدور بنجاح');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(fn($p) => explode('.', $p->name)[0] ?? 'other');
        $rolePermissions = $role->permissions()->pluck('name')->toArray();

        return view('app.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

public function update(Request $request, Role $role)
{
    // ✅ حماية: لا أحد يقدر يعدّل super-admin إلا super-admin
    if ($this->isSuperAdminRole($role) && !$this->currentUserIsSuperAdmin()) {
        abort(403);
    }

    $data = $request->validate([
        'name' => ['required','string','max:50', 'regex:/^[a-z0-9\-_]+$/i', Rule::unique('roles','name')->ignore($role->id)],
        'permissions' => ['array'],
        'permissions.*' => ['string'],
    ]);

    // ✅ حماية إضافية: اسم super-admin ما يتغير نهائي
    if ($this->isSuperAdminRole($role) && $data['name'] !== 'super-admin') {
        return back()->with('error', 'لا يمكن تغيير اسم دور super-admin.');
    }

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $role->update(['name' => $data['name']]);
    $role->syncPermissions($data['permissions'] ?? []);

    return redirect()->route('roles.index')->with('success', 'تم تحديث الدور بنجاح');
}


public function destroy(Role $role)
{
    // ✅ super-admin محمي من الحذف نهائيًا
    if ($this->isSuperAdminRole($role)) {
        return back()->with('error', 'لا يمكن حذف دور super-admin.');
    }

    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $role->delete();

    return back()->with('success', 'تم حذف الدور.');
}

}
