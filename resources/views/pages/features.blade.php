@extends('layouts.app')

@section('title', 'Features')
@section('meta_description', 'Packer features — instant pricing, city search and trip comparison, live shipment tracking with proof of delivery, in-app chat, route alerts, push notifications, ratings, ID verification, and zero platform fees.')
@section('body_id', 'features')

@php
    $u = fn ($id, $w = 1800) => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$w}&q=80";
    $arrowSvg = '<svg class="pc-btn__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp

@section('content')

    {{-- ===================== HERO (dark image) ===================== --}}
    <section class="pc-hero-img">
        <div class="pc-hero-img__media" data-media>
            <img src="{{ $u('photo-1565026057447-bc90a3dceb87', 2200) }}" alt="A close-up of a hard-shell suitcase under warm light" data-fallback fetchpriority="high">
        </div>
        <div class="pc-hero-img__inner">
            <span class="pc-eyebrow pc-eyebrow--light pc-reveal">Features</span>
            <h1 class="pc-display pc-reveal" data-delay="1">Everything a safe handover needs.</h1>
            <p class="pc-lead pc-reveal" data-delay="2">From the first search to a rated delivery — the tools that make every handover safe, fair and easy to follow, all inside one app.</p>
        </div>
    </section>

    {{-- ===================== FEATURE INTRO ===================== --}}
    <section class="pc-section pc-section--tight">
        <div class="pc-container">
            <div class="pc-shead pc-reveal">
                <span class="pc-eyebrow">The idea</span>
                <h2 class="pc-h2">One marketplace, focused entirely on the trip.</h2>
                <p class="pc-lead pc-muted">Packer doesn't try to run logistics for you. It does one thing thoroughly: it connects a traveller's spare space with a sender's request, and gets out of the way.</p>
            </div>
        </div>
    </section>

    {{-- ===================== DETAILED FEATURE 1 ===================== --}}
    <section class="pc-section pc-section--tight pc-section--paper2">
        <div class="pc-container">
            <div class="pc-feature-lead pc-reveal">
                <div class="pc-feature-lead__media pc-reveal-img" data-media>
                    <img src="{{ $u('photo-1530521954074-e64f6810b32d', 1200) }}" alt="A traveller resting beside a suitcase in an airport lounge as a plane departs" data-fallback loading="lazy">
                </div>
                <div class="pc-feature-lead__body">
                    <span class="pc-eyebrow">01 — Instant cost calculator</span>
                    <h3>Know the price the moment you pick a trip.</h3>
                    <p>Every traveller sets their own rate per kilo, in the currency they prefer. Pick a trip and the app does the maths instantly — weight times rate — then asks who pays the traveller, the sender or the receiver. No commission on top, no negotiation over a spreadsheet.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== DETAILED FEATURE 2 ===================== --}}
    <section class="pc-section pc-section--tight">
        <div class="pc-container">
            <div class="pc-feature-lead pc-feature-lead--flip pc-reveal">
                <div class="pc-feature-lead__media pc-reveal-img" data-media>
                    <img src="{{ $u('photo-1553062407-98eeb64c6a62', 1200) }}" alt="A travel bag set against a plain, sunlit wall" data-fallback loading="lazy">
                </div>
                <div class="pc-feature-lead__body">
                    <span class="pc-eyebrow">02 — Dual-feed marketplace</span>
                    <h3>One app, both sides of the trip.</h3>
                    <p>A single toggle switches between Travellers and Items — so whether you have space to offer or something to send, you're never in the wrong feed. Featured travellers on the home screen and a recent-activity list of your own shipments keep the next step one tap away.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== BAND ===================== --}}
    <section class="pc-band" aria-label="The inspection mandate">
        <div class="pc-band__media" data-media>
            <img src="{{ $u('photo-1436491865332-7a61a109cc05', 2400) }}" alt="Aircraft wing above a layer of cloud" data-fallback loading="lazy">
        </div>
        <div class="pc-band__inner pc-reveal">
            <span class="pc-eyebrow pc-eyebrow--light">03 — The inspection mandate</span>
            <h2 class="pc-h2">Nothing is sealed before it's seen.</h2>
            <p>Senders can never seal an item before handover. Travellers always have the right to inspect it in person before it goes anywhere near their luggage — no exceptions. The app reinforces it: before a request is sent or accepted, both sides confirm the safety rules, and a delivery can only be marked complete after the traveller confirms the item was inspected and attaches a photo as proof.</p>
        </div>
    </section>

    {{-- ===================== FIND THE RIGHT MATCH ===================== --}}
    <section class="pc-section">
        <div class="pc-container">
            <div class="pc-split-head pc-reveal">
                <div>
                    <span class="pc-eyebrow">Find the right match</span>
                    <h2 class="pc-h2">Search, compare, and pick the trip that fits.</h2>
                </div>
                <p>Whether you're posting or browsing, the app narrows things down to people actually going your way.</p>
            </div>

            <div class="pc-feature-grid pc-reveal">
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">04</div>
                    <h3>Search by city</h3>
                    <p>Type a city anywhere in the world and pick from instant suggestions — results narrow straight to trips or items on your route.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">05</div>
                    <h3>Compare trips side by side</h3>
                    <p>Open a trip and see the other travellers on the same route. Compare price per kilo, rating, completed deliveries, free weight, departure date and delivery method in one view.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">06</div>
                    <h3>Flexible handovers</h3>
                    <p>Choose how it changes hands — the traveller collects the item or the sender drops it off, and the receiver picks it up or the traveller delivers it. Add a photo to any trip or item.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== FOLLOW EVERY HANDOVER ===================== --}}
    <section class="pc-section pc-section--paper2">
        <div class="pc-container">
            <div class="pc-split-head pc-reveal">
                <div>
                    <span class="pc-eyebrow">Follow every handover</span>
                    <h2 class="pc-h2">Always know where a request stands.</h2>
                </div>
                <p>Status, messages and alerts keep both sides in step — no chasing, no guessing.</p>
            </div>

            <div class="pc-feature-grid pc-reveal">
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">07</div>
                    <h3>Live shipment tracking</h3>
                    <p>Every request moves through clear stages — pending, accepted, in transit, arrived and delivered — and the sender sees each change as it happens.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">08</div>
                    <h3>Proof of delivery</h3>
                    <p>A delivery is marked complete with a photo taken at handover and the traveller's confirmation that the item was inspected, leaving a clear record for both sides.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">09</div>
                    <h3>In-app chat</h3>
                    <p>Message the other person inside Packer — with online status and unread badges — before you request, while you arrange the meet-up, and after the handover.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">10</div>
                    <h3>Call &amp; WhatsApp</h3>
                    <p>Once a request is accepted, contact details unlock in the app — reach out with your phone's own dialler or WhatsApp to agree a time and place.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">11</div>
                    <h3>Route alerts</h3>
                    <p>Save a route you care about and get notified the moment a matching trip or item is posted. Travellers hear about new items to carry; senders hear about new travellers.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">12</div>
                    <h3>Notifications you control</h3>
                    <p>Push and in-app alerts for booking updates, new messages and route matches. Switch each category on or off in Settings — and if a trip you booked is removed, you're told straight away.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== TRUST & SAFETY ===================== --}}
    <section class="pc-section">
        <div class="pc-container">
            <div class="pc-split-head pc-reveal">
                <div>
                    <span class="pc-eyebrow">Trust &amp; safety</span>
                    <h2 class="pc-h2">The details that keep every trip dependable.</h2>
                </div>
                <p>Quiet safeguards that do their work in the background, every request.</p>
            </div>

            <div class="pc-feature-grid pc-reveal">
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">13</div>
                    <h3>Mutual rating system</h3>
                    <p>After every completed handover, both sides rate each other with stars and an optional comment — building a public track record that makes the next request easier to trust.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">14</div>
                    <h3>ID verification</h3>
                    <p>Optionally verify your identity with a CNIC or passport. Our team reviews it, and an ID Verified badge appears on your profile — the document itself is never shown to anyone.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">15</div>
                    <h3>Safety notice &amp; guidelines</h3>
                    <p>Prohibited items and each side's responsibilities are spelled out in the app, shown before you send or accept a request, and always available under Settings → Safety Guidelines.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">16</div>
                    <h3>Block &amp; report</h3>
                    <p>Block anyone and you'll stop seeing each other's listings or messages. Report a user for a no-show, scam or unsafe behaviour — our team reviews it and lets you know the outcome.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">17</div>
                    <h3>Zero platform fees</h3>
                    <p>Packer never takes a commission and never handles your money. The rate a traveller sets is the rate a sender pays — settled directly between the two of you.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">18</div>
                    <h3>You own your account</h3>
                    <p>Verify your email with a one-time code, reset a forgotten password by email, and delete your account and data any time from Settings — no support ticket needed.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== SHAPE PACKER ===================== --}}
    <section class="pc-section pc-section--paper2">
        <div class="pc-container">
            <div class="pc-split-head pc-reveal">
                <div>
                    <span class="pc-eyebrow">Built with you</span>
                    <h2 class="pc-h2">Help decide what Packer does next.</h2>
                </div>
                <p>The roadmap isn't guesswork — it's a public list that users write and vote on.</p>
            </div>

            <div class="pc-feature-grid pc-reveal">
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">19</div>
                    <h3>Feature request board</h3>
                    <p>Suggest an idea, search what's already been asked, and upvote or downvote other people's suggestions. Each idea moves from Open to In Progress to Completed, and you're notified when yours does — or when it gets popular.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">20</div>
                    <h3>Any currency</h3>
                    <p>Travellers and senders price in the currency that suits their route, and you can set your own preferred currency in Settings so new posts start with the right one.</p>
                </div>
                <div class="pc-feature-cell">
                    <div class="pc-feature-cell__idx">21</div>
                    <h3>Your activity at a glance</h3>
                    <p>Home shows your recent activity, a Shipments screen filters requests by pending, active and done, and a full history keeps every finished delivery a tap away.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ===================== CTA ===================== --}}
    <section class="pc-section pc-section--ink">
        <div class="pc-container">
            <div class="pc-cta pc-reveal">
                <span class="pc-eyebrow pc-eyebrow--light">Get started</span>
                <h2 class="pc-h2">See the features on your own route.</h2>
                <p class="pc-lead">Tell us where you're headed, and we'll show you what Packer would find.</p>
                <div class="pc-btn-row">
                    <a href="{{ route('home') }}#download" class="pc-btn pc-btn--light">Download App {!! $arrowSvg !!}</a>
                    <a href="{{ route('how-it-works') }}" class="pc-btn pc-btn--on-dark">See how it works</a>
                </div>
            </div>
        </div>
    </section>

@endsection
