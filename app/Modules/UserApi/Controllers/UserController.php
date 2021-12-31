<?php

namespace App\Modules\UserApi\Controllers;

use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['auth:user_api']);
    }

    public function me(Request $request)
    {
        $user = $this->user($request);
        return ok($user);
    }
}
