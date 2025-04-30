<?php
require_once '../db_connection.php';
include 'navbar.php';

// İstatistikler için sorguları çalıştır
// Toplam ilan sayısı
$ilan_sql = "SELECT COUNT(*) as toplam FROM ilanlar";
$ilan_result = mysqli_query($conn, $ilan_sql);
$ilan_row = mysqli_fetch_assoc($ilan_result);
$toplam_ilan = $ilan_row['toplam'];

// Aktif ilan sayısı
$aktif_ilan_sql = "SELECT COUNT(*) as aktif FROM ilanlar WHERE durum = 'aktif' AND ilan_bitis_tarihi >= CURDATE()";
$aktif_ilan_result = mysqli_query($conn, $aktif_ilan_sql);
$aktif_ilan_row = mysqli_fetch_assoc($aktif_ilan_result);
$aktif_ilan = $aktif_ilan_row['aktif'];

// Toplam başvuru sayısı
$basvuru_sql = "SELECT COUNT(*) as toplam FROM basvurular";
$basvuru_result = mysqli_query($conn, $basvuru_sql);
$basvuru_row = mysqli_fetch_assoc($basvuru_result);
$toplam_basvuru = $basvuru_row['toplam'];

// Değerlendirme bekleyen başvuru sayısı
$bekleyen_sql = "SELECT COUNT(*) as bekleyen FROM basvurular WHERE durum = 'Değerlendirmede'";
$bekleyen_result = mysqli_query($conn, $bekleyen_sql);
$bekleyen_row = mysqli_fetch_assoc($bekleyen_result);
$bekleyen_basvuru = $bekleyen_row['bekleyen'];

// Toplam kullanıcı sayısı
$kullanici_sql = "SELECT COUNT(*) as toplam FROM kullanicilar";
$kullanici_result = mysqli_query($conn, $kullanici_sql);
$kullanici_row = mysqli_fetch_assoc($kullanici_result);
$toplam_kullanici = $kullanici_row['toplam'];

// Son 5 başvuru
$son_basvurular_sql = "SELECT b.*, k.ad, k.soyad, i.fakulte_birim, i.bolum, i.kadro_unvani 
                      FROM basvurular b 
                      JOIN kullanicilar k ON b.aday_id = k.id 
                      JOIN ilanlar i ON b.ilan_id = i.id 
                      ORDER BY b.basvuru_tarihi DESC LIMIT 5";
$son_basvurular_result = mysqli_query($conn, $son_basvurular_sql);

// Son 5 ilan
$son_ilanlar_sql = "SELECT * FROM ilanlar ORDER BY ilan_baslangic_tarihi DESC LIMIT 5";
$son_ilanlar_result = mysqli_query($conn, $son_ilanlar_sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Paneli - Akademik Personel Başvuru Sistemi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="mb-4">Admin Paneli</h2>
        
        <!-- İstatistik Kartları -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Toplam İlan</h6>
                                <h2 class="mb-0"><?php echo $toplam_ilan; ?></h2>
                            </div>
                            <i class="fas fa-bullhorn fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="ilanlar.php" class="text-white text-decoration-none">Detayları Gör</a>
                        <i class="fas fa-arrow-circle-right text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Aktif İlan</h6>
                                <h2 class="mb-0"><?php echo $aktif_ilan; ?></h2>
                            </div>
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="ilanlar.php?durum=aktif" class="text-white text-decoration-none">Detayları Gör</a>
                        <i class="fas fa-arrow-circle-right text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Toplam Başvuru</h6>
                                <h2 class="mb-0"><?php echo $toplam_basvuru; ?></h2>
                            </div>
                            <i class="fas fa-file-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="basvurular.php" class="text-white text-decoration-none">Detayları Gör</a>
                        <i class="fas fa-arrow-circle-right text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Toplam Kullanıcı</h6>
                                <h2 class="mb-0"><?php echo $toplam_kullanici; ?></h2>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="kullanicilar.php" class="text-white text-decoration-none">Detayları Gör</a>
                        <i class="fas fa-arrow-circle-right text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Son Başvurular ve İlanlar -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Son Başvurular</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Aday</th>
                                        <th>Fakülte/Bölüm</th>
                                        <th>Kadro</th>
                                        <th>Durum</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($son_basvurular_result) > 0): ?>
                                        <?php while ($basvuru = mysqli_fetch_assoc($son_basvurular_result)): ?>
                                            <tr>
                                                <td><?php echo $basvuru['ad'] . ' ' . $basvuru['soyad']; ?></td>
                                                <td><?php echo $basvuru['fakulte_birim'] . '/' . $basvuru['bolum']; ?></td>
                                                <td><?php echo $basvuru['kadro_unvani']; ?></td>
                                                <td>
                                                    <?php 
                                                    $durum_class = '';
                                                    switch ($basvuru['durum']) {
                                                        case 'Değerlendirmede':
                                                            $durum_class = 'bg-warning';
                                                            break;
                                                        case 'Kabul Edildi':
                                                            $durum_class = 'bg-success';
                                                            break;
                                                        case 'Reddedildi':
                                                            $durum_class = 'bg-danger';
                                                            break;
                                                        default:
                                                            $durum_class = 'bg-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $durum_class; ?>"><?php echo $basvuru['durum']; ?></span>
                                                </td>
                                                <td>
                                                    <a href="basvuru_detay.php?id=<?php echo $basvuru['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Henüz başvuru bulunmamaktadır.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="basvurular.php" class="btn btn-primary btn-sm">Tüm Başvuruları Gör</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-bullhorn me-2"></i>Son İlanlar</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Fakülte/Bölüm</th>
                                        <th>Kadro</th>
                                        <th>Son Başvuru</th>
                                        <th>Durum</th>
                                        <th>İşlem</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($son_ilanlar_result) > 0): ?>
                                        <?php while ($ilan = mysqli_fetch_assoc($son_ilanlar_result)): ?>
                                            <tr>
                                                <td><?php echo $ilan['fakulte_birim'] . '/' . $ilan['bolum']; ?></td>
                                                <td><?php echo $ilan['kadro_unvani']; ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($ilan['ilan_bitis_tarihi'])); ?></td>
                                                <td>
                                                    <?php 
                                                    $durum_badge = $ilan['durum'] == 'aktif' ? 'bg-success' : 'bg-secondary';
                                                    $durum_text = $ilan['durum'] == 'aktif' ? 'Aktif' : 'Pasif';
                                                    
                                                    // Son başvuru tarihi geçmiş mi kontrol et
                                                    if ($ilan['durum'] == 'aktif' && strtotime($ilan['ilan_bitis_tarihi']) < strtotime(date('Y-m-d'))) {
                                                        $durum_badge = 'bg-warning';
                                                        $durum_text = 'Süresi Dolmuş';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $durum_badge; ?>"><?php echo $durum_text; ?></span>
                                                </td>
                                                <td>
                                                    <a href="ilan_detay.php?id=<?php echo $ilan['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Henüz ilan bulunmamaktadır.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end mt-3">
                            <a href="ilanlar.php" class="btn btn-success btn-sm">Tüm İlanları Gör</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>