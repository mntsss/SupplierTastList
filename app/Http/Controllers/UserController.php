<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use Carbon\Carbon;
use Hash;
use App\User;

class UserController extends Controller
{
    public function __construct(){
      $this->middleware('auth');
    }

    public function changePassword(){
      return view('change-password');
    }

    public function changePasswordSubmit(Request $request)
    {
      Validator::make($request->all(),
      ['oldpass' => 'required|string|min:6|max:85',
        'password' => 'required|string|min:6|confirmed',])->validate();

        if(!Hash::check($request->oldpass, Auth::user()->password))
        {
            $request->session()->flash('error', 'Senas slaptažodis įvestas neteisingai! ');
            return redirect()->back();
        }
      User::find(Auth::user()->id)->update(['password' => Hash::make($request->password)]);

        $request->session()->flash('success', 'Slaptažodis pakeistas sėkmingai!');
        return redirect()->route("active");
    }

    public function register(){
      return view('register-user');
    }

    public function registerSubmit(Request $request){
      Validator::make($request->all(), [
        'email' => 'email|required|min:6|max:35|unique:users',
        'password' => 'required|string|min:6|confirmed',
        'name' => 'required|string|min:6|max:50',
        'role' => 'required|string'
      ])->validate();
      User::create([
        'email' => $request->email,
        'name' => $request->name,
        'password' => Hash::make($request->password),
        'role' => $request->role
      ]);
      $request->session()->flash('success', 'Vartotojas '.$request->name.' užregistruotas.');
      return redirect()->route('active');
    }

    public function updateNotificationSubscription(Request $request){
      if(User::find(Auth::user()->id)->update(['notificationToken' => $request->subscription]))
        return 1;
      else {
        return 0;
      }
    }
}
