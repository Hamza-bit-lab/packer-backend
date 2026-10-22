@extends('admin.layout')

@section('title', 'Reports')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Reports</h3>
        <div class="btn-group">
            @foreach (['pending' => 'Pending', 'reviewed' => 'Reviewed', 'dismissed' => 'Dismissed', 'all' => 'All'] as $value => $label)
                <a href="{{ route('admin.reports.index', ['status' => $value]) }}"
                   class="btn btn-sm {{ $status === $value ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div class="card stat-card">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reported User</th>
                    <th>Reported By</th>
                    <th>Reason</th>
                    <th>Details</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr>
                        <td>{{ $report->id }}</td>
                        <td>{{ $report->reportedUser?->name }}<br><span class="text-muted small">{{ $report->reportedUser?->email }}</span></td>
                        <td>{{ $report->reporter?->name }}</td>
                        <td>{{ str($report->reason)->replace('_', ' ')->title() }}</td>
                        <td class="small" style="max-width: 240px;">{{ $report->details }}</td>
                        <td><span class="badge text-bg-{{ $report->status->value === 'pending' ? 'warning' : ($report->status->value === 'reviewed' ? 'success' : 'secondary') }}">{{ $report->status->value }}</span></td>
                        <td class="small text-muted">{{ $report->created_at->diffForHumans() }}</td>
                        <td class="text-end">
                            @if ($report->status->value === 'pending')
                                <form method="POST" action="{{ route('admin.reports.update', $report) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="decision" value="reviewed">
                                    <button class="btn btn-sm btn-outline-success">Mark Reviewed</button>
                                </form>
                                <form method="POST" action="{{ route('admin.reports.update', $report) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="decision" value="dismissed">
                                    <button class="btn btn-sm btn-outline-secondary">Dismiss</button>
                                </form>
                            @endif
                            @if ($report->reportedUser)
                                <form method="POST" action="{{ route('admin.users.toggle-ban', $report->reportedUser) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-danger">{{ $report->reportedUser->is_banned ? 'Unban' : 'Ban' }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Nothing here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $reports->links() }}</div>
@endsection
