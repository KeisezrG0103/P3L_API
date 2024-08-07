<?php

use App\Http\Controllers\controller_alamat;
use App\Http\Controllers\controller_customer;
use App\Http\Controllers\controller_pesanan;
use App\Http\Controllers\controller_forgot_password;
use App\Http\Controllers\controller_pesanan_selesai;
use App\Http\Controllers\controller_reset_password;
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
use App\Http\Controllers\controller_resep;
use App\Http\Controllers\controller_detail_pemesanan;
use App\Http\Controllers\controller_history_bahan_baku;
use App\Http\Controllers\controller_konfirmasi_pembelian;
use App\Http\Controllers\controller_konfirm_saldo;
use App\Http\Controllers\controller_laporan;

Route::post('register', [controller_auth::class, 'register']);
Route::post('login', [controller_auth::class, 'login'])->withoutMiddleware('Role');
Route::post('logout', [controller_auth::class, 'logout'])->middleware('auth:sanctum');
Route::post('register_customer', [controller_customer::class, 'registerCustomer']);

Route::post('/forgot-password', [controller_forgot_password::class, 'forgotPassword']);
Route::post('/verify/pin', [controller_forgot_password::class, 'verifyPin']);
Route::post('/reset-password', [controller_reset_password::class, 'resetPassword']);

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

        Route::get('customer', [controller_customer::class, 'readCustomer']);

        Route::get('customer_nama/{nama}', [controller_customer::class, 'getByNama']);

        Route::get('history/{email}', [controller_pesanan::class, 'getHistoryByEmail']);
        Route::get('history', [controller_pesanan::class, 'getAllHistoryPesanan']);

        Route::get('resep', [controller_resep::class, 'getResep']);


        Route::get('getSaldoToConfirm', [controller_konfirm_saldo::class, 'getSaldoToConfirm']);
        Route::put('confirmRequestSaldo/{id}', [controller_konfirm_saldo::class, 'confirmRequestSaldo']);
        Route::put('rejectRequestSaldo/{id}', [controller_konfirm_saldo::class, 'rejectRequestSaldo']);
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

        Route::get('getDaftarPesananYangDiprosesHariIni/{tanggalBesok}', [controller_pesanan::class, 'getDaftarPesananYangDiprosesHariIni']);
        Route::post('changeStatusToDiproses/{noNota}', [controller_resep::class, 'changeStatusToProses']);

        route::get('getHistoryBahanBaku', [controller_history_bahan_baku::class, 'getHistoryBahanBaku']);

        route::get('getListPesananHarianDanYangDibeli/{tanggalBesok}', [controller_pesanan::class, 'getListPesananHarianDanYangDibeli']);
        Route::get('getDetailResepDanNamaResepUntukPesananBesok/{tanggal_besok}', [controller_resep::class, 'getDetailResepDanNamaResepUntukPesananBesok']);
        Route::get('getRekapPesananHarian/{tanggal}', [controller_pesanan::class, 'getRekapPesananHarian']);
        Route::get('rekapBahanBakuPesananHarian/{tanggal}', [controller_resep::class, 'rekapBahanBakuPesananHarian']);

        Route::get('GetKekuranganBahanBaku/{noNota}', [controller_resep::class, 'GetKekuranganBahanBaku']);

        Route::get('getYangPerluDibuat/{tanggal}', [controller_resep::class, 'getYangPerluDibuat']);
    });

    Route::group(['middleware' => ['can:isMOorAdmin']], function () {
        Route::get('bahan_baku', [controller_bahan_baku::class, 'readBahanBaku']);
    });

    Route::group(['middleware' => ['can:isMOorOwner']], function () {
        Route::get('laporan_bahan_baku', [controller_laporan::class, 'getAllBahanBaku']);
        Route::get('laporan_produk_per_bulan/{bulan}/{year}', [controller_laporan::class, 'laporanProdukPerBulan']);
        Route::get('laporan_produk_per_bulanV2/{bulan}/{year}', [controller_laporan::class, 'LaporanPenjualanPerProdukV2']);


        Route::get('LaporanPenggunaanBahanBakuByPeriode/{start}/{end}', [controller_laporan::class, 'LaporanPenggunaanBahanBakuByPeriode']);
        Route::get('LaporanPenjualanBulananSecaraKeseluruhan', [controller_laporan::class, 'LaporanPenjualanBulananSecaraKeseluruhan']);


        route::get('getHistoryBahanBaku', [controller_history_bahan_baku::class, 'getHistoryBahanBaku']);
        route::get('getLaporanPresensi/{date}', [controller_laporan::class, 'getLaporanPresensi']);
        route::get('getLaporanKeuangan/{date}', [controller_laporan::class, 'getLaporanKeuangan']);
        route::get('getLaporanPenitip/{date}', [controller_laporan::class, 'getLaporanPenitip']);
    });

    Route::get('Tanggal_Lahir_Customer/{Email}', [controller_customer::class, 'getTanggalLahirPerCustomer']);

    Route::get('customer/{Email}', [controller_customer::class, 'getById']);
    Route::get('penitip', [controller_penitip::class, 'ReadPenitip']);
    Route::get('poin/{email}', [controller_promo_poin::class, 'getPointPerCustomer']);
    Route::get('latestNota/{month}', [controller_pesanan::class, 'getLatestPesanan']);
    Route::get('generateNoNota/{month}', [controller_pesanan::class, 'generateNoNota']);
    Route::post('pesanProduk', [controller_pesanan::class, 'PesanProduk']);
    Route::post('AddDetailPemesanan', [controller_detail_pemesanan::class, 'addDetailPemesananProduk']);

    Route::get('getNotaById/{NoNota}', [controller_pesanan::class, 'getNotaById']);

    route::get('PesananSelesaiWithDetailPesananAndTanggal/{Email}', [controller_pesanan_selesai::class, 'getPesananSelesaiWithDetailPesananAndTanggal']);
    route::get('getPesananAndProdukOnGoing/{Email}', [controller_pesanan::class, 'getPesananAndProdukOnGoing']);
    route::get('getPesananAndProdukDitolak/{Email}', [controller_pesanan::class, 'getPesananAndProdukDitolak']);
    route::post('sendBuktiBayar/{id}', [controller_pesanan::class, 'sendBuktiPembayaran']);
    Route::get('daftarPesananToConfirm', [controller_konfirmasi_pembelian::class, 'getDaftarPesananToConfirm']);
    route::put('konfirmasiPesanan/{id}', [controller_konfirmasi_pembelian::class, 'konfirmasiPesanan']);
    route::put('tolakPesanan/{id}', [controller_konfirmasi_pembelian::class, 'tolakPesanan']);
    route::get('getAlamat/{email}', [controller_alamat::class, 'getAlamat']);
    route::post('addAlamat/{email}', [controller_alamat::class, 'addAlamat']);


    Route::get('getCustomerByEmail/{email}', [controller_customer::class, 'getCustomerByEmail']);
    Route::post('requestSaldo/{email}', [controller_customer::class, 'requestSaldo']);
    Route::get('getHistoryPenarikanSaldo/{email}', [controller_customer::class, 'getHistoryPenarikanSaldo']);
});

// transaksi no 72
Route::get('produkNonPenitipWithKuota/{date}', [controller_produk::class, 'getProdukNonPenitipWithKuota']);
Route::get('produkKuota/{id}/{date}', [controller_produk::class, 'getProdukKuota']);
Route::get('ProdukPenitip', [controller_produk::class, 'getProdukPenitip']);
Route::get('getProdukByIdWithQuota/{Id}/{date}', [controller_transaksi_pesanan::class, 'getProdukByIdWithQuota']);
Route::get('kategori', [controller_kategori::class, 'ReadKategori']);
Route::post('generate_resep', [controller_resep::class, 'generateResepAllProduk']);
Route::get('getHampersWithProdukAndKuota/{date}', [controller_hampers::class, 'getHamperandProdukwithKuota']);
Route::get('getProdukInHampersWithKuota/{id}/{date}', [controller_produk::class, 'getHampersProdukAndKuota']);
Route::get('getHampersByIdWithKuota/{id}/{date}', [controller_hampers::class, 'getHampersByIdWithKuota']);
Route::get('getKuotaHampersById/{id}/{date}', [controller_hampers::class, 'getKuotaHampersById']);
Route::post('AutomaticPresensi', [controller_presensi::class, 'AutomaticPresensi']);
Route::get('getProdukByRequestandKuota/{kategori}', [controller_produk::class, 'getProdukByRequestandKuota']);


//debug only
// Route::get('getResepFromDetailPesanan/{noNota}', [controller_resep::class, 'getResepFromDetailPesanan']);
// Route::get('prosesPesanan/{NoNota}', [controller_pesanan::class, 'prosesPesanan']);
// Route::get('getDetailResepByPesanan/{nota}', [controller_resep::class, 'getDetailResepByPesanan']);
// Route::get('GetKekuranganBahanBaku/{noNota}', [controller_resep::class, 'GetKekuranganBahanBaku']);

// Route::get('getCustomerByEmail/{email}', [controller_customer::class, 'getCustomerByEmail']);
// Route::post('requestSaldo/{email}', [controller_customer::class, 'requestSaldo']);
// Route::get('getHistoryPenarikanSaldo/{email}', [controller_customer::class, 'getHistoryPenarikanSaldo']);

// Route::get('getYangPerluDibuat/{tanggal}', [controller_resep::class, 'getYangPerluDibuat']);



// Route::get('compareStokBahanBakuDanKebutuhan/{noNota}', [controller_resep::class, 'compareStokBahanBakuDanKebutuhan']);

// Route::get('getSaldoToConfirm', [controller_konfirm_saldo::class, 'getSaldoToConfirm']);
// Route::put('confirmRequestSaldo/{id}', [controller_konfirm_saldo::class, 'confirmRequestSaldo']);
// Route::put('rejectRequestSaldo/{id}', [controller_konfirm_saldo::class, 'rejectRequestSaldo']);


// route::get('getHistoryBahanBaku', [controller_history_bahan_baku::class, 'getHistoryBahanBaku']);
// route::get('getLaporanPresensi/{date}', [controller_laporan::class, 'getLaporanPresensi']);
// route::get('getLaporanKeuangan/{date}', [controller_laporan::class, 'getLaporanKeuangan']);
// route::get('getLaporanPenitip/{date}', [controller_laporan::class, 'getLaporanPenitip']);


// Route::get('laporan_bahan_baku', [controller_laporan::class, 'getAllBahanBaku']);

// Route::get('laporan_produk_per_bulanV2/{bulan}/{year}', [controller_laporan::class, 'LaporanPenjualanPerProdukV2']);
