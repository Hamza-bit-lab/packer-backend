@extends('layouts.app')

@section('title', 'Frequently Asked Questions')
@section('meta_description', 'Answers to common questions about how Packer works, pricing, payments, notifications, safety, accounts, and what can and cannot be sent.')
@section('body_id', 'faq')

@php
    $faqs = [
        [
            'q' => 'What is Packer?',
            'a' => 'Packer is a peer-to-peer marketplace that connects travellers who have spare luggage weight with senders who need something carried abroad. A traveller lists a trip and available kilos; a sender lists an item and where it needs to go. The two agree directly, and the item is inspected and handed over in person.',
        ],
        [
            'q' => 'How is the price worked out?',
            'a' => 'Every traveller sets their own rate per kilogram for a trip. The moment a sender picks that trip, the app multiplies the item\'s weight by the traveller\'s rate to show an exact estimated cost — there is no haggling, and Packer does not add any platform fee on top.',
        ],
        [
            'q' => 'What can I not send or carry?',
            'a' => 'Nothing illegal, restricted, or banned by customs, aviation, or airport security authorities — this includes drugs or narcotics, weapons or ammunition, explosives, flammable or hazardous materials, and counterfeit goods, among others. Read the full <a href="' . route('terms') . '#prohibited-items">Prohibited Items &amp; Liability</a> section of our Terms &amp; Conditions before posting or accepting anything.',
        ],
        [
            'q' => "Who is responsible if a traveller carries something illegal?",
            'a' => 'Packer does not inspect, handle, or transport any item — we only connect the two people. Full responsibility for what is sent and carried sits with the sender and the traveller who agreed to it, not with Packer. See our <a href="' . route('terms') . '#prohibited-items">Terms &amp; Conditions</a> for the complete policy.',
        ],
        [
            'q' => 'Can a traveller refuse to carry an item?',
            'a' => 'Yes, always. A traveller should personally inspect every item before it goes anywhere near their luggage, and can decline to carry anything they are not satisfied with — whatever the reason. An item must never be sealed in a way that prevents this inspection.',
        ],
        [
            'q' => 'Do I need to verify my identity?',
            'a' => 'ID verification is optional but strongly encouraged. A verified badge appears on your profile and builds trust with the people you are matching with — many users prefer to deal only with verified travellers or senders.',
        ],
        [
            'q' => 'How does the rating system work?',
            'a' => 'After a delivery is marked complete, both the traveller and the sender rate each other. Your average rating and number of completed deliveries are shown on your public profile, so trust builds with every trip.',
        ],
        [
            'q' => 'How do I contact the other person?',
            'a' => 'You can message anyone directly in Packer\'s in-app chat — for example from a trip page before you send a request. Once a traveller accepts a request, both sides\' phone details also unlock, so you can call or use WhatsApp to arrange the handover.',
        ],
        [
            'q' => 'How do I track a request?',
            'a' => 'Every request moves through clear stages — pending, accepted, in transit, arrived and delivered — and the Shipments screen in the app shows where each one stands. You also get a notification whenever a status changes. To finish a delivery, the traveller confirms the item was inspected and attaches a photo as proof.',
        ],
        [
            'q' => 'Can I cancel a request?',
            'a' => 'Yes, while it is still pending — either the sender or the traveller can withdraw it, and the other side is notified. A traveller can also decline a request. Once a request has been accepted, please coordinate any change directly with the other person through the in-app chat.',
        ],
        [
            'q' => 'What happens if the other person removes their trip or deletes their account?',
            'a' => 'If a trip, an item or an account is removed while a request on it is still live, that request is cancelled and you are notified straight away, so you are never left waiting on something that no longer exists.',
        ],
        [
            'q' => 'How do I pay?',
            'a' => 'Packer does not process payments or hold any money. When you send a request you choose who pays the traveller — the sender or the receiver — and you settle that amount directly with the traveller, in the currency shown on the trip.',
        ],
        [
            'q' => 'Which currencies are supported?',
            'a' => 'Travellers and senders price their trips and items in the currency that suits their route. You can set your own preferred currency under Settings → Preferences so new posts start with the right one.',
        ],
        [
            'q' => 'What are route alerts?',
            'a' => 'A route alert saves a route you care about — say Lahore to Dubai. When a matching trip or item is posted, you get a notification straight away. Travellers can watch for new items to carry; senders can watch for new travellers.',
        ],
        [
            'q' => 'What notifications will I get, and can I turn them off?',
            'a' => 'You get push and in-app notifications for booking updates, new messages and route-alert matches, plus a few account updates such as an ID verification result. Under Settings → Notifications you can switch booking, message and route-alert notifications on or off individually.',
        ],
        [
            'q' => 'How do I report or block someone?',
            'a' => 'Open their profile in the app and choose Report user or Block user. Blocking hides your listings from each other and stops messages both ways. Reports are reviewed by our team, who can suspend accounts that break the rules — and you are told once your report has been reviewed.',
        ],
        [
            'q' => 'I forgot my password. What do I do?',
            'a' => 'On the login screen tap Forgot password?, enter your email and we will send you a 6-digit code. Enter the code with a new password and you are back in. The code expires after 15 minutes, and resetting signs out any other devices.',
        ],
        [
            'q' => 'How do I delete my account?',
            'a' => 'In the app go to Settings and tap Delete Account, then confirm with your password. This permanently removes your profile, trips, items, messages, ratings, notifications and every photo you uploaded, including any ID document. Any request still in progress is cancelled and the other person is told.',
        ],
        [
            'q' => 'Can I suggest a feature?',
            'a' => 'Yes — Settings → Feature Requests is a public board where anyone can suggest an idea, search what has already been asked, and upvote or downvote other suggestions. You are notified when your idea moves to In Progress or Completed, or when it becomes popular.',
        ],
        [
            'q' => 'Is there a mobile app?',
            'a' => 'Yes — Packer is built as a mobile app and is rolling out to early users now. See the <a href="' . route('home') . '#download">Download the App</a> section on our homepage.',
        ],
    ];
@endphp

@section('content')

    <section class="pc-hero-lite">
        <div class="pc-container">
            <span class="pc-eyebrow pc-reveal">Support</span>
            <h1 class="pc-display pc-reveal" data-delay="1">Frequently Asked Questions</h1>
        </div>
    </section>

    <section class="pc-section pc-section--tight">
        <div class="pc-container">
            <div class="pc-faq pc-reveal">
                <div class="accordion" id="pcFaqAccordion">
                    @foreach ($faqs as $i => $faq)
                        <div class="accordion-item pc-faq__item">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $i === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#pcFaq{{ $i }}" aria-expanded="{{ $i === 0 ? 'true' : 'false' }}" aria-controls="pcFaq{{ $i }}">
                                    {{ $faq['q'] }}
                                </button>
                            </h2>
                            <div id="pcFaq{{ $i }}" class="accordion-collapse collapse {{ $i === 0 ? 'show' : '' }}" data-bs-parent="#pcFaqAccordion">
                                <div class="accordion-body">{!! $faq['a'] !!}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="pc-faq__more pc-reveal">Still have a question? <a href="{{ route('contact') }}">Get in touch</a>.</p>
        </div>
    </section>

@endsection
