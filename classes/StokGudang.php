<?php
require_once __DIR__ . '/../config/Database.php';

class StokGudang
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        $sql = "SELECT s.*, m.nama_musim
                FROM stok_gudang s
                JOIN musim_tanam m ON m.id_musim = s.id_musim
                ORDER BY s.id_stok DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getByMusim($id_musim)
    {
        $stmt = $this->db->prepare("SELECT * FROM stok_gudang WHERE id_musim = ?");
        $stmt->execute([$id_musim]);
        return $stmt->fetchAll();
    }

    public function getStokSaatIni()
    {
        $stmt = $this->db->query("SELECT IFNULL(SUM(jumlah_stok),0) AS total FROM stok_gudang");
        return (float) $stmt->fetch()['total'];
    }

    // Bonus (bukan bagian dari 5 method wajib): tambah stok manual lewat tombol "+ Tambah".
    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO stok_gudang (id_musim, jumlah_stok) VALUES (?, ?)");
        return $stmt->execute([$data['id_musim'], $data['jumlah_stok']]);
    }

    public function update($id, $jumlah)
    {
        $stmt = $this->db->prepare("UPDATE stok_gudang SET jumlah_stok = ? WHERE id_stok = ?");
        return $stmt->execute([$jumlah, $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM stok_gudang WHERE id_stok = ?");
        return $stmt->execute([$id]);
    }
}