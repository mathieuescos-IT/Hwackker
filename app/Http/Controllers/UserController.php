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

        $user = User::all()->where('username', $request->get('username'))->first();

        // If user does not exist redirect to user page
        if ($user) {
            // Do nothing
        } elseif($request->has('username')){
            return redirect()->route('user');
        }

        // ?username=mathieu
        if ($request->has('username')) {
            $hwacks = Hwack::where(
                [
                    ['user_id', '=', $user->id],

                ])
                ->latest('created_at')->simplePaginate(100);
        } else {
            // /user/
            $user = auth()->user();
            $hwacks = Hwack::where('private', false)->latest('created_at')->simplePaginate(100);
        }
        
        // ?username= && private == true Others username
        if ($request->has('username') && $request->has('private') && $request->get('private') == 'true') {
            $hwacks = Hwack::where(
                [
                    ['user_id', '=', $user->id],
                    ['private', '=', true],
                ]
            )->latest('created_at')->simplePaginate(100);
        }
        
        // ?username= && private == false Others username
        if ($request->has('username') && $request->get('private') == 'false') {
            $hwacks = Hwack::where(
                [
                    ['user_id', '=', $user->id],
                    ['private', '=', false]
                ]
            )->latest('created_at')->simplePaginate(100);
        }

        // ?search= && private == false
        if($request->get('search')) {
            $search = $request->get('search');
            $hwacks = Hwack::where(
                [
                    ['private', '=', 'false'],
                    ['content', '=', "$search"],
                ]
            
            )->latest('created_at')->simplePaginate(100);
        }

        

        return view('user', [
            'user' => $user,
            'hwacks' => $hwacks,
        ]);
    }

    public function createHwack(Request $request)
    {
        $request->validate([
            'image' => 'mimes:jpg,png,gif,webp',
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