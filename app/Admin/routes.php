<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');

    $router->group(['prefix' => 'googleAuth'], function ($router) {
        $router->get('', 'GoogleTokenController@index')->name('admin.googleauth');
        $router->post('checkToken', 'GoogleTokenController@checkToken')->name('admin.googleauth.check');
    });
});
