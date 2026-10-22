@extends('admin.layout')

@section('title', 'Users')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Users</h3>
        <form method="GET" action="{{ route('admin.users.index') }}" class="d-flex gap-2">
            <label for="search" class="visually-hidden">Search users</label>
            <input type="search" id="search" name="search" class="form-control form-control-sm" placeholder="Search name or email" value="{{ $search }}">
            <button class="btn btn-sm btn-dark">Search</button>
        </form>
    </div>

    <div class="card stat-card">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Rating</th>
                    <th>Verified</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ number_format((float) $user->average_rating, 1) }} ({{ $user->total_reviews }})</td>
                        <td>
                            @if ($user->hasVerifiedEmail())<span class="badge text-bg-success">Email</span>@endif
                            @if ($user->isIdVerified())<span class="badge text-bg-info">ID</span>@endif
                        </td>
                        <td>
                            @if ($user->is_admin)
                                <span class="badge text-bg-dark">Admin</span>
                            @elseif ($user->is_banned)
                                <span class="badge text-bg-danger">Banned</span>
                            @else
                                <span class="badge text-bg-success">Active</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $user->created_at->format('M Y') }}</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1">
                                @unless ($user->is_admin || $user->id === auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle-ban', $user) }}">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-outline-danger">{{ $user->is_banned ? 'Unban' : 'Ban' }}</button>
                                    </form>
                                @endunless
                                @if ($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" onsubmit="return confirm('{{ $user->is_admin ? 'Remove' : 'Grant' }} admin access for {{ addslashes($user->name) }}?');">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm btn-outline-dark">{{ $user->is_admin ? 'Remove admin' : 'Make admin' }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
@endsection
