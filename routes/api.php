<?php

use App\Http\Controllers\controller_customer;
use App\Http\Controllers\controller_pesanan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\controller_auth;
use App\Http\Controllers\controller_promo_poin;
use App\Http\Controllers\controller_produk;
use App\Http\Controllers\controller_hampers;
use App\Http\Controllers\controller_detail_hampers;
use App\Http\Controllers\controller_kategori;
use App\Http\Controllers\controller_pengadaan_bahan_baku;
use App\Http\Controllers\controller_penitip;
use App\Http\Controllers\controller_bahan_baku;
use App\Http\Controllers\controller_pengeluaran;
use App\Http\Controllers\controller_presensi;
use App\Http\Controllers\controller_transaksi_pesanan;

Route::post('register', [controller_auth::class, 'register']);
Route::post('login', [controller_auth::class, 'login'])->withoutMiddleware('Role');
Route::post('logout', [controller_auth::class, 'logout'])->middleware('auth:sanctum');

Route::post('register_karyawan', [controller_auth::class, 'register_karyawan']);

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::group(['middleware' => ['can:isOwner']], function () {
    });

    Route::group(['middleware' => ['can:isAdmin']], function () {

        Route::post('promo_poin', [controller_promo_poin::class, 'createPromoPoin']);
        Route::put('promo_poin/{id}', [controller_promo_poin::class, 'updatePromoPoin']);
        Route::delete('promo_poin/{id}', [controller_promo_poin::class, 'deletePromoPoin']);
        Route::get('promo_poin', [controller_promo_poin::class, 'readPromo']);
        Route::get('promo_poin/{id}', [controller_promo_poin::class, 'getById']);

        Route::post('produk', [controller_produk::class, 'createProduk']);
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

        Route::post('bahan_baku', [controller_bahan_baku::class, 'createBahanBaku']);
        Route::delete('bahan_baku/{id_bahan_baku}', [controller_bahan_baku::class, 'deleteBahanBaku']);
        Route::put('bahan_baku/{id_bahan_baku}', [controller_bahan_baku::class, 'updateBahanBaku']);
        Route::get('bahan_baku', [controller_bahan_baku::class, 'readBahanBaku']);
        Route::get('bahan_baku/{id_bahan_baku}', [controller_bahan_baku::class, 'getById']);
        Route::get('bahan_baku_nama/{nama}', [controller_bahan_baku::class, 'getByNama']);


        Route::post('penitip', [controller_penitip::class, 'createPenitip']);
        Route::delete('penitip/{id_penitip}', [controller_penitip::class, 'deletePenitip']);
        Route::put('penitip/{id_penitip}', [controller_penitip::class, 'updatePenitip']);
        Route::get('penitip', [controller_penitip::class, 'readPenitip']);
        Route::get('penitip/{id_penitip}', [controller_penitip::class, 'getById']);
        Route::get('penitip_nama/{nama}', [controller_penitip::class, 'getByNama']);

        Route::post('pengeluaran', [controller_pengeluaran::class, 'createPengeluaran']);
        Route::delete('pengeluaran/{id_pengeluaran}', [controller_pengeluaran::class, 'deletePengeluaran']);
        Route::put('pengeluaran/{id_pengeluaran}', [controller_pengeluaran::class, 'updatePengeluaran']);
        Route::get('pengeluaran', [controller_pengeluaran::class, 'readPengeluaran']);
        Route::get('pengeluaran/{id_pengeluaran}', [controller_pengeluaran::class, 'getById']);
        Route::get('pengeluaran_nama/{nama}', [controller_pengeluaran::class, 'getByNama']);

        Route::get('customer', [controller_customer::class, 'readCustomer']);
        Route::get('customer/{id_pengeluaran}', [controller_customer::class, 'getById']);
        Route::get('customer_nama/{nama}', [controller_customer::class, 'getByNama']);

        Route::get('pesanan', [controller_pesanan::class, 'readPesanan']);
    });

    Route::group(['middleware' => ['can:isMO']], function () {

        Route::post('pengadaan_bahan_baku', [controller_pengadaan_bahan_baku::class, 'createPengadaanBahanBaku']);
        Route::put('pengadaan_bahan_baku/{id}', [controller_pengadaan_bahan_baku::class, 'updatePengadaanBahanBaku']);
        Route::delete('pengadaan_bahan_baku/{id}', [controller_pengadaan_bahan_baku::class, 'deletePengadaanBahanBaku']);
        Route::get('pengadaan_bahan_baku', [controller_pengadaan_bahan_baku::class, 'readPengadaanBahanBaku']);
        Route::get('pengadaan_bahan_baku/{id}', [controller_pengadaan_bahan_baku::class, 'readPengadaanBahanBakuByID']);


        Route::get('presensi', [controller_presensi::class, 'ReadAllPresensi']);
        Route::get('presensi/{date}', [controller_presensi::class, 'ReadByDate']);
        Route::put('presensi/{id}', [controller_presensi::class, 'ChangeStatusToTidakHadir']);
    });

    Route::group(['middleware' => ['can:isMOorAdmin']], function () {
        Route::get('bahan_baku', [controller_bahan_baku::class, 'readBahanBaku']);
    });


    Route::get('penitip', [controller_penitip::class, 'ReadPenitip']);
    Route::get('kategori', [controller_kategori::class, 'ReadKategori']);

    //fungsi customer kasi sini semua

});

// transaksi no 72
Route::get('produkNonPenitipWithKuota/{date}', [controller_produk::class, 'getProdukNonPenitipWithKuota']);
Route::get('produkWithPenitip', [controller_produk::class, 'getProdukWithPenitip']);

Route::get('getProdukByIdWithQuota/{Id}/{date}', [controller_transaksi_pesanan::class, 'getProdukByIdWithQuota']);
