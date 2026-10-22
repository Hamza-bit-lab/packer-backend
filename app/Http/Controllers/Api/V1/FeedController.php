<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class FeedController extends Controller
{
    public function index(Request $request, TripController $tripController, GoodController $goodController): AnonymousResourceCollection
    {
        $type = $request->string('type', 'travelers')->toString();

        return match ($type) {
            'travelers' => $tripController->index($request),
            'goods' => $goodController->index($request),
            default => throw ValidationException::withMessages([
                'type' => 'The type must be either "travelers" or "goods".',
            ]),
        };
    }
}
