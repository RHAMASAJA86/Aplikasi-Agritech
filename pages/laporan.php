<?php
// File: pages/laporan.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Laporan.php';
require_once __DIR__ . '/../classes/MusimTanam.php';

$auth = new Auth();
$auth->requireLogin();
$currentUser = $auth->getUser();
$pageTitle = 'Laporan';

$laporanModel = new Laporan();
$musimModel   = new MusimTanam();

$musimFilter = $_GET['id_musim'] ?? '';
$bulan       = $_GET['bulan'] ?? date('Y-m');

$ringkasan = $laporanModel->getRingkasan($musimFilter !== '' ? (int) $musimFilter : null);
$grafik    = $laporanModel->getGrafikMingguan($bulan);

// Tab Biaya/Penjualan/Stok butuh satu musim spesifik dipilih
$biayaDetail     = $musimFilter !== '' ? $laporanModel->getBiayaDetail((int) $musimFilter) : [];
$penjualanDetail = $musimFilter !== '' ? $laporanModel->getPenjualanDetail((int) $musimFilter) : [];
$stokDetail      = $musimFilter !== '' ? $laporanModel->getStokDetail((int) $musimFilter) : [];

// Tab Siklus Panen selalu menampilkan seluruh musim + ringkasan v_laporan
//$db = Database::getInstance()->getConnection();
//$semuaMusimRingkasan = $db->query("SELECT * FROM v_laporan ORDER BY id_musim DESC")->fetchAll();

// Tab Siklus Panen selalu menampilkan seluruh musim + ringkasan (hitung langsung, tidak pakai v_laporan)
$db = Database::getInstance()->getConnection();
$semuaMusimRingkasan = $db->query(
    "SELECT
        m.id_musim,
        m.nama_musim,
        IFNULL(b.total_biaya, 0) AS total_pengeluaran,
        IFNULL(p.total_jual, 0) AS total_pendapatan,
        IFNULL(p.total_jual, 0) - IFNULL(b.total_biaya, 0) AS keuntungan
     FROM musim_tanam m
     LEFT JOIN (SELECT id_musim, SUM(nominal) AS total_biaya FROM biaya GROUP BY id_musim) b ON b.id_musim = m.id_musim
     LEFT JOIN (SELECT id_musim, SUM(total) AS total_jual FROM penjualan GROUP BY id_musim) p ON p.id_musim = m.id_musim
     ORDER BY m.id_musim DESC"
)->fetchAll();
$daftarMusim = $musimModel->getAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<style>
@media print {
    .app-sidebar, .app-header, .app-footer, form, .btn, .nav-tabs { display: none !important; }
    .app-main { margin: 0 !important; }
    .tab-pane { display: block !important; opacity: 1 !important; }
}
</style>
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <h3 class="mb-0">Laporan</h3>
            <p class="text-muted mb-0">Ringkasan biaya, penjualan, stok, dan siklus panen.</p>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <div class="card">
                <div class="card-body">
                    <form method="get" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Musim Tanam</label>
                            <select name="id_musim" class="form-select">
                                <option value="">Semua Laporan</option>
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
                            <button type="button" class="btn btn-outline-success" onclick="window.print()">
                                <i class="bi bi-printer"></i> Cetak Laporan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabRingkasan">Ringkasan</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabBiaya">Biaya</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPenjualan">Penjualan</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabStok">Stok</a></li>
                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabSiklus">Siklus Panen</a></li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane fade show active" id="tabRingkasan">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box bg-info bg-gradient text-white">
                                <span class="info-box-icon"><i class="bi bi-cash-stack"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Pendapatan</span>
                                    <span class="info-box-number"><?= formatRupiah($ringkasan['total_pendapatan']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-warning bg-gradient text-white">
                                <span class="info-box-icon"><i class="bi bi-wallet2"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Pengeluaran</span>
                                    <span class="info-box-number"><?= formatRupiah($ringkasan['total_pengeluaran']) ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box <?= $ringkasan['keuntungan'] >= 0 ? 'bg-success' : 'bg-danger' ?> bg-gradient text-white">
                                <span class="info-box-icon"><i class="bi bi-graph-up-arrow"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Keuntungan</span>
                                    <span class="info-box-number"><?= formatRupiah($ringkasan['keuntungan']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Grafik Pendapatan dan Pengeluaran</h5></div>
                        <div class="card-body"><canvas id="chartLaporan" height="240"></canvas></div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tabBiaya">
                    <?php if ($musimFilter === ''): ?>
                        <div class="alert alert-info">Pilih musim tanam di atas untuk melihat detail biaya.</div>
                    <?php else: ?>
                        <div class="card"><div class="card-body table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead><tr><th>Tanggal</th><th>Kategori</th><th>Deskripsi</th><th>Nominal</th></tr></thead>
                                <tbody>
                                <?php if (empty($biayaDetail)): ?>
                                    <tr><td colspan="4" class="text-center text-muted">Tidak ada data.</td></tr>
                                <?php else: foreach ($biayaDetail as $b): ?>
                                    <tr>
                                        <td><?= formatTanggalIndo($b['tanggal']) ?></td>
                                        <td><?= $b['kategori'] ?></td>
                                        <td><?= $b['deskripsi'] ?></td>
                                        <td><?= formatRupiah($b['nominal']) ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div></div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="tabPenjualan">
                    <?php if ($musimFilter === ''): ?>
                        <div class="alert alert-info">Pilih musim tanam di atas untuk melihat detail penjualan.</div>
                    <?php else: ?>
                        <div class="card"><div class="card-body table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead><tr><th>Tanggal</th><th>Pembeli</th><th>Jumlah (kg)</th><th>Harga/kg</th><th>Total</th></tr></thead>
                                <tbody>
                                <?php if (empty($penjualanDetail)): ?>
                                    <tr><td colspan="5" class="text-center text-muted">Tidak ada data.</td></tr>
                                <?php else: foreach ($penjualanDetail as $p): ?>
                                    <tr>
                                        <td><?= formatTanggalIndo($p['tanggal']) ?></td>
                                        <td><?= $p['pembeli'] ?></td>
                                        <td><?= number_format($p['jumlah_kg'], 2, ',', '.') ?></td>
                                        <td><?= formatRupiah($p['harga_perkg']) ?></td>
                                        <td><?= formatRupiah($p['total']) ?></td>
                                    </tr>
                                <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div></div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="tabStok">
                    <?php if ($musimFilter === ''): ?>
                        <div class="alert alert-info">Pilih musim tanam di atas untuk melihat detail stok.</div>
                    <?php else: ?>
                        <div class="card"><div class="card-body table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead><tr><th>ID Stok</th><th>Jumlah Stok</th></tr></thead>
                                <tbody>
                                <?php if (empty($stokDetail)): ?>
                                    <tr><td colspan="2" class="text-center text-muted">Tidak ada data.</td></tr>
                                <?php else: foreach ($stokDetail as $s): ?>
                                    <tr><td><?= $s['id_stok'] ?></td><td><?= number_format($s['jumlah_stok'], 2, ',', '.') ?> kg</td></tr>
                                <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div></div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="tabSiklus">
                    <div class="card"><div class="card-body table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead><tr><th>Musim Tanam</th><th>Pengeluaran</th><th>Pendapatan</th><th>Keuntungan</th></tr></thead>
                            <tbody>
                            <?php foreach ($semuaMusimRingkasan as $r): ?>
                                <tr>
                                    <td><?= $r['nama_musim'] ?></td>
                                    <td><?= formatRupiah($r['total_pengeluaran']) ?></td>
                                    <td><?= formatRupiah($r['total_pendapatan']) ?></td>
                                    <td class="<?= $r['keuntungan'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= formatRupiah($r['keuntungan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div></div>
                </div>

            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
new Chart(document.getElementById('chartLaporan'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($grafik['labels']) ?>,
        datasets: [
            { label: 'Pendapatan', data: <?= json_encode($grafik['pendapatan']) ?>, backgroundColor: '#0d6efd' },
            { label: 'Pengeluaran', data: <?= json_encode($grafik['pengeluaran']) ?>, backgroundColor: '#6c757d' }
        ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
});
</script>