<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\controller_auth;
use App\Http\Controllers\controller_promo_poin;
use App\Http\Controllers\controller_produk;
use App\Http\Controllers\controller_hampers;
use App\Http\Controllers\controller_detail_hampers;
use App\Http\Controllers\controller_pengadaan_bahan_baku;

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

        Route::post('produk' , [controller_produk::class, 'createProduk']);
        Route::put('produk/{id}', [controller_produk::class, 'updateProduk']);
        Route::delete('produk/{id}', [controller_produk::class, 'deleteProduk']);
        Route::get('produk', [controller_produk::class, 'readProduk']);
        Route::get('produk/{id}', [controller_produk::class, 'getById']);
        Route::get('produkNama/{nama}', [controller_produk::class, 'getByNama']);

        Route::post('hampers', [controller_hampers::class, 'createHampers']);
        Route::put('hampers/{id}', [controller_hampers::class, 'updateHampers']);
        Route::delete('hampers/{id}', [controller_hampers::class, 'deleteHampers']);
        Route::get('hampers', [controller_hampers::class, 'readHampers']);
        Route::get('hampers/{id}', [controller_hampers::class, 'getById']);

        Route::post('detail_hampers', [controller_detail_hampers::class, 'addProdukToHampers']);
        Route::delete('detail_hampers/{id_hampers}/{id_produk}', [controller_detail_hampers::class, 'deleteProdukFromHampers']);
        Route::put('detail_hampers/{id_hampers}/{id_produk}', [controller_detail_hampers::class, 'updateProdukFromHampers']);
        Route::get('detail_hampers/{id_hampers}', [controller_detail_hampers::class, 'getProdukFromHampers']);


    });

    Route::group(['middleware' => ['can:isMO']], function () {
        //fungsi MO kasi sini semua

        Route::post('pengadaan_bahan_baku', [controller_pengadaan_bahan_baku::class, 'createPengadaanBahanBaku']);
        Route::put('pengadaan_bahan_baku/{id}', [controller_pengadaan_bahan_baku::class, 'updatePengadaanBahanBaku']);
        Route::delete('pengadaan_bahan_baku/{id}', [controller_pengadaan_bahan_baku::class, 'deletePengadaanBahanBaku']);
        Route::get('pengadaan_bahan_baku', [controller_pengadaan_bahan_baku::class, 'readPengadaanBahanBaku']);
        Route::get('pengadaan_bahan_baku/{id}', [controller_pengadaan_bahan_baku::class, 'readPengadaanBahanBakuByID']);



    });

    //fungsi customer kasi sini semua

});
