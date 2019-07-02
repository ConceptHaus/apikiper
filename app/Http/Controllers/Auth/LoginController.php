<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use App\Http\Requests;
use DB;
use Mail;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;

use App\Modelos\User;

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


    /**
     * Where to redirect users after login.
     *
     * @var string
     */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function login(Request $request){
        $credentials = $request->only('email', 'password');
        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try{
        
            $this->guard()->logout();
            return response()->json(['message' => 'Successfully logged out'],200);
        
        }catch(Exception $e){
            Bugsnag::notifyException(new RuntimeException("El usuario no pudo hacer login"));
            return response()->json([
                'message'=>$e,
                'error'=>true
            ],401); 
        }
        
    }
    
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

       /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() / 60,
            'hash'=>$this->hash($this->guard()->user()->id)
        ]);
    }

    protected function hash($id){
        return hash_hmac(
        'sha256', // hash function
        $id, // user's id
        '7hVgZ2IFrt6AFM9VWQvy54wMyQk8sDyIY5CNjyFF' // secret key (keep safe!)
        );
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }


}
