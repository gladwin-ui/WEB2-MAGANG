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
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
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
        $this->addFormalHeader($sheet1, 'LAPORAN KHUSUS — ANALISIS MASALAH & ROOT CAUSE (' . strtoupper($this->productName) . ')', 6);

        $currentRow = 4;

        // --- TABEL 1: KPI & DISTRIBUSI SEVERITY ---
        $sheet1->mergeCells("A{$currentRow}:F{$currentRow}");
        $sheet1->setCellValue("A{$currentRow}", "STATISTIK UTAMA & DISTRIBUSI SEVERITY");
        $sheet1->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1A3D63'], 'name' => 'Inter'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension($currentRow)->setRowHeight(24);
        $currentRow++;

        $kpiHeaders = ['TOTAL LAPORAN DI-EXPORT', 'RATA-RATA REWORK RATE', 'CRITICAL SEVERITY', 'MAJOR SEVERITY', 'MINOR SEVERITY'];
        $sheet1->setCellValue("A{$currentRow}", $kpiHeaders[0]);
        $sheet1->mergeCells("B{$currentRow}:C{$currentRow}");
        $sheet1->setCellValue("B{$currentRow}", $kpiHeaders[1]);
        $sheet1->setCellValue("D{$currentRow}", $kpiHeaders[2]);
        $sheet1->setCellValue("E{$currentRow}", $kpiHeaders[3]);
        $sheet1->setCellValue("F{$currentRow}", $kpiHeaders[4]);

        $sheet1->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Inter', 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3D63']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension($currentRow)->setRowHeight(26);
        $currentRow++;

        $sheet1->setCellValue("A{$currentRow}", count($this->bugs));
        $sheet1->mergeCells("B{$currentRow}:C{$currentRow}");
        $sheet1->setCellValue("B{$currentRow}", $this->avgRework . '%');
        $sheet1->setCellValue("D{$currentRow}", $this->severityMix['Critical'] ?? 0);
        $sheet1->setCellValue("E{$currentRow}", $this->severityMix['Major'] ?? 0);
        $sheet1->setCellValue("F{$currentRow}", $this->severityMix['Minor'] ?? 0);

        $sheet1->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
            'font' => ['bold' => true, 'name' => 'Inter', 'size' => 14, 'color' => ['rgb' => '0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5E3F0']]],
        ]);
        $sheet1->getRowDimension($currentRow)->setRowHeight(32);
        $currentRow += 2;

        // --- NATIVE EXCEL DONUT CHART (SEVERITY) ---
        try {
            $dataSeriesLabels = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Analisis Khusus'!\$A\$4", null, 1)];
            $xAxisTickValues = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Analisis Khusus'!\$D\$5:\$F\$5", null, 3)];
            $dataSeriesValues = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Analisis Khusus'!\$D\$6:\$F\$6", null, 3)];

            $series = new DataSeries(
                DataSeries::TYPE_DONUTCHART, null, range(0, count($dataSeriesValues) - 1),
                $dataSeriesLabels, $xAxisTickValues, $dataSeriesValues
            );
            $plotArea = new PlotArea(null, [$series]);
            $legend = new Legend(Legend::POSITION_RIGHT, null, false);
            $title = new Title('Distribusi Severity Level');
            $chart = new Chart('chart_severity_khusus', $title, $legend, $plotArea);
            $chart->setTopLeftPosition('D8');
            $chart->setBottomRightPosition('F18');
            $sheet1->addChart($chart);
        } catch (\Exception $e) {}

        // --- TABEL 2: TOP 5 REWORK RATE PER PRODUK ---
        if ($this->reworkRates && count($this->reworkRates) > 0) {
            $sheet1->mergeCells("A{$currentRow}:F{$currentRow}");
            $sheet1->setCellValue("A{$currentRow}", "TOP 5 REWORK RATE ANTAR PRODUK");
            $sheet1->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1A3D63'], 'name' => 'Inter'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet1->getRowDimension($currentRow)->setRowHeight(24);
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
            $sheet1->getStyle("A{$startTableRow}:B" . ($currentRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("D{$startTableRow}:F" . ($currentRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $currentRow += 2;
        }

        // --- TABEL 3: TOP MASALAH TERSERING ---
        if (!empty($this->masalahTop5)) {
            $sheet1->mergeCells("A{$currentRow}:D{$currentRow}");
            $sheet1->setCellValue("A{$currentRow}", "TOP MASALAH TERSERING (SIMILARITY CLUSTERING)");
            $sheet1->getStyle("A{$currentRow}:D{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1A3D63'], 'name' => 'Inter'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet1->getRowDimension($currentRow)->setRowHeight(24);
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
            $sheet1->getStyle("A{$startTableRow}:A" . ($currentRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("C{$startTableRow}:D" . ($currentRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $currentRow += 2;
        }

        // --- TABEL 4: TOP ROOT CAUSE TERSERING ---
        if (!empty($this->rootCauseTop5)) {
            $sheet1->mergeCells("A{$currentRow}:D{$currentRow}");
            $sheet1->setCellValue("A{$currentRow}", "TOP ROOT CAUSE TERSERING (SIMILARITY CLUSTERING)");
            $sheet1->getStyle("A{$currentRow}:D{$currentRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1A3D63'], 'name' => 'Inter'],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet1->getRowDimension($currentRow)->setRowHeight(24);
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
            $sheet1->getStyle("A{$startTableRow}:A" . ($currentRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("C{$startTableRow}:D" . ($currentRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
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
        $this->addFormalHeader($sheet2, 'DAFTAR BUG — ' . strtoupper($this->productName), 21);

        $startRow2 = 4;
        $sheet2->mergeCells("A{$startRow2}:U{$startRow2}");
        $sheet2->setCellValue("A{$startRow2}", "DATA LENGKAP LAPORAN BUG (" . strtoupper($this->productName) . ")");
        $sheet2->getStyle("A{$startRow2}:U{$startRow2}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1A3D63'], 'name' => 'Inter'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet2->getRowDimension($startRow2)->setRowHeight(24);
        $startRow2++;

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
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                ]);
            }

            $rowIndex2++;
        }

        $lastDataRow2 = $rowIndex2 - 1;
        if ($lastDataRow2 >= $startRow2 + 1) {
            $sheet2->getStyle("A" . ($startRow2 + 1) . ":{$lastColLetter2}{$lastDataRow2}")->getFont()->setName('Inter')->setSize(9.5);
            $sheet2->getStyle("A{$startRow2}:{$lastColLetter2}{$lastDataRow2}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5E3F0']]],
            ]);
            $sheet2->getStyle("D" . ($startRow2 + 1) . ":D{$lastDataRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("F" . ($startRow2 + 1) . ":F{$lastDataRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("L" . ($startRow2 + 1) . ":M{$lastDataRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet2->freezePane('A' . ($startRow2 + 1));

        for ($col = 1; $col <= $lastColIdx2; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet2->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    protected function addFormalHeader($sheet, string $reportTitle, int $maxCol = 6)
    {
        // 1. Logo di Kiri (Kolom A)
        $logoPath = public_path('LOGO LOGO LAGI.png');
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

        // 2. Judul di Tengah (Rata Tengah / Center Aligned)
        $centerEndCol = max(3, $maxCol - 2);
        $centerEndLetter = Coordinate::stringFromColumnIndex($centerEndCol);

        $sheet->mergeCells("B1:{$centerEndLetter}1");
        $sheet->mergeCells("B2:{$centerEndLetter}2");

        $sheet->setCellValue('B1', $reportTitle);
        $sheet->getStyle("B1:{$centerEndLetter}1")->applyFromArray([
            'font' => ['bold' => true, 'name' => 'Inter', 'size' => 14, 'color' => ['rgb' => '1A3D63']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->setCellValue('B2', 'ManufakTrack — Sistem Pelacakan Manufaktur by Hariff Defense');
        $sheet->getStyle("B2:{$centerEndLetter}2")->applyFromArray([
            'font' => ['name' => 'Inter', 'size' => 10, 'color' => ['rgb' => '4A7FA7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // 3. Tanggal Cetak di Kanan
        $rightStartCol = $centerEndCol + 1;
        $rightStartLetter = Coordinate::stringFromColumnIndex($rightStartCol);
        $maxColLetter = Coordinate::stringFromColumnIndex($maxCol);

        if ($rightStartCol < $maxCol) {
            $sheet->mergeCells("{$rightStartLetter}1:{$maxColLetter}2");
        }

        $sheet->setCellValue("{$rightStartLetter}1", "Tanggal Cetak:\n" . now()->translatedFormat('d F Y, H:i'));
        $sheet->getStyle("{$rightStartLetter}1:{$maxColLetter}2")->applyFromArray([
            'font' => ['name' => 'Inter', 'size' => 9.5, 'italic' => true, 'color' => ['rgb' => '475569']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);

        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->getRowDimension(2)->setRowHeight(25);
    }

    protected function writeTableHeader($sheet, int $row, array $headers, string $lastColLetter)
    {
        foreach ($headers as $colIdx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue($colLetter . $row, $header);
        }

        $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Inter', 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3D63']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($row)->setRowHeight(26);
    }

    protected function styleTableBody($sheet, int $startRow, int $endRow, string $lastColLetter, bool $zebra = true)
    {
        if ($endRow < $startRow) return;

        $sheet->getStyle("A{$startRow}:{$lastColLetter}{$endRow}")->getFont()->setName('Inter')->setSize(9.5);
        $sheet->getStyle("A" . ($startRow - 1) . ":{$lastColLetter}{$endRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5E3F0']]],
        ]);

        if ($zebra) {
            for ($r = $startRow; $r <= $endRow; $r++) {
                if ($r % 2 === 0) {
                    $sheet->getStyle("A{$r}:{$lastColLetter}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                    ]);
                }
            }
        }
    }
}
