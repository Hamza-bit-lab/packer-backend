@extends('layouts.app')

@section('title', 'Terms & Conditions')
@section('meta_description', 'The terms that apply to your use of the Packer website.')
@section('body_id', 'terms')

@section('content')

    <section class="pc-hero-lite">
        <div class="pc-container">
            <span class="pc-eyebrow pc-reveal">Legal</span>
            <h1 class="pc-display pc-reveal" data-delay="1">Terms &amp; Conditions</h1>
        </div>
    </section>

    <section class="pc-section pc-section--tight">
        <div class="pc-container">
            <div class="pc-prose pc-reveal">
                <p class="pc-prose__updated">Last updated {{ now()->format('F Y') }}</p>

                <h2>Acceptance</h2>
                <p>By accessing this website you agree to these terms. If you do not agree with them, please do not use the site.</p>

                <h2>Use of the site</h2>
                <p>This website is provided for general information about Packer. You may view and share its content for personal, non-commercial purposes. You agree not to misuse the site, interfere with its operation, or attempt to access it other than through the interfaces we provide.</p>

                <h2>What Packer is</h2>
                <p>Packer is a marketplace that introduces travellers with spare luggage space to senders who need something carried abroad. We do not transport, store or handle any item ourselves — the agreement to carry an item, and the responsibility for it, is between the traveller and the sender.</p>

                <h2>Your account</h2>
                <p>You are responsible for keeping your login secure and for what happens under your account. You can delete your account at any time from the app under Settings. We may suspend or remove accounts that break these terms or our safety rules — for example by sending or carrying prohibited items, or by misusing reports, messages or ratings.</p>

                <h2 id="prohibited-items">Prohibited items &amp; liability</h2>
                <div class="pc-callout">
                    @include('partials.disclaimer')
                </div>

                <h2>Information accuracy</h2>
                <p>We take care to keep the content accurate and current, but we provide it “as is” without warranties of any kind. Product descriptions on this site are for information only and do not form part of any contract.</p>

                <h2>Intellectual property</h2>
                <p>The Packer name, brand, text and design elements on this site are owned by Packer or its licensors. Photography is licensed from third-party providers and is used for illustration. Nothing here transfers any ownership to you.</p>

                <h2>Third-party links</h2>
                <p>The site may link to external resources we do not control. We are not responsible for their content or practices.</p>

                <h2>Limitation of liability</h2>
                <p>To the extent permitted by law, Packer is not liable for any indirect or consequential loss arising from your use of, or inability to use, this website.</p>

                <h2>Changes</h2>
                <p>We may revise these terms at any time. The version published here, with the date above, is the one that applies.</p>

                <h2>Contact</h2>
                <p>Questions about these terms can be sent through the <a href="{{ route('contact') }}">contact form</a>.</p>
            </div>
        </div>
    </section>

@endsection
