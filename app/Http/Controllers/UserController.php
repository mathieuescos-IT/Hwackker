<?php

namespace App\Http\Controllers;

use App\Models\Hwack;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index(Request $request)
    {
        if ($request->has('username')) {
            $user = User::all()->where('username', $request->get('username'))->first();
            return view('user', [
                'user' => $user,
                'hwacks' => Hwack::where('user_id', $user->id, 'private', false)->latest('created_at')->simplePaginate(100),
            ]);
        }

        $user = auth()->user();

        if (!$user) {
            return redirect()->route('user');
        }

        return view('user', [
            'user' => $user,
            'hwacks' => Hwack::where('private', false)->latest('created_at')->simplePaginate(100),
        ]);
    }

    public function createHwack(Request $request)
    {
        $request->validate([
            'image' => 'required|mimes:jpg,png,gif,webp',
            'content' => 'required|max:500',
            'private' => 'nullable',
        ]);

        $data_hwack = $request->all();

        // Check if img is uploaded
        if ($request->hasFile('image')) {
            $avatarname = time() . '.' . $request->image->extension();
            $request->image->move(public_path('uploads'), $avatarname);
            $path_img = "uploads/$avatarname";
            $data_hwack['image'] = $path_img;
        }

        // Check if private is checked
        $checkbox = $request->input('private');
        if($checkbox){
            $data_hwack['private'] = true;
        }

        unset($data_hwack['_token']);

        $user = User::find($request->get('user_id'));

        Hwack::forceCreate($data_hwack);

        return redirect()->route('user', ['username' => $user->username]);
    }
}