<?php

namespace App\Imports;

use App\Models\DailyActivity;
use App\Models\DailyActivityDetail;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DailyActivityRevisionImport implements ToCollection, WithHeadingRow
{
    protected $tanggal;
    protected $costCenterId;

    public function __construct(
        string $tanggal,
        ?int $costCenterId = null,
    ) {
        $this->tanggal = $tanggal;
        $this->costCenterId = $costCenterId;
    }

    public function headingRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        // if ($rows->isEmpty()) {
        //     throw new \Exception('File Excel kosong.');
        // }

        // $requiredHeaders = [
        //     'products',
        //     // 'ouptut_pac',
        //     'total_kg',
        // ];

        // $headers = array_keys($rows->first()->toArray());

        // foreach ($requiredHeaders as $header) {
        //     if (! in_array($header, $headers)) {
        //         throw new \Exception(
        //             "Format Excel tidak sesuai. Kolom '{$header}' tidak ditemukan."
        //         );
        //     }
        // }

        $user = Auth::user();

        if (! $user) {
            throw new \Exception('User tidak terautentikasi.');
        }

        // Buat 1 record DailyActivity sebagai parent untuk semua detail di file ini
        $dailyActivity = DailyActivity::create([
            'tanggal'        => $this->tanggal,
            'department_id'  => $user->department_id,
            'cost_center_id' => $this->costCenterId,
            'input_by'       => $user->id,
        ]);

        foreach ($rows as $row) {
            $namaMaterial = trim($row['products'] ?? '');

            if (empty($namaMaterial)) {
                continue;
            }

            // $outputPac = $row['ouptut_pac'] ?? 0;
            $totalKg  = $row['total_kg'] ?? 0;

            $product = Product::where('material_name', $namaMaterial)->first();

            if (!$product) {
                continue;
            }

            DailyActivityDetail::updateOrCreate(
                [
                    'daily_activity_id' => $dailyActivity->id,
                    'product_id'        => $product->id,
                ],
                [
                    'total_kg'  => $totalKg,
                    'harga_per_kg' => $product->harga_per_kg,
                    'total_harga' => $product->harga_per_kg * $totalKg,
                ]
            );
        }
    }
}