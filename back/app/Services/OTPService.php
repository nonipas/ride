<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;

class OTPService
{
    public function otpRequest($phone){
        $client = new Client();
        $response = $client->request('POST', 'https://api.sendchamp.com/api/v1/verification/create', [
            'json' => [
                'channel' => 'sms',
                'sender' => 'Sendchamp',
                'token_type' => 'numeric',
                'token_length' => 6,
                'expiration_time' => 5,
                'customer_mobile_number' => $phone,
            ],
            'headers' => [
                'Authorization' => 'Bearer sendchamp_live_$2a$10$L0hf7vujlPxbbIgvkxGRjuoIpbJDQtebaJqy8AkDhYZkMcaJvHi0O',
                "Accept: application/json,text/plain,*/*",
                'content-type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);

    }

    public function saveOTP($code, $phone): void
    {
        $user = User::where('phone', $phone)->first();

        if($user){
            $user->login_code = $code;
            $user->save();
        }

    }

    public function send($number): array
    {
        $data = [];

        $existing = User::where('phone',$number)->first();

        if($existing){
            $otp_time = Carbon::parse($existing->otp_expires_at);
            if($otp_time->greaterThan(Carbon::now())){
                $data['status'] = true;
                $data['message'] = "OTP request interval is 2 minutes, use old code or wait a little";

                return $data;
            }

        }

        try {

            $res = $this->otpRequest($this->getOTPNo($number));

            $code = $res['data']['token'];

            $this->saveOTP($code, $number);

            $data['status'] = true;
//            $data['code'] = $code;

        }catch (Exception $e){
            $data['status'] = false;
            $data['info'] = $e->getMessage();
            $data['message'] = "something went wrong, please contact support ".$e->getMessage();
        }

        return $data;
    }

    public function verifyOTP($code, $phone): array
    {
        $otp = user::where('phone', $phone)->where('login_code', $code)->first();
        $data = [];
        if(!$otp){
            $data['status'] = false;
            $data['message'] = "Invalid OTP code";
        }
        if($otp){
            $otp_time = Carbon::parse($otp->otp_expires_at);
            if(Carbon::now()->greaterThan($otp_time)){
                $data['status'] = false;
                $data['message'] = "OTP expired, please request new code";
            }else{

                $data['status'] = true;
            }
        }
        return $data;
    }

    public function getOTPNo($phone): string
    {
        //add country code to phone number
        $phone = "234".substr($phone, 1);
        return $phone;
    }
}
