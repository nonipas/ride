<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\LoginNeedsVerification;
use App\Services\OTPService;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    //peform login
    public function submit(Request $request){

      //validate the phone number

      $request->validate([
        'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10'
      ]);
      
      //check if the user exists

      $user = User::firstOrCreate([
        'phone' => $request->phone
      ]);

      if(!$user){
        return response()->json([
          'message' => 'Could not process a user with that phone number.'
        ],401);
      }

      //send the user a one-time use code using OTPService

        $response = (new OTPService())->send($user->phone);

        if(!$response['status']){
          return response()->json([
            'message' => 'Could not send a one-time use code to your phone number.'
          ],401);
        }

        //return a response

        return response()->json([
          'message' => 'A one-time use code has been sent to your phone number.'
        ],200);
    }

    //verify login
    public function verify(Request $request){

      //validate the request

      $request->validate([
        'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
        'login_code' => 'required|numeric'
      ]);

      //check if the user exists

      $user = User::where('phone',$request->phone)->first();

      if(!$user){
        return response()->json([
          'message' => 'Could not process a user with that phone number.'
        ],401);
      }

      //check if the code is valid

      if($user->login_code != $request->login_code){
        return response()->json([
          'message' => 'Invalid verification code.'
        ],401);
      }

      //check if the code has expired

    //   if($user->otp_expires_at < now()){
    //     return response()->json([
    //       'message' => 'Code has expired.'
    //     ],401);
    //   }

        //update the user

        $user->login_code = null;
        $user->otp_expires_at = null;
        $user->save();

      //generate a token for the user

      $token = $user->createToken($request->login_code)->plainTextToken;

      //return a response

      return $token;

    }
}
