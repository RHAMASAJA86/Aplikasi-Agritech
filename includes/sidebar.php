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
            <img src="<?= BASE_URL ?>assets/img/iconAgritechPutih.png" alt="Logo Agritech" style="height: 50px;">
        </a>
    </div>

    <div class="sidebar-wrapper">
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
                <li class="nav-item">
                    <a href="<?= BASE_URL ?>logout.php" class="nav-link logout-link">
                        <i class="nav-icon bi bi-box-arrow-right"></i>
                        <p>Keluar</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>