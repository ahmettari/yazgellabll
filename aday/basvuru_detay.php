<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'aday') {
    header("Location: ../index.php");
    exit();
}

// Başvuru ID'si kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: basvurularim.php");
    exit();
}

$basvuru_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Başvuru bilgilerini getir
$sql = "SELECT b.*, i.ilan_baslik, i.kadro_unvani, i.fakulte_birim, i.bolum, i.anabilim_dali 
        FROM basvurular b 
        JOIN ilanlar i ON b.ilan_id = i.id 
        WHERE b.id = $basvuru_id AND b.aday_id = $user_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: basvurularim.php");
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
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Başvuru Detayı - Akademik Personel Başvuru Sistemi</title>
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
                        <li class="breadcrumb-item"><a href="basvurularim.php">Başvurularım</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Başvuru Detayı</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-file-alt"></i> Başvuru Detayı</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2">İlan Bilgileri</h5>
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
                                        <td><?php echo $basvuru['bolum']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Anabilim Dalı:</th>
                                        <td><?php echo $basvuru['anabilim_dali']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Kadro Ünvanı:</th>
                                        <td><?php echo $basvuru['kadro_unvani']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h5 class="border-bottom pb-2">Başvuru Bilgileri</h5>
                                <table class="table table-striped">
                                    <tr>
                                        <th width="40%">Başvuru Tarihi:</th>
                                        <td><?php echo date('d.m.Y H:i', strtotime($basvuru['basvuru_tarihi'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Durum:</th>
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
                                    </tr>
                                    <?php if (!empty($basvuru['sonuc_aciklama'])): ?>
                                    <tr>
                                        <th>Sonuç Açıklaması:</th>
                                        <td><?php echo nl2br($basvuru['sonuc_aciklama']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Yüklenen Belgeler</h5>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="fas fa-file-alt"></i> Özgeçmiş (CV)</h6>
                                                <?php if (!empty($basvuru['ozgecmis'])): ?>
                                                    <a href="<?php echo $basvuru['ozgecmis']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                                        <i class="fas fa-download"></i> Görüntüle
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Belge yüklenmemiş</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="fas fa-file-alt"></i> Diploma</h6>
                                                <?php if (!empty($basvuru['diploma'])): ?>
                                                    <a href="<?php echo $basvuru['diploma']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                                        <i class="fas fa-download"></i> Görüntüle
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Belge yüklenmemiş</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="fas fa-file-alt"></i> Yayınlar</h6>
                                                <?php if (!empty($basvuru['yayinlar'])): ?>
                                                    <a href="<?php echo $basvuru['yayinlar']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                                        <i class="fas fa-download"></i> Görüntüle
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Belge yüklenmemiş</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="fas fa-file-alt"></i> Yabancı Dil Belgesi</h6>
                                                <?php if (!empty($basvuru['dil_belgesi'])): ?>
                                                    <a href="<?php echo $basvuru['dil_belgesi']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                                        <i class="fas fa-download"></i> Görüntüle
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Belge yüklenmemiş</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6 class="card-title"><i class="fas fa-file-alt"></i> Diğer Belgeler</h6>
                                                <?php if (!empty($basvuru['diger_belgeler'])): ?>
                                                    <a href="<?php echo $basvuru['diger_belgeler']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                                        <i class="fas fa-download"></i> Görüntüle
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Belge yüklenmemiş</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (count($kriterler) > 0): ?>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Başvuru Kriterleri</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>Kriter Adı</th>
                                                <th>Açıklama</th>
                                                <th>Minimum Değer</th>
                                                <th>Girilen Değer</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($kriterler as $kriter): ?>
                                                <tr>
                                                    <td><?php echo $kriter['kriter_adi']; ?></td>
                                                    <td><?php echo $kriter['aciklama']; ?></td>
                                                    <td><?php echo $kriter['minimum_deger']; ?></td>
                                                    <td><?php echo $kriter['deger']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (count($juri_degerlendirmeleri) > 0 && $basvuru['durum'] != 'Beklemede'): ?>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="border-bottom pb-2">Jüri Değerlendirmeleri</h5>
                                <div class="accordion" id="juriDegerlendirmeleri">
                                    <?php foreach ($juri_degerlendirmeleri as $index => $degerlendirme): ?>
                                        <div class="accordion-item">
                                            <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                                <button class="accordion-button <?php echo ($index > 0) ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="<?php echo ($index == 0) ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                                    Jüri Üyesi: <?php echo $degerlendirme['ad'] . ' ' . $degerlendirme['soyad']; ?> - 
                                                    <?php echo ($degerlendirme['sonuc'] == 'Olumlu') ? '<span class="text-success">Olumlu</span>' : '<span class="text-danger">Olumsuz</span>'; ?>
                                                </button>
                                            </h2>
                                            <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo ($index == 0) ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#juriDegerlendirmeleri">
                                                <div class="accordion-body">
                                                    <h6>Değerlendirme Tarihi: <?php echo date('d.m.Y H:i', strtotime($degerlendirme['degerlendirme_tarihi'])); ?></h6>
                                                    <div class="mt-3">
                                                        <h6>Değerlendirme:</h6>
                                                        <p><?php echo nl2br($degerlendirme['degerlendirme']); ?></p>
                                                    </div>
                                                    <?php if (!empty($degerlendirme['rapor_dosyasi'])): ?>
                                                        <div class="mt-3">
                                                            <h6>Değerlendirme Raporu:</h6>
                                                            <a href="<?php echo $degerlendirme['rapor_dosyasi']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                                                <i class="fas fa-file-pdf"></i> Raporu Görüntüle
                                                            </a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row mt-4">
                            <div class="col-md-12 text-center">
                                <a href="basvurularim.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Başvurularıma Dön
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
