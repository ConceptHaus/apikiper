<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Http\Services\Password\PasswordManagementService;
use App\Http\Exceptions\Auth\UserNotFoundException;
use App\Http\Exceptions\Auth\VerificationTokenNotFoundException;

class ForgotPasswordController extends Controller
{

    private $passwordManagementService;

    public function __construct(PasswordManagementService $passwordManagementService){
        $this->passwordManagementService = $passwordManagementService;
    }

    public generatePasswordRecoveryToken(Request $request) {
        try {

            $validator = Validator::make($request->all(),['email' => 'required|email']);
            if ($validator->fails()) 
                return response()->json(['message'=>$validator->errors()->toArray()],400);
            $this->passwordManagementService->generatePasswordRecoveryToken($request->email);
            return response()->json([],200);

        } catch (UserNotFoundException $ex) {
            return response()->json(['message'=>$ex->message],400);

        } catch (Exception $ex) {
            return response()->json([],500);
        }

    }

    public changePasswordByToken(Request $request) {
        try {

            $validator = Validator::make($request->all(),['email' => 'required|email', 'token' => 'required|string']);
            if ($validator->fails()) 
                return response()->json(['message'=>$validator->errors()->toArray()],400);
            $this->passwordManagementService->changePasswordByToken($request->token, $request->password);
            return response()->json([],200);

        } catch (VerificationTokenNotFoundException $ex) {
            return response()->json(['message'=>$ex->message],400);

        } catch (Exception $ex) {
            return response()->json([],500);
        }
    }

}
