<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function show(Request $request)
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $user = User::where([
            'username' => $request->username,
        ])->first();
        
        // If user doesn't exist or password is not correct
        if(!$user->password  || !Hash::check($user->password,$request->password)){
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        } else {
            auth()->loginUsingId($user->id);
            return redirect()->route('user', ['username' => $user->username]);
        }
    }
}