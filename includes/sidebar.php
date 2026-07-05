<?php
$currentPage = basename($_SERVER['PHP_SELF']);

function isActive($file, $currentPage)
{
    return $file === $currentPage ? 'active' : '';
}
?>
<aside class="app-sidebar bg-success shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="<?= BASE_URL ?>pages/dashboard.php" class="brand-link">
            <i class="bi bi-flower1 fs-4 ms-2 me-2"></i>
            <!--<span class="brand-text fw-bold">AGRITECH</span>-->
            <img src="<?= BASE_URL ?>assets/img/iconAgritechPutih.png" alt="Logo Agritech" style="height: 50px;">
        </a>
    </div>

    <div class="sidebar-wrapper">
        <div class="user-panel d-flex align-items-center px-3 py-2">
            <i class="bi bi-person-circle fs-2 me-2"></i>
            <div>
                <div class="fw-semibold"><?= htmlspecialchars($currentUser['nama'] ?? '-') ?></div>
                <small class="text-uppercase opacity-75"><?= htmlspecialchars($currentUser['role'] ?? '-') ?></small>
            </div>
        </div>

        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>pages/dashboard.php" class="nav-link <?= isActive('dashboard.php', $currentPage) ?>">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>pages/siklus_panen.php" class="nav-link <?= isActive('siklus_panen.php', $currentPage) ?>">
                        <i class="nav-icon bi bi-flower3"></i>
                        <p>Siklus Panen</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>pages/catat_biaya.php" class="nav-link <?= isActive('catat_biaya.php', $currentPage) ?>">
                        <i class="nav-icon bi bi-cash-coin"></i>
                        <p>Catat Biaya</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>pages/stok_gudang.php" class="nav-link <?= isActive('stok_gudang.php', $currentPage) ?>">
                        <i class="nav-icon bi bi-building"></i>
                        <p>Stok Gudang</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>pages/penjualan.php" class="nav-link <?= isActive('penjualan.php', $currentPage) ?>">
                        <i class="nav-icon bi bi-cart-check"></i>
                        <p>Penjualan</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>pages/laporan.php" class="nav-link <?= isActive('laporan.php', $currentPage) ?>">
                        <i class="nav-icon bi bi-file-earmark-text"></i>
                        <p>Laporan</p>
                    </a>
                </li>
                <li class="nav-item mt-3 border-top pt-2">
                    <a href="<?= BASE_URL ?>logout.php" class="nav-link logout-link">
                        <i class="nav-icon bi bi-box-arrow-right"></i>
                        <p>Keluar</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>