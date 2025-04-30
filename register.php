<?php
session_start();
include("db_connection.php");

// Kayıt işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tc_no = mysqli_real_escape_string($conn, $_POST['tc_no']);
    $ad = mysqli_real_escape_string($conn, $_POST['ad']);
    $soyad = mysqli_real_escape_string($conn, $_POST['soyad']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $telefon = mysqli_real_escape_string($conn, $_POST['telefon']);
    $sifre = mysqli_real_escape_string($conn, $_POST['sifre']);
    $sifre_tekrar = mysqli_real_escape_string($conn, $_POST['sifre_tekrar']);
    $dogum_tarihi = mysqli_real_escape_string($conn, $_POST['dogum_tarihi']);
    $dogum_yeri = mysqli_real_escape_string($conn, $_POST['dogum_yeri']);
    $cinsiyet = mysqli_real_escape_string($conn, $_POST['cinsiyet']);
    
    $error = false;
    $error_message = "";
    
    // TC Kimlik No kontrolü
    if (strlen($tc_no) != 11 || !is_numeric($tc_no)) {
        $error = true;
        $error_message .= "TC Kimlik No 11 haneli ve sayısal olmalıdır.<br>";
    }
    
    // Şifre kontrolü
    if ($sifre != $sifre_tekrar) {
        $error = true;
        $error_message .= "Şifreler eşleşmiyor.<br>";
    }
    
    if (strlen($sifre) < 6) {
        $error = true;
        $error_message .= "Şifre en az 6 karakter olmalıdır.<br>";
    }
    
    // E-posta formatı kontrolü
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = true;
        $error_message .= "Geçerli bir e-posta adresi giriniz.<br>";
    }
    
    // TC Kimlik No ve E-posta benzersizlik kontrolü
    $check_sql = "SELECT * FROM kullanicilar WHERE tc_kimlik_no = '$tc_no' OR email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        $user = mysqli_fetch_assoc($check_result);
        if ($user['tc_kimlik_no'] == $tc_no) {
            $error = true;
            $error_message .= "Bu TC Kimlik No ile daha önce kayıt yapılmış.<br>";
        }
        if ($user['email'] == $email) {
            $error = true;
            $error_message .= "Bu e-posta adresi ile daha önce kayıt yapılmış.<br>";
        }
    }
    
    // Hata yoksa kayıt işlemini gerçekleştir
    if (!$error) {
        $insert_sql = "INSERT INTO kullanicilar (tc_kimlik_no, ad, soyad, email, telefon, sifre, rol, durum, kayit_tarihi, dogum_tarihi, dogum_yeri, cinsiyet) 
                      VALUES ('$tc_no', '$ad', '$soyad', '$email', '$telefon', '$sifre', 'aday', 1, NOW(), '$dogum_tarihi', '$dogum_yeri', '$cinsiyet')";
        
        if (mysqli_query($conn, $insert_sql)) {
            // Sistem log kaydı
            $aday_id = mysqli_insert_id($conn);
            $log_sql = "INSERT INTO sistem_log (kullanici_id, islem, aciklama, ip_adresi) 
                      VALUES ($aday_id, 'Kayıt', 'Yeni aday kaydı yapıldı', '" . $_SERVER['REMOTE_ADDR'] . "')";
            mysqli_query($conn, $log_sql);
            
            $_SESSION['success'] = "Kayıt işleminiz başarıyla tamamlandı! Giriş yapabilirsiniz.";
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Kayıt sırasında bir hata oluştu: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aday Kayıt - Akademik Personel Başvuru Sistemi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <div class="container">
        <header class="text-center my-4">
            <img src="logo.png" alt="Kocaeli Üniversitesi Logo" class="img-fluid mb-3" style="max-height: 100px;">
            <h1>Kocaeli Üniversitesi</h1>
            <h2>Akademik Personel Başvuru Sistemi</h2>
        </header>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-user-plus"></i> Aday Kayıt Formu</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message) && $error_message != ""): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i> 
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="register.php" method="post" class="needs-validation" novalidate>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tc_no" class="form-label"><i class="fas fa-id-card"></i> TC Kimlik No <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="tc_no" name="tc_no" required maxlength="11" pattern="[0-9]{11}" value="<?php echo isset($_POST['tc_no']) ? $_POST['tc_no'] : ''; ?>">
                                    <div class="invalid-feedback">
                                        TC Kimlik No 11 haneli ve sayısal olmalıdır.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label"><i class="fas fa-envelope"></i> E-posta <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" required value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                                    <div class="invalid-feedback">
                                        Geçerli bir e-posta adresi giriniz.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="ad" class="form-label"><i class="fas fa-user"></i> Ad <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ad" name="ad" required value="<?php echo isset($_POST['ad']) ? $_POST['ad'] : ''; ?>">
                                    <div class="invalid-feedback">
                                        Ad alanı zorunludur.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="soyad" class="form-label"><i class="fas fa-user"></i> Soyad <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="soyad" name="soyad" required value="<?php echo isset($_POST['soyad']) ? $_POST['soyad'] : ''; ?>">
                                    <div class="invalid-feedback">
                                        Soyad alanı zorunludur.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="telefon" class="form-label"><i class="fas fa-phone"></i> Telefon</label>
                                    <input type="text" class="form-control" id="telefon" name="telefon" value="<?php echo isset($_POST['telefon']) ? $_POST['telefon'] : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="dogum_tarihi" class="form-label"><i class="fas fa-calendar"></i> Doğum Tarihi</label>
                                    <input type="date" class="form-control" id="dogum_tarihi" name="dogum_tarihi" value="<?php echo isset($_POST['dogum_tarihi']) ? $_POST['dogum_tarihi'] : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="dogum_yeri" class="form-label"><i class="fas fa-map-marker-alt"></i> Doğum Yeri</label>
                                    <input type="text" class="form-control" id="dogum_yeri" name="dogum_yeri" value="<?php echo isset($_POST['dogum_yeri']) ? $_POST['dogum_yeri'] : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="cinsiyet" class="form-label"><i class="fas fa-venus-mars"></i> Cinsiyet</label>
                                    <select class="form-select" id="cinsiyet" name="cinsiyet">
                                        <option value="Belirtilmemiş" <?php echo (isset($_POST['cinsiyet']) && $_POST['cinsiyet'] == 'Belirtilmemiş') ? 'selected' : ''; ?>>Belirtilmek İstemiyorum</option>
                                        <option value="Erkek" <?php echo (isset($_POST['cinsiyet']) && $_POST['cinsiyet'] == 'Erkek') ? 'selected' : ''; ?>>Erkek</option>
                                        <option value="Kadın" <?php echo (isset($_POST['cinsiyet']) && $_POST['cinsiyet'] == 'Kadın') ? 'selected' : ''; ?>>Kadın</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="sifre" class="form-label"><i class="fas fa-lock"></i> Şifre <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="sifre" name="sifre" required minlength="6">
                                    <div class="invalid-feedback">
                                        Şifre en az 6 karakter olmalıdır.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="sifre_tekrar" class="form-label"><i class="fas fa-lock"></i> Şifre Tekrar <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="sifre_tekrar" name="sifre_tekrar" required minlength="6">
                                    <div class="invalid-feedback">
                                        Şifreler eşleşmiyor.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="kvkk" required>
                                <label class="form-check-label" for="kvkk">
                                    <small>Kişisel verilerin korunması kanunu kapsamında bilgilerimin işlenmesini kabul ediyorum. <span class="text-danger">*</span></small>
                                </label>
                                <div class="invalid-feedback">
                                    Devam etmek için kabul etmelisiniz.
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-user-plus"></i> Kayıt Ol
                                </button>
                            </div>
                        </form>
                        
                        <hr>
                        <div class="text-center">
                            <p>Zaten hesabınız var mı? <a href="index.php">Giriş Yap</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="text-center mt-4 mb-4">
            <p>&copy; <?php echo date("Y"); ?> Kocaeli Üniversitesi - Tüm Hakları Saklıdır.</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form doğrulama için Bootstrap validation
        (function () {
            'use strict'
            
            // Tüm formları seç
            var forms = document.querySelectorAll('.needs-validation')
            
            // Gönderme işlemini engelle ve doğrulama uygula
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        
                        // Şifre eşleşme kontrolü
                        var password = document.getElementById('sifre')
                        var confirm_password = document.getElementById('sifre_tekrar')
                        
                        if (password.value != confirm_password.value) {
                            confirm_password.setCustomValidity('Şifreler eşleşmiyor.')
                            event.preventDefault()
                            event.stopPropagation()
                        } else {
                            confirm_password.setCustomValidity('')
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
