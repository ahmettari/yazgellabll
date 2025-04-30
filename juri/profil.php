<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'juri') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini getir
$sql = "SELECT * FROM kullanicilar WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

// Profil güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = mysqli_real_escape_string($conn, $_POST['ad']);
    $soyad = mysqli_real_escape_string($conn, $_POST['soyad']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telefon = mysqli_real_escape_string($conn, $_POST['telefon']);
    
    // E-posta kontrolü
    $email_check = "SELECT * FROM kullanicilar WHERE email = '$email' AND id != $user_id";
    $email_result = mysqli_query($conn, $email_check);
    
    if (mysqli_num_rows($email_result) > 0) {
        $_SESSION['error'] = "Bu e-posta adresi başka bir kullanıcı tarafından kullanılmaktadır.";
    } else {
        // Profil bilgilerini güncelle
        $update_sql = "UPDATE kullanicilar SET ad = '$ad', soyad = '$soyad', email = '$email', telefon = '$telefon' WHERE id = $user_id";
        
        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['success'] = "Profil bilgileriniz başarıyla güncellendi.";
            $_SESSION['name'] = $ad . ' ' . $soyad;
            header("Location: profil.php");
            exit();
        } else {
            $_SESSION['error'] = "Profil güncellenirken bir hata oluştu: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilim - Jüri Paneli</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Profilim</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-user"></i> Profil Bilgileri</h3>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-6x text-primary"></i>
                        </div>
                        <h4><?php echo $user['ad'] . ' ' . $user['soyad']; ?></h4>
                        <p class="text-muted"><?php echo $user['email']; ?></p>
                        <hr>
                        <div class="text-start">
                            <p><strong>TC Kimlik No:</strong> <?php echo $user['tc_kimlik_no']; ?></p>
                            <p><strong>Telefon:</strong> <?php echo $user['telefon']; ?></p>
                            <p><strong>Kayıt Tarihi:</strong> <?php echo date('d.m.Y', strtotime($user['kayit_tarihi'])); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-cog"></i> Hesap İşlemleri</h3>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <a href="sifre_degistir.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-key"></i> Şifre Değiştir
                            </a>
                            <a href="basvurular.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-clipboard-list"></i> Değerlendirilecek Başvurular
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-user-edit"></i> Profil Düzenle</h3>
                    </div>
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
                        
                        <form action="profil.php" method="post">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="ad" class="form-label">Ad *</label>
                                    <input type="text" class="form-control" id="ad" name="ad" value="<?php echo $user['ad']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="soyad" class="form-label">Soyad *</label>
                                    <input type="text" class="form-control" id="soyad" name="soyad" value="<?php echo $user['soyad']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">E-posta *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="telefon" class="form-label">Telefon</label>
                                    <input type="text" class="form-control" id="telefon" name="telefon" value="<?php echo $user['telefon']; ?>">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tc_kimlik_no" class="form-label">TC Kimlik No</label>
                                    <input type="text" class="form-control" id="tc_kimlik_no" value="<?php echo $user['tc_kimlik_no']; ?>" disabled>
                                    <div class="form-text">TC Kimlik No değiştirilemez.</div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Değişiklikleri Kaydet
                                </button>
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
