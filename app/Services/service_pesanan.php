<?php

namespace App\Services;

use App\Models\model_pesanan;
use App\Models\model_produk;
use App\Services\services_poin;
use App\Models\model_detail_transaksi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Class service_pesanan.
 */
class service_pesanan
{
    private services_poin $service_Poin;
    private service_utils $service_Utils;

    public function __construct(services_poin $service_Poin, service_utils $service_Utils)
    {
        $this->service_Poin = $service_Poin;
        $this->service_Utils = $service_Utils;
    }



    public function readHistoryByEmail(string $id): object
    {
        $pesanan = model_pesanan::select(
            'pesanan.Id as Id',
            'pesanan.Ongkos_Kirim as Ongkos_Kirim',
            'pesanan.Total as Total',
            'pesanan.Status as Status',
            'pesanan.Tanggal_Diambil as Tanggal_Diambil',
            'pesanan.Tanggal_Pesan as Tanggal_Pesan',
            'customer.Email as Email',
            'customer.Nama as Nama',
            'pesanan.Bukti_Pembayaran as Bukti_Pembayaran',
            'pesanan.Tanggal_Pelunasan as Tanggal_Pelunasan',
            'alamat.Alamat as Alamat',
            'pesanan.Status_Pembayaran as Status_Pembayaran',
            'pesanan.Tip as Tip',
        )
            ->leftJoin('customer', 'pesanan.Customer_Email', '=', 'customer.Email')
            ->leftJoin('alamat', 'pesanan.Alamat_Id', '=', 'alamat.Id')
            ->where('customer.Email', $id)
            ->get();


        foreach ($pesanan as $pes) {
            $produk = model_pesanan::select('produk.Nama as Nama_Produk')
                ->join('detail_transaksi', 'pesanan.Id', '=', 'detail_transaksi.Pesanan_id')
                ->join('produk', 'detail_transaksi.Produk_Id', '=', 'produk.Id')
                ->where('pesanan.Id', $pes->Id)
                ->get();

            $hampers = model_pesanan::select('hampers.Nama_Hampers as Nama_Hampers')
                ->join('detail_transaksi', 'pesanan.Id', '=', 'detail_transaksi.Pesanan_id')
                ->join('hampers', 'detail_transaksi.Hampers_Id', '=', 'hampers.Id')
                ->where('pesanan.Id', $pes->Id)
                ->get();

            $produkNames = [];
            foreach ($produk as $prod) {
                $produkNames[] = $prod->Nama_Produk;
            }
            $pes->Nama_Produk = implode(', ', $produkNames);


            $hampersNames = [];
            foreach ($hampers as $hamp) {
                $hampersNames[] = $hamp->Nama_Hampers;
            }
            $pes->Nama_Hampers = implode(', ', $hampersNames);
        }

        return $pesanan;
    }



    public function getAllHistoryPesanan(): object
    {
        return model_pesanan::select(
            'pesanan.Id as Id',
            'pesanan.Ongkos_Kirim as Ongkos_Kirim',
            'pesanan.Total as Total',
            'pesanan.Status as Status',
            'pesanan.Tanggal_Diambil as Tanggal_Diambil',
            'pesanan.Tanggal_Pesan as Tanggal_Pesan',
            'customer.Email as Email',
            'customer.Nama as Nama',
            'pesanan.Bukti_Pembayaran as Bukti_Pembayaran',
            'pesanan.Tanggal_Pelunasan as Tanggal_Pelunasan',
            'alamat.Alamat as Alamat',
            'pesanan.Status_Pembayaran as Status_Pembayaran',
            'pesanan.Tip as Tip',
        )->leftJoin('customer', 'pesanan.Customer_Email', '=', 'customer.Email')
            ->leftJoin('alamat', 'pesanan.Alamat_Id', '=', 'alamat.Id')
            ->get();
    }

    public function getLatestPesananId($month): String
    {
        $tahunNow = date('y');

        $latestPesanan = model_pesanan::select('Id')
            ->latest()->get()->first();


        if ($latestPesanan == null) {
            if ($month < 10)
                return $tahunNow . '.0' . $month . '.' . '00';
            else
                return $tahunNow . '.' . $month . '.' . '00';
        }

        return $latestPesanan->Id;
    }

    public function generateNoNota($month): String
    {
        $latestPesanan = $this->getLatestPesananId($month);

        $latestPesanan = explode('.', $latestPesanan);

        $latestPesanan[2] = strval(intval($latestPesanan[2]) + 1);

        $latestPesanan[2] = str_pad($latestPesanan[2], 2, '0', STR_PAD_LEFT);

        return implode('.', $latestPesanan);
    }

    public function penggunaanPoin($email, $total): int
    {
        $poin = $this->service_Poin->getPoinPerCustomer($email);

        $poinDigunakan = min(floor($total / 100), $poin);

        $sisaPoin = $poin - $poinDigunakan;

        $this->service_Poin->setPoinPerCustomer($email, $sisaPoin);

        return $poinDigunakan;
    }


    public function updateTotalBayar($poinDigunakan, $total): float
    {
        $maksPoin = floor($total / 100);

        if ($poinDigunakan > $maksPoin) {
            $poinDigunakan = $maksPoin;
        }

        $totalBayar = $total - ($poinDigunakan * 100);

        return $totalBayar;
    }


    public function isPenitip($produkId): bool
    {
        $produk = model_produk::find($produkId);

        return $produk->Penitip_Id != null;
    }

    public function kurangiStok($produkId, $jumlah): void
    {
        $produk = model_produk::find($produkId);

        $produk->Stok -= $jumlah;

        $produk->save();
    }

    public function PesanProduk($request): void
    {
        $pesanan = new model_pesanan();
        $pesanan->Id = $request['Id'];
        $pesanan->Customer_Email = $request['Customer_Email'];
        $pesanan->Tanggal_Pesan = $request['Tanggal_Pesan'];
        $pesanan->Tanggal_Diambil = $request['Tanggal_Diambil'];
        $pesanan->Status = "Menunggu Pembayaran";
        $pesanan->Status_Pembayaran = "Belum Lunas";
        $pesanan->Poin_Didapat = $request['Poin_Didapat'];
        $poinDigunakan = $this->penggunaanPoin($request['Customer_Email'], $request['Total']);
        $pesanan->Penggunaan_Poin = $poinDigunakan;
        $totalBayar = $this->updateTotalBayar($poinDigunakan, $request['Total']);
        $pesanan->Total = $totalBayar;
        $pesanan->Alamat_Id = $request['Alamat_Id'];
        $pesanan->Is_Deliver = $request['Is_Deliver'];
        $pesanan->save();
    }


    public function getTanggalPesananSelesai($Email)
    {
        $pesanan = model_pesanan::select('Tanggal_Diambil')
            ->where('Customer_Email', $Email)
            ->where('Status', 'Selesai')
            ->get();
        return $pesanan;
    }

    public function getNoNotaDenganStatusSelesai($Email)
    {
        $pesanan = model_pesanan::select('Id')
            ->where('Customer_Email', $Email)
            ->where('Status', 'Selesai')
            ->get();
        return $pesanan;
    }
    public function getDetailPesananByNota($Id)
    {
        $detailPesanan = model_detail_transaksi::select(
            'detail_transaksi.Total_Produk',
            DB::raw('IFNULL(produk.Nama, NULL) as Nama_Produk'),
            DB::raw('IFNULL(hampers.Nama_Hampers, NULL) as Nama_Hampers'),
            'hampers.Gambar as Gambar_Hampers',
            'produk.Gambar as Gambar_Produk',
            'detail_transaksi.SubTotal',
            'produk.Harga as Harga_Produk',
            'hampers.Harga as Harga_Hampers',
        )->leftJoin('produk', 'detail_transaksi.Produk_Id', '=', 'produk.Id')
            ->leftJoin('hampers', 'detail_transaksi.Hampers_Id', '=', 'hampers.Id')
            ->where('detail_transaksi.Pesanan_id', $Id)
            ->where(function ($query) {
                $query->whereNotNull('produk.Nama')
                    ->orWhereNotNull('hampers.Nama_Hampers');
            })
            ->get();

        foreach ($detailPesanan as $detail) {
            if (!is_null($detail->Nama_Produk)) {
                if ($detail->Gambar_Produk == null || $detail->Gambar_Produk == "undefined")
                    $detail->Gambar_Produk = url(Storage::url('defaultimage.webp'));
                else
                    $detail->Gambar_Produk = url(Storage::url('produk/' . $detail->Gambar_Produk));
            } else {
                if ($detail->Gambar_Hampers == null || $detail->Gambar_Hampers == "undefined")
                    $detail->Gambar_Hampers = url(Storage::url('defaultimage.webp'));
                else
                    $detail->Gambar_Hampers = url(Storage::url('hampers/' . $detail->Gambar_Hampers));
            }
        }

        return $detailPesanan;
    }



    public function getPesananSelesaiWithDetailPesananAndTanggal($Email)
    {
        //group by tanggal diambil
        $Nota = $this->getNoNotaDenganStatusSelesai($Email);
        $Tanggal = $this->getTanggalPesananSelesai($Email);

        $data = [];
        for ($i = 0; $i < count($Nota); $i++) {
            $detailPesanan = $this->getDetailPesananByNota($Nota[$i]->Id);
            $data[] = [
                'No_Nota' => $Nota[$i]->Id,
                'Tanggal_Diambil' => $Tanggal[$i]->Tanggal_Diambil,
                'Detail_Pesanan' => $detailPesanan
            ];
        }
        return $data;
    }


    public function GenerateNota($NoNota)
    {
        $Nota = model_pesanan::select(
            "pesanan.Id as NoNota",
            "pesanan.Tanggal_Pesan as TanggalPesan",
            "pesanan.Tanggal_Diambil as TanggalDiambil",
            "pesanan.Tanggal_Pelunasan as TanggalPelunasan",
            "customer.Email as Email",
            "alamat.Alamat as Alamat",
            "pesanan.Jasa_Pengiriman as JasaPengiriman",
            "pesanan.Ongkos_Kirim as OngkosKirim",
            "alamat.Jarak as Jarak",
            "pesanan.Poin_Didapat as PoinDidapat",
            DB::raw("sum(detail_transaksi.SubTotal) as Total_Raw"),
            DB::raw("pesanan.Penggunaan_Poin * 100 as PenggunaanPoin"),
            "pesanan.Total as Total"
        )->leftJoin("customer", "pesanan.Customer_Email", "=", "customer.Email")
            ->leftJoin("alamat", "pesanan.Alamat_Id", "=", "alamat.Id")
            ->leftJoin("detail_transaksi", "detail_transaksi.Pesanan_Id", "=", "pesanan.Id")
            ->where("pesanan.Id", $NoNota)
            ->groupBy(
                "pesanan.Id",
                "pesanan.Tanggal_Pesan",
                "pesanan.Tanggal_Diambil",
                "pesanan.Tanggal_Pelunasan",
                "customer.Email",
                "alamat.Alamat",
                "pesanan.Jasa_Pengiriman",
                "pesanan.Ongkos_Kirim",
                "alamat.Jarak",
                "pesanan.Total",
                "pesanan.Poin_Didapat",
                "pesanan.Penggunaan_Poin"
            )
            ->get();

        return $Nota;
    }



    public function getFullNota($NoNota)
    {
        $Nota = $this->GenerateNota($NoNota);
        $DetailPesanan = $this->getDetailPesananByNota($NoNota);

        $data = [
            'Nota' => $Nota,
            'DetailPesanan' => $DetailPesanan
        ];

        return $data;
    }

    public function getPesananOnGoing($Email)
    {
        $pesanan = model_pesanan::select(
            "pesanan.Id as NoNota",
            "pesanan.Tanggal_Pesan as TanggalPesan",
            "pesanan.Tanggal_Diambil as TanggalDiambil",
            "pesanan.Status as Status",
            "pesanan.Total as Total",
            "pesanan.Bukti_Pembayaran as Bukti"
        )->where("Customer_Email", $Email)
            ->whereNot("Status", "Selesai")->whereNot("Status", "Ditolak")->get();

        return $pesanan;
    }


    public function getPesananAndProdukOnGoing($Email)
    {
        $pesanan = $this->getPesananOnGoing($Email);

        $data = [];
        foreach ($pesanan as $pes) {
            $detailPesanan = $this->getDetailPesananByNota($pes->NoNota);
            $data[] = [
                'NoNota' => $pes->NoNota,
                'TanggalPesan' => $pes->TanggalPesan,
                'TanggalDiambil' => $pes->TanggalDiambil,
                'Status' => $pes->Status,
                'Total' => $pes->Total,
                'Bukti' => $pes->Bukti,
                'DetailPesanan' => $detailPesanan
            ];
        }

        return $data;
    }

    public function getPesananDitolak($Email)
    {
        $pesanan = model_pesanan::select(
            "pesanan.Id as NoNota",
            "pesanan.Tanggal_Pesan as TanggalPesan",
            "pesanan.Tanggal_Diambil as TanggalDiambil",
            "pesanan.Status as Status",
            "pesanan.Total as Total",
            "pesanan.Bukti_Pembayaran as Bukti"
        )->where("Customer_Email", $Email)
            ->where("Status", "Ditolak")->get();

        return $pesanan;
    }

    public function getPesananAndProdukDitolak($Email)
    {
        $pesanan = $this->getPesananDitolak($Email);

        $data = [];
        foreach ($pesanan as $pes) {
            $detailPesanan = $this->getDetailPesananByNota($pes->NoNota);
            $data[] = [
                'NoNota' => $pes->NoNota,
                'TanggalPesan' => $pes->TanggalPesan,
                'TanggalDiambil' => $pes->TanggalDiambil,
                'Status' => $pes->Status,
                'Total' => $pes->Total,
                'Bukti' => $pes->Bukti,
                'Detail_Pesanan' => $detailPesanan
            ];
        }

        return $data;
    }
}
