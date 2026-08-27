<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Line;
use Illuminate\Http\Request;

class LineController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search'        => ['nullable', 'string'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'size'          => ['nullable', 'integer'],
        ]);
 
        $search       = $request->input('search');
        $departmentId = $request->input('department_id');
        $size         = $request->input('size', 10);
 
        $query = Line::with('department');
 
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
 
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
 
        $lines = $query->latest()->paginate($size)->withQueryString();
 
        $departmentList = Department::orderBy('name')->get();
 
        return view('pages.admin.line.index', compact([
            'lines',
            'departmentList',
            'search',
            'departmentId'
        ]));
    }
 
    public function generalManagerIndex(Request $request)
    {
        $request->validate([
            'search'        => ['nullable', 'string'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'size'          => ['nullable', 'integer'],
        ]);
 
        $search       = $request->input('search');
        $departmentId = $request->input('department_id');
        $size         = $request->input('size', 10);
 
        $query = Line::with('department');
 
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
 
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
 
        $lines = $query->latest()->paginate($size)->withQueryString();
 
        $departmentList = Department::orderBy('name')->get();
 
        return view('pages.general_manager.line.index', compact([
            'lines',
            'departmentList',
            'search',
            'departmentId'
        ]));
    }
 
    public function managerIndex(Request $request)
    {
        $request->validate([
            'search'        => ['nullable', 'string'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'size'          => ['nullable', 'integer'],
        ]);
 
        $search       = $request->input('search');
        $departmentId = $request->input('department_id');
        $size         = $request->input('size', 10);
 
        $query = Line::with('department');
 
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
 
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
 
        $lines = $query->latest()->paginate($size)->withQueryString();
 
        $departmentList = Department::orderBy('name')->get();
 
        return view('pages.manager.line.index', compact([
            'lines',
            'departmentList',
            'search',
            'departmentId'
        ]));
    }
 
    public function create()
    {
        $departmentList = Department::orderBy('name')->get();
 
        return view('pages.admin.line.create', compact('departmentList'));
    }
 
    public function store(Request $request)
    {
        $request->validate([
            'department_id' => [
                'required',
                'exists:departments,id',
            ],
            'name' => [
                'required',
            ],
        ]);
 
        Line::create([
            'department_id' => $request->department_id,
            'name'          => $request->name,
        ]);
 
        return redirect()
            ->route('admin.line.index')
            ->with('success', 'Line created successfully.');
    }
 
    public function edit($id)
    {
        $line = Line::findOrFail($id);
        $departmentList = Department::orderBy('name')->get();
 
        return view(
            'pages.admin.line.edit',
            compact('line', 'departmentList')
        );
    }
 
    public function update(Request $request, $id)
    {
        $line = Line::findOrFail($id);
 
        $request->validate([
            'department_id' => [
                'required',
                'exists:departments,id',
            ],
            'name' => [
                'required',
            ],
        ]);
 
        $line->update([
            'department_id' => $request->department_id,
            'name'          => $request->name,
        ]);
 
        return redirect()
            ->route('admin.line.index')
            ->with('success', 'Line updated successfully.');
    }
 
    public function destroy($id)
    {
        $line = Line::findOrFail($id);
 
        $line->delete();
 
        return redirect()
            ->route('admin.line.index')
            ->with('success', 'Line deleted successfully.');
    }
}
