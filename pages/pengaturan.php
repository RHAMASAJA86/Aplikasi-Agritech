<?php
// File: pages/pengaturan.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();
$currentUser = $auth->getUser();
$pageTitle = 'Pengaturan';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Pengaturan</h3>
            <p class="text-muted mb-0">Ubah nama dan password kamu.</p>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Ubah Nama</h5></div>
                        <div class="card-body">
                            <form id="formNama">
                                <!--<div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($currentUser['username']) ?>" disabled>
                                </div>-->
                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" id="inputUsername" class="form-control"
                                        value="<?= htmlspecialchars($currentUser['username']) ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($currentUser['nama']) ?>" required>
                                </div>
                                <button type="submit" class="btn btn-success">Simpan Nama</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Ubah Password</h5></div>
                        <div class="card-body">
                            <form id="formPassword">
                                <div class="mb-3">
                                    <label class="form-label">Password Lama</label>
                                    <input type="password" name="password_lama" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" name="password_baru" class="form-control" required minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" name="password_konfirmasi" class="form-control" required minlength="6">
                                </div>
                                <button type="submit" class="btn btn-success">Simpan Password</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mt-4">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Pertanyaan Keamanan</h5></div>
                        <div class="card-body">
                            <p class="text-muted small">Dipakai untuk reset password lewat halaman "Lupa Password" kalau suatu saat lupa.</p>
                            <form id="formKeamanan">
                                <div class="mb-3">
                                    <label class="form-label">Pilih Pertanyaan</label>
                                    <select name="pertanyaan_keamanan" class="form-select" required>
                                        <option value="">-- Pilih Pertanyaan --</option>
                                        <option value="Nama hewan peliharaan pertama Anda?">Nama hewan peliharaan pertama Anda?</option>
                                        <option value="Kota kelahiran Anda?">Kota kelahiran Anda?</option>
                                        <option value="Nama sekolah dasar Anda?">Nama sekolah dasar Anda?</option>
                                        <option value="Makanan favorit Anda?">Makanan favorit Anda?</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jawaban</label>
                                    <input type="text" name="jawaban_keamanan" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-success">Simpan Pertanyaan Keamanan</button>
                            </form>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
const BASE_URL = '<?= BASE_URL ?>';

document.getElementById('formNama').addEventListener('submit', function (e) {
    e.preventDefault();
    fetch(BASE_URL + 'actions/pengaturan_action.php?act=update_nama', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan koneksi.', 'error'));
});

document.getElementById('formPassword').addEventListener('submit', function (e) {
    e.preventDefault();
    const form = this;
    const baru = form.password_baru.value;
    const konfirmasi = form.password_konfirmasi.value;

    if (baru !== konfirmasi) {
        Swal.fire('Gagal', 'Konfirmasi password baru tidak cocok.', 'error');
        return;
    }

    fetch(BASE_URL + 'actions/pengaturan_action.php?act=update_password', { method: 'POST', body: new FormData(form) })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire('Berhasil', data.message, 'success').then(() => form.reset());
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan koneksi.', 'error'));
});

document.getElementById('formKeamanan').addEventListener('submit', function (e) {
    e.preventDefault();
    fetch(BASE_URL + 'actions/pengaturan_action.php?act=update_keamanan', { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire('Berhasil', data.message, 'success');
            } else {
                Swal.fire('Gagal', data.message, 'error');
            }
        })
        .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan koneksi.', 'error'));
});
</script>