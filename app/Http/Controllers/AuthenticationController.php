<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    public function authenticate(Request $request) {
        $validator =  Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(["status" => false ,"data"=> $validator->errors(), "code" => 400]);
        }else{
            $credentials = ["email" => $request->email, "password" => $request->password];
            if(Auth::attempt($credentials)){
                $user =  User::find(Auth::user()->id);
                $token = $user->createToken("token")->plainTextToken;
                return response()->json(["status" => true,"id" => $user->id,"data"=> $user, "token" => $token, "code" => 200]);
            }else{
                return response()->json(["status" => false, "message" => "Either email or password is incorrect"]);
            }
        }
    }

    public function logOut(){
        $user = User::find(Auth::user()->id);
        $user->tokens()->delete();
        return response()->json(["status" => true, "message" => "Successfully logged out"]);
    }
}
