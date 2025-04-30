<?php
// Oturum başlatılmamışsa başlat
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Oturum kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'aday') {
    header("Location: ../login.php");
    exit();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-info">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            <img src="../logo.png" alt="Logo" height="30" class="d-inline-block align-text-top me-2">
            KOÜ Aday Paneli
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Ana Sayfa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="ilanlar.php"><i class="fas fa-bullhorn"></i> İlanlar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="basvurularim.php"><i class="fas fa-file-alt"></i> Başvurularım</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="belgelerim.php"><i class="fas fa-file-pdf"></i> Belgelerim</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle"></i> <?php echo $_SESSION['name']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="profil.php"><i class="fas fa-id-card"></i> Profil</a></li>
                        <li><a class="dropdown-item" href="sifre_degistir.php"><i class="fas fa-key"></i> Şifre Değiştir</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Çıkış Yap</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
