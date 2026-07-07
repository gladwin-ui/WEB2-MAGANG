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

class LaporanKhususExport
{
    protected $bugs;
    protected $reworkRates;
    protected $severityMix;
    protected $avgRework;
    protected $masalahTop5;
    protected $rootCauseTop5;
    protected $selectedProductId;
    protected $productName;

    public function __construct($bugs, $reworkRates, $severityMix, $avgRework, $masalahTop5, $rootCauseTop5, $selectedProductId, $productName)
    {
        $this->bugs = $bugs;
        $this->reworkRates = $reworkRates;
        $this->severityMix = $severityMix;
        $this->avgRework = $avgRework;
        $this->masalahTop5 = $masalahTop5;
        $this->rootCauseTop5 = $rootCauseTop5;
        $this->selectedProductId = $selectedProductId;
        $this->productName = $productName;
    }

    public function generate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // ==========================================
        // SHEET 1: RINGKASAN ANALISIS KHUSUS
        // ==========================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Analisis Khusus');
        $this->addFormalHeader($sheet1, 'LAPORAN KHUSUS — ANALISIS MASALAH & ROOT CAUSE (' . strtoupper($this->productName) . ')');

        $currentRow = 7;

        // --- TABEL 1: KPI & DISTRIBUSI SEVERITY ---
        $sheet1->setCellValue("A{$currentRow}", "KPI & DISTRIBUSI SEVERITY");
        $sheet1->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1A3D63'));
        $currentRow++;

        $kpiHeaders = ['Total Laporan Di-export', 'Rata-rata Rework Rate', 'Critical Severity', 'Major Severity', 'Minor Severity'];
        $this->writeTableHeader($sheet1, $currentRow, $kpiHeaders, 'E');
        $currentRow++;

        $sheet1->setCellValue("A{$currentRow}", count($this->bugs));
        $sheet1->setCellValue("B{$currentRow}", $this->avgRework . '%');
        $sheet1->setCellValue("C{$currentRow}", $this->severityMix['Critical'] ?? 0);
        $sheet1->setCellValue("D{$currentRow}", $this->severityMix['Major'] ?? 0);
        $sheet1->setCellValue("E{$currentRow}", $this->severityMix['Minor'] ?? 0);
        $this->styleTableBody($sheet1, $currentRow, $currentRow, 'E', false);
        $currentRow += 3;

        // --- TABEL 2: TOP 5 REWORK RATE PER PRODUK ---
        if ($this->reworkRates && count($this->reworkRates) > 0) {
            $sheet1->setCellValue("A{$currentRow}", "TOP 5 REWORK RATE ANTAR PRODUK");
            $sheet1->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1A3D63'));
            $currentRow++;

            $reworkHeaders = ['Peringkat', 'ID Project', 'Nama Produk / Project', 'Total Bug', 'Rework Count', 'Rework Rate (%)'];
            $this->writeTableHeader($sheet1, $currentRow, $reworkHeaders, 'F');
            $startTableRow = ++$currentRow;

            foreach ($this->reworkRates as $idx => $item) {
                $sheet1->setCellValue("A{$currentRow}", $idx + 1);
                $sheet1->setCellValue("B{$currentRow}", $item->id);
                $sheet1->setCellValue("C{$currentRow}", $item->name ?? "Project #" . $item->id);
                $sheet1->setCellValue("D{$currentRow}", $item->total_bugs);
                $sheet1->setCellValue("E{$currentRow}", $item->rework_count);
                $sheet1->setCellValue("F{$currentRow}", $item->rework_rate . '%');
                $currentRow++;
            }
            $this->styleTableBody($sheet1, $startTableRow, $currentRow - 1, 'F', true);
            $currentRow += 3;
        }

        // --- TABEL 3: TOP 5 MASALAH TERSERING ---
        if (!empty($this->masalahTop5)) {
            $sheet1->setCellValue("A{$currentRow}", "TOP MASALAH TERSERING (SIMILARITY CLUSTERING)");
            $sheet1->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1A3D63'));
            $currentRow++;

            $masalahHeaders = ['Peringkat', 'Kelompok Masalah (Label Clustering)', 'Jumlah Laporan', 'Persentase dari Total'];
            $this->writeTableHeader($sheet1, $currentRow, $masalahHeaders, 'D');
            $startTableRow = ++$currentRow;

            foreach ($this->masalahTop5 as $idx => $item) {
                $count = $item['count'] ?? 0;
                $pct = count($this->bugs) > 0 ? round(($count / count($this->bugs)) * 100, 1) : 0;
                $sheet1->setCellValue("A{$currentRow}", $idx + 1);
                $sheet1->setCellValue("B{$currentRow}", $item['label'] ?? '-');
                $sheet1->setCellValue("C{$currentRow}", $count);
                $sheet1->setCellValue("D{$currentRow}", $pct . '%');
                $currentRow++;
            }
            $this->styleTableBody($sheet1, $startTableRow, $currentRow - 1, 'D', true);
            $currentRow += 3;
        }

        // --- TABEL 4: TOP 5 ROOT CAUSE TERSERING ---
        if (!empty($this->rootCauseTop5)) {
            $sheet1->setCellValue("A{$currentRow}", "TOP ROOT CAUSE TERSERING (SIMILARITY CLUSTERING)");
            $sheet1->getStyle("A{$currentRow}")->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1A3D63'));
            $currentRow++;

            $rcHeaders = ['Peringkat', 'Kelompok Root Cause (Label Clustering)', 'Jumlah Laporan', 'Persentase dari Total'];
            $this->writeTableHeader($sheet1, $currentRow, $rcHeaders, 'D');
            $startTableRow = ++$currentRow;

            foreach ($this->rootCauseTop5 as $idx => $item) {
                $count = $item['count'] ?? 0;
                $pct = count($this->bugs) > 0 ? round(($count / count($this->bugs)) * 100, 1) : 0;
                $sheet1->setCellValue("A{$currentRow}", $idx + 1);
                $sheet1->setCellValue("B{$currentRow}", $item['label'] ?? '-');
                $sheet1->setCellValue("C{$currentRow}", $count);
                $sheet1->setCellValue("D{$currentRow}", $pct . '%');
                $currentRow++;
            }
            $this->styleTableBody($sheet1, $startTableRow, $currentRow - 1, 'D', true);
        }

        for ($col = 1; $col <= 6; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet1->getColumnDimension($colLetter)->setAutoSize(true);
        }


        // ==========================================
        // SHEET 2: DAFTAR BUG TERKAIT
        // ==========================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Daftar Bug Terkait');
        $this->addFormalHeader($sheet2, 'DAFTAR BUG — ' . strtoupper($this->productName));

        $startRow2 = 7;
        $headers2 = [
            'ID Bug', 'Project', 'Title', 'Severity', 'SN Code Snapshot',
            'Reporter Type', 'Description', 'Version', 'Environment',
            'Root Cause', 'Repair Action', 'Rework?', 'Status',
            'Reported By', 'Fixed By', 'Closed At', 'Created At',
            'Sentiment Label', 'Sentiment Score',
            'Severity Recommended', 'Severity Rec Reason'
        ];

        $lastColIdx2 = count($headers2);
        $lastColLetter2 = Coordinate::stringFromColumnIndex($lastColIdx2);

        foreach ($headers2 as $colIdx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $cellCoord = $colLetter . $startRow2;
            $sheet2->setCellValue($cellCoord, $header);

            if ($header === 'ID Bug' || $header === 'SN Code Snapshot') {
                $sheet2->getStyle($colLetter)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            }
        }

        $this->writeTableHeader($sheet2, $startRow2, $headers2, $lastColLetter2);

        $rowIndex2 = $startRow2 + 1;
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
                $cellCoord = $colLetter . $rowIndex2;

                if ($colIdx === 0 || $colIdx === 4) {
                    $sheet2->setCellValueExplicit($cellCoord, (string)($value ?? ''), DataType::TYPE_STRING);
                    $sheet2->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                } else {
                    $sheet2->setCellValue($cellCoord, $value ?? '');
                }
            }

            if ($rowIndex2 % 2 === 0) {
                $sheet2->getStyle("A{$rowIndex2}:{$lastColLetter2}{$rowIndex2}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8FAFC'],
                    ],
                ]);
            }

            $rowIndex2++;
        }

        $lastDataRow2 = $rowIndex2 - 1;
        if ($lastDataRow2 >= $startRow2 + 1) {
            $sheet2->getStyle("A" . ($startRow2 + 1) . ":{$lastColLetter2}{$lastDataRow2}")->getFont()->setName('Inter')->setSize(9.5);
            $sheet2->getStyle("A{$startRow2}:{$lastColLetter2}{$lastDataRow2}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D5E3F0'],
                    ],
                ],
            ]);
        }

        $sheet2->freezePane('A' . ($startRow2 + 1));

        for ($col = 1; $col <= $lastColIdx2; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet2->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    protected function addFormalHeader($sheet, string $reportTitle)
    {
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

        $sheet->setCellValue('C1', 'HARIFF DEFENSE');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(18)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1A3D63'));
        
        $sheet->setCellValue('C2', 'ManufakTrack — Sistem Pelacakan Manufaktur');
        $sheet->getStyle('C2')->getFont()->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4A7FA7'));

        $sheet->setCellValue('A4', $reportTitle);
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1A3D63'));

        $sheet->setCellValue('A5', 'Tanggal Cetak: ' . now()->translatedFormat('d F Y, H:i'));
        $sheet->getStyle('A5')->getFont()->setSize(10)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('475569'));

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getRowDimension(2)->setRowHeight(25);
    }

    protected function writeTableHeader($sheet, int $row, array $headers, string $lastColLetter)
    {
        foreach ($headers as $colIdx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue($colLetter . $row, $header);
        }

        $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'name' => 'Inter',
                'size' => 10,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1A3D63'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(26);
    }

    protected function styleTableBody($sheet, int $startRow, int $endRow, string $lastColLetter, bool $zebra = true)
    {
        if ($endRow < $startRow) return;

        $sheet->getStyle("A{$startRow}:{$lastColLetter}{$endRow}")->getFont()->setName('Inter')->setSize(9.5);
        $sheet->getStyle("A" . ($startRow - 1) . ":{$lastColLetter}{$endRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D5E3F0'],
                ],
            ],
        ]);

        if ($zebra) {
            for ($r = $startRow; $r <= $endRow; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8FAFC'],
                        ],
                    ]);
                }
            }
        }
    }
}
