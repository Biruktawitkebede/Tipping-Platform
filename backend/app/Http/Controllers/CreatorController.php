<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class CreatorController extends Controller
{
    //
    public function show($id)
    {
        $creator = User::where('id', $id)
            ->where('role', 'creator')
            ->firstOrFail();

        return response()->json([
            'id' => $creator->id,
            'name' => $creator->name,
            'bio' => $creator->bio,
            'avatar' => $creator->avatar_url, // uses your accessor
            'currency' => 'ETB',
        ]);
    }
}
