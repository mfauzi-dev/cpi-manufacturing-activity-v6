<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ProcessType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcessTypeController extends Controller
{
        public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
            'department_id' => ['nullable', 'exists:departments,id'],
        ]);

        $search = $request->search;
        $size = $request->size ?? 50;
        $departmentId = $request->input('department_id');

        $query = ProcessType::with('department');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $departmentList = Department::orderBy('name')->get();

        $processTypes = $query->orderBy('name')->paginate($size)->withQueryString();

        return view('pages.admin.process-type.index', compact([
            'processTypes',
            'search',
            'departmentId',
            'departmentList',
        ]));
    }

    public function create()
    {
        $departmentList = Department::orderBy('name')->get();

        return view('pages.admin.process-type.create', compact([
            'departmentList',
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
        ]);

        ProcessType::create([
            'name' => $request->name,
            'department_id' => $request->department_id,
        ]);

        return redirect()
            ->route('admin.process-type.index')
            ->with('success', 'Process Type berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $processType = ProcessType::findOrFail($id);

        $departmentList = Department::orderBy('name')->get();

        return view('pages.admin.process-type.edit', compact([
            'processType',
            'departmentList',
        ]));
    }

    public function update(Request $request, $id)
    {
        $processType = ProcessType::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
        ]);

        $processType->update([
            'name' => $request->name,
            'department_id' => $request->department_id,
        ]);

        return redirect()
            ->route('admin.process-type.index')
            ->with('success', 'Process Type berhasil diupdate.');
    }

    public function destroy($id)
    {
        $processType = ProcessType::findOrFail($id);

        $processType->delete();

        return redirect()
            ->route('admin.process-type.index')
            ->with('success', 'Process Type berhasil dihapus.');
    }
}
