<?php

namespace App\Http\Controllers\Archer;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * GET /archer/profile
     */
    public function show(): Response
    {
        $user   = Auth::user();
        $archer = Archer::where('user_id', $user->id)->firstOrFail();

        return Inertia::render('Archer/Profile', [
            'user'   => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'archer' => [
                'id'             => $archer->id,
                'name'           => $user->name,
                'category'       => $archer->category,
                'date_of_birth'  => $archer->date_of_birth?->format('Y-m-d'),
                'phone'          => $archer->phone,
                'dominant_hand'  => $archer->dominant_hand,
                'age'            => $archer->age,
                'created_at'     => $archer->created_at,
            ],
        ]);
    }

    /**
     * PUT /archer/profile
     */
    public function update(Request $request)
    {
        $user   = Auth::user();
        $archer = Archer::where('user_id', $user->id)->firstOrFail();

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'category'      => ['nullable', 'string', 'in:U12,U15,U18,U21,Senior,Master'],
            'phone'         => ['nullable', 'string', 'max:30'],
            'dominant_hand' => ['nullable', 'string', 'in:right,left'],
        ]);

        // Update central user — must use the central connection
        \App\Models\Central\User::on('mysql')->where('id', $user->id)->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        $archer->update([
            'category'       => $data['category'],
            'date_of_birth'  => $data['date_of_birth'],
            'phone'          => $data['phone'],
            'dominant_hand'  => $data['dominant_hand'],
        ]);

        return redirect()->back()->with('success', 'Profile updated.');
    }

    /**
     * PUT /archer/password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        \App\Models\Central\User::on('mysql')->where('id', $user->id)->update([
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->back()->with('success', 'Password updated.');
    }

    /**
     * DELETE /archer/account
     */
    public function destroy(Request $request)
    {
        $user   = Auth::user();
        $archer = Archer::where('user_id', $user->id)->firstOrFail();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $archer->delete();

        return redirect('/login');
    }
}
