<?php

namespace App\Admin\Controllers;

use Dcat\Admin\Admin;
use Dcat\Admin\Http\Controllers\AuthController as BaseAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseAuthController
{
    protected $view = 'admin.login';
    protected $redirectTo = '/';


    public function postLogin(Request $request)
    {
        $credentials = $request->only([$this->username(), 'password']);
        $remember = (bool)$request->input('remember', false);

        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($credentials, [
            $this->username() => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorsResponse($validator);
        }

        if ($this->guard()->attempt($credentials, $remember)) {
            $guard = $this->guard();
            $adminUser = $guard->getProvider()->retrieveByCredentials($credentials);

//            dump($adminUser);

            if (!$this->checkGoogeCode($adminUser)) {
                return $this->validationErrorsResponse([
                    'google_token' => '谷歌验证码错误',
                ]);
            }

            return $this->sendLoginResponse($request);
        }

        return $this->validationErrorsResponse([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }

    private function checkGoogeCode($user)
    {
        try {

            if (empty($user['google_code'])) {
                return true;
            }

            if (method_exists($user, 'checkGoogleToken')) {
                return call_user_func([$user, 'checkGoogleToken'], request('google_token'));
            }

            return false;

        } catch (\Exception $e) {
            dump("Exception :", $e->getMessage());
            return false;
        }
    }

    protected function getRedirectPath(): string
    {
        return $this->redirectTo ?: admin_url('/');
    }
}
