<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\controller_auth;

Route::post('register', [controller_auth::class, 'register']);
Route::post('login', [controller_auth::class, 'login'])->withoutMiddleware('Role');
Route::post('logout', [controller_auth::class, 'logout'])->middleware('auth:sanctum');



Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::group(['middleware' => ['can:isOwner']], function () {
        //fungsi Owner kasi sini semua
    });

    Route::group(['middleware' => ['can:isAdmin']], function () {
        //fungsi Admin kasi sini semua
    });

    Route::group(['middleware' => ['can:isMO']], function () {
        //fungsi MO kasi sini semua
        Route::get('coba', [controller_auth::class, 'coba']);
    });

    //fungsi customer kasi sini semua


});
