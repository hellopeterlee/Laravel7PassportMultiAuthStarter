<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::any("/demo/test", function () {
    return "test";
});

Route::get("/login", function () {
    return "login";
})->name("login");

Route::get("/qrcode","QrcodeController@index")->name('qrcode');


Route::get("/demo/oauth","DemoController@oauth");
Route::get("/demo/googlekey","DemoController@googlekey");
Route::get("/demo/checkKey","DemoController@checkKey");
