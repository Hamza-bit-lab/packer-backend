<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Notifications\ReportResolved;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $query = Report::query()->with(['reporter', 'reportedUser', 'booking'])->latest();

        if ($status !== 'all') {
            $query->where('status', ReportStatus::from($status));
        }

        return view('admin.reports.index', [
            'reports' => $query->paginate(20)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'string', 'in:reviewed,dismissed'],
        ]);

        $wasPending = $report->status === ReportStatus::Pending;

        $report->forceFill([
            'status' => ReportStatus::from($data['decision']),
            'reviewed_at' => now(),
        ])->save();

        if ($wasPending) {
            $report->reporter?->notify(new ReportResolved($report));
        }

        return back()->with('status', "Report #{$report->id} marked {$data['decision']}.");
    }
}
