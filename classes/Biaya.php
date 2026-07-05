<?php
require_once __DIR__ . '/../config/Database.php';

class Biaya
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($id_musim = null, $bulan = null)
    {
        $sql = "SELECT b.*, m.nama_musim
                FROM biaya b
                JOIN musim_tanam m ON m.id_musim = b.id_musim
                WHERE 1=1";
        $params = [];

        if (!empty($id_musim)) {
            $sql .= " AND b.id_musim = ?";
            $params[] = $id_musim;
        }
        if (!empty($bulan)) {
            $sql .= " AND DATE_FORMAT(b.tanggal, '%Y-%m') = ?";
            $params[] = $bulan;
        }
        $sql .= " ORDER BY b.tanggal DESC, b.id_biaya DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM biaya WHERE id_biaya = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: [];
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO biaya (id_musim, tanggal, kategori, deskripsi, nominal)
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['id_musim'], $data['tanggal'], $data['kategori'],
            $data['deskripsi'], $data['nominal'],
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE biaya
             SET id_musim = ?, tanggal = ?, kategori = ?, deskripsi = ?, nominal = ?
             WHERE id_biaya = ?"
        );
        return $stmt->execute([
            $data['id_musim'], $data['tanggal'], $data['kategori'],
            $data['deskripsi'], $data['nominal'], $id,
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM biaya WHERE id_biaya = ?");
        return $stmt->execute([$id]);
    }

    public function getTotalBiayaBulanIni()
    {
        $stmt = $this->db->prepare(
            "SELECT IFNULL(SUM(nominal),0) AS total FROM biaya WHERE DATE_FORMAT(tanggal,'%Y-%m') = ?"
        );
        $stmt->execute([date('Y-m')]);
        return (float) $stmt->fetch()['total'];
    }

    public function getTotalByKategori($kategori, $bulan)
    {
        $stmt = $this->db->prepare(
            "SELECT IFNULL(SUM(nominal),0) AS total
             FROM biaya WHERE kategori = ? AND DATE_FORMAT(tanggal,'%Y-%m') = ?"
        );
        $stmt->execute([$kategori, $bulan]);
        return (float) $stmt->fetch()['total'];
    }

    // Untuk line chart "Analisis Pengeluaran" di dashboard.
    // Tgl 1-7 = Minggu 1, 8-14 = Minggu 2, 15-21 = Minggu 3, 22-akhir bulan = Minggu 4.
    public function getAnalisisMingguanBulanIni($bulan = null)
    {
        $bulan = $bulan ?? date('Y-m');
        $stmt = $this->db->prepare(
            "SELECT LEAST(CEIL(DAY(tanggal)/7),4) AS minggu, SUM(nominal) AS total
             FROM biaya WHERE DATE_FORMAT(tanggal,'%Y-%m') = ? GROUP BY minggu"
        );
        $stmt->execute([$bulan]);

        $hasil = ['Minggu 1' => 0, 'Minggu 2' => 0, 'Minggu 3' => 0, 'Minggu 4' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $hasil['Minggu ' . (int) $row['minggu']] = (float) $row['total'];
        }
        return $hasil;
    }
}