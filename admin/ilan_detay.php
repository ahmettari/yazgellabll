<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// İlan ID'si kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ilanlar.php");
    exit();
}

$ilan_id = $_GET['id'];

// İlan bilgilerini getir
$sql = "SELECT i.*, k.ad as olusturan_ad, k.soyad as olusturan_soyad, 
        k2.ad as guncelleyen_ad, k2.soyad as guncelleyen_soyad 
        FROM ilanlar i 
        LEFT JOIN kullanicilar k ON i.olusturan_id = k.id 
        LEFT JOIN kullanicilar k2 ON i.guncelleyen_id = k2.id 
        WHERE i.id = $ilan_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: ilanlar.php");
    exit();
}

$ilan = mysqli_fetch_assoc($result);

// İlan kriterlerini getir
$kriter_sql = "SELECT * FROM ilan_kriterleri WHERE ilan_id = $ilan_id";
$kriter_result = mysqli_query($conn, $kriter_sql);
$kriterler = mysqli_fetch_all($kriter_result, MYSQLI_ASSOC);

// İlana yapılan başvuruları getir
$basvuru_sql = "SELECT b.*, k.ad, k.soyad, k.email 
                FROM basvurular b 
                JOIN kullanicilar k ON b.aday_id = k.id 
                WHERE b.ilan_id = $ilan_id 
                ORDER BY b.basvuru_tarihi DESC";
$basvuru_result = mysqli_query($conn, $basvuru_sql);
$basvurular = mysqli_fetch_all($basvuru_result, MYSQLI_ASSOC);

// Jüri atamalarını getir
$juri_sql = "SELECT ja.*, k.ad, k.soyad, k.email 
             FROM juri_atamalari ja 
             JOIN kullanicilar k ON ja.juri_id = k.id 
             WHERE ja.ilan_id = $ilan_id";
$juri_result = mysqli_query($conn, $juri_sql);
$juri_atamalari = mysqli_fetch_all($juri_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Detayı - Admin Paneli</title>
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
                        <li class="breadcrumb-item"><a href="ilanlar.php">İlanlar</a></li>
                        <li class="breadcrumb-item active" aria-current="page">İlan Detayı</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-bullhorn"></i> <?php echo $ilan['ilan_baslik']; ?></h2>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group">
                    <a href="ilan_duzenle.php?id=<?php echo $ilan_id; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Düzenle
                    </a>
                    <a href="javascript:void(0);" onclick="ilanSil(<?php echo $ilan_id; ?>)" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Sil
                    </a>
                    <a href="ilanlar.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Geri
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-info-circle"></i> İlan Bilgileri</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-striped">
                                    <tr>
                                        <th width="40%">Fakülte/Birim:</th>
                                        <td><?php echo $ilan['fakulte_birim']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Bölüm:</th>
                                        <td><?php echo $ilan['bolum']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Anabilim Dalı:</th>
                                        <td><?php echo $ilan['anabilim_dali']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Kadro Ünvanı:</th>
                                        <td><?php echo $ilan['kadro_unvani']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-striped">
                                    <tr>
                                        <th width="40%">İlan Başlangıç:</th>
                                        <td><?php echo date('d.m.Y', strtotime($ilan['ilan_baslangic_tarihi'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>İlan Bitiş:</th>
                                        <td><?php echo date('d.m.Y', strtotime($ilan['ilan_bitis_tarihi'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Durum:</th>
                                        <td>
                                            <?php if (strtotime($ilan['ilan_bitis_tarihi']) >= strtotime(date('Y-m-d')) && $ilan['durum'] == 'aktif'): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Oluşturan:</th>
                                        <td><?php echo $ilan['olusturan_ad'] . ' ' . $ilan['olusturan_soyad']; ?></td>
                                    </tr>
                                    <?php if (!empty($ilan['guncelleyen_ad'])): ?>
                                    <tr>
                                        <th>Güncelleyen:</th>
                                        <td><?php echo $ilan['guncelleyen_ad'] . ' ' . $ilan['guncelleyen_soyad']; ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">İlan Açıklaması</h5>
                                <div class="p-3 bg-light rounded">
                                    <?php echo nl2br($ilan['ilan_aciklama']); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Başvuru Koşulları</h5>
                                <div class="p-3 bg-light rounded">
                                    <?php echo nl2br($ilan['basvuru_kosullari']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-list-check"></i> İlan Kriterleri</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($kriterler) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Kriter Adı</th>
                                            <th>Açıklama</th>
                                            <th>Minimum Değer</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($kriterler as $kriter): ?>
                                            <tr>
                                                <td><?php echo $kriter['kriter_adi']; ?></td>
                                                <td><?php echo $kriter['aciklama']; ?></td>
                                                <td><?php echo $kriter['minimum_deger']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Bu ilan için özel kriter bulunmamaktadır.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-file-alt"></i> Başvurular</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($basvurular) > 0): ?>
                            <div class="list-group">
                                <?php foreach ($basvurular as $basvuru): ?>
                                    <a href="basvuru_detay.php?id=<?php echo $basvuru['id']; ?>" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo $basvuru['ad'] . ' ' . $basvuru['soyad']; ?></h6>
                                            <small>
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
                                            </small>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-alt"></i> <?php echo date('d.m.Y H:i', strtotime($basvuru['basvuru_tarihi'])); ?>
                                        </small>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="basvurular.php?ilan_id=<?php echo $ilan_id; ?>" class="btn btn-outline-success btn-sm">
                                    Tüm Başvuruları Görüntüle
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Bu ilana henüz başvuru yapılmamıştır.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card shadow mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-users"></i> Jüri Atamaları</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($juri_atamalari) > 0): ?>
                            <div class="list-group">
                                <?php foreach ($juri_atamalari as $juri): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo $juri['ad'] . ' ' . $juri['soyad']; ?></h6>
                                            <small><?php echo date('d.m.Y', strtotime($juri['atama_tarihi'])); ?></small>
                                        </div>
                                        <small class="text-muted"><?php echo $juri['email']; ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Bu ilana henüz jüri ataması yapılmamıştır.
                            </div>
                        <?php endif; ?>
                        <div class="text-center mt-3">
                            <a href="../yonetici/juri_ata.php?ilan_id=<?php echo $ilan_id; ?>" class="btn btn-outline-secondary btn-sm">
                                Jüri Atama Sayfasına Git
                            </a>
                        </div>
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
