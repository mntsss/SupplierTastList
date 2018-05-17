<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Auth;
class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = "/active";

    /**
     * Create a new controller instance.
     *
     * @return void
     */
/*
      protected function redirectTo()
      {
        if(Auth::user()->role == "Vadybininkas")
          return $this->redirectTo = '/manager';
        if(Auth::user()->role == "Perziura")
            return $this->redirectTo = '/viewer';
        if(Auth::user()->role == "Tiekejas")
              return $this->redirectTo = '/supplier';
      }
*/

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }
}
