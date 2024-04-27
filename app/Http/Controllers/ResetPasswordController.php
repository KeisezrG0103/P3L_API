<?php

namespace App\Http\Controllers;

use App\Models\model_customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    public function resetPassword(Request $request)
{        
    $validator = Validator::make($request->all(), [
        'email' => ['required', 'string', 'email', 'max:255'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ]);

    if ($validator->fails()) {
        return new JsonResponse(['success' => false, 'message' => $validator->errors()], 422);
    }

    $user = model_customer::where('Email',$request->email);
    $user->update([
        'password'=>$request->password
    ]);

    $token = $user->first()->createToken('myapptoken')->plainTextToken;

    return new JsonResponse(
        [
            'success' => true, 
            'message' => "Your password has been reset", 
            'token'=>$token
        ], 
        200
    );
}

}
