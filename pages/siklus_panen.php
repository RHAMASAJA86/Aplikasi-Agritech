<?php
// File: pages/siklus_panen.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/MusimTanam.php';

$auth = new Auth();
$auth->requireLogin();
$currentUser = $auth->getUser();
$pageTitle = 'Siklus Panen';

$musimModel  = new MusimTanam();
$daftarMusim = $musimModel->getAll();

// Ambil ringkasan (v_laporan) sekali saja, index by id_musim, biar tidak query berulang per baris
$db = Database::getInstance()->getConnection();
$ringkasanPerMusim = [];
foreach ($db->query("SELECT * FROM v_laporan")->fetchAll() as $r) {
    $ringkasanPerMusim[$r['id_musim']] = $r;
}

$jsonFlags = JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid d-flex flex-wrap justify-content-between align-items-end gap-2">
            <div>
                <h3 class="mb-0">Siklus Panen</h3>
                <p class="text-muted mb-0">Kelola penanaman, penggunaan bahan, hingga panen.</p>
            </div>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalMusim" onclick="bukaTambahMusim()">
                <i class="bi bi-plus-lg"></i> Tambah
            </button>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Daftar Riwayat Musim Tanam</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Musim Tanam</th>
                            <th>Luas Lahan</th>
                            <th>Tanggal Panen</th>
                            <th>Hasil Panen</th>
                            <th style="width:230px;">Keterangan</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($daftarMusim)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Belum ada data musim tanam.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftarMusim as $i => $m): ?>
                                <?php $r = $ringkasanPerMusim[$m['id_musim']] ?? ['total_pengeluaran' => 0, 'total_pendapatan' => 0, 'keuntungan' => 0]; ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <?= $m['nama_musim'] ?><br>
                                        <span class="badge <?= $m['status'] === 'Aktif' ? 'bg-success' : 'bg-secondary' ?>"><?= $m['status'] ?></span>
                                    </td>
                                    <td><?= number_format($m['luas_lahan'], 2, ',', '.') ?> Ha</td>
                                    <td><?= formatTanggalIndo($m['tanggal_panen'], 'd-m-Y') ?></td>
                                    <td><?= number_format($m['hasil_panen'], 2, ',', '.') ?> kg</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info text-white"
                                                data-bs-toggle="modal" data-bs-target="#modalDetail"
                                                onclick='bukaDetail(<?= json_encode($m, $jsonFlags) ?>, <?= json_encode($r, $jsonFlags) ?>)'>Detail</button>
                                        <button type="button" class="btn btn-sm btn-warning"
                                                data-bs-toggle="modal" data-bs-target="#modalMusim"
                                                onclick='bukaEditMusim(<?= json_encode($m, $jsonFlags) ?>)'>Edit</button>
                                        <?php if ($m['status'] === 'Aktif'): ?>
                                            <button type="button" class="btn btn-sm btn-success" onclick="tandaiSelesai(<?= (int) $m['id_musim'] ?>)">Tandai Selesai</button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="hapusMusim(<?= (int) $m['id_musim'] ?>)"><i class="bi bi-trash"></i></button>
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

<div class="modal fade" id="modalMusim" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formMusim">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMusimTitle">Tambah Musim Tanam</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="musim_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Musim</label>
                        <input type="text" name="nama_musim" id="musim_nama" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Luas Lahan (Ha)</label>
                            <input type="number" step="0.01" min="0" name="luas_lahan" id="musim_luas" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="musim_status" class="form-select">
                                <option value="Aktif">Aktif</option>
                                <option value="Selesai">Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="musim_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Panen</label>
                            <input type="date" name="tanggal_panen" id="musim_panen" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hasil Panen (kg)</label>
                        <input type="number" step="0.01" min="0" name="hasil_panen" id="musim_hasil" class="form-control">
                        <small class="text-muted">Isi saat musim tanam sudah dipanen / ditandai Selesai.</small>
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

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Musim Tanam</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
const BASE_URL = '<?= BASE_URL ?>';

function bukaTambahMusim() {
    document.getElementById('formMusim').reset();
    document.getElementById('modalMusimTitle').innerText = 'Tambah Musim Tanam';
    document.getElementById('musim_id').value = '';
}

function bukaEditMusim(m) {
    document.getElementById('modalMusimTitle').innerText = 'Edit Musim Tanam';
    document.getElementById('musim_id').value = m.id_musim;
    document.getElementById('musim_nama').value = m.nama_musim;
    document.getElementById('musim_luas').value = m.luas_lahan;
    document.getElementById('musim_mulai').value = m.tanggal_mulai;
    document.getElementById('musim_panen').value = m.tanggal_panen;
    document.getElementById('musim_status').value = m.status;
    document.getElementById('musim_hasil').value = m.hasil_panen;
}

function bukaDetail(m, r) {
    document.getElementById('detailBody').innerHTML = `
        <table class="table table-borderless mb-0">
            <tr><th>Nama Musim</th><td>: ${m.nama_musim}</td></tr>
            <tr><th>Luas Lahan</th><td>: ${m.luas_lahan} Ha</td></tr>
            <tr><th>Tanggal Mulai</th><td>: ${m.tanggal_mulai}</td></tr>
            <tr><th>Tanggal Panen</th><td>: ${m.tanggal_panen}</td></tr>
            <tr><th>Hasil Panen</th><td>: ${m.hasil_panen} kg</td></tr>
            <tr><th>Status</th><td>: ${m.status}</td></tr>
            <tr><td colspan="2"><hr></td></tr>
            <tr><th>Total Pengeluaran</th><td>: Rp. ${Number(r.total_pengeluaran).toLocaleString('id-ID')}</td></tr>
            <tr><th>Total Pendapatan</th><td>: Rp. ${Number(r.total_pendapatan).toLocaleString('id-ID')}</td></tr>
            <tr><th>Keuntungan</th><td>: Rp. ${Number(r.keuntungan).toLocaleString('id-ID')}</td></tr>
        </table>`;
}

document.getElementById('formMusim').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('musim_id').value;
    const act = id ? 'update' : 'create';
    const url = BASE_URL + 'actions/musim_action.php?act=' + act + (id ? '&id=' + id : '');
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

function tandaiSelesai(id) {
    Swal.fire({
        title: 'Tandai musim ini selesai?',
        text: 'Stok gudang akan otomatis bertambah sesuai hasil panen.',
        icon: 'question', showCancelButton: true,
        confirmButtonText: 'Ya, Selesai', cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(BASE_URL + 'actions/musim_action.php?act=selesai&id=' + id, { method: 'POST' })
                .then(res => res.json())
                .then(data => Swal.fire(data.status === 'success' ? 'Berhasil' : 'Gagal', data.message, data.status).then(() => data.status === 'success' && location.reload()));
        }
    });
}

function hapusMusim(id) {
    Swal.fire({
        title: 'Hapus data musim tanam ini?',
        text: 'Data biaya & stok terkait ikut terhapus.',
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Ya, Hapus', confirmButtonColor: '#dc3545', cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(BASE_URL + 'actions/musim_action.php?act=delete&id=' + id, { method: 'POST' })
                .then(res => res.json())
                .then(data => Swal.fire(data.status === 'success' ? 'Berhasil' : 'Gagal', data.message, data.status).then(() => data.status === 'success' && location.reload()));
        }
    });
}
</script>