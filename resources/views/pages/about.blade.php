@extends('layouts.app')

@section('title', 'About')
@section('meta_description', 'About Packer — why we built a fairer way to ship abroad, the problem it solves, and the approach behind the product.')
@section('body_id', 'about')

@php
    $u = fn ($id, $w = 1800) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=80";
    $arrowSvg = '<svg class="pc-btn__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp

@section('content')

    {{-- ===================== HERO (dark image) ===================== --}}
    <section class="pc-hero-img">
        <div class="pc-hero-img__media" data-media>
            <img src="{{ $u('photo-1503220317375-aaad61436b1b', 2200) }}" alt="A traveller with a backpack looking out across a still mountain lake" data-fallback fetchpriority="high">
        </div>
        <div class="pc-hero-img__inner">
            <span class="pc-eyebrow pc-eyebrow--light pc-reveal">About Packer</span>
            <h1 class="pc-display pc-reveal" data-delay="1">We think spare luggage space is worth more than empty.</h1>
            <p class="pc-lead pc-reveal" data-delay="2">Packer started with a simple observation: every flight carries kilos of allowance nobody uses, while someone else is paying a courier a fortune to send far less.</p>
        </div>
    </section>

    {{-- ===================== INTRODUCTION ===================== --}}
    <section class="pc-section">
        <div class="pc-container">
            <div class="pc-editorial pc-editorial--text-lead">
                <div class="pc-editorial__body pc-reveal">
                    <span class="pc-eyebrow">Introduction</span>
                    <h2 class="pc-h2">A smoother way to send something home.</h2>
                    <p>Shipping abroad through a courier is slow, expensive, and often more than the item itself is worth. Meanwhile, travellers fly the exact same routes every day with luggage allowance going unused.</p>
                    <p>Packer exists to close that gap: to turn a trip that was happening anyway into a delivery, safely and fairly, for both the person carrying it and the person sending it.</p>
                </div>
                <div class="pc-editorial__media pc-reveal-img" data-media>
                    <img src="{{ $u('photo-1553062407-98eeb64c6a62', 1200) }}" alt="A travel bag set against a plain, sunlit wall" data-fallback loading="lazy">
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== THE PROBLEM ===================== --}}
    <section class="pc-section pc-section--paper2">
        <div class="pc-container">
            <div class="pc-split-head pc-reveal">
                <div>
                    <span class="pc-eyebrow">The problem</span>
                    <h2 class="pc-h2">Sending something abroad shouldn't cost more than the item.</h2>
                </div>
                <p>Courier pricing punishes small, personal shipments — and travellers are the one resource the industry never thinks to use.</p>
            </div>
            <div class="pc-journey">
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">—</div>
                    <h3 class="pc-step__title">Courier rates are steep</h3>
                    <p class="pc-step__desc">International shipping fees often outweigh the value of a small gift, document or parcel of local delicacies.</p>
                </div>
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">—</div>
                    <h3 class="pc-step__title">Spare capacity goes to waste</h3>
                    <p class="pc-step__desc">Most travellers fly with unused luggage allowance and no easy way to turn it into anything useful.</p>
                </div>
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">—</div>
                    <h3 class="pc-step__title">Trust is the missing piece</h3>
                    <p class="pc-step__desc">Asking a stranger to carry your belongings — or carrying someone else's — needs real safeguards, not just good faith.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== THE IDEA ===================== --}}
    <section class="pc-section">
        <div class="pc-container">
            <div class="pc-editorial pc-editorial--flip">
                <div class="pc-editorial__media pc-reveal-img pc-editorial__media--wide" data-media>
                    <img src="{{ $u('photo-1469854523086-cc02fe5d8800', 1200) }}" alt="A camper van on an open desert road between rock formations" data-fallback loading="lazy">
                </div>
                <div class="pc-editorial__body pc-reveal">
                    <span class="pc-eyebrow">The idea</span>
                    <h2 class="pc-h2">A marketplace built around a trip that already exists.</h2>
                    <p>Instead of building warehouses and routes, Packer simply connects two people already going the same way — a traveller with room to spare, and a sender who needs it filled.</p>
                    <p>An instant price, a mandatory inspection, live tracking and a direct handover. That's the whole product.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== OUR APPROACH ===================== --}}
    <section class="pc-section pc-section--paper2">
        <div class="pc-container">
            <div class="pc-split-head pc-reveal">
                <div>
                    <span class="pc-eyebrow">Our approach</span>
                    <h2 class="pc-h2">Restraint, on purpose.</h2>
                </div>
                <p>We would rather do one thing dependably than many things adequately. Everything in Packer earns its place.</p>
            </div>
            <div class="pc-feature-grid pc-reveal">
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">01</div>
                    <h3>Trust over cleverness</h3>
                    <p>Ratings, visible history and a mandatory inspection step — safeguards that matter more than a slicker feature list.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">02</div>
                    <h3>Fair over cheap</h3>
                    <p>No commission, no dynamic pricing. The rate a traveller sets is the price a sender pays — nothing added in between.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">03</div>
                    <h3>Direct over automated</h3>
                    <p>Real conversations, not chatbots. Message each other in the app, or call once a request is accepted — the two people involved simply talk.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">04</div>
                    <h3>Visible over opaque</h3>
                    <p>Every request has a live status, every delivery ends with a photo, and every update reaches you by notification — nothing happens out of sight.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">05</div>
                    <h3>Safe over silent</h3>
                    <p>Block or report anyone straight from their profile. Reports are reviewed by our team, and accounts that break the rules are suspended.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">06</div>
                    <h3>Listening over guessing</h3>
                    <p>A public feature board lets users suggest ideas and vote on them, so what we build next comes from the people using Packer.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== WHY PACK & CARRY ===================== --}}
    <section class="pc-band" aria-label="Why Packer">
        <div class="pc-band__media" data-media>
            <img src="{{ $u('photo-1436491865332-7a61a109cc05', 2400) }}" alt="Aircraft wing above soft cloud" data-fallback loading="lazy">
        </div>
        <div class="pc-band__inner pc-reveal">
            <span class="pc-eyebrow pc-eyebrow--light">Why Packer</span>
            <h2 class="pc-h2">Your journey. Someone else's delivery.</h2>
            <p>We're building the layer of trust that turns an ordinary flight into a fairer way to send something home.</p>
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section class="pc-section pc-section--ink">
        <div class="pc-container">
            <div class="pc-cta pc-reveal">
                <span class="pc-eyebrow pc-eyebrow--light">Join us</span>
                <h2 class="pc-h2">Let's make your spare kilos count.</h2>
                <p class="pc-lead">Tell us about your next trip, or what you need sent, and we'll take it from there.</p>
                <div class="pc-btn-row">
                    <a href="{{ route('contact') }}" class="pc-btn pc-btn--light">Get in touch {!! $arrowSvg !!}</a>
                    <a href="{{ route('features') }}" class="pc-btn pc-btn--on-dark">Explore features</a>
                </div>
            </div>
        </div>
    </section>

@endsection
