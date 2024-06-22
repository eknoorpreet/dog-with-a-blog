<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function register(Request $request)
    {
        $incomingFields = $request->validate(
            [
                'username' => ['required', 'min: 3', 'max: 20', Rule::unique('users', 'username')],
                'email' => ['required', 'email', Rule::unique('users', 'email')],
                'password' => ['required', 'min: 8', 'confirmed']
            ]
        );

        $incomingFields['password'] = bcrypt($incomingFields['password']);
        $user = User::create($incomingFields);
        // log in the user as well
        auth()->login($user);
        return redirect('/')->with('success', 'Thank you for registering with us!');
    }

    public function login(Request $request)
    {
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required',
        ]);

        if (auth()->attempt(
            [
                'username' => $incomingFields['loginusername'],
                'password' => $incomingFields['loginpassword']
            ]
        )) {
            // login (generate and send cookie session to the browser which the browser
            // will send back to the server on every incoming request)
            $request->session()->regenerate();
            return redirect('/')->with('success', 'You have successfully logged in, ' . $incomingFields['loginusername'] . '!');
        } else {
            return redirect('/')->with('failure', 'Invalid credentials!');
        }
    }

    public function logout()
    {
        auth()->logout();
        return redirect('/')->with('success', 'You have successfully logged out!');
    }

    public function showCorrectHomepage()
    {
        if (auth()->check()) {
            return view('homepage-feed');
        } else {
            return view('homepage');
        }
    }
}
