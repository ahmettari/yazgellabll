<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Kullanıcı silme işlemi
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    // Kullanıcıyı silmek yerine durum değerini 0 yap (deaktif et)
    $update_sql = "UPDATE kullanicilar SET durum = 0 WHERE id = $delete_id";
    
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['success'] = "Kullanıcı başarıyla deaktif edildi.";
    } else {
        $_SESSION['error'] = "Kullanıcı deaktif edilirken bir hata oluştu: " . mysqli_error($conn);
    }
    
    header("Location: kullanicilar.php");
    exit();
}

// Kullanıcı aktifleştirme işlemi
if (isset($_GET['activate']) && is_numeric($_GET['activate'])) {
    $activate_id = $_GET['activate'];
    
    $update_sql = "UPDATE kullanicilar SET durum = 1 WHERE id = $activate_id";
    
    if (mysqli_query($conn, $update_sql)) {
        $_SESSION['success'] = "Kullanıcı başarıyla aktifleştirildi.";
    } else {
        $_SESSION['error'] = "Kullanıcı aktifleştirilirken bir hata oluştu: " . mysqli_error($conn);
    }
    
    header("Location: kullanicilar.php");
    exit();
}

// Filtreleme ve arama işlemleri
$where_clause = "1=1"; // Başlangıç koşulu

// Rol filtresi
if (isset($_GET['role']) && !empty($_GET['role'])) {
    $role = mysqli_real_escape_string($conn, $_GET['role']);
    $where_clause .= " AND rol = '$role'";
}

// Durum filtresi
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    if ($status == '1') {
        $where_clause .= " AND durum = 1";
    } elseif ($status == '0') {
        $where_clause .= " AND durum = 0";
    }
}

// Arama filtresi
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clause .= " AND (tc_kimlik_no LIKE '%$search%' OR ad LIKE '%$search%' OR soyad LIKE '%$search%' OR email LIKE '%$search%')";
}

// Sayfalama
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Toplam kayıt sayısını al
$count_sql = "SELECT COUNT(*) as total FROM kullanicilar WHERE $where_clause";
$count_result = mysqli_query($conn, $count_sql);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

// Kullanıcıları getir
$sql = "SELECT * FROM kullanicilar WHERE $where_clause ORDER BY id DESC LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcılar - Admin Paneli</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Kullanıcılar</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-8">
                <h2><i class="fas fa-users"></i> Kullanıcılar</h2>
            </div>
            <div class="col-md-4 text-end">
                <a href="kullanici_ekle.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Yeni Kullanıcı Ekle
                </a>
            </div>
        </div>
        
        <?php
        // Başarı mesajı
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        
        // Hata mesajı
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        ?>
        
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold">Kullanıcı Listesi</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <form action="kullanicilar.php" method="get" class="row g-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search" placeholder="TC No, Ad, Soyad veya E-posta" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="role">
                                    <option value="">Tüm Roller</option>
                                    <option value="aday" <?php echo (isset($_GET['role']) && $_GET['role'] == 'aday') ? 'selected' : ''; ?>>Aday</option>
                                    <option value="juri" <?php echo (isset($_GET['role']) && $_GET['role'] == 'juri') ? 'selected' : ''; ?>>Jüri Üyesi</option>
                                    <option value="yonetici" <?php echo (isset($_GET['role']) && $_GET['role'] == 'yonetici') ? 'selected' : ''; ?>>Yönetici</option>
                                    <option value="admin" <?php echo (isset($_GET['role']) && $_GET['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="1" <?php echo (isset($_GET['status']) && $_GET['status'] == '1') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="0" <?php echo (isset($_GET['status']) && $_GET['status'] == '0') ? 'selected' : ''; ?>>Pasif</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Filtrele
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>TC Kimlik No</th>
                                <th>Ad Soyad</th>
                                <th>E-posta</th>
                                <th>Telefon</th>
                                <th>Rol</th>
                                <th>Durum</th>
                                <th>Kayıt Tarihi</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Rol adını Türkçe'ye çevir
                                    $role_name = '';
                                    switch ($row['rol']) {
                                        case 'aday':
                                            $role_name = 'Aday';
                                            break;
                                        case 'juri':
                                            $role_name = 'Jüri Üyesi';
                                            break;
                                        case 'yonetici':
                                            $role_name = 'Yönetici';
                                            break;
                                        case 'admin':
                                            $role_name = 'Admin';
                                            break;
                                        default:
                                            $role_name = $row['rol'];
                                    }
                                    
                                    echo '<tr>';
                                    echo '<td>' . $row['id'] . '</td>';
                                    echo '<td>' . $row['tc_kimlik_no'] . '</td>';
                                    echo '<td>' . $row['ad'] . ' ' . $row['soyad'] . '</td>';
                                    echo '<td>' . $row['email'] . '</td>';
                                    echo '<td>' . $row['telefon'] . '</td>';
                                    echo '<td>' . $role_name . '</td>';
                                    echo '<td>';
                                    if ($row['durum'] == 1) {
                                        echo '<span class="badge bg-success">Aktif</span>';
                                    } else {
                                        echo '<span class="badge bg-danger">Pasif</span>';
                                    }
                                    echo '</td>';
                                    echo '<td>' . date('d.m.Y', strtotime($row['kayit_tarihi'])) . '</td>';
                                    echo '<td>';
                                    echo '<div class="btn-group" role="group">';
                                    echo '<a href="kullanici_duzenle.php?id=' . $row['id'] . '" class="btn btn-sm btn-primary" title="Düzenle"><i class="fas fa-edit"></i></a> ';
                                    
                                    if ($row['durum'] == 1) {
                                        echo '<a href="kullanicilar.php?delete=' . $row['id'] . '" class="btn btn-sm btn-danger" title="Deaktif Et" onclick="return confirm(\'Bu kullanıcıyı deaktif etmek istediğinizden emin misiniz?\')"><i class="fas fa-user-slash"></i></a>';
                                    } else {
                                        echo '<a href="kullanicilar.php?activate=' . $row['id'] . '" class="btn btn-sm btn-success" title="Aktif Et" onclick="return confirm(\'Bu kullanıcıyı aktif etmek istediğinizden emin misiniz?\')"><i class="fas fa-user-check"></i></a>';
                                    }
                                    
                                    echo '</div>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="9" class="text-center">Kayıt bulunamadı.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Sayfalama">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1<?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . urlencode($_GET['role']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>" aria-label="İlk">
                                <span aria-hidden="true">&laquo;&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . urlencode($_GET['role']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>" aria-label="Önceki">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . urlencode($_GET['role']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . urlencode($_GET['role']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>" aria-label="Sonraki">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?><?php echo isset($_GET['role']) ? '&role=' . urlencode($_GET['role']) : ''; ?><?php echo isset($_GET['status']) ? '&status=' . urlencode($_GET['status']) : ''; ?>" aria-label="Son">
                                <span aria-hidden="true">&raquo;&raquo;</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <footer class="text-center mt-4 mb-4">
        <p>&copy; <?php echo date("Y"); ?> Kocaeli Üniversitesi - Tüm Hakları Saklıdır.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
