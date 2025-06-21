<?php
// laporan.php
require_once 'auth.php';
requireLogin();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Default filter
$filter_periode = isset($_GET['periode']) ? $_GET['periode'] : 'hari_ini';
$filter_tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : '';
$filter_tanggal_selesai = isset($_GET['tanggal_selesai']) ? $_GET['tanggal_selesai'] : '';

// Build query conditions based on filter
$where_condition = "WHERE 1=1";
$params = [];

switch($filter_periode) {
    case 'hari_ini':
        $where_condition .= " AND DATE(p.tanggal_pesan) = CURDATE()";
        break;
    case 'minggu_ini':
        $where_condition .= " AND WEEK(p.tanggal_pesan) = WEEK(CURDATE()) AND YEAR(p.tanggal_pesan) = YEAR(CURDATE())";
        break;
    case 'bulan_ini':
        $where_condition .= " AND MONTH(p.tanggal_pesan) = MONTH(CURDATE()) AND YEAR(p.tanggal_pesan) = YEAR(CURDATE())";
        break;
    case 'custom':
        if($filter_tanggal_mulai && $filter_tanggal_selesai) {
            $where_condition .= " AND DATE(p.tanggal_pesan) BETWEEN ? AND ?";
            $params[] = $filter_tanggal_mulai;
            $params[] = $filter_tanggal_selesai;
        }
        break;
}

// Get summary statistics
$query_summary = "SELECT 
    COUNT(*) as total_pesanan,
    COALESCE(SUM(p.total_harga), 0) as total_pendapatan,
    COALESCE(AVG(p.total_harga), 0) as rata_rata_pesanan
    FROM pemesanan p $where_condition";

$stmt_summary = $db->prepare($query_summary);
if(!empty($params)) {
    foreach($params as $i => $param) {
        $stmt_summary->bindValue($i + 1, $param);
    }
}
$stmt_summary->execute();
$summary = $stmt_summary->fetch(PDO::FETCH_ASSOC);

// Get menu terlaris
$query_terlaris = "SELECT 
    m.nama_menu,
    m.kategori,
    SUM(dp.jumlah) as total_terjual,
    SUM(dp.subtotal) as total_pendapatan_menu
    FROM detail_pemesanan dp
    JOIN menu m ON dp.menu_id = m.id
    JOIN pemesanan p ON dp.pemesanan_id = p.id
    $where_condition
    GROUP BY m.id, m.nama_menu, m.kategori
    ORDER BY total_terjual DESC
    LIMIT 10";

$stmt_terlaris = $db->prepare($query_terlaris);
if(!empty($params)) {
    foreach($params as $i => $param) {
        $stmt_terlaris->bindValue($i + 1, $param);
    }
}
$stmt_terlaris->execute();
$menu_terlaris = $stmt_terlaris->fetchAll(PDO::FETCH_ASSOC);

// Get penjualan per kategori
$query_kategori = "SELECT 
    m.kategori,
    COUNT(DISTINCT dp.pemesanan_id) as jumlah_pesanan,
    SUM(dp.jumlah) as total_item,
    SUM(dp.subtotal) as total_pendapatan
    FROM detail_pemesanan dp
    JOIN menu m ON dp.menu_id = m.id
    JOIN pemesanan p ON dp.pemesanan_id = p.id
    $where_condition
    GROUP BY m.kategori
    ORDER BY total_pendapatan DESC";

$stmt_kategori = $db->prepare($query_kategori);
if(!empty($params)) {
    foreach($params as $i => $param) {
        $stmt_kategori->bindValue($i + 1, $param);
    }
}
$stmt_kategori->execute();
$penjualan_kategori = $stmt_kategori->fetchAll(PDO::FETCH_ASSOC);

// Get penjualan harian (untuk grafik)
$query_harian = "SELECT 
    DATE(p.tanggal_pesan) as tanggal,
    COUNT(*) as jumlah_pesanan,
    SUM(p.total_harga) as total_pendapatan
    FROM pemesanan p 
    $where_condition
    GROUP BY DATE(p.tanggal_pesan)
    ORDER BY tanggal DESC
    LIMIT 30";

$stmt_harian = $db->prepare($query_harian);
if(!empty($params)) {
    foreach($params as $i => $param) {
        $stmt_harian->bindValue($i + 1, $param);
    }
}
$stmt_harian->execute();
$penjualan_harian = $stmt_harian->fetchAll(PDO::FETCH_ASSOC);

// Get periode label for display
$periode_label = '';
switch($filter_periode) {
    case 'hari_ini': $periode_label = 'Hari Ini'; break;
    case 'minggu_ini': $periode_label = 'Minggu Ini'; break;
    case 'bulan_ini': $periode_label = 'Bulan Ini'; break;
    case 'custom': 
        if($filter_tanggal_mulai && $filter_tanggal_selesai) {
            $periode_label = date('d/m/Y', strtotime($filter_tanggal_mulai)) . ' - ' . date('d/m/Y', strtotime($filter_tanggal_selesai));
        } else {
            $periode_label = 'Semua Data';
        }
        break;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Sistem Angkringan</title>
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>📊 Laporan Penjualan</h1>
            <p>Analisis performa penjualan angkringan Anda</p>
        </div>
        
        <!-- Filter Periode -->
        <div class="form-container">
            <h2>🔍 Filter Laporan</h2>
            <form method="GET" class="form-grid">
                <div class="form-group">
                    <label for="periode">Periode:</label>
                    <select name="periode" id="periode" onchange="toggleCustomDate()">
                        <option value="hari_ini" <?php echo $filter_periode == 'hari_ini' ? 'selected' : ''; ?>>Hari Ini</option>
                        <option value="minggu_ini" <?php echo $filter_periode == 'minggu_ini' ? 'selected' : ''; ?>>Minggu Ini</option>
                        <option value="bulan_ini" <?php echo $filter_periode == 'bulan_ini' ? 'selected' : ''; ?>>Bulan Ini</option>
                        <option value="custom" <?php echo $filter_periode == 'custom' ? 'selected' : ''; ?>>Pilih Tanggal</option>
                    </select>
                </div>
                
                <div class="form-group" id="custom-date" style="<?php echo $filter_periode == 'custom' ? '' : 'display: none;'; ?>">
                    <label for="tanggal_mulai">Tanggal Mulai:</label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="<?php echo $filter_tanggal_mulai; ?>">
                </div>
                
                <div class="form-group" id="custom-date-end" style="<?php echo $filter_periode == 'custom' ? '' : 'display: none;'; ?>">
                    <label for="tanggal_selesai">Tanggal Selesai:</label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" value="<?php echo $filter_tanggal_selesai; ?>">
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary">Tampilkan Laporan</button>
                </div>
            </form>
        </div>
        
        <!-- Summary Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <h3><?php echo number_format($summary['total_pesanan']); ?></h3>
                    <p>Total Pesanan</p>
                    <small><?php echo $periode_label; ?></small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>Rp <?php echo number_format($summary['total_pendapatan'], 0, ',', '.'); ?></h3>
                    <p>Total Pendapatan</p>
                    <small><?php echo $periode_label; ?></small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-info">
                    <h3>Rp <?php echo number_format($summary['rata_rata_pesanan'], 0, ',', '.'); ?></h3>
                    <p>Rata-rata per Pesanan</p>
                    <small><?php echo $periode_label; ?></small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏆</div>
                <div class="stat-info">
                    <h3><?php echo count($menu_terlaris) > 0 ? $menu_terlaris[0]['nama_menu'] : 'Tidak ada'; ?></h3>
                    <p>Menu Terlaris</p>
                    <small><?php echo count($menu_terlaris) > 0 ? $menu_terlaris[0]['total_terjual'] . ' terjual' : ''; ?></small>
                </div>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <!-- Grafik Penjualan Harian -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>📊 Grafik Penjualan Harian</h2>
                </div>
                <div class="card-content">
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <!-- Penjualan per Kategori -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>🍽️ Penjualan per Kategori</h2>
                </div>
                <div class="card-content">
                    <?php if(count($penjualan_kategori) > 0): ?>
                        <div class="kategori-stats">
                            <?php foreach($penjualan_kategori as $kategori): ?>
                                <div class="kategori-item">
                                    <div class="kategori-info">
                                        <h4><?php echo ucfirst($kategori['kategori']); ?></h4>
                                        <p><?php echo $kategori['jumlah_pesanan']; ?> pesanan • <?php echo $kategori['total_item']; ?> item</p>
                                    </div>
                                    <div class="kategori-revenue">
                                        <strong>Rp <?php echo number_format($kategori['total_pendapatan'], 0, ',', '.'); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">Belum ada data penjualan</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Menu Terlaris -->
        <div class="table-container">
            <div class="card-header">
                <h2>🔥 Menu Terlaris</h2>
            </div>
            <div class="card-content" style="padding: 0;">
                <?php if(count($menu_terlaris) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Ranking</th>
                                <th>Nama Menu</th>
                                <th>Kategori</th>
                                <th>Total Terjual</th>
                                <th>Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($menu_terlaris as $index => $menu): ?>
                                <tr>
                                    <td>
                                        <span class="rank">#<?php echo ($index + 1); ?></span>
                                    </td>
                                    <td><strong><?php echo $menu['nama_menu']; ?></strong></td>
                                    <td>
                                        <span class="kategori-badge kategori-<?php echo $menu['kategori']; ?>">
                                            <?php echo ucfirst($menu['kategori']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $menu['total_terjual']; ?> item</td>
                                    <td><strong>Rp <?php echo number_format($menu['total_pendapatan_menu'], 0, ',', '.'); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">Belum ada data penjualan</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Penjualan Harian Detail -->
        <div class="table-container">
            <div class="card-header">
                <h2>📅 Detail Penjualan Harian</h2>
            </div>
            <div class="card-content" style="padding: 0;">
                <?php if(count($penjualan_harian) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jumlah Pesanan</th>
                                <th>Total Pendapatan</th>
                                <th>Rata-rata per Pesanan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($penjualan_harian as $harian): ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($harian['tanggal'])); ?></td>
                                    <td><?php echo $harian['jumlah_pesanan']; ?> pesanan</td>
                                    <td><strong>Rp <?php echo number_format($harian['total_pendapatan'], 0, ',', '.'); ?></strong></td>
                                    <td>Rp <?php echo number_format($harian['total_pendapatan'] / $harian['jumlah_pesanan'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">Belum ada data penjualan</p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Export Buttons -->
        <div class="export-actions">
            <h2>📤 Export Laporan</h2>
            <div class="action-buttons">
                <a href="export_pdf.php?<?php echo http_build_query($_GET); ?>" class="action-btn" target="_blank">
                    <span class="btn-icon">📄</span>
                    <span>Export PDF</span>
                </a>
                <a href="export_excel.php?<?php echo http_build_query($_GET); ?>" class="action-btn">
                    <span class="btn-icon">📊</span>
                    <span>Export Excel</span>
                </a>
                <button onclick="window.print()" class="action-btn">
                    <span class="btn-icon">🖨️</span>
                    <span>Cetak Laporan</span>
                </button>
            </div>
        </div>
    </div>
    
    <script>
        function toggleCustomDate() {
            const periode = document.getElementById('periode').value;
            const customDate = document.getElementById('custom-date');
            const customDateEnd = document.getElementById('custom-date-end');
            
            if(periode === 'custom') {
                customDate.style.display = 'block';
                customDateEnd.style.display = 'block';
            } else {
                customDate.style.display = 'none';
                customDateEnd.style.display = 'none';
            }
        }
        
        // Chart.js configuration
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesData = <?php echo json_encode(array_reverse($penjualan_harian)); ?>;
        
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.map(data => {
                    const date = new Date(data.tanggal);
                    return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit' });
                }),
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: salesData.map(data => data.total_pendapatan),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Jumlah Pesanan',
                    data: salesData.map(data => data.jumlah_pesanan),
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Tren Penjualan Harian'
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Pendapatan (Rp)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Jumlah Pesanan'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    </script>
    
    <style>
        .kategori-stats {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .kategori-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .kategori-info h4 {
            margin-bottom: 0.25rem;
            color: #333;
        }
        
        .kategori-info p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .kategori-revenue {
            color: #28a745;
            font-size: 1.1rem;
        }
        
        .kategori-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .kategori-makanan { background: #e3f2fd; color: #1976d2; }
        .kategori-minuman { background: #f3e5f5; color: #7b1fa2; }
        .kategori-gorengan { background: #fff3e0; color: #f57c00; }
        
        .export-actions {
            margin-top: 2rem;
            text-align: center;
        }
        
        .export-actions h2 {
            color: #333;
            margin-bottom: 1rem;
        }
        
        @media print {
            .navbar, .export-actions, .form-container {
                display: none;
            }
            
            .container {
                max-width: 100%;
                margin: 0;
                padding: 1rem;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</body>
</html>