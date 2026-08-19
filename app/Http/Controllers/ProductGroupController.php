<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ProductGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductGroupController extends Controller
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

        $query = ProductGroup::with('department');

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $productGroups = $query->latest()->paginate($size)->withQueryString();

        $departmentList = Department::orderBy('name')->get();

        return view('pages.admin.product-group.index', compact([
            'productGroups',
            'departmentList',
            'search',
            'departmentId'
        ]));
    }

    public function create()
    {
        $departmentList = Department::orderBy('name')->get();

        return view('pages.admin.product-group.create', compact('departmentList'));
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
                'unique:product_groups,name',
            ],
        ]);

        ProductGroup::create([
            'department_id' => $request->department_id,
            'name'          => $request->name,
        ]);

        return redirect()
            ->route('admin.product-group.index')
            ->with('success', 'Product Group created successfully.');
    }

    public function edit($id)
    {
        $productGroup = ProductGroup::findOrFail($id);
        $departmentList = Department::orderBy('name')->get();

        return view(
            'pages.admin.product-group.edit',
            compact('productGroup', 'departmentList')
        );
    }

    public function update(Request $request, $id)
    {
        $productGroup = ProductGroup::findOrFail($id);

        $request->validate([
            'department_id' => [
                'required',
                'exists:departments,id',
            ],
            'name' => [
                'required',
                Rule::unique('product_groups', 'name')->ignore($id),
            ],
        ]);

        $productGroup->update([
            'department_id' => $request->department_id,
            'name'          => $request->name,
        ]);

        return redirect()
            ->route('admin.product-group.index')
            ->with('success', 'Product Group updated successfully.');
    }

    public function destroy($id)
    {
        $productGroup = ProductGroup::findOrFail($id);

        $productGroup->delete();

        return redirect()
            ->route('admin.product-group.index')
            ->with('success', 'Product Group deleted successfully.');
    }
}
