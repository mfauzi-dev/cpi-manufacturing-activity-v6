<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CostCenterController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search'        => ['nullable', 'string'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'size'          => ['nullable', 'integer']
        ]);

        $search        = $request->input('search');
        $departmentId  = $request->input('department_id');
        $size          = $request->input('size', 10);

        $query = CostCenter::with('department');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $costCenters = $query->latest()->paginate($size)->withQueryString();

        $departmentList = Department::orderBy('name')->get();

        return view('pages.admin.cost-center.index', compact([
            'costCenters',
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
            'size'          => ['nullable', 'integer']
        ]);

        $search        = $request->input('search');
        $departmentId  = $request->input('department_id');
        $size          = $request->input('size', 10);

        $query = CostCenter::with('department');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $costCenters = $query->latest()->paginate($size)->withQueryString();

        $departmentList = Department::orderBy('name')->get();

        return view('pages.general_manager.cost-center.index', compact([
            'costCenters',
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
            'size'          => ['nullable', 'integer']
        ]);

        $search        = $request->input('search');
        $departmentId  = $request->input('department_id');
        $size          = $request->input('size', 10);

        $query = CostCenter::with('department');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('name', 'like', '%' . $search . '%');
            });
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $costCenters = $query->latest()->paginate($size)->withQueryString();

        $departmentList = Department::orderBy('name')->get();

        return view('pages.manager.cost-center.index', compact([
            'costCenters',
            'departmentList',
            'search',
            'departmentId'
        ]));
    }

    public function create()
    {
        $departmentList = Department::orderBy('name')->get();

        return view('pages.admin.cost-center.create', compact('departmentList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => [
                'required',
                'exists:departments,id',
            ],
            'code' => [
                'required',
            ],
            'name' => [
                'required',
                Rule::unique('cost_centers', 'name')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                }),
            ],
        ]);

        CostCenter::create([
            'department_id' => $request->department_id,
            'code'          => $request->code,
            'name'          => $request->name,
        ]);

        return redirect()
            ->route('admin.cost-center.index')
            ->with('success', 'Cost Center created successfully.');
    }

    public function edit($id)
    {
        $costCenter = CostCenter::findOrFail($id);
        $departmentList = Department::orderBy('name')->get();

        return view(
            'pages.admin.cost-center.edit',
            compact('costCenter', 'departmentList')
        );
    }

    public function update(Request $request, $id)
    {
        $costCenter = CostCenter::findOrFail($id);

        $request->validate([
            'department_id' => [
                'required',
                'exists:departments,id',
            ],
            'code' => [
                'required',
            ],
            'name' => [
                'required',
                Rule::unique('cost_centers', 'name')->where(function ($query) use ($request) {
                    return $query->where('department_id', $request->department_id);
                })->ignore($costCenter->id),
            ],
        ]);

        $costCenter->update([
            'department_id' => $request->department_id,
            'code'          => $request->code,
            'name'          => $request->name,
        ]);

        return redirect()
            ->route('admin.cost-center.index')
            ->with('success', 'Cost Center updated successfully.');
    }

    public function destroy($id)
    {
        $costCenter = CostCenter::findOrFail($id);

        $costCenter->delete();

        return redirect()
            ->route('admin.cost-center.index')
            ->with('success', 'Cost Center deleted successfully.');
    }  
}
