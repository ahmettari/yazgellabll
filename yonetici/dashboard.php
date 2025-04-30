<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'yonetici') {
    header("Location: ../index.php");
    exit();
}

// İstatistikleri getir
// Toplam ilan sayısı
$ilan_sql = "SELECT COUNT(*) as toplam FROM ilanlar";
$ilan_result = mysqli_query($conn, $ilan_sql);
$ilan_count = mysqli_fetch_assoc($ilan_result)['toplam'];

// Aktif ilan sayısı
$aktif_ilan_sql = "SELECT COUNT(*) as toplam FROM ilanlar WHERE ilan_bitis_tarihi >= CURDATE()";
$aktif_ilan_result = mysqli_query($conn, $aktif_ilan_sql);
$aktif_ilan_count = mysqli_fetch_assoc($aktif_ilan_result)['toplam'];

// Toplam başvuru sayısı
$basvuru_sql = "SELECT COUNT(*) as toplam FROM basvurular";
$basvuru_result = mysqli_query($conn, $basvuru_sql);
$basvuru_count = mysqli_fetch_assoc($basvuru_result)['toplam'];

// Jüri atanmış ilan sayısı
$juri_ilan_sql = "SELECT COUNT(DISTINCT ilan_id) as toplam FROM juri_atamalari";
$juri_ilan_result = mysqli_query($conn, $juri_ilan_sql);
$juri_ilan_count = mysqli_fetch_assoc($juri_ilan_result)['toplam'];

// Son ilanları getir
$son_ilanlar_sql = "SELECT * FROM ilanlar ORDER BY ilan_baslangic_tarihi DESC LIMIT 5";
$son_ilanlar_result = mysqli_query($conn, $son_ilanlar_sql);
$son_ilanlar = mysqli_fetch_all($son_ilanlar_result, MYSQLI_ASSOC);

// Son jüri atamalarını getir
$son_juri_sql = "SELECT ja.*, i.ilan_baslik, k.ad, k.soyad 
                FROM juri_atamalari ja 
                JOIN ilanlar i ON ja.ilan_id = i.id 
                JOIN kullanicilar k ON ja.juri_id = k.id 
                ORDER BY ja.atama_tarihi DESC LIMIT 5";
$son_juri_result = mysqli_query($conn, $son_juri_sql);
$son_juri_atamalari = mysqli_fetch_all($son_juri_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Paneli - Akademik Personel Başvuru Sistemi</title>
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
                    <p>Yönetici paneline giriş yaptınız. Kriterleri yönetebilir, jüri atayabilir ve nihai kararları verebilirsiniz.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Toplam İlan</h6>
                                <h2 class="mb-0"><?php echo $ilan_count; ?></h2>
                            </div>
                            <i class="fas fa-bullhorn fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="ilanlar.php" class="text-white text-decoration-none">Tüm İlanlar</a>
                        <i class="fas fa-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Aktif İlan</h6>
                                <h2 class="mb-0"><?php echo $aktif_ilan_count; ?></h2>
                            </div>
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="ilanlar.php?durum=aktif" class="text-white text-decoration-none">Aktif İlanlar</a>
                        <i class="fas fa-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card bg-info text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Toplam Başvuru</h6>
                                <h2 class="mb-0"><?php echo $basvuru_count; ?></h2>
                            </div>
                            <i class="fas fa-file-alt fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="basvurular.php" class="text-white text-decoration-none">Tüm Başvurular</a>
                        <i class="fas fa-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Jüri Atanan İlan</h6>
                                <h2 class="mb-0"><?php echo $juri_ilan_count; ?></h2>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                    <div class="card-footer d-flex align-items-center justify-content-between">
                        <a href="juri_ata.php" class="text-white text-decoration-none">Jüri Atamaları</a>
                        <i class="fas fa-arrow-right text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-bullhorn"></i> Son İlanlar</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($son_ilanlar) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>İlan Başlığı</th>
                                            <th>Kadro Ünvanı</th>
                                            <th>Son Başvuru</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($son_ilanlar as $ilan): ?>
                                            <tr>
                                                <td><?php echo $ilan['ilan_baslik']; ?></td>
                                                <td><?php echo $ilan['kadro_unvani']; ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($ilan['ilan_bitis_tarihi'])); ?></td>
                                                <td>
                                                    <a href="ilan_detay.php?id=<?php echo $ilan['id']; ?>" class="btn btn-info btn-sm">
                                                        <i class="fas fa-info-circle"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="ilanlar.php" class="btn btn-primary">Tüm İlanları Görüntüle</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Henüz ilan bulunmamaktadır.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-warning text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-users"></i> Son Jüri Atamaları</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($son_juri_atamalari) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Jüri Üyesi</th>
                                            <th>İlan</th>
                                            <th>Atama Tarihi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($son_juri_atamalari as $juri): ?>
                                            <tr>
                                                <td><?php echo $juri['ad'] . ' ' . $juri['soyad']; ?></td>
                                                <td><?php echo $juri['ilan_baslik']; ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($juri['atama_tarihi'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="juri_ata.php" class="btn btn-warning">Tüm Jüri Atamalarını Görüntüle</a>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Henüz jüri ataması yapılmamıştır.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-tasks"></i> Hızlı İşlemler</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="kriterler.php" class="btn btn-primary btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center p-4">
                                    <i class="fas fa-list-check fa-3x mb-3"></i>
                                    <span>Kriterleri Yönet</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="juri_ata.php" class="btn btn-warning btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center p-4">
                                    <i class="fas fa-users fa-3x mb-3"></i>
                                    <span>Jüri Ata</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="nihai_karar.php" class="btn btn-success btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center p-4">
                                    <i class="fas fa-gavel fa-3x mb-3"></i>
                                    <span>Nihai Karar Ver</span>
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="raporlar.php" class="btn btn-info btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center p-4">
                                    <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                    <span>Raporlar</span>
                                </a>
                            </div>
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
</body>
</html>
