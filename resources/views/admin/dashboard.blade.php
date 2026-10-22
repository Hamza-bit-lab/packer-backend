@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <h3 class="mb-4">Dashboard</h3>

    <div class="row g-3">
        @foreach ([
            ['label' => 'Users', 'value' => $stats['users']],
            ['label' => 'Trips', 'value' => $stats['trips'], 'link' => route('admin.listings.index', ['type' => 'trips'])],
            ['label' => 'Goods', 'value' => $stats['goods'], 'link' => route('admin.listings.index', ['type' => 'goods'])],
            ['label' => 'Bookings', 'value' => $stats['bookings']],
            ['label' => 'Pending Reports', 'value' => $stats['pending_reports'], 'link' => route('admin.reports.index')],
            ['label' => 'Pending ID Verifications', 'value' => $stats['pending_id_verifications'], 'link' => route('admin.id-verifications.index')],
            ['label' => 'Open Feature Requests', 'value' => $stats['open_feature_requests'], 'link' => route('admin.feature-requests.index', ['status' => 'open'])],
            ['label' => 'Banned Users', 'value' => $stats['banned_users'], 'link' => route('admin.users.index')],
        ] as $card)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card stat-card p-3 h-100">
                    <div class="text-muted small">{{ $card['label'] }}</div>
                    <div class="fs-2 fw-bold">{{ $card['value'] }}</div>
                    @isset($card['link'])
                        <a href="{{ $card['link'] }}" class="stretched-link"></a>
                    @endisset
                </div>
            </div>
        @endforeach
    </div>
@endsection
