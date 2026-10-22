@extends('admin.layout')

@section('title', 'ID Verifications')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">ID Verifications</h3>
        <div class="btn-group">
            @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $value => $label)
                <a href="{{ route('admin.id-verifications.index', ['status' => $value]) }}"
                   class="btn btn-sm {{ $status === $value ? 'btn-dark' : 'btn-outline-secondary' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>

    <div class="row g-3">
        @forelse ($verifications as $verification)
            <div class="col-md-6 col-lg-4">
                <div class="card stat-card p-3 h-100">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="fw-bold">{{ $verification->user->name }}</div>
                            <div class="text-muted small">{{ $verification->user->email }}</div>
                        </div>
                        <span class="badge text-bg-{{ $verification->status->value === 'pending' ? 'warning' : ($verification->status->value === 'approved' ? 'success' : 'danger') }}">{{ $verification->status->value }}</span>
                    </div>
                    <div class="small text-muted mb-2">{{ str($verification->document_type->value)->upper() }} · submitted {{ $verification->created_at->diffForHumans() }}</div>
                    <a href="{{ route('admin.id-verifications.photo', $verification) }}" target="_blank">
                        <img src="{{ route('admin.id-verifications.photo', $verification) }}" alt="Submitted {{ $verification->document_type->value }} document" class="img-fluid rounded mb-2" style="max-height: 200px; object-fit: cover;">
                    </a>
                    @if ($verification->status->value === 'rejected' && $verification->rejection_reason)
                        <div class="small text-danger mb-2">Reason: {{ $verification->rejection_reason }}</div>
                    @endif
                    @if ($verification->status->value === 'pending')
                        <form method="POST" action="{{ route('admin.id-verifications.update', $verification) }}" class="d-flex gap-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="decision" value="approve">
                            <button class="btn btn-sm btn-success flex-fill">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('admin.id-verifications.update', $verification) }}" class="mt-2">
                            @csrf @method('PATCH')
                            <input type="hidden" name="decision" value="reject">
                            <label for="rejection_reason_{{ $verification->id }}" class="visually-hidden">Rejection reason</label>
                            <input type="text" id="rejection_reason_{{ $verification->id }}" name="rejection_reason" class="form-control form-control-sm mb-2" placeholder="Rejection reason" required>
                            <button class="btn btn-sm btn-outline-danger w-100">Reject</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="text-muted py-4">Nothing here.</div>
        @endforelse
    </div>

    <div class="mt-3">{{ $verifications->links() }}</div>
@endsection
