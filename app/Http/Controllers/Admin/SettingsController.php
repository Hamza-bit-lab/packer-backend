<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => collect(array_keys(Setting::DEFAULTS))
                ->mapWithKeys(fn (string $key) => [$key => Setting::enabled($key)]),
            'admins' => User::query()->where('is_admin', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        // An unchecked box isn't submitted at all, so absence means "off".
        foreach (array_keys(Setting::DEFAULTS) as $key) {
            Setting::put($key, $request->boolean($key));
        }

        return back()->with('status', 'Settings saved.');
    }
}
