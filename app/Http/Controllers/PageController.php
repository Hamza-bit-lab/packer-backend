<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', ['navTheme' => 'dark']);
    }

    public function howItWorks(): View
    {
        return view('pages.how-it-works', ['navTheme' => 'light']);
    }

    public function features(): View
    {
        return view('pages.features', ['navTheme' => 'dark']);
    }

    public function faq(): View
    {
        return view('pages.faq', ['navTheme' => 'light']);
    }

    public function about(): View
    {
        return view('pages.about', ['navTheme' => 'dark']);
    }

    public function contact(): View
    {
        return view('pages.contact', ['navTheme' => 'light']);
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'company' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // No mail transport is configured for this project, so the enquiry is
        // recorded to the application log for follow-up rather than sent.
        logger()->info('Packer contact enquiry', $validated);

        return redirect()
            ->route('contact')
            ->with('status', 'Thank you — your message has been received. We will be in touch shortly.');
    }

    public function privacy(): View
    {
        return view('pages.privacy', ['navTheme' => 'light']);
    }

    public function terms(): View
    {
        return view('pages.terms', ['navTheme' => 'light']);
    }
}
