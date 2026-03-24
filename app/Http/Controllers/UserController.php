<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $users = User::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('app.users.index', compact('users', 'q'));
    }

    public function create()
{
    $roles = Role::orderBy('name')->pluck('name');

    $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
        return explode('.', $p->name)[0] ?? 'other';
    });

    return view('app.users.create', compact('roles', 'permissions'));
}


   public function store(StoreUserRequest $request)
{
    $data = $request->validated();

    // ✅ إنشاء المستخدم
    $user = User::create([
        'name'              => $data['name'],
        'email'             => $data['email'],
        'password'          => Hash::make($data['password']),
        'email_verified_at' => now(), // اختياري: إذا تبي اللي ينشأ من الأدمن يكون verified
    ]);

    // ✅ قفل أمان: ما أحد يعطي super-admin إلا super-admin
    $roleName = $data['role'] ?? null;

    if ($roleName === 'super-admin' && !auth()->user()->hasRole('super-admin')) {
        abort(403);
    }

    // ✅ تعيين الدور (دور واحد)
    if (!empty($roleName)) {
        $user->syncRoles([$roleName]);
    }

    // ✅ صلاحيات مباشرة للمستخدم (اختياري)
    $user->syncPermissions($data['permissions'] ?? []);

    return redirect()->route('users.index')->with('success', 'تم إنشاء المستخدم بنجاح');
}


   public function edit(User $user)
{
    $roles = Role::orderBy('name')->pluck('name');

    $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
        return explode('.', $p->name)[0] ?? 'other';
    });

    $userRole = $user->roles()->pluck('name')->first();
    $directPermissions = $user->getDirectPermissions()->pluck('name')->toArray();

    return view('app.users.edit', compact('user', 'roles', 'permissions', 'userRole', 'directPermissions'));
}


  public function update(UpdateUserRequest $request, User $user)
{
    $data = $request->validated();

    // ====== تحديث البيانات الأساسية ======
    $user->name  = $data['name'];
    $user->email = $data['email'];

    if (!empty($data['password'])) {
        $user->password = Hash::make($data['password']);
    }

    $user->save();

    // ====== قفل أمان super-admin ======
    $currentIsSuper = auth()->check() && auth()->user()->hasRole('super-admin');
    $targetIsSuper  = $user->hasRole('super-admin');

    // ✅ إذا الهدف super-admin: ما أحد يقدر يعدّله إلا super-admin
    if ($targetIsSuper && !$currentIsSuper) {
        abort(403);
    }

    // الدور الجديد المطلوب
    $roleName = $data['role'] ?? null;

    // ✅ ما أحد يعطي super-admin إلا super-admin
    if ($roleName === 'super-admin' && !$currentIsSuper) {
        abort(403);
    }

    // ✅ ما تقدر تشيل super-admin عن نفسك بالغلط
    if ($user->id === auth()->id() && $targetIsSuper && $roleName !== 'super-admin') {
        return back()->with('error', 'ما تقدر تشيل super-admin عن حسابك.');
    }

    // ====== تحديث الدور (اختياري) ======
    if (array_key_exists('role', $data)) {
        if (!empty($roleName)) {
            $user->syncRoles([$roleName]);   // دور واحد
        } else {
            // ✅ ملاحظة: لو المستخدم super-admin لا تسمح بتفريغ دوره (احتياط)
            if ($targetIsSuper) {
                return back()->with('error', 'لا يمكن تفريغ دور super-admin.');
            }

            $user->syncRoles([]); // لو اخترت فارغ
        }
    }

    // ====== صلاحيات مباشرة للمستخدم ======
    $user->syncPermissions($data['permissions'] ?? []);

    return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح');
}


    public function destroy(User $user)
    {
        // حماية بسيطة: لا تحذف نفسك بالغلط
        if (auth()->id() === $user->id) {
            return back()->with('error', 'ما تقدر تحذف حسابك وأنت مسجل دخول.');
        }

        $user->delete();

        return back()->with('success', 'تم حذف المستخدم.');
    }
}
