<?php

namespace App\Admin\Controllers;

use App\Models\AdminUser;
use App\Models\MchUser;
use App\Admin\LazyRenderable\GoogleTokenForm;
use App\StaffAdmin\Renderable\Pages\EnableGoogleTokenForm;
use Dcat\Admin\Admin;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Card;
use Google2FA;
use Illuminate\Http\Request;

class GoogleTokenController extends AdminController
{
    public function index(Content $content)
    {
        $user = Admin::user();
        $adminUser = AdminUser::query()->find($user['id']);
        if (empty($adminUser['google_code'])) {
            return $this->showEnableTokenForm($content, $adminUser);
        }
        return redirect(admin_url('/'));
    }

    private function showEnableTokenForm(Content $content, $adminUser)
    {
        $key = Google2FA::generateSecretKey(16);
        $google2fa_url = Google2FA::getQRCodeUrl(
            'MtPay',
            $adminUser['username'] . '@mtpay.com',
            $key
        );

        $data = [
            'user' => $adminUser,
            'key' => $key,
            'QRCode' => $google2fa_url
        ];

        return $content
            ->full()
            ->title('谷歌验证码绑定')
            ->body(new Card(new GoogleTokenForm($data)));
    }

    public function checkToken(Request $request)
    {
        $user = Admin::user();
        $adminUser = AdminUser::query()->find($user['id']);

        try {
            if ($adminUser->checkGoogleToken($request->get('code'))) {
                return ok();
            }
        } catch (\Exception $e) {
            return fail($e->getMessage());
        }
    }


    public function updateToken(Request $request)
    {
        if (is_null($request->get('token'))) {
            return $this->disableToken();
        }
        $secretKey = $request->get('secret');
        $token = $request->get('token');
        if (Google2FA::verifyKey($secretKey, $token)) {
            auth()->user()->update(['google_token' => $secretKey]);
            return redirect('/profile/token')->with('success', 'Google Token successfully enabled!');
        }
        return redirect('/profile/token')->withErrors(['error' => 'The provided token does not match.']);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    private function disableToken()
    {
        auth()->user()->update(['google_token' => null]);
        return redirect('/profile/token')->with('success', 'Google Token successfully removed!');
    }
}
