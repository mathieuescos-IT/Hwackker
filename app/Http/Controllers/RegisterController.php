<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    public function show()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'birth_date' => 'date|nullable|before:14 years ago',
            'facebook_url' => 'required_without:twitter_url',
            'twitter_url' => 'required_without:facebook_url',
            'profile_picture' => 'required|mimes:jpg,png,gif,webp',
            'username' => 'required|string|min:2|max:12|unique:users',
            'country' => 'required|string',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|string|min:1|confirmed',
        ]);
        $avatarname = time() . '.' . $request->profile_picture->extension();
        $request->profile_picture->move(public_path('uploads'), $avatarname);
        $path_img = "uploads/$avatarname";
        $data_users = $request->all();
        $data_users['profile_picture'] = $path_img;
        unset($data_users['_token']);
        unset($data_users['password_confirmation']);

        $user = User::forceCreate($data_users);

        auth()->loginUsingId($user->id);

        return redirect()->route('user', ['username' => $user->username]);
    }
}