<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CorrectedDataExport implements FromCollection, WithEvents
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Konversi array ke Collection
        return collect($this->data);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Mendapatkan kolom tertinggi (misalnya 'AA', 'AB')
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                // Iterasi untuk semua kolom dan set auto size
                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
                }
            },
        ];
    }
}
