<?php

namespace App\Imports;

use App\Models\CostCenter;
use App\Models\Product;
use App\Models\ProductGroup;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductImport implements ToCollection, WithHeadingRow
{
    protected $costCenter;

    public function __construct(CostCenter $costCenter)
    {
        $this->costCenter = $costCenter;
    }

    public function headingRow(): int
    {
        return 3;
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            throw new \Exception('File Excel kosong.');
        }

        $requiredHeaders = [
            'material_code',
            'products',
            'group',
            'harga_per_kg',
        ];

        $headers = array_keys($rows->first()->toArray());

        foreach ($requiredHeaders as $header) {
            if (!in_array($header, $headers)) {
                throw new \Exception(
                    "Format Excel tidak sesuai. Kolom '{$header}' tidak ditemukan."
                );
            }
        }

        foreach ($rows as $row) {
            $materialCode     = trim($row['material_code'] ?? '');
            $productGroupName = trim($row['group'] ?? '');
            $materialName     = trim($row['products'] ?? '');
            $hargaPerKg       = trim($row['harga_per_kg'] ?? '');

            // skip kalau material_code DAN products dua-duanya kosong
            if (empty($materialCode) && empty($materialName)) {
                continue;
            }

            $productGroup = ProductGroup::where('name', $productGroupName)->first();

            $data = [
                'material_code'    => $materialCode ?: null,
                'material_name'    => $materialName,
                'harga_per_kg'     => $hargaPerKg ? $hargaPerKg : 0,
                'product_group_id' => $productGroup?->id,
                'department_id'    => auth()->user()->department_id,
                'cost_center_id'   => $this->costCenter->id,
            ];

            if (!empty($materialCode)) {
                Product::updateOrCreate(
                    [
                        'cost_center_id' => $this->costCenter->id,
                        'material_name' => $materialName,
                        'product_group_id' => $productGroup?->id,
                    ],
                    $data
                );
            } else {
                Product::updateOrCreate(
                    [
                        'cost_center_id' => $this->costCenter->id,
                        'material_name' => $materialName,
                        'product_group_id' => $productGroup?->id,       
                    ],
                    $data
                );
            }
        }
    }
}
