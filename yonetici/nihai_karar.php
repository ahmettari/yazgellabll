<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'yonetici') {
    header("Location: ../index.php");
    exit();
}

// Nihai karar verme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['basvuru_id'])) {
    $basvuru_id = mysqli_real_escape_string($conn, $_POST['basvuru_id']);
    $durum = mysqli_real_escape_string($conn, $_POST['durum']);
    $sonuc_aciklama = mysqli_real_escape_string($conn, $_POST['sonuc_aciklama']);
    
    // sonuc_tarihi sütunu olmadığı için kaldırıldı
    $update_sql = "UPDATE basvurular SET durum = '$durum', sonuc_aciklama = '$sonuc_aciklama' WHERE id = $basvuru_id";
    
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['success'] = "Nihai karar başarıyla kaydedildi.";
    } else {
        $_SESSION['error'] = "Nihai karar kaydedilirken bir hata oluştu: " . mysqli_error($conn);
    }
    
    header("Location: nihai_karar.php");
    exit();
}

// İlanları getir
$ilanlar_sql = "SELECT * FROM ilanlar ORDER BY ilan_baslangic_tarihi DESC";
$ilanlar_result = mysqli_query($conn, $ilanlar_sql);
$ilanlar = mysqli_fetch_all($ilanlar_result, MYSQLI_ASSOC);

// Filtreleme
$where_clause = "WHERE b.durum = 'Beklemede'";
if (isset($_GET['ilan_id']) && is_numeric($_GET['ilan_id'])) {
    $ilan_id = $_GET['ilan_id'];
    $where_clause .= " AND b.ilan_id = $ilan_id";
}

// Başvuruları getir
$sql = "SELECT b.*, i.ilan_baslik, i.kadro_unvani, k.ad, k.soyad, k.email,
        (SELECT COUNT(*) FROM juri_degerlendirmeleri jd WHERE jd.basvuru_id = b.id) as degerlendirme_sayisi,
        (SELECT COUNT(*) FROM juri_atamalari ja WHERE ja.ilan_id = b.ilan_id) as juri_sayisi
        FROM basvurular b 
        JOIN ilanlar i ON b.ilan_id = i.id 
        JOIN kullanicilar k ON b.aday_id = k.id 
        $where_clause 
        ORDER BY b.basvuru_tarihi DESC";
$result = mysqli_query($conn, $sql);
$basvurular = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nihai Karar - Yönetici Paneli</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Nihai Karar</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-gavel"></i> Nihai Karar</h2>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form action="nihai_karar.php" method="get">
                                    <div class="input-group">
                                        <select class="form-select" name="ilan_id" id="ilan-filtre">
                                            <option value="">Tüm İlanlar</option>
                                            <?php foreach ($ilanlar as $ilan): ?>
                                                <option value="<?php echo $ilan['id']; ?>" <?php echo (isset($_GET['ilan_id']) && $_GET['ilan_id'] == $ilan['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $ilan['ilan_baslik']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-filter"></i> Filtrele
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
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
                        
                        <?php if (count($basvurular) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Aday</th>
                                            <th>İlan</th>
                                            <th>Kadro Ünvanı</th>
                                            <th>Başvuru Tarihi</th>
                                            <th>Değerlendirme</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($basvurular as $basvuru): ?>
                                            <tr>
                                                <td><?php echo $basvuru['id']; ?></td>
                                                <td>
                                                    <?php echo $basvuru['ad'] . ' ' . $basvuru['soyad']; ?><br>
                                                    <small class="text-muted"><?php echo $basvuru['email']; ?></small>
                                                </td>
                                                <td><?php echo $basvuru['ilan_baslik']; ?></td>
                                                <td><?php echo $basvuru['kadro_unvani']; ?></td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($basvuru['basvuru_tarihi'])); ?></td>
                                                <td>
                                                    <?php if ($basvuru['degerlendirme_sayisi'] == 0): ?>
                                                        <span class="badge bg-danger">Değerlendirme Yok</span>
                                                    <?php elseif ($basvuru['degerlendirme_sayisi'] < $basvuru['juri_sayisi']): ?>
                                                        <span class="badge bg-warning"><?php echo $basvuru['degerlendirme_sayisi']; ?>/<?php echo $basvuru['juri_sayisi']; ?> Değerlendirme</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Tüm Değerlendirmeler Tamamlandı</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="../admin/basvuru_detay.php?id=<?php echo $basvuru['id']; ?>" class="btn btn-info btn-sm" title="Detay">
                                                            <i class="fas fa-info-circle"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#kararModal<?php echo $basvuru['id']; ?>" title="Karar Ver">
                                                            <i class="fas fa-gavel"></i>
                                                        </button>
                                                    </div>
                                                    
                                                    <!-- Karar Verme Modal -->
                                                    <div class="modal fade" id="kararModal<?php echo $basvuru['id']; ?>" tabindex="-1" aria-labelledby="kararModalLabel<?php echo $basvuru['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-primary text-white">
                                                                    <h5 class="modal-title" id="kararModalLabel<?php echo $basvuru['id']; ?>">Nihai Karar Ver</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form action="nihai_karar.php" method="post">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="basvuru_id" value="<?php echo $basvuru['id']; ?>">
                                                                        
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Aday:</label>
                                                                            <p class="form-control-static"><?php echo $basvuru['ad'] . ' ' . $basvuru['soyad']; ?></p>
                                                                        </div>
                                                                        
                                                                        <div class="mb-3">
                                                                            <label class="form-label">İlan:</label>
                                                                            <p class="form-control-static"><?php echo $basvuru['ilan_baslik']; ?></p>
                                                                        </div>
                                                                        
                                                                        <div class="mb-3">
                                                                            <label for="durum<?php echo $basvuru['id']; ?>" class="form-label">Karar *</label>
                                                                            <select class="form-select" id="durum<?php echo $basvuru['id']; ?>" name="durum" required>
                                                                                <option value="">Karar Seçiniz</option>
                                                                                <option value="Onaylandı">Onaylandı</option>
                                                                                <option value="Reddedildi">Reddedildi</option>
                                                                            </select>
                                                                        </div>
                                                                        
                                                                        <div class="mb-3">
                                                                            <label for="sonuc_aciklama<?php echo $basvuru['id']; ?>" class="form-label">Açıklama</label>
                                                                            <textarea class="form-control" id="sonuc_aciklama<?php echo $basvuru['id']; ?>" name="sonuc_aciklama" rows="3"></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                                        <button type="submit" class="btn btn-primary">Kararı Kaydet</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Karar verilecek başvuru bulunamadı.
                            </div>
                        <?php endif; ?>
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
