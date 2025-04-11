<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Catch_;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'email|required',
            'password' => 'string|required'
        ]);

        if (!Auth::attempt($validated)) {
            return back()->with('error', 'Invalid email or password');
        }

        $user = Auth::user();

        return redirect()->route('products.index')->with('success', 'Login successful');
    }

    public function registrationForm()
    {
        return view('/register');
    }

    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'designation' => 'required|string|max:30',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $validated = $validator->validated();

            $user = User::create([
                'last_name' => $validated['last_name'],
                'first_name' => $validated['first_name'],
                'phone' => $validated['phone'],
                'designation' => $validated['designation'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // Mail::to($user->email)->queue(new WelcomeMail($user));

            Auth::login($user);

            return redirect()->route('products.index')->with('success', "Registration successful! Welcome, {$user->name}.");
        } catch (\Exception $e) {
            Log::error('User registration failed successfully: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Registration failed. Please try again later.')->withInput();
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
