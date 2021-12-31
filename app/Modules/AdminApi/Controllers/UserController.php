<?php
/**
 * Created by PhpStorm.
 * Date: 2021/12/29 13:56
 */

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
