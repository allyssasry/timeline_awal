<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller {
    public function registerForm() { return view('Auth.register'); }
    public function loginForm() { return view('Auth.login'); }

    public function register(Request $request) {
        $request->validate([
            'name'=>'required',
            'username'=>'required|unique:users',
            'password'=>'required',
            'role'=>'required|in:digital_banking,it,supervisor',
        ]);

        User::create([
            'name'=>$request->name,
            'username'=>$request->username,
            'password'=>Hash::make($request->password),
            'role'=>$request->role,
        ]);

        return redirect('/login')->with('success','Register berhasil!');
    }

    public function login(Request $request) {
        $credentials = $request->only('username','password');
        if(Auth::attempt($credentials)){
            $user = Auth::user();
            if($user->role == 'digital_banking') return redirect('/dig/dashboard');
            if($user->role == 'it') return redirect('/it/dashboard');
            if($user->role == 'supervisor') return redirect('/supervisor/dashboard');
        }
        return back()->with('error','Login gagal!');
    }

    public function logout() {
        Auth::logout();
        return redirect('/login');
    }
}
