<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\RejectReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaderDashboardController extends Controller
{
    public function index()
    {
        // Antrian verifikasi = laporan yang diedit operator setelah submit
        $pendingReports = RejectReport::with(['operator', 'details'])
            ->where('status', 'pending')
            ->orderByDesc('edited_at')
            ->take(10)
            ->get();

        $stats = [
            'pending_count' => RejectReport::where('status', 'pending')->count(),
            'verified_today' => RejectReport::where('status', 'verified')
                ->whereDate('verified_at', today())->count(),
            'total_today' => RejectReport::whereDate('production_date', today())->count(),
            'total_reject_today' => RejectReport::whereDate('production_date', today())->sum('total_reject_qty'),
        ];

        // Chart: reject per department this month
        $deptChart = RejectReport::selectRaw('department, SUM(total_reject_qty) as total')
            ->whereMonth('production_date', now()->month)
            ->whereYear('production_date', now()->year)
            ->counted()
            ->groupBy('department')
            ->pluck('total', 'department');

        // Chart: daily reject trend (last 7 days)
        $trend = RejectReport::selectRaw('DATE(production_date) as date, SUM(total_reject_qty) as total')
            ->where('production_date', '>=', now()->subDays(6))
            ->counted()
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        return view('leader.dashboard', compact('pendingReports', 'stats', 'deptChart', 'trend'));
    }

    public function reports(Request $request)
    {
        $query = RejectReport::with(['operator', 'details', 'lineLeader']);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('department')) $query->where('department', $request->department);
        if ($request->filled('shift')) $query->where('shift', $request->shift);
        if ($request->filled('date_from')) $query->whereDate('production_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('production_date', '<=', $request->date_to);
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('report_number', 'like', '%'.$request->search.'%')
                  ->orWhere('product_name', 'like', '%'.$request->search.'%')
                  ->orWhere('product_code', 'like', '%'.$request->search.'%');
            });
        }

        $reports = $query->latest('submitted_at')->paginate(20)->withQueryString();
        return view('leader.reports', compact('reports'));
    }

    public function show(RejectReport $report)
    {
        $report->load(['details', 'operator', 'lineLeader']);
        return view('leader.report-detail', compact('report'));
    }

    public function verify(Request $request, RejectReport $report)
    {
        $request->validate([
            'leader_notes' => 'nullable|string|max:1000',
        ]);

        if ($report->status !== 'pending') {
            return back()->with('error', 'Hanya laporan yang diedit operator yang perlu diverifikasi.');
        }

        $report->update([
            'status' => 'verified',
            'line_leader_id' => Auth::id(),
            'leader_notes' => $request->leader_notes,
            'edit_history' => $report->historyWithDecision('verified', $request->leader_notes, Auth::user()->name),
            'verified_at' => now(),
        ]);

        return redirect()->route('leader.dashboard')
            ->with('success', 'Laporan ' . $report->report_number . ' berhasil diverifikasi.');
    }

    public function reject(Request $request, RejectReport $report)
    {
        $request->validate([
            'leader_notes' => 'required|string|max:1000',
        ]);

        if ($report->status !== 'pending') {
            return back()->with('error', 'Hanya laporan yang diedit operator yang perlu diverifikasi.');
        }

        $report->update([
            'status' => 'rejected',
            'line_leader_id' => Auth::id(),
            'leader_notes' => $request->leader_notes,
            'edit_history' => $report->historyWithDecision('rejected', $request->leader_notes, Auth::user()->name),
            'verified_at' => now(),
        ]);

        return redirect()->route('leader.dashboard')
            ->with('success', 'Laporan ' . $report->report_number . ' telah dikembalikan ke operator.');
    }
}
