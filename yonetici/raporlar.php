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

// Durum bazlı başvuru sayıları
$durum_sql = "SELECT durum, COUNT(*) as sayi FROM basvurular GROUP BY durum";
$durum_result = mysqli_query($conn, $durum_sql);
$durum_counts = [];
while ($row = mysqli_fetch_assoc($durum_result)) {
    $durum_counts[$row['durum']] = $row['sayi'];
}

// Jüri atama sayısı
$juri_sql = "SELECT COUNT(*) as toplam FROM juri_atamalari";
$juri_result = mysqli_query($conn, $juri_sql);
$juri_count = mysqli_fetch_assoc($juri_result)['toplam'];

// Fakülte bazlı ilan sayıları
$fakulte_sql = "SELECT fakulte_birim, COUNT(*) as sayi FROM ilanlar GROUP BY fakulte_birim";
$fakulte_result = mysqli_query($conn, $fakulte_sql);
$fakulte_counts = [];
while ($row = mysqli_fetch_assoc($fakulte_result)) {
    $fakulte_counts[$row['fakulte_birim']] = $row['sayi'];
}

// Kadro ünvanı bazlı ilan sayıları
$unvan_sql = "SELECT kadro_unvani, COUNT(*) as sayi FROM ilanlar GROUP BY kadro_unvani";
$unvan_result = mysqli_query($conn, $unvan_sql);
$unvan_counts = [];
while ($row = mysqli_fetch_assoc($unvan_result)) {
    $unvan_counts[$row['kadro_unvani']] = $row['sayi'];
}

// Aylık başvuru sayıları
$aylik_sql = "SELECT DATE_FORMAT(basvuru_tarihi, '%Y-%m') as ay, COUNT(*) as sayi 
              FROM basvurular 
              GROUP BY DATE_FORMAT(basvuru_tarihi, '%Y-%m') 
              ORDER BY ay DESC 
              LIMIT 12";
$aylik_result = mysqli_query($conn, $aylik_sql);
$aylik_counts = [];
while ($row = mysqli_fetch_assoc($aylik_result)) {
    $aylik_counts[$row['ay']] = $row['sayi'];
}

// Jüri değerlendirme istatistikleri
$degerlendirme_sql = "SELECT jd.sonuc, COUNT(*) as sayi 
                      FROM juri_degerlendirmeleri jd 
                      GROUP BY jd.sonuc";
$degerlendirme_result = mysqli_query($conn, $degerlendirme_sql);
$degerlendirme_counts = [];
while ($row = mysqli_fetch_assoc($degerlendirme_result)) {
    $degerlendirme_counts[$row['sonuc']] = $row['sayi'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar - Yönetici Paneli</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php include("navbar.php"); ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Ana Sayfa</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Raporlar</li>
                    </ol>
                </nav>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-12">
                <h2><i class="fas fa-chart-bar"></i> Sistem Raporları</h2>
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
                </div>
            </div>
            
            <div class="col-md-3 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Jüri Ataması</h6>
                                <h2 class="mb-0"><?php echo $juri_count; ?></h2>
                            </div>
                            <i class="fas fa-users fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-clipboard-list"></i> Başvuru Durumları</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="applicationStatusChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-check-double"></i> Jüri Değerlendirmeleri</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="juryEvaluationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-chart-line"></i> Aylık Başvuru İstatistikleri</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyApplicationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-warning text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-university"></i> Fakülte/Birim Bazlı İlan Dağılımı</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="facultyChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-user-tie"></i> Kadro Ünvanı Bazlı İlan Dağılımı</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="positionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer class="text-center mt-4 mb-4">
        <p>&copy; <?php echo date("Y"); ?> Kocaeli Üniversitesi - Tüm Hakları Saklıdır.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Başvuru durumları grafiği
        const applicationStatusChart = new Chart(
            document.getElementById('applicationStatusChart'),
            {
                type: 'doughnut',
                data: {
                    labels: [
                        'Beklemede', 
                        'Onaylandı', 
                        'Reddedildi'
                    ],
                    datasets: [{
                        data: [
                            <?php echo isset($durum_counts['Beklemede']) ? $durum_counts['Beklemede'] : 0; ?>,
                            <?php echo isset($durum_counts['Onaylandı']) ? $durum_counts['Onaylandı'] : 0; ?>,
                            <?php echo isset($durum_counts['Reddedildi']) ? $durum_counts['Reddedildi'] : 0; ?>
                        ],
                        backgroundColor: [
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)'
                        ],
                        borderColor: [
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        title: {
                            display: true,
                            text: 'Başvuru Durumları'
                        }
                    }
                }
            }
        );
        
        // Jüri değerlendirmeleri grafiği
        const juryEvaluationChart = new Chart(
            document.getElementById('juryEvaluationChart'),
            {
                type: 'pie',
                data: {
                    labels: [
                        'Olumlu', 
                        'Olumsuz'
                    ],
                    datasets: [{
                        data: [
                            <?php echo isset($degerlendirme_counts['Olumlu']) ? $degerlendirme_counts['Olumlu'] : 0; ?>,
                            <?php echo isset($degerlendirme_counts['Olumsuz']) ? $degerlendirme_counts['Olumsuz'] : 0; ?>
                        ],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)'
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 99, 132, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'right',
                        },
                        title: {
                            display: true,
                            text: 'Jüri Değerlendirmeleri'
                        }
                    }
                }
            }
        );
        
        // Aylık başvuru istatistikleri grafiği
        const monthlyLabels = <?php echo json_encode(array_keys($aylik_counts)); ?>;
        const monthlyData = <?php echo json_encode(array_values($aylik_counts)); ?>;
        
        const monthlyApplicationChart = new Chart(
            document.getElementById('monthlyApplicationChart'),
            {
                type: 'bar',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Başvuru Sayısı',
                        data: monthlyData,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Aylık Başvuru Sayıları'
                        }
                    }
                }
            }
        );
        
        // Fakülte/Birim bazlı ilan dağılımı grafiği
        const facultyLabels = <?php echo json_encode(array_keys($fakulte_counts)); ?>;
        const facultyData = <?php echo json_encode(array_values($fakulte_counts)); ?>;
        
        const facultyChart = new Chart(
            document.getElementById('facultyChart'),
            {
                type: 'bar',
                data: {
                    labels: facultyLabels,
                    datasets: [{
                        label: 'İlan Sayısı',
                        data: facultyData,
                        backgroundColor: 'rgba(255, 206, 86, 0.8)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Fakülte/Birim Bazlı İlan Dağılımı'
                        }
                    }
                }
            }
        );
        
        // Kadro ünvanı bazlı ilan dağılımı grafiği
        const positionLabels = <?php echo json_encode(array_keys($unvan_counts)); ?>;
        const positionData = <?php echo json_encode(array_values($unvan_counts)); ?>;
        
        const positionChart = new Chart(
            document.getElementById('positionChart'),
            {
                type: 'bar',
                data: {
                    labels: positionLabels,
                    datasets: [{
                        label: 'İlan Sayısı',
                        data: positionData,
                        backgroundColor: 'rgba(153, 102, 255, 0.8)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Kadro Ünvanı Bazlı İlan Dağılımı'
                        }
                    }
                }
            }
        );
    </script>
</body>
</html>
