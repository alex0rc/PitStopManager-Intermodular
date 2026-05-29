<?php

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'Credenciales incorrectas.',
            ]);
        }

        $user = Auth::user();

        if (!$user->isAdmin()) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Solo cuentas de administrador pueden acceder.',
            ]);
        }

        if (!$user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está desactivada.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Sesión cerrada.');
    }

    // --- Puente SPA ---
    public function goToApp(Request $request)
    {
        $user = $request->user();
        $user->tokens()->where('name', 'admin-app-bridge')->delete();
        $token = $user->createToken('admin-app-bridge')->plainTextToken;

        $frontend = rtrim(config('app.frontend_url', 'http://localhost:4200'), '/');
        $target = $frontend . '/auth/callback?token=' . urlencode($token);

        return redirect()->away($target);
    }
}
