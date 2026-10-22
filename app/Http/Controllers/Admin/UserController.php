<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $query = User::query()->latest();

        if ($search) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
        }

        return view('admin.users.index', [
            'users' => $query->paginate(20)->withQueryString(),
            'search' => $search,
        ]);
    }

    public function toggleAdmin(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'You cannot change your own admin access.']);
        }

        $user->forceFill(['is_admin' => ! $user->is_admin])->save();

        return back()->with('status', $user->is_admin
            ? "{$user->name} is now an admin."
            : "{$user->name} is no longer an admin.");
    }

    public function toggleBan(Request $request, User $user): RedirectResponse
    {
        if ($user->is_admin) {
            return back()->withErrors(['user' => 'You cannot ban another admin.']);
        }

        if ($user->is_banned) {
            $user->unban();

            return back()->with('status', "{$user->name} has been unbanned.");
        }

        $user->ban();

        return back()->with('status', "{$user->name} has been banned.");
    }
}
