<?php
/**
 * Created by PhpStorm.
 * Date: 2021/12/29 13:47
 */

namespace App\Modules\UserApi\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public function user(Request $request)
    {
        return $request->user('user_api');
    }
}
