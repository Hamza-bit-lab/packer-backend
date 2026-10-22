<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Resources\RatingResource;
use App\Models\Booking;

class RatingController extends Controller
{
    public function store(StoreRatingRequest $request, Booking $booking): RatingResource
    {
        $rateeId = $request->user()->id === $booking->sender_id
            ? $booking->traveler_id
            : $booking->sender_id;

        $rating = $booking->ratings()->create([
            'rater_id' => $request->user()->id,
            'ratee_id' => $rateeId,
            'stars' => $request->validated('stars'),
            'comment' => $request->validated('comment'),
        ]);

        return new RatingResource($rating->load(['rater', 'ratee']));
    }
}
