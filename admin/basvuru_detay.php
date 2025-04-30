<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Başvuru ID'si kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: basvurular.php");
    exit();
}

$basvuru_id = $_GET['id'];

// Başvuru bilgilerini getir
$sql = "SELECT b.*, i.ilan_baslik, i.kadro_unvani, i.fakulte_birim, i.bolum, i.anabilim_dali, 
        k.ad, k.soyad, k.tc_kimlik_no, k.email, k.telefon 
        FROM basvurular b 
        JOIN ilanlar i ON b.ilan_id = i.id 
        JOIN kullanicilar k ON b.aday_id = k.id 
        WHERE b.id = $basvuru_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: basvurular.php");
    exit();
}

$basvuru = mysqli_fetch_assoc($result);

// Başvuru kriterlerini getir
$kriter_sql = "SELECT bk.*, ik.kriter_adi, ik.aciklama, ik.minimum_deger 
               FROM basvuru_kriterleri bk 
               JOIN ilan_kriterleri ik ON bk.kriter_id = ik.id 
               WHERE bk.basvuru_id = $basvuru_id";
$kriter_result = mysqli_query($conn, $kriter_sql);
$kriterler = mysqli_fetch_all($kriter_result, MYSQLI_ASSOC);

// Jüri değerlendirmelerini getir
$juri_sql = "SELECT jd.*, k.ad, k.soyad 
             FROM juri_degerlendirmeleri jd 
             JOIN kullanicilar k ON jd.juri_id = k.id 
             WHERE jd.basvuru_id = $basvuru_id";
$juri_result = mysqli_query($conn, $juri_sql);
$juri_degerlendirmeleri = mysqli_fetch_all($juri_result, MYSQLI_ASSOC);

// Başvuru durumu güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['durum'])) {
    $durum = mysqli_real_escape_string($conn, $_POST['durum']);
    $sonuc_aciklama = mysqli_real_escape_string($conn, $_POST['sonuc_aciklama']);
    
    $update_sql = "UPDATE basvurular SET durum = '$durum', sonuc_aciklama = '$sonuc_aciklama', sonuc_tarihi = NOW() WHERE id = $basvuru_id";
    
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['success'] = "Başvuru durumu başarıyla güncellendi.";
        header("Location: basvuru_detay.php?id=$basvuru_id");
        exit();
    } else {
        $_SESSION['error'] = "Başvuru durumu güncellenirken bir hata oluştu: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Başvuru Detayı - Admin Paneli</title>
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
                        <li class="breadcrumb-item"><a href="basvurular.php">Başvurular</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Başvuru Detayı</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-file-alt"></i> Başvuru Detayı</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="basvurular.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
            </div>
        </div>
        
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
        
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-user"></i> Aday Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <tr>
                                <th width="40%">Ad Soyad:</th>
                                <td><?php echo $basvuru['ad'] . ' ' . $basvuru['soyad']; ?></td>
                            </tr>
                            <tr>
                                <th>TC Kimlik No:</th>
                                <td><?php echo $basvuru['tc_kimlik_no']; ?></td>
                            </tr>
                            <tr>
                                <th>E-posta:</th>
                                <td><?php echo $basvuru['email']; ?></td>
                            </tr>
                            <tr>
                                <th>Telefon:</th>
                                <td><?php echo $basvuru['telefon']; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-bullhorn"></i> İlan Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <tr>
                                <th width="40%">İlan Başlığı:</th>
                                <td><?php echo $basvuru['ilan_baslik']; ?></td>
                            </tr>
                            <tr>
                                <th>Fakülte/Birim:</th>
                                <td><?php echo $basvuru['fakulte_birim']; ?></td>
                            </tr>
                            <tr>
                                <th>Bölüm:</th>
                                <td><?php echo
typescriptreact file="admin/kullanicilar.php"
<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Kullanıcı silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $kullanici_id = $_GET['sil'];
    
    // Kullanıcıyı sil
    $sil_sql = "DELETE FROM kullanicilar WHERE id = $kullanici_id";
    if (mysqli_query($conn, $sil_sql)) {
        $_SESSION['success'] = "Kullanıcı başarıyla silindi.";
    } else {
        $_SESSION['error'] = "Kullanıcı silinirken bir hata oluştu: " . mysqli_error($conn);
    }
    
    header("Location: kullanicilar.php");
    exit();
}

// Kullanıcı aktiflik durumu değiştirme
if (isset($_GET['aktif']) && is_numeric($_GET['aktif']) && isset($_GET['durum'])) {
    $kullanici_id = $_GET['aktif'];
    $durum = ($_GET['durum'] == '1') ? 1 : 0;
    
    $update_sql = "UPDATE kullanicilar SET aktif = $durum WHERE id = $kullanici_id";
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['success'] = "Kullanıcı durumu başarıyla güncellendi.";
    } else {
        $_SESSION['error'] = "Kullanıcı durumu güncellenirken bir hata oluştu: " . mysqli_error($conn);
    }
    
    header("Location: kullanicilar.php");
    exit();
}

// Filtreleme
$where_clause = "";
if (isset($_GET['rol']) && !empty($_GET['rol'])) {
    $rol = mysqli_real_escape_string($conn, $_GET['rol']);
    $where_clause = "WHERE rol = '$rol'";
}

// Arama
if (isset($_GET['arama']) && !empty($_GET['arama'])) {
    $arama = mysqli_real_escape_string($conn, $_GET['arama']);
    if (empty($where_clause)) {
        $where_clause = "WHERE (ad LIKE '%$arama%' OR soyad LIKE '%$arama%' OR tc_kimlik_no LIKE '%$arama%' OR email LIKE '%$arama%')";
    } else {
        $where_clause .= " AND (ad LIKE '%$arama%' OR soyad LIKE '%$arama%' OR tc_kimlik_no LIKE '%$arama%' OR email LIKE '%$arama%')";
    }
}

// Kullanıcıları getir
$sql = "SELECT * FROM kullanicilar $where_clause ORDER BY kayit_tarihi DESC";
$result = mysqli_query($conn, $sql);
$kullanicilar = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcılar - Admin Paneli</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Kullanıcılar</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-users"></i> Kullanıcılar</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="kullanici_ekle.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Yeni Kullanıcı Ekle
                </a>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <form action="kullanicilar.php" method="get" class="d-flex">
                                    <input type="text" name="arama" class="form-control me-2" placeholder="Kullanıcı ara..." value="<?php echo isset($_GET['arama']) ? $_GET['arama'] : ''; ?>">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-search"></i> Ara
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <div class="btn-group w-100">
                                    <a href="kullanicilar.php" class="btn btn-outline-secondary <?php echo !isset($_GET['rol']) ? 'active' : ''; ?>">Tümü</a>
                                    <a href="kullanicilar.php?rol=aday" class="btn btn-outline-primary <?php echo (isset($_GET['rol']) && $_GET['rol'] == 'aday') ? 'active' : ''; ?>">Adaylar</a>
                                    <a href="kullanicilar.php?rol=juri" class="btn btn-outline-info <?php echo (isset($_GET['rol']) && $_GET['rol'] == 'juri') ? 'active' : ''; ?>">Jüri</a>
                                    <a href="kullanicilar.php?rol=yonetici" class="btn btn-outline-success <?php echo (isset($_GET['rol']) && $_GET['rol'] == 'yonetici') ? 'active' : ''; ?>">Yöneticiler</a>
                                    <a href="kullanicilar.php?rol=admin" class="btn btn-outline-danger <?php echo (isset($_GET['rol']) && $_GET['rol'] == 'admin') ? 'active' : ''; ?>">Adminler</a>
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
                        
                        <?php if (count($kullanicilar) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>TC Kimlik No</th>
                                            <th>Ad Soyad</th>
                                            <th>E-posta</th>
                                            <th>Telefon</th>
                                            <th>Rol</th>
                                            <th>Kayıt Tarihi</th>
                                            <th>Durum</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($kullanicilar as $kullanici): ?>
                                            <tr>
                                                <td><?php echo $kullanici['id']; ?></td>
                                                <td><?php echo $kullanici['tc_kimlik_no']; ?></td>
                                                <td><?php echo $kullanici['ad'] . ' ' . $kullanici['soyad']; ?></td>
                                                <td><?php echo $kullanici['email']; ?></td>
                                                <td><?php echo $kullanici['telefon']; ?></td>
                                                <td>
                                                    <?php 
                                                    switch ($kullanici['rol']) {
                                                        case 'aday':
                                                            echo '<span class="badge bg-primary">Aday</span>';
                                                            break;
                                                        case 'admin':
                                                            echo '<span class="badge bg-danger">Admin</span>';
                                                            break;
                                                        case 'yonetici':
                                                            echo '<span class="badge bg-success">Yönetici</span>';
                                                            break;
                                                        case 'juri':
                                                            echo '<span class="badge bg-info">Jüri</span>';
                                                            break;
                                                        default:
                                                            echo '<span class="badge bg-secondary">Belirsiz</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo date('d.m.Y', strtotime($kullanici['kayit_tarihi'])); ?></td>
                                                <td>
                                                    <?php if ($kullanici['aktif'] == 1): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Pasif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="kullanici_duzenle.php?id=<?php echo $kullanici['id']; ?>" class="btn btn-warning btn-sm" title="Düzenle">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($kullanici['aktif'] == 1): ?>
                                                            <a href="kullanicilar.php?aktif=<?php echo $kullanici['id']; ?>&durum=0" class="btn btn-secondary btn-sm" title="Pasif Yap">
                                                                <i class="fas fa-toggle-off"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="kullanicilar.php?aktif=<?php echo $kullanici['id']; ?>&durum=1" class="btn btn-success btn-sm" title="Aktif Yap">
                                                                <i class="fas fa-toggle-on"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <a href="javascript:void(0);" onclick="kullaniciSil(<?php echo $kullanici['id']; ?>)" class="btn btn-danger btn-sm" title="Sil">
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
                                <i class="fas fa-exclamation-triangle"></i> Kriterlere uygun kullanıcı bulunamadı.
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
        function kullaniciSil(id) {
            if (confirm("Bu kullanıcıyı silmek istediğinize emin misiniz?")) {
                window.location.href = "kullanicilar.php?sil=" + id;
            }
        }
    </script>
</body>
</html>
