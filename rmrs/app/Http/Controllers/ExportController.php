<?php

namespace App\Http\Controllers;

use App\Models\RejectReport;
use App\Models\RejectDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    public function export(Request $request)
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'department' => ['nullable', 'in:lenscase,pouching'],
            'status' => ['nullable', 'in:pending,verified,rejected'],
            'shift' => ['nullable', 'in:1,2'],
        ]);

        $query = RejectReport::with(['operator', 'lineLeader', 'details']);

        if ($request->filled('department')) $query->where('department', $request->department);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('shift')) $query->where('shift', $request->shift);
        if ($request->filled('date_from')) $query->whereDate('production_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('production_date', '<=', $request->date_to);

        $reports = $query->orderBy('production_date')->orderBy('report_number')->get();

        $filename = 'RMRS_Export_' . now()->format('Ymd_His') . '.xlsx';

        return $this->generateExcel($reports, $filename);
    }

    private function generateExcel($reports, $filename)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ============ Sheet 1: Summary ============
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Ringkasan Laporan');

        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D3A8C']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ];

        $rowStyle = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            'alignment' => ['vertical' => 'center'],
        ];

        // Company header
        $sheet1->mergeCells('A1:S1');
        $sheet1->setCellValue('A1', 'REJECT MATERIAL REPORTING SYSTEM (RMRS)');
        $sheet1->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '2D3A8C']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $sheet1->mergeCells('A2:S2');
        $sheet1->setCellValue('A2', 'Laporan Diekspor: ' . now()->format('d F Y H:i:s') . ' | Total Data: ' . $reports->count() . ' laporan');
        $sheet1->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '666666']],
            'alignment' => ['horizontal' => 'center'],
        ]);

        $sheet1->getRowDimension(1)->setRowHeight(30);
        $sheet1->getRowDimension(2)->setRowHeight(18);
        $sheet1->getRowDimension(4)->setRowHeight(30);

        // Column headers
        $headers = [
            'A' => 'No.',
            'B' => 'Nomor Laporan',
            'C' => 'Tanggal Produksi',
            'D' => 'Waktu Submit',
            'E' => 'Departemen',
            'F' => 'Shift',
            'G' => 'Nama Produk',
            'H' => 'Kode Produk',
            'I' => 'Lot Number',
            'J' => 'No. Mesin',
            'K' => 'Qty Produksi',
            'L' => 'Qty Reject',
            'M' => '% Reject',
            'N' => 'Status',
            'O' => 'Operator',
            'P' => 'Line Leader',
            'Q' => 'Waktu Verifikasi',
            'R' => 'Catatan Operator',
            'S' => 'Catatan Line Leader',
        ];

        $row = 4;
        foreach ($headers as $col => $header) {
            $sheet1->setCellValue($col . $row, $header);
        }
        $sheet1->getStyle('A4:S4')->applyFromArray($headerStyle);

        // Column widths
        $widths = [
            'A' => 5, 'B' => 22, 'C' => 18, 'D' => 20, 'E' => 12, 'F' => 24,
            'G' => 25, 'H' => 15, 'I' => 15, 'J' => 12, 'K' => 14,
            'L' => 12, 'M' => 10, 'N' => 18, 'O' => 22, 'P' => 22,
            'Q' => 20, 'R' => 30, 'S' => 30,
        ];
        foreach ($widths as $col => $width) {
            $sheet1->getColumnDimension($col)->setWidth($width);
        }

        // Data rows
        $row = 5;
        foreach ($reports as $i => $report) {
            $sheet1->setCellValue('A' . $row, $i + 1);
            $sheet1->setCellValue('B' . $row, $report->report_number);
            $sheet1->setCellValue('C' . $row, $report->production_date->format('d/m/Y'));
            $sheet1->setCellValue('D' . $row, ($report->submitted_at ?? $report->created_at)->format('d/m/Y H:i:s'));
            $sheet1->setCellValue('E' . $row, $report->department_display);
            $sheet1->setCellValue('F' . $row, $report->shift_display);
            $sheet1->setCellValue('G' . $row, $report->product_name);
            $sheet1->setCellValue('H' . $row, $report->product_code ?? '-');
            $sheet1->setCellValue('I' . $row, $report->lot_number ?? '-');
            $sheet1->setCellValue('J' . $row, $report->machine_number ?? '-');
            $sheet1->setCellValue('K' . $row, $report->total_production_qty);
            $sheet1->setCellValue('L' . $row, $report->total_reject_qty);
            $sheet1->setCellValue('M' . $row, $report->reject_percentage . '%');
            $sheet1->setCellValue('N' . $row, $report->status_display);
            $sheet1->setCellValue('O' . $row, $report->operator->name ?? '-');
            $sheet1->setCellValue('P' . $row, $report->lineLeader->name ?? '-');
            $sheet1->setCellValue('Q' . $row, $report->verified_at ? $report->verified_at->format('d/m/Y H:i') : '-');
            $sheet1->setCellValue('R' . $row, $report->operator_notes ?? '-');
            $sheet1->setCellValue('S' . $row, $report->leader_notes ?? '-');

            // Zebra striping
            $bgColor = $row % 2 === 0 ? 'F5F7FF' : 'FFFFFF';
            $sheet1->getStyle('A'.$row.':S'.$row)->applyFromArray([
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'DDDDDD']]],
            ]);

            // Status color
            $statusColor = match($report->status) {
                'verified' => '10B981',
                'rejected' => 'EF4444',
                default => 'F59E0B',
            };
            $sheet1->getStyle('N' . $row)->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => $statusColor]],
            ]);

            $sheet1->getRowDimension($row)->setRowHeight(20);
            $row++;
        }

        // ============ Sheet 2: Detail Defects ============
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Detail Defect');

        $detailHeaders = ['A' => 'Nomor Laporan', 'B' => 'Tanggal', 'C' => 'Departemen', 'D' => 'Produk', 'E' => 'Jenis Defect', 'F' => 'Kategori', 'G' => 'Qty', 'H' => 'Lokasi', 'I' => 'Deskripsi'];
        $detailRow = 1;
        foreach ($detailHeaders as $col => $header) {
            $sheet2->setCellValue($col . $detailRow, $header);
        }
        $sheet2->getStyle('A1:I1')->applyFromArray($headerStyle);

        $detailRow = 2;
        foreach ($reports as $report) {
            foreach ($report->details as $detail) {
                $sheet2->setCellValue('A' . $detailRow, $report->report_number);
                $sheet2->setCellValue('B' . $detailRow, $report->production_date->format('d/m/Y'));
                $sheet2->setCellValue('C' . $detailRow, $report->department_display);
                $sheet2->setCellValue('D' . $detailRow, $report->product_name);
                $sheet2->setCellValue('E' . $detailRow, $detail->defect_type);
                $sheet2->setCellValue('F' . $detailRow, strtoupper($detail->defect_category));
                $sheet2->setCellValue('G' . $detailRow, $detail->quantity);
                $sheet2->setCellValue('H' . $detailRow, $detail->defect_location ?? '-');
                $sheet2->setCellValue('I' . $detailRow, $detail->description ?? '-');

                $catColor = match($detail->defect_category) {
                    'critical' => 'EF4444', 'major' => 'F59E0B', default => '10B981'
                };
                $sheet2->getStyle('F' . $detailRow)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => $catColor]],
                ]);
                $detailRow++;
            }
        }

        foreach (['A' => 20, 'B' => 12, 'C' => 12, 'D' => 25, 'E' => 30, 'F' => 12, 'G' => 8, 'H' => 20, 'I' => 35] as $col => $width) {
            $sheet2->getColumnDimension($col)->setWidth($width);
        }

        // Back to first sheet
        $spreadsheet->setActiveSheetIndex(0);

        // Output
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Length' => strlen($content),
        ]);
    }
}
