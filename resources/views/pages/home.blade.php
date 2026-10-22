@extends('layouts.app')

@section('title', 'Turn spare luggage space into someone else\'s fastest delivery')
@section('meta_description', 'Packer connects travellers with spare luggage space to people who need something sent abroad — an instant price, a mandatory inspection, live shipment tracking, in-app chat and a direct handover.')
@section('body_id', 'home')

@php
    $u = fn ($id, $w = 1920) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=80";
    $arrowSvg = '<svg class="pc-btn__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>';

    $slides = [
        [
            'img' => $u('photo-1530521954074-e64f6810b32d', 2000),
            'alt' => 'A traveller waiting in an airport lounge with their feet resting on a suitcase as a plane departs outside',
            'eyebrow' => 'Peer-to-peer crowdshipping',
            'title' => 'Turn spare luggage space into someone\'s fastest delivery.',
            'text' => 'Connect with travellers already flying where your package needs to go.',
        ],
        [
            'img' => $u('photo-1469854523086-cc02fe5d8800', 2000),
            'alt' => 'A camper van on an open desert road winding between red rock formations',
            'eyebrow' => 'Built around trust',
            'title' => 'Nothing is sealed until it\'s seen.',
            'text' => 'Every handover starts with an in-person inspection — always the traveller\'s call — and ends with photo proof of delivery.',
        ],
        [
            'img' => $u('photo-1436491865332-7a61a109cc05', 2000),
            'alt' => 'The wing of an aircraft above a soft layer of cloud at sunset',
            'eyebrow' => 'One price, agreed upfront',
            'title' => 'Weight times rate. Nothing hidden.',
            'text' => 'See the exact cost the moment you pick a trip — no haggling, no platform fees.',
        ],
        [
            'img' => $u('photo-1476514525535-07fb3b4ae5f1', 2000),
            'alt' => 'The bow of a wooden boat pointing across a calm alpine lake toward mountains',
            'eyebrow' => 'Always in the loop',
            'title' => 'Every update, the moment it happens.',
            'text' => 'Live shipment status, in-app chat, route alerts and push notifications — no refreshing, no chasing.',
        ],
        [
            'img' => $u('photo-1503220317375-aaad61436b1b', 2000),
            'alt' => 'A traveller with a backpack looking out across a still mountain lake',
            'eyebrow' => 'Your journey, their delivery',
            'title' => 'Carried by someone already going there.',
            'text' => 'No couriers, no warehouses — just two people and a trip that was happening anyway.',
        ],
    ];
@endphp

@section('content')

    {{-- ===================== HERO SLIDER ===================== --}}
    <section class="pc-hero" data-hero-slider data-hero-duration="6000" aria-roledescription="carousel" aria-label="Packer highlights" tabindex="0">
        <div class="pc-hero__track">
            @foreach ($slides as $i => $slide)
                <article class="pc-slide {{ $i === 0 ? 'is-active' : '' }}" aria-roledescription="slide" aria-label="{{ $i + 1 }} of {{ count($slides) }}" aria-hidden="{{ $i === 0 ? 'false' : 'true' }}">
                    <div class="pc-slide__media" data-media>
                        <img class="pc-slide__img" src="{{ $slide['img'] }}" alt="{{ $slide['alt'] }}" data-fallback @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                    </div>
                    <div class="pc-hero__inner">
                        <div class="pc-slide__copy">
                            <span class="pc-eyebrow">{{ $slide['eyebrow'] }}</span>
                            <h1 class="pc-slide__title">{{ $slide['title'] }}</h1>
                            <p class="pc-slide__text">{{ $slide['text'] }}</p>
                            <div class="pc-slide__cta">
                                <a href="#download" class="pc-btn pc-btn--light">Download App {!! $arrowSvg !!}</a>
                                <a href="{{ route('how-it-works') }}" class="pc-btn pc-btn--on-dark">Explore</a>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="pc-hero__controls">
            <div class="pc-hero__controls-inner">
                <span class="pc-hero__count"><b data-hero-current>01</b> / <span data-hero-total>05</span></span>
                <div class="pc-hero__bars" role="tablist" aria-label="Choose slide">
                    @foreach ($slides as $i => $slide)
                        <button class="pc-hero__bar {{ $i === 0 ? 'is-active' : '' }}" style="--slide-dur:6s" role="tab" aria-label="Go to slide {{ $i + 1 }}"><span></span></button>
                    @endforeach
                </div>
                <div class="pc-hero__arrows">
                    <button class="pc-hero__arrow" data-hero-prev aria-label="Previous slide">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M15 18l-6-6 6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <button class="pc-hero__arrow" data-hero-next aria-label="Next slide">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M9 18l6-6-6-6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== INTRODUCTION ===================== --}}
    <section class="pc-section">
        <div class="pc-container">
            <div class="pc-editorial pc-editorial--text-lead">
                <div class="pc-editorial__body pc-reveal">
                    <span class="pc-eyebrow">What Packer is</span>
                    <h2 class="pc-h2">Less time chasing couriers. More time enjoying the journey.</h2>
                    <p>Packer is a peer-to-peer marketplace that connects international travellers who have spare luggage weight with people who need something carried abroad — a document, a gift, a taste of home. Two strangers, one trip, one price agreed before anything moves.</p>
                    <p>It exists because shipping abroad is slow and expensive, while millions of kilos of luggage allowance fly empty every day. Packer puts that spare space to work.</p>
                    <dl class="pc-statline">
                        <div>
                            <dt>For travellers</dt>
                            <dd>Offset your ticket by carrying something on a route you were already flying.</dd>
                        </div>
                        <div>
                            <dt>For senders</dt>
                            <dd>Skip the courier queue — find someone already heading where you need to send.</dd>
                        </div>
                    </dl>
                </div>
                <div class="pc-editorial__media pc-reveal-img" data-media>
                    <img src="{{ $u('photo-1553062407-98eeb64c6a62', 1200) }}" alt="A travel bag set against a plain, sunlit wall" data-fallback loading="lazy">
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== PRODUCT EXPERIENCE / JOURNEY ===================== --}}
    <section class="pc-section pc-section--paper2">
        <div class="pc-container">
            <div class="pc-split-head pc-reveal">
                <div>
                    <span class="pc-eyebrow">The product experience</span>
                    <h2 class="pc-h2">Follow a request from post to handover.</h2>
                </div>
                <p>Every step happens inside the app, between the two people actually involved — with a live status and a notification at each stage, and no dispatcher or call centre in between.</p>
            </div>

            <div class="pc-flow pc-reveal" aria-hidden="true">
                <span>Post</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Request</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Accept</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Inspect</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Deliver</span>
            </div>

            <div class="pc-journey" style="margin-top:2.5rem;">
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">01</div>
                    <h3 class="pc-step__title">Post</h3>
                    <p class="pc-step__desc">A traveller lists a trip and spare kilos. A sender lists an item, its weight, and where it needs to go. Search any city, compare trips on the route, or save a route alert to be told when a match appears.</p>
                </div>
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">02</div>
                    <h3 class="pc-step__title">Request</h3>
                    <p class="pc-step__desc">The sender picks a matching trip and chooses who pays. The cost — weight times the traveller's rate — is calculated instantly, and the traveller is notified straight away.</p>
                </div>
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">03</div>
                    <h3 class="pc-step__title">Accept</h3>
                    <p class="pc-step__desc">The traveller accepts, and contact details unlock — a direct call, WhatsApp or the in-app chat to arrange the meet-up.</p>
                </div>
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">04</div>
                    <h3 class="pc-step__title">Inspect</h3>
                    <p class="pc-step__desc">They meet in person. The traveller inspects the item before it goes anywhere near their luggage.</p>
                </div>
                <div class="pc-step pc-reveal">
                    <div class="pc-step__num">05</div>
                    <h3 class="pc-step__title">Deliver</h3>
                    <p class="pc-step__desc">The item travels — in transit, then arrived — and is marked delivered with a photo as proof. Both sides then rate each other, building trust for the next trip.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== MEDIA BAND ===================== --}}
    <section class="pc-band" aria-label="From request to arrival">
        <div class="pc-band__media" data-media>
            <img src="{{ $u('photo-1436491865332-7a61a109cc05', 2200) }}" alt="An aircraft wing above a soft layer of cloud at altitude" data-fallback loading="lazy">
        </div>
        <div class="pc-band__inner pc-reveal">
            <span class="pc-eyebrow pc-eyebrow--light">From request to arrival</span>
            <h2 class="pc-h2">Your journey should be about the destination.</h2>
            <p>Packer takes the uncertainty out of shipping abroad, so both sides can think about everything else.</p>
        </div>
    </section>

    {{-- ===================== FEATURES ===================== --}}
    <section class="pc-section">
        <div class="pc-container">
            <div class="pc-split-head pc-reveal">
                <div>
                    <span class="pc-eyebrow">What it does</span>
                    <h2 class="pc-h2">Built for trust, transparency and a fair price.</h2>
                </div>
                <p>A focused set of tools — everything a safe handover between strangers needs, from the first search to a rated delivery.</p>
            </div>

            <div class="pc-feature-lead pc-reveal" style="margin-bottom:clamp(3rem,7vw,5rem);">
                <div class="pc-feature-lead__media pc-reveal-img" data-media>
                    <img src="{{ $u('photo-1530521954074-e64f6810b32d', 1200) }}" alt="A traveller resting in an airport lounge, suitcase beside them, watching a plane depart" data-fallback loading="lazy">
                </div>
                <div class="pc-feature-lead__body">
                    <span class="pc-eyebrow">Instant cost calculator</span>
                    <h3>Know the exact price before you ask.</h3>
                    <p>Every traveller sets their own rate per kilo. The moment a sender picks a trip, the app multiplies weight by rate and shows the cost — no commission layers, no back-and-forth over numbers. You choose whether the sender or the receiver pays the traveller.</p>
                    <div class="pc-btn-row">
                        <a href="{{ route('features') }}" class="pc-inline-link">See all features
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="pc-feature-grid pc-reveal">
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">01</div>
                    <h3>Two feeds, one app</h3>
                    <p>Switch between Travellers and Items in one tap, search any city with instant suggestions, and compare every trip on a route side by side.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">02</div>
                    <h3>Live shipment tracking</h3>
                    <p>Follow each request from pending to accepted, in transit, arrived and delivered — with a photo taken at handover as proof of delivery.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">03</div>
                    <h3>The inspection mandate</h3>
                    <p>Senders can never seal a package before handover. Travellers always have the final say on what they carry, and both sides confirm the safety rules first.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">04</div>
                    <h3>Chat, call &amp; WhatsApp</h3>
                    <p>Message the other person inside Packer any time. Once a request is accepted, contact details unlock for a quick call or WhatsApp.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">05</div>
                    <h3>Route alerts</h3>
                    <p>Save a route and get notified the moment a matching trip or item is posted — so you hear about it before anyone else does.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">06</div>
                    <h3>Notifications you control</h3>
                    <p>Push and in-app alerts for bookings, messages and route matches. Switch each category on or off in Settings.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">07</div>
                    <h3>Ratings &amp; ID verification</h3>
                    <p>Every completed handover is rated by both sides, and an optional ID Verified badge shows who has been checked by our team.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">08</div>
                    <h3>Block, report &amp; moderation</h3>
                    <p>Block anyone you'd rather not deal with, or report a problem. Reports are reviewed by our team, and rule-breaking accounts are suspended.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">09</div>
                    <h3>Zero platform fees</h3>
                    <p>Packer never takes a cut and never handles your money. The rate a traveller sets is the rate the sender pays.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">10</div>
                    <h3>Any route, any currency</h3>
                    <p>From Karachi to Riyadh, São Paulo to New York — wherever a traveller is flying, a request can follow, priced in the currency that suits.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">11</div>
                    <h3>Flexible handovers</h3>
                    <p>The traveller collects the item or the sender drops it off; the receiver picks up or the traveller delivers. Add photos to any trip or item.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">12</div>
                    <h3>Shape the app</h3>
                    <p>Suggest features, vote on other people's ideas and follow them from Open to Completed — the roadmap is written by the people who use it.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== TRUST / EDITORIAL ===================== --}}
    <section class="pc-section pc-section--paper2">
        <div class="pc-container">
            <div class="pc-editorial pc-editorial--flip">
                <div class="pc-editorial__media pc-reveal-img pc-editorial__media--wide" data-media>
                    <img src="{{ $u('photo-1476514525535-07fb3b4ae5f1', 1200) }}" alt="The bow of a wooden boat pointing across a calm alpine lake toward mountains" data-fallback loading="lazy">
                </div>
                <div class="pc-editorial__body pc-reveal">
                    <span class="pc-eyebrow">Why it is different</span>
                    <h2 class="pc-h2">Not a courier. A trip that was happening anyway.</h2>
                    <p>Most shipping options are built around warehouses and fixed schedules. Packer is built around a traveller who was already flying — with an interface that is quiet, clear and quick to read, and safeguards that put the person carrying your item first.</p>
                    <p>The result is a fairer price for the sender, real income for the traveller, and a handover both sides can trust.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== DOWNLOAD THE APP ===================== --}}
    <section class="pc-section pc-section--paper2" id="download">
        <div class="pc-container">
            <div class="pc-split-head pc-reveal">
                <div>
                    <span class="pc-eyebrow">Get the app</span>
                    <h2 class="pc-h2">Packer lives on your phone.</h2>
                </div>
                <p>Post a trip, request a delivery, and message a traveller — all from the app. The app itself is rolling out to early users now.</p>
            </div>

            <div class="pc-app-preview pc-reveal">
                <div class="pc-app-preview__phone">
                    <img src="{{ asset('images/app/screenshot-my-posts.png') }}" alt="Packer app screen showing a traveller's posted trips and items with reward and view counts" loading="lazy">
                </div>
                <div class="pc-app-preview__phone pc-app-preview__phone--lead">
                    <img src="{{ asset('images/app/screenshot-home.png') }}" alt="Packer app home screen showing featured travellers, quick actions, and recent activity" loading="lazy">
                </div>
                <div class="pc-app-preview__phone">
                    <img src="{{ asset('images/app/screenshot-profile.png') }}" alt="Packer app profile screen showing ratings, deliveries, and account settings" loading="lazy">
                </div>
            </div>

            <div class="pc-app-preview__actions pc-reveal">
                <a href="#" class="pc-btn pc-btn--solid" aria-disabled="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" style="margin-right:.5rem;"><path d="M16.365 1.43c0 1.14-.468 2.16-1.225 2.9-.822.81-2.116 1.44-3.16 1.36-.126-1.11.45-2.28 1.19-3.01.83-.83 2.25-1.44 3.195-1.25zm4.29 16.83c-.53 1.22-.78 1.77-1.46 2.85-.95 1.5-2.29 3.37-3.95 3.39-1.47.02-1.85-.96-3.84-.95-1.99.01-2.41.97-3.88.95-1.66-.02-2.93-1.7-3.88-3.2-2.66-4.2-2.94-9.13-1.3-11.75 1.16-1.86 2.99-2.95 4.71-2.95 1.75 0 2.85 1 4.3 1 1.4 0 2.26-1 4.3-1 1.53 0 3.15.83 4.3 2.27-3.79 2.08-3.17 7.5.67 9.34z"/></svg>
                    App Store <span class="pc-muted" style="text-transform:none;letter-spacing:0;">— coming soon</span>
                </a>
                <a href="#" class="pc-btn pc-btn--ghost" aria-disabled="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true" style="margin-right:.5rem;"><path d="M3.6 2.2c-.35.35-.55.9-.55 1.6v16.4c0 .7.2 1.25.55 1.6l.1.1L13 12.5v-.2L3.7 2.1l-.1.1zm11 10.3l3.2-3.2 3.9 2.2c1.1.65 1.1 1.7 0 2.35l-3.9 2.2-3.2-3.2v-.35zM4.3 20.8l9-9.3.1.1-9 9.3-.1-.1zm0-17.6l.1-.1 9 9.3-.1.1-9-9.3z"/></svg>
                    Google Play <span class="pc-muted" style="text-transform:none;letter-spacing:0;">— coming soon</span>
                </a>
            </div>
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section class="pc-section pc-section--ink">
        <div class="pc-container">
            <div class="pc-cta pc-reveal">
                <span class="pc-eyebrow pc-eyebrow--light">Get started</span>
                <h2 class="pc-h2">Ready to carry, or ready to send?</h2>
                <p class="pc-lead">Packer is rolling out to early users now.</p>
                <div class="pc-btn-row">
                    <a href="#download" class="pc-btn pc-btn--light">Download App {!! $arrowSvg !!}</a>
                    <a href="{{ route('how-it-works') }}" class="pc-btn pc-btn--on-dark">See how it works</a>
                </div>
            </div>
        </div>
    </section>

@endsection
