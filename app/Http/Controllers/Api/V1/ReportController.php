<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReportRequest;
use App\Models\User;
use App\Support\AdminAlerts;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request, User $user): JsonResponse
    {
        if ($request->user()->id === $user->id) {
            throw ValidationException::withMessages(['reason' => 'You cannot report yourself.']);
        }

        $report = $request->user()->reportsFiled()->create([
            'reported_user_id' => $user->id,
            'booking_id' => $request->validated('booking_id'),
            'reason' => $request->validated('reason'),
            'details' => $request->validated('details'),
        ]);

        AdminAlerts::send(
            'admin_alert_new_report',
            'New user report',
            "{$request->user()->name} reported {$user->name} ("
                .str($request->validated('reason'))->replace('_', ' ').').',
            ['report_id' => $report->id],
        );

        return response()->json(['message' => 'Thanks — your report has been submitted for review.'], 201);
    }
}
