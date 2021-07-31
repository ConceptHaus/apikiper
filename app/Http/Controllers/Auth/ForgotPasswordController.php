<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Exceptions\Auth\UserNotFoundException;

class ForgotPasswordController extends Controller
{

    private $userService;

    public function __construct(UserService $userService){
        $this->userService = $userService;
    }

    public generatePasswordRecoveryToken(Request $request) {
        try {

            DynamicForm form = commonsControllerService.getFormFactory().form().bindFromRequest(request);
            passwordManagementService.generatePasswordRecoveryToken(form.get("email"), form.get("platformId"), request);
            return response()->json([],200);

        } catch (UserNotFoundException ex) {
            return response()->json(['message'=>ex.getMessage()],400);

        } catch (Exception ex) {
            return response()->json([],500);
        }

    }

    public changePasswordByToken(Request $request) {
        try {

            DynamicForm form = commonsControllerService.getFormFactory().form().bindFromRequest(request);
            passwordManagementService.changePasswordByToken(form.get("token"), form.get("password"));
            return response()->json([],200);

        } catch (PasswordManagementService.AccountNotFoundException ex) {
            return response()->json(['message'=>ex.getMessage()],400);

        } catch (Exception ex) {
            return response()->json([],500);
        }
    }

}
