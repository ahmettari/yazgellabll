<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Kullanıcı ekleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tc_no = mysqli_real_escape_string($conn, $_POST['tc_no']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $surname = mysqli_real_escape_string($conn, $_POST['surname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $active = isset($_POST['active']) ? 1 : 0;
    
    // TC Kimlik No kontrolü
    if (strlen($tc_no) != 11 || !is_numeric($tc_no)) {
        $_SESSION['error'] = "TC Kimlik No 11 haneli ve sayısal olmalıdır!";
    } else {
        // TC Kimlik No daha önce kullanılmış mı kontrolü
        $check_sql = "SELECT * FROM kullanicilar WHERE tc_kimlik_no = '$tc_no'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $_SESSION['error'] = "Bu TC Kimlik No ile daha önce kayıt yapılmış!";
        } else {
            // E-posta daha önce kullanılmış mı kontrolü
            $check_email_sql = "SELECT * FROM kullanicilar WHERE email = '$email'";
            $check_email_result = mysqli_query($conn, $check_email_sql);
            
            if (mysqli_num_rows($check_email_result) > 0) {
                $_SESSION['error'] = "Bu e-posta adresi ile daha önce kayıt yapılmış!";
            } else {
                // Kullanıcıyı veritabanına ekle
                $insert_sql = "INSERT INTO kullanicilar (tc_kimlik_no, sifre, ad, soyad, email, telefon, rol, durum, kayit_tarihi) 
                              VALUES ('$tc_no', '$password', '$name', '$surname', '$email', '$phone', '$role', $active, NOW())";
                
                if (mysqli_query($conn, $insert_sql)) {
                    $_SESSION['success'] = "Kullanıcı başarıyla eklendi.";
                    header("Location: kullanicilar.php");
                    exit();
                } else {
                    $_SESSION['error'] = "Kullanıcı eklenirken bir hata oluştu: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Ekle - Admin Paneli</title>
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
                        <li class="breadcrumb-item"><a href="kullanicilar.php">Kullanıcılar</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kullanıcı Ekle</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-user-plus"></i> Yeni Kullanıcı Ekle</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="kullanicilar.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <?php
                        // Hata mesajı varsa göster
                        if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                            unset($_SESSION['error']);
                        }
                        ?>
                        
                        <form action="kullanici_ekle.php" method="post">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tc_no" class="form-label">TC Kimlik No *</label>
                                    <input type="text" class="form-control" id="tc_no" name="tc_no" maxlength="11" required>
                                    <div class="form-text">11 haneli TC Kimlik Numarası giriniz.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">E-posta *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Ad *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="surname" class="form-label">Soyad *</label>
                                    <input type="text" class="form-control" id="surname" name="surname" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Telefon</label>
                                    <input type="text" class="form-control" id="phone" name="phone">
                                </div>
                                <div class="col-md-6">
                                    <label for="role" class="form-label">Kullanıcı Rolü *</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Rol Seçiniz</option>
                                        <option value="aday">Aday</option>
                                        <option value="juri">Jüri Üyesi</option>
                                        <option value="yonetici">Yönetici</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Şifre *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                                        <label class="form-check-label" for="active">
                                            Kullanıcı Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Kullanıcıyı Kaydet
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
