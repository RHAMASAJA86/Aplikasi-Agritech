<?php
// File: actions/profil_action.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi berakhir, silakan login kembali.']);
    exit;
}

$currentUser = $auth->getUser();
$act = $_GET['act'] ?? '';

try {
    switch ($act) {
        case 'update_nama':
        $nama     = trim(htmlspecialchars($_POST['nama'] ?? ''));
        $username = trim(htmlspecialchars($_POST['username'] ?? ''));

        if ($nama === '' || $username === '') {
            echo json_encode(['status' => 'error', 'message' => 'Nama dan username tidak boleh kosong.']);
            exit;
        }

        $auth->updateNama($currentUser['id_user'], $nama);
        $auth->updateUsername($currentUser['id_user'], $username); // akan lempar Exception kalau username sudah dipakai

        echo json_encode(['status' => 'success', 'message' => 'Nama dan username berhasil diperbarui.']);
        break;

        case 'update_password':
            $lama = $_POST['password_lama'] ?? '';
            $baru = $_POST['password_baru'] ?? '';

            if ($lama === '' || $baru === '') {
                echo json_encode(['status' => 'error', 'message' => 'Semua field password wajib diisi.']);
                exit;
            }
            if (strlen($baru) < 6) {
                echo json_encode(['status' => 'error', 'message' => 'Password baru minimal 6 karakter.']);
                exit;
            }

            $auth->updatePassword($currentUser['id_user'], $lama, $baru);
            echo json_encode(['status' => 'success', 'message' => 'Password berhasil diperbarui.']);
            break;
            
        case 'update_keamanan':
            $pertanyaan = trim($_POST['pertanyaan_keamanan'] ?? '');
            $jawaban    = trim($_POST['jawaban_keamanan'] ?? '');

            if ($pertanyaan === '' || $jawaban === '') {
                echo json_encode(['status' => 'error', 'message' => 'Pertanyaan dan jawaban keamanan wajib diisi.']);
                exit;
            }

            $ok = $auth->updatePertanyaanKeamanan($currentUser['id_user'], $pertanyaan, $jawaban);
            echo json_encode(['status' => $ok ? 'success' : 'error', 'message' => $ok ? 'Pertanyaan keamanan berhasil disimpan.' : 'Gagal menyimpan pertanyaan keamanan.']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
    }
} catch (Exception $e) {
    // Termasuk pesan "Password lama tidak sesuai." dari Auth::updatePassword()
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}