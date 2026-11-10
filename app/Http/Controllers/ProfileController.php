<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = User::findOrFail(session('user_id'));
        $nameColumn = Schema::hasColumn('users', 'full_name') ? 'full_name' : 'name';
        $displayName = $user->getAttribute($nameColumn) ?: $user->username;

        return $this->page('profile', compact('user', 'displayName'), 'Profil Akun', 'profile');
    }

    public function update(Request $request)
    {
        $user = User::findOrFail(session('user_id'));
        $nameColumn = Schema::hasColumn('users', 'full_name') ? 'full_name' : 'name';
        $passwordColumn = Schema::hasColumn('users', 'password_hash') ? 'password_hash' : 'password';

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:60',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'current_password' => ['nullable', 'required_with:password', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], [
            'username.unique' => 'Username sudah digunakan oleh akun lain.',
            'password.confirmed' => 'Konfirmasi password baru tidak sama.',
        ]);

        if (!empty($validated['password'])) {
            $currentPassword = (string) ($validated['current_password'] ?? '');
            $storedPassword = (string) $user->getAttribute($passwordColumn);
            $passwordInfo = password_get_info($storedPassword);
            $passwordIsValid = Hash::check($currentPassword, $storedPassword);

            // Dukungan untuk akun lama yang hash-nya belum sempat dimigrasikan saat login.
            if (!$passwordIsValid && ($passwordInfo['algoName'] ?? 'unknown') === 'unknown') {
                if (preg_match('/^[a-f0-9]{64}$/i', $storedPassword)) {
                    $passwordIsValid = hash_equals(strtolower($storedPassword), hash('sha256', $currentPassword));
                } elseif (preg_match('/^[a-f0-9]{32}$/i', $storedPassword)) {
                    $passwordIsValid = hash_equals(strtolower($storedPassword), md5($currentPassword));
                } else {
                    $passwordIsValid = hash_equals($storedPassword, $currentPassword);
                }
            }

            if (!$passwordIsValid) {
                return back()->withErrors([
                    'current_password' => 'Password saat ini tidak sesuai.',
                ])->withInput($request->except(['password', 'password_confirmation', 'current_password']));
            }

            $user->setAttribute($passwordColumn, Hash::driver('bcrypt')->make($validated['password']));
        }

        $user->setAttribute($nameColumn, $validated['name']);
        $user->username = $validated['username'];

        if ($request->hasFile('profile_photo')) {
            $oldPhoto = $user->getAttribute('profile_photo');
            $newPhoto = $request->file('profile_photo')->store('profile-photos', 'public');

            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }

            $user->setAttribute('profile_photo', $newPhoto);
        }

        $user->save();

        session()->put([
            'user_name' => $validated['name'],
            'user_username' => $validated['username'],
            'user_photo' => $user->getAttribute('profile_photo'),
        ]);

        return redirect()->route('profile')->with('success', 'Profil akun berhasil diperbarui.');
    }
}
