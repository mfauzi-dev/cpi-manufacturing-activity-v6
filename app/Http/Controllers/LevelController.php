<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer'],
        ]);

        $search = $request->input('search');
        $size = $request->input('size', 10);

        $query = Level::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $levels = $query->latest()->paginate($size)->withQueryString();

        return view('pages.admin.level.index', compact([
            'levels',
            'search',
        ]));
    }

    public function create()
    {
        return view('pages.admin.level.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Level::create([
            'name' => $request->name,
        ]);

        return redirect()
            ->route('admin.level.index')
            ->with('success', 'Level berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $level = Level::findOrFail($id);

        return view('pages.admin.level.edit', compact('level'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $level = Level::findOrFail($id);

        $level->name = $request->name;
        $level->save();

        return redirect()
            ->route('admin.level.index')
            ->with('success', 'Level berhasil diupdate.');
    }

    public function destroy($id)
    {
        $level = Level::findOrFail($id);

        $level->delete();

        return redirect()
            ->route('admin.level.index')
            ->with('success', 'Level berhasil dihapus.');
    }
}