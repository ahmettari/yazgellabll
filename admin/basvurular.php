<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Filtreleme
$where_clause = "";
if (isset($_GET['durum']) && !empty($_GET['durum'])) {
    $durum = mysqli_real_escape_string($conn, $_GET['durum']);
    $where_clause = "WHERE b.durum = '$durum'";
}

if (isset($_GET['ilan_id']) && is_numeric($_GET['ilan_id'])) {
    $ilan_id = $_GET['ilan_id'];
    if (empty($where_clause)) {
        $where_clause = "WHERE b.ilan_id = $ilan_id";
    } else {
        $where_clause .= " AND b.ilan_id = $ilan_id";
    }
}

// Arama
if (isset($_GET['arama']) && !empty($_GET['arama'])) {
    $arama = mysqli_real_escape_string($conn, $_GET['arama']);
    if (empty($where_clause)) {
        $where_clause = "WHERE (k.ad LIKE '%$arama%' OR k.soyad LIKE '%$arama%' OR k.tc_kimlik_no LIKE '%$arama%' OR i.ilan_baslik LIKE '%$arama%')";
    } else {
        $where_clause .= " AND (k.ad LIKE '%$arama%' OR k.soyad LIKE '%$arama%' OR k.tc_kimlik_no LIKE '%$arama%' OR i.ilan_baslik LIKE '%$arama%')";
    }
}

// Başvuruları getir
$sql = "SELECT b.*, i.ilan_baslik, i.kadro_unvani, k.ad, k.soyad, k.tc_kimlik_no, k.email 
        FROM basvurular b 
        JOIN ilanlar i ON b.ilan_id = i.id 
        JOIN kullanicilar k ON b.aday_id = k.id 
        $where_clause 
        ORDER BY b.basvuru_tarihi DESC";
$result = mysqli_query($conn, $sql);
$basvurular = mysqli_fetch_all($result, MYSQLI_ASSOC);

// İlanları getir (filtreleme için)
$ilanlar_sql = "SELECT id, ilan_baslik FROM ilanlar ORDER BY ilan_baslangic_tarihi DESC";
$ilanlar_result = mysqli_query($conn, $ilanlar_sql);
$ilanlar = mysqli_fetch_all($ilanlar_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Başvurular - Admin Paneli</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Başvurular</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-file-alt"></i> Başvurular</h2>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form action="basvurular.php" method="get" class="d-flex">
                                    <input type="text" name="arama" class="form-control me-2" placeholder="Başvuru ara..." value="<?php echo isset($_GET['arama']) ? $_GET['arama'] : ''; ?>">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i> Ara
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <select class="form-select" id="durum-filtre">
                                            <option value="">Tüm Durumlar</option>
                                            <option value="Beklemede" <?php echo (isset($_GET['durum']) && $_GET['durum'] == 'Beklemede') ? 'selected' : ''; ?>>Beklemede</option>
                                            <option value="Onaylandı" <?php echo (isset($_GET['durum']) && $_GET['durum'] == 'Onaylandı') ? 'selected' : ''; ?>>Onaylandı</option>
                                            <option value="Reddedildi" <?php echo (isset($_GET['durum']) && $_GET['durum'] == 'Reddedildi') ? 'selected' : ''; ?>>Reddedildi</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <select class="form-select" id="ilan-filtre">
                                            <option value="">Tüm İlanlar</option>
                                            <?php foreach ($ilanlar as $ilan): ?>
                                                <option value="<?php echo $ilan['id']; ?>" <?php echo (isset($_GET['ilan_id']) && $_GET['ilan_id'] == $ilan['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $ilan['ilan_baslik']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
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
                                            <th>Durum</th>
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
                                                    <?php 
                                                    switch ($basvuru['durum']) {
                                                        case 'Beklemede':
                                                            echo '<span class="badge bg-warning">Beklemede</span>';
                                                            break;
                                                        case 'Onaylandı':
                                                            echo '<span class="badge bg-success">Onaylandı</span>';
                                                            break;
                                                        case 'Reddedildi':
                                                            echo '<span class="badge bg-danger">Reddedildi</span>';
                                                            break;
                                                        default:
                                                            echo '<span class="badge bg-secondary">Belirsiz</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a href="basvuru_detay.php?id=<?php echo $basvuru['id']; ?>" class="btn btn-info btn-sm" title="Detay">
                                                        <i class="fas fa-info-circle"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Kriterlere uygun başvuru bulunamadı.
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
        document.addEventListener('DOMContentLoaded', function() {
            // Durum filtresi değiştiğinde
            document.getElementById('durum-filtre').addEventListener('change', function() {
                filterBasvurular();
            });
            
            // İlan filtresi değiştiğinde
            document.getElementById('ilan-filtre').addEventListener('change', function() {
                filterBasvurular();
            });
            
            function filterBasvurular() {
                const durum = document.getElementById('durum-filtre').value;
                const ilan = document.getElementById('ilan-filtre').value;
                const arama = "<?php echo isset($_GET['arama']) ? $_GET['arama'] : ''; ?>";
                
                let url = "basvurular.php?";
                
                if (durum) {
                    url += "durum=" + durum + "&";
                }
                
                if (ilan) {
                    url += "ilan_id=" + ilan + "&";
                }
                
                if (arama) {
                    url += "arama=" + arama;
                }
                
                // Son karakteri kontrol et ve gerekirse & işaretini kaldır
                if (url.endsWith("&")) {
                    url = url.slice(0, -1);
                }
                
                window.location.href = url;
            }
        });
    </script>
</body>
</html>
