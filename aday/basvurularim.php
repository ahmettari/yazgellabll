<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'aday') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Adayın başvurularını getir
$basvuru_sql = "SELECT b.*, i.ilan_baslik, i.kadro_unvani, i.fakulte_birim FROM basvurular b 
                JOIN ilanlar i ON b.ilan_id = i.id 
                WHERE b.aday_id = $user_id 
                ORDER BY b.basvuru_tarihi DESC";
$basvuru_result = mysqli_query($conn, $basvuru_sql);
$basvurular = mysqli_fetch_all($basvuru_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Başvurularım - Akademik Personel Başvuru Sistemi</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Başvurularım</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-clipboard-list"></i> Başvurularım</h3>
                    </div>
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
                        
                        <?php if (count($basvurular) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>İlan Başlığı</th>
                                            <th>Fakülte/Birim</th>
                                            <th>Kadro Ünvanı</th>
                                            <th>Başvuru Tarihi</th>
                                            <th>Durum</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($basvurular as $basvuru): ?>
                                            <tr>
                                                <td><?php echo $basvuru['ilan_baslik']; ?></td>
                                                <td><?php echo $basvuru['fakulte_birim']; ?></td>
                                                <td><?php echo $basvuru['kadro_unvani']; ?></td>
                                                <td><?php echo date('d.m.Y', strtotime($basvuru['basvuru_tarihi'])); ?></td>
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
                                                <td>
                                                    <a href="basvuru_detay.php?id=<?php echo $basvuru['id']; ?>" class="btn btn-info btn-sm">
                                                        <i class="fas fa-info-circle"></i> Detay
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> Henüz bir başvurunuz bulunmamaktadır.
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
