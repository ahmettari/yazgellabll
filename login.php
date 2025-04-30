<?php
session_start();
include("db_connection.php");

// Kullanıcı zaten giriş yapmışsa, rolüne göre yönlendir
if (isset($_SESSION['user_id'])) {
  if ($_SESSION['role'] == 'admin') {
      header("Location: admin/dashboard.php");
  } elseif ($_SESSION['role'] == 'yonetici') {
      header("Location: yonetici/dashboard.php");
  } elseif ($_SESSION['role'] == 'juri') {
      header("Location: juri/dashboard.php");
  } else {
      header("Location: aday/dashboard.php");
  }
  exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $tc_no = mysqli_real_escape_string($conn, $_POST['tc_no']);
  $password = mysqli_real_escape_string($conn, $_POST['password']);
  
  // Kullanıcıyı veritabanında ara
  $sql = "SELECT * FROM kullanicilar WHERE tc_kimlik_no = '$tc_no' AND sifre = '$password' AND durum = 1";
  $result = mysqli_query($conn, $sql);
  
  if (mysqli_num_rows($result) == 1) {
      $user = mysqli_fetch_assoc($result);
      
      // Session değişkenlerini ayarla
      $_SESSION['user_id'] = $user['id'];
      $_SESSION['tc_no'] = $user['tc_kimlik_no'];
      $_SESSION['name'] = $user['ad'] . ' ' . $user['soyad'];
      $_SESSION['role'] = $user['rol'];
      $_SESSION['user_role'] = $user['rol']; // Navbar.php için ek session değişkeni
      
      // Son giriş tarihini güncelle
      $update_sql = "UPDATE kullanicilar SET son_giris_tarihi = NOW() WHERE id = " . $user['id'];
      mysqli_query($conn, $update_sql);
      
      // Sistem log kaydı
      $log_sql = "INSERT INTO sistem_log (kullanici_id, islem, aciklama, ip_adresi) 
                  VALUES (" . $user['id'] . ", 'Giriş', 'Kullanıcı sisteme giriş yaptı', '" . $_SERVER['REMOTE_ADDR'] . "')";
      mysqli_query($conn, $log_sql);
      
      // Kullanıcı rolüne göre yönlendir
      if ($user['rol'] == 'admin') {
          header("Location: admin/dashboard.php");
      } elseif ($user['rol'] == 'yonetici') {
          header("Location: yonetici/dashboard.php");
      } elseif ($user['rol'] == 'juri') {
          header("Location: juri/dashboard.php");
      } else {
          header("Location: aday/dashboard.php");
      }
      exit();
  } else {
      $error = "TC Kimlik No veya şifre hatalı!";
  }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Giriş Yap - Akademik Personel Başvuru Sistemi</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
  <div class="container">
      <div class="row justify-content-center mt-5">
          <div class="col-md-6">
              <div class="card shadow">
                  <div class="card-header bg-primary text-white text-center">
                      <h4><i class="fas fa-user-circle"></i> Giriş Yap</h4>
                  </div>
                  <div class="card-body">
                      <div class="text-center mb-4">
                          <img src="assets/img/logo.png" alt="Kocaeli Üniversitesi" class="img-fluid" style="max-height: 100px;">
                          <h5 class="mt-2">Akademik Personel Başvuru Sistemi</h5>
                      </div>
                      
                      <?php if ($error): ?>
                          <div class="alert alert-danger">
                              <i class="fas fa-exclamation-triangle"></i> <?php echo $error; ?>
                          </div>
                      <?php endif; ?>
                      
                      <form action="login.php" method="post">
                          <div class="mb-3">
                              <label for="tc_no" class="form-label"><i class="fas fa-id-card"></i> TC Kimlik No</label>
                              <input type="text" class="form-control" id="tc_no" name="tc_no" required>
                          </div>
                          <div class="mb-3">
                              <label for="password" class="form-label"><i class="fas fa-lock"></i> Şifre</label>
                              <input type="password" class="form-control" id="password" name="password" required>
                          </div>
                          <div class="d-grid gap-2">
                              <button type="submit" class="btn btn-primary">
                                  <i class="fas fa-sign-in-alt"></i> Giriş Yap
                              </button>
                          </div>
                      </form>
                      
                      <div class="mt-3 text-center">
                          <p>Hesabınız yok mu? <a href="register.php">Kayıt Ol</a></p>
                      </div>
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
