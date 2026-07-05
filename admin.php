<?php
require_once __DIR__ . '/config/Database.php';
$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
$stmt->execute([
    'Administrator',
    'admin',
    password_hash('admin', PASSWORD_DEFAULT),
    'admin'
]);