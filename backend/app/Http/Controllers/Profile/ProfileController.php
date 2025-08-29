<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{

    public function show(Request $request)
    {
        return response()->json([
            'data' => $request->user()->fresh(),
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->only(['name', 'email', 'bio']);

        // Avatar upload handling
        if ($request->hasFile('avatar')) {
            // delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        // update user data
        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data'    => $user->fresh(),
        ]);
    }
}