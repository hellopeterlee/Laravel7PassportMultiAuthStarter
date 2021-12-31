<?php
/**
 * Created by PhpStorm.
 * Date: 2021/12/30 11:01
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Google2FA;

class DemoController
{
    public function oauth()
    {
        $data = [
            'grant_type' => 'password',
            'client_id' => config('passport.clients.password.client_id'),
            'client_secret' => config('passport.clients.password.client_secret'),
            'username' => 'admin',
            'password' => 'admin',
            'provider' => 'admin_users',
            'scope' => '*',
        ];

        appLog(url('/oauth/token'), $data);

        $res = Http::asForm()->post(url('/oauth/token'), $data);

//        $res = app(Client::class)->post(url('/demo/test'), [
//            'form_params' => $data,
//        ]);

//        var_dump($data);

        return $res;
    }

    public function googlekey()
    {
        return Google2FA::generateSecretKey();
    }

    public function checkKey(Request $request)
    {
        $inputCode = $request->get("key");
        return Google2FA::verify($inputCode, "TDNEOH7DKYK43JMA") ? "true" : "false";
    }
}
