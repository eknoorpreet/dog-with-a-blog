<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

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

    public function viewProfile(User $user)
    {
        $posts = $user->posts();
        return view('profile-posts', ['avatar' => $user->avatar, 'username' => $user->username, 'posts' => $posts->latest()->get(), 'postCount' => $posts->count()]);
    }

    public function showAvatarForm()
    {
        return view('avatar-form');
    }

    public function updateAvatar(Request $request, User $user)
    {
        $request->validate(['avatar' => 'required|image|max:3000']);
        $user = auth()->user();
        $filename = $user->id . "-" . uniqid() . ".jpg";
        $manager = new ImageManager(new Driver());
        $image = $manager->read($request->file('avatar'));
        // returns the raw resized data to save somewhere
        $imgData = $image->cover(120, 120)->toJpeg();
        Storage::put('public/avatars/' .  $filename, $imgData);

        // Access old avatar
        $oldAvatar = $user->avatar;
        $user->avatar = $filename;
        $user->save();

        // Delete old avatar
        if ($oldAvatar != "/fallback-avatar.jpg") {
            Storage::delete(str_replace('/storage/', 'public/', $oldAvatar));
        }
        return back()->with('success', 'You have successfully updated your avatar!');
    }
}
