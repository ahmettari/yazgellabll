<?php
session_start();
require_once '../db_connection.php';

// Oturum kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'yonetici') {
    header("Location: ../login.php");
    exit();
}

// İlan ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ilanlar.php");
    exit();
}

$ilan_id = $_GET['id'];

// İlan bilgilerini getir
$sql = "SELECT i.*, k.ad as olusturan_ad, k.soyad as olusturan_soyad 
        FROM ilanlar i 
        LEFT JOIN kullanicilar k ON i.olusturan_id = k.id 
        WHERE i.id = $ilan_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: ilanlar.php");
    exit();
}

$ilan = mysqli_fetch_assoc($result);

// İlana ait başvuruları getir
$basvuru_sql = "SELECT b.*, k.ad, k.soyad, k.email 
                FROM basvurular b 
                JOIN kullanicilar k ON b.kullanici_id = k.id 
                WHERE b.ilan_id = $ilan_id 
                ORDER BY b.basvuru_tarihi DESC";
$basvuru_result = mysqli_query($conn, $basvuru_sql);
$basvurular = mysqli_fetch_all($basvuru_result, MYSQLI_ASSOC);

// İlana atanmış jüri üyelerini getir
$juri_sql = "SELECT j.*, k.ad, k.soyad, k.email 
             FROM juri_atama j 
             JOIN kullanicilar k ON j.juri_id = k.id 
             WHERE j.ilan_id = $ilan_id";
$juri_result = mysqli_query($conn, $juri_sql);
$juri_uyeleri = mysqli_fetch_all($juri_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Detayı - Yönetici Paneli</title>
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
                        <li class="breadcrumb-item active" aria-current="page">İlan Detayı</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-info-circle"></i> İlan Detayı</h2>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group">
                    <a href="ilanlar.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> İlanlara Dön
                    </a>
                    <a href="ilan_duzenle.php?id=<?php echo $ilan_id; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Düzenle
                    </a>
                    <a href="juri_ata.php?ilan_id=<?php echo $ilan_id; ?>" class="btn btn-primary">
                        <i class="fas fa-users"></i> Jüri Ata
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-bullhorn"></i> İlan Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="bg-light" width="40%">İlan Başlığı</th>
                                        <td><?php echo $ilan['ilan_baslik']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Fakülte/Birim</th>
                                        <td><?php echo $ilan['fakulte_birim']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Bölüm</th>
                                        <td><?php echo $ilan['bolum']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Anabilim Dalı</th>
                                        <td><?php echo $ilan['anabilim_dali']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Kadro Ünvanı</th>
                                        <td><?php echo $ilan['kadro_unvani']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr>
                                        <th class="bg-light" width="40%">Kadro Sayısı</th>
                                        <td><?php echo $ilan['kadro_sayisi']; ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">İlan Tarihi</th>
                                        <td><?php echo date('d.m.Y', strtotime($ilan['ilan_baslangic_tarihi'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Son Başvuru Tarihi</th>
                                        <td><?php echo date('d.m.Y', strtotime($ilan['ilan_bitis_tarihi'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Durum</th>
                                        <td>
                                            <?php if (strtotime($ilan['ilan_bitis_tarihi']) >= strtotime(date('Y-m-d')) && $ilan['durum'] == 'aktif'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Oluşturan</th>
                                        <td><?php echo $ilan['olusturan_ad'] . ' ' . $ilan['olusturan_soyad']; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <?php if (!empty($ilan['aciklama'])): ?>
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0">Açıklama</h6>
                                        </div>
                                        <div class="card-body">
                                            <?php echo nl2br($ilan['aciklama']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Jüri Üyeleri -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-users"></i> Atanmış Jüri Üyeleri</h5>
                            <a href="juri_ata.php?ilan_id=<?php echo $ilan_id; ?>" class="btn btn-sm btn-light">
                                <i class="fas fa-user-plus"></i> Jüri Ata
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($juri_uyeleri) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Adı Soyadı</th>
                                            <th>E-posta</th>
                                            <th>Atanma Tarihi</th>
                                            <th>Durum</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($juri_uyeleri as $juri): ?>
                                            <tr>
                                                <td><?php echo $juri['ad'] . ' ' . $juri['soyad']; ?></td>
                                                <td><?php echo $juri['email']; ?></td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($juri['atanma_tarihi'])); ?></td>
                                                <td>
                                                    <?php if ($juri['durum'] == 'aktif'): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Pasif</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Bu ilana henüz jüri üyesi atanmamış.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Başvurular -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt"></i> Başvurular</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($basvurular) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Adı Soyadı</th>
                                            <th>E-posta</th>
                                            <th>Başvuru Tarihi</th>
                                            <th>Durum</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($basvurular as $basvuru): ?>
                                            <tr>
                                                <td><?php echo $basvuru['id']; ?></td>
                                                <td><?php echo $basvuru['ad'] . ' ' . $basvuru['soyad']; ?></td>
                                                <td><?php echo $basvuru['email']; ?></td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($basvuru['basvuru_tarihi'])); ?></td>
                                                <td>
                                                    <?php if ($basvuru['durum'] == 'beklemede'): ?>
                                                        <span class="badge bg-warning">Beklemede</span>
                                                    <?php elseif ($basvuru['durum'] == 'degerlendirildi'): ?>
                                                        <span class="badge bg-info">Değerlendirildi</span>
                                                    <?php elseif ($basvuru['durum'] == 'kabul'): ?>
                                                        <span class="badge bg-success">Kabul Edildi</span>
                                                    <?php elseif ($basvuru['durum'] == 'red'): ?>
                                                        <span class="badge bg-danger">Reddedildi</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="basvuru_detay.php?id=<?php echo $basvuru['id']; ?>" class="btn btn-info btn-sm" title="Detay">
                                                            <i class="fas fa-info-circle"></i>
                                                        </a>
                                                        <a href="nihai_karar.php?id=<?php echo $basvuru['id']; ?>" class="btn btn-primary btn-sm" title="Nihai Karar">
                                                            <i class="fas fa-gavel"></i>
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
                                <i class="fas fa-exclamation-triangle"></i> Bu ilana henüz başvuru yapılmamış.
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
