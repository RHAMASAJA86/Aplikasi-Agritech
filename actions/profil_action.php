<?php
// File: actions/profil_action.php
ini_set('display_errors', 1);// ini baru
error_reporting(E_ALL);// ini baru
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi berakhir, silakan login kembali.']);
    exit;
}

//$act = $_GET['act'] ?? '';
$act = $_GET['act'] ?? '';

if (!extension_loaded('gd')) {
    echo json_encode(['status' => 'error', 'message' => 'Ekstensi GD belum aktif di server. Aktifkan dulu di php.ini.']);
    exit;
}

try {
    switch ($act) {
        case 'upload_foto':
            if (empty($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['status' => 'error', 'message' => 'File foto tidak ditemukan atau gagal diupload.']);
                exit;
            }

            $file     = $_FILES['foto'];
            $maxSize  = 2 * 1024 * 1024; // 2MB

            if ($file['size'] > $maxSize) {
                echo json_encode(['status' => 'error', 'message' => 'Ukuran file maksimal 2MB.']);
                exit;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            // Konversi jadi PNG asli, supaya nama file tetap "profile.png" (dipakai sama untuk semua user)
            switch ($mime) {
                case 'image/jpeg':
                case 'image/jpg':
                    $image = @imagecreatefromjpeg($file['tmp_name']);
                    break;
                case 'image/png':
                    $image = @imagecreatefrompng($file['tmp_name']);
                    break;
                default:
                    $image = false;
            }

            if (!$image) {
                echo json_encode(['status' => 'error', 'message' => 'Format file harus JPG atau PNG.']);
                exit;
            }

            $targetPath = __DIR__ . '/../assets/img/profile.png';
            $ok = imagepng($image, $targetPath);
            imagedestroy($image);

            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Foto profil berhasil diperbarui.' : 'Gagal menyimpan foto ke server.']);
            break;

            default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
    }
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}