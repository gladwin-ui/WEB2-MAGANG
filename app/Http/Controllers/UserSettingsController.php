<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserSettingsController extends Controller
{
    public function edit()
    {
        return view('users.settings');
    }

    public function photo(User $user)
    {
        abort_unless(
            $user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path),
            404
        );

        return Storage::disk('public')->response($user->profile_photo_path);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $removePhoto = $request->boolean('remove_photo');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'phone.required' => 'Nomor telepon wajib diisi untuk verifikasi akun.',
            'profile_photo.image' => 'File foto profil harus berupa gambar.',
            'profile_photo.mimes' => 'Foto profil harus berformat JPG, JPEG, PNG, atau WEBP.',
            'profile_photo.max' => 'Ukuran foto profil maksimal 2MB.',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'];

        if ($removePhoto) {
            if (!empty($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->profile_photo_path = null;
        } elseif ($request->hasFile('profile_photo')) {
            if (!empty($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->profile_photo_path = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user->save();

        return back()->with('success', 'Pengaturan akun berhasil diperbarui.');
    }
}
