<?php
// File: pages/profil.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';

$auth = new Auth();
$auth->requireLogin();
$currentUser = $auth->getUser();
$pageTitle = 'Profil';

$fotoPath    = __DIR__ . '/../assets/img/profile.png';
$fotoVersion = file_exists($fotoPath) ? filemtime($fotoPath) : time();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Profil</h3>
            <p class="text-muted mb-0">Ubah foto profil aplikasi.</p>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Foto Profil</h5></div>
                        <div class="card-body text-center">
                            <img id="previewFoto" src="<?= BASE_URL ?>assets/img/profile.png?v=<?= $fotoVersion ?>"
                                alt="Foto Profil" class="rounded-circle mb-3"
                                style="width:140px; height:140px; object-fit:cover; border:3px solid #198754;">
                            <form id="formFoto" enctype="multipart/form-data">
                                <div class="mb-3 text-start">
                                    <label class="form-label">Pilih Foto Baru</label>
                                    <input type="file" name="foto" id="inputFoto" class="form-control"
                                           accept="image/png, image/jpeg, image/jpg" required>
                                    <small class="text-muted">Format JPG/PNG, maksimal 2MB.</small>
                                </div>
                                <button type="submit" class="btn btn-success">Simpan Foto</button>
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

document.getElementById('inputFoto').addEventListener('change', function () {
    if (this.files && this.files[0]) {
        document.getElementById('previewFoto').src = URL.createObjectURL(this.files[0]);
    }
});

document.getElementById('formFoto').addEventListener('submit', function (e) {
    e.preventDefault();
    fetch(BASE_URL + 'actions/profil_action.php?act=upload_foto', { method: 'POST', body: new FormData(this) })
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
</script>