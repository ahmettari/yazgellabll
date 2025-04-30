<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'yonetici') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Form gönderildi mi kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mevcut_sifre = mysqli_real_escape_string($conn, $_POST['mevcut_sifre']);
    $yeni_sifre = mysqli_real_escape_string($conn, $_POST['yeni_sifre']);
    $yeni_sifre_tekrar = mysqli_real_escape_string($conn, $_POST['yeni_sifre_tekrar']);
    
    // Mevcut şifreyi kontrol et
    $sql = "SELECT sifre FROM kullanicilar WHERE id = $user_id";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    
    if ($user['sifre'] != md5($mevcut_sifre)) {
        $_SESSION['error'] = "Mevcut şifreniz hatalı.";
    } elseif ($yeni_sifre != $yeni_sifre_tekrar) {
        $_SESSION['error'] = "Yeni şifreler eşleşmiyor.";
    } elseif (strlen($yeni_sifre) < 6) {
        $_SESSION['error'] = "Yeni şifre en az 6 karakter olmalıdır.";
    } else {
        // Şifreyi güncelle
        $hashed_password = md5($yeni_sifre);
        $update_sql = "UPDATE kullanicilar SET sifre = '$hashed_password' WHERE id = $user_id";
        
        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['success'] = "Şifreniz başarıyla güncellendi.";
        } else {
            $_SESSION['error'] = "Şifre güncellenirken bir hata oluştu: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Değiştir - Yönetici Paneli</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include("navbar.php"); ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><a href="profil.php">Profil</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Şifre Değiştir</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <h2><i class="fas fa-key"></i> Şifre Değiştir</h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card shadow">
                    <div class="card-body">
                        <?php
                        // Başarı mesajı varsa göster
                        if (isset($_SESSION['success'])) {
                            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                            unset($_SESSION['success']);
                        }
                        
                        // Hata mesajı varsa göster
                        if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                            unset($_SESSION['error']);
                        }
                        ?>
                        
                        <form action="sifre_degistir.php" method="post">
                            <div class="mb-3">
                                <label for="mevcut_sifre" class="form-label">Mevcut Şifre *</label>
                                <input type="password" class="form-control" id="mevcut_sifre" name="mevcut_sifre" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="yeni_sifre" class="form-label">Yeni Şifre *</label>
                                <input type="password" class="form-control" id="yeni_sifre" name="yeni_sifre" required>
                                <div class="form-text">Şifreniz en az 6 karakter olmalıdır.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="yeni_sifre_tekrar" class="form-label">Yeni Şifre (Tekrar) *</label>
                                <input type="password" class="form-control" id="yeni_sifre_tekrar" name="yeni_sifre_tekrar" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Şifreyi Güncelle
                                </button>
                                <a href="profil.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Profile Dön
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="text-center mt-4 mb-4">
        <p>&copy; <?php echo date("Y"); ?> Kocaeli Üniversitesi - Tüm Hakları Saklıdır.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
