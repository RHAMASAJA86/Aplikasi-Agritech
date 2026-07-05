<?php
require_once __DIR__ . '/../config/Database.php';

class Penjualan
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($bulan = null)
    {
        $sql = "SELECT p.*, m.nama_musim
                FROM penjualan p
                JOIN musim_tanam m ON m.id_musim = p.id_musim
                WHERE 1=1";
        $params = [];
        if (!empty($bulan)) {
            $sql .= " AND DATE_FORMAT(p.tanggal, '%Y-%m') = ?";
            $params[] = $bulan;
        }
        $sql .= " ORDER BY p.tanggal DESC, p.id_penjualan DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM penjualan WHERE id_penjualan = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: [];
    }

    // trigger DB (total & kurangi stok) cuma jalan saat INSERT.
    // Di sini kita validasi dulu jumlah_kg vs stok yang tersedia untuk musim itu.
    public function create($data)
    {
        $stokTersedia = $this->getStokMusim($data['id_musim']);
        if ((float) $data['jumlah_kg'] > $stokTersedia) {
            throw new Exception('Stok tidak mencukupi. Stok tersedia: ' . $stokTersedia . ' kg.');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO penjualan (id_musim, tanggal, pembeli, jumlah_kg, harga_perkg)
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['id_musim'], $data['tanggal'], $data['pembeli'],
            $data['jumlah_kg'], $data['harga_perkg'],
        ]);
    }

    // Trigger DB tidak jalan saat UPDATE, jadi diurus manual:
    // kembalikan stok lama -> validasi stok musim baru -> hitung ulang total -> kurangi stok baru.
    public function update($id, $data)
    {
        $lama = $this->getById($id);
        if (!$lama) {
            throw new Exception('Data penjualan tidak ditemukan.');
        }

        $this->db->beginTransaction();
        try {
            $this->ubahStok($lama['id_musim'], $lama['jumlah_kg']); // kembalikan dulu

            $stokTersedia = $this->getStokMusim($data['id_musim']);
            if ((float) $data['jumlah_kg'] > $stokTersedia) {
                throw new Exception('Stok tidak mencukupi. Stok tersedia: ' . $stokTersedia . ' kg.');
            }

            $total = (float) $data['jumlah_kg'] * (float) $data['harga_perkg'];

            $stmt = $this->db->prepare(
                "UPDATE penjualan
                 SET id_musim = ?, tanggal = ?, pembeli = ?, jumlah_kg = ?, harga_perkg = ?, total = ?
                 WHERE id_penjualan = ?"
            );
            $stmt->execute([
                $data['id_musim'], $data['tanggal'], $data['pembeli'],
                $data['jumlah_kg'], $data['harga_perkg'], $total, $id,
            ]);

            $this->ubahStok($data['id_musim'], -$data['jumlah_kg']);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // Hapus penjualan + kembalikan stok yang sudah dikurangi trigger saat insert.
    public function delete($id)
    {
        $lama = $this->getById($id);
        if (!$lama) {
            throw new Exception('Data penjualan tidak ditemukan.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("DELETE FROM penjualan WHERE id_penjualan = ?");
            $stmt->execute([$id]);

            $this->ubahStok($lama['id_musim'], $lama['jumlah_kg']);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getTotalPenjualanBulanIni()
    {
        $stmt = $this->db->prepare(
            "SELECT IFNULL(SUM(total),0) AS total FROM penjualan WHERE DATE_FORMAT(tanggal,'%Y-%m') = ?"
        );
        $stmt->execute([date('Y-m')]);
        return (float) $stmt->fetch()['total'];
    }

    public function getTotalTransaksiBulanIni()
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM penjualan WHERE DATE_FORMAT(tanggal,'%Y-%m') = ?"
        );
        $stmt->execute([date('Y-m')]);
        return (int) $stmt->fetch()['total'];
    }

    public function getTotalKgTerjualBulanIni()
    {
        $stmt = $this->db->prepare(
            "SELECT IFNULL(SUM(jumlah_kg),0) AS total FROM penjualan WHERE DATE_FORMAT(tanggal,'%Y-%m') = ?"
        );
        $stmt->execute([date('Y-m')]);
        return (float) $stmt->fetch()['total'];
    }

    private function getStokMusim($id_musim)
    {
        $stmt = $this->db->prepare(
            "SELECT IFNULL(SUM(jumlah_stok),0) AS total FROM stok_gudang WHERE id_musim = ?"
        );
        $stmt->execute([$id_musim]);
        return (float) $stmt->fetch()['total'];
    }

    // $delta positif = nambah stok, negatif = mengurangi. Diterapkan ke baris
    // stok_gudang terbaru milik musim tsb (dibuat otomatis oleh trg_tambah_stok).
    private function ubahStok($id_musim, $delta)
    {
        $stmt = $this->db->prepare(
            "SELECT id_stok FROM stok_gudang WHERE id_musim = ? ORDER BY id_stok DESC LIMIT 1"
        );
        $stmt->execute([$id_musim]);
        $row = $stmt->fetch();

        if ($row) {
            $upd = $this->db->prepare("UPDATE stok_gudang SET jumlah_stok = jumlah_stok + ? WHERE id_stok = ?");
            $upd->execute([$delta, $row['id_stok']]);
        }
    }
}