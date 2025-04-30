<?php
session_start();
require_once '../db_connection.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'yonetici') {
    header("Location: ../login.php");
    exit();
}

$success_message = "";
$error_message = "";

// İlan ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ilanlar.php");
    exit();
}

$ilan_id = $_GET['id'];

// Form gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al
    $fakulte = mysqli_real_escape_string($conn, $_POST['fakulte']);
    $bolum = mysqli_real_escape_string($conn, $_POST['bolum']);
    $anabilim_dali = mysqli_real_escape_string($conn, $_POST['anabilim_dali']);
    $kadro_unvani = mysqli_real_escape_string($conn, $_POST['kadro_unvani']);
    $kadro_sayisi = intval($_POST['kadro_sayisi']);
    $ilan_tarihi = mysqli_real_escape_string($conn, $_POST['ilan_tarihi']);
    $son_basvuru_tarihi = mysqli_real_escape_string($conn, $_POST['son_basvuru_tarihi']);
    $aciklama = mysqli_real_escape_string($conn, $_POST['aciklama']);
    $durum = isset($_POST['durum']) ? 'aktif' : 'pasif';
    
    // İlan başlığı oluştur
    $ilan_baslik = "$fakulte - $bolum - $kadro_unvani";
    
    // Veritabanında güncelle
    $sql = "UPDATE ilanlar SET 
            ilan_baslik = '$ilan_baslik',
            fakulte_birim = '$fakulte',
            bolum = '$bolum',
            anabilim_dali = '$anabilim_dali',
            kadro_unvani = '$kadro_unvani',
            kadro_sayisi = $kadro_sayisi,
            ilan_baslangic_tarihi = '$ilan_tarihi',
            ilan_bitis_tarihi = '$son_basvuru_tarihi',
            aciklama = '$aciklama',
            durum = '$durum',
            guncelleme_tarihi = NOW()
            WHERE id = $ilan_id";
    
    if (mysqli_query($conn, $sql)) {
        $success_message = "İlan başarıyla güncellendi.";
    } else {
        $error_message = "İlan güncellenirken bir hata oluştu: " . mysqli_error($conn);
    }
}

// İlan bilgilerini getir
$sql = "SELECT * FROM ilanlar WHERE id = $ilan_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: ilanlar.php");
    exit();
}

$ilan = mysqli_fetch_assoc($result);

// Fakülte listesini getir
$fakulte_sql = "SELECT DISTINCT fakulte_birim FROM ilanlar ORDER BY fakulte_birim";
$fakulte_result = mysqli_query($conn, $fakulte_sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Düzenle - Yönetici Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item"><a href="ilanlar.php">İlanlar</a></li>
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
                <a href="ilanlar.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> İlanlara Dön
                </a>
            </div>
        </div>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="card shadow">
            <div class="card-body">
                <form method="post" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fakulte" class="form-label">Fakülte/Birim</label>
                            <input type="text" class="form-control" id="fakulte" name="fakulte" list="fakulte_list" value="<?php echo $ilan['fakulte_birim']; ?>" required>
                            <datalist id="fakulte_list">
                                <?php while ($row = mysqli_fetch_assoc($fakulte_result)): ?>
                                    <option value="<?php echo $row['fakulte_birim']; ?>">
                                <?php endwhile; ?>
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label for="bolum" class="form-label">Bölüm</label>
                            <input type="text" class="form-control" id="bolum" name="bolum" value="<?php echo $ilan['bolum']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="anabilim_dali" class="form-label">Anabilim Dalı</label>
                            <input type="text" class="form-control" id="anabilim_dali" name="anabilim_dali" value="<?php echo $ilan['anabilim_dali']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="kadro_unvani" class="form-label">Kadro Ünvanı</label>
                            <select class="form-select" id="kadro_unvani" name="kadro_unvani" required>
                                <option value="">Seçiniz</option>
                                <option value="Profesör" <?php echo ($ilan['kadro_unvani'] == 'Profesör') ? 'selected' : ''; ?>>Profesör</option>
                                <option value="Doçent" <?php echo ($ilan['kadro_unvani'] == 'Doçent') ? 'selected' : ''; ?>>Doçent</option>
                                <option value="Doktor Öğretim Üyesi" <?php echo ($ilan['kadro_unvani'] == 'Doktor Öğretim Üyesi') ? 'selected' : ''; ?>>Doktor Öğretim Üyesi</option>
                                <option value="Öğretim Görevlisi" <?php echo ($ilan['kadro_unvani'] == 'Öğretim Görevlisi') ? 'selected' : ''; ?>>Öğretim Görevlisi</option>
                                <option value="Araştırma Görevlisi" <?php echo ($ilan['kadro_unvani'] == 'Araştırma Görevlisi') ? 'selected' : ''; ?>>Araştırma Görevlisi</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="kadro_sayisi" class="form-label">Kadro Sayısı</label>
                            <input type="number" class="form-control" id="kadro_sayisi" name="kadro_sayisi" min="1" value="<?php echo $ilan['kadro_sayisi']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="ilan_tarihi" class="form-label">İlan Tarihi</label>
                            <input type="date" class="form-control" id="ilan_tarihi" name="ilan_tarihi" value="<?php echo $ilan['ilan_baslangic_tarihi']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="son_basvuru_tarihi" class="form-label">Son Başvuru Tarihi</label>
                            <input type="date" class="form-control" id="son_basvuru_tarihi" name="son_basvuru_tarihi" value="<?php echo $ilan['ilan_bitis_tarihi']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="aciklama" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="5"><?php echo $ilan['aciklama']; ?></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="durum" name="durum" <?php echo ($ilan['durum'] == 'aktif') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="durum">İlan Aktif</label>
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
    
    <footer class="text-center mt-4 mb-4">
        <p>&copy; <?php echo date("Y"); ?> Kocaeli Üniversitesi - Tüm Hakları Saklıdır.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Son başvuru tarihi için minimum değeri bugün olarak ayarla
        document.addEventListener('DOMContentLoaded', function() {
            const ilanTarihi = document.getElementById('ilan_tarihi');
            const sonBasvuruTarihi = document.getElementById('son_basvuru_tarihi');
            
            // İlan tarihi değiştiğinde son başvuru tarihinin minimum değerini güncelle
            ilanTarihi.addEventListener('change', function() {
                sonBasvuruTarihi.min = ilanTarihi.value;
                if (sonBasvuruTarihi.value && sonBasvuruTarihi.value < ilanTarihi.value) {
                    sonBasvuruTarihi.value = ilanTarihi.value;
                }
            });
        });
    </script>
</body>
</html>
