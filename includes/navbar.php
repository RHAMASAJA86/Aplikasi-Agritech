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
        </ul>

        <ul class="navbar-nav ms-auto">
            <li class="nav-item dropdown">
                <?php
                $fotoPath    = __DIR__ . '/../assets/img/profile.png';
                $fotoVersion = file_exists($fotoPath) ? filemtime($fotoPath) : time();
                ?>
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                    <img src="<?= BASE_URL ?>assets/img/profile.png?v=<?= $fotoVersion ?>" alt="Foto Profil"
                        class="rounded-circle me-2" style="width:32px; height:32px; object-fit:cover;">
                    <span class="d-none d-md-inline"><?= htmlspecialchars($currentUser['nama'] ?? '') ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>pages/profil.php"><i class="bi bi-image me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="<?= BASE_URL ?>pages/pengaturan.php"><i class="bi bi-gear me-2"></i>Pengaturan</a></li>
                </li>

                 
            </li>
        </ul>
    </div>
</nav>