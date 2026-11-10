<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Jabatan;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeExport
{
    protected ?int $appId;
    protected ?string $jabatanId;
    protected ?string $search;

    public function __construct(?int $appId = null, ?string $jabatanId = null, ?string $search = null)
    {
        $this->appId = $appId;
        $this->jabatanId = $jabatanId;
        $this->search = $search;
    }

    /**
     * Generate the Excel file and stream download.
     */
    public function export(): StreamedResponse
    {
        $query = Employee::with(['jabatan', 'user'])
            ->latest()
            ->when($this->appId, function ($query) {
                $query->where('app_id', $this->appId);
            })
            ->when($this->jabatanId, function ($query) {
                if ($this->jabatanId === 'none') {
                    $query->whereNull('jabatan_id');
                } else {
                    $query->where('jabatan_id', $this->jabatanId);
                }
            })
            ->when($this->search, function ($query) {
                $search = $this->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('alamat', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($uq) use ($search) {
                            $uq->where('nomor', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        })
                        ->orWhereHas('jabatan', function ($jq) use ($search) {
                            $jq->where('name', 'like', "%{$search}%");
                        });
                });
            });

        $employees = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Karyawan');

        // Document Properties
        $spreadsheet->getProperties()
            ->setCreator('Breskul')
            ->setTitle('Data Master Karyawan')
            ->setSubject('Data Master Karyawan');

        // Title Block
        $sheet->setCellValue('A1', 'DATA MASTER KARYAWAN');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('FF15803D'));

        // Filter info
        $filterInfo = 'Semua Jabatan';
        if ($this->jabatanId === 'none') {
            $filterInfo = 'Tanpa Jabatan';
        } elseif ($this->jabatanId) {
            $jabatan = Jabatan::find($this->jabatanId);
            if ($jabatan) {
                $filterInfo = $jabatan->name;
            }
        }

        $subtitle = 'Tanggal Ekspor: ' . date('d-m-Y H:i') . ' | Filter Jabatan: ' . $filterInfo;
        if (!empty($this->search)) {
            $subtitle .= ' | Pencarian: "' . $this->search . '"';
        }
        $subtitle .= ' | Total Data: ' . $employees->count() . ' Karyawan';

        $sheet->setCellValue('A2', $subtitle);
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->setColor(new Color('FF4B5563'));

        // Table Header
        $headers = [
            'A' => 'No',
            'B' => 'Nama Karyawan',
            'C' => 'Jenis Kelamin',
            'D' => 'Jabatan',
            'E' => 'Role',
            'F' => 'No. Handphone',
            'G' => 'Email',
            'H' => 'Username',
            'I' => 'Alamat',
            'J' => 'Status Akun',
        ];

        $headerRow = 4;
        foreach ($headers as $col => $title) {
            $sheet->setCellValue($col . $headerRow, $title);
        }

        // Header Styling
        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size'  => 10,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF16A34A'], // Breskul Theme Green
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle("A{$headerRow}:J{$headerRow}")->applyFromArray($headerStyle);
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        // Data Rows
        $currentRow = 5;
        if ($employees->isEmpty()) {
            $sheet->setCellValue('A5', 'Tidak ada data karyawan');
            $sheet->mergeCells('A5:J5');
            $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getRowDimension(5)->setRowHeight(22);
            $lastRow = 5;
        } else {
            foreach ($employees as $index => $emp) {
                $roleLabel = '-';
                if ($emp->user) {
                    if ($emp->user->role == 3) {
                        $roleLabel = 'Guru';
                    } elseif ($emp->user->role == 4) {
                        $roleLabel = 'Petugas';
                    } else {
                        $roleLabel = $emp->user->roles ?? '-';
                    }
                }

                $sheet->setCellValue('A' . $currentRow, $index + 1);
                $sheet->setCellValue('B' . $currentRow, $emp->name);
                $sheet->setCellValue('C' . $currentRow, $emp->jenis ?? ($emp->jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan'));
                $sheet->setCellValue('D' . $currentRow, $emp->jabatan->name ?? '-');
                $sheet->setCellValue('E' . $currentRow, $roleLabel);

                // Phone number explicit string to preserve leading zero
                $phone = $emp->user->nomor ?? '-';
                $sheet->setCellValueExplicit('F' . $currentRow, $phone, DataType::TYPE_STRING);

                $sheet->setCellValue('G' . $currentRow, $emp->user->email ?? '-');
                $sheet->setCellValue('H' . $currentRow, $emp->user->username ?? '-');
                $sheet->setCellValue('I' . $currentRow, $emp->alamat ?? '-');
                $sheet->setCellValue('J' . $currentRow, $emp->user ? ($emp->user->status == 1 ? 'Aktif' : 'Nonaktif') : '-');

                // Alignments
                $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('H' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('I' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $sheet->getStyle('J' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Alternating zebra row background
                if ($index % 2 == 1) {
                    $sheet->getStyle("A{$currentRow}:J{$currentRow}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('FFF9FAFB');
                }

                $sheet->getRowDimension($currentRow)->setRowHeight(20);
                $currentRow++;
            }
            $lastRow = $currentRow - 1;
        }

        // Table Borders
        $sheet->getStyle("A{$headerRow}:J{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFD1D5DB'],
                ],
            ],
        ]);

        // AutoFilter and Freeze Pane
        $sheet->setAutoFilter("A{$headerRow}:J{$lastRow}");
        $sheet->freezePane('A5');

        // Column Dimensions
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(22);
        $sheet->getColumnDimension('E')->setWidth(14);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(26);
        $sheet->getColumnDimension('H')->setWidth(18);
        $sheet->getColumnDimension('I')->setWidth(32);
        $sheet->getColumnDimension('J')->setWidth(14);

        $fileName = 'data_karyawan_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Cache-Control'       => 'max-age=0',
        ]);
    }
}
