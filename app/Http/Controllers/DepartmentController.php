<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentStoreRequest;
use App\Http\Requests\DepartmentUpdateRequest;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size'   => ['nullable', 'integer']
        ]);

        $search = $request->input('search');
        $size   = $request->input('size', 10);

        $query = Department::with('employees');

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $departments = $query->latest()->paginate($size)->withQueryString();

        return view('pages.admin.department.index', compact([
            'departments',
            'search'
        ]));
    }

    public function generalManagerIndex(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size'   => ['nullable', 'integer']
        ]);

        $search = $request->input('search');
        $size   = $request->input('size', 10);

        $query = Department::with('employees');

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $departments = $query->latest()->paginate($size)->withQueryString();

        return view('pages.general_manager.department.index', compact([
            'departments',
            'search'
        ]));
    }

    public function managerIndex(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size'   => ['nullable', 'integer']
        ]);

        $search = $request->input('search');
        $size   = $request->input('size', 10);

        $query = Department::with('employees');

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $departments = $query->latest()->paginate($size)->withQueryString();

        return view('pages.manager.department.index', compact([
            'departments',
            'search'
        ]));
    }

    public function create()
    {
        return view('pages.admin.department.create');
    }

    public function store(DepartmentStoreRequest $request)
    {
        Department::create([
            'name'          => $request->name,
        ]);

        return redirect()->route('department.index')->with('success', 'Department berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);

        return view('pages.admin.department.edit', compact('department'));
    }
    
    public function update(DepartmentUpdateRequest $request, $id)
    {
        $department = Department::findOrFail($id);

        $department->name        = $request->name;

        $department->save();

        return redirect()->route('department.index')->with('success', 'Department berhasil diupdate.');
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        $department->delete();

        return redirect()->route('department.index')->with('success', 'Department berhasil dihapus');
    }
}
