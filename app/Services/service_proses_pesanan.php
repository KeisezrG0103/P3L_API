<?php

namespace App\Services;

use App\Models\model_bahan_baku;
use App\Models\model_detail_hampers;
use App\Models\model_detail_resep;
use Carbon\Carbon;
use App\Models\model_pesanan;
use Illuminate\Support\Facades\DB;
use App\Models\model_detail_transaksi;
use App\Models\model_hampers;
use App\Models\model_resep;

class service_proses_pesanan
{


    private service_history_bahan_baku $service_history_bahan_baku;

    public function __construct(service_history_bahan_baku $service_history_bahan_baku)
    {
        $this->service_history_bahan_baku = $service_history_bahan_baku;
    }


    public function getDaftarPesananYangDiprosesHariIni($tanggalBesok)
    {
        $pesanan = model_pesanan::select(
            'pesanan.Id',
            'pesanan.Tanggal_Pesan',
            'pesanan.Status',
            'pesanan.Tanggal_Diambil'
        )->where('pesanan.Status_Pembayaran', 'Lunas')
            ->where('pesanan.Status', 'Diterima')
            ->whereDate('pesanan.Tanggal_Diambil', $tanggalBesok)
            ->get();

        return $pesanan;
    }



    public function getListPesananHarian($tanggalBesok)
    {
        $pesanan = model_pesanan::select(
            'pesanan.Id',
            'customer.Nama',
            'pesanan.Tanggal_Diambil'
        )->where('pesanan.Status_Pembayaran', 'Lunas')
            ->where('pesanan.Status', 'Diterima')
            ->whereDate('pesanan.Tanggal_Diambil', $tanggalBesok)
            ->join('customer', 'pesanan.Customer_Email', '=', 'customer.Email')
            ->get();

        return $pesanan;
    }

    public function getListPesananHarianDanYangDibeli($tanggalBesok)
    {
        $pesanan = $this->getListPesananHarian($tanggalBesok);

        $listPesanan = [];

        foreach ($pesanan as $p) {
            $listPesanan[] = [
                'Pesanan' => $p,
                'Detail_Pesanan' => $this->getDetailPesanan($p->Id)
            ];
        }

        return $listPesanan;
    }


    public function getDetailPesanan($noNota)
    {
        $detailPesanan = model_detail_transaksi::select(
            DB::raw('IFNULL(produk.Nama, NULL) as Nama_Produk'),
            DB::raw('IFNULL(hampers.Nama_Hampers, NULL) as Nama_Hampers'),
            'detail_transaksi.Produk_Id',
            'detail_transaksi.Hampers_Id',
            'detail_transaksi.Total_Produk',
            'detail_transaksi.Pesanan_Id',
        )->leftJoin('produk', 'detail_transaksi.Produk_Id', '=', 'produk.Id')
            ->leftJoin('hampers', 'detail_transaksi.Hampers_Id', '=', 'hampers.Id')
            ->where('detail_transaksi.Pesanan_id', $noNota)
            ->get();

        return $detailPesanan;
    }

    public function getProdukFromHampers($idHampers)
    {
        $produk = model_detail_hampers::select(
            'produk.Nama',
            'produk.Id',
            'detail_hampers.Jumlah'
        )->where('detail_hampers.Hampers_Id', $idHampers)
            ->leftJoin('produk', 'detail_hampers.Produk_Id', '=', 'produk.Id')
            ->get();

        return $produk;
    }

    public function filterAndDeleteTrailingSpace($string)
    {
        $string = preg_replace('/[^a-zA-Z ]/', '', $string);
        $string = str_replace('Loyang', '', $string);
        $string = rtrim($string);

        return $string;
    }


    public function getResepFromDetailPesanan($noNota)
    {
        $pesanan = $this->getDetailPesanan($noNota);

        $isiResep = [];

        foreach ($pesanan as $p) {
            if ($p->Nama_Hampers != null) {
                $produk = $this->getProdukFromHampers($p->Hampers_Id);
                foreach ($produk as $pr) {

                    $cleanedProductName = $this->filterAndDeleteTrailingSpace($pr->Nama);


                    $resep = model_resep::select(
                        'resep.Id',
                        'resep.Nama as Nama_Resep',

                    )->where('Nama', 'like', '%' . $cleanedProductName . '%')

                        ->get();

                    foreach ($resep as $r) {
                        $r->Nama_Produk_asli = $pr->Nama;
                        $r->Jumlah = $pr->Jumlah;
                        $isiResep[] = $r;
                    }
                }
            }
            if ($p->Nama_Produk != null) {
                $cleanedProductName = $this->filterAndDeleteTrailingSpace($p->Nama_Produk);
                $resep = model_resep::select(
                    'resep.Id',
                    'resep.Nama as Nama_Resep'
                )->where('Nama', 'like', '%' . $cleanedProductName . '%')
                    ->get();

                foreach ($resep as $r) {
                    $r->Nama_Produk_asli = $p->Nama_Produk;
                    $r->Jumlah = $p->Total_Produk;
                    $isiResep[] = $r;
                }
            }
        }

        return $isiResep;
    }

    public function cekProdukSetengahLoyang($NamaProduk)
    {
        $pattern = '/1\/2\s*loyang/i';

        // Perform the regex match
        if (preg_match($pattern, $NamaProduk)) {
            return true;
        } else {
            return false;
        }
    }

    public function convertSetengahLoyangKeSatuLoyang($jumlah)
    {
        $jumlah = $jumlah / 2;

        return $jumlah;
    }

    public function prosesPesanan($noNota)
    {
        $resep = $this->getResepFromDetailPesanan($noNota);


        $ProsesPesanan = [];

        foreach ($resep as $r) {

            if ($this->cekProdukSetengahLoyang($r->Nama_Produk_asli) && $r->Jumlah > 1) {

                $r->Jumlah_Proses = $this->convertSetengahLoyangKeSatuLoyang($r->Jumlah);
                $r->sisa = false;
                $ProsesPesanan[] = $r;
            } else if ($this->cekProdukSetengahLoyang($r->Nama_Produk_asli) && $r->Jumlah == 1) {
                $r->Jumlah_Proses = $r->Jumlah;
                $r->sisa = true;
                $ProsesPesanan[] = $r;
            } else {
                $r->Jumlah_Proses = $r->Jumlah;
                $r->sisa = false;
                $ProsesPesanan[] = $r;
            }
        }

        return $ProsesPesanan;
    }

    public function getDetailResepAndKebutuhanById($id, $kebutuhan)
    {
        $detail_resep = model_detail_resep::select(
            'resep.Id as Id_Resep',
            'bahan_baku.Nama',
            DB::raw('SUM(detail_resep.qty * ' . $kebutuhan . ') as Jumlah'),
            'detail_resep.satuan',
            'bahan_baku.Id as Id_Bahan_Baku'
        )->leftJoin('bahan_baku', 'detail_resep.Bahan_Baku_Id', '=', 'bahan_baku.Id')
            ->leftJoin('resep', 'detail_resep.Resep_Id', '=', 'resep.Id')
            ->where('detail_resep.Resep_Id', '=', $id)
            ->groupBy('detail_resep.Bahan_Baku_Id', 'detail_resep.satuan', 'resep.Id', 'bahan_baku.Nama', 'resep.Nama', 'bahan_baku.Id')
            ->get();

        return $detail_resep;
    }



    public function getDetailResepByPesanan($noNota)
    {
        $resep = $this->prosesPesanan($noNota);

        $detailResep = [];
        foreach ($resep as $r) {

            $detailResep[] = $this->getDetailResepAndKebutuhanById($r->Id, $r->Jumlah_Proses);
        }
        return $detailResep;
    }

    public function getDetailResepDanNamaResep($noNota)
    {
        $resep = $this->prosesPesanan($noNota);

        $detailResep = [];
        foreach ($resep as $r) {

            $detailResep[] = [
                'Nama_Resep' => $r->Nama_Resep,
                'Detail_Resep' => $this->getDetailResepAndKebutuhanById($r->Id, $r->Jumlah_Proses)
            ];
        }

        return $detailResep;
    }

    public function getDetailResepDanNamaResepUntukPesananBesok($tanggal_besok)
    {
        $pesanan = $this->getListPesananHarian($tanggal_besok);

        $detail = [];

        foreach ($pesanan as $p) {

            $detail[] = [
                'Detail_Resep' => $this->getDetailResepDanNamaResep($p->Id)
            ];
        }

        return $detail;
    }
    public function getRekapPesananHarian($tanggalBesok)
    {
        $pesanan = $this->getListPesananHarian($tanggalBesok);

        $produk = [];

        foreach ($pesanan as $p) {
            $detailPesanan = $this->getDetailPesanan($p->Id);
            foreach ($detailPesanan as $dp) {
                if ($dp->Nama_Produk != null) {
                    $produk[] = [
                        'Produk' => $dp->Nama_Produk,
                        'Jumlah' => $dp->Total_Produk
                    ];
                }
                if ($dp->Nama_Hampers != null) {
                    $namaProduk = $this->getProdukFromHampers($dp->Hampers_Id);
                    foreach ($namaProduk as $np) {
                        $produk[] = [
                            'Produk' => $np->Nama,
                            'Jumlah' => $np->Jumlah
                        ];
                    }
                }
            }
        }

        return $produk;
    }

    public function getBahanBakubyId($id)
    {
        $bahan_baku = model_bahan_baku::select(
            'bahan_baku.Id',
            'bahan_baku.Nama',
            'bahan_baku.Qty',
            'bahan_baku.Satuan'
        )->where('bahan_baku.Id', $id)
            ->get();

        return $bahan_baku;
    }

    public function countKebutuhanBahanBaku($jumlah)
    {
        $kebutuhan = $jumlah;
        return $kebutuhan;
    }

    public function compareStokBahanBakuDanKebutuhan($noNota)
    {
        $pesanan = $this->getDetailResepByPesanan($noNota);

        $stokBahanBaku = [];

        foreach ($pesanan as $p) {
            foreach ($p as $b) {

                $bahan_baku = $this->getBahanBakubyId($b->Id_Bahan_Baku);
                foreach ($bahan_baku as $bb) {
                    $kebutuhan = $this->countKebutuhanBahanBaku($b->Jumlah);
                    if ($bb->Qty < $kebutuhan) {
                        $bb->Kebutuhan = $kebutuhan;
                        $bb->Kekurangan = $kebutuhan - $bb->Qty;
                        $stokBahanBaku[] = $bb;
                    }
                }
            }
        }

        return $stokBahanBaku;
    }




    public function CatatPemakaianBahanBaku($noNota)
    {
        $pesanan = $this->getDetailResepByPesanan($noNota);

        foreach ($pesanan as $p) {
            foreach ($p as $b) {
                $bahan_baku = $this->getBahanBakubyId($b->Id_Bahan_Baku);
                foreach ($bahan_baku as $bb) {
                    $kebutuhan = $this->countKebutuhanBahanBaku($b->Jumlah);
                    $today = Carbon::now();

                    $today = $today->format('Y-m-d');

                    $data = [
                        'Bahan_Baku_Id' => $bb->Id,
                        'Tanggal_Digunakan' => $today,
                        'Jumlah_Penggunaan' => $kebutuhan,
                        'Satuan' => $bb->Satuan
                    ];

                    $this->service_history_bahan_baku->CatatPemakaianBahanBaku($data);
                }
            }
        }
    }

    public function KurangiStokBahanBaku($noNota)
    {
        $pesanan = $this->getDetailResepByPesanan($noNota);

        foreach ($pesanan as $p) {
            foreach ($p as $b) {
                $bahan_baku = $this->getBahanBakubyId($b->Id_Bahan_Baku);
                foreach ($bahan_baku as $bb) {
                    $kebutuhan = $this->countKebutuhanBahanBaku($b->Jumlah);
                    $bb->Qty = $bb->Qty - $kebutuhan;
                    $bb->save();
                }
            }
        }
    }
}
