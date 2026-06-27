<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fullName' => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($request->user()->id)],
            'phone'    => ['nullable', 'string', 'max:20', 'regex:/^(?:\+63|0)[0-9]{10}$/'],
            'barangay' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        $nameParts = explode(' ', trim($data['fullName']), 2);
        $firstName = $nameParts[0];
        $lastName  = $nameParts[1] ?? '';

        $user->update([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?: null,
            'city'       => $data['barangay'] ?: null,
        ]);

        return response()->json([
            'message'  => 'Personal information saved.',
            'fullName' => $user->name,
            'email'    => $user->email,
            'phone'    => $user->phone,
            'barangay' => $user->city,
        ]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $data = $request->validate([
            'emailNotifications' => ['required', 'string', Rule::in(['All updates', 'Bookings only', 'Messages only', 'None'])],
            'language'           => ['required', 'string', Rule::in(['Filipino', 'English'])],
        ]);

        $user = $request->user();

        $user->update([
            'email_notifications' => $data['emailNotifications'],
            'language'            => $data['language'],
        ]);

        return response()->json([
            'message' => 'Preferences saved.',
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => $path]);

        return response()->json([
            'message'   => 'Avatar uploaded.',
            'avatar_url' => Storage::url($path),
        ]);
    }
}
