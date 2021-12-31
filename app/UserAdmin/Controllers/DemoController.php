<?php
/**
 * Created by PhpStorm.
 * Date: 2021/12/31 10:38
 */

namespace App\UserAdmin\Controllers;

use App\Http\Controllers\Controller;
use Dcat\Admin\Layout\Content;

class DemoController extends Controller
{
    public function index(Content $content)
    {
        return $content->body("demo");
    }
}
