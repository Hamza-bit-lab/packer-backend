<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FeatureRequestStatus;
use App\Enums\IdVerificationStatus;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\FeatureRequest;
use App\Models\Good;
use App\Models\IdVerification;
use App\Models\Report;
use App\Models\Trip;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'trips' => Trip::count(),
                'goods' => Good::count(),
                'bookings' => Booking::count(),
                'pending_reports' => Report::where('status', ReportStatus::Pending)->count(),
                'pending_id_verifications' => IdVerification::where('status', IdVerificationStatus::Pending)->count(),
                'open_feature_requests' => FeatureRequest::where('status', FeatureRequestStatus::Open)->count(),
                'banned_users' => User::where('is_banned', true)->count(),
            ],
        ]);
    }
}
