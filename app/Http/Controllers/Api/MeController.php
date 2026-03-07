<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\Coach;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * GET /api/v1/me
     * Return the authenticated user's profile, role, and linked tenant record.
     */
    public function show(Request $request): JsonResponse
    {
        $user  = $request->user();
        $roles = $user->getRoleNames();

        $profile = null;

        if ($roles->contains('archer')) {
            $archer  = Archer::where('user_id', $user->id)->first();
            $profile = $archer ? [
                'type'          => 'archer',
                'id'            => $archer->id,
                'category'      => $archer->category,
                'dominant_hand' => $archer->dominant_hand,
                'coach_id'      => $archer->coach_id,
            ] : null;
        } elseif ($roles->contains('coach')) {
            $coach   = Coach::where('user_id', $user->id)->first();
            $profile = $coach ? [
                'type'  => 'coach',
                'id'    => $coach->id,
                'level' => $coach->level,
            ] : null;
        }

        return response()->json([
            'id'      => $user->id,
            'name'    => $user->name,
            'email'   => $user->email,
            'roles'   => $roles,
            'profile' => $profile,
        ]);
    }
}
