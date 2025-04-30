<?php
session_start();
include("db_connection.php");

// Ana sayfa - Giriş sayfası veya kullanıcı rolüne göre yönlendirme
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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kocaeli Üniversitesi - Akademik Personel Başvuru Sistemi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="text-center my-4">
            <img src="logo.png" alt="Kocaeli Üniversitesi Logo" class="img-fluid mb-3" style="max-height: 100px;">
            <h1>Kocaeli Üniversitesi</h1>
            <h2>Akademik Personel Başvuru Sistemi</h2>
        </header>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-sign-in-alt"></i> Giriş Yap</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        // Hata mesajı varsa göster
                        if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger">';
                            echo '<i class="fas fa-exclamation-triangle"></i> ' . $_SESSION['error'];
                            echo '</div>';
                            unset($_SESSION['error']);
                        }
                        
                        // Başarı mesajı varsa göster
                        if (isset($_SESSION['success'])) {
                            echo '<div class="alert alert-success">';
                            echo '<i class="fas fa-check-circle"></i> ' . $_SESSION['success'];
                            echo '</div>';
                            unset($_SESSION['success']);
                        }
                        ?>
                        <form action="login.php" method="post">
                            <div class="mb-3">
                                <label for="tc_no" class="form-label"><i class="fas fa-id-card"></i> TC Kimlik Numarası</label>
                                <input type="text" class="form-control" id="tc_no" name="tc_no" required maxlength="11" pattern="[0-9]{11}">
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
                        <hr>
                        <div class="text-center">
                            <p>Hesabınız yok mu? <a href="register.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-user-plus"></i> Kayıt Ol
                            </a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-info-circle"></i> (DEMO SİSTEM)Sistem Hakkında</h3>
                    </div>
                    <div class="card-body">
                        <p>Kocaeli Üniversitesi Akademik Personel Başvuru Sistemi, akademik personel adaylarının ilgili kadrolara başvurmasını sağlarken, yönetici ve admin kullanıcıları için ilan ve başvuru kriterlerinin düzenlenmesine olanak tanır.</p>
                        <h4><i class="fas fa-users"></i> Kullanıcı Rolleri</h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-user-graduate"></i> Aday</h5>
                                        <p class="card-text">Akademik kadrolara başvuru yapabilen kullanıcılar</p>
                                        <ul>
                                            <li>İlanlara başvuru yapabilir</li>
                                            <li>Başvuru durumunu takip edebilir</li>
                                            <li>Belgelerini yönetebilir</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-user-shield"></i> Admin</h5>
                                        <p class="card-text">İlanları oluşturan ve düzenleyen kullanıcılar</p>
                                        <ul>
                                            <li>İlanları yönetebilir</li>
                                            <li>Başvuruları inceleyebilir</li>
                                            <li>Kullanıcıları yönetebilir</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-user-tie"></i> Yönetici</h5>
                                        <p class="card-text">Başvuru kriterlerini belirleyen ve sistemin genel kurallarını yöneten kullanıcılar</p>
                                        <ul>
                                            <li>Kriterleri yönetebilir</li>
                                            <li>Jüri atayabilir</li>
                                            <li>Nihai kararları verebilir</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-user-check"></i> Jüri Üyesi</h5>
                                        <p class="card-text">Adayların başvuru belgelerini inceleyen ve değerlendirme raporu oluşturan kullanıcılar</p>
                                        <ul>
                                            <li>Başvuruları değerlendirebilir</li>
                                            <li>Değerlendirme raporu oluşturabilir</li>
                                            <li>Puanlama yapabilir</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
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
</body>
</html>
