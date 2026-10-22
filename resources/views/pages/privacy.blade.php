@extends('layouts.app')

@section('title', 'Privacy Policy')
@section('meta_description', 'How Packer collects, uses and shares personal information — on this website and within the Packer app.')
@section('body_id', 'privacy')

@section('content')

    <section class="pc-hero-lite">
        <div class="pc-container">
            <span class="pc-eyebrow pc-reveal">Legal</span>
            <h1 class="pc-display pc-reveal" data-delay="1">Privacy Policy</h1>
        </div>
    </section>

    <section class="pc-section pc-section--tight">
        <div class="pc-container">
            <div class="pc-prose pc-reveal">
                <p class="pc-prose__updated">Last updated {{ now()->format('F Y') }}</p>

                <h2>1. Who this policy covers</h2>
                <p>This policy applies to anyone who uses this website or the Packer mobile app (together, the "Service"), whether as a traveller listing spare luggage space or a sender listing an item to be carried. By creating an account or submitting the contact form, you agree to the collection and use of information described here.</p>

                <h2>2. Information we collect</h2>
                <ul>
                    <li><strong>Account information:</strong> name, email address, phone number, country and password (stored hashed, never in plain text).</li>
                    <li><strong>Profile information:</strong> an optional profile photo, and the average rating and review count generated from completed trips.</li>
                    <li><strong>Trip &amp; listing information:</strong> routes, dates, airline, available weight, rate per kilogram, item descriptions and offered rewards you choose to post.</li>
                    <li><strong>Booking &amp; rating information:</strong> requests you send or receive, who pays the traveller, their status, the photo a traveller attaches as proof of delivery, and any ratings or comments left after a completed handover.</li>
                    <li><strong>Messages:</strong> the messages you send and receive in the app's chat.</li>
                    <li><strong>Identity verification (optional):</strong> if you choose to verify, the type of document (CNIC or passport) and a photo of it. It is stored privately, reviewed only by our team, and never shown to other users.</li>
                    <li><strong>Notifications &amp; preferences:</strong> your notification settings, saved route alerts, preferred currency, and a push-notification token that identifies your device.</li>
                    <li><strong>Safety &amp; feedback:</strong> reports you file, users you block, and the ideas and votes you post on the feature board.</li>
                    <li><strong>Contact-form submissions:</strong> the name, email, optional company and message you enter on this website.</li>
                    <li><strong>Technical logs:</strong> basic information such as app version, device type and approximate location derived from your network address, used for security and troubleshooting.</li>
                </ul>
                <p>We do <strong>not</strong> collect payment card details. Packer does not process payments — travellers and senders settle directly with each other.</p>

                <h2>3. How we use your information</h2>
                <ul>
                    <li>To operate the core features of the app — posting trips and items, matching, booking requests, and ratings.</li>
                    <li>To calculate estimated costs based on weight and rate.</li>
                    <li>To send you the notifications and emails the app needs to work — booking updates, new messages, route-alert matches, verification codes and password-reset codes — in line with the notification settings you choose.</li>
                    <li>To maintain trust and safety, including displaying ratings, review counts and an ID Verified badge on public profiles, and reviewing reports.</li>
                    <li>To reply to enquiries submitted through this website.</li>
                    <li>To investigate misuse, fraud or violations of our terms.</li>
                </ul>

                <h2>4. What other users can see</h2>
                <p>Packer is built around real people meeting in person, so some information is visible to other users by design:</p>
                <ul>
                    <li><strong>Public profile:</strong> your name, country, profile photo, average rating and review count are visible to anyone browsing the app.</li>
                    <li><strong>ID Verified badge:</strong> if your identity check is approved, a badge appears on your profile. The document and its details are never shown.</li>
                    <li><strong>Phone number:</strong> becomes visible to the other party only once a booking request is accepted, so you can arrange the handover by call or WhatsApp. It is never shown before that point.</li>
                    <li><strong>Messages:</strong> visible only to the person you send them to.</li>
                    <li><strong>Feature board:</strong> your name is shown next to ideas you post.</li>
                    <li>Your email address and password are never shown to other users.</li>
                </ul>

                <h2>5. Sharing</h2>
                <p>We do not sell personal information and do not share it with advertisers. Limited technical information may be shared with infrastructure providers who host the app and deliver emails and push notifications (for example Google Firebase Cloud Messaging), strictly to operate the Service, and we may disclose information where required by law.</p>

                <h2>6. Retention</h2>
                <p>We retain account and trip information for as long as an account is active, and contact-form enquiries for as long as needed to handle the request.</p>
                <p><strong>Deleting your account.</strong> You can delete your account yourself at any time in the app under Settings → Delete Account (your password is required). This permanently removes your profile, trips, items, bookings, messages, ratings, notifications, saved route alerts and device tokens, along with every photo you uploaded, including any ID document. Requests still in progress are cancelled and the other person is told. Beyond that, we keep information only where the law requires it, for example to resolve a dispute or comply with a legal obligation.</p>

                <h2>7. International use</h2>
                <p>Packer connects travellers and senders across borders. Your information may be processed and stored in a country other than where you live, and may be visible to a user in a different country as part of arranging a cross-border trip.</p>

                <h2>8. Your rights</h2>
                <p>You can view and edit your name, photo, phone number and country in the app, reset a forgotten password with an emailed code, and delete your account under Settings. Depending on where you live, you may also have the right to access a copy of the personal information we hold about you or to object to certain uses of it. Contact us using the details on the <a href="{{ route('contact') }}">contact page</a> and we will respond within a reasonable period.</p>

                <h2>9. Children's privacy</h2>
                <p>Packer is not intended for use by anyone under the age of 18. We do not knowingly collect information from minors.</p>

                <h2>10. Changes</h2>
                <p>We may update this policy from time to time. Material changes will be reflected by the "last updated" date above.</p>

                <h2>11. Contact</h2>
                <p>Questions about this policy can be sent through the <a href="{{ route('contact') }}">contact form</a>.</p>
            </div>
        </div>
    </section>

@endsection
