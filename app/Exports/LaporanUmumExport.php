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

class LaporanUmumExport
{
    protected $bugs;

    public function __construct($bugs)
    {
        $this->bugs = $bugs;
    }

    public function generate(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();

        // ==========================================
        // SHEET 1: RINGKASAN UMUM & KPI
        // ==========================================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Ringkasan Umum');
        $this->addFormalHeader($sheet1, 'LAPORAN UMUM PELACAKAN BUG MANUFAKTUR', 6);

        // --- BAGIAN 1: KPI CARDS ---
        $sheet1->mergeCells("A4:F4");
        $sheet1->setCellValue("A4", "STATISTIK UTAMA (KPI CARDS)");
        $sheet1->getStyle("A4:F4")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1A3D63'], 'name' => 'Inter'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension(4)->setRowHeight(24);

        $kpiHeaders = ['TOTAL BUG', 'PERBAIKAN (OPEN)', 'SELESAI (CLOSED)', 'TOTAL REWORK', 'REWORK RATE (%)'];
        $sheet1->setCellValue("A5", $kpiHeaders[0]);
        $sheet1->setCellValue("B5", $kpiHeaders[1]);
        $sheet1->setCellValue("C5", $kpiHeaders[2]);
        $sheet1->setCellValue("D5", $kpiHeaders[3]);
        $sheet1->mergeCells("E5:F5");
        $sheet1->setCellValue("E5", $kpiHeaders[4]);

        $sheet1->getStyle("A5:F5")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Inter', 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3D63']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension(5)->setRowHeight(26);

        $total = $this->bugs->count();
        $openCount = $this->bugs->whereIn('status', ['open', 'in_progress', 'investigating', 'reopened'])->count();
        $closedCount = $this->bugs->whereIn('status', ['closed', 'resolved', 'done'])->count();
        if ($openCount + $closedCount < $total) {
            $openCount = $total - $closedCount;
        }
        $reworkCount = $this->bugs->where('is_rework', 1)->count();
        $reworkRate = $total > 0 ? round(($reworkCount / $total) * 100, 1) : 0;

        $sheet1->setCellValue("A6", $total);
        $sheet1->setCellValue("B6", $openCount);
        $sheet1->setCellValue("C6", $closedCount);
        $sheet1->setCellValue("D6", $reworkCount);
        $sheet1->mergeCells("E6:F6");
        $sheet1->setCellValue("E6", $reworkRate . '%');

        $sheet1->getStyle("A6:F6")->applyFromArray([
            'font' => ['bold' => true, 'name' => 'Inter', 'size' => 14, 'color' => ['rgb' => '0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5E3F0']]],
        ]);
        $sheet1->getRowDimension(6)->setRowHeight(32);

        // --- BAGIAN 2: DISTRIBUSI BUG (STATUS & SEVERITY) ---
        $sheet1->mergeCells("A8:F8");
        $sheet1->setCellValue("A8", "DISTRIBUSI BUG (STATUS & SEVERITY LEVEL)");
        $sheet1->getStyle("A8:F8")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1A3D63'], 'name' => 'Inter'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension(8)->setRowHeight(24);

        $sheet1->mergeCells("A9:C9");
        $sheet1->setCellValue("A9", "Distribusi Status Open / Closed");
        $sheet1->mergeCells("D9:F9");
        $sheet1->setCellValue("D9", "Distribusi Severity Level");
        $sheet1->getStyle("A9:F9")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1A3D63'], 'name' => 'Inter'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension(9)->setRowHeight(22);

        $sheet1->setCellValue("A10", "Status");
        $sheet1->mergeCells("B10:C10");
        $sheet1->setCellValue("B10", "Jumlah Laporan");
        $sheet1->setCellValue("D10", "Severity");
        $sheet1->mergeCells("E10:F10");
        $sheet1->setCellValue("E10", "Jumlah Laporan");

        $sheet1->getStyle("A10:F10")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Inter', 'size' => 9.5],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3D63']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension(10)->setRowHeight(24);

        // Data Status
        $sheet1->setCellValue("A11", "Open / In Progress");
        $sheet1->mergeCells("B11:C11");
        $sheet1->setCellValue("B11", $openCount);
        $sheet1->setCellValue("A12", "Closed / Resolved");
        $sheet1->mergeCells("B12:C12");
        $sheet1->setCellValue("B12", $closedCount);
        $sheet1->setCellValue("A13", "Total Laporan");
        $sheet1->mergeCells("B13:C13");
        $sheet1->setCellValue("B13", $total);

        // Data Severity
        $critCount = $this->bugs->where('severity', 'Critical')->count();
        $majCount = $this->bugs->where('severity', 'Major')->count();
        $minCount = $this->bugs->where('severity', 'Minor')->count();

        $sheet1->setCellValue("D11", "Critical");
        $sheet1->mergeCells("E11:F11");
        $sheet1->setCellValue("E11", $critCount);
        $sheet1->setCellValue("D12", "Major");
        $sheet1->mergeCells("E12:F12");
        $sheet1->setCellValue("E12", $majCount);
        $sheet1->setCellValue("D13", "Minor");
        $sheet1->mergeCells("E13:F13");
        $sheet1->setCellValue("E13", $minCount);

        $sheet1->getStyle("A11:F13")->applyFromArray([
            'font' => ['name' => 'Inter', 'size' => 9.5],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5E3F0']]],
        ]);
        $sheet1->getStyle("B11:C13")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle("E11:F13")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle("A13:C13")->getFont()->setBold(true);
        $sheet1->getStyle("A13:C13")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1F5F9');
        $sheet1->getStyle("D13:F13")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');

        // --- NATIVE EXCEL CHARTS (DONUT STATUS & SEVERITY) ---
        try {
            $dataSeriesLabels1 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Ringkasan Umum'!\$A\$9", null, 1)];
            $xAxisTickValues1 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Ringkasan Umum'!\$A\$11:\$A\$12", null, 2)];
            $dataSeriesValues1 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Ringkasan Umum'!\$B\$11:\$B\$12", null, 2)];

            $series1 = new DataSeries(
                DataSeries::TYPE_DONUTCHART, null, range(0, count($dataSeriesValues1) - 1),
                $dataSeriesLabels1, $xAxisTickValues1, $dataSeriesValues1
            );
            $plotArea1 = new PlotArea(null, [$series1]);
            $legend1 = new Legend(Legend::POSITION_BOTTOM, null, false);
            $title1 = new Title('Donut Open / Closed');
            $chart1 = new Chart('chart_status', $title1, $legend1, $plotArea1);
            $chart1->setTopLeftPosition('A15');
            $chart1->setBottomRightPosition('C26');
            $sheet1->addChart($chart1);
        } catch (\Exception $e) {}

        try {
            $dataSeriesLabels2 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Ringkasan Umum'!\$D\$9", null, 1)];
            $xAxisTickValues2 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Ringkasan Umum'!\$D\$11:\$D\$13", null, 3)];
            $dataSeriesValues2 = [new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Ringkasan Umum'!\$E\$11:\$E\$13", null, 3)];

            $series2 = new DataSeries(
                DataSeries::TYPE_DONUTCHART, null, range(0, count($dataSeriesValues2) - 1),
                $dataSeriesLabels2, $xAxisTickValues2, $dataSeriesValues2
            );
            $plotArea2 = new PlotArea(null, [$series2]);
            $legend2 = new Legend(Legend::POSITION_BOTTOM, null, false);
            $title2 = new Title('Donut Severity Level');
            $chart2 = new Chart('chart_severity', $title2, $legend2, $plotArea2);
            $chart2->setTopLeftPosition('D15');
            $chart2->setBottomRightPosition('F26');
            $sheet1->addChart($chart2);
        } catch (\Exception $e) {}

        // --- BAGIAN 3: TOP 5 PRODUK / PROJECT ---
        $rowTop = 28;
        $sheet1->mergeCells("A{$rowTop}:F{$rowTop}");
        $sheet1->setCellValue("A{$rowTop}", "TOP 5 PRODUK / PROJECT DENGAN BUG TERBANYAK");
        $sheet1->getStyle("A{$rowTop}:F{$rowTop}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1A3D63'], 'name' => 'Inter'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension($rowTop)->setRowHeight(24);
        $rowTop++;

        $topHeaders = ['Peringkat', 'ID Project', 'Nama Produk / Project', 'Total Bug', 'Rework Count', 'Persentase (%)'];
        foreach ($topHeaders as $idx => $th) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            $sheet1->setCellValue($colLetter . $rowTop, $th);
        }
        $sheet1->getStyle("A{$rowTop}:F{$rowTop}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Inter', 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3D63']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet1->getRowDimension($rowTop)->setRowHeight(25);
        $startTableTop = ++$rowTop;

        $topProjects = $this->bugs->groupBy('project_id')->map(function ($group, $projId) {
            $first = $group->first();
            $name = $first?->project?->name ?? ($projId ? "Project #{$projId}" : "Tanpa Proyek");
            $rework = $group->where('is_rework', 1)->count();
            return (object)[
                'id' => $projId ?? '-',
                'name' => $name,
                'total' => $group->count(),
                'rework' => $rework,
            ];
        })->sortByDesc('total')->take(5)->values();

        foreach ($topProjects as $idx => $item) {
            $pct = $total > 0 ? round(($item->total / $total) * 100, 1) : 0;
            $sheet1->setCellValue("A{$rowTop}", $idx + 1);
            $sheet1->setCellValue("B{$rowTop}", $item->id);
            $sheet1->setCellValue("C{$rowTop}", $item->name);
            $sheet1->setCellValue("D{$rowTop}", $item->total);
            $sheet1->setCellValue("E{$rowTop}", $item->rework);
            $sheet1->setCellValue("F{$rowTop}", $pct . '%');

            if ($rowTop % 2 === 0) {
                $sheet1->getStyle("A{$rowTop}:F{$rowTop}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }
            $rowTop++;
        }

        $lastRowTop = $rowTop - 1;
        if ($lastRowTop >= $startTableTop) {
            $sheet1->getStyle("A{$startTableTop}:F{$lastRowTop}")->applyFromArray([
                'font' => ['name' => 'Inter', 'size' => 9.5],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5E3F0']]],
            ]);
            $sheet1->getStyle("A{$startTableTop}:B{$lastRowTop}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet1->getStyle("D{$startTableTop}:F{$lastRowTop}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        for ($col = 1; $col <= 6; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $sheet1->getColumnDimension($colLetter)->setAutoSize(true);
        }


        // ==========================================
        // SHEET 2: DAFTAR BUG LENGKAP
        // ==========================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Daftar Bug');
        $this->addFormalHeader($sheet2, 'DAFTAR BUG MANUFAKTRACK (' . strtoupper($total . ' LAPORAN') . ')', 21);

        $row2 = 4;
        $sheet2->mergeCells("A{$row2}:U{$row2}");
        $sheet2->setCellValue("A{$row2}", "DATA LENGKAP LAPORAN BUG (SEMUA PRODUK)");
        $sheet2->getStyle("A{$row2}:U{$row2}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1A3D63'], 'name' => 'Inter'],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF3FB']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet2->getRowDimension($row2)->setRowHeight(24);
        $row2++;

        $headers2 = [
            'ID Bug', 'Project', 'Title', 'Severity', 'SN Code Snapshot',
            'Reporter Type', 'Description', 'Version', 'Environment',
            'Root Cause', 'Repair Action', 'Rework?', 'Status',
            'Reported By', 'Fixed By', 'Closed At', 'Created At',
            'Sentiment Label', 'Sentiment Score',
            'Severity Recommended', 'Severity Rec Reason'
        ];

        foreach ($headers2 as $colIdx => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $cellCoord = $colLetter . $row2;
            $sheet2->setCellValue($cellCoord, $header);

            if ($header === 'ID Bug' || $header === 'SN Code Snapshot') {
                $sheet2->getStyle($colLetter)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            }
        }

        $sheet2->getStyle("A{$row2}:U{$row2}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Inter', 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A3D63']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet2->getRowDimension($row2)->setRowHeight(26);

        $startDataRow2 = $row2 + 1;
        $rowIndex2 = $startDataRow2;

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
                $sheet2->getStyle("A{$rowIndex2}:U{$rowIndex2}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
                ]);
            }

            $rowIndex2++;
        }

        $lastDataRow2 = $rowIndex2 - 1;
        if ($lastDataRow2 >= $startDataRow2) {
            $sheet2->getStyle("A{$startDataRow2}:U{$lastDataRow2}")->applyFromArray([
                'font' => ['name' => 'Inter', 'size' => 9.5],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D5E3F0']]],
            ]);
            $sheet2->getStyle("D{$startDataRow2}:D{$lastDataRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("F{$startDataRow2}:F{$lastDataRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet2->getStyle("L{$startDataRow2}:M{$lastDataRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet2->freezePane('A' . ($startDataRow2));

        for ($col = 1; $col <= 21; $col++) {
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
}
