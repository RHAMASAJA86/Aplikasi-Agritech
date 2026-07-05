<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/StokGudang.php';

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

$stok = new StokGudang();
$act  = $_GET['act'] ?? '';

try {
    switch ($act) {
        case 'create':
            $id_musim   = (int) ($_POST['id_musim'] ?? 0);
            $jumlahStok = parseAngka($_POST['jumlah_stok'] ?? 0);

            if ($id_musim <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Musim tanam wajib dipilih.']);
                exit;
            }
            if ($jumlahStok < 0) {
                echo json_encode(['status' => 'error', 'message' => 'Jumlah stok tidak boleh negatif.']);
                exit;
            }

            $ok = $stok->create(['id_musim' => $id_musim, 'jumlah_stok' => $jumlahStok]);
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Stok berhasil ditambahkan.' : 'Gagal menambahkan stok.']);
            break;

        case 'update':
            $id         = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            $jumlahStok = parseAngka($_POST['jumlah_stok'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID stok tidak valid.']);
                exit;
            }
            if ($jumlahStok < 0) {
                echo json_encode(['status' => 'error', 'message' => 'Jumlah stok tidak boleh negatif.']);
                exit;
            }

            $ok = $stok->update($id, $jumlahStok);
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Stok berhasil diperbarui.' : 'Gagal memperbarui stok.']);
            break;

        case 'delete':
            $id = (int) ($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID stok tidak valid.']);
                exit;
            }
            $ok = $stok->delete($id);
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Stok berhasil dihapus.' : 'Gagal menghapus stok.']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}