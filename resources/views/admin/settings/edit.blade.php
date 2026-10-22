@extends('admin.layout')

@section('title', 'Settings')

@section('content')
    <h3 class="mb-4">Settings</h3>

    <div class="row g-4">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="card stat-card p-4">
                @csrf @method('PUT')
                <h5 class="mb-1">Admin alerts</h5>
                <p class="text-muted small mb-3">Every admin gets an in-app notification and a phone push for each enabled alert below.</p>

                @foreach ([
                    'admin_alert_new_report' => ['New user reports', 'Someone reports another user.'],
                    'admin_alert_new_id_verification' => ['New ID verifications', 'A user submits an ID document for review.'],
                    'admin_alert_new_feature_request' => ['New feature requests', 'A user posts a new idea on the feature board.'],
                    'admin_alert_email' => ['Also send alerts by email', 'Adds an email copy of every alert above to each admin.'],
                ] as $key => [$label, $hint])
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="{{ $key }}" name="{{ $key }}" value="1" @checked($settings[$key])>
                        <label class="form-check-label fw-semibold" for="{{ $key }}">{{ $label }}</label>
                        <div class="text-muted small">{{ $hint }}</div>
                    </div>
                @endforeach

                <div><button class="btn btn-dark">Save settings</button></div>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card stat-card p-4">
                <h5 class="mb-1">Admins</h5>
                <p class="text-muted small mb-3">People who can open this dashboard and receive admin alerts. Grant or remove access from the <a href="{{ route('admin.users.index') }}">Users</a> page.</p>
                <ul class="list-group list-group-flush">
                    @foreach ($admins as $admin)
                        <li class="list-group-item px-0">
                            <div class="fw-semibold">{{ $admin->name }}</div>
                            <div class="text-muted small">{{ $admin->email }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
