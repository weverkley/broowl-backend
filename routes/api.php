<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'namespace' => 'API'], function () {

    Route::group(['prefix' => 'auth'], function () {
        Route::post('/signin', 'AuthController@signin');
        Route::post('/signup', 'AuthController@signup');
    });

    Route::group(['prefix' => 'user'], function () {
        // Route::post('', 'UserController@store');
        Route::get('', 'UserController@index');
        Route::get('/{id}', 'UserController@show');
        Route::put('/{id}', 'UserController@update');
        Route::post('/upload/profile', 'UserController@uploadProfile');
        // Route::delete('/{id}', 'UserController@delete');
    });

    Route::group(['prefix' => 'channel'], function () {
        Route::post('', 'ChannelController@store');
        Route::get('', 'ChannelController@index');
        Route::get('/{id}', 'ChannelController@show');
        Route::put('/{id}', 'ChannelController@update');
        Route::delete('/{id}', 'ChannelController@delete');

        Route::get('/{id}/post', 'ChannelController@getPosts');
        Route::post('/upload/cover', 'ChannelController@uploadCover');
    });

    Route::group(['prefix' => 'post'], function () {
        Route::post('', 'PostController@store');
        Route::get('', 'PostController@index');
        Route::get('/{id}', 'PostController@show');
        Route::put('/{id}', 'PostController@update');
        Route::delete('/{id}', 'PostController@delete');
    });
});
