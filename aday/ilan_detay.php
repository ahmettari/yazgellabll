<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'aday') {
    header("Location: ../index.php");
    exit();
}

// İlan ID'si kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$ilan_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// İlan bilgilerini getir
$sql = "SELECT * FROM ilanlar WHERE id = $ilan_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: dashboard.php");
    exit();
}

$ilan = mysqli_fetch_assoc($result);

// İlana ait kriterleri getir
$kriter_sql = "SELECT * FROM ilan_kriterleri WHERE ilan_id = $ilan_id";
$kriter_result = mysqli_query($conn, $kriter_sql);
$kriterler = mysqli_fetch_all($kriter_result, MYSQLI_ASSOC);

// Adayın bu ilana başvurup başvurmadığını kontrol et
$basvuru_sql = "SELECT * FROM basvurular WHERE ilan_id = $ilan_id AND aday_id = $user_id";
$basvuru_result = mysqli_query($conn, $basvuru_sql);
$basvuru_yapilmis = (mysqli_num_rows($basvuru_result) > 0);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Detayı - Akademik Personel Başvuru Sistemi</title>
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
                        <li class="breadcrumb-item active" aria-current="page">İlan Detayı</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-bullhorn"></i> <?php echo $ilan['ilan_baslik']; ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2">İlan Bilgileri</h5>
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
                                    <tr>
                                        <th>İlan Başlangıç Tarihi:</th>
                                        <td><?php echo date('d.m.Y', strtotime($ilan['ilan_baslangic_tarihi'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>İlan Bitiş Tarihi:</th>
                                        <td><?php echo date('d.m.Y', strtotime($ilan['ilan_bitis_tarihi'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2">İlan Açıklaması</h5>
                                <div class="p-3 bg-light rounded">
                                    <?php echo nl2br($ilan['ilan_aciklama']); ?>
                                </div>
                                
                                <h5 class="border-bottom pb-2 mt-4">Başvuru Koşulları</h5>
                                <div class="p-3 bg-light rounded">
                                    <?php echo nl2br($ilan['basvuru_kosullari']); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">İlan Kriterleri</h5>
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
                        
                        <div class="row mt-4">
                            <div class="col-md-12 text-center">
                                <?php if ($basvuru_yapilmis): ?>
                                    <div class="alert alert-success">
                                        <i class="fas fa-check-circle"></i> Bu ilana daha önce başvuru yaptınız.
                                    </div>
                                    <a href="basvurularim.php" class="btn btn-info">
                                        <i class="fas fa-clipboard-list"></i> Başvurularımı Görüntüle
                                    </a>
                                <?php else: ?>
                                    <?php if (strtotime($ilan['ilan_bitis_tarihi']) >= strtotime(date('Y-m-d'))): ?>
                                        <a href="basvuru_yap.php?id=<?php echo $ilan_id; ?>" class="btn btn-success btn-lg">
                                            <i class="fas fa-file-alt"></i> Başvuru Yap
                                        </a>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i> Bu ilanın başvuru süresi sona ermiştir.
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
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
