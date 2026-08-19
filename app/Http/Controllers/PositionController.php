<?php

namespace App\Http\Controllers;

use App\Http\Requests\PositionStoreRequest;
use App\Http\Requests\PositionUpdateRequest;
use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size'   => ['nullable', 'integer']
        ]);

        $search = $request->input('search');
        $size   = $request->input('size', 10);

        $query = Position::query();

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $positions = $query->latest()->paginate($size)->withQueryString();

        return view('pages.admin.position.index', compact([
            'positions',
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

        $query = Position::query();

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $positions = $query->latest()->paginate($size)->withQueryString();

        return view('pages.general_manager.position.index', compact([
            'positions',
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

        $query = Position::query();

        if($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $positions = $query->latest()->paginate($size)->withQueryString();

        return view('pages.manager.position.index', compact([
            'positions',
            'search'
        ]));
    }

    public function create()
    {
        return view('pages.admin.position.create');
    }

    public function store(PositionStoreRequest $request)
    {
        Position::create([
            'name' => $request->name,
        ]);

        return redirect()->route('position.index')->with('success', 'Position berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $position = Position::findOrFail($id);

        return view('pages.admin.position.edit', compact('position'));
    }
    
    public function update(PositionUpdateRequest $request, $id)
    {
        $position = Position::findOrFail($id);

        $position->name = $request->name;

        $position->save();

        return redirect()->route('position.index')->with('success', 'Position berhasil diupdate.');
    }

    public function destroy($id)
    {
        $position = Position::findOrFail($id);

        $position->delete();

        return redirect()->route('position.index')->with('success', 'Position berhasil dihapus');
    }
}
