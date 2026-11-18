<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfilController extends Controller
{
    // Informations personnelles : nom, e-mail, photo de profil
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user->name  = $data['name'];
        $user->email = $data['email'];

        if ($request->hasFile('avatar')) {
            $this->deleteAvatar($user);
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        } elseif ($request->boolean('remove_avatar')) {
            $this->deleteAvatar($user);
            $user->avatar = null;
        }

        $user->save();

        return redirect()->route('profil')->with('success', 'Profil mis à jour.');
    }

    // Changement de mot de passe (l'ancien mot de passe est exigé)
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8), 'different:current_password'],
        ], [
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.different'                => 'Le nouveau mot de passe doit être différent de l\'actuel.',
        ]);

        $user = $request->user();
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return redirect()->route('profil')
            ->with('success', 'Mot de passe modifié.')
            ->with('tab', 'security');
    }

    private function deleteAvatar($user): void
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
    }
}