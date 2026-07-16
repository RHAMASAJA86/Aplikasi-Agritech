<?php
require_once __DIR__ . '/../config/Database.php';

class Laporan
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getRingkasan($id_musim = null)
    {
        if (!empty($id_musim)) {
            $stmt = $this->db->prepare(
                "SELECT
                    m.id_musim,
                    m.nama_musim,
                    IFNULL((SELECT SUM(nominal) FROM biaya WHERE id_musim = m.id_musim), 0) AS total_pengeluaran,
                    IFNULL((SELECT SUM(total) FROM penjualan WHERE id_musim = m.id_musim), 0) AS total_pendapatan
                 FROM musim_tanam m
                 WHERE m.id_musim = ?"
            );
            $stmt->execute([$id_musim]);
            $row = $stmt->fetch();

            if (!$row) {
                return ['id_musim' => $id_musim, 'nama_musim' => '-', 'total_pengeluaran' => 0, 'total_pendapatan' => 0, 'keuntungan' => 0];
            }

            $row['total_pengeluaran'] = (float) $row['total_pengeluaran'];
            $row['total_pendapatan']  = (float) $row['total_pendapatan'];
            $row['keuntungan']        = $row['total_pendapatan'] - $row['total_pengeluaran'];
            return $row;
        }

        $totalPengeluaran = (float) $this->db->query("SELECT IFNULL(SUM(nominal),0) AS total FROM biaya")->fetch()['total'];
        $totalPendapatan  = (float) $this->db->query("SELECT IFNULL(SUM(total),0) AS total FROM penjualan")->fetch()['total'];

        return [
            'nama_musim'        => 'Semua Musim',
            'total_pengeluaran' => $totalPengeluaran,
            'total_pendapatan'  => $totalPendapatan,
            'keuntungan'        => $totalPendapatan - $totalPengeluaran,
        ];
    }

    public function getBiayaDetail($id_musim)
    {
        $stmt = $this->db->prepare("SELECT * FROM biaya WHERE id_musim = ? ORDER BY tanggal DESC");
        $stmt->execute([$id_musim]);
        return $stmt->fetchAll();
    }

    public function getPenjualanDetail($id_musim)
    {
        $stmt = $this->db->prepare("SELECT * FROM penjualan WHERE id_musim = ? ORDER BY tanggal DESC");
        $stmt->execute([$id_musim]);
        return $stmt->fetchAll();
    }

    public function getStokDetail($id_musim)
    {
        $stmt = $this->db->prepare("SELECT * FROM stok_gudang WHERE id_musim = ?");
        $stmt->execute([$id_musim]);
        return $stmt->fetchAll();
    }

    // Untuk bar chart "Grafik Pendapatan dan Pengeluaran" per minggu dalam 1 bulan.
    public function getGrafikMingguan($bulan)
    {
        $labels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'];
        $pendapatan  = [0, 0, 0, 0];
        $pengeluaran = [0, 0, 0, 0];

        $stmtP = $this->db->prepare(
            "SELECT LEAST(CEIL(DAY(tanggal)/7),4) AS minggu, SUM(total) AS total
             FROM penjualan WHERE DATE_FORMAT(tanggal,'%Y-%m') = ? GROUP BY minggu"
        );
        $stmtP->execute([$bulan]);
        foreach ($stmtP->fetchAll() as $row) {
            $pendapatan[(int) $row['minggu'] - 1] = (float) $row['total'];
        }

        $stmtB = $this->db->prepare(
            "SELECT LEAST(CEIL(DAY(tanggal)/7),4) AS minggu, SUM(nominal) AS total
             FROM biaya WHERE DATE_FORMAT(tanggal,'%Y-%m') = ? GROUP BY minggu"
        );
        $stmtB->execute([$bulan]);
        foreach ($stmtB->fetchAll() as $row) {
            $pengeluaran[(int) $row['minggu'] - 1] = (float) $row['total'];
        }

        return ['labels' => $labels, 'pendapatan' => $pendapatan, 'pengeluaran' => $pengeluaran];
    }

    // Pakai stored procedure laporan_musim(id_musim) yang sudah ada di database.
    public function callLaporanMusim($id_musim)
    {
        $stmt = $this->db->prepare("CALL laporan_musim(?)");
        $stmt->execute([$id_musim]);
        $result = $stmt->fetch();
        $stmt->closeCursor(); // wajib setelah CALL supaya koneksi bisa dipakai lagi
        return $result ?: [];
    }
}