<?php
require_once '../db_connection.php';
include 'navbar.php';

// Form gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fakulte = $_POST['fakulte'];
    $bolum = $_POST['bolum'];
    $anabilim_dali = $_POST['anabilim_dali'];
    $kadro_unvani = $_POST['kadro_unvani'];
    $kadro_sayisi = $_POST['kadro_sayisi'];
    $ilan_tarihi = $_POST['ilan_tarihi'];
    $son_basvuru_tarihi = $_POST['son_basvuru_tarihi'];
    $aciklama = $_POST['aciklama'];
    $durum = isset($_POST['durum']) ? 'aktif' : 'taslak';
    $ekleyen_id = $_SESSION['user_id'];

    // Veritabanına kaydet
    $stmt = $conn->prepare("INSERT INTO ilanlar (fakulte_birim, bolum, anabilim_dali, kadro_unvani, kadro_sayisi, ilan_baslangic_tarihi, ilan_bitis_tarihi, ilan_aciklama, durum, olusturan_id, olusturma_tarihi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssisssii", $fakulte, $bolum, $anabilim_dali, $kadro_unvani, $kadro_sayisi, $ilan_tarihi, $son_basvuru_tarihi, $aciklama, $durum, $ekleyen_id);
    
    if ($stmt->execute()) {
        $success_message = "İlan başarıyla eklendi.";
    } else {
        $error_message = "İlan eklenirken bir hata oluştu: " . $conn->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni İlan Ekle - Admin Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Yeni İlan Ekle</h2>
            <a href="ilanlar.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> İlanlara Dön
            </a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="post" action="">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fakulte" class="form-label">Fakülte/Birim</label>
                            <input type="text" class="form-control" id="fakulte" name="fakulte" required>
                        </div>
                        <div class="col-md-6">
                            <label for="bolum" class="form-label">Bölüm</label>
                            <input type="text" class="form-control" id="bolum" name="bolum" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="anabilim_dali" class="form-label">Anabilim Dalı</label>
                            <input type="text" class="form-control" id="anabilim_dali" name="anabilim_dali" required>
                        </div>
                        <div class="col-md-6">
                            <label for="kadro_unvani" class="form-label">Kadro Ünvanı</label>
                            <select class="form-select" id="kadro_unvani" name="kadro_unvani" required>
                                <option value="">Seçiniz</option>
                                <option value="Profesör">Profesör</option>
                                <option value="Doçent">Doçent</option>
                                <option value="Doktor Öğretim Üyesi">Doktor Öğretim Üyesi</option>
                                <option value="Öğretim Görevlisi">Öğretim Görevlisi</option>
                                <option value="Araştırma Görevlisi">Araştırma Görevlisi</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="kadro_sayisi" class="form-label">Kadro Sayısı</label>
                            <input type="number" class="form-control" id="kadro_sayisi" name="kadro_sayisi" min="1" value="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="ilan_tarihi" class="form-label">İlan Tarihi</label>
                            <input type="date" class="form-control" id="ilan_tarihi" name="ilan_tarihi" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="son_basvuru_tarihi" class="form-label">Son Başvuru Tarihi</label>
                            <input type="date" class="form-control" id="son_basvuru_tarihi" name="son_basvuru_tarihi" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="aciklama" class="form-label">Açıklama</label>
                        <textarea class="form-control" id="aciklama" name="aciklama" rows="5"></textarea>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="durum" name="durum" checked>
                        <label class="form-check-label" for="durum">İlan Aktif</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">İlanı Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
