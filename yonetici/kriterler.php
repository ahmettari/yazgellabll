<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'yonetici') {
    header("Location: ../index.php");
    exit();
}

// Kriter silme işlemi
if (isset($_GET['sil']) && is_numeric($_GET['sil'])) {
    $kriter_id = $_GET['sil'];
    
    // Kriteri sil
    $sil_sql = "DELETE FROM ilan_kriterleri WHERE id = $kriter_id";
    if (mysqli_query($conn, $sil_sql)) {
        $_SESSION['success'] = "Kriter başarıyla silindi.";
    } else {
        $_SESSION['error'] = "Kriter silinirken bir hata oluştu: " . mysqli_error($conn);
    }
    
    header("Location: kriterler.php");
    exit();
}

// İlanları getir
$ilanlar_sql = "SELECT * FROM ilanlar ORDER BY ilan_baslangic_tarihi DESC";
$ilanlar_result = mysqli_query($conn, $ilanlar_sql);
$ilanlar = mysqli_fetch_all($ilanlar_result, MYSQLI_ASSOC);

// Filtreleme
$where_clause = "";
if (isset($_GET['ilan_id']) && is_numeric($_GET['ilan_id'])) {
    $ilan_id = $_GET['ilan_id'];
    $where_clause = "WHERE ilan_id = $ilan_id";
}

// Kriterleri getir
$sql = "SELECT ik.*, i.ilan_baslik 
        FROM ilan_kriterleri ik 
        JOIN ilanlar i ON ik.ilan_id = i.id 
        $where_clause 
        ORDER BY i.ilan_baslangic_tarihi DESC, ik.kriter_adi ASC";
$result = mysqli_query($conn, $sql);
$kriterler = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Kriter ekleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al
    $ilan_id = mysqli_real_escape_string($conn, $_POST['ilan_id']);
    $kriter_adi = mysqli_real_escape_string($conn, $_POST['kriter_adi']);
    $aciklama = mysqli_real_escape_string($conn, $_POST['aciklama']);
    $minimum_deger = mysqli_real_escape_string($conn, $_POST['minimum_deger']);
    
    // Kullanıcı ID'sini al
    $ekleyen_id = $_SESSION['user_id'];
    
    // Kriteri veritabanına ekle
    $insert_sql = "INSERT INTO ilan_kriterleri (ilan_id, kriter_adi, aciklama, minimum_deger, ekleyen_id) 
                  VALUES ($ilan_id, '$kriter_adi', '$aciklama', '$minimum_deger', $ekleyen_id)";
    
    if (mysqli_query($conn, $insert_sql)) {
        $_SESSION['success'] = "Kriter başarıyla eklendi.";
        header("Location: kriterler.php?ilan_id=$ilan_id");
        exit();
    } else {
        $_SESSION['error'] = "Kriter eklenirken bir hata oluştu: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kriterler - Yönetici Paneli</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Kriterler</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-list-check"></i> Kriterler</h2>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kriterEkleModal">
                    <i class="fas fa-plus-circle"></i> Yeni Kriter Ekle
                </button>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <form action="kriterler.php" method="get">
                                    <div class="input-group">
                                        <select class="form-select" name="ilan_id" id="ilan-filtre">
                                            <option value="">Tüm İlanlar</option>
                                            <?php foreach ($ilanlar as $ilan): ?>
                                                <option value="<?php echo $ilan['id']; ?>" <?php echo (isset($_GET['ilan_id']) && $_GET['ilan_id'] == $ilan['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $ilan['ilan_baslik']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-filter"></i> Filtrele
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
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
                        
                        <?php if (count($kriterler) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>İlan</th>
                                            <th>Kriter Adı</th>
                                            <th>Açıklama</th>
                                            <th>Minimum Değer</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($kriterler as $kriter): ?>
                                            <tr>
                                                <td><?php echo $kriter['id']; ?></td>
                                                <td><?php echo $kriter['ilan_baslik']; ?></td>
                                                <td><?php echo $kriter['kriter_adi']; ?></td>
                                                <td><?php echo $kriter['aciklama']; ?></td>
                                                <td><?php echo $kriter['minimum_deger']; ?></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#kriterDuzenleModal<?php echo $kriter['id']; ?>" title="Düzenle">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="javascript:void(0);" onclick="kriterSil(<?php echo $kriter['id']; ?>)" class="btn btn-danger btn-sm" title="Sil">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </div>
                                                    
                                                    <!-- Kriter Düzenleme Modal -->
                                                    <div class="modal fade" id="kriterDuzenleModal<?php echo $kriter['id']; ?>" tabindex="-1" aria-labelledby="kriterDuzenleModalLabel<?php echo $kriter['id']; ?>" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-warning text-dark">
                                                                    <h5 class="modal-title" id="kriterDuzenleModalLabel<?php echo $kriter['id']; ?>">Kriter Düzenle</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <form action="kriter_duzenle.php" method="post">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="kriter_id" value="<?php echo $kriter['id']; ?>">
                                                                        
                                                                        <div class="mb-3">
                                                                            <label for="ilan_id<?php echo $kriter['id']; ?>" class="form-label">İlan *</label>
                                                                            <select class="form-select" id="ilan_id<?php echo $kriter['id']; ?>" name="ilan_id" required>
                                                                                <?php foreach ($ilanlar as $ilan): ?>
                                                                                    <option value="<?php echo $ilan['id']; ?>" <?php echo ($kriter['ilan_id'] == $ilan['id']) ? 'selected' : ''; ?>>
                                                                                        <?php echo $ilan['ilan_baslik']; ?>
                                                                                    </option>
                                                                                <?php endforeach; ?>
                                                                            </select>
                                                                        </div>
                                                                        
                                                                        <div class="mb-3">
                                                                            <label for="kriter_adi<?php echo $kriter['id']; ?>" class="form-label">Kriter Adı *</label>
                                                                            <input type="text" class="form-control" id="kriter_adi<?php echo $kriter['id']; ?>" name="kriter_adi" value="<?php echo $kriter['kriter_adi']; ?>" required>
                                                                        </div>
                                                                        
                                                                        <div class="mb-3">
                                                                            <label for="aciklama<?php echo $kriter['id']; ?>" class="form-label">Açıklama</label>
                                                                            <textarea class="form-control" id="aciklama<?php echo $kriter['id']; ?>" name="aciklama" rows="3"><?php echo $kriter['aciklama']; ?></textarea>
                                                                        </div>
                                                                        
                                                                        <div class="mb-3">
                                                                            <label for="minimum_deger<?php echo $kriter['id']; ?>" class="form-label">Minimum Değer *</label>
                                                                            <input type="text" class="form-control" id="minimum_deger<?php echo $kriter['id']; ?>" name="minimum_deger" value="<?php echo $kriter['minimum_deger']; ?>" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                                        <button type="submit" class="btn btn-warning">Değişiklikleri Kaydet</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Kriterlere uygun kriter bulunamadı.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Kriter Ekleme Modal -->
    <div class="modal fade" id="kriterEkleModal" tabindex="-1" aria-labelledby="kriterEkleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="kriterEkleModalLabel">Yeni Kriter Ekle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="kriterler.php" method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="ilan_id" class="form-label">İlan *</label>
                            <select class="form-select" id="ilan_id" name="ilan_id" required>
                                <option value="">İlan Seçiniz</option>
                                <?php foreach ($ilanlar as $ilan): ?>
                                    <option value="<?php echo $ilan['id']; ?>" <?php echo (isset($_GET['ilan_id']) && $_GET['ilan_id'] == $ilan['id']) ? 'selected' : ''; ?>>
                                        <?php echo $ilan['ilan_baslik']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="kriter_adi" class="form-label">Kriter Adı *</label>
                            <input type="text" class="form-control" id="kriter_adi" name="kriter_adi" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="aciklama" class="form-label">Açıklama</label>
                            <textarea class="form-control" id="aciklama" name="aciklama" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="minimum_deger" class="form-label">Minimum Değer *</label>
                            <input type="text" class="form-control" id="minimum_deger" name="minimum_deger" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kriter Ekle</button>
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
        function kriterSil(id) {
            if (confirm("Bu kriteri silmek istediğinize emin misiniz?")) {
                window.location.href = "kriterler.php?sil=" + id;
            }
        }
    </script>
</body>
</html>
