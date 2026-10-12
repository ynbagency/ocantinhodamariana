<?php
namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController
{
    public function create() { return view('auth.login'); }

    public function store(Request $request)
    {
        $credentials = $request->validate(['email'=>'required|email','password'=>'required|string']);
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email'=>'Email ou palavra-passe incorretos.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return redirect()->intended(route('operation.dashboard'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
