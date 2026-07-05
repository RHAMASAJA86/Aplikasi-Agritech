<?php
// File: index.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();

if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
} else {
    header('Location: ' . BASE_URL . 'login.php');
}
exit;