<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Packer Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body { background: #f4f5f7; }
        .admin-sidebar { min-height: 100vh; background: #14171f; color: #fff; }
        .admin-sidebar a { color: #c7cad1; text-decoration: none; display: block; padding: .6rem 1rem; border-radius: .5rem; }
        .admin-sidebar a.active, .admin-sidebar a:hover { background: #262b38; color: #fff; }
        .stat-card { border: 0; border-radius: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,.06); }
    </style>
</head>
<body>
    <div class="d-flex">
        <nav class="admin-sidebar p-3" style="width: 230px; flex-shrink: 0;">
            <div class="fw-bold fs-5 mb-4 px-1">📦 Packer Admin</div>
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">Reports</a>
            <a href="{{ route('admin.id-verifications.index') }}" class="{{ request()->routeIs('admin.id-verifications.*') ? 'active' : '' }}">ID Verifications</a>
            <a href="{{ route('admin.feature-requests.index') }}" class="{{ request()->routeIs('admin.feature-requests.*') ? 'active' : '' }}">Feature Requests</a>
            <a href="{{ route('admin.listings.index') }}" class="{{ request()->routeIs('admin.listings.*') ? 'active' : '' }}">Listings</a>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">Users</a>
            <a href="{{ route('admin.settings.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">Settings</a>
            <form method="POST" action="{{ route('admin.logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm w-100">Log Out</button>
            </form>
        </nav>
        <main class="flex-grow-1 p-4">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
