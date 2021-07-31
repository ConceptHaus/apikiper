<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Services\Auth\AuthService;
use App\Http\Services\Users\UserService;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller{
    
    public $userService;
    public $authService;

    public function __construct(
        UserService $service,
        AuthService $auth
    ){
        $this->userService = $service;
        $this->authService = $auth;
    }

    public function resetPassword(Request $request){

        $validator = Validator::make($request->all(),$this->rules());
        if ($validator->fails()) {
            return response()->json([
                'message'=>$validator->errors()->toArray(),
                'error'=>true
            ],400);
        }
            
        $user = $this->userService->findById($this->authService->getCurrentUserId());
        $user->password = Hash::make($request->password);
        $this->userService->save($user);
        
        return response()->json([
            'message'=>"Contraseña Actualizada exitosamente",
            'error'=>false
        ],200);
        
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6'
        ];
    }

}
