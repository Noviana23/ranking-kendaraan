<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('user_id')) {
            return redirect()->route('dashboard');
        }

        return view('login');
    }

    public function showRegister()
    {
        if (session()->has('user_id')) {
            return redirect()->route('dashboard');
        }

        return view('register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'username' => ['required', 'string', 'min:3', 'max:60', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'pimpinan'])],
        ], [
            'username.unique' => 'Username sudah digunakan. Silakan pilih username lain.',
        ]);

        // Menyesuaikan dengan dua skema tabel users yang pernah dipakai proyek.
        $nameColumn = Schema::hasColumn('users', 'full_name') ? 'full_name' : 'name';
        $passwordColumn = Schema::hasColumn('users', 'password_hash') ? 'password_hash' : 'password';

        $user = new User();
        $user->username = $validated['username'];
        $user->role = $validated['role'];
        $user->setAttribute($nameColumn, $validated['name']);
        // Paksa bcrypt agar hash akun baru konsisten dengan konfigurasi login.
        $user->setAttribute($passwordColumn, Hash::driver('bcrypt')->make($validated['password']));
        $user->save();

        return redirect()->route('login')->with('success', 'Pendaftaran berhasil. Silakan masuk menggunakan akun yang baru dibuat.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('username', $credentials['username'])->first();
        $passwordColumn = Schema::hasColumn('users', 'password_hash') ? 'password_hash' : 'password';
        $storedPassword = $user ? $user->getAttribute($passwordColumn) : null;

        if (!$user || !is_string($storedPassword) || !$this->verifyPasswordAndUpgrade($credentials['password'], $storedPassword, $user, $passwordColumn)) {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Username atau password salah. Periksa kembali data akun yang digunakan.');
        }

        $displayName = $user->getAttribute('full_name')
            ?: $user->getAttribute('name')
            ?: $user->username;

        $request->session()->regenerate();
        $request->session()->put([
            'user_id' => $user->id,
            'user_name' => $displayName,
            'user_role' => $user->role,
            'user_username' => $user->username,
            'user_photo' => $user->getAttribute('profile_photo'),
        ]);

        return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang, ' . $displayName . '.');
    }

    /**
     * Verifikasi beberapa format hash lama yang mungkin sudah tersimpan.
     * Setelah verifikasi berhasil, hash lama langsung dimigrasikan ke bcrypt.
     */
    private function verifyPasswordAndUpgrade(string $plainPassword, string $storedPassword, User $user, string $passwordColumn): bool
    {
        $info = password_get_info($storedPassword);
        $algoName = $info['algoName'] ?? 'unknown';

        if ($algoName !== 'unknown') {
            // password_verify mendukung bcrypt maupun Argon2 dan tidak memaksa driver Laravel tertentu.
            $valid = password_verify($plainPassword, $storedPassword);
        } else {
            // Kompatibilitas untuk akun lama yang mungkin tersimpan sebagai SHA-256, MD5, atau teks biasa.
            $valid = false;

            if (preg_match('/^[a-f0-9]{64}$/i', $storedPassword)) {
                $valid = hash_equals(strtolower($storedPassword), hash('sha256', $plainPassword));
            } elseif (preg_match('/^[a-f0-9]{32}$/i', $storedPassword)) {
                $valid = hash_equals(strtolower($storedPassword), md5($plainPassword));
            } else {
                $valid = hash_equals($storedPassword, $plainPassword);
            }
        }

        if (!$valid) {
            return false;
        }

        // Migrasikan hash yang bukan bcrypt atau bcrypt yang sudah perlu diperbarui.
        $needsBcryptUpgrade = $algoName !== 'bcrypt'
            || password_needs_rehash($storedPassword, PASSWORD_BCRYPT);

        if ($needsBcryptUpgrade) {
            $user->setAttribute($passwordColumn, Hash::driver('bcrypt')->make($plainPassword));
            $user->save();
        }

        return true;
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
