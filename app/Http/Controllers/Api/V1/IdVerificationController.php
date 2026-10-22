<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\IdVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreIdVerificationRequest;
use App\Http\Resources\IdVerificationResource;
use App\Support\AdminAlerts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IdVerificationController extends Controller
{
    public function show(Request $request): IdVerificationResource|JsonResponse
    {
        $latest = $request->user()->latestIdVerification();

        if ($latest === null) {
            return response()->json(['data' => null]);
        }

        return new IdVerificationResource($latest);
    }

    public function store(StoreIdVerificationRequest $request): IdVerificationResource
    {
        $latest = $request->user()->latestIdVerification();

        if ($latest !== null && $latest->status !== IdVerificationStatus::Rejected) {
            throw ValidationException::withMessages([
                'document_type' => $latest->status === IdVerificationStatus::Approved
                    ? 'You are already ID verified.'
                    : 'Your submission is still under review.',
            ]);
        }

        // Stored on the `local` disk (storage/app/private — never publicly
        // served, unlike trip/good/profile photos which use the `public`
        // disk) since this is a government ID document. Only reachable by
        // an authorized reviewer reading the file straight off disk.
        $path = $request->file('photo')->store('id-verification-photos', 'local');

        $verification = $request->user()->idVerifications()->create([
            'document_type' => $request->validated('document_type'),
            'document_photo_url' => $path,
            'status' => IdVerificationStatus::Pending,
        ]);

        AdminAlerts::send(
            'admin_alert_new_id_verification',
            'New ID verification',
            "{$request->user()->name} submitted a {$verification->document_type->value} for review.",
            ['id_verification_id' => $verification->id],
        );

        return new IdVerificationResource($verification);
    }
}
