@extends('admin.layout')

@section('title', 'Feature Requests')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0">Feature Requests</h3>
        <div class="d-flex flex-wrap gap-2">
            <div class="btn-group">
                @foreach (['all' => 'All', 'open' => 'Open', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $value => $label)
                    <a href="{{ route('admin.feature-requests.index', ['status' => $value, 'sort' => $sort, 'search' => $search]) }}"
                       class="btn btn-sm {{ $status === $value ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.feature-requests.index', ['status' => $status, 'sort' => 'top', 'search' => $search]) }}"
                   class="btn btn-sm {{ $sort === 'top' ? 'btn-dark' : 'btn-outline-secondary' }}">Most voted</a>
                <a href="{{ route('admin.feature-requests.index', ['status' => $status, 'sort' => 'new', 'search' => $search]) }}"
                   class="btn btn-sm {{ $sort === 'new' ? 'btn-dark' : 'btn-outline-secondary' }}">Newest</a>
            </div>
            <form method="GET" action="{{ route('admin.feature-requests.index') }}" class="d-flex gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <input type="hidden" name="sort" value="{{ $sort }}">
                <label for="search" class="visually-hidden">Search feature requests</label>
                <input type="search" id="search" name="search" class="form-control form-control-sm" placeholder="Search title or description" value="{{ $search }}">
                <button class="btn btn-sm btn-dark">Search</button>
            </form>
        </div>
    </div>

    <div class="card stat-card">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Request</th>
                    <th>Author</th>
                    <th class="text-center">Votes</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($featureRequests as $request)
                    <tr>
                        <td>{{ $request->id }}</td>
                        <td style="max-width: 360px;">
                            <div class="fw-semibold">{{ $request->title }}</div>
                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($request->description, 160) }}</div>
                        </td>
                        <td>{{ $request->user?->name }}<br><span class="text-muted small">{{ $request->user?->email }}</span></td>
                        <td class="text-center text-nowrap">
                            <span class="text-success">▲ {{ $request->upvotes_count }}</span>
                            <span class="text-danger ms-2">▼ {{ $request->downvotes_count }}</span>
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.feature-requests.update', $request) }}" class="d-flex gap-1">
                                @csrf @method('PATCH')
                                <select name="status" class="form-select form-select-sm" aria-label="Status for request {{ $request->id }}">
                                    @foreach ($statuses as $option)
                                        <option value="{{ $option->value }}" @selected($request->status === $option)>{{ str($option->value)->replace('_', ' ')->title() }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-outline-dark">Save</button>
                            </form>
                        </td>
                        <td class="small text-muted">{{ $request->created_at->diffForHumans() }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('admin.feature-requests.destroy', $request) }}" onsubmit="return confirm('Delete this feature request and its votes?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Nothing here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $featureRequests->links() }}</div>
@endsection
