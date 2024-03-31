<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\controller_auth;
use App\Http\Controllers\controller_promo_poin;

Route::post('register', [controller_auth::class, 'register']);
Route::post('login', [controller_auth::class, 'login'])->withoutMiddleware('Role');
Route::post('logout', [controller_auth::class, 'logout'])->middleware('auth:sanctum');



Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::group(['middleware' => ['can:isOwner']], function () {
        //fungsi Owner kasi sini semua
    });

    Route::group(['middleware' => ['can:isAdmin']], function () {
        //fungsi Admin kasi sini semua
        Route::post('promo_poin', [controller_promo_poin::class, 'createPromoPoin']);
        Route::put('promo_poin/{id}', [controller_promo_poin::class, 'updatePromoPoin']);
        Route::delete('promo_poin/{id}', [controller_promo_poin::class, 'deletePromoPoin']);
        Route::get('promo_poin', [controller_promo_poin::class, 'readPromo']);
        Route::get('promo_poin/{id}', [controller_promo_poin::class, 'getById']);
        Route::get('coba', [controller_auth::class, 'coba']);
    });

    Route::group(['middleware' => ['can:isMO']], function () {
        //fungsi MO kasi sini semua

    });

    //fungsi customer kasi sini semua

});
