<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/MusimTanam.php';

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

$musim = new MusimTanam();
$act   = $_GET['act'] ?? '';

try {
    switch ($act) {
        case 'create':
            $nama   = trim(htmlspecialchars($_POST['nama_musim'] ?? ''));
            $luas   = parseAngka($_POST['luas_lahan'] ?? 0);
            $mulai  = $_POST['tanggal_mulai'] ?? '';
            $panen  = $_POST['tanggal_panen'] ?? '';
            $status = in_array($_POST['status'] ?? '', ['Aktif', 'Selesai']) ? $_POST['status'] : 'Aktif';

            if ($nama === '' || $mulai === '' || $panen === '') {
                echo json_encode(['status' => 'error', 'message' => 'Nama musim, tanggal mulai, dan tanggal panen wajib diisi.']);
                exit;
            }
            if ($luas <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'Luas lahan harus lebih dari 0.']);
                exit;
            }

            $ok = $musim->create([
                'nama_musim'    => $nama,
                'luas_lahan'    => $luas,
                'tanggal_mulai' => $mulai,
                'tanggal_panen' => $panen,
                'status'        => $status,
            ]);
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Musim tanam berhasil ditambahkan.' : 'Gagal menambahkan musim tanam.']);
            break;

        case 'update':
            $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID musim tidak valid.']);
                exit;
            }

            $existing = $musim->getById($id);
            if (!$existing) {
                echo json_encode(['status' => 'error', 'message' => 'Data musim tanam tidak ditemukan.']);
                exit;
            }

            $nama       = trim(htmlspecialchars($_POST['nama_musim'] ?? ''));
            $luas       = parseAngka($_POST['luas_lahan'] ?? 0);
            $mulai      = $_POST['tanggal_mulai'] ?? '';
            $panen      = $_POST['tanggal_panen'] ?? '';
            $hasilPanen = parseAngka($_POST['hasil_panen'] ?? $existing['hasil_panen']);
            $status     = in_array($_POST['status'] ?? '', ['Aktif', 'Selesai']) ? $_POST['status'] : $existing['status'];

            if ($nama === '' || $mulai === '' || $panen === '') {
                echo json_encode(['status' => 'error', 'message' => 'Nama musim, tanggal mulai, dan tanggal panen wajib diisi.']);
                exit;
            }

            $ok = $musim->update($id, [
                'nama_musim'    => $nama,
                'luas_lahan'    => $luas,
                'tanggal_mulai' => $mulai,
                'tanggal_panen' => $panen,
                'hasil_panen'   => $hasilPanen,
            ]);

            // Kalau status berubah jadi 'Selesai', updateStatus() dipanggil SETELAH
            // hasil_panen tersimpan, supaya trigger trg_tambah_stok memakai nilai terbaru.
            if ($ok && $status === 'Selesai' && $existing['status'] === 'Aktif') {
                $musim->updateStatus($id, 'Selesai');
            }

            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Musim tanam berhasil diperbarui.' : 'Gagal memperbarui musim tanam.']);
            break;

        // Tombol cepat "Tandai Selesai" di tabel (tanpa buka modal edit)
        case 'selesai':
            $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
            $existing = $musim->getById($id);
            if (!$existing) {
                echo json_encode(['status' => 'error', 'message' => 'Data musim tanam tidak ditemukan.']);
                exit;
            }
            $ok = $musim->updateStatus($id, 'Selesai');
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Musim tanam ditandai selesai. Stok gudang otomatis bertambah.' : 'Gagal menandai selesai.']);
            break;

        case 'delete':
            $id = (int) ($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['status' => 'error', 'message' => 'ID musim tidak valid.']);
                exit;
            }
            $ok = $musim->delete($id);
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Musim tanam berhasil dihapus.' : 'Gagal menghapus musim tanam.']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}