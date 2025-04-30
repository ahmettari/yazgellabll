<?php
session_start();
include("../db_connection.php");

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'juri') {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// İstatistikleri getir
// Jüri üyesinin atandığı ilan sayısı
$ilan_sql = "SELECT COUNT(DISTINCT ilan_id) as toplam FROM juri_atamalari WHERE juri_id = $user_id";
$ilan_result = mysqli_query($conn, $ilan_sql);
$ilan_count = mysqli_fetch_assoc($ilan_result)['toplam'];

// Değerlendirilecek toplam başvuru sayısı
$basvuru_sql = "SELECT COUNT(DISTINCT b.id) as toplam 
                FROM basvurular b 
                JOIN juri_atamalari ja ON b.ilan_id = ja.ilan_id 
                WHERE ja.juri_id = $user_id";
$basvuru_result = mysqli_query($conn, $basvuru_sql);
$basvuru_count = mysqli_fetch_assoc($basvuru_result)['toplam'];

// Değerlendirilen başvuru sayısı
$degerlendirilen_sql = "SELECT COUNT(*) as toplam 
                        FROM juri_degerlendirmeleri 
                        WHERE juri_id = $user_id";
$degerlendirilen_result = mysqli_query($conn, $degerlendirilen_sql);
$degerlendirilen_count = mysqli_fetch_assoc($degerlendirilen_result)['toplam'];

// Bekleyen başvuru sayısı
$bekleyen_count = $basvuru_count - $degerlendirilen_count;

// Değerlendirme sonuçları
$sonuc_sql = "SELECT sonuc, COUNT(*) as sayi 
              FROM juri_degerlendirmeleri 
              WHERE juri_id = $user_id 
              GROUP BY sonuc";
$sonuc_result = mysqli_query($conn, $sonuc_sql);
$sonuc_counts = [];
while ($row = mysqli_fetch_assoc($sonuc_result)) {
    $sonuc_counts[$row['sonuc']] = $row['sayi'];
}

// İlan bazlı değerlendirme sayıları
$ilan_degerlendirme_sql = "SELECT i.ilan_baslik, COUNT(jd.id) as degerlendirme_sayisi 
                           FROM juri_degerlendirmeleri jd 
                           JOIN basvurular b ON jd.basvuru_id = b.id 
                           JOIN ilanlar i ON b.ilan_id = i.id 
                           WHERE jd.juri_id = $user_id 
                           GROUP BY i.id";
$ilan_degerlendirme_result = mysqli_query($conn, $ilan_degerlendirme_sql);
$ilan_degerlendirme_counts = [];
while ($row = mysqli_fetch_assoc($ilan_degerlendirme_result)) {
    $ilan_degerlendirme_counts[$row['ilan_baslik']] = $row['degerlendirme_sayisi'];
}

// Aylık değerlendirme sayıları
$aylik_sql = "SELECT DATE_FORMAT(degerlendirme_tarihi, '%Y-%m') as ay, COUNT(*) as sayi 
              FROM juri_degerlendirmeleri 
              WHERE juri_id = $user_id 
              GROUP BY DATE_FORMAT(degerlendirme_tarihi, '%Y-%m') 
              ORDER BY ay DESC 
              LIMIT 12";
$aylik_result = mysqli_query($conn, $aylik_sql);
$aylik_counts = [];
while ($row = mysqli_fetch_assoc($aylik_result)) {
    $aylik_counts[$row['ay']] = $row['sayi'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raporlar - Jüri Paneli</title>
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
                <h2><i class="fas fa-chart-bar"></i> Değerlendirme Raporları</h2>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Atanan İlan</h6>
                                <h2 class="mb-0"><?php echo $ilan_count; ?></h2>
                            </div>
                            <i class="fas fa-bullhorn fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Değerlendirilen</h6>
                                <h2 class="mb-0"><?php echo $degerlendirilen_count; ?></h2>
                            </div>
                            <i class="fas fa-check-circle fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase">Bekleyen</h6>
                                <h2 class="mb-0"><?php echo $bekleyen_count; ?></h2>
                            </div>
                            <i class="fas fa-clock fa-3x opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-clipboard-check"></i> Değerlendirme Sonuçları</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="evaluationResultChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-bullhorn"></i> İlan Bazlı Değerlendirmeler</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="advertisementEvaluationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-chart-line"></i> Aylık Değerlendirme İstatistikleri</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyEvaluationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="card-title mb-0"><i class="fas fa-tasks"></i> Değerlendirme Durumu Özeti</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Metrik</th>
                                        <th>Değer</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Atanan İlan Sayısı</td>
                                        <td><?php echo $ilan_count; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Toplam Değerlendirilecek Başvuru</td>
                                        <td><?php echo $basvuru_count; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Değerlendirilen Başvuru</td>
                                        <td><?php echo $degerlendirilen_count; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Bekleyen Başvuru</td>
                                        <td><?php echo $bekleyen_count; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Olumlu Değerlendirme</td>
                                        <td><?php echo isset($sonuc_counts['Olumlu']) ? $sonuc_counts['Olumlu'] : 0; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Olumsuz Değerlendirme</td>
                                        <td><?php echo isset($sonuc_counts['Olumsuz']) ? $sonuc_counts['Olumsuz'] : 0; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tamamlanma Oranı</td>
                                        <td>
                                            <?php 
                                            $tamamlanma_orani = ($basvuru_count > 0) ? round(($degerlendirilen_count / $basvuru_count) * 100) : 0;
                                            echo $tamamlanma_orani . '%';
                                            ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
    <script>
        // Değerlendirme sonuçları grafiği
        const evaluationResultChart = new Chart(
            document.getElementById('evaluationResultChart'),
            {
                type: 'pie',
                data: {
                    labels: [
                        'Olumlu', 
                        'Olumsuz'
                    ],
                    datasets: [{
                        data: [
                            <?php echo isset($sonuc_counts['Olumlu']) ? $sonuc_counts['Olumlu'] : 0; ?>,
                            <?php echo isset($sonuc_counts['Olumsuz']) ? $sonuc_counts['Olumsuz'] : 0; ?>
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
                            text: 'Değerlendirme Sonuçları'
                        }
                    }
                }
            }
        );
        
        // İlan bazlı değerlendirme sayıları grafiği
        const advertisementLabels = <?php echo json_encode(array_keys($ilan_degerlendirme_counts)); ?>;
        const advertisementData = <?php echo json_encode(array_values($ilan_degerlendirme_counts)); ?>;
        
        const advertisementEvaluationChart = new Chart(
            document.getElementById('advertisementEvaluationChart'),
            {
                type: 'bar',
                data: {
                    labels: advertisementLabels,
                    datasets: [{
                        label: 'Değerlendirme Sayısı',
                        data: advertisementData,
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgba(54, 162, 235, 1)',
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
                            text: 'İlan Bazlı Değerlendirmeler'
                        }
                    }
                }
            }
        );
        
        // Aylık değerlendirme istatistikleri grafiği
        const monthlyLabels = <?php echo json_encode(array_keys($aylik_counts)); ?>;
        const monthlyData = <?php echo json_encode(array_values($aylik_counts)); ?>;
        
        const monthlyEvaluationChart = new Chart(
            document.getElementById('monthlyEvaluationChart'),
            {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Değerlendirme Sayısı',
                        data: monthlyData,
                        backgroundColor: 'rgba(153, 102, 255, 0.8)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 2,
                        tension: 0.1
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
                            text: 'Aylık Değerlendirme Sayıları'
                        }
                    }
                }
            }
        );
    </script>
</body>
</html>
