# 集成一下组件:

* dcat/laravel-admin (自定义登录页面)
* "smartins/passport-multiauth": "^7.0"(自带laravel/passport)
* fruitcake/laravel-cors
* pragmarx/google2fa-laravel (登录页面加入谷歌验证码验证逻辑)

系统自带了*lcobucci/jwt4.5*版本,安装*smartins/passport-multiauth:^7.0*后会有冲突,需要强行在composer.json开中加入

~~~
"require": {
    ...
    "smartins/passport-multiauth": "^7.0",
    "lcobucci/jwt": "3.3.3"
},
~~~

> 这里指定使用"lcobucci/jwt": "3.3.3"版本 然后执行composer update，强行使用3.3.3版本

安装完passport-multiauth后,集成步骤:
1.执行以下命令，初始化passport

~~~
php artisan optimize:clear
php artisan migrate
php artisan passport:install
php artisan passport:keys --force
php artisan passport:client --password
~~~

> 上面的命令会生产passport相关的数据库表，并在oauth_access_tokens表中添加password类型的记录

我们假设需要在user表格admin_user两个表中使用passport的password模式:
在config/auth.php中，加入:

~~~
'guards' => [
        ...
        'user' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],

        'admin_user' => [
            'driver' => 'passport',
            'provider' => 'admin_users',
        ],
    ],
...
'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        'admin_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\AdminUser::class,
        ],
    ],    
~~~

composer.json中排除laravel/passport

~~~
"extra": {
        "laravel": {
            "dont-discover": [
                "laravel/passport",
            ]
        }
    },
~~~

config/app.php,手动添加PassportServiceProvider,注意将MultiauthServiceProviderServiceProvider放在PassportServiceProvider之前

~~~
'providers' => [
    ...
    SMartins\PassportMultiauth\Providers\MultiauthServiceProvider::class,
    Laravel\Passport\PassportServiceProvider::class,
]
~~~

User.php

~~~
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

    ......

    /**
     * 默认是使用email字段查找用户，这里覆盖findForPassport,使用name字段在查找用户
     * @throws LeagueException
     */
    public function findForPassport($username)
    {
        $user = $this->where('name', $username)->first();
        if (!$user) {
            throw new LeagueException('User account is not activated', 6, 'account_inactive', 401);
        }
        return $user;
    }
}
~~~

AdminUser.php

~~~
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

    ...

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
~~~

### 获取token(注意provider参数用于区分哪个表)

~~~
POST: http://demo.me/oauth/token
参数:
username:admin
password:admin
grant_type:password
client_id:4
client_secret:9WsYwcIi6fs3OprQatX5m2Owgsx7BLmwSXnj6lk4
provider:admin_users

得到响应:
{
    "token_type": "Bearer",
    "expires_in": 31536000,
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI0IiwianRpIjoiMjFjZTRiYzJlMTE4MWJiNzk3ZDQ1Y2I2MDVkZTk5MTFlMGRkODgwYjgyODRkOGQwN2FlNTk1ZmUzMmNhZWQ3YjMyYjJmMDJiNDYwZDMyZmYiLCJpYXQiOjE2NDA4MjM5MDcsIm5iZiI6MTY0MDgyMzkwNywiZXhwIjoxNjcyMzU5OTA3LCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.pAcm-dyQQCStz4rySoWzStLA_josll_BOlDs7WaD6RgPS6sEydxQ2eHv_IuT_DSW6IDlExdRyNs5aV310qV5oFWx79RUqYDUG4mum2nzTj2VdtjNCT5OT18zKJDUEygewosMzTWks0NNf2PqnP0vQuFUTi9KhxGKqjlooaTK6rBx23XCyic3MMkoOltRdOO-MFiOp7vDp5VLE4uOXIRqzI6aHde_n-6gRCWEMbVR0J723LKoKkq4sc9XjqDMyRnFyHjntglD1L6EdG35dH3ArZPDd0h1N6lCOun1MVr7bAkaYZnjP2b9cahA6JEoJ6-cP2IUQy0rShtEX7rukT79gY13P4jqZ012TT9sfrhwNb6vkzRyWhKmingosAQhMcvPoWpEkUjx4XGEh7scNgYMiuypzVMNM22L-RwLCOy9WCUWLKHzFL96721Z5FtnotLOIu4p1ibVhQhTUnv3vfl5FJnaWT46IsoECAt3PmahAVAHNGyNPoKNxKn1b0YkIc0QZKHmWK-Ld4hyTqLb9CEAo9Dp5WKtP4VrWsnqyOcOKwPcOFMFLMCRjXzTi3naonBLpHfvf6p0VFLNhcpg_N_MLjVHmOplaQpAMEjFCo7XkR3g_TEKWxdSSaq-pz-hHw_PHL8gkx4JU8hOx-1oCClh4DPzQvwTnmmFU_nAUXAfjJg",
    "refresh_token": "def502000717796a21ca576bee432dbfc2b44b0b64e6f58aaccd71a70e81154c2533295f101f5a22ec92c5b2cd9dae6b71173f2a4f3512cdc6445aac733d81cfd9b21f23bf675468d5fd1e3aa1178ee1a63e6c63ab3969dbcbcdf2215fad36aa888458d303f39e824a37ea5b473638a46f1d7ff2b9f7b966b676ef9d6728e208e596ae283328a9d005a2cb37c3b0cdbf0b91ea7b3efd02497754d36e0432fcb454953b7578a43dc46d3e7a0a35e4bdd16adb81301e73b86da084d165a55e4aad55239178734aee91399ea3f94e1e232dace1d42304f8c63fc053715c63d922fa80bc3437779e9226b4a6523eefadcb43df1a9f25907cd8f70ed61ee0c1df4e22b2241343548aaf62b0afbe6eee3c6af9f9ed45b650b6a7691af5e8266267c81bc676db1c1ad4d7b667fcf5dd62225e19a7c3102d83a6fb1ea43d06936cdd19737ffd98d4c0c78629b90baa8ca43854502b348c013f2aca1ffac46c5e3ee4a8077f"
}
~~~

AuthController.php

~~~
<?php

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
~~~

route.php

~~~
Route::post('/login', '\App\Modules\AdminApi\Controllers\AuthController@login');
Route::post('me', '\App\Modules\AdminApi\Controllers\UserController@me');
~~~

登陆并获取token

~~~
POST http://demo.me/api/adminuser/login
参数:
username:admin
password:admin
得到响应:
{
    "code": 0,
    "msg": "操作成功",
    "data": {
        "token_type": "Bearer",
        "expires_in": 31536000,
        "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI4IiwianRpIjoiYmFhZDM5YmZjMGU3Y2VhMTI0Nzc2YzY1ZmIwMDM5YTM1OWU4ZGQ4YWMxZThhNTVjY2Q4MGY0ZWY3MmViODNjNjY0ODdjNTI0ZGQzZWFlMTUiLCJpYXQiOjE2NDA4Mzc3NjMsIm5iZiI6MTY0MDgzNzc2MywiZXhwIjoxNjcyMzczNzYzLCJzdWIiOiIxIiwic2NvcGVzIjpbIioiXX0.C5uEnKwgW-sFGfzj4AeVGB84RfFCU92Pt6rMpqHT7HCyil2oUUytxOmHEQY9vZ_FEwCIKQN6LHcXlaK-DBHL9zmwFLNm3gLWsFiRc_rGzUR4FTr0A7j7G0xGrxifu8HGFZoNXc6JAIuuiM1KXkrRzmwwvG51LoeJdD35r-NNNrLfS3VO5-Zv2WMuZV1cwUPwsZAdHCJFdjC_PsL34A6lx8Zp7BDkboty2LLMTF5PNlTRvN7_qDQMSk4-L-VXqXQfMyCOLZTDbQ9BZ_szpodAjrQ8yZ_LwdTjQ9ZaECQ05w9NJwPwf5OFV-53sQAyX2AzL0DKmV-OhouYm6gvtM5QGMMXuY-0sAfAbcBapZFmShFiSpm_3rcLLVi1-AnBP9WrR4LQ9fWvhLrhrpTcXrxXzUyIjLqzqyhgJFbxYuChKeaw84ybhq_phrEMKJdh8GOcczmiw6wJU0ZajQqQGO--OsbRbj8bG8CycsoFlacO39bAD4R792-VfAlS46FYAVX9klcJdEyPFwjQfckyPCzwa05-Y93jYQWE91tu1zDBIuMwuDzFAD5VamCkAiETuV81HqJRiDQLVvMziUcgVb3p4edPwZjfdc-AdpO-hH0HsEHRYwqNkUAA6-buIkLrFiSb9ssnqrshIlfq1P3L7GvJwxX0Rmrts5OEj_6z9H05WYc",
        "refresh_token": "def50200836b04903491bfee93262bfa10666b9224c917ed6cf062a16aef8ab01179d7bb78de6e654e4451d9dc0db51c3137216c74012b780152ad3f011804bdd2c9a3311657b0beca2b51f10a571b53cf9e02a95f02ef91de5e17f0fce2e599fbdbf3d1f0720c483b7cc5e09e047c6a79d1e8c949124a96242f8ca4414ef0a53dd20daa8b83a0e4f3966f4ce77f5c14c5471157ca192fcaad61e40d3f3878b27067b9c764b8bb030b12c31487c6721ecf24415bf6bd75e5e93b8603cfd544be2c1664cffae8b8d9cf699ae70b034e334302b3c652503968dc8f4959c8a6891379563a4f5a2d58662af055b82aec7b47432e4101aeef195d1648125fcad8e364980b69811102e170dc722a2eed45f2b299b3536f742719c65a55c786a43501d897bea77bb9c2f3b6c04cb5005ad0f09641ca738f0ff8155e89250f3590fcb00307344867aeb566ea68910e1c56528c4964e80803b0a2bd5812326c7fd11f9f47870d3fd0"
    }
}
~~~

~~~
<?php
namespace App\Modules\AdminApi\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function user(Request $request)
    {
        return $request->user('admin_user_api');
    }
}


<?php
namespace App\Modules\AdminApi\Controllers;

use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['auth:admin_user_api']);
    }

    public function me(Request $request)
    {
        $adminUser = $this->user($request);
        return ok($adminUser);
    }
}
~~~

调用admin_user_api

~~~
POST http://demo.me/api/adminuser/me
Header =>
Authorization: "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI4IiwianRpIjoiYmFhZDM5YmZjMGU3Y2VhMTI0Nzc2YzY1ZmIwMDM5YTM1OWU4ZGQ4YWMxZThhNTVjY2Q4MGY0ZWY3MmViODNjNjY0ODdjNTI0ZGQzZWFlMTUiLCJpYXQiOjE2NDA4Mzc3NjMsIm5iZiI6MTY0MDgzNzc2MywiZXhwIjoxNjcyMzczNzYzLCJzdWIiOiIxIiwic2NvcGVzIjpbIioiXX0.C5uEnKwgW-sFGfzj4AeVGB84RfFCU92Pt6rMpqHT7HCyil2oUUytxOmHEQY9vZ_FEwCIKQN6LHcXlaK-DBHL9zmwFLNm3gLWsFiRc_rGzUR4FTr0A7j7G0xGrxifu8HGFZoNXc6JAIuuiM1KXkrRzmwwvG51LoeJdD35r-NNNrLfS3VO5-Zv2WMuZV1cwUPwsZAdHCJFdjC_PsL34A6lx8Zp7BDkboty2LLMTF5PNlTRvN7_qDQMSk4-L-VXqXQfMyCOLZTDbQ9BZ_szpodAjrQ8yZ_LwdTjQ9ZaECQ05w9NJwPwf5OFV-53sQAyX2AzL0DKmV-OhouYm6gvtM5QGMMXuY-0sAfAbcBapZFmShFiSpm_3rcLLVi1-AnBP9WrR4LQ9fWvhLrhrpTcXrxXzUyIjLqzqyhgJFbxYuChKeaw84ybhq_phrEMKJdh8GOcczmiw6wJU0ZajQqQGO--OsbRbj8bG8CycsoFlacO39bAD4R792-VfAlS46FYAVX9klcJdEyPFwjQfckyPCzwa05-Y93jYQWE91tu1zDBIuMwuDzFAD5VamCkAiETuV81HqJRiDQLVvMziUcgVb3p4edPwZjfdc-AdpO-hH0HsEHRYwqNkUAA6-buIkLrFiSb9ssnqrshIlfq1P3L7GvJwxX0Rmrts5OEj_6z9H05WYc"

返回:
{
    "code": 0,
    "msg": "操作成功",
    "data": {
        "id": 1,
        "username": "admin",
        "name": "Administrator",
        "created_at": "2021-12-29 06:05:13",
        "updated_at": "2021-12-29 06:05:14"
    }
}
~~~

~~~
同理:
UserController::__construct设置的中间件auth:admin_user_api和$request->user('admin_user_api')
换成 => 
public function __construct()
{
    $this->middleware(['auth:user_api']);
}

public function user(Request $request)
{
    return $request->user('user_api');
}
即可调用user_api相关api了
~~~

---
##dact-admin多应用 (多后台)
~~~
php artisan admin:app UserAdmin

#在 config/admin.php 中添加
return [
    ...

    'multi_app' => [
        // 与新应用的配置文件名称一致
        // 设置为true启用，false则是停用
        'user-admin' => true,
    ],

];
~~~

添加新的菜单表:
~~~
CREATE TABLE `user_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL DEFAULT '0',
  `order` int(11) NOT NULL DEFAULT '0',
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uri` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
~~~

创建新的菜单模型
~~~
<?php
namespace App\Models;

use Dcat\Admin\Models\Menu;

class UserMenu extends Menu
{
    protected $table = 'user_menu';
}
~~~

编辑 config/user-admin.php
~~~
return [
    ...

    'database' => [

      ...

      // 写入新的模型和菜单表
      'menu_table' => 'user_menu',
      'menu_model' => App\Models\UserMenu::class,
      ...
      
    ],
];
~~~
> 这样新的应用就可以使用独立的菜单功能了

更改用户和权限,这里关闭所有权限相关的配置,config/user-admin.php
~~~
   ...

   'auth' => [
        'enable' => false, #关闭权限认证

        'controller' => App\UserAdmin\Controllers\AuthController::class,

        'guard' => 'user-admin',

        'guards' => [
            'user-admin' => [
                'driver'   => 'session',
                'provider' => 'user-admin',
            ],
        ],

        'providers' => [
            'user-admin' => [
                'driver' => 'eloquent',
                'model'  => \App\Models\User::class
            ],
        ],

        ...
    ],
    
    ...
    
    'permission' => [
        // Whether enable permission.
        'enable' => false, #关闭权限认证
    ],
~~~

添加用户和菜单项
~~~
INSERT INTO `demo_me`.`users`(`id`, `username`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES (1, 'test', 'billy.flatley@example.com', '2021-12-29 06:41:20', '$2y$10$fkranF.Nq9FO4UJo370CVOSUXOfocYe28X8hACPygGzoMy9XfZwaa', 'dYhQMHGOJb5gI1Pay1mucAWX4dmDjr1FGuRJHdOPxjjBgzbIYHaKdW5BccHr', '2021-12-29 06:41:22', '2021-12-29 06:41:22');

INSERT INTO `demo_me`.`user_menu`(`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `created_at`, `updated_at`) VALUES (1, 0, 1, '首页', 'feather icon-bar-chart-2', '/', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
INSERT INTO `demo_me`.`user_menu`(`id`, `parent_id`, `order`, `title`, `icon`, `uri`, `created_at`, `updated_at`) VALUES (2, 0, 2, 'demo', 'feather icon-bar-chart-2', '/demo', '0000-00-00 00:00:00', '0000-00-00 00:00:00');
~~~

访问 http://demo.me/user-admin/
test/111111


