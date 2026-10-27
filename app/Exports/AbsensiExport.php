<?php

namespace App\Exports;

use App\Models\Present;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbsensiExport
{
    protected string $type;
    protected string $month;
    protected ?int $appId;

    public function __construct(string $type = 'karyawan', ?string $month = null, ?int $appId = null)
    {
        $this->type = in_array(strtolower($type ?? ''), ['murid', 'karyawan']) ? strtolower($type) : 'karyawan';
        $this->month = $month ?: date('Y-m');
        $this->appId = $appId;
    }

    /**
     * Generate the multi-sheet Excel file and stream download.
     */
    public function export(): StreamedResponse
    {
        try {
            $carbonMonth = Carbon::createFromFormat('Y-m', $this->month);
        } catch (\Exception $e) {
            $carbonMonth = Carbon::now();
        }

        $startOfMonth = $carbonMonth->copy()->startOfMonth()->startOfDay();
        $endOfMonth = $carbonMonth->copy()->endOfMonth()->endOfDay();

        // Query presents for the specified month, type, and app
        $presents = Present::query()
            ->whereBetween('waktu', [$startOfMonth, $endOfMonth])
            ->when($this->appId, function ($query) {
                $query->where('app', $this->appId);
            })
            ->when($this->type === 'murid', function ($query) {
                $query->whereNotNull('student_id')->with('murid');
            }, function ($query) {
                $query->whereNotNull('employee_id')->with('employee.jabatan');
            })
            ->orderBy('waktu', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();

        // Group by calendar date (Y-m-d) so they can be sorted chronologically
        $presentsByDate = $presents->groupBy(function ($item) {
            return Carbon::parse($item->waktu)->format('Y-m-d');
        })->sortKeys();

        if ($presentsByDate->isEmpty()) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Info');
            $sheet->setCellValue('A1', 'Tidak ada data absensi untuk periode ' . $carbonMonth->translatedFormat('F Y'));
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
            $sheet->getColumnDimension('A')->setWidth(40);
        } else {
            $sheetIndex = 0;

            foreach ($presentsByDate as $dateStr => $dayRecords) {
                $sheet = $sheetIndex === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
                $sheetIndex++;

                $dateCarbon = Carbon::parse($dateStr);
                // Sheet title format: 1-10-2026, 2-10-2026, etc.
                $sheetTitle = $dateCarbon->format('j-n-Y');
                $sheet->setTitle($sheetTitle);

                // Group presents on this date by person
                $groupedAttendees = $dayRecords->groupBy(function ($item) {
                    if ($this->type === 'murid') {
                        return $item->student_id ?: ('name_' . $item->name);
                    }
                    return $item->employee_id ?: ('name_' . $item->name);
                });

                $attendeeRows = [];
                foreach ($groupedAttendees as $key => $records) {
                    // Check-in record (earliest)
                    $masukRecord = $records->filter(function ($r) {
                        return strtolower(trim($r->status ?? '')) === 'masuk';
                    })->sortBy(function ($r) {
                        return Carbon::parse($r->waktu)->timestamp;
                    })->first();

                    // Check-out record (latest)
                    $pulangRecord = $records->filter(function ($r) {
                        return strtolower(trim($r->status ?? '')) === 'pulang';
                    })->sortByDesc(function ($r) {
                        return Carbon::parse($r->waktu)->timestamp;
                    })->first();

                    $firstRec = $records->first();
                    $name = $this->type === 'murid'
                        ? ($firstRec->murid?->name ?? $firstRec->name ?? '-')
                        : ($firstRec->employee?->name ?? $firstRec->name ?? '-');

                    if (empty(trim($name)) || $name === '-') {
                        $name = $firstRec->name ?? '-';
                    }

                    $jamMasuk = $masukRecord ? Carbon::parse($masukRecord->waktu)->format('H:i:s') : '-';
                    $jamPulang = $pulangRecord ? Carbon::parse($pulangRecord->waktu)->format('H:i:s') : '-';

                    $isLate = false;
                    $lateNote = null;
                    if ($masukRecord) {
                        $lateTime = $masukRecord->late_time;
                        if (!empty($lateTime)) {
                            $isLate = true;
                            $lateNote = $lateTime;
                        }
                    }

                    $attendeeRows[] = [
                        'name'      => $name,
                        'masuk'     => $jamMasuk,
                        'pulang'    => $jamPulang,
                        'is_late'   => $isLate,
                        'late_note' => $lateNote,
                    ];
                }

                // Sort attendees alphabetically by name
                usort($attendeeRows, function ($a, $b) {
                    return strcasecmp($a['name'], $b['name']);
                });

                // Header Columns
                $sheet->setCellValue('A1', 'No');
                $sheet->setCellValue('B1', 'Nama');
                $sheet->setCellValue('C1', 'Masuk');
                $sheet->setCellValue('D1', 'Pulang');

                // Header Styling
                $headerStyle = [
                    'font' => [
                        'bold'  => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                        'size'  => 11,
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF16A34A'], // App theme green
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                ];
                $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
                $sheet->getRowDimension(1)->setRowHeight(26);

                // Populate Data Rows
                $rowNum = 2;
                foreach ($attendeeRows as $idx => $row) {
                    $sheet->setCellValue('A' . $rowNum, $idx + 1);
                    $sheet->setCellValue('B' . $rowNum, $row['name']);
                    $sheet->setCellValue('C' . $rowNum, $row['masuk']);
                    $sheet->setCellValue('D' . $rowNum, $row['pulang']);

                    // Alignments
                    $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('B' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Block cell in red if late
                    if ($row['is_late']) {
                        $sheet->getStyle('C' . $rowNum)->applyFromArray([
                            'fill' => [
                                'fillType'   => Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFFF0000'], // Pure red
                            ],
                            'font' => [
                                'color' => ['argb' => 'FFFFFFFF'], // White text for clear contrast
                                'bold'  => true,
                            ],
                        ]);

                        if ($row['late_note']) {
                            $sheet->getComment('C' . $rowNum)
                                ->getText()
                                ->createTextRun($row['late_note']);
                        }
                    }

                    $sheet->getRowDimension($rowNum)->setRowHeight(22);
                    $rowNum++;
                }

                $lastRow = max(2, $rowNum - 1);

                // Thin borders around table
                $sheet->getStyle('A1:D' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FFD1D5DB'],
                        ],
                    ],
                ]);

                // Freeze header pane so row 1 stays visible when scrolling
                $sheet->freezePane('A2');

                // Column dimensions
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(18);
                $sheet->getColumnDimension('D')->setWidth(18);
            }

            // Set active sheet to the first sheet
            $spreadsheet->setActiveSheetIndex(0);
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = "absensi_{$this->type}_{$this->month}.xlsx";

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
