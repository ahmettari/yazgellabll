<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'juri') {
    header("Location: ../index.php");
    exit();
}

// Başvuru ID'si kontrol et
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: basvurular.php");
    exit();
}

$basvuru_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

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

// Jüri üyesinin bu başvuruyu değerlendirme yetkisi var mı kontrol et
$yetki_sql = "SELECT * FROM juri_atamalari 
              WHERE juri_id = $user_id AND ilan_id = {$basvuru['ilan_id']}";
$yetki_result = mysqli_query($conn, $yetki_sql);

if (mysqli_num_rows($yetki_result) == 0) {
    $_SESSION['error'] = "Bu başvuruyu değerlendirme yetkiniz bulunmamaktadır.";
    header("Location: basvurular.php");
    exit();
}

// Başvuru kriterlerini getir
$kriter_sql = "SELECT bk.*, ik.kriter_adi, ik.aciklama, ik.minimum_deger 
               FROM basvuru_kriterleri bk 
               JOIN ilan_kriterleri ik ON bk.kriter_id = ik.id 
               WHERE bk.basvuru_id = $basvuru_id";
$kriter_result = mysqli_query($conn, $kriter_sql);
$kriterler = mysqli_fetch_all($kriter_result, MYSQLI_ASSOC);

// Jüri değerlendirmesini getir
$degerlendirme_sql = "SELECT * FROM juri_degerlendirmeleri 
                      WHERE juri_id = $user_id AND basvuru_id = $basvuru_id";
$degerlendirme_result = mysqli_query($conn, $degerlendirme_sql);
$degerlendirme = mysqli_fetch_assoc($degerlendirme_result);

// Değerlendirme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sonuc = mysqli_real_escape_string($conn, $_POST['sonuc']);
    $degerlendirme_metni = mysqli_real_escape_string($conn, $_POST['degerlendirme']);
    
    // Rapor dosyası yükleme
    $rapor_dosyasi = "";
    if (isset($_FILES['rapor_dosyasi']) && $_FILES['rapor_dosyasi']['error'] == 0) {
        $upload_dir = "../uploads/";
        $rapor_name = $user_id . "_" . time() . "_rapor_" . basename($_FILES['rapor_dosyasi']['name']);
        $rapor_dosyasi = $upload_dir . $rapor_name;
        move_uploaded_file($_FILES['rapor_dosyasi']['tmp_name'], $rapor_dosyasi);
    }
    
    // Değerlendirme daha önce yapılmış mı kontrol et
    if ($degerlendirme) {
        // Güncelleme yap
        $update_sql = "UPDATE juri_degerlendirmeleri 
                      SET sonuc = '$sonuc', degerlendirme = '$degerlendirme_metni', 
                      degerlendirme_tarihi = NOW()";
        
        // Rapor dosyası yüklendiyse güncelle
        if (!empty($rapor_dosyasi)) {
            $update_sql .= ", rapor_dosyasi = '$rapor_dosyasi'";
        }
        
        $update_sql .= " WHERE id = {$degerlendirme['id']}";
        
        if (mysqli_query($conn, $update_sql)) {
            $_SESSION['success'] = "Değerlendirme başarıyla güncellendi.";
            header("Location: basvurular.php");
            exit();
        } else {
            $_SESSION['error'] = "Değerlendirme güncellenirken bir hata oluştu: " . mysqli_error($conn);
        }
    } else {
        // Yeni değerlendirme ekle
        $insert_sql = "INSERT INTO juri_degerlendirmeleri 
                      (juri_id, basvuru_id, sonuc, degerlendirme, rapor_dosyasi, degerlendirme_tarihi) 
                      VALUES ($user_id, $basvuru_id, '$sonuc', '$degerlendirme_metni', '$rapor_dosyasi', NOW())";
        
        if (mysqli_query($conn, $insert_sql)) {
            $_SESSION['success'] = "Değerlendirme başarıyla kaydedildi.";
            header("Location: basvurular.php");
            exit();
        } else {
            $_SESSION['error'] = "Değerlendirme kaydedilirken bir hata oluştu: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Değerlendirme Yap - Jüri Paneli</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Değerlendirme Yap</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-clipboard-check"></i> Başvuru Değerlendirme</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="basvurular.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Geri
                </a>
            </div>
        </div>
        
        <?php
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
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-file-alt"></i> Başvuru Belgeleri</h3>
                    </div>
                    <div class="card-body">
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
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h3 class="card-title mb-0"><i class="fas fa-list-check"></i> Başvuru Kriterleri</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Kriter Adı</th>
                                        <th>Açıklama</th>
                                        <th>Minimum Değer</th>
                                        <th>Adayın Değeri</th>
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
                
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-clipboard-check"></i> Değerlendirme Formu</h3>
                    </div>
                    <div class="card-body">
                        <form action="degerlendirme_yap.php?id=<?php echo $basvuru_id; ?>" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="sonuc" class="form-label">Değerlendirme Sonucu *</label>
                                <select class="form-select" id="sonuc" name="sonuc" required>
                                    <option value="">Sonuç Seçiniz</option>
                                    <option value="Olumlu" <?php echo (isset($degerlendirme) && $degerlendirme['sonuc'] == 'Olumlu') ? 'selected' : ''; ?>>Olumlu</option>
                                    <option value="Olumsuz" <?php echo (isset($degerlendirme) && $degerlendirme['sonuc'] == 'Olumsuz') ? 'selected' : ''; ?>>Olumsuz</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="degerlendirme" class="form-label">Değerlendirme *</label>
                                <textarea class="form-control" id="degerlendirme" name="degerlendirme" rows="5" required><?php echo isset($degerlendirme) ? $degerlendirme['degerlendirme'] : ''; ?></textarea>
                                <div class="form-text">Adayın başvurusuna ilişkin detaylı değerlendirmenizi yazınız.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="rapor_dosyasi" class="form-label">Değerlendirme Raporu</label>
                                <input type="file" class="form-control" id="rapor_dosyasi" name="rapor_dosyasi" accept=".pdf,.doc,.docx">
                                <div class="form-text">PDF veya Word formatında değerlendirme raporu yükleyebilirsiniz.</div>
                                <?php if (isset($degerlendirme) && !empty($degerlendirme['rapor_dosyasi'])): ?>
                                    <div class="mt-2">
                                        <a href="<?php echo $degerlendirme['rapor_dosyasi']; ?>" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-file-pdf"></i> Mevcut Raporu Görüntüle
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?php echo isset($degerlendirme) ? '<i class="fas fa-save"></i> Değerlendirmeyi Güncelle' : '<i class="fas fa-clipboard-check"></i> Değerlendirmeyi Kaydet'; ?>
                                </button>
                            </div>
                        </form>
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
