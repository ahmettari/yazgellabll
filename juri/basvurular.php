<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'juri') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Filtreleme
$where_clause = "WHERE ja.juri_id = $user_id";
if (isset($_GET['ilan_id']) && is_numeric($_GET['ilan_id'])) {
    $ilan_id = $_GET['ilan_id'];
    $where_clause .= " AND ja.ilan_id = $ilan_id";
}

if (isset($_GET['durum']) && !empty($_GET['durum'])) {
    if ($_GET['durum'] == 'degerlendirilmemis') {
        $where_clause .= " AND jd.id IS NULL";
    } elseif ($_GET['durum'] == 'degerlendirilmis') {
        $where_clause .= " AND jd.id IS NOT NULL";
    }
}

// İlanları getir (filtreleme için)
$ilanlar_sql = "SELECT i.id, i.ilan_baslik 
                FROM ilanlar i 
                JOIN juri_atamalari ja ON i.id = ja.ilan_id 
                WHERE ja.juri_id = $user_id 
                ORDER BY i.ilan_baslangic_tarihi DESC";
$ilanlar_result = mysqli_query($conn, $ilanlar_sql);
$ilanlar = mysqli_fetch_all($ilanlar_result, MYSQLI_ASSOC);

// Başvuruları getir
$sql = "SELECT b.*, i.ilan_baslik, i.kadro_unvani, k.ad, k.soyad, k.email, jd.id as degerlendirme_id, jd.sonuc 
        FROM juri_atamalari ja 
        JOIN ilanlar i ON ja.ilan_id = i.id 
        JOIN basvurular b ON i.id = b.ilan_id 
        JOIN kullanicilar k ON b.aday_id = k.id 
        LEFT JOIN juri_degerlendirmeleri jd ON b.id = jd.basvuru_id AND jd.juri_id = $user_id 
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
    <title>Başvurular - Jüri Paneli</title>
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
                <h2><i class="fas fa-clipboard-list"></i> Değerlendirilecek Başvurular</h2>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form action="basvurular.php" method="get">
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
                            <div class="col-md-6">
                                <div class="btn-group w-100">
                                    <a href="basvurular.php<?php echo isset($_GET['ilan_id']) ? '?ilan_id='.$_GET['ilan_id'] : ''; ?>" class="btn btn-outline-secondary <?php echo !isset($_GET['durum']) ? 'active' : ''; ?>">Tümü</a>
                                    <a href="basvurular.php?durum=degerlendirilmemis<?php echo isset($_GET['ilan_id']) ? '&ilan_id='.$_GET['ilan_id'] : ''; ?>" class="btn btn-outline-danger <?php echo (isset($_GET['durum']) && $_GET['durum'] == 'degerlendirilmemis') ? 'active' : ''; ?>">Değerlendirilmemiş</a>
                                    <a href="basvurular.php?durum=degerlendirilmis<?php echo isset($_GET['ilan_id']) ? '&ilan_id='.$_GET['ilan_id'] : ''; ?>" class="btn btn-outline-success <?php echo (isset($_GET['durum']) && $_GET['durum'] == 'degerlendirilmis') ? 'active' : ''; ?>">Değerlendirilmiş</a>
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
                                                    <?php if (empty($basvuru['degerlendirme_id'])): ?>
                                                        <span class="badge bg-danger">Değerlendirilmedi</span>
                                                    <?php else: ?>
                                                        <?php if ($basvuru['sonuc'] == 'Olumlu'): ?>
                                                            <span class="badge bg-success">Olumlu</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Olumsuz</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="degerlendirme_yap.php?id=<?php echo $basvuru['id']; ?>" class="btn btn-primary btn-sm" title="Değerlendir">
                                                        <?php if (empty($basvuru['degerlendirme_id'])): ?>
                                                            <i class="fas fa-clipboard-check"></i> Değerlendir
                                                        <?php else: ?>
                                                            <i class="fas fa-edit"></i> Düzenle
                                                        <?php endif; ?>
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
</body>
</html>
