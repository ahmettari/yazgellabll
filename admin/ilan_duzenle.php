<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// İlan ID'si kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ilanlar.php");
    exit();
}

$ilan_id = $_GET['id'];

// İlan bilgilerini getir
$sql = "SELECT * FROM ilanlar WHERE id = $ilan_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: ilanlar.php");
    exit();
}

$ilan = mysqli_fetch_assoc($result);

// Form gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ilan_baslik = mysqli_real_escape_string($conn, $_POST['ilan_baslik']);
    $fakulte_birim = mysqli_real_escape_string($conn, $_POST['fakulte_birim']);
    $bolum = mysqli_real_escape_string($conn, $_POST['bolum']);
    $anabilim_dali = mysqli_real_escape_string($conn, $_POST['anabilim_dali']);
    $kadro_unvani = mysqli_real_escape_string($conn, $_POST['kadro_unvani']);
    $ilan_baslangic_tarihi = mysqli_real_escape_string($conn, $_POST['ilan_baslangic_tarihi']);
    $ilan_bitis_tarihi = mysqli_real_escape_string($conn, $_POST['ilan_bitis_tarihi']);
    $ilan_aciklama = mysqli_real_escape_string($conn, $_POST['ilan_aciklama']);
    $basvuru_kosullari = mysqli_real_escape_string($conn, $_POST['basvuru_kosullari']);
    
    $user_id = $_SESSION['user_id'];
    
    // İlanı güncelle
    $update_sql = "UPDATE ilanlar SET 
                  ilan_baslik = '$ilan_baslik', 
                  fakulte_birim = '$fakulte_birim', 
                  bolum = '$bolum', 
                  anabilim_dali = '$anabilim_dali', 
                  kadro_unvani = '$kadro_unvani', 
                  ilan_baslangic_tarihi = '$ilan_baslangic_tarihi', 
                  ilan_bitis_tarihi = '$ilan_bitis_tarihi', 
                  ilan_aciklama = '$ilan_aciklama', 
                  basvuru_kosullari = '$basvuru_kosullari', 
                  guncelleyen_id = $user_id, 
                  guncelleme_tarihi = NOW() 
                  WHERE id = $ilan_id";
    
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['success'] = "İlan başarıyla güncellendi.";
        header("Location: ilan_detay.php?id=$ilan_id");
        exit();
    } else {
        $_SESSION['error'] = "İlan güncellenirken bir hata oluştu: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Düzenle - Admin Paneli</title>
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
                        <li class="breadcrumb-item"><a href="ilanlar.php">İlanlar</a></li>
                        <li class="breadcrumb-item"><a href="ilan_detay.php?id=<?php echo $ilan_id; ?>">İlan Detayı</a></li>
                        <li class="breadcrumb-item active" aria-current="page">İlan Düzenle</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-edit"></i> İlan Düzenle</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="ilan_detay.php?id=<?php echo $ilan_id; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
            </div>
        </div>
        
        <?php
        // Hata mesajı varsa göster
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <form action="ilan_duzenle.php?id=<?php echo $ilan_id; ?>" method="post">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="ilan_baslik" class="form-label">İlan Başlığı *</label>
                                    <input type="text" class="form-control" id="ilan_baslik" name="ilan_baslik" value="<?php echo $ilan['ilan_baslik']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="fakulte_birim" class="form-label">Fakülte/Birim *</label>
                                    <input type="text" class="form-control" id="fakulte_birim" name="fakulte_birim" value="<?php echo $ilan['fakulte_birim']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="bolum" class="form-label">Bölüm *</label>
                                    <input type="text" class="form-control" id="bolum" name="bolum" value="<?php echo $ilan['bolum']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="anabilim_dali" class="form-label">Anabilim Dalı *</label>
                                    <input type="text" class="form-control" id="anabilim_dali" name="anabilim_dali" value="<?php echo $ilan['anabilim_dali']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="kadro_unvani" class="form-label">Kadro Ünvanı *</label>
                                    <input type="text" class="form-control" id="kadro_unvani" name="kadro_unvani" value="<?php echo $ilan['kadro_unvani']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="ilan_baslangic_tarihi" class="form-label">İlan Başlangıç Tarihi *</label>
                                    <input type="date" class="form-control" id="ilan_baslangic_tarihi" name="ilan_baslangic_tarihi" value="<?php echo $ilan['ilan_baslangic_tarihi']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="ilan_bitis_tarihi" class="form-label">İlan Bitiş Tarihi *</label>
                                    <input type="date" class="form-control" id="ilan_bitis_tarihi" name="ilan_bitis_tarihi" value="<?php echo $ilan['ilan_bitis_tarihi']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="ilan_aciklama" class="form-label">İlan Açıklaması *</label>
                                <textarea class="form-control" id="ilan_aciklama" name="ilan_aciklama" rows="5" required><?php echo $ilan['ilan_aciklama']; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="basvuru_kosullari" class="form-label">Başvuru Koşulları *</label>
                                <textarea class="form-control" id="basvuru_kosullari" name="basvuru_kosullari" rows="5" required><?php echo $ilan['basvuru_kosullari']; ?></textarea>
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
