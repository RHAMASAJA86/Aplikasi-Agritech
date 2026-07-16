<?php
session_start();
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/classes/Auth.php';

$auth = new Auth();

// Jika sudah login, langsung lempar ke dashboard
if ($auth->isLoggedIn()) {
    header('Location: ' . BASE_URL . 'pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        if ($auth->login($username, $password)) {
            header('Location: ' . BASE_URL . 'pages/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    } else {
        $error = 'Semua kolom wajib diisi.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Agritech</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/css/adminlte.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        .login-wrapper {
            height: 100vh;
            display: flex;
        }
        /* Sisi Kiri: Gambar Latar Belakang */
        .login-bg {
            flex: 1;
            background-image: url('assets/img/bgLogin.png');
            background-size: cover;
            background-position: center;
            position: relative;
        }
        /* Susunan tombol mata pas di dalam input-group */
        .input-group .btn-outline-secondary {
            border-color: #ced4da;
            background-color: #f8f9fa;
        }
        .input-group .btn-outline-secondary:hover {
            background-color: #e9ecef;
        }
        /* Overlay gradasi tipis di atas gambar agar lebih estetik */
        .login-bg::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.3));
        }
        /* Sisi Kanan: Area Form Login */
        .login-form-area {
            width: 450px;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 3rem;
            box-shadow: -5px 0 25px rgba(0, 0, 0, 0.05);
        }
        .btn-success {
            background-color: #198754;
            border-color: #198754;
            padding: 0.6rem;
            font-weight: 600;
        }
        .btn-success:hover {
            background-color: #157347;
            border-color: #146c43;
        }
        .form-control {
            padding: 0.6rem 0.75rem;
        }
        /* Responsif untuk layar HP/Tablet kecil */
        @media (max-width: 768px) {
            .login-bg {
                display: none;
            }
            .login-form-area {
                width: 100%;
                padding: 2rem;
            }
        }
    </style>
</head>
<body class="bg-light">

<div class="login-wrapper">
    <!-- Kolom Kiri: Foto Sesuai Wireframe -->
    <div class="login-bg d-none d-md-block"></div>

    <!-- Kolom Kanan: Konten Form -->
    <div class="login-form-area">
        <!-- Bagian Logo -->
        <div class="text-center mb-4">
            <img src="<?= BASE_URL ?>assets/img/iconAgritech.png" alt="Logo Agritech" style="height: 60px;" class="mt-2">
        </div>


        <!-- Notifikasi Error jika login gagal -->
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form action="" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i>
                        <img src="<?= BASE_URL ?>assets/img/iconUser.png" alt="User" style="width: 18px; height: 18px; opacity: 0.6;">
                    </span>
                    <input type="text" name="username" id="username" class="form-control bg-light border-start-0" placeholder="Masukkan username" required autocomplete="username">
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i>
                        <img src="<?= BASE_URL ?>assets/img/iconLock.png" alt="User" style="width: 18px; height: 18px; opacity: 0.6;">
                    </span>
                    <input type="password" name="password" id="password" class="form-control bg-light border-start-0 border-end-0" placeholder="Masukkan password" required autocomplete="current-password">
                    <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword">
                        <img src="<?= BASE_URL ?>assets/img/iconSilang.png" id="eyeIcon" alt="Toggle Password" style="width: 18px; height: 18px; opacity: 0.6;">
                    </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <a href="<?= BASE_URL ?>lupa_password.php" class="text-success text-decoration-none small fw-semibold">Lupa password?</a>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-success btn-block rounded shadow-sm text-uppercase">LOGIN</button>
            </div>
        </form>
    </div>
</div>

<script>
    // toggle
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const eyeIcon = document.querySelector('#eyeIcon');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        
        // Ganti source gambarnya
        if (type === 'password') {
            eyeIcon.src = '<?= BASE_URL ?>assets/img/iconSilang.png';
        } else {
            eyeIcon.src = '<?= BASE_URL ?>assets/img/iconEye.png';
        }
    });
</script>
</body>
</html>