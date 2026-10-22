@extends('admin.layout')

@section('title', 'Listings')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0">Listings</h3>
        <div class="d-flex flex-wrap gap-2">
            <div class="btn-group">
                <a href="{{ route('admin.listings.index', ['type' => 'trips', 'search' => $search]) }}"
                   class="btn btn-sm {{ $type === 'trips' ? 'btn-dark' : 'btn-outline-secondary' }}">Trips</a>
                <a href="{{ route('admin.listings.index', ['type' => 'goods', 'search' => $search]) }}"
                   class="btn btn-sm {{ $type === 'goods' ? 'btn-dark' : 'btn-outline-secondary' }}">Items</a>
            </div>
            <form method="GET" action="{{ route('admin.listings.index') }}" class="d-flex gap-2">
                <input type="hidden" name="type" value="{{ $type }}">
                <label for="search" class="visually-hidden">Search listings</label>
                <input type="search" id="search" name="search" class="form-control form-control-sm" placeholder="City, name or email" value="{{ $search }}">
                <button class="btn btn-sm btn-dark">Search</button>
            </form>
        </div>
    </div>

    <div class="card stat-card">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Route</th>
                    <th>{{ $type === 'trips' ? 'Traveler' : 'Sender' }}</th>
                    <th>{{ $type === 'trips' ? 'Departure' : 'Item' }}</th>
                    <th>{{ $type === 'trips' ? 'Max weight' : 'Weight' }}</th>
                    <th class="text-center">Bookings</th>
                    <th>Posted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($listings as $listing)
                    <tr>
                        <td>{{ $listing->id }}</td>
                        <td>{{ $listing->from_city }} → {{ $listing->to_city }}</td>
                        <td>{{ $listing->user?->name }}<br><span class="text-muted small">{{ $listing->user?->email }}</span></td>
                        <td class="small" style="max-width: 240px;">
                            @if ($type === 'trips')
                                {{ $listing->departure_date_time?->format('d M Y, H:i') }}
                            @else
                                {{ \Illuminate\Support\Str::limit($listing->item_description, 80) }}
                            @endif
                        </td>
                        <td>{{ rtrim(rtrim(number_format((float) ($type === 'trips' ? $listing->max_weight_kg : $listing->weight_kg), 2), '0'), '.') }} kg</td>
                        <td class="text-center">{{ $listing->bookings_count }}</td>
                        <td class="small text-muted">{{ $listing->created_at->diffForHumans() }}</td>
                        <td class="text-end">
                            <form method="POST"
                                  action="{{ $type === 'trips' ? route('admin.listings.trips.destroy', $listing) : route('admin.listings.goods.destroy', $listing) }}"
                                  onsubmit="return confirm('Remove this listing? Its bookings are cancelled and the people involved are notified.');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Nothing here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $listings->links() }}</div>
@endsection
