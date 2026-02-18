<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if ($request->boolean('remove_profile_photo') && $user->profile_photo_path) {
            Storage::disk('local')->delete($user->profile_photo_path);
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->profile_photo_path = null;
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('local')->delete($user->profile_photo_path);
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $user->profile_photo_path = $request->file('profile_photo')->store('profile-photos', 'local');
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function showPhoto(Request $request, User $user): StreamedResponse
    {
        $actor = $request->user();
        if ((int) $actor->id !== (int) $user->id && $actor->role !== User::ROLE_ADMIN) {
            abort(403);
        }

        $path = (string) $user->profile_photo_path;
        if ($path === '') {
            abort(404);
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->response($path);
        }

        // Backward compatibility for previously stored public photos.
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->response($path);
        }

        abort(404);
    }
}
