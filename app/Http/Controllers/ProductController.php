<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductUpdateRequest;
use App\Imports\ProductImport;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Product;
use App\Models\ProductGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function getByDepartment($departmentId)
    {
        $products = Product::where('department_id', $departmentId)
            ->select('id', 'name', 'amount')
            ->get();

        return response()->json($products);
    }

    public function costCenterByDepartment(Request $request)
    {
        $departmentId = auth()->user->department_id;

        if (!$departmentId) {
            return response()->json([]);
        }

        $costCenters = CostCenter::where('department_id', $departmentId)
            ->orderBy('name')
            ->get(['id','name']);
    
        return response()->json($costCenters);
    }
    
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
            'cost_center_id' => ['nullable', 'exists:cost_centers,id'],
            'product_group_id' => ['nullable', 'exists:product_groups,id'],
        ]);

        $search = $request->search;
        $size = $request->size ?? 50;
        $costCenterId = $request->input('cost_center_id');
        $productGroupId = $request->input('product_group_id');

        $query = Product::query();

        if ($search) {
            $query->where('material_name','like',"%{$search}%")
                ->orWhere('material_code', 'like',"%{$search}%");
        }

        if ($costCenterId) {
            $query->where('cost_center_id', $costCenterId);
        }

        if ($productGroupId) {
            $query->where('product_group_id', $productGroupId);
        }

        $departmentId = Auth::user()->department_id;

        $costCenterList = CostCenter::where('department_id', $departmentId)->orderBy('name')->get();

        $productGroupList = ProductGroup::where('department_id', $departmentId)->orderBy('name')->get();

        $products = $query->where('department_id', $departmentId)->orderBy('department_id')->paginate($size)->withQueryString();

        return view('pages.admin_production.product.index',compact([
                'products',
                'search',
                'costCenterId',
                'productGroupId',
                'costCenterList',
                'productGroupList',
            ])
        );
    }

    public function create()
    {
        $departmentId = auth()->user()->department_id;

        $costCenterList = CostCenter::where('department_id', $departmentId)->orderBy('name')->get();
        $productGroupList = ProductGroup::where('department_id', $departmentId)->orderBy('name')->get();

        return view('pages.admin_production.product.create', compact([
            'costCenterList',
            'productGroupList',
        ]));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_material' => [
                'required',
            ],
            'kode_material' => [
                'nullable',
            ],
            'cost_center_id' => [
                'required'
            ],
            'product_group_id' => [
                'nullable'
            ],
            'harga_per_kg' => [
                'required'
            ]
        ]);

        Product::create([
            'material_code' => $request->kode_material,
            'material_name' => $request->nama_material,
            'cost_center_id' => $request->cost_center_id,
            'product_group_id' => $request->product_group_id,
            'harga_per_kg' => $request->harga_per_kg,
            'department_id' => auth()->user()->department_id,
        ]);

        return redirect()
            ->route('admin-production.product.index')
            ->with(
                'success',
                'Product created successfully.'
            );
    }

    public function importPage()
    {
        $department = auth()->user()->department;

        $costCenterList = CostCenter::where('department_id', $department->id)
            ->orderBy('name')
            ->get();

        return view('pages.admin_production.product.import', compact([
            'department',
            'costCenterList',
        ]));
    }

    public function upload(Request $request)
    {
        try {

            $costCenter = CostCenter::findOrFail($request->cost_center_id);
            Excel::import(
            new ProductImport(
                $costCenter,
                ),
                $request->file('file')
            );

            return redirect()->route('admin-production.product.index')->with('success', 'Daily activity berhasil disimpan.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $departmentId = auth()->user()->department_id;

        $costCenterList = CostCenter::where('department_id', $departmentId)->orderBy('name')->get();

        $productGroupList = ProductGroup::where('department_id', $departmentId)->orderBy('name')->get();


        return view(
            'pages.admin_production.product.edit',
            compact([
                'product',
                'costCenterList',
                'productGroupList',
            ])
        );
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'nama_material' => [
                'required',
            ],
            'kode_material' => ['nullable'],
            'cost_center_id' => ['required'],
            'product_group_id' => ['nullable'],
            'harga_per_kg' => ['required'],
        ]);

        $product->update([
            'material_code' => $request->kode_material,
            'material_name' => $request->nama_material,
            'cost_center_id' => $request->cost_center_id,
            'product_group_id' => $request->product_group_id,
            'harga_per_kg' => $request->harga_per_kg,
            'department_id' => auth()->user()->department_id,
        ]);

        return redirect()
            ->route('admin-production.product.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return redirect()
            ->route('admin-production.product.index')
            ->with(
                'success',
                'Product deleted successfully.'
            );
    }
}
