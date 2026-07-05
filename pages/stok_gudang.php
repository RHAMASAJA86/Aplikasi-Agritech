<?php
// File: pages/stok_gudang.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/StokGudang.php';
require_once __DIR__ . '/../classes/MusimTanam.php';

$auth = new Auth();
$auth->requireLogin();
$currentUser = $auth->getUser();
$pageTitle = 'Stok Gudang';

$stokModel  = new StokGudang();
$musimModel = new MusimTanam();

$daftarStok  = $stokModel->getAll();
$stokSaatIni = $stokModel->getStokSaatIni();
$daftarMusim = $musimModel->getAll();
$jsonFlags   = JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-end gap-2">
            <div>
                <h3 class="mb-0">Stok Gudang</h3>
                <p class="text-muted mb-0">Kelola stok hasil panen dan bahan produksi di gudang.</p>
            </div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalStok" onclick="bukaTambahStok()">
                <i class="bi bi-plus-lg"></i> Tambah
            </button>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="bi bi-info-circle fs-5"></i>
                <div>Total stok saat ini: <strong><?= number_format($stokSaatIni, 2, ',', '.') ?> kg</strong>
                    <br><small>Stok akan otomatis berkurang saat ada transaksi penjualan (ditangani trigger database).</small>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Daftar Stok Gudang</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                        <tr><th>No</th><th>Musim Tanam</th><th>Jumlah Stok</th><th style="width:140px;">Keterangan</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($daftarStok)): ?>
                            <tr><td colspan="4" class="text-center text-muted">Belum ada data stok gudang.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftarStok as $i => $s): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><?= $s['nama_musim'] ?></td>
                                    <td><?= number_format($s['jumlah_stok'], 2, ',', '.') ?> kg</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalStok"
                                                onclick='bukaEditStok(<?= json_encode($s, $jsonFlags) ?>)'>Edit</button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="hapusStok(<?= (int) $s['id_stok'] ?>)">Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</main>

<div class="modal fade" id="modalStok" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formStok">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalStokTitle">Tambah Stok</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="stok_id">
                    <div class="mb-3">
                        <label class="form-label">Musim Tanam</label>
                        <select name="id_musim" id="stok_musim" class="form-select" required>
                            <option value="">-- Pilih Musim Tanam --</option>
                            <?php foreach ($daftarMusim as $m): ?>
                                <option value="<?= $m['id_musim'] ?>"><?= $m['nama_musim'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Stok (kg)</label>
                        <input type="number" step="0.01" min="0" name="jumlah_stok" id="stok_jumlah" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
const BASE_URL = '<?= BASE_URL ?>';

function bukaTambahStok() {
    document.getElementById('formStok').reset();
    document.getElementById('modalStokTitle').innerText = 'Tambah Stok';
    document.getElementById('stok_id').value = '';
}

function bukaEditStok(s) {
    document.getElementById('modalStokTitle').innerText = 'Edit Stok';
    document.getElementById('stok_id').value = s.id_stok;
    document.getElementById('stok_musim').value = s.id_musim;
    document.getElementById('stok_jumlah').value = s.jumlah_stok;
}

document.getElementById('formStok').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('stok_id').value;
    const act = id ? 'update' : 'create';
    const url = BASE_URL + 'actions/stok_action.php?act=' + act + (id ? '&id=' + id : '');
    fetch(url, { method: 'POST', body: new FormData(this) })
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

function hapusStok(id) {
    Swal.fire({
        title: 'Hapus data stok ini?', icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Ya, Hapus', confirmButtonColor: '#dc3545', cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(BASE_URL + 'actions/stok_action.php?act=delete&id=' + id, { method: 'POST' })
                .then(res => res.json())
                .then(data => Swal.fire(data.status === 'success' ? 'Berhasil' : 'Gagal', data.message, data.status).then(() => data.status === 'success' && location.reload()));
        }
    });
}
</script>