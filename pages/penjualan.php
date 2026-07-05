<?php
// File: pages/penjualan.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Penjualan.php';
require_once __DIR__ . '/../classes/MusimTanam.php';

$auth = new Auth();
$auth->requireLogin();
$currentUser = $auth->getUser();
$pageTitle = 'Penjualan';

$penjualanModel = new Penjualan();
$musimModel     = new MusimTanam();

$musimFilter = $_GET['id_musim'] ?? '';
$bulan       = $_GET['bulan'] ?? date('Y-m');

$daftarPenjualan = $penjualanModel->getAll($bulan);
if ($musimFilter !== '') {
    $daftarPenjualan = array_values(array_filter($daftarPenjualan, function ($p) use ($musimFilter) {
        return (int) $p['id_musim'] === (int) $musimFilter;
    }));
}

$totalPenjualanBulanIni  = $penjualanModel->getTotalPenjualanBulanIni();
$totalTransaksiBulanIni  = $penjualanModel->getTotalTransaksiBulanIni();
$totalKgTerjualBulanIni  = $penjualanModel->getTotalKgTerjualBulanIni();

$daftarMusim = $musimModel->getAll();
$jsonFlags   = JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Penjualan</h3>
            <p class="text-muted mb-0">Catat transaksi penjualan hasil panen.</p>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-success bg-gradient text-white">
                        <span class="info-box-icon"><i class="bi bi-cash-stack"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Penjualan (Bulan Ini)</span>
                            <span class="info-box-number"><?= formatRupiah($totalPenjualanBulanIni) ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-info bg-gradient text-white">
                        <span class="info-box-icon"><i class="bi bi-receipt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Transaksi (Bulan Ini)</span>
                            <span class="info-box-number"><?= $totalTransaksiBulanIni ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-warning bg-gradient text-white">
                        <span class="info-box-icon"><i class="bi bi-box-seam"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Produk Terjual (Bulan Ini)</span>
                            <span class="info-box-number"><?= number_format($totalKgTerjualBulanIni, 2, ',', '.') ?> kg</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="get" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Musim Tanam</label>
                            <select name="id_musim" class="form-select">
                                <option value="">Semua Musim</option>
                                <?php foreach ($daftarMusim as $m): ?>
                                    <option value="<?= $m['id_musim'] ?>" <?= (string) $musimFilter === (string) $m['id_musim'] ? 'selected' : '' ?>><?= $m['nama_musim'] ?></option>
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
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalJual" onclick="bukaTambahJual()">
                                <i class="bi bi-plus-lg"></i> Tambah
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Daftar Penjualan</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead>
                        <tr><th>Tanggal</th><th>Pembeli</th><th>Jumlah (kg)</th><th>Harga/kg</th><th>Total</th><th style="width:140px;">Keterangan</th></tr>
                        </thead>
                        <tbody>
                        <?php if (empty($daftarPenjualan)): ?>
                            <tr><td colspan="6" class="text-center text-muted">Tidak ada data penjualan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($daftarPenjualan as $p): ?>
                                <tr>
                                    <td><?= formatTanggalIndo($p['tanggal']) ?></td>
                                    <td><?= $p['pembeli'] ?></td>
                                    <td><?= number_format($p['jumlah_kg'], 2, ',', '.') ?> kg</td>
                                    <td><?= formatRupiah($p['harga_perkg']) ?></td>
                                    <td><?= formatRupiah($p['total']) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalJual"
                                                onclick='bukaEditJual(<?= json_encode($p, $jsonFlags) ?>)'>Edit</button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="hapusJual(<?= (int) $p['id_penjualan'] ?>)">Hapus</button>
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

<div class="modal fade" id="modalJual" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formJual">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalJualTitle">Tambah Penjualan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="jual_id">
                    <div class="mb-3">
                        <label class="form-label">Musim Tanam</label>
                        <select name="id_musim" id="jual_musim" class="form-select" required>
                            <option value="">-- Pilih Musim Tanam --</option>
                            <?php foreach ($daftarMusim as $m): ?>
                                <option value="<?= $m['id_musim'] ?>"><?= $m['nama_musim'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" id="jual_tanggal" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Pembeli</label>
                            <input type="text" name="pembeli" id="jual_pembeli" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Jumlah (kg)</label>
                            <input type="number" step="0.01" min="0" name="jumlah_kg" id="jual_jumlah" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Harga per kg (Rp)</label>
                            <input type="number" step="1" min="0" name="harga_perkg" id="jual_harga" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total</label>
                            <input type="text" id="jual_total" class="form-control" readonly>
                            <small class="text-muted">Total sesungguhnya dihitung server.</small>
                        </div>
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

function hitungTotalJual() {
    const jumlah = parseFloat(document.getElementById('jual_jumlah').value) || 0;
    const harga  = parseFloat(document.getElementById('jual_harga').value) || 0;
    document.getElementById('jual_total').value = 'Rp. ' + (jumlah * harga).toLocaleString('id-ID');
}
document.getElementById('jual_jumlah').addEventListener('input', hitungTotalJual);
document.getElementById('jual_harga').addEventListener('input', hitungTotalJual);

function bukaTambahJual() {
    document.getElementById('formJual').reset();
    document.getElementById('modalJualTitle').innerText = 'Tambah Penjualan';
    document.getElementById('jual_id').value = '';
    document.getElementById('jual_total').value = '';
}

function bukaEditJual(p) {
    document.getElementById('modalJualTitle').innerText = 'Edit Penjualan';
    document.getElementById('jual_id').value = p.id_penjualan;
    document.getElementById('jual_musim').value = p.id_musim;
    document.getElementById('jual_tanggal').value = p.tanggal;
    document.getElementById('jual_pembeli').value = p.pembeli;
    document.getElementById('jual_jumlah').value = p.jumlah_kg;
    document.getElementById('jual_harga').value = p.harga_perkg;
    hitungTotalJual();
}

document.getElementById('formJual').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('jual_id').value;
    const act = id ? 'update' : 'create';
    const url = BASE_URL + 'actions/penjualan_action.php?act=' + act + (id ? '&id=' + id : '');
    fetch(url, { method: 'POST', body: new FormData(this) })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Gagal', data.message, 'error'); // termasuk pesan "stok tidak mencukupi"
            }
        })
        .catch(() => Swal.fire('Gagal', 'Terjadi kesalahan koneksi.', 'error'));
});

function hapusJual(id) {
    Swal.fire({
        title: 'Hapus data penjualan ini?', text: 'Stok gudang akan dikembalikan.', icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Ya, Hapus', confirmButtonColor: '#dc3545', cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(BASE_URL + 'actions/penjualan_action.php?act=delete&id=' + id, { method: 'POST' })
                .then(res => res.json())
                .then(data => Swal.fire(data.status === 'success' ? 'Berhasil' : 'Gagal', data.message, data.status).then(() => data.status === 'success' && location.reload()));
        }
    });
}
</script>