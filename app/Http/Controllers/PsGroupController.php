<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\PsGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PsGroupController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search'         => ['nullable', 'string'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'size'           => ['nullable', 'integer'],
        ]);

        $search       = $request->input('search');
        $costCenterId = $request->input('cost_center_id');
        $size         = $request->input('size', 10);

        $query = PsGroup::with('costCenter');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        $psGroups = $query->latest()->paginate($size)->withQueryString();

        $costCenterList = CostCenter::orderBy('name')->get();

        return view('pages.admin.ps-group.index', compact([
            'psGroups',
            'costCenterList',
            'search',
            'costCenterId'
        ]));
    }

    public function generalManagerIndex(Request $request)
    {
        $request->validate([
            'search'         => ['nullable', 'string'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'size'           => ['nullable', 'integer'],
        ]);

        $search       = $request->input('search');
        $costCenterId = $request->input('cost_center_id');
        $size         = $request->input('size', 10);

        $query = PsGroup::with('costCenter');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        $psGroups = $query->latest()->paginate($size)->withQueryString();

        $costCenterList = CostCenter::orderBy('name')->get();

        return view('pages.general_manager.ps-group.index', compact([
            'psGroups',
            'costCenterList',
            'search',
            'costCenterId'
        ]));
    }

    public function managerIndex(Request $request)
    {
        $request->validate([
            'search'         => ['nullable', 'string'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'size'           => ['nullable', 'integer'],
        ]);

        $search       = $request->input('search');
        $costCenterId = $request->input('cost_center_id');
        $size         = $request->input('size', 10);

        $query = PsGroup::with('costCenter');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        $psGroups = $query->latest()->paginate($size)->withQueryString();

        $costCenterList = CostCenter::orderBy('name')->get();

        return view('pages.manager.ps-group.index', compact([
            'psGroups',
            'costCenterList',
            'search',
            'costCenterId'
        ]));
    }

    public function create()
    {
        $costCenterList = CostCenter::orderBy('name')->get();

        return view('pages.admin.ps-group.create', compact('costCenterList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cost_center_id' => [
                'required',
                'exists:cost_centers,id',
            ],
            'name' => [
                'required',
            ],
        ]);

        PsGroup::create([
            'cost_center_id' => $request->cost_center_id,
            'name'           => $request->name,
        ]);

        return redirect()
            ->route('admin.ps-group.index')
            ->with('success', 'PS Group created successfully.');
    }

    public function edit($id)
    {
        $psGroup = PsGroup::findOrFail($id);
        $costCenterList = CostCenter::orderBy('name')->get();

        return view(
            'pages.admin.ps-group.edit',
            compact('psGroup', 'costCenterList')
        );
    }

    public function update(Request $request, $id)
    {
        $psGroup = PsGroup::findOrFail($id);

        $request->validate([
            'cost_center_id' => [
                'required',
                'exists:cost_centers,id',
            ],
            'name' => [
                'required',
            ],
        ]);

        $psGroup->update([
            'cost_center_id' => $request->cost_center_id,
            'name'           => $request->name,
        ]);

        return redirect()
            ->route('admin.ps-group.index')
            ->with('success', 'PS Group updated successfully.');
    }

    public function destroy($id)
    {
        $psGroup = PsGroup::findOrFail($id);

        $psGroup->delete();

        return redirect()
            ->route('admin.ps-group.index')
            ->with('success', 'PS Group deleted successfully.');
    }
}
