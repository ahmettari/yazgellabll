<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'yonetici') {
    header("Location: ../index.php");
    exit();
}

// Form gönderildi mi kontrol et
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al
    $kriter_id = mysqli_real_escape_string($conn, $_POST['kriter_id']);
    $ilan_id = mysqli_real_escape_string($conn, $_POST['ilan_id']);
    $kriter_adi = mysqli_real_escape_string($conn, $_POST['kriter_adi']);
    $aciklama = mysqli_real_escape_string($conn, $_POST['aciklama']);
    $minimum_deger = mysqli_real_escape_string($conn, $_POST['minimum_deger']);
    
    // Kriteri güncelle
    $update_sql = "UPDATE ilan_kriterleri 
                  SET ilan_id = $ilan_id, 
                      kriter_adi = '$kriter_adi', 
                      aciklama = '$aciklama', 
                      minimum_deger = '$minimum_deger' 
                  WHERE id = $kriter_id";
    
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['success'] = "Kriter başarıyla güncellendi.";
    } else {
        $_SESSION['error'] = "Kriter güncellenirken bir hata oluştu: " . mysqli_error($conn);
    }
    
    header("Location: kriterler.php");
    exit();
} else {
    // Kriter ID'si kontrol et
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: kriterler.php");
        exit();
    }

    $kriter_id = $_GET['id'];

    // Kriter bilgilerini getir
    $sql = "SELECT ik.*, i.ilan_baslik 
            FROM ilan_kriterleri ik 
            JOIN ilanlar i ON ik.ilan_id = i.id 
            WHERE ik.id = $kriter_id";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
        header("Location: kriterler.php");
        exit();
    }

    $kriter = mysqli_fetch_assoc($result);

    // İlanları getir
    $ilanlar_sql = "SELECT * FROM ilanlar ORDER BY ilan_baslangic_tarihi DESC";
    $ilanlar_result = mysqli_query($conn, $ilanlar_sql);
    $ilanlar = mysqli_fetch_all($ilanlar_result, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kriter Düzenle - Yönetici Paneli</title>
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
                        <li class="breadcrumb-item"><a href="kriterler.php">Kriterler</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Kriter Düzenle</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-edit"></i> Kriter Düzenle</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="kriterler.php" class="btn btn-secondary">
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
                        
                        <form action="kriter_duzenle.php" method="post">
                            <input type="hidden" name="kriter_id" value="<?php echo $kriter['id']; ?>">
                            <div class="mb-3">
                                <label for="ilan_id" class="form-label">İlan *</label>
                                <select class="form-select" id="ilan_id" name="ilan_id" required>
                                    <option value="">İlan Seçiniz</option>
                                    <?php foreach ($ilanlar as $ilan): ?>
                                        <option value="<?php echo $ilan['id']; ?>" <?php echo ($kriter['ilan_id'] == $ilan['id']) ? 'selected' : ''; ?>>
                                            <?php echo $ilan['ilan_baslik']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="kriter_adi" class="form-label">Kriter Adı *</label>
                                <input type="text" class="form-control" id="kriter_adi" name="kriter_adi" value="<?php echo $kriter['kriter_adi']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="aciklama" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="aciklama" name="aciklama" rows="3"><?php echo $kriter['aciklama']; ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="minimum_deger" class="form-label">Minimum Değer *</label>
                                <input type="text" class="form-control" id="minimum_deger" name="minimum_deger" value="<?php echo $kriter['minimum_deger']; ?>" required>
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
