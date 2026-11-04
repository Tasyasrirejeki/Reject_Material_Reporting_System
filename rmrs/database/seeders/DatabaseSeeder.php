<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RejectReport;
use App\Models\RejectDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users
        $supervisor = User::create([
            'name' => 'Ahmad Supervisor',
            'email' => 'supervisor@rmrs.com',
            'password' => Hash::make('password'),
            'role' => 'supervisor',
            'department' => 'all',
            'is_active' => true,
        ]);

        $leader1 = User::create([
            'name' => 'Budi Line Leader',
            'email' => 'leader@rmrs.com',
            'password' => Hash::make('password'),
            'role' => 'line_leader',
            'department' => 'all',
            'is_active' => true,
        ]);

        // Operator bisa bekerja di Lenscase maupun Pouching (department = 'all')
        $operator1 = User::create([
            'name' => 'Citra Operator',
            'email' => 'operator1@rmrs.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'department' => 'all',
            'is_active' => true,
        ]);

        $operator2 = User::create([
            'name' => 'Deni Operator',
            'email' => 'operator2@rmrs.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'department' => 'all',
            'is_active' => true,
        ]);

        // Create sample reports
        $lensDefects = ['Scratch / Goresan', 'Bubble / Gelembung', 'Coating Tidak Merata', 'Dimensi Tidak Sesuai', 'Retak / Crack', 'Edge Chip', 'Coating Peeling'];
        $pouchDefects = ['Seal Bocor', 'Seal Tidak Rapi', 'Label Miring', 'Expired Date Salah', 'Jumlah Isi Kurang'];

        for ($i = 0; $i < 20; $i++) {
            $dept = $i % 2 === 0 ? 'lenscase' : 'pouching';
            $operator = $i % 4 < 2 ? $operator1 : $operator2;
            $date = now()->subDays(rand(0, 30));
            $shift = rand(1, 2);
            // Waktu submit: pada jam shift terkait (shift 1: 07-19, shift 2: 19-07)
            $submittedAt = $date->copy()->setTime($shift === 1 ? rand(8, 18) : rand(19, 23), rand(0, 59), rand(0, 59));
            $status = ['pending', 'verified', 'verified', 'rejected'][$i % 4];
            $totalProd = rand(500, 2000);

            $report = RejectReport::create([
                'report_number' => 'RR-' . strtoupper(substr($dept, 0, 2)) . '-' . $date->format('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'operator_id' => $operator->id,
                'line_leader_id' => in_array($status, ['verified', 'rejected']) ? $leader1->id : null,
                'department' => $dept,
                'shift' => $shift,
                'production_date' => $date,
                'product_name' => $dept === 'lenscase' ? 'Lens Case Type ' . chr(65 + ($i % 5)) : 'Pouch Premium ' . ($i % 3 + 1),
                'product_code' => strtoupper($dept[0]) . 'P-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT),
                'lot_number' => 'LOT' . $date->format('ymd') . str_pad($i, 2, '0', STR_PAD_LEFT),
                'machine_number' => 'M-' . str_pad(rand(1, 10), 2, '0', STR_PAD_LEFT),
                'total_production_qty' => $totalProd,
                'status' => $status,
                'submitted_at' => $submittedAt,
                'operator_notes' => 'Ditemukan defect pada proses produksi shift ini.',
                'leader_notes' => in_array($status, ['verified', 'rejected']) ? 'Sudah diperiksa dan dicatat.' : null,
                'verified_at' => in_array($status, ['verified', 'rejected']) ? $date->addHours(rand(2, 8)) : null,
            ]);

            $defects = $dept === 'lenscase' ? $lensDefects : $pouchDefects;
            $numDefects = rand(1, 3);
            $totalReject = 0;
            for ($j = 0; $j < $numDefects; $j++) {
                $qty = rand(1, 50);
                $totalReject += $qty;
                $report->details()->create([
                    'defect_type' => $defects[$j % count($defects)],
                    'defect_category' => ['critical', 'major', 'minor'][rand(0, 2)],
                    'quantity' => $qty,
                    'description' => 'Terdeteksi pada inspeksi visual.',
                    'defect_location' => 'Pos ' . rand(1, 5),
                ]);
            }

            $pct = $totalProd > 0 ? round(($totalReject / $totalProd) * 100, 2) : 0;
            $report->update(['total_reject_qty' => $totalReject, 'reject_percentage' => $pct]);
        }
    }
}
