@extends('layouts.app')

@section('title', 'Contact')
@section('meta_description', 'Contact Packer — tell us about your next trip or what you need sent, and we will be in touch.')
@section('body_id', 'contact')

@section('content')

    {{-- ===================== HERO (light editorial) ===================== --}}
    <section class="pc-hero-lite">
        <div class="pc-container">
            <span class="pc-eyebrow pc-reveal">Contact</span>
            <h1 class="pc-display pc-reveal" data-delay="1">Start a conversation.</h1>
            <p class="pc-lead pc-reveal" data-delay="2">Whether you're planning a trip, have something to send, or you're just curious how Packer works, send a note and we will reply properly.</p>
        </div>
    </section>

    {{-- ===================== CONTACT ===================== --}}
    <section class="pc-section">
        <div class="pc-container">
            <div class="pc-contact">
                <div class="pc-contact__aside pc-reveal">
                    <h2 class="pc-h3">Tell us what you need</h2>
                    <p>Share as much or as little as you like. A few lines about your route, or what you're hoping to send, is enough for a useful first conversation.</p>

                    <dl class="pc-contact__meta">
                        <div>
                            <dt>Enquiries</dt>
                            <dd>Use the form and we will route your message to the right person.</dd>
                        </div>
                        <div>
                            <dt>Response time</dt>
                            <dd>We aim to reply within two working days.</dd>
                        </div>
                    </dl>
                </div>

                <div class="pc-contact__form pc-reveal" data-delay="1">
                    @if (session('status'))
                        <div class="pc-flash" role="status" style="margin-bottom:1.75rem;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.submit') }}" class="pc-form" novalidate>
                        @csrf

                        <div class="pc-form__row">
                            <div class="pc-field">
                                <label for="name">Name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required
                                       aria-invalid="@error('name')true @else false @enderror"
                                       @error('name') aria-describedby="name-error" @enderror>
                                @error('name')<span class="pc-field__error" id="name-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="pc-field">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required
                                       aria-invalid="@error('email')true @else false @enderror"
                                       @error('email') aria-describedby="email-error" @enderror>
                                @error('email')<span class="pc-field__error" id="email-error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="pc-field">
                            <label for="company">Company <span class="pc-muted" style="text-transform:none;letter-spacing:0;font-weight:400;">(optional)</span></label>
                            <input type="text" id="company" name="company" value="{{ old('company') }}" autocomplete="organization"
                                   @error('company') aria-describedby="company-error" @enderror>
                            @error('company')<span class="pc-field__error" id="company-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pc-field">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="6" required
                                      aria-invalid="@error('message')true @else false @enderror"
                                      @error('message') aria-describedby="message-error" @enderror>{{ old('message') }}</textarea>
                            @error('message')<span class="pc-field__error" id="message-error">{{ $message }}</span>@enderror
                        </div>

                        <div class="pc-form__actions">
                            <button type="submit" class="pc-btn pc-btn--solid">
                                Send message
                                <svg class="pc-btn__arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </button>
                            <p class="pc-form__hint">We will only use your details to reply to this enquiry.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

@endsection
