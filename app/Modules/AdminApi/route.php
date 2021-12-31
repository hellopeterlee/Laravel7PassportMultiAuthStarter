<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', '\App\Modules\AdminApi\Controllers\AuthController@login');
Route::post('me', '\App\Modules\AdminApi\Controllers\UserController@me');
