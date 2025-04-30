<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'aday') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Belge yükleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $belge_turu = mysqli_real_escape_string($conn, $_POST['belge_turu']);
    $belge_adi = mysqli_real_escape_string($conn, $_POST['belge_adi']);
    
    // Dosya yükleme
    if (isset($_FILES['belge_dosyasi']) && $_FILES['belge_dosyasi']['error'] == 0) {
        $upload_dir = "../uploads/";
        $dosya_adi = $user_id . "_" . time() . "_" . basename($_FILES['belge_dosyasi']['name']);
        $dosya_yolu = $upload_dir . $dosya_adi;
        
        // Dosyayı yükle
        if (move_uploaded_file($_FILES['belge_dosyasi']['tmp_name'], $dosya_yolu)) {
            // Belgeyi veritabanına kaydet
            $insert_sql = "INSERT INTO belgeler (aday_id, belge_turu, belge_adi, dosya_yolu) 
                          VALUES ($user_id, '$belge_turu', '$belge_adi', '$dosya_yolu')";
            
            if (mysqli_query($conn, $insert_sql)) {
                $_SESSION['success'] = "Belge başarıyla yüklendi.";
                header("Location: belgelerim.php");
                exit();
            } else {
                $_SESSION['error'] = "Belge kaydedilirken bir hata oluştu: " . mysqli_error($conn);
            }
        } else {
            $_SESSION['error'] = "Dosya yüklenirken bir hata oluştu.";
        }
    } else {
        $_SESSION['error'] = "Lütfen bir dosya seçiniz.";
    }
}

// Belge silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $belge_id = $_GET['sil'];
    
    // Belgenin bu kullanıcıya ait olup olmadığını kontrol et
    $kontrol_sql = "SELECT * FROM belgeler WHERE id = $belge_id AND aday_id = $user_id";
    $kontrol_result = mysqli_query($conn, $kontrol_sql);
    
    if (mysqli_num_rows($kontrol_result) > 0) {
        $belge = mysqli_fetch_assoc($kontrol_result);
        
        // Belgeyi veritabanından sil
        $sil_sql = "DELETE FROM belgeler WHERE id = $belge_id";
        if (mysqli_query($conn, $sil_sql)) {
            // Dosyayı fiziksel olarak sil
            if (file_exists($belge['dosya_yolu'])) {
                unlink($belge['dosya_yolu']);
            }
            $_SESSION['success'] = "Belge başarıyla silindi.";
        } else {
            $_SESSION['error'] = "Belge silinirken bir hata oluştu: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Belge bulunamadı veya bu belgeyi silme yetkiniz yok.";
    }
    
    header("Location: belgelerim.php");
    exit();
}

// Belgeleri getir
$sql = "SELECT * FROM belgeler WHERE aday_id = $user_id ORDER BY yukleme_tarihi DESC";
$result = mysqli_query($conn, $sql);
$belgeler = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belgelerim - Akademik Personel Başvuru Sistemi</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Belgelerim</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-file-alt"></i> Belgelerim</h2>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#belgeYukleModal">
                    <i class="fas fa-upload"></i> Yeni Belge Yükle
                </button>
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
                        
                        <?php if (count($belgeler) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Belge Adı</th>
                                            <th>Belge Türü</th>
                                            <th>Yükleme Tarihi</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($belgeler as $belge): ?>
                                            <tr>
                                                <td><?php echo $belge['belge_adi']; ?></td>
                                                <td><?php echo $belge['belge_turu']; ?></td>
                                                <td><?php echo date('d.m.Y H:i', strtotime($belge['yukleme_tarihi'])); ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="<?php echo $belge['dosya_yolu']; ?>" class="btn btn-info btn-sm" target="_blank" title="Görüntüle">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="javascript:void(0);" onclick="belgeSil(<?php echo $belge['id']; ?>)" class="btn btn-danger btn-sm" title="Sil">
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
                                <i class="fas fa-exclamation-triangle"></i> Henüz yüklediğiniz belge bulunmamaktadır.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Belge Yükleme Modal -->
    <div class="modal fade" id="belgeYukleModal" tabindex="-1" aria-labelledby="belgeYukleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="belgeYukleModalLabel">Yeni Belge Yükle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="belgelerim.php" method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="belge_turu" class="form-label">Belge Türü *</label>
                            <select class="form-select" id="belge_turu" name="belge_turu" required>
                                <option value="">Belge Türü Seçiniz</option>
                                <option value="Özgeçmiş">Özgeçmiş</option>
                                <option value="Diploma">Diploma</option>
                                <option value="Yabancı Dil Belgesi">Yabancı Dil Belgesi</option>
                                <option value="Yayın Listesi">Yayın Listesi</option>
                                <option value="Sertifika">Sertifika</option>
                                <option value="Diğer">Diğer</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="belge_adi" class="form-label">Belge Adı *</label>
                            <input type="text" class="form-control" id="belge_adi" name="belge_adi" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="belge_dosyasi" class="form-label">Belge Dosyası *</label>
                            <input type="file" class="form-control" id="belge_dosyasi" name="belge_dosyasi" required>
                            <div class="form-text">PDF, Word, Excel veya resim formatında dosya yükleyebilirsiniz.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Belgeyi Yükle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <footer class="text-center mt-4 mb-4">
        <p>&copy; <?php echo date("Y"); ?> Kocaeli Üniversitesi - Tüm Hakları Saklıdır.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function belgeSil(id) {
            if (confirm("Bu belgeyi silmek istediğinize emin misiniz?")) {
                window.location.href = "belgelerim.php?sil=" + id;
            }
        }
    </script>
</body>
</html>
