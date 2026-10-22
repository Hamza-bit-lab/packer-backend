@extends('layouts.app')

@section('title', 'How It Works')
@section('meta_description', 'How Packer works — post or browse a trip, request a delivery, get an instant price, chat and track it live, and hand items over only after an in-person inspection.')
@section('body_id', 'how-it-works')

@php
    $u = fn ($id, $w = 1800) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=80";
    $arrowSvg = '<svg class="pc-btn__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp

@section('content')

    {{-- ===================== HERO (light editorial) ===================== --}}
    <section class="pc-hero-lite">
        <div class="pc-container">
            <span class="pc-eyebrow pc-reveal">How it works</span>
            <h1 class="pc-display pc-reveal" data-delay="1">Five steps. No middleman.</h1>
            <p class="pc-lead pc-reveal" data-delay="2">A traveller with spare kilos, a sender with something to deliver — here's exactly what happens between posting a trip and shaking hands at the gate.</p>
        </div>
    </section>

    {{-- ===================== FULL-BLEED IMAGE ===================== --}}
    <img class="pc-fullimg pc-reveal-img" src="{{ $u('photo-1530521954074-e64f6810b32d', 2200) }}" alt="A traveller waiting beside a suitcase in an airport lounge as a plane departs outside" data-fallback loading="lazy">

    {{-- ===================== THE PROCESS ===================== --}}
    <section class="pc-section">
        <div class="pc-container">
            <div class="pc-split-head pc-reveal">
                <div>
                    <span class="pc-eyebrow">The process</span>
                    <h2 class="pc-h2">From posting a trip to a handshake at handover.</h2>
                </div>
                <p>Each stage happens directly between the traveller and the sender. Nothing relies on a call centre, and every step is visible to both sides.</p>
            </div>

            <div class="pc-journey">
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">01</div>
                    <h3 class="pc-step__title">Post or browse</h3>
                    <p class="pc-step__desc">Travellers list an upcoming trip — route, dates, airline, spare weight, rate per kilo and how they'd like to hand over. Senders list what they need shipped and where. Search any city, compare trips on the same route, or save a route alert to be told when a match appears.</p>
                </div>
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">02</div>
                    <h3 class="pc-step__title">Request</h3>
                    <p class="pc-step__desc">A sender finds a matching trip and sends a request, choosing who pays the traveller. The app calculates the cost instantly — weight times the traveller's rate — and notifies the traveller straight away.</p>
                </div>
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">03</div>
                    <h3 class="pc-step__title">Accept &amp; connect</h3>
                    <p class="pc-step__desc">The traveller accepts, and phone contact unlocks. Call, WhatsApp or use the in-app chat to agree on a time and place to meet. A traveller can also decline — the sender is told immediately.</p>
                </div>
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">04</div>
                    <h3 class="pc-step__title">Inspect</h3>
                    <p class="pc-step__desc">They meet — at the airport or an agreed public spot. The item is never sealed beforehand; the traveller inspects it first.</p>
                </div>
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">05</div>
                    <h3 class="pc-step__title">Deliver &amp; rate</h3>
                    <p class="pc-step__desc">The traveller marks the item in transit, then arrived, and finally delivered — confirming the inspection and attaching a photo as proof. Both sides then rate each other, building a record for the next request.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== TWO-UP DETAIL ===================== --}}
    <section class="pc-section pc-section--paper2">
        <div class="pc-container">
            <div class="pc-editorial pc-editorial--flip">
                <div class="pc-editorial__media pc-reveal-img pc-editorial__media--wide" data-media>
                    <img src="{{ $u('photo-1488646953014-85cb44e25828', 1200) }}" alt="A flat lay of a folded map, camera, travel guides and a well-used rucksack" data-fallback loading="lazy">
                </div>
                <div class="pc-editorial__body pc-reveal">
                    <span class="pc-eyebrow">What the sender sees</span>
                    <h2 class="pc-h2">A status you can check, not a courier hotline you have to call.</h2>
                    <p>Once a request is sent, the sender follows it through the app — pending, accepted, in transit, arrived, delivered — in plain language, with a push notification at every change and the traveller's rating visible the whole way.</p>
                    <p>If a traveller can't take the request, it's declined instantly, so a sender is never left waiting on silence. And if a trip or item is removed while a request is still live, the other person is told right away instead of finding out later.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== BAND ===================== --}}
    <section class="pc-band" aria-label="Designed around the handover">
        <div class="pc-band__media" data-media>
            <img src="{{ $u('photo-1436491865332-7a61a109cc05', 2400) }}" alt="An aircraft wing above cloud cover" data-fallback loading="lazy">
        </div>
        <div class="pc-band__inner pc-reveal">
            <span class="pc-eyebrow pc-eyebrow--light">Designed around the handover</span>
            <h2 class="pc-h2">The moment an item changes hands is the moment that matters.</h2>
            <p>Packer builds the whole process around that one meeting — inspected, agreed, and carried in good faith.</p>
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section class="pc-section pc-section--ink">
        <div class="pc-container">
            <div class="pc-cta pc-reveal">
                <span class="pc-eyebrow pc-eyebrow--light">Get started</span>
                <h2 class="pc-h2">Have a trip, or something to send?</h2>
                <p class="pc-lead">Post your first trip or item and see how quickly a match comes in.</p>
                <div class="pc-btn-row">
                    <a href="{{ route('home') }}#download" class="pc-btn pc-btn--light">Download App {!! $arrowSvg !!}</a>
                    <a href="{{ route('features') }}" class="pc-btn pc-btn--on-dark">Explore features</a>
                </div>
            </div>
        </div>
    </section>

@endsection
