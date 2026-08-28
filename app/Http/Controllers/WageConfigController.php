<?php

namespace App\Http\Controllers;

use App\Http\Requests\WageConfigStoreRequest;
use App\Http\Requests\WageConfigUpdateRequest;
use App\Models\WageConfig;
use Illuminate\Http\Request;

class WageConfigController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'size'   => ['nullable', 'integer'],
        ]);

        $search = $request->input('search');
        $size   = $request->input('size', 10);

        $query = WageConfig::query();

        if ($search) {
            $query->where('tahun', 'like', '%' . $search . '%');
        }

        $wageConfigs = $query->latest('tahun')->paginate($size)->withQueryString();

        return view('pages.admin.wage-config.index', compact([
            'wageConfigs',
            'search',
        ]));
    }

    public function create()
    {
        return view('pages.admin.wage-config.create');
    }

    public function store(WageConfigStoreRequest $request)
    {
        WageConfig::create([
            'tahun'              => $request->tahun,
            'ump'                => $request->ump,
            'hari_kerja_standar' => $request->hari_kerja_standar,
        ]);

        return redirect()->route('admin.wage-config.index')->with('success', 'UMP berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $wageConfig = WageConfig::findOrFail($id);

        return view('pages.admin.wage-config.edit', compact('wageConfig'));
    }

    public function update(WageConfigUpdateRequest $request, $id)
    {
        $wageConfig = WageConfig::findOrFail($id);

        $wageConfig->tahun              = $request->tahun;
        $wageConfig->ump                = $request->ump;
        $wageConfig->hari_kerja_standar = $request->hari_kerja_standar;

        $wageConfig->save();

        return redirect()->route('admin.wage-config.index')->with('success', 'UMP berhasil diupdate.');
    }

    public function destroy($id)
    {
        $wageConfig = WageConfig::findOrFail($id);

        $wageConfig->delete();

        return redirect()->route('admin.wage-config.index')->with('success', 'UMP berhasil dihapus.');
    }
}