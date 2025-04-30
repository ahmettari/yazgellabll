<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Kullanıcı ID'si kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: kullanicilar.php");
    exit();
}

$kullanici_id = $_GET['id'];

// Kullanıcı bilgilerini getir
$sql = "SELECT * FROM kullanicilar WHERE id = $kullanici_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: kullanicilar.php");
    exit();
}

$kullanici = mysqli_fetch_assoc($result);

// Kullanıcı güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $surname = mysqli_real_escape_string($conn, $_POST['surname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $active = isset($_POST['active']) ? 1 : 0;
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    // E-posta kontrolü
    $email_check = "SELECT * FROM kullanicilar WHERE email = '$email' AND id != $kullanici_id";
    $email_result = mysqli_query($conn, $email_check);
    
    if (mysqli_num_rows($email_result) > 0) {
        $_SESSION['error'] = "Bu e-posta adresi başka bir kullanıcı tarafından kullanılmaktadır.";
    } else {
        // Kullanıcıyı güncelle
        $update_sql = "UPDATE kullanicilar SET 
                      ad = '$name', 
                      soyad = '$surname', 
                      email = '$email', 
                      telefon = '$phone', 
                      rol = '$role', 
                      durum = $active";
        
        // Şifre değiştirilecekse ekle
        if (!empty($password)) {
            $update_sql .= ", sifre = '$password'";
        }
        
        $update_sql .= " WHERE id = $kullanici_id";
        
        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['success'] = "Kullanıcı bilgileri başarıyla güncellendi.";
            header("Location: kullanicilar.php");
            exit();
        } else {
            $_SESSION['error'] = "Kullanıcı güncellenirken bir hata oluştu: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Düzenle - Admin Paneli</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Kullanıcı Düzenle</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-user-edit"></i> Kullanıcı Düzenle</h2>
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
                        
                        <form action="kullanici_duzenle.php?id=<?php echo $kullanici_id; ?>" method="post">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tc_no" class="form-label">TC Kimlik No</label>
                                    <input type="text" class="form-control" id="tc_no" value="<?php echo $kullanici['tc_kimlik_no']; ?>" disabled>
                                    <div class="form-text">TC Kimlik No değiştirilemez.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">E-posta *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $kullanici['email']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Ad *</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo $kullanici['ad']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="surname" class="form-label">Soyad *</label>
                                    <input type="text" class="form-control" id="surname" name="surname" value="<?php echo $kullanici['soyad']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Telefon</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $kullanici['telefon']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="role" class="form-label">Kullanıcı Rolü *</label>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="">Rol Seçiniz</option>
                                        <option value="aday" <?php echo ($kullanici['rol'] == 'aday') ? 'selected' : ''; ?>>Aday</option>
                                        <option value="juri" <?php echo ($kullanici['rol'] == 'juri') ? 'selected' : ''; ?>>Jüri Üyesi</option>
                                        <option value="yonetici" <?php echo ($kullanici['rol'] == 'yonetici') ? 'selected' : ''; ?>>Yönetici</option>
                                        <option value="admin" <?php echo ($kullanici['rol'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Şifre</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    <div class="form-text">Şifreyi değiştirmek istemiyorsanız boş bırakın.</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="active" name="active" <?php echo ($kullanici['durum'] == 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="active">
                                            Kullanıcı Aktif
                                        </label>
                                    </div>
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
