<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\FeatureRequestStatus;
use App\Enums\VoteDirection;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFeatureRequestRequest;
use App\Http\Resources\FeatureRequestResource;
use App\Models\FeatureRequest;
use App\Models\FeatureRequestVote;
use App\Notifications\FeatureRequestMilestone;
use App\Support\AdminAlerts;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class FeatureRequestController extends Controller
{
    /**
     * Upvote counts at which the author gets a "your idea is popular"
     * notification.
     *
     * @var array<int, int>
     */
    private const VOTE_MILESTONES = [5, 10, 25, 50, 100];

    /**
     * A shared board — every user sees every request (not just their own),
     * so people can search before posting a duplicate.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $search = trim((string) $request->query('search', ''));

        $requests = $this->withVoteData(FeatureRequest::query(), $request)
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")))
            // id, not just created_at — two requests submitted in the same
            // second would otherwise tie and sort unpredictably.
            ->orderByDesc('id')
            ->paginate($request->integer('per_page', 30));

        return FeatureRequestResource::collection($requests);
    }

    public function store(StoreFeatureRequestRequest $request): FeatureRequestResource
    {
        // Set status explicitly rather than leaning on the column's DB
        // default — Eloquent has no way to know a default applied after an
        // insert, so the in-memory model (what FeatureRequestResource
        // renders below) would otherwise still show status as null.
        $featureRequest = $request->user()->featureRequests()->create([
            ...$request->validated(),
            'status' => FeatureRequestStatus::Open,
        ]);
        $featureRequest->setRelation('user', $request->user());
        $featureRequest->setRelation('votes', collect());
        $featureRequest->upvotes_count = 0;
        $featureRequest->downvotes_count = 0;

        AdminAlerts::send(
            'admin_alert_new_feature_request',
            'New feature request',
            "{$request->user()->name}: {$featureRequest->title}",
            ['feature_request_id' => $featureRequest->id],
        );

        return new FeatureRequestResource($featureRequest);
    }

    /**
     * Casting the same direction again removes the vote; casting the other
     * direction switches it — the standard "tap to toggle" behaviour.
     */
    public function vote(Request $request, FeatureRequest $featureRequest): FeatureRequestResource
    {
        $validated = $request->validate([
            'direction' => ['required', Rule::enum(VoteDirection::class)],
        ]);
        $direction = VoteDirection::from($validated['direction']);
        $user = $request->user();

        $existing = FeatureRequestVote::query()
            ->where('feature_request_id', $featureRequest->id)
            ->where('user_id', $user->id)
            ->first();

        $upvoteAdded = false;

        if ($existing?->direction === $direction) {
            $existing->delete();
        } elseif ($existing) {
            $existing->update(['direction' => $direction]);
            $upvoteAdded = $direction === VoteDirection::Up;
        } else {
            FeatureRequestVote::create([
                'feature_request_id' => $featureRequest->id,
                'user_id' => $user->id,
                'direction' => $direction,
            ]);
            $upvoteAdded = $direction === VoteDirection::Up;
        }

        if ($upvoteAdded && $featureRequest->user_id !== $user->id) {
            $upvotes = $featureRequest->votes()->where('direction', VoteDirection::Up)->count();

            if (in_array($upvotes, self::VOTE_MILESTONES, true)) {
                $featureRequest->user->notify(new FeatureRequestMilestone($featureRequest, $upvotes));
            }
        }

        return new FeatureRequestResource(
            $this->withVoteData(FeatureRequest::query(), $request)->findOrFail($featureRequest->id)
        );
    }

    /**
     * Eager-loads exactly what FeatureRequestResource needs to render vote
     * state: the submitter, per-direction counts, and — scoped to just
     * this user — their own vote (if any), so the resource never has to
     * guess "did they vote" from the full votes list.
     */
    private function withVoteData(Builder $query, Request $request): Builder
    {
        return $query
            ->with(['user', 'votes' => fn ($q) => $q->where('user_id', $request->user()->id)])
            ->withCount([
                'votes as upvotes_count' => fn ($q) => $q->where('direction', VoteDirection::Up),
                'votes as downvotes_count' => fn ($q) => $q->where('direction', VoteDirection::Down),
            ]);
    }
}
