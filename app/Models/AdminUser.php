<?php
/**
 * Created by PhpStorm.
 * Date: 2021/12/29 15:40
 */

namespace App\Models;

use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use League\OAuth2\Server\Exception\OAuthServerException as LeagueException;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;

class AdminUser extends Administrator
{
    use HasDateTimeFormatter, Authenticatable, HasPermissions, Notifiable, HasMultiAuthApiTokens;

    protected $fillable = [
        'name', 'email', 'password'
    ];

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


}
