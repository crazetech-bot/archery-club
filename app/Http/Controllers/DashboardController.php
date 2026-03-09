<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Redirect the authenticated user to their role-appropriate dashboard.
     *
     * This controller is Module-1 only. Subsequent modules will introduce
     * role-specific dashboard controllers with full data rendering.
     */
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        return match (true) {
            $user->hasRole('club_admin') => redirect()->route('admin.members.index'),
            $user->hasRole('coach')      => redirect()->route('profile.edit'),
            default                      => redirect()->route('profile.edit'),
        };
    }
}
