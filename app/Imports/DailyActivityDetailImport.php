<?php

namespace App\Imports;

use App\Models\CostCenter;
use App\Models\DailyActivity;
use App\Models\DailyActivityDetail;
use App\Models\Employee;
use App\Models\Product;
use App\Models\PsGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DailyActivityDetailImport implements ToCollection, WithHeadingRow
{
     protected string $tanggal;
    protected CostCenter $costCenter;
    protected PsGroup $psGroup;
    protected int $departmentId;
    protected int $inputBy;
 
    /** @var array<int, string> */
    public array $errors = [];
 
    public int $headersCreated = 0;
    public int $headersUpdated = 0;
    public int $detailsCreated = 0;
    public int $detailsUpdated = 0;
 
    public function __construct(
        string $tanggal,
        CostCenter $costCenter,
        PsGroup $psGroup,
        int $departmentId,
        int $inputBy
    ) {
        $this->tanggal      = $tanggal;
        $this->costCenter   = $costCenter;
        $this->psGroup      = $psGroup;
        $this->departmentId = $departmentId;
        $this->inputBy      = $inputBy;
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
 
        $requiredHeaders = ['nama', 'kode_material', 'harga', 'output_kg', 'lama_packing'];
        $headers = array_keys($rows->first()->toArray());
 
        foreach ($requiredHeaders as $header) {
            if (!in_array($header, $headers)) {
                throw new \Exception("Format Excel tidak sesuai. Kolom '{$header}' tidak ditemukan.");
            }
        }
 
        $employeeCache = [];
        $headerCache   = [];
 
        DB::transaction(function () use ($rows, &$employeeCache, &$headerCache) {
            foreach ($rows as $index => $row) {
                $rowNum = $index + $this->headingRow() + 1;
 
                $namaAsli     = trim($row['nama'] ?? '');
                $materialName = trim($row['kode_material'] ?? '');

                $qtyRaw       = $row['output_kg'] ?? null;
                $lamaRaw      = $row['lama_packing'] ?? null;
                $hargaRaw     = $row['harga'] ?? null;

                $hargaRaw = $row['harga'] ?? null;

                if (is_string($hargaRaw)) {
                    $hargaRaw = trim($hargaRaw);
                    $hargaRaw = str_replace(',', '.', $hargaRaw);
                }

                $harga = is_numeric($hargaRaw)
                    ? round((float) $hargaRaw, 2)
                    : null;
                
                if (empty($namaAsli) && empty($materialName) && $qtyRaw === '') {
                    continue;
                }
 
                if (empty($namaAsli)) {
                    $this->errors[] = "Baris {$rowNum}: Nama karyawan kosong.";
                    continue;
                }
 
                if (empty($materialName)) {
                    $this->errors[] = "Baris {$rowNum}: Kode Material kosong (karyawan '{$namaAsli}').";
                    continue;
                }
 
                $employeeKey = strtoupper($namaAsli);
                if (!array_key_exists($employeeKey, $employeeCache)) {
                    $employeeCache[$employeeKey] = Employee::whereRaw(
                        'UPPER(TRIM(name)) = ?',
                        [$employeeKey]
                    )->first();
                }
                $employee = $employeeCache[$employeeKey];
 
                if (!$employee) {
                    $this->errors[] = "Baris {$rowNum}: Karyawan '{$namaAsli}' tidak ditemukan di master employee.";
                    continue;
                }
 
                $product = Product::where('cost_center_id', $this->costCenter->id)
                    ->whereRaw(
                        'LOWER(TRIM(material_code)) = ?',
                        [strtolower($materialName)]
                    )
                    ->where('harga_per_kg', $harga)
                    ->first();

                if (!$product) {
                    $this->errors[] = "Baris {$rowNum}: Produk '{$materialName}' tidak ditemukan di cost center {$this->costCenter->name}.";
                    continue;
                }

                if ($harga === null) {
                    $this->errors[] = "Baris {$rowNum}: Harga '{$hargaRaw}' tidak valid untuk produk '{$materialName}'.";
                    continue;
                }

                if ((float) $product->harga_per_kg !== $harga) {
                    $this->errors[] = "Baris {$rowNum}: Harga produk '{$materialName}' tidak cocok. Excel: {$harga}, Master: {$product->harga_per_kg}.";
                    continue;
                }

                $outputKg = (float) $qtyRaw;
 
                $lamaPacking = (is_numeric($lamaRaw) && $lamaRaw !== '') ? (float) $lamaRaw : 0;
 
                $hargaPerKg = $product->harga_per_kg;
 
                $totalHarga = round($outputKg * $hargaPerKg, 2);
 
                if (!isset($headerCache[$employee->id])) {
                    $header = DailyActivity::updateOrCreate(
                        [
                            'tanggal'        => $this->tanggal,
                            'employee_id'    => $employee->id,
                            'cost_center_id' => $this->costCenter->id,
                        ],
                        [
                            'department_id' => $this->departmentId,
                            'ps_group_id'   => $this->psGroup->id,
                            'input_by'      => $this->inputBy,
                        ]
                    );
 
                    $header->wasRecentlyCreated ? $this->headersCreated++ : $this->headersUpdated++;
                    $headerCache[$employee->id] = $header;
                }
 
                $header = $headerCache[$employee->id];
 
                $detail = DailyActivityDetail::create([
                    'daily_activity_id' => $header->id,
                    'product_id'        => $product->id,
                    'total_kg'          => $outputKg,
                    'harga_per_kg'      => $hargaPerKg,
                    'total_harga'       => $totalHarga,
                    'lama_packing'      => $lamaPacking,
                ]);

                $this->detailsCreated++;
            }
        });
    }
}