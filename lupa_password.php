<?php
// File: lupa_password.php
require_once __DIR__ . '/config/Database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password | Agritech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
</head>
<body class="d-flex align-items-center justify-content-center bg-body-tertiary" style="min-height:100vh; margin:0;">
<div class="card shadow" style="max-width:420px; width:100%;">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <i class="bi bi-shield-lock text-success" style="font-size:2.5rem;"></i>
            <h4 class="fw-bold mt-2">Lupa Password</h4>
            <p class="text-muted mb-0">Jawab pertanyaan keamanan untuk reset password.</p>
        </div>

        <form id="formUsername">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>
            <button type="submit" class="btn btn-success w-100">Lanjutkan</button>
        </form>

        <form id="formReset" class="d-none">
            <div class="mb-3">
                <label class="form-label">Pertanyaan Keamanan</label>
                <input type="text" id="teksPertanyaan" class="form-control" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label">Jawaban</label>
                <input type="text" name="jawaban" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password Baru</label>
                <input type="password" name="password_baru" class="form-control" required minlength="6">
            </div>
            <button type="submit" class="btn btn-success w-100">Reset Password</button>
        </form>

        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>login.php" class="text-decoration-none"> Kembali ke Login</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const BASE_URL = '<?= BASE_URL ?>';
let usernameTersimpan = '';

document.getElementById('formUsername').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    usernameTersimpan = formData.get('username');

    fetch(BASE_URL + 'actions/lupa_password_action.php?act=get_pertanyaan', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('teksPertanyaan').value = data.pertanyaan;
                document.getElementById('formUsername').classList.add('d-none');
                document.getElementById('formReset').classList.remove('d-none');
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan koneksi.', 'error'));
});

document.getElementById('formReset').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('username', usernameTersimpan);

    fetch(BASE_URL + 'actions/lupa_password_action.php?act=reset', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire('Berhasil', data.message, 'success').then(() => {
                    window.location.href = BASE_URL + 'login.php';
                });
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan koneksi.', 'error'));
});
</script>
</body>
</html>