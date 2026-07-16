<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/Aplikasi-Agritech/');
}

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        try {
            $this->pdo = new PDO(
                'mysql:host=localhost;dbname=agritech;charset=utf8mb4',
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            die('Koneksi database gagal: ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton Database');
    }
}