<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8fafc">

    <script>document.documentElement.className += ' pc-js';</script>

    <title>@yield('title', config('app.name')) — {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta_description', config('app.name').' connects travellers with spare luggage space to people who need something sent abroad — an instant price, a mandatory inspection, and a direct handover.')">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="@yield('title', config('app.name'))">
    <meta property="og:description" content="@yield('meta_description', 'A peer-to-peer marketplace connecting travellers with spare luggage space to people who need something sent abroad.')">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;450;500;600&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/pack-carry.css') }}?v=4">

    @stack('head')
</head>
<body class="pc-body" data-page="@yield('body_id', 'page')">

    <a class="pc-skip-link" href="#main">Skip to content</a>

    @include('partials.navbar', ['navTheme' => $navTheme ?? 'light'])

    <main id="main">
        @yield('content')
    </main>

    @include('partials.footer')

    {{-- Shows on every page load — a deliberate choice so this safety notice
         is never missed, not a one-time/cookie-remembered dismissal. --}}
    <div class="modal fade pc-disclaimer-modal" id="pcDisclaimerModal" tabindex="-1" aria-labelledby="pcDisclaimerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="pcDisclaimerModalLabel">⚠️ Before you continue — a safety notice</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pc-prose">
                    @include('partials.disclaimer')
                </div>
                <div class="modal-footer">
                    <a href="{{ route('terms') }}#prohibited-items" class="pc-inline-link">Read the full policy</a>
                    <button type="button" class="pc-btn pc-btn--solid" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="{{ asset('js/pack-carry.js') }}?v=2"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var el = document.getElementById('pcDisclaimerModal');
            if (el) new bootstrap.Modal(el).show();
        });
    </script>

    @stack('scripts')
</body>
</html>
