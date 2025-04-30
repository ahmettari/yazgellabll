<?php
session_start();
require_once '../db_connection.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'yonetici') {
    header("Location: ../login.php");
    exit();
}

// İlan silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $ilan_id = $_GET['sil'];
    
    // İlanı sil
    $sil_sql = "DELETE FROM ilanlar WHERE id = $ilan_id";
    if (mysqli_query($conn, $sil_sql)) {
        $_SESSION['success'] = "İlan başarıyla silindi.";
    } else {
        $_SESSION['error'] = "İlan silinirken bir hata oluştu: " . mysqli_error($conn);
    }
    
    header("Location: ilanlar.php");
    exit();
}

// Filtreleme
$where_clause = "";
if (isset($_GET['durum']) && $_GET['durum'] == 'aktif') {
    $where_clause = "WHERE ilan_bitis_tarihi >= CURDATE() AND durum = 'aktif'";
} elseif (isset($_GET['durum']) && $_GET['durum'] == 'pasif') {
    $where_clause = "WHERE ilan_bitis_tarihi < CURDATE() OR durum != 'aktif'";
}

// Arama
if (isset($_GET['arama']) && !empty($_GET['arama'])) {
    $arama = mysqli_real_escape_string($conn, $_GET['arama']);
    if (empty($where_clause)) {
        $where_clause = "WHERE (ilan_baslik LIKE '%$arama%' OR fakulte_birim LIKE '%$arama%' OR bolum LIKE '%$arama%' OR kadro_unvani LIKE '%$arama%')";
    } else {
        $where_clause .= " AND (ilan_baslik LIKE '%$arama%' OR fakulte_birim LIKE '%$arama%' OR bolum LIKE '%$arama%' OR kadro_unvani LIKE '%$arama%')";
    }
}

// İlanları getir
$sql = "SELECT i.*, k.ad as olusturan_ad, k.soyad as olusturan_soyad 
        FROM ilanlar i 
        LEFT JOIN kullanicilar k ON i.olusturan_id = k.id 
        $where_clause 
        ORDER BY i.olusturma_tarihi DESC";
$result = mysqli_query($conn, $sql);
$ilanlar = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlanlar - Yönetici Paneli</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                        <li class="breadcrumb-item active" aria-current="page">İlanlar</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-bullhorn"></i> İlanlar</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="ilan_ekle.php" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i> Yeni İlan Ekle
                </a>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="btn-group" role="group">
                                    <a href="ilanlar.php" class="btn <?php echo !isset($_GET['durum']) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        Tüm İlanlar
                                    </a>
                                    <a href="ilanlar.php?durum=aktif" class="btn <?php echo (isset($_GET['durum']) && $_GET['durum'] == 'aktif') ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        Aktif İlanlar
                                    </a>
                                    <a href="ilanlar.php?durum=pasif" class="btn <?php echo (isset($_GET['durum']) && $_GET['durum'] == 'pasif') ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        Pasif İlanlar
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <form action="ilanlar.php" method="get" class="d-flex">
                                    <input type="text" name="arama" class="form-control" placeholder="İlan ara..." value="<?php echo isset($_GET['arama']) ? $_GET['arama'] : ''; ?>">
                                    <button type="submit" class="btn btn-outline-primary ms-2">
                                        <i class="fas fa-search"></i>
                                    </button>
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
                        
                        <?php if (count($ilanlar) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>İlan Başlığı</th>
                                            <th>Fakülte/Birim</th>
                                            <th>Bölüm</th>
                                            <th>Kadro Ünvanı</th>
                                            <th>İlan Tarihi</th>
                                            <th>Son Başvuru</th>
                                            <th>Durum</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ilanlar as $ilan): ?>
                                            <tr>
                                                <td><?php echo $ilan['id']; ?></td>
                                                <td><?php echo $ilan['ilan_baslik']; ?></td>
                                                <td><?php echo $ilan['fakulte_birim']; ?></td>
                                                <td><?php echo $ilan['bolum']; ?></td>
                                                <td><?php echo $ilan['kadro_unvani']; ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($ilan['ilan_baslangic_tarihi'])); ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($ilan['ilan_bitis_tarihi'])); ?></td>
                                                <td>
                                                    <?php if (strtotime($ilan['ilan_bitis_tarihi']) >= strtotime(date('Y-m-d')) && $ilan['durum'] == 'aktif'): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Pasif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="ilan_detay.php?id=<?php echo $ilan['id']; ?>" class="btn btn-info btn-sm" title="Detay">
                                                            <i class="fas fa-info-circle"></i>
                                                        </a>
                                                        <a href="ilan_duzenle.php?id=<?php echo $ilan['id']; ?>" class="btn btn-warning btn-sm" title="Düzenle">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="juri_ata.php?ilan_id=<?php echo $ilan['id']; ?>" class="btn btn-primary btn-sm" title="Jüri Ata">
                                                            <i class="fas fa-users"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" onclick="ilanSil(<?php echo $ilan['id']; ?>)" class="btn btn-danger btn-sm" title="Sil">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Kriterlere uygun ilan bulunamadı.
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
    <script>
        function ilanSil(id) {
            if (confirm("Bu ilanı silmek istediğinize emin misiniz?")) {
                window.location.href = "ilanlar.php?sil=" + id;
            }
        }
    </script>
</body>
</html>
