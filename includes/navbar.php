<?php
?>
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list"></i>
                </a>
            </li>
            <li class="nav-item d-none d-md-flex align-items-center ms-2">
                <span class="fw-semibold text-body"><?= htmlspecialchars($pageTitle) ?></span>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-4 me-1"></i>
                    <span class="d-none d-md-inline"><?= htmlspecialchars($currentUser['nama'] ?? '') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>pages/profil.php"><i class="bi bi-person me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-danger logout-link" href="<?= BASE_URL ?>logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Keluar
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>