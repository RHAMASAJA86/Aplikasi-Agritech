<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();
$auth->logout();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anda Telah Keluar | Agritech</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/adminlte/dist/css/adminlte.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="d-flex align-items-center justify-content-center bg-body-tertiary" style="min-height:100vh; margin:0;">
    <div class="text-center p-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size:4rem;"></i>
        <h3 class="fw-bold mt-3">Anda telah keluar</h3>
        <p class="text-muted">Terima kasih telah menggunakan aplikasi ini.</p>
        <a href="<?= BASE_URL ?>login.php" class="btn btn-success px-4">Masuk</a>
    </div>
</body>
</html>