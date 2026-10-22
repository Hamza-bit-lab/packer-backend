@php($navTheme = $navTheme ?? 'light')

<header class="pc-nav pc-nav--{{ $navTheme }}" id="pcNav" data-nav-theme="{{ $navTheme }}">
    <div class="pc-nav__inner">
        <a href="{{ route('home') }}" class="pc-nav__brand" aria-label="{{ config('app.name') }} — home">
            <span class="pc-nav__mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                    <path d="M8 7V5.5A1.5 1.5 0 0 1 9.5 4h5A1.5 1.5 0 0 1 16 5.5V7" stroke-linecap="round"/>
                    <rect x="4.5" y="7" width="15" height="13" rx="1.4"/>
                    <path d="M9 7v13M15 7v13" opacity=".55"/>
                </svg>
            </span>
            <span class="pc-nav__word">{{ config('app.name') }}</span>
        </a>

        <nav class="pc-nav__links" aria-label="Primary">
            <a href="{{ route('home') }}" class="pc-nav__link {{ request()->routeIs('home') ? 'is-current' : '' }}">Home</a>
            <a href="{{ route('how-it-works') }}" class="pc-nav__link {{ request()->routeIs('how-it-works') ? 'is-current' : '' }}">How It Works</a>
            <a href="{{ route('features') }}" class="pc-nav__link {{ request()->routeIs('features') ? 'is-current' : '' }}">Features</a>
            <a href="{{ route('faq') }}" class="pc-nav__link {{ request()->routeIs('faq') ? 'is-current' : '' }}">FAQ</a>
            <a href="{{ route('about') }}" class="pc-nav__link {{ request()->routeIs('about') ? 'is-current' : '' }}">About</a>
            <a href="{{ route('contact') }}" class="pc-nav__link {{ request()->routeIs('contact') ? 'is-current' : '' }}">Contact</a>
        </nav>

        <div class="pc-nav__actions">
            <a href="{{ route('home') }}#download" class="pc-btn pc-btn--sm pc-btn--solid">Download App</a>
            <button type="button" class="pc-nav__toggle" aria-label="Open menu" aria-controls="pcMobileMenu"
                    data-bs-toggle="offcanvas" data-bs-target="#pcMobileMenu">
                <span></span><span></span>
            </button>
        </div>
    </div>
</header>

<div class="offcanvas offcanvas-end pc-mobile" tabindex="-1" id="pcMobileMenu" aria-label="Menu">
    <div class="pc-mobile__top">
        <span class="pc-mobile__brand">{{ config('app.name') }}</span>
        <button type="button" class="pc-mobile__close" data-bs-dismiss="offcanvas" aria-label="Close menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 6l12 12M18 6L6 18" stroke-linecap="round"/></svg>
        </button>
    </div>
    <nav class="pc-mobile__links" aria-label="Mobile">
        <a href="{{ route('home') }}" style="--i:1">Home</a>
        <a href="{{ route('how-it-works') }}" style="--i:2">How It Works</a>
        <a href="{{ route('features') }}" style="--i:3">Features</a>
        <a href="{{ route('faq') }}" style="--i:4">FAQ</a>
        <a href="{{ route('about') }}" style="--i:5">About</a>
        <a href="{{ route('contact') }}" style="--i:6">Contact</a>
    </nav>
    <div class="pc-mobile__foot">
        <a href="{{ route('home') }}#download" class="pc-btn pc-btn--solid pc-btn--block">Download App</a>
    </div>
</div>
