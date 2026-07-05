<?php
// File: pages/catat_biaya.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Biaya.php';
require_once __DIR__ . '/../classes/MusimTanam.php';

$auth = new Auth();
$auth->requireLogin();
$currentUser = $auth->getUser();
$pageTitle = 'Catat Biaya';

$biayaModel = new Biaya();
$musimModel = new MusimTanam();

$kategoriList = ['Pengolahan Tanah', 'Benih', 'Pupuk', 'Pestisida', 'Tenaga Kerja', 'Panen', 'Lain-lain'];

$kategoriFilter = $_GET['kategori'] ?? '';
$bulan          = $_GET['bulan'] ?? date('Y-m');
$semua          = isset($_GET['semua']);

$daftarBiaya = $biayaModel->getAll(null, $semua ? null : $bulan);
if ($kategoriFilter !== '') {
    $daftarBiaya = array_values(array_filter($daftarBiaya, function ($b) use ($kategoriFilter) {
        return $b['kategori'] === $kategoriFilter;
    }));
}

$totalBiayaBulanIni = $biayaModel->getTotalBiayaBulanIni();
$biayaPupuk         = $biayaModel->getTotalByKategori('Pupuk', date('Y-m'));
$biayaTenagaKerja   = $biayaModel->getTotalByKategori('Tenaga Kerja', date('Y-m'));

$daftarMusim = $musimModel->getAll();
$jsonFlags   = JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Catat Biaya</h3>
            <p class="text-muted mb-0">Kelola pengeluaran per musim tanam.</p>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-danger bg-gradient text-white">
                        <span class="info-box-icon"><i class="bi bi-wallet2"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Biaya (Bulan Ini)</span>
                            <span class="info-box-number"><?= formatRupiah($totalBiayaBulanIni) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-success bg-gradient text-white">
                        <span class="info-box-icon"><i class="bi bi-flower2"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Biaya Pupuk</span>
                            <span class="info-box-number"><?= formatRupiah($biayaPupuk) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-secondary bg-gradient text-white">
                        <span class="info-box-icon"><i class="bi bi-people"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Biaya Tenaga Kerja</span>
                            <span class="info-box-number"><?= formatRupiah($biayaTenagaKerja) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="get" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" class="form-select">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($kategoriList as $k): ?>
                                    <option value="<?= $k ?>" <?= $kategoriFilter === $k ? 'selected' : '' ?>><?= $k ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bulan</label>
                            <input type="month" name="bulan" class="form-control" value="<?= htmlspecialchars($bulan) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-success w-100"><i class="bi bi-funnel"></i> Filter</button>
                        </div>
                        <div class="col-md-3 text-md-end">
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalBiaya" onclick="bukaTambahBiaya()">
                                <i class="bi bi-plus-lg"></i> Tambah
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Daftar Biaya</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                        <tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th>Jumlah</th><th style="width:140px;">Keterangan</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($daftarBiaya)): ?>
                            <tr><td colspan="5" class="text-center text-muted">Tidak ada data biaya.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftarBiaya as $b): ?>
                                <tr>
                                    <td><?= formatTanggalIndo($b['tanggal']) ?></td>
                                    <td><span class="badge bg-secondary"><?= $b['kategori'] ?></span></td>
                                    <td><?= $b['deskripsi'] ?></td>
                                    <td><?= formatRupiah($b['nominal']) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalBiaya"
                                                onclick='bukaEditBiaya(<?= json_encode($b, $jsonFlags) ?>)'>Edit</button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="hapusBiaya(<?= (int) $b['id_biaya'] ?>)">Hapus</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if (!$semua): ?>
                        <a href="?semua=1" class="btn btn-outline-success btn-sm">Lihat Semua</a>
                    <?php else: ?>
                        <a href="catat_biaya.php" class="btn btn-outline-secondary btn-sm">Kembali ke Filter Bulan Ini</a>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</main>

<div class="modal fade" id="modalBiaya" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formBiaya">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalBiayaTitle">Tambah Biaya</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="biaya_id">
                    <div class="mb-3">
                        <label class="form-label">Musim Tanam</label>
                        <select name="id_musim" id="biaya_musim" class="form-select" required>
                            <option value="">-- Pilih Musim Tanam --</option>
                            <?php foreach ($daftarMusim as $m): ?>
                                <option value="<?= $m['id_musim'] ?>"><?= $m['nama_musim'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" id="biaya_tanggal" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" id="biaya_kategori" class="form-select" required>
                                <?php foreach ($kategoriList as $k): ?><option value="<?= $k ?>"><?= $k ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" id="biaya_deskripsi" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nominal (Rp)</label>
                        <input type="number" step="1" min="0" name="nominal" id="biaya_nominal" class="form-control" required>
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

function bukaTambahBiaya() {
    document.getElementById('formBiaya').reset();
    document.getElementById('modalBiayaTitle').innerText = 'Tambah Biaya';
    document.getElementById('biaya_id').value = '';
}

function bukaEditBiaya(b) {
    document.getElementById('modalBiayaTitle').innerText = 'Edit Biaya';
    document.getElementById('biaya_id').value = b.id_biaya;
    document.getElementById('biaya_musim').value = b.id_musim;
    document.getElementById('biaya_tanggal').value = b.tanggal;
    document.getElementById('biaya_kategori').value = b.kategori;
    document.getElementById('biaya_deskripsi').value = b.deskripsi;
    document.getElementById('biaya_nominal').value = b.nominal;
}

document.getElementById('formBiaya').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('biaya_id').value;
    const act = id ? 'update' : 'create';
    const url = BASE_URL + 'actions/biaya_action.php?act=' + act + (id ? '&id=' + id : '');
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

function hapusBiaya(id) {
    Swal.fire({
        title: 'Hapus data biaya ini?', icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Ya, Hapus', confirmButtonColor: '#dc3545', cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(BASE_URL + 'actions/biaya_action.php?act=delete&id=' + id, { method: 'POST' })
                .then(res => res.json())
                .then(data => Swal.fire(data.status === 'success' ? 'Berhasil' : 'Gagal', data.message, data.status).then(() => data.status === 'success' && location.reload()));
        }
    });
}
</script>