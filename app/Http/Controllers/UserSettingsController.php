<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class UserSettingsController extends Controller
{
    public function photo(User $user)
    {
        abort_unless(
            $user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path),
            404
        );

        return Storage::disk('public')->response($user->profile_photo_path);
    }
}
