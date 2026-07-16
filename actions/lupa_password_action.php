<?php
// File: actions/lupa_password_action.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
$act = $_GET['act'] ?? '';

try {
    switch ($act) {
        case 'get_pertanyaan':
            $username = trim($_POST['username'] ?? '');
            if ($username === '') {
                echo json_encode(['status' => 'error', 'message' => 'Username wajib diisi.']);
                exit;
            }
            $pertanyaan = $auth->getPertanyaanKeamanan($username);
            if (!$pertanyaan) {
                echo json_encode(['status' => 'error', 'message' => 'Username tidak ditemukan atau belum mengatur pertanyaan keamanan.']);
                exit;
            }
            echo json_encode(['status' => 'success', 'pertanyaan' => $pertanyaan]);
            break;

        case 'reset':
            $username = trim($_POST['username'] ?? '');
            $jawaban  = $_POST['jawaban'] ?? '';
            $baru     = $_POST['password_baru'] ?? '';

            if ($username === '' || $jawaban === '' || $baru === '') {
                echo json_encode(['status' => 'error', 'message' => 'Semua field wajib diisi.']);
                exit;
            }
            if (strlen($baru) < 6) {
                echo json_encode(['status' => 'error', 'message' => 'Password baru minimal 6 karakter.']);
                exit;
            }

            $auth->resetPasswordViaKeamanan($username, $jawaban, $baru);
            echo json_encode(['status' => 'success', 'message' => 'Password berhasil direset. Silakan login dengan password baru.']);
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali.']);
    }
} catch (Throwable $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}