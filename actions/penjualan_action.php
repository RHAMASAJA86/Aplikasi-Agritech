<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Penjualan.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi berakhir, silakan login kembali.']);
    exit;
}

function parseAngka($val)
{
    $val = str_replace(['Rp', '.', ',', ' '], '', (string) $val);
    return $val === '' ? 0 : (float) $val;
}

$penjualan = new Penjualan();
$act       = $_GET['act'] ?? '';

try {
    switch ($act) {
        case 'create':
        case 'update':
            $id_musim   = (int) ($_POST['id_musim'] ?? 0);
            $tanggal    = $_POST['tanggal'] ?? '';
            $pembeli    = htmlspecialchars(trim($_POST['pembeli'] ?? ''));
            $jumlahKg   = parseAngka($_POST['jumlah_kg'] ?? 0);
            $hargaPerKg = parseAngka($_POST['harga_perkg'] ?? 0);

            if ($id_musim <= 0 || $tanggal === '' || $pembeli === '') {
                echo json_encode(['status' => 'error', 'message' => 'Musim tanam, tanggal, dan nama pembeli wajib diisi.']);
                exit;
            }
            if ($jumlahKg <= 0 || $hargaPerKg <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Jumlah kg dan harga per kg harus lebih dari 0.']);
                exit;
            }

            $data = [
                'id_musim'    => $id_musim,
                'tanggal'     => $tanggal,
                'pembeli'     => $pembeli,
                'jumlah_kg'   => $jumlahKg,
                'harga_perkg' => $hargaPerKg,
            ];

            if ($act === 'create') {
                $ok  = $penjualan->create($data);
                $msg = 'Penjualan berhasil ditambahkan.';
            } else {
                $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
                if ($id <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'ID penjualan tidak valid.']);
                    exit;
                }
                $ok  = $penjualan->update($id, $data);
                $msg = 'Penjualan berhasil diperbarui.';
            }
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? $msg : 'Gagal menyimpan data penjualan.']);
            break;

        case 'delete':
            $id = (int) ($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID penjualan tidak valid.']);
                exit;
            }
            $ok = $penjualan->delete($id);
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Penjualan berhasil dihapus, stok dikembalikan.' : 'Gagal menghapus penjualan.']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
    }
} catch (Exception $e) {
    // Exception dari Penjualan::create/update biasanya soal stok tidak cukup
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}