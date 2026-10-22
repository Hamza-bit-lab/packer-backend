<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IdVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\IdVerification;
use App\Notifications\IdVerificationReviewed;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IdVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $query = IdVerification::query()->with('user')->latest();

        if ($status !== 'all') {
            $query->where('status', IdVerificationStatus::from($status));
        }

        return view('admin.id-verifications.index', [
            'verifications' => $query->paginate(20)->withQueryString(),
            'status' => $status,
        ]);
    }

    public function photo(IdVerification $idVerification): StreamedResponse
    {
        return Storage::disk('local')->response($idVerification->document_photo_url);
    }

    public function update(Request $request, IdVerification $idVerification): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'string', 'in:approve,reject'],
            'rejection_reason' => [Rule::requiredIf($request->input('decision') === 'reject'), 'nullable', 'string', 'max:500'],
        ]);

        $idVerification->loadMissing('user');

        if ($data['decision'] === 'approve') {
            $idVerification->update(['status' => IdVerificationStatus::Approved, 'reviewed_at' => now()]);
        } else {
            $idVerification->update([
                'status' => IdVerificationStatus::Rejected,
                'rejection_reason' => $data['rejection_reason'],
                'reviewed_at' => now(),
            ]);
        }

        $idVerification->user->notify(new IdVerificationReviewed($idVerification));

        $verb = $data['decision'] === 'approve' ? 'approved' : 'rejected';

        return back()->with('status', "ID verification #{$idVerification->id} {$verb}.");
    }
}
