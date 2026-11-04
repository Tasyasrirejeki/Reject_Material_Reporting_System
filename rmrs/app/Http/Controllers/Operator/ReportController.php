<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\RejectReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * List operator's own reports.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = RejectReport::where('operator_id', $user->id)->with('details');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('production_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('production_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('report_number', 'like', '%'.$request->search.'%')
                  ->orWhere('product_name', 'like', '%'.$request->search.'%')
                  ->orWhere('product_code', 'like', '%'.$request->search.'%');
            });
        }

        $reports = $query->latest('submitted_at')->paginate(15)->withQueryString();

        return view('operator.reports.index', compact('reports'));
    }

    /**
     * Show form to create new report.
     */
    public function create()
    {
        $user = Auth::user();
        // Operator boleh bekerja di Lenscase maupun Pouching.
        // Departemen default = departemen profil user (atau lenscase jika 'all').
        $department = old('department', $user->department === 'all' ? 'lenscase' : $user->department);
        $currentShift = RejectReport::currentShift();
        $report = null;
        $existingDetails = $this->formDetails(old('details'));

        return view('operator.reports.create', compact('department', 'currentShift', 'report', 'existingDetails'));
    }

    /**
     * Store new report.
     * Laporan baru berstatus "submitted" dan TIDAK perlu verifikasi Line Leader.
     */
    public function store(Request $request)
    {
        $request->validate($this->rules());

        DB::beginTransaction();
        try {
            $report = RejectReport::create([
                'report_number' => RejectReport::generateReportNumber($request->department),
                'operator_id' => Auth::id(),
                'department' => $request->department,
                'shift' => $request->shift,
                'production_date' => $request->production_date,
                'product_name' => $request->product_name,
                'product_code' => $request->product_code,
                'total_production_qty' => $request->total_production_qty,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            foreach ($request->details as $detail) {
                $report->details()->create($this->detailPayload($detail));
            }

            $report->recalculateTotals();

            DB::commit();
            return redirect()->route('operator.reports.show', $report)
                ->with('success', 'Laporan reject berhasil dikirim! Nomor Laporan: ' . $report->report_number);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan laporan: ' . $e->getMessage());
        }
    }

    /**
     * Show single report detail.
     */
    public function show(RejectReport $report)
    {
        $this->authorizeOwner($report);

        $report->load(['details', 'operator', 'lineLeader']);
        return view('operator.reports.show', compact('report'));
    }

    /**
     * Show form to edit an already-submitted report.
     */
    public function edit(RejectReport $report)
    {
        $this->authorizeOwner($report);

        $report->load('details');
        $department = old('department', $report->department);
        $currentShift = $report->shift;
        $existingDetails = $this->formDetails(old('details') ?? $report->details->map(fn ($d) => [
            'defect_type' => $d->defect_type,
            'defect_category' => $d->defect_category,
            'quantity' => $d->quantity,
            'defect_location' => $d->defect_location,
            'description' => $d->description,
        ])->all());

        return view('operator.reports.create', compact('department', 'currentShift', 'report', 'existingDetails'));
    }

    /**
     * Update a submitted report.
     * Setiap pengeditan mengubah status menjadi "pending" -> menunggu verifikasi Line Leader.
     */
    public function update(Request $request, RejectReport $report)
    {
        $this->authorizeOwner($report);
        $request->validate($this->rules());

        DB::beginTransaction();
        try {
            $beforeHeader = $report->editableSnapshot();
            $beforeDefects = $report->defectsSnapshot();
            $beforeTotal = (int) $report->total_reject_qty;
            $previousStatus = $report->status;
            $previousLeaderNotes = $report->leader_notes;

            $report->update([
                'department' => $request->department,
                'shift' => $request->shift,
                'production_date' => $request->production_date,
                'product_name' => $request->product_name,
                'product_code' => $request->product_code,
                'total_production_qty' => $request->total_production_qty,
            ]);

            $report->details()->delete();
            foreach ($request->details as $detail) {
                $report->details()->create($this->detailPayload($detail));
            }
            $report->recalculateTotals();
            $report->refresh();

            $afterHeader = $report->editableSnapshot();
            $afterDefects = $report->defectsSnapshot();

            $changes = [];
            foreach ($afterHeader as $field => $new) {
                if (($beforeHeader[$field] ?? null) !== $new) {
                    $changes[] = ['field' => $field, 'old' => $beforeHeader[$field] ?? '-', 'new' => $new];
                }
            }
            $defectsChanged = $beforeDefects !== $afterDefects;

            if (!$changes && !$defectsChanged) {
                DB::rollBack();
                return redirect()->route('operator.reports.show', $report)
                    ->with('error', 'Tidak ada perubahan pada laporan, jadi tidak perlu verifikasi.');
            }

            $history = $report->edit_history ?? [];
            $history[] = [
                'at' => now()->toDateTimeString(),
                'by' => Auth::user()->name,
                'changes' => $changes,
                'defects_changed' => $defectsChanged,
                'defects_before' => $beforeDefects,
                'defects_after' => $afterDefects,
                'total_before' => $beforeTotal,
                'total_after' => (int) $report->total_reject_qty,
                'previous_status' => $previousStatus,
                'previous_leader_notes' => $previousLeaderNotes,
            ];

            $report->update([
                'status' => 'pending',
                'edited_at' => now(),
                'edit_count' => $report->edit_count + 1,
                'edit_history' => $history,
                // Hasil verifikasi sebelumnya sudah tidak berlaku setelah laporan diedit
                'line_leader_id' => null,
                'leader_notes' => null,
                'verified_at' => null,
            ]);

            DB::commit();
            return redirect()->route('operator.reports.show', $report)
                ->with('success', 'Laporan berhasil diperbarui dan dikirim ke Line Leader untuk diverifikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui laporan: ' . $e->getMessage());
        }
    }

    private function authorizeOwner(RejectReport $report): void
    {
        if ($report->operator_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengakses laporan ini.');
        }
    }

    private function rules(): array
    {
        return [
            'department' => ['required', 'in:lenscase,pouching'],
            'shift' => ['required', 'in:1,2'],
            'production_date' => ['required', 'date', 'before_or_equal:today'],
            'product_name' => ['required', 'string', 'max:255'],
            'product_code' => ['nullable', 'string', 'max:100'],
            'total_production_qty' => ['required', 'integer', 'min:1'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.defect_type' => ['required', 'string', 'max:255'],
            'details.*.quantity' => ['required', 'integer', 'min:1'],
            'details.*.description' => ['nullable', 'string', 'max:500'],
        ];
    }

    private function detailPayload(array $detail): array
    {
        return [
            'defect_type' => $detail['defect_type'],
            'quantity' => $detail['quantity'],
            'description' => $detail['description'] ?? null,
        ];
    }

    /** Normalise defect rows for pre-filling the form (edit mode / validation errors). */
    private function formDetails($details): array
    {
        return array_values((array) ($details ?? []));
    }
}
