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

        $check_password = Hash::check($request->password, $user->password);
        if($user && $check_password){
            auth()->loginUsingId($user->id);
            return redirect()->route('user', ['username' => $user->username]);
        } else {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }
    }
}