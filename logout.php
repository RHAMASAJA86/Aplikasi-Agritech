<?php
// File: logout.php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$auth->logout();

header('Location: ' . BASE_URL . 'login.php?logout');
exit;