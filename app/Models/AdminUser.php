<?php
/**
 * Created by PhpStorm.
 * Date: 2021/12/29 15:40
 */

namespace App\Models;

use App\Exceptions\AppException;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use League\OAuth2\Server\Exception\OAuthServerException as LeagueException;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;
use Google2FA;

class AdminUser extends Administrator
{
    use HasDateTimeFormatter, Authenticatable, HasPermissions, Notifiable, HasMultiAuthApiTokens;

    protected $fillable = ['username', 'password', 'name', 'avatar', 'google_code'];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $table = 'admin_users';

    public function findForPassport($username)
    {
        $user = $this->orWhere('username', $username)->first();
        if (!$user) {
            throw new LeagueException('User account is not activated', 6, 'account_inactive', 401);
        }
        return $user;
    }

    /**
     * @throws AppException
     */
    public function checkGoogleToken($inputCode): bool
    {
        if (is_null($this['google_code'])) {
            throw new AppException('您未绑定谷歌验证码，不能查看');
        } else {
            $googleCode = $this['google_code'];
            try {
                if (Google2FA::verify($inputCode, $googleCode)) {
                    return true;
                } else {
                    throw new AppException('验证失败，请重新输入');
                }
            } catch (\Exception $e) {
                throw new AppException('验证异常，请重新输入');
            }
        }
    }
}
