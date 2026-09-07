<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\OvertimeRate;
use App\Models\Position;
use Illuminate\Http\Request;

class OvertimeRateController extends Controller
{
    private function authorizeManagerGeneralAffair()
    {
        $user = auth()->user();

        if (
            !$user ||
            strtolower($user->role?->name ?? '') !== 'manager' ||
            strtolower($user->department?->name ?? '') !== 'general affair'
        ) {
            abort(403, 'Anda tidak memiliki akses ke halaman Overtime Rate.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeManagerGeneralAffair();

        $request->validate([
            'tahun' => ['nullable', 'integer', 'digits:4'],
            'employee_status' => ['nullable', 'string'],
            'level_id' => ['nullable', 'integer', 'exists:levels,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'search' => ['nullable', 'string'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $tahun = $request->input('tahun');
        $employeeStatus = $request->input('employee_status');
        $levelId = $request->input('level_id');
        $positionId = $request->input('position_id');
        $search = $request->input('search');
        $size = $request->input('size', 10);

        $levels = Level::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();

        $query = OvertimeRate::with([
            'level',
            'position',
        ]);

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        if ($employeeStatus) {
            $query->where('employee_status', $employeeStatus);
        }

        if ($levelId) {
            $query->where('level_id', $levelId);
        }

        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('employee_status', 'like', "%{$search}%")
                    ->orWhereHas('level', function ($levelQuery) use ($search) {
                        $levelQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('position', function ($positionQuery) use ($search) {
                        $positionQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $overtimeRates = $query
            ->orderByDesc('tahun')
            ->orderBy('employee_status')
            ->paginate($size)
            ->withQueryString();

        return view('pages.manager.overtime-rate.index', compact(
            'overtimeRates',
            'tahun',
            'employeeStatus',
            'levelId',
            'positionId',
            'search',
            'size',
            'levels',
            'positions'
        ));
    }

    public function create()
    {
        $this->authorizeManagerGeneralAffair();

        $levels = Level::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();

        return view('pages.manager.overtime-rate.create', compact(
            'levels',
            'positions'
        ));
    }

    public function store(Request $request)
    {
        $this->authorizeManagerGeneralAffair();

        $request->validate([
            'tahun' => ['required', 'integer', 'digits:4'],
            'employee_status' => ['required', 'string'],
            'level_id' => ['required', 'exists:levels,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'rate' => ['required', 'numeric', 'min:0'],
        ]);

        OvertimeRate::create([
            'tahun' => $request->tahun,
            'employee_status' => $request->employee_status,
            'level_id' => $request->level_id,
            'position_id' => $request->position_id,
            'rate' => $request->rate,
        ]);

        return redirect()
            ->route('manager.overtime-rate.index')
            ->with('success', 'Overtime Rate berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $this->authorizeManagerGeneralAffair();

        $overtimeRate = OvertimeRate::with([
            'level',
            'position',
        ])->findOrFail($id);

        $levels = Level::orderBy('name')->get();
        $positions = Position::orderBy('name')->get();

        return view('pages.manager.overtime-rate.edit', compact(
            'overtimeRate',
            'levels',
            'positions'
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeManagerGeneralAffair();

        $request->validate([
            'tahun' => ['required', 'integer', 'digits:4'],
            'employee_status' => ['required', 'string'],
            'level_id' => ['required', 'exists:levels,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'rate' => ['required', 'numeric', 'min:0'],
        ]);

        $overtimeRate = OvertimeRate::findOrFail($id);

        $overtimeRate->update([
            'tahun' => $request->tahun,
            'employee_status' => $request->employee_status,
            'level_id' => $request->level_id,
            'position_id' => $request->position_id,
            'rate' => $request->rate,
        ]);

        return redirect()
            ->route('manager.overtime-rate.index')
            ->with('success', 'Overtime Rate berhasil diupdate.');
    }

    public function destroy($id)
    {
        $this->authorizeManagerGeneralAffair();

        $overtimeRate = OvertimeRate::findOrFail($id);

        $overtimeRate->delete();

        return redirect()
            ->route('manager.overtime-rate.index')
            ->with('success', 'Overtime Rate berhasil dihapus.');
    }
}