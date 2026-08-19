<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutsourcingStoreRequest;
use App\Http\Requests\OutsourcingUpdateRequest;
use App\Models\Outsourcing;
use Illuminate\Http\Request;

class OutsourcingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size'   => ['nullable', 'integer']
        ]);

        $search = $request->input('search');
        $size   = $request->input('size', 10);

        $query = Outsourcing::query();

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $outsourcings = $query->latest()->paginate($size)->withQueryString();

        return view('pages.admin.outsourcing.index', compact([
            'outsourcings',
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

        $query = Outsourcing::query();

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $outsourcings = $query->latest()->paginate($size)->withQueryString();

        return view('pages.general_manager.outsourcing.index', compact([
            'outsourcings',
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

        $query = Outsourcing::query();

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $outsourcings = $query->latest()->paginate($size)->withQueryString();

        return view('pages.manager.outsourcing.index', compact([
            'outsourcings',
            'search'
        ]));
    }

    public function create()
    {
        return view('pages.admin.outsourcing.create');
    }

    public function store(OutsourcingStoreRequest $request)
    {
        Outsourcing::create([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.outsourcing.index')->with('success', 'Position berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $outsourcing = Outsourcing::findOrFail($id);

        return view('pages.admin.outsourcing.edit', compact('outsourcing'));
    }
    
    public function update(OutsourcingUpdateRequest $request, $id)
    {
        $Outsourcing = Outsourcing::findOrFail($id);

        $Outsourcing->name        = $request->name;

        $Outsourcing->save();

        return redirect()->route('admin.outsourcing.index')->with('success', 'Outsourcing berhasil diupdate.');
    }

    public function destroy($id)
    {
        $Outsourcing = Outsourcing::findOrFail($id);

        $Outsourcing->delete();

        return redirect()->route('admin.outsourcing.index')->with('success', 'outsourcing berhasil dihapus');
    }
}
