<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function authenticate(Request $request)
    {
        $validator = $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|string|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/'
            // Min 8 characters, must contain: 
            // - at least one lowercase letter,
            // - at least one uppercase letter
            // - at least one digit
            // - a special character 
        ]);

        $user = User::where('email', $request->input('email'))->first();
        if(!$user) {
            $hash = Hash::make($request->input('password'));
            return response(["status" => false, "message" => "User not found."], 404);
        }

        if(Hash::check($request->input('password'), $user->password)) {
            $apikey = base64_encode(Str::random(40));
            User::where('email', $request->input('email'))->update(['api_key' => $apikey]);

            return response(["status" => true, "api_key" => $apikey], 200);
        }else{
            return response(["status" => false, "message" => "Unauthorized"], 401);
        }
    }
}
