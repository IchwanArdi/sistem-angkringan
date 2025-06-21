<?php
// riwayat.php
require_once 'auth.php';
requireLogin();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$where_clause = "";
$params = [];

if($date_filter) {
    $where_clause = "WHERE DATE(p.tanggal_pesan) = ?";
    $params[] = $date_filter;
}

// Count total records
$count_query = "SELECT COUNT(*) as total FROM pemesanan p $where_clause";
$count_stmt = $db->prepare($count_query);
if($params) {
    $count_stmt->bindParam(1, $params[0]);
}
$count_stmt->execute();
$total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_records / $limit);

// Get orders
$query = "SELECT p.id, p.total_harga, p.tanggal_pesan, p.catatan, pl.nama as nama_pelanggan 
          FROM pemesanan p 
          LEFT JOIN pelanggan pl ON p.pelanggan_id = pl.id 
          $where_clause
          ORDER BY p.tanggal_pesan DESC 
          LIMIT $limit OFFSET $offset";
$stmt = $db->prepare($query);
if($params) {
    $stmt->bindParam(1, $params[0]);
}
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Sistem Angkringan</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>📋 Riwayat Pesanan</h1>
            <p>Lihat semua pesanan yang telah masuk</p>
        </div>
        
        <!-- Filter -->
        <div class="form-container">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="date">Filter Tanggal:</label>
                    <input type="date" id="date" name="date" value="<?php echo $date_filter; ?>">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="riwayat.php" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        
        <!-- Orders Table -->
        <div class="table-container">
            <?php if(count($orders) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Tanggal</th>
                            <th>Catatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo $order['nama_pelanggan'] ? $order['nama_pelanggan'] : 'Pelanggan'; ?></td>
                            <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['tanggal_pesan'])); ?></td>
                            <td><?php echo $order['catatan'] ? $order['catatan'] : '-'; ?></td>
                            <td>
                                <a href="detail_pesanan.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                <div class="pagination">
                    <?php if($page > 1): ?>
                        <a href="?page=<?php echo ($page-1); ?><?php echo $date_filter ? '&date='.$date_filter : ''; ?>" class="pagination-btn">« Sebelumnya</a>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $date_filter ? '&date='.$date_filter : ''; ?>" 
                           class="pagination-btn <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if($page < $total_pages): ?>
                        <a href="?page=<?php echo ($page+1); ?><?php echo $date_filter ? '&date='.$date_filter : ''; ?>" class="pagination-btn">Selanjutnya »</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="no-data">Tidak ada pesanan ditemukan</p>
            <?php endif; ?>
        </div>
    </div>
    
    <style>
    .filter-form {
        display: flex;
        gap: 1rem;
        align-items: end;
    }
    
    .filter-form .form-group {
        margin-bottom: 0;
    }
    
    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
    
    .btn-secondary {
        background: #6c757d;
        color: white;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
        padding: 1rem;
    }
    
    .pagination-btn {
        padding: 0.5rem 1rem;
        background: white;
        color: #333;
        text-decoration: none;
        border: 1px solid #ddd;
        border-radius: 5px;
        transition: all 0.3s;
    }
    
    .pagination-btn:hover {
        background: #f8f9fa;
    }
    
    .pagination-btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: #667eea;
    }
    
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .table-container {
            overflow-x: auto;
        }
    }
    </style>
</body>
</html>