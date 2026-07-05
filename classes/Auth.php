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
}