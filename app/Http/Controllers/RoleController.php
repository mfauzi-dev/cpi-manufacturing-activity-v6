<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{

    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size'   => ['nullable', 'integer']
        ]);

        $search = $request->input('search');
        $size   = $request->input('size', 10);

        $query = Role::query();

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $roles = $query->latest()->paginate($size)->withQueryString();

        return view('pages.admin.role.index', compact([
            'roles',
            'search'
        ]));
    }

    public function create()
    {
        return view('pages.admin.role.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                        => ['required', 'string', 'max:255', 'unique:roles,name'],
            'can_access_all_departments'  => ['nullable', 'boolean'],
        ]);

        Role::create([
            'name'                        => $request->name,
            'can_access_all_departments'  => $request->boolean('can_access_all_departments'),
        ]);

        return redirect()->route('role.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);

        return view('pages.admin.role.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'can_access_all_departments' => ['nullable', 'boolean'],
        ]);

        $role->name                       = $request->name;
        $role->can_access_all_departments = $request->boolean('can_access_all_departments');

        $role->save();

        return redirect()->route('role.index')->with('success', 'Role berhasil diupdate.');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $role->delete();

        return redirect()->route('role.index')->with('success', 'Role berhasil dihapus');
    }
}