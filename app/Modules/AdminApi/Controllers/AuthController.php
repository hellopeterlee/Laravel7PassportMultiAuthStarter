<?php
/**
 * Created by PhpStorm.
 * Date: 2021/12/30 09:42
 */

namespace App\Modules\AdminApi\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class AuthController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api')->except([
            'login', 'register', 'forgetPassword', 'resetPassword', 'resetPasswordByToken',
            'sendLoginVerifycode', 'sendFindPasswordVerifycode', 'resetPassword'
        ]);
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = [
                'grant_type' => 'password',
                'client_id' => config('passport.clients.password.client_id'),
                'client_secret' => config('passport.clients.password.client_secret'),
                'username' => $request->get('username'),
                'password' => $request->get('password'),
                'provider' => 'admin_users',
                'scope' => '*',
            ];

            appLog(url('/oauth/token'), $data);

            $res = app(Client::class)->post(url('/oauth/token'), [
                'form_params' => $data,
            ]);

            $res = json_decode($res->getBody(), true);
            appLog('login res=>', $res);
            return response()->json(ok($res));
        } catch (ClientException $e) {
            appLog('login GuzzleHttpException=>', $e);
            return $this->fail('账号或密码错误，请重新输入');
        } catch (\Exception $e) {
            appLog('login Exception=>', $e);
            \abort($e->getCode(), $e->getMessage());
        }
    }
}
