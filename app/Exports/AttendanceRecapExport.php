<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;

use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Illuminate\Support\Collection;

class AttendanceRecapExport implements FromCollection, WithTitle, WithStyles, WithEvents, WithCustomStartCell
{
    protected array $recapData;

    private const COLOR_PRIMARY_BLUE = '1E40AF';
    private const COLOR_SECONDARY_BLUE = '3B82F6';
    private const COLOR_LIGHT_GRAY_BG = 'F8FAFC';
    private const COLOR_LIGHTER_GRAY_BG = 'F3F4F6';
    private const COLOR_BORDER = 'E2E8F0';
    private const COLOR_TEXT_DARK = '1F2937';
    private const COLOR_TEXT_GRAY = '6B7280';
    private const COLOR_WHITE = 'FFFFFF';

    public function __construct(array $recapData)
    {
        $this->recapData = $recapData;
    }

    public function collection(): Collection
    {
        $rows = new Collection();

        // Metadata
        $rows->push(['Tanggal', ':', $this->recapData['date'] ?? '-']);
        $rows->push(['Kelas', ':', $this->recapData['classroom']['name'] ?? '-']);
        $rows->push(['Tahun Ajaran', ':', $this->recapData['tahun_ajaran'] ?? '-']);
        $rows->push(['Total Siswa', ':', (string)($this->recapData['total_students'] ?? 0)]);
        $rows->push(['']);

        // Summary
        $rows->push(['RINGKASAN KEHADIRAN']);
        $summary = $this->recapData['attendance_summary'] ?? [];
        $rows->push(['Hadir', $summary['present'] ?? 0]);
        $rows->push(['Terlambat', $summary['late'] ?? 0]);
        $rows->push(['Sakit', $summary['sick'] ?? 0]);
        $rows->push(['Izin', $summary['permission'] ?? 0]);
        $rows->push(['Alpha', $summary['alpha'] ?? 0]);
        $rows->push(['']);

        // Attendance List
        $rows->push(['DAFTAR ABSENSI SISWA']);
        $rows->push(['No', 'NISN', 'Nama Siswa', 'Status Kehadiran']);

        $no = 1;
        foreach ($this->recapData['students'] ?? [] as $student) {
            $rows->push([
                $no++,
                $student['nisn'] ?? '-',
                $student['student_name'] ?? '-',
                $this->translateStatus($student['status'] ?? 'absent')
            ]);
        }

        return $rows;
    }

    protected function translateStatus(string $status): string
    {
        return match ($status) {
            'present' => 'Hadir',
            'late' => 'Terlambat',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            'alpha' => 'Alpha',
            default => 'Alpha'
        };
    }

    public function title(): string
    {
        return 'Rekap Absensi';
    }

    public function startCell(): string
    {
        return 'A7';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'name' => 'Calibri',
                    'size' => 11,
                    'color' => ['rgb' => self::COLOR_TEXT_DARK]
                ],
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->setShowGridlines(false);
                $sheet->getPageSetup()->setOrientation('portrait');

                $this->styleMainHeader($sheet);
                $this->styleMetadataSection($sheet);
                $this->styleSummarySection($sheet);
                $this->styleAttendanceTable($sheet);
                $this->optimizeColumnWidths($sheet);
                $this->configurePrintSettings($sheet);
            }
        ];
    }

    private function styleMainHeader(Worksheet $sheet): void
    {
        $sheet->setCellValue('A1', 'REKAP ABSENSI KELAS');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 18,
                'bold' => true,
                'color' => ['rgb' => self::COLOR_WHITE]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => false,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_PRIMARY_BLUE]
            ]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(32);

        $sheet->setCellValue('A2', strtoupper($this->recapData['classroom']['name'] ?? 'KELAS TIDAK TERSEDIA'));
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 14,
                'bold' => true,
                'color' => ['rgb' => self::COLOR_WHITE]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_SECONDARY_BLUE]
            ]
        ]);
        $sheet->getRowDimension(2)->setRowHeight(28);

        $sheet->setCellValue('A3', 'Tanggal: ' . ($this->recapData['date'] ?? 'N/A'));
        $sheet->mergeCells('A3:D3');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'color' => ['rgb' => self::COLOR_WHITE]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_SECONDARY_BLUE]
            ]
        ]);
        $sheet->getRowDimension(3)->setRowHeight(24);

        $sheet->mergeCells('A4:D4');
        $sheet->getStyle('A4:D4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => self::COLOR_LIGHT_GRAY_BG]
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => self::COLOR_SECONDARY_BLUE]
                ]
            ]
        ]);
        $sheet->getRowDimension(4)->setRowHeight(8);
        $sheet->getRowDimension(5)->setRowHeight(4);
        $sheet->getRowDimension(6)->setRowHeight(4);
    }

    private function styleMetadataSection(Worksheet $sheet): void
    {
        $startRow = 7;
        $endRow = 10;

        for ($row = $startRow; $row <= $endRow; $row++) {
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 11,
                    'color' => ['rgb' => self::COLOR_TEXT_GRAY]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::COLOR_LIGHT_GRAY_BG]
                ]
            ]);

            $sheet->getStyle("B{$row}")->applyFromArray([
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::COLOR_LIGHT_GRAY_BG]
                ]
            ]);

            $sheet->getStyle("C{$row}:D{$row}")->applyFromArray([
                'font' => [
                    'size' => 11,
                    'color' => ['rgb' => self::COLOR_TEXT_DARK]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::COLOR_WHITE]
                ],
                'borders' => [
                    'bottom' => [
                        'borderStyle' => Border::BORDER_HAIR,
                        'color' => ['rgb' => self::COLOR_BORDER]
                    ]
                ]
            ]);

            $sheet->getRowDimension($row)->setRowHeight(22);
        }

        $sheet->getStyle('A10:D10')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A10:D10')->getBorders()->getBottom()->setColor(new Color(self::COLOR_BORDER));
    }

    private function styleSummarySection(Worksheet $sheet): void
    {
        $sheet->setCellValue('A12', 'RINGKASAN KEHADIRAN');
        $sheet->mergeCells('A12:D12');
        $sheet->getStyle('A12')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 12,
                'bold' => true,
                'color' => ['rgb' => self::COLOR_SECONDARY_BLUE]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => self::COLOR_SECONDARY_BLUE]
                ]
            ]
        ]);
        $sheet->getRowDimension(12)->setRowHeight(24);

        $summaryLabels = ['Hadir', 'Terlambat', 'Sakit', 'Izin', 'Alpha'];
        for ($i = 0; $i < 5; $i++) {
            $row = 13 + $i;
            $summary = $this->recapData['attendance_summary'] ?? [];
            $statusKeys = ['present', 'late', 'sick', 'permission', 'alpha'];
            $value = $summary[$statusKeys[$i]] ?? 0;

            $sheet->setCellValue("A{$row}", $summaryLabels[$i]);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => [
                    'size' => 11,
                    'color' => ['rgb' => self::COLOR_TEXT_DARK]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::COLOR_LIGHTER_GRAY_BG]
                ],
                'borders' => [
                    'left' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDER]],
                    'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDER]],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDER]]
                ]
            ]);

            $sheet->setCellValue("B{$row}", $value);
            $sheet->getStyle("B{$row}")->applyFromArray([
                'font' => [
                    'size' => 11,
                    'bold' => true,
                    'color' => ['rgb' => self::COLOR_PRIMARY_BLUE]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'numberFormat' => ['formatCode' => '0']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::COLOR_LIGHTER_GRAY_BG]
                ],
                'borders' => [
                    'right' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDER]],
                    'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDER]],
                    'bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDER]]
                ]
            ]);

            $sheet->getStyle("C{$row}:D{$row}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::COLOR_WHITE]
                ]
            ]);

            $sheet->getRowDimension($row)->setRowHeight(20);
        }
    }

    private function styleAttendanceTable(Worksheet $sheet): void
    {
        $sheet->setCellValue('A19', 'DAFTAR ABSENSI SISWA');
        $sheet->mergeCells('A19:D19');
        $sheet->getStyle('A19')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'size' => 12,
                'bold' => true,
                'color' => ['rgb' => self::COLOR_SECONDARY_BLUE]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => Border::BORDER_MEDIUM,
                    'color' => ['rgb' => self::COLOR_SECONDARY_BLUE]
                ]
            ]
        ]);
        $sheet->getRowDimension(19)->setRowHeight(24);

        $headerRow = 20;
        $headers = ['No', 'NISN', 'Nama Siswa', 'Status Kehadiran'];

        foreach ($headers as $col => $header) {
            $colLetter = chr(65 + $col);
            $sheet->setCellValue("{$colLetter}{$headerRow}", $header);
            $sheet->getStyle("{$colLetter}{$headerRow}")->applyFromArray([
                'font' => [
                    'name' => 'Calibri',
                    'size' => 11,
                    'bold' => true,
                    'color' => ['rgb' => self::COLOR_WHITE]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => self::COLOR_PRIMARY_BLUE]
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => self::COLOR_PRIMARY_BLUE]
                    ]
                ]
            ]);
        }
        $sheet->getRowDimension($headerRow)->setRowHeight(24);

        $highestRow = $sheet->getHighestRow();
        $dataStartRow = $headerRow + 1;

        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            $isEvenRow = ($row - $dataStartRow) % 2 == 0;
            $backgroundColor = $isEvenRow ? self::COLOR_WHITE : self::COLOR_LIGHTER_GRAY_BG;

            for ($col = 0; $col < 4; $col++) {
                $colLetter = chr(65 + $col);
                $cellRef = "{$colLetter}{$row}";

                $sheet->getStyle($cellRef)->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => self::COLOR_TEXT_DARK]
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $backgroundColor]
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => self::COLOR_BORDER]
                        ]
                    ]
                ]);
            }

            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        if ($highestRow > $dataStartRow) {
            $sheet->getStyle("A{$headerRow}:D{$highestRow}")->getBorders()->getOutline()
                ->setBorderStyle(Border::BORDER_MEDIUM)
                ->setColor(new Color(self::COLOR_PRIMARY_BLUE));
        }
    }

    private function optimizeColumnWidths(Worksheet $sheet): void
    {
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(15);

        // Dynamic width for Column C (Student Name)
        $maxNameLength = 0;
        foreach ($this->recapData['students'] ?? [] as $student) {
            $nameLength = strlen($student['student_name'] ?? '');
            if ($nameLength > $maxNameLength) {
                $maxNameLength = $nameLength;
            }
        }

        $widthC = 20; // Default
        if ($maxNameLength > 30) {
            $widthC = 30;
        } elseif ($maxNameLength > 20) {
            $widthC = 25;
        }

        $sheet->getColumnDimension('C')->setWidth($widthC);
        $sheet->getColumnDimension('D')->setWidth(19);
    }

    private function configurePrintSettings(Worksheet $sheet): void
    {
        $sheet->getPageSetup()->setPrintArea('A1:D' . $sheet->getHighestRow());

        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageSetup()->setFitToWidth(1);

        $sheet->getPageMargins()->setLeft(0.5);
        $sheet->getPageMargins()->setRight(0.5);
        $sheet->getPageMargins()->setTop(0.75);
        $sheet->getPageMargins()->setBottom(0.75);

        $sheet->getHeaderFooter()->setOddHeader('&CAttendance Report - &D');
        $sheet->getHeaderFooter()->setOddFooter('&LPage &P of &N&R' . date('Y-m-d H:i:s'));

        $sheet->getPageSetup()->setOrientation('portrait');
        $sheet->getPageSetup()->setPaperSize(0);
        $sheet->setShowGridlines(false);

        $sheet->freezePane('A21');
    }
}
