<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'juri') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Jüri üyesinin atandığı ilanları getir
$sql = "SELECT ja.*, i.ilan_baslik, i.fakulte_birim, i.bolum, i.kadro_unvani, i.ilan_bitis_tarihi,
        (SELECT COUNT(*) FROM basvurular b WHERE b.ilan_id = i.id) as basvuru_sayisi,
        (SELECT COUNT(*) FROM juri_degerlendirmeleri jd 
         JOIN basvurular b ON jd.basvuru_id = b.id 
         WHERE b.ilan_id = i.id AND jd.juri_id = $user_id) as degerlendirilen_sayisi
        FROM juri_atamalari ja 
        JOIN ilanlar i ON ja.ilan_id = i.id 
        WHERE ja.juri_id = $user_id 
        ORDER BY i.ilan_bitis_tarihi DESC";
$result = mysqli_query($conn, $sql);
$atanan_ilanlar = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Son değerlendirmeleri getir
$degerlendirme_sql = "SELECT jd.*, b.id as basvuru_id, i.ilan_baslik, k.ad, k.soyad 
                      FROM juri_degerlendirmeleri jd 
                      JOIN basvurular b ON jd.basvuru_id = b.id 
                      JOIN ilanlar i ON b.ilan_id = i.id 
                      JOIN kullanicilar k ON b.aday_id = k.id 
                      WHERE jd.juri_id = $user_id 
                      ORDER BY jd.degerlendirme_tarihi DESC 
                      LIMIT 5";
$degerlendirme_result = mysqli_query($conn, $degerlendirme_sql);
$son_degerlendirmeler = mysqli_fetch_all($degerlendirme_result, MYSQLI_ASSOC);

// İstatistikler
// Toplam atanan ilan sayısı
$ilan_count = count($atanan_ilanlar);

// Toplam başvuru sayısı
$basvuru_sql = "SELECT COUNT(DISTINCT b.id) as toplam 
                FROM basvurular b 
                JOIN juri_atamalari ja ON b.ilan_id = ja.ilan_id 
                WHERE ja.juri_id = $user_id";
$basvuru_result = mysqli_query($conn, $basvuru_sql);
$basvuru_count = mysqli_fetch_assoc($basvuru_result)['toplam'];

// Değerlendirilen başvuru sayısı
$degerlendirilen_sql = "SELECT COUNT(*) as toplam 
                        FROM juri_degerlendirmeleri 
                        WHERE juri_id = $user_id";
$degerlendirilen_result = mysqli_query($conn, $degerlendirilen_sql);
$degerlendirilen_count = mysqli_fetch_assoc($degerlendirilen_result)['toplam'];

// Bekleyen başvuru sayısı
$bekleyen_count = $basvuru_count - $degerlendirilen_count;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jüri Paneli - Akademik Personel Başvuru Sistemi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include("navbar.php"); ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <h4><i class="fas fa-user-tie"></i> Hoş Geldiniz, <?php echo $_SESSION['name']; ?></h4>
                    <p>Jüri paneline giriş yaptınız. Atandığınız ilanlara yapılan başvuruları değerlendirebilirsiniz.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Atanan İlan</h6>
                                <h2 class="mb-0"><?php echo $ilan_count; ?></h2>
                            </div>
                            <i class="fas fa-bullhorn fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Değerlendirilen</h6>
                                <h2 class="mb-0"><?php echo $degerlendirilen_count; ?></h2>
                            </div>
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Bekleyen</h6>
                                <h2 class="mb-0"><?php echo $bekleyen_count; ?></h2>
                            </div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-bullhorn"></i> Atandığınız İlanlar</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($atanan_ilanlar) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>İlan Başlığı</th>
                                            <th>Fakülte/Birim</th>
                                            <th>Kadro Ünvanı</th>
                                            <th>Son Başvuru</th>
                                            <th>Başvuru Sayısı</th>
                                            <th>Değerlendirme Durumu</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($atanan_ilanlar as $ilan): ?>
                                            <tr>
                                                <td><?php echo $ilan['ilan_baslik']; ?></td>
                                                <td><?php echo $ilan['fakulte_birim']; ?></td>
                                                <td><?php echo $ilan['kadro_unvani']; ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($ilan['ilan_bitis_tarihi'])); ?></td>
                                                <td><?php echo $ilan['basvuru_sayisi']; ?></td>
                                                <td>
                                                    <?php if ($ilan['basvuru_sayisi'] == 0): ?>
                                                        <span class="badge bg-secondary">Başvuru Yok</span>
                                                    <?php elseif ($ilan['degerlendirilen_sayisi'] == 0): ?>
                                                        <span class="badge bg-danger">Değerlendirilmedi</span>
                                                    <?php elseif ($ilan['degerlendirilen_sayisi'] < $ilan['basvuru_sayisi']): ?>
                                                        <span class="badge bg-warning"><?php echo $ilan['degerlendirilen_sayisi']; ?>/<?php echo $ilan['basvuru_sayisi']; ?> Değerlendirildi</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Tamamlandı</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="basvurular.php?ilan_id=<?php echo $ilan['ilan_id']; ?>" class="btn btn-info btn-sm">
                                                        <i class="fas fa-list"></i> Başvuruları Gör
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Henüz atandığınız bir ilan bulunmamaktadır.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-check-circle"></i> Son Değerlendirmeleriniz</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($son_degerlendirmeler) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Aday</th>
                                            <th>İlan</th>
                                            <th>Değerlendirme Tarihi</th>
                                            <th>Sonuç</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($son_degerlendirmeler as $degerlendirme): ?>
                                            <tr>
                                                <td><?php echo $degerlendirme['ad'] . ' ' . $degerlendirme['soyad']; ?></td>
                                                <td><?php echo $degerlendirme['ilan_baslik']; ?></td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($degerlendirme['degerlendirme_tarihi'])); ?></td>
                                                <td>
                                                    <?php if ($degerlendirme['sonuc'] == 'Olumlu'): ?>
                                                        <span class="badge bg-success">Olumlu</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Olumsuz</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="degerlendirme_yap.php?id=<?php echo $degerlendirme['basvuru_id']; ?>" class="btn btn-info btn-sm">
                                                        <i class="fas fa-eye"></i> Görüntüle
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="basvurular.php" class="btn btn-success">Tüm Başvuruları Görüntüle</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Henüz değerlendirme yapmadınız.
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
