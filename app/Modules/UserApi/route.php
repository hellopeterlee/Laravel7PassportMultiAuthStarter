<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', '\App\Modules\UserApi\Controllers\AuthController@login');
Route::post('me', '\App\Modules\UserApi\Controllers\UserController@me');
