<?php

namespace App\Http\Controllers;

use App\Exports\CorrectedDataExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UploadController extends Controller
{

    public function viewupload()
    {
        return view('upload-view');
    }

    public function validateExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'sheet' => 'required'
        ]);


        dd($request->all());

        $file = $request->file('file');
        $data = Excel::toArray([], $file);

        $errors = $this->validateMandatoryColumns($data[$request->sheet]);

        if (!empty($errors)) {
            return redirect()->back()->with('errors', $errors);
        }

        $modifiedData = $this->modifyData($data[$request->sheet]);

        return Excel::download(new CorrectedDataExport($modifiedData), 'modified_data.xlsx');

        return redirect()->back()->with('success', 'File Excel valid dan berhasil diproses!');
    }


    private function validateMandatoryColumns(array $rows): array
    {
        $errors = [];
        $mandatoryColumns = ['NIK SAI', 'NAMA LENGKAP'];

        // Ambil header dan ubah ke huruf besar
        $headers = array_map('strtoupper', $rows[0]);
        foreach ($mandatoryColumns as $column) {
            if (!in_array(strtoupper($column), $headers)) {
                return ["Kolom '$column' tidak ditemukan di file Excel."];
            }
        }

        // Ambil indeks kolom wajib
        $columnIndices = array_map(fn ($col) => array_search(strtoupper($col), $headers), $mandatoryColumns);

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === 0) continue; // Lewati header

            // Periksa apakah baris sepenuhnya kosong
            if (!array_filter($row)) {
                continue;
            }

            // Validasi kolom wajib pada baris
            foreach ($columnIndices as $key => $colIndex) {
                $value = $row[$colIndex] ?? null;
                if (is_null($value) || trim((string)$value) === '') {
                    $errors[] = "Data kosong pada baris " . ($rowIndex + 1) . ", kolom '{$mandatoryColumns[$key]}'.";
                }
            }
        }

        return $errors;
    }


    private function modifyData(array $rows): array
    {
        $headers = array_map('strtoupper', $rows[0]);
        $kategoriStatusIndex = array_search('KATEGORI STATUS PKWT', $headers);
        $statusIndex = array_search('STATUS PKWT', $headers);
        $batchIndex = array_search('BATCH', $headers);
        $mappingYcIndex = array_search('MAPPING YC', $headers);
        $golonganIndex = array_search('GOLONGAN', $headers);
        $lokasiIndex = array_search('LOKASI', $headers);
        $periodeUpdateIndex = array_search('PERIODE UPDATE', $headers);

        if (
            $kategoriStatusIndex === false ||
            $statusIndex === false ||
            $batchIndex === false ||
            $mappingYcIndex === false ||
            $golonganIndex === false ||
            $lokasiIndex === false ||
            $periodeUpdateIndex === false
        ) {
            throw new \Exception("Kolom 'KATEGORI STATUS PKWT', 'STATUS PKWT', 'BATCH', 'MAPPING YC', 'GOLONGAN', 'LOKASI', atau 'PERIODE UPDATE's tidak ditemukan dalam file.");
        }

        foreach ($rows as $rowIndex => &$row) {
            if ($rowIndex === 0) continue;

            if (!array_filter($row)) {
                continue;
            }

            $kategoriStatus = $row[$kategoriStatusIndex] ?? null;
            if (is_null($kategoriStatus)) {
                $row[$kategoriStatusIndex] = 'TETAP';
                $row[$statusIndex] = 'PKWTT';
            } else {
                $kategoriStatus = strtoupper(trim((string)$kategoriStatus));
                if ($kategoriStatus === 'TETAP') {
                    $row[$statusIndex] = 'PKWTT';
                } else {
                    $row[$statusIndex] = 'PKWT';
                }
            }

            if ($row[$batchIndex] === '' || $row[$batchIndex] === null || $row[$batchIndex] === 0) {
                $row[$batchIndex] = '0';
            }

            $mappingYc = $row[$mappingYcIndex] ?? null;
            if (strtoupper(trim((string)$mappingYc)) === 'DL') {
                $row[$mappingYcIndex] = 'DIRECT LABOUR';
            }

            $golongan = $row[$golonganIndex] ?? null;
            if (is_numeric($golongan)) {
                $row[$golonganIndex] = $this->convertToRoman((int)$golongan);
            }

            $lokasi = strtoupper(trim((string)($row[$lokasiIndex] ?? '')));
            if ($lokasi !== 'SAI B' && $lokasi !== 'SAI T') {
                $row[$lokasiIndex] = 'SAI T';
            }

            $periodeUpdate = $row[$periodeUpdateIndex] ?? null;
            if (!empty($periodeUpdate)) {
                if (is_numeric($periodeUpdate)) {
                    // Jika berupa angka serial, konversikan ke format 'YYYY-MM-DD'
                    $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($periodeUpdate);
                    $row[$periodeUpdateIndex] = $date->format('Y-d-01'); // Paksa menjadi tanggal 1
                } else {
                    // Jika berupa string, gunakan format d/m/y
                    $date = \DateTime::createFromFormat('d/m/y', $periodeUpdate);
                    if ($date !== false) {
                        $row[$periodeUpdateIndex] = $date->format('Y-d-01'); // Paksa menjadi tanggal 1
                    } else {
                        // Jika format tidak valid, tetap kosong atau tambahkan logika default
                        $row[$periodeUpdateIndex] = null;
                    }
                }
            }
        }

        return $rows;
    }

    private function convertToRoman(int $number): string
    {
        $map = [
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV',
            1 => 'I'
        ];

        $result = '';
        foreach ($map as $value => $roman) {
            while ($number >= $value) {
                $result .= $roman;
                $number -= $value;
            }
        }

        return $result;
    }
}
