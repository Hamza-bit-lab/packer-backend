<footer class="pc-footer">
    <div class="pc-container">
        <div class="pc-footer__grid">
            <div class="pc-footer__brand">
                <a href="{{ route('home') }}" class="pc-footer__word">{{ config('app.name') }}</a>
                <p class="pc-footer__tag">Peer-to-peer crowdshipping<br>for travellers and senders.</p>
            </div>

            <nav class="pc-footer__col" aria-label="Product">
                <h2 class="pc-footer__heading">Product</h2>
                <a href="{{ route('how-it-works') }}">How It Works</a>
                <a href="{{ route('features') }}">Features</a>
                <a href="{{ route('faq') }}">FAQ</a>
            </nav>

            <nav class="pc-footer__col" aria-label="Company">
                <h2 class="pc-footer__heading">Company</h2>
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('contact') }}">Contact</a>
            </nav>

            <nav class="pc-footer__col" aria-label="Legal">
                <h2 class="pc-footer__heading">Legal</h2>
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms &amp; Conditions</a>
            </nav>
        </div>

        <div class="pc-footer__base">
            <p>&copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.</p>
            <p class="pc-footer__note">Turn spare luggage space into someone's fastest delivery.</p>
        </div>
    </div>
</footer>
