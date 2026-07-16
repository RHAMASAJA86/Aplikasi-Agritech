<?php
require_once __DIR__ . '/../config/Database.php';

class MusimTanam
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        return $this->db->query("SELECT * FROM musim_tanam ORDER BY tanggal_mulai DESC")->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM musim_tanam WHERE id_musim = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: [];
    }

    public function getAktif()
    {
        return $this->db->query("SELECT * FROM musim_tanam WHERE status = 'Aktif' ORDER BY tanggal_mulai DESC")->fetchAll();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO musim_tanam (nama_musim, luas_lahan, tanggal_mulai, tanggal_panen, status)
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['nama_musim'],
            $data['luas_lahan'],
            $data['tanggal_mulai'],
            $data['tanggal_panen'],
            $data['status'] ?? 'Aktif',
        ]);
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE musim_tanam
             SET nama_musim = ?, luas_lahan = ?, tanggal_mulai = ?, tanggal_panen = ?, hasil_panen = ?
             WHERE id_musim = ?"
        );
        return $stmt->execute([
            $data['nama_musim'],
            $data['luas_lahan'],
            $data['tanggal_mulai'],
            $data['tanggal_panen'],
            $data['hasil_panen'] ?? 0,
            $id,
        ]);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE musim_tanam SET status = ? WHERE id_musim = ?");
        return $stmt->execute([$status, $id]);
    }

    public function delete($id)
    {
        $this->db->beginTransaction();
        try {
            // Hapus dulu riwayat penjualan (tidak auto-cascade dari DB)
            $stmt = $this->db->prepare("DELETE FROM penjualan WHERE id_musim = ?");
            $stmt->execute([$id]);

            // biaya & stok_gudang otomatis ikut terhapus (sudah ON DELETE CASCADE)
            $stmt = $this->db->prepare("DELETE FROM musim_tanam WHERE id_musim = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getTotalHasilPanen()
    {
        $stmt = $this->db->query("SELECT IFNULL(SUM(hasil_panen),0) AS total FROM musim_tanam");
        return (float) $stmt->fetch()['total'];
    }
}