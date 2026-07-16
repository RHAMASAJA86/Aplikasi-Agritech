<?php
require_once __DIR__ . '/../config/Database.php';

class Auth
{
    private $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($username, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id_user'  => $user['id_user'],
                'nama'     => $user['nama'],
                'username' => $user['username'],
                'role'     => $user['role'],
            ];
            return true;
        }
        return false;
    }

    public function logout()
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user']);
    }

    public function getUser()
    {
        return $_SESSION['user'] ?? [];
    }

    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            header('Location: ' . BASE_URL . 'login.php');
            exit;
        }
    }

    public function requireRole($role)
    {
        $this->requireLogin();
        if (($_SESSION['user']['role'] ?? '') !== $role) {
            header('Location: ' . BASE_URL . 'pages/dashboard.php');
            exit;
        }
    }

    //method baru untuk profil
    public function getUserById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id_user = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: [];
    }

    public function updateNama($id, $nama)
    {
        $stmt = $this->db->prepare("UPDATE users SET nama = ? WHERE id_user = ?");
        $ok = $stmt->execute([$nama, $id]);
        if ($ok && isset($_SESSION['user']) && $_SESSION['user']['id_user'] == $id) {
            $_SESSION['user']['nama'] = $nama; // biar navbar langsung update tanpa perlu login ulang
        }
        return $ok;
    }

    public function updatePassword($id, $passwordLama, $passwordBaru)
    {
        $user = $this->getUserById($id);
        if (!$user || !password_verify($passwordLama, $user['password'])) {
            throw new Exception('Password lama tidak sesuai.');
        }
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id_user = ?");
        return $stmt->execute([password_hash($passwordBaru, PASSWORD_DEFAULT), $id]);
    }

    // Method update nama pada pengaturan
    public function updateUsername($id, $usernameBaru)
    {
        // Cek dulu apakah username baru sudah dipakai user lain
        $stmt = $this->db->prepare("SELECT id_user FROM users WHERE username = ? AND id_user != ?");
        $stmt->execute([$usernameBaru, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Username sudah dipakai, silakan pilih username lain.');
        }

        $stmt = $this->db->prepare("UPDATE users SET username = ? WHERE id_user = ?");
        $ok = $stmt->execute([$usernameBaru, $id]);
        if ($ok && isset($_SESSION['user']) && $_SESSION['user']['id_user'] == $id) {
            $_SESSION['user']['username'] = $usernameBaru; // biar langsung update tanpa perlu login ulang
        }
        return $ok;
    }

    // Method untuk lupa pasword
    public function getPertanyaanKeamanan($username)
    {
        $stmt = $this->db->prepare("SELECT pertanyaan_keamanan FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        if (!$row || empty($row['pertanyaan_keamanan'])) {
            return null;
        }
        return $row['pertanyaan_keamanan'];
    }

    public function resetPasswordViaKeamanan($username, $jawaban, $passwordBaru)
    {
        $stmt = $this->db->prepare("SELECT id_user, jawaban_keamanan FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user || empty($user['jawaban_keamanan'])) {
            throw new Exception('Username tidak ditemukan atau pertanyaan keamanan belum diatur.');
        }

        $jawabanNormalized = strtolower(trim($jawaban));
        if (!password_verify($jawabanNormalized, $user['jawaban_keamanan'])) {
            throw new Exception('Jawaban keamanan salah.');
        }

        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id_user = ?");
        return $stmt->execute([password_hash($passwordBaru, PASSWORD_DEFAULT), $user['id_user']]);
    }

    public function updatePertanyaanKeamanan($id, $pertanyaan, $jawaban)
    {
        $stmt = $this->db->prepare("UPDATE users SET pertanyaan_keamanan = ?, jawaban_keamanan = ? WHERE id_user = ?");
        return $stmt->execute([$pertanyaan, password_hash(strtolower(trim($jawaban)), PASSWORD_DEFAULT), $id]);
    }
}