<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'yonetici') {
    header("Location: ../index.php");
    exit();
}

// Jüri atama silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $atama_id = $_GET['sil'];
    
    // Jüri atamasını sil
    $sil_sql = "DELETE FROM juri_atamalari WHERE id = $atama_id";
    if (mysqli_query($conn, $sil_sql)) {
        $_SESSION['success'] = "Jüri ataması başarıyla silindi.";
    } else {
        $_SESSION['error'] = "Jüri ataması silinirken bir hata oluştu: " . mysqli_error($conn);
    }
    
    // Eğer ilan_id parametresi varsa, o ilana geri dön
    if (isset($_GET['ilan_id'])) {
        header("Location: juri_ata.php?ilan_id=" . $_GET['ilan_id']);
    } else {
        header("Location: juri_ata.php");
    }
    exit();
}

// İlanları getir
$ilanlar_sql = "SELECT * FROM ilanlar ORDER BY ilan_baslangic_tarihi DESC";
$ilanlar_result = mysqli_query($conn, $ilanlar_sql);
$ilanlar = mysqli_fetch_all($ilanlar_result, MYSQLI_ASSOC);

// Jüri üyelerini getir
$juri_sql = "SELECT * FROM kullanicilar WHERE rol = 'juri' AND durum = 1 ORDER BY ad, soyad";
$juri_result = mysqli_query($conn, $juri_sql);
$juri_uyeleri = mysqli_fetch_all($juri_result, MYSQLI_ASSOC);

// Filtreleme
$where_clause = "";
if (isset($_GET['ilan_id']) && is_numeric($_GET['ilan_id'])) {
    $ilan_id = $_GET['ilan_id'];
    $where_clause = "WHERE ja.ilan_id = $ilan_id";
}

// Jüri atamalarını getir
$sql = "SELECT ja.*, i.ilan_baslik, k.ad, k.soyad, k.email 
        FROM juri_atamalari ja 
        JOIN ilanlar i ON ja.ilan_id = i.id 
        JOIN kullanicilar k ON ja.juri_id = k.id 
        $where_clause 
        ORDER BY ja.atama_tarihi DESC";
$result = mysqli_query($conn, $sql);
$juri_atamalari = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Jüri atama işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al
    $ilan_id = mysqli_real_escape_string($conn, $_POST['ilan_id']);
    $juri_id = mysqli_real_escape_string($conn, $_POST['juri_id']);
    
    // Aynı jüri üyesi aynı ilana atanmış mı kontrol et
    $kontrol_sql = "SELECT * FROM juri_atamalari WHERE ilan_id = $ilan_id AND juri_id = $juri_id";
    $kontrol_result = mysqli_query($conn, $kontrol_sql);
    
    if (mysqli_num_rows($kontrol_result) > 0) {
        $_SESSION['error'] = "Bu jüri üyesi zaten bu ilana atanmış.";
    } else {
        // Jüri atamasını veritabanına ekle
        $user_id = $_SESSION['user_id'];
        $insert_sql = "INSERT INTO juri_atamalari (ilan_id, juri_id, atama_tarihi, atayan_id) 
                      VALUES ($ilan_id, $juri_id, NOW(), $user_id)";
        
        if (mysqli_query($conn, $insert_sql)) {
            $_SESSION['success'] = "Jüri ataması başarıyla yapıldı.";
            header("Location: juri_ata.php?ilan_id=$ilan_id");
            exit();
        } else {
            $_SESSION['error'] = "Jüri ataması yapılırken bir hata oluştu: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jüri Ata - Yönetici Paneli</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Jüri Ata</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-users"></i> Jüri Atamaları</h2>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#juriAtaModal">
                    <i class="fas fa-plus-circle"></i> Yeni Jüri Ata
                </button>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form action="juri_ata.php" method="get">
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
                        
                        <?php if (count($juri_atamalari) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>İlan</th>
                                            <th>Jüri Üyesi</th>
                                            <th>E-posta</th>
                                            <th>Atama Tarihi</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($juri_atamalari as $atama): ?>
                                            <tr>
                                                <td><?php echo $atama['id']; ?></td>
                                                <td><?php echo $atama['ilan_baslik']; ?></td>
                                                <td><?php echo $atama['ad'] . ' ' . $atama['soyad']; ?></td>
                                                <td><?php echo $atama['email']; ?></td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($atama['atama_tarihi'])); ?></td>
                                                <td>
                                                    <a href="javascript:void(0);" onclick="juriAtamaSil(<?php echo $atama['id']; ?>, <?php echo isset($_GET['ilan_id']) ? $_GET['ilan_id'] : 'null'; ?>)" class="btn btn-danger btn-sm" title="Sil">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Kriterlere uygun jüri ataması bulunamadı.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Jüri Atama Modal -->
    <div class="modal fade" id="juriAtaModal" tabindex="-1" aria-labelledby="juriAtaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="juriAtaModalLabel">Yeni Jüri Ata</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="juri_ata.php" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="ilan_id" class="form-label">İlan *</label>
                            <select class="form-select" id="ilan_id" name="ilan_id" required>
                                <option value="">İlan Seçiniz</option>
                                <?php foreach ($ilanlar as $ilan): ?>
                                    <option value="<?php echo $ilan['id']; ?>" <?php echo (isset($_GET['ilan_id']) && $_GET['ilan_id'] == $ilan['id']) ? 'selected' : ''; ?>>
                                        <?php echo $ilan['ilan_baslik']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="juri_id" class="form-label">Jüri Üyesi *</label>
                            <select class="form-select" id="juri_id" name="juri_id" required>
                                <option value="">Jüri Üyesi Seçiniz</option>
                                <?php foreach ($juri_uyeleri as $juri): ?>
                                    <option value="<?php echo $juri['id']; ?>">
                                        <?php echo $juri['ad'] . ' ' . $juri['soyad'] . ' (' . $juri['email'] . ')'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Jüri Ata</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <footer class="text-center mt-4 mb-4">
        <p>&copy; <?php echo date("Y"); ?> Kocaeli Üniversitesi - Tüm Hakları Saklıdır.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function juriAtamaSil(id, ilan_id) {
            if (confirm("Bu jüri atamasını silmek istediğinize emin misiniz?")) {
                let url = "juri_ata.php?sil=" + id;
                if (ilan_id !== null) {
                    url += "&ilan_id=" + ilan_id;
                }
                window.location.href = url;
            }
        }
    </script>
</body>
</html>
