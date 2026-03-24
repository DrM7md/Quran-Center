<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));

        $permissions = Permission::query()
            ->when($q !== '', fn($qr) => $qr->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('app.permissions.index', compact('permissions', 'q'));
    }
}
