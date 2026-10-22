<?php

namespace App\Http\Controllers\Admin;

use App\Enums\FeatureRequestStatus;
use App\Enums\VoteDirection;
use App\Http\Controllers\Controller;
use App\Models\FeatureRequest;
use App\Notifications\FeatureRequestStatusChanged;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FeatureRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $sort = $request->query('sort', 'top');
        $search = trim((string) $request->query('search', ''));

        $query = FeatureRequest::query()
            ->with('user')
            ->withCount([
                'votes as upvotes_count' => fn ($q) => $q->where('direction', VoteDirection::Up),
                'votes as downvotes_count' => fn ($q) => $q->where('direction', VoteDirection::Down),
            ])
            ->when($status !== 'all', fn ($q) => $q->where('status', FeatureRequestStatus::from($status)))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")));

        $sort === 'new'
            ? $query->orderByDesc('id')
            : $query->orderByRaw('(upvotes_count - downvotes_count) desc')->orderByDesc('id');

        return view('admin.feature-requests.index', [
            'featureRequests' => $query->paginate(20)->withQueryString(),
            'status' => $status,
            'sort' => $sort,
            'search' => $search,
            'statuses' => FeatureRequestStatus::cases(),
        ]);
    }

    public function update(Request $request, FeatureRequest $featureRequest): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::enum(FeatureRequestStatus::class)],
        ]);
        $new = FeatureRequestStatus::from($data['status']);

        if ($featureRequest->status === $new) {
            return back()->with('status', "Request #{$featureRequest->id} is already {$new->value}.");
        }

        $featureRequest->update(['status' => $new]);
        $featureRequest->user?->notify(new FeatureRequestStatusChanged($featureRequest, $new));

        return back()->with('status', "Request #{$featureRequest->id} moved to ".str($new->value)->replace('_', ' ').'. The author was notified.');
    }

    public function destroy(FeatureRequest $featureRequest): RedirectResponse
    {
        $featureRequest->delete();

        return back()->with('status', "Request #{$featureRequest->id} deleted.");
    }
}
