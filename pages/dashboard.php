<?php
// File: pages/dashboard.php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/MusimTanam.php';
require_once __DIR__ . '/../classes/Biaya.php';
require_once __DIR__ . '/../classes/StokGudang.php';
require_once __DIR__ . '/../classes/Penjualan.php';

$auth = new Auth();
$auth->requireLogin();
$currentUser = $auth->getUser();
$pageTitle = 'Dashboard';

$musimModel     = new MusimTanam();
$biayaModel     = new Biaya();
$stokModel      = new StokGudang();
$penjualanModel = new Penjualan();

$bulan = $_GET['bulan'] ?? date('Y-m');

// Stat cards
$stokSaatIni     = $stokModel->getStokSaatIni();
$totalHasilPanen = $musimModel->getTotalHasilPanen();

$penjualanBulan       = $penjualanModel->getAll($bulan);
$totalPendapatanBulan = array_sum(array_column($penjualanBulan, 'total'));

// Pie chart: ringkasan siklus panen (status musim). Enum DB cuma Aktif/Selesai.
$statusCount = ['Aktif' => 0, 'Selesai' => 0];
foreach ($musimModel->getAll() as $m) {
    $statusCount[$m['status']] = ($statusCount[$m['status']] ?? 0) + 1;
}

// Line chart: analisis pengeluaran mingguan sesuai bulan yang difilter
$analisisMingguan = $biayaModel->getAnalisisMingguanBulanIni($bulan);

// Aktivitas terbaru: gabungan 5 biaya + penjualan terbaru
$aktivitas = [];
foreach ($biayaModel->getAll() as $b) {
    $aktivitas[] = [
        'tanggal' => $b['tanggal'],
        'teks'    => 'Mencatat biaya ' . $b['kategori'] . ' - ' . $b['deskripsi'],
    ];
}
foreach ($penjualanModel->getAll() as $p) {
    $aktivitas[] = [
        'tanggal' => $p['tanggal'],
        'teks'    => 'Penjualan ke ' . $p['pembeli'] . ' sebanyak ' . number_format($p['jumlah_kg'], 0, ',', '.') . ' kg',
    ];
}
usort($aktivitas, function ($a, $b) {
    return strcmp($b['tanggal'], $a['tanggal']);
});
$aktivitas = array_slice($aktivitas, 0, 5);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<main class="app-main">
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2">
                <div>
                    <h3 class="mb-0">Selamat Datang, <?= htmlspecialchars($currentUser['nama']) ?></h3>
                    <p class="text-muted mb-0">ringkasan perkembangan lahan dan gudang.</p>
                </div>
                <form method="get" class="d-flex gap-2">
                    <input type="month" name="bulan" class="form-control" value="<?= htmlspecialchars($bulan) ?>">
                    <button type="submit" class="btn btn-success"><i class="bi bi-funnel"></i> Filter</button>
                </form>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-info bg-gradient text-white">
                        <span class="info-box-icon"><i class="bi bi-building"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Stok Gudang</span>
                            <span class="info-box-number"><?= number_format($stokSaatIni, 2, ',', '.') ?> kg</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-success bg-gradient text-white">
                        <span class="info-box-icon"><i class="bi bi-flower3"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Hasil Panen</span>
                            <span class="info-box-number"><?= number_format($totalHasilPanen, 2, ',', '.') ?> kg</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-warning bg-gradient text-white">
                        <span class="info-box-icon"><i class="bi bi-cash-stack"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pendapatan (Bulan Ini)</span>
                            <span class="info-box-number"><?= formatRupiah($totalPendapatanBulan) ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Ringkasan Siklus Panen</h5></div>
                        <div class="card-body"><canvas id="chartSiklus" height="220"></canvas></div>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">Analisis Pengeluaran</h5></div>
                        <div class="card-body"><canvas id="chartPengeluaran" height="220"></canvas></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                    <a href="<?= BASE_URL ?>pages/laporan.php" class="btn btn-sm btn-outline-success">Lihat Semua</a>
                </div>
                <ul class="list-group list-group-flush">
                    <?php if (empty($aktivitas)): ?>
                        <li class="list-group-item text-muted">Belum ada aktivitas.</li>
                    <?php else: ?>
                        <?php foreach ($aktivitas as $i => $a): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span><?= $i + 1 ?>. <?= $a['teks'] ?></span>
                                <span class="text-muted small"><?= formatTanggalIndo($a['tanggal'], 'd-m-Y') ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>

        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
new Chart(document.getElementById('chartSiklus'), {
    type: 'doughnut',
    data: {
        labels: ['Aktif', 'Selesai'],
        datasets: [{
            data: [<?= (int) $statusCount['Aktif'] ?>, <?= (int) $statusCount['Selesai'] ?>],
            backgroundColor: ['#28a745', '#6c757d']
        }]
    },
    options: { responsive: true }
});

new Chart(document.getElementById('chartPengeluaran'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_keys($analisisMingguan)) ?>,
        datasets: [{
            label: 'Total Biaya',
            data: <?= json_encode(array_values($analisisMingguan)) ?>,
            borderColor: '#ffc107',
            backgroundColor: 'rgba(255,193,7,.2)',
            tension: 0.3,
            fill: true
        }]
    },
    options: { responsive: true }
});
</script>