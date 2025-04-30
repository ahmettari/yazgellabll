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

// İlanın başvuru süresi geçmiş mi kontrol et
if (strtotime($ilan['ilan_bitis_tarihi']) < strtotime(date('Y-m-d'))) {
    $_SESSION['error'] = "Bu ilanın başvuru süresi sona ermiştir.";
    header("Location: ilan_detay.php?id=$ilan_id");
    exit();
}

// Adayın bu ilana başvurup başvurmadığını kontrol et
$basvuru_sql = "SELECT * FROM basvurular WHERE ilan_id = $ilan_id AND aday_id = $user_id";
$basvuru_result = mysqli_query($conn, $basvuru_sql);

if (mysqli_num_rows($basvuru_result) > 0) {
    $_SESSION['error'] = "Bu ilana daha önce başvuru yaptınız.";
    header("Location: ilan_detay.php?id=$ilan_id");
    exit();
}

// İlana ait kriterleri getir
$kriter_sql = "SELECT * FROM ilan_kriterleri WHERE ilan_id = $ilan_id";
$kriter_result = mysqli_query($conn, $kriter_sql);
$kriterler = mysqli_fetch_all($kriter_result, MYSQLI_ASSOC);

// Başvuru işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al
    $ozgecmis = isset($_FILES['ozgecmis']) ? $_FILES['ozgecmis'] : null;
    $diploma = isset($_FILES['diploma']) ? $_FILES['diploma'] : null;
    $yayinlar = isset($_FILES['yayinlar']) ? $_FILES['yayinlar'] : null;
    $dil_belgesi = isset($_FILES['dil_belgesi']) ? $_FILES['dil_belgesi'] : null;
    $diger_belgeler = isset($_FILES['diger_belgeler']) ? $_FILES['diger_belgeler'] : null;
    
    // Dosya yükleme işlemleri
    $upload_dir = "../uploads/";
    $ozgecmis_path = "";
    $diploma_path = "";
    $yayinlar_path = "";
    $dil_belgesi_path = "";
    $diger_belgeler_path = "";
    
    // Özgeçmiş yükleme
    if ($ozgecmis && $ozgecmis['error'] == 0) {
        $ozgecmis_name = $user_id . "_" . time() . "_ozgecmis_" . basename($ozgecmis['name']);
        $ozgecmis_path = $upload_dir . $ozgecmis_name;
        move_uploaded_file($ozgecmis['tmp_name'], $ozgecmis_path);
    }
    
    // Diploma yükleme
    if ($diploma && $diploma['error'] == 0) {
        $diploma_name = $user_id . "_" . time() . "_diploma_" . basename($diploma['name']);
        $diploma_path = $upload_dir . $diploma_name;
        move_uploaded_file($diploma['tmp_name'], $diploma_path);
    }
    
    // Yayınlar yükleme
    if ($yayinlar && $yayinlar['error'] == 0) {
        $yayinlar_name = $user_id . "_" . time() . "_yayinlar_" . basename($yayinlar['name']);
        $yayinlar_path = $upload_dir . $yayinlar_name;
        move_uploaded_file($yayinlar['tmp_name'], $yayinlar_path);
    }
    
    // Dil belgesi yükleme
    if ($dil_belgesi && $dil_belgesi['error'] == 0) {
        $dil_belgesi_name = $user_id . "_" . time() . "_dil_belgesi_" . basename($dil_belgesi['name']);
        $dil_belgesi_path = $upload_dir . $dil_belgesi_name;
        move_uploaded_file($dil_belgesi['tmp_name'], $dil_belgesi_path);
    }
    
    // Diğer belgeler yükleme
    if ($diger_belgeler && $diger_belgeler['error'] == 0) {
        $diger_belgeler_name = $user_id . "_" . time() . "_diger_belgeler_" . basename($diger_belgeler['name']);
        $diger_belgeler_path = $upload_dir . $diger_belgeler_name;
        move_uploaded_file($diger_belgeler['tmp_name'], $diger_belgeler_path);
    }
    
    // Başvuru bilgilerini veritabanına kaydet
    $insert_sql = "INSERT INTO basvurular (aday_id, ilan_id, ozgecmis, diploma, yayinlar, dil_belgesi, diger_belgeler, durum, basvuru_tarihi) 
                  VALUES ($user_id, $ilan_id, '$ozgecmis_path', '$diploma_path', '$yayinlar_path', '$dil_belgesi_path', '$diger_belgeler_path', 'Beklemede', NOW())";
    
    if (mysqli_query($conn, $insert_sql)) {
        $basvuru_id = mysqli_insert_id($conn);
        
        // Kriter değerlerini kaydet
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'kriter_') === 0) {
                $kriter_id = substr($key, 7);
                $kriter_deger = mysqli_real_escape_string($conn, $value);
                
                $kriter_insert_sql = "INSERT INTO basvuru_kriterleri (basvuru_id, kriter_id, deger) 
                                     VALUES ($basvuru_id, $kriter_id, '$kriter_deger')";
                mysqli_query($conn, $kriter_insert_sql);
            }
        }
        
        $_SESSION['success'] = "Başvurunuz başarıyla kaydedilmiştir.";
        header("Location: basvurularim.php");
        exit();
    } else {
        $_SESSION['error'] = "Başvuru sırasında bir hata oluştu: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Başvuru Yap - Akademik Personel Başvuru Sistemi</title>
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
                        <li class="breadcrumb-item"><a href="ilan_detay.php?id=<?php echo $ilan_id; ?>">İlan Detayı</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Başvuru Yap</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-file-alt"></i> Başvuru Formu - <?php echo $ilan['ilan_baslik']; ?></h3>
                    </div>
                    <div class="card-body">
                        <?php
                        // Hata mesajı varsa göster
                        if (isset($_SESSION['error'])) {
                            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                            unset($_SESSION['error']);
                        }
                        ?>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Lütfen aşağıdaki formu eksiksiz doldurunuz ve gerekli belgeleri yükleyiniz.
                        </div>
                        
                        <form action="basvuru_yap.php?id=<?php echo $ilan_id; ?>" method="post" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2">Başvuru Belgeleri</h5>
                                    
                                    <div class="mb-3">
                                        <label for="ozgecmis" class="form-label">Özgeçmiş (CV) *</label>
                                        <input type="file" class="form-control" id="ozgecmis" name="ozgecmis" required accept=".pdf,.doc,.docx">
                                        <div class="form-text">PDF veya Word formatında yükleyiniz.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="diploma" class="form-label">Diploma *</label>
                                        <input type="file" class="form-control" id="diploma" name="diploma" required accept=".pdf,.jpg,.jpeg,.png">
                                        <div class="form-text">PDF veya resim formatında yükleyiniz.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="yayinlar" class="form-label">Yayınlar *</label>
                                        <input type="file" class="form-control" id="yayinlar" name="yayinlar" required accept=".pdf,.zip,.rar">
                                        <div class="form-text">PDF veya sıkıştırılmış dosya formatında yükleyiniz.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="dil_belgesi" class="form-label">Yabancı Dil Belgesi *</label>
                                        <input type="file" class="form-control" id="dil_belgesi" name="dil_belgesi" required accept=".pdf,.jpg,.jpeg,.png">
                                        <div class="form-text">PDF veya resim formatında yükleyiniz.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="diger_belgeler" class="form-label">Diğer Belgeler</label>
                                        <input type="file" class="form-control" id="diger_belgeler" name="diger_belgeler" accept=".pdf,.zip,.rar">
                                        <div class="form-text">PDF veya sıkıştırılmış dosya formatında yükleyiniz.</div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <h5 class="border-bottom pb-2">Başvuru Kriterleri</h5>
                                    
                                    <?php if (count($kriterler) > 0): ?>
                                        <?php foreach ($kriterler as $kriter): ?>
                                            <div class="mb-3">
                                                <label for="kriter_<?php echo $kriter['id']; ?>" class="form-label"><?php echo $kriter['kriter_adi']; ?> *</label>
                                                <input type="text" class="form-control" id="kriter_<?php echo $kriter['id']; ?>" name="kriter_<?php echo $kriter['id']; ?>" required>
                                                <div class="form-text"><?php echo $kriter['aciklama']; ?> (Minimum: <?php echo $kriter['minimum_deger']; ?>)</div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> Bu ilan için özel kriter bulunmamaktadır.
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mb-3">
                                        <label for="aciklama" class="form-label">Ek Açıklama</label>
                                        <textarea class="form-control" id="aciklama" name="aciklama" rows="4"></textarea>
                                        <div class="form-text">Başvurunuzla ilgili eklemek istediğiniz bilgileri yazabilirsiniz.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="onay" name="onay" required>
                                        <label class="form-check-label" for="onay">
                                            Yukarıda verdiğim bilgilerin doğruluğunu ve belgelerin gerçekliğini onaylıyorum.
                                        </label>
                                    </div>
                                    
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="ilan_detay.php?id=<?php echo $ilan_id; ?>" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Geri Dön
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-paper-plane"></i> Başvuruyu Tamamla
                                        </button>
                                    </div>
                                </div>
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
