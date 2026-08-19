<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = $request->input('search');
        $size   = $request->input('size', 10);

        $query = User::with('role');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('role', function ($roleQuery) use ($search) {
                        $roleQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query
            ->latest()
            ->paginate($size)
            ->withQueryString();

        return view('pages.admin.user.index', compact(
            'users',
            'search'
        ));
    }

    public function generalManagerIndex(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = $request->input('search');
        $size   = $request->input('size', 10);

        $query = User::with('role');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('role', function ($roleQuery) use ($search) {
                        $roleQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query
            ->latest()
            ->paginate($size)
            ->withQueryString();

        return view('pages.general_manager.user.index', compact(
            'users',
            'search'
        ));
    }

    public function managerIndex(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size'   => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = $request->input('search');
        $size   = $request->input('size', 10);

        $query = User::with('role');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('role', function ($roleQuery) use ($search) {
                        $roleQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $users = $query
            ->latest()
            ->paginate($size)
            ->withQueryString();

        return view('pages.manager.user.index', compact(
            'users',
            'search'
        ));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        return view('pages.admin.user.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:6'],
            'role_id'  => ['required', 'exists:roles,id'],
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role_id'  => $request->role_id,
        ]);

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        $user->delete();

        return redirect()
            ->route('admin.user.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
