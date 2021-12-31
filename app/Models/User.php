<?php
/**
 * Created by PhpStorm.
 * Date: 2021/12/29 14:08
 */

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;
use League\OAuth2\Server\Exception\OAuthServerException as LeagueException;

class User extends Authenticatable
{
    use Notifiable, HasMultiAuthApiTokens;

    protected $fillable = [
        'name', 'email', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAvatar()
    {
        return "/vendor/dcat-admin/images/default-avatar.jpg";
    }

    /**
     * 默认是使用email字段查找用户，这里覆盖findForPassport,使用name字段在查找用户
     * @throws LeagueException
     */
    public function findForPassport($username)
    {
        $user = $this->where('username', $username)->first();
        if (!$user) {
            throw new LeagueException('User account is not activated', 6, 'account_inactive', 401);
        }
        return $user;
    }

}
