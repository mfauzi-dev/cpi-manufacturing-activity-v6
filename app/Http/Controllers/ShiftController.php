<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer']
        ]);

        $search = $request->input('search');
        $size = $request->input('size', 10);

        $query = Shift::with('department');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('department', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            });
        }

        $shifts = $query
            ->join('departments', 'departments.id', '=', 'shifts.department_id')
            ->orderBy('departments.name')
            ->orderBy('shifts.id')
            ->select('shifts.*')
            ->paginate($size)
            ->withQueryString();

        return view('pages.admin.shift.index', compact([
            'shifts',
            'search'
        ]));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('pages.admin.shift.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shifts')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                }),
            ],
        ], [
            'department_id.required' => 'Department wajib dipilih.',
            'department_id.exists' => 'Department tidak valid.',
            'name.required' => 'Nama shift wajib diisi.',
            'name.unique' => 'Nama shift sudah digunakan pada department tersebut.',
        ]);

        Shift::create([
            'department_id' => $request->department_id,
            'name' => $request->name,
            'jam_masuk' => $request->jam_masuk,
            'jam_keluar' => $request->jam_keluar,
        ]);

        return redirect()
            ->route('admin.shift.index')
            ->with('success', 'Shift berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $shift = Shift::findOrFail($id);
        $departments = Department::orderBy('name')->get();

        return view('pages.admin.shift.edit', compact([
            'shift',
            'departments'
        ]));
    }

    public function update(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);

        $request->validate([
            'department_id' => ['required', 'exists:departments,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shifts')
                    ->ignore($shift->id)
                    ->where(function ($query) use ($request) {
                        return $query->where(
                            'department_id',
                            $request->department_id
                        );
                    }),
            ],
        ], [
            'department_id.required' => 'Department wajib dipilih.',
            'department_id.exists' => 'Department tidak valid.',
            'name.required' => 'Nama shift wajib diisi.',
            'name.unique' => 'Nama shift sudah digunakan pada department tersebut.',
        ]);

        $shift->department_id = $request->department_id;
        $shift->name = $request->name;
        $shift->save();

        return redirect()
            ->route('admin.shift.index')
            ->with('success', 'Shift berhasil diupdate.');
    }

    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);

        $shift->delete();

        return redirect()
            ->route('admin.shift.index')
            ->with('success', 'Shift berhasil dihapus.');
    }
}