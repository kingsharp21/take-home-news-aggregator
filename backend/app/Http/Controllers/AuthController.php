<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function register (Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
        if ($validator->fails())
        {
            return response(["status" =>"error",'message'=>$validator->errors()->all()], 422);
        }
        $request['password']=Hash::make($request['password']);
        $request['remember_token'] = Str::random(10);
        $user = User::create($request->toArray());
        $response = ["status" =>"success","data"=>$user];
        return response($response, 200);
    }


    public function login (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
        $user = User::where('email', $request->email)->first();
       
        if ($user) {
            
            if (Hash::check($request->password, $user->password)) {
                // $preference = Preference::where('user_id', $user->id)->first();
                // $user['preference'] = array(
                //     "authers" => $preference->authors,
                //     "sources" => $preference->sources
                // );
                $response = ["status" =>"success","data"=>$user];
                return response($response, 200);
            } else {
                $response = ["status" =>"error","message" => "Password mismatch"];
                return response($response, 422);
            }
        } else {
            $response = ["status" =>"error","message" =>'User does not exist'];
            return response($response, 422);
        }
    }
}
