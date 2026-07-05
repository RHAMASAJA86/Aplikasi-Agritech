<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Biaya.php';

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

$kategoriValid = ['Pengolahan Tanah', 'Benih', 'Pupuk', 'Pestisida', 'Tenaga Kerja', 'Panen', 'Lain-lain'];

$biaya = new Biaya();
$act   = $_GET['act'] ?? '';

try {
    switch ($act) {
        case 'create':
        case 'update':
            $id_musim  = (int) ($_POST['id_musim'] ?? 0);
            $tanggal   = $_POST['tanggal'] ?? '';
            $kategori  = $_POST['kategori'] ?? '';
            $deskripsi = htmlspecialchars(trim($_POST['deskripsi'] ?? ''));
            $nominal   = parseAngka($_POST['nominal'] ?? 0);

            if ($id_musim <= 0 || $tanggal === '' || !in_array($kategori, $kategoriValid)) {
                echo json_encode(['status' => 'error', 'message' => 'Musim tanam, tanggal, dan kategori wajib diisi dengan benar.']);
                exit;
            }
            if ($nominal <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Nominal harus lebih dari 0.']);
                exit;
            }

            $data = [
                'id_musim'  => $id_musim,
                'tanggal'   => $tanggal,
                'kategori'  => $kategori,
                'deskripsi' => $deskripsi,
                'nominal'   => $nominal,
            ];

            if ($act === 'create') {
                $ok  = $biaya->create($data);
                $msg = 'Biaya berhasil ditambahkan.';
            } else {
                $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
                if ($id <= 0) {
                    echo json_encode(['status' => 'error', 'message' => 'ID biaya tidak valid.']);
                    exit;
                }
                $ok  = $biaya->update($id, $data);
                $msg = 'Biaya berhasil diperbarui.';
            }
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? $msg : 'Gagal menyimpan data biaya.']);
            break;

        case 'delete':
            $id = (int) ($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID biaya tidak valid.']);
                exit;
            }
            $ok = $biaya->delete($id);
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Biaya berhasil dihapus.' : 'Gagal menghapus biaya.']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}