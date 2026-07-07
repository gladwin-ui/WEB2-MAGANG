<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Carbon\Carbon;

class LaporanUmumExport
{
    protected $bugs;
    protected $startRow = 7;

    public function __construct($bugs)
    {
        $this->bugs = $bugs;
    }

    public function generate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Umum');

        // 1. Embed Logo Hariff Defense
        $logoPath = public_path('LOGO LOGO LAGI.png');
        if (!file_exists($logoPath)) {
            $logoPath = public_path('logo-hariff.jpg');
        }
        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo Hariff Defense');
            $drawing->setDescription('Logo Hariff Defense');
            $drawing->setPath($logoPath);
            $drawing->setHeight(55);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(5);
            $drawing->setWorksheet($sheet);
        }

        // 2. Company Name & Sub-name
        $sheet->setCellValue('C1', 'HARIFF DEFENSE');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(18)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1A3D63'));
        
        $sheet->setCellValue('C2', 'ManufakTrack — Sistem Pelacakan Manufaktur');
        $sheet->getStyle('C2')->getFont()->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4A7FA7'));

        // 3. Report Title & Print Date
        $sheet->setCellValue('A4', 'LAPORAN UMUM PELACAKAN BUG MANUFAKTUR');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1A3D63'));

        $sheet->setCellValue('A5', 'Tanggal Cetak: ' . now()->translatedFormat('d F Y, H:i'));
        $sheet->getStyle('A5')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('475569'));

        // 4. Table Headers
        $headers = [
            'ID Bug', 'Project', 'Title', 'Severity', 'SN Code Snapshot',
            'Reporter Type', 'Description', 'Version', 'Environment',
            'Root Cause', 'Repair Action', 'Rework?', 'Status',
            'Reported By', 'Fixed By', 'Closed At', 'Created At',
            'Sentiment Label', 'Sentiment Score',
            'Severity Recommended', 'Severity Rec Reason'
        ];

        $headerRow = $this->startRow;
        $lastColIndex = count($headers);
        $lastColLetter = Coordinate::stringFromColumnIndex($lastColIndex);

        foreach ($headers as $colIdx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $cellCoord = $colLetter . $headerRow;
            $sheet->setCellValue($cellCoord, $header);

            if ($header === 'ID Bug' || $header === 'SN Code Snapshot') {
                $sheet->getStyle($colLetter)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            }
        }

        // Style Header Row
        $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$headerRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Inter',
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1A3D63'], // Navy Hariff Defense
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // 5. Data Rows
        $rowIndex = $headerRow + 1;
        foreach ($this->bugs as $bug) {
            $data = [
                $bug->id,
                $bug->project?->name ?? ($bug->project_id ? 'Project #' . $bug->project_id : 'Tanpa Proyek'),
                $bug->title,
                $bug->severity,
                $bug->sn_code_snapshot,
                $bug->reporter_type,
                $bug->description,
                $bug->product_version,
                $bug->environment,
                $bug->root_cause,
                $bug->repair_action,
                $bug->is_rework ? 'Yes' : 'No',
                $bug->status,
                $bug->reported_by ?? 'System',
                $bug->fixed_by ?? '',
                $bug->closed_at ? Carbon::parse($bug->closed_at)->format('Y-m-d H:i:s') : '',
                Carbon::parse($bug->created_at)->format('Y-m-d H:i:s'),
                $bug->sentiment_label,
                $bug->sentiment_score,
                $bug->severity_recommended,
                $bug->severity_recommendation_reason
            ];

            foreach ($data as $colIdx => $value) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
                $cellCoord = $colLetter . $rowIndex;

                if ($colIdx === 0 || $colIdx === 4) { // ID Bug, SN Code Snapshot
                    $sheet->setCellValueExplicit($cellCoord, (string)($value ?? ''), DataType::TYPE_STRING);
                    $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } else {
                    $sheet->setCellValue($cellCoord, $value ?? '');
                }
            }

            // Zebra striping for even rows
            if ($rowIndex % 2 === 0) {
                $sheet->getStyle("A{$rowIndex}:{$lastColLetter}{$rowIndex}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ],
                ]);
            }

            $rowIndex++;
        }

        $lastDataRow = $rowIndex - 1;
        if ($lastDataRow >= $headerRow + 1) {
            $sheet->getStyle("A" . ($headerRow + 1) . ":{$lastColLetter}{$lastDataRow}")->getFont()->setName('Inter')->setSize(9.5);
            
            $sheet->getStyle("A{$headerRow}:{$lastColLetter}{$lastDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D5E3F0'],
                    ],
                ],
            ]);
        }

        // Freeze table headers
        $sheet->freezePane('A' . ($headerRow + 1));

        // Auto-fit column widths
        for ($col = 1; $col <= $lastColIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        // Adjust row 1 & 2 heights for logo
        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(25);

        return $spreadsheet;
    }
}
