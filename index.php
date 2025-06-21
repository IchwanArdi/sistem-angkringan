<?php
// index.php
require_once 'auth.php';
requireLogin();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Ambil statistik hari ini
$today = date('Y-m-d');
$query_today = "SELECT COUNT(*) as total_pesanan, COALESCE(SUM(total_harga), 0) as total_pendapatan 
                FROM pemesanan WHERE DATE(tanggal_pesan) = ?";
$stmt_today = $db->prepare($query_today);
$stmt_today->bindParam(1, $today);
$stmt_today->execute();
$stats_today = $stmt_today->fetch(PDO::FETCH_ASSOC);

// Ambil statistik bulan ini
$this_month = date('Y-m');
$query_month = "SELECT COUNT(*) as total_pesanan, COALESCE(SUM(total_harga), 0) as total_pendapatan 
                FROM pemesanan WHERE DATE_FORMAT(tanggal_pesan, '%Y-%m') = ?";
$stmt_month = $db->prepare($query_month);
$stmt_month->bindParam(1, $this_month);
$stmt_month->execute();
$stats_month = $stmt_month->fetch(PDO::FETCH_ASSOC);

// Ambil menu paling laris (top 5)
$query_popular = "SELECT m.nama_menu, SUM(dp.jumlah) as total_terjual 
                  FROM detail_pemesanan dp 
                  JOIN menu m ON dp.menu_id = m.id 
                  GROUP BY m.id, m.nama_menu 
                  ORDER BY total_terjual DESC 
                  LIMIT 5";
$stmt_popular = $db->prepare($query_popular);
$stmt_popular->execute();
$popular_menu = $stmt_popular->fetchAll(PDO::FETCH_ASSOC);

// Ambil pesanan terbaru (5 pesanan terakhir)
$query_recent = "SELECT p.id, pl.nama as nama_pelanggan, p.total_harga, p.tanggal_pesan 
                 FROM pemesanan p 
                 LEFT JOIN pelanggan pl ON p.pelanggan_id = pl.id 
                 ORDER BY p.tanggal_pesan DESC 
                 LIMIT 5";
$stmt_recent = $db->prepare($query_recent);
$stmt_recent->execute();
$recent_orders = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Angkringan</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>🍛 Dashboard Angkringan</h1>
            <p>Selamat datang, <?php echo $_SESSION['nama']; ?>! Kelola angkringan Anda dengan mudah.</p>
        </div>
        
        <!-- Statistik Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3><?php echo $stats_today['total_pesanan']; ?></h3>
                    <p>Pesanan Hari Ini</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>Rp <?php echo number_format($stats_today['total_pendapatan'], 0, ',', '.'); ?></h3>
                    <p>Pendapatan Hari Ini</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-info">
                    <h3><?php echo $stats_month['total_pesanan']; ?></h3>
                    <p>Pesanan Bulan Ini</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🏆</div>
                <div class="stat-info">
                    <h3>Rp <?php echo number_format($stats_month['total_pendapatan'], 0, ',', '.'); ?></h3>
                    <p>Pendapatan Bulan Ini</p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <!-- Menu Paling Laris -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>🔥 Menu Paling Laris</h2>
                </div>
                <div class="card-content">
                    <?php if(count($popular_menu) > 0): ?>
                        <div class="popular-list">
                            <?php foreach($popular_menu as $index => $menu): ?>
                                <div class="popular-item">
                                    <span class="rank">#<?php echo ($index + 1); ?></span>
                                    <span class="menu-name"><?php echo $menu['nama_menu']; ?></span>
                                    <span class="sold-count"><?php echo $menu['total_terjual']; ?> terjual</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">Belum ada data penjualan</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pesanan Terbaru -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>🕒 Pesanan Terbaru</h2>
                </div>
                <div class="card-content">
                    <?php if(count($recent_orders) > 0): ?>
                        <div class="recent-orders">
                            <?php foreach($recent_orders as $order): ?>
                                <div class="order-item">
                                    <div class="order-info">
                                        <strong>Pesanan #<?php echo $order['id']; ?></strong>
                                        <p><?php echo $order['nama_pelanggan'] ? $order['nama_pelanggan'] : 'Pelanggan'; ?></p>
                                        <small><?php echo date('d/m/Y H:i', strtotime($order['tanggal_pesan'])); ?></small>
                                    </div>
                                    <div class="order-total">
                                        Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">Belum ada pesanan</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>⚡ Aksi Cepat</h2>
            <div class="action-buttons">
                <a href="pemesanan.php" class="action-btn primary">
                    <span class="btn-icon">🛒</span>
                    <span>Buat Pesanan Baru</span>
                </a>
                <a href="menu.php" class="action-btn">
                    <span class="btn-icon">🍽️</span>
                    <span>Kelola Menu</span>
                </a>
                <a href="laporan.php" class="action-btn">
                    <span class="btn-icon">📊</span>
                    <span>Lihat Laporan</span>
                </a>
                <a href="riwayat.php" class="action-btn">
                    <span class="btn-icon">📋</span>
                    <span>Riwayat Pesanan</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>