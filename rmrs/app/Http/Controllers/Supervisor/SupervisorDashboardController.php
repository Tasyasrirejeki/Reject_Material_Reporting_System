<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\RejectReport;
use App\Models\User;
use App\Models\RejectDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupervisorDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_reports' => RejectReport::count(),
            'pending' => RejectReport::where('status', 'pending')->count(),
            'verified' => RejectReport::where('status', 'verified')->count(),
            'rejected' => RejectReport::where('status', 'rejected')->count(),
            'total_reject_this_month' => RejectReport::whereMonth('production_date', now()->month)
                ->whereYear('production_date', now()->year)
                ->counted()->sum('total_reject_qty'),
            'total_operators' => User::where('role', 'operator')->where('is_active', true)->count(),
        ];

        // Monthly trend (last 6 months)
        $monthlyTrend = RejectReport::selectRaw('YEAR(production_date) as year, MONTH(production_date) as month, SUM(total_reject_qty) as total, COUNT(*) as reports')
            ->where('production_date', '>=', now()->subMonths(5)->startOfMonth())
            ->counted()
            ->groupBy('year', 'month')
            ->orderBy('year')->orderBy('month')
            ->get();

        // By department this month
        $deptStats = RejectReport::selectRaw('department, SUM(total_reject_qty) as total_qty, COUNT(*) as total_reports')
            ->whereMonth('production_date', now()->month)
            ->whereYear('production_date', now()->year)
            ->counted()
            ->groupBy('department')
            ->get();

        // Top defect types
        $topDefects = RejectDetail::selectRaw('defect_type, defect_category, SUM(quantity) as total')
            ->whereHas('report', fn($q) => $q->counted()
                ->whereMonth('production_date', now()->month))
            ->groupBy('defect_type', 'defect_category')
            ->orderByDesc('total')
            ->take(8)
            ->get();

        // Daily trend last 14 days
        $dailyTrend = RejectReport::selectRaw('DATE(production_date) as date, SUM(total_reject_qty) as total')
            ->where('production_date', '>=', now()->subDays(13))
            ->counted()
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        // Recent reports
        $recentReports = RejectReport::with(['operator', 'details'])
            ->latest('submitted_at')->take(8)->get();

        return view('supervisor.dashboard', compact(
            'stats', 'monthlyTrend', 'deptStats', 'topDefects', 'dailyTrend', 'recentReports'
        ));
    }

    public function reports(Request $request)
    {
        $query = RejectReport::with(['operator', 'details', 'lineLeader']);

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('department')) $query->where('department', $request->department);
        if ($request->filled('shift')) $query->where('shift', $request->shift);
        if ($request->filled('date_from')) $query->whereDate('production_date', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('production_date', '<=', $request->date_to);
        if ($request->filled('operator_id')) $query->where('operator_id', $request->operator_id);
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('report_number', 'like', '%'.$request->search.'%')
                  ->orWhere('product_name', 'like', '%'.$request->search.'%')
                  ->orWhere('product_code', 'like', '%'.$request->search.'%');
            });
        }

        $reports = $query->latest('production_date')->paginate(25)->withQueryString();
        $operators = User::where('role', 'operator')->orderBy('name')->get();

        return view('supervisor.reports', compact('reports', 'operators'));
    }

}
