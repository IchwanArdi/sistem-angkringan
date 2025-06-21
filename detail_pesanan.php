<?php
// detail_pesanan.php
require_once 'auth.php';
requireLogin();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$pesanan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if(!$pesanan_id) {
    header("Location: riwayat.php");
    exit();
}

// Get order details
$query = "SELECT p.*, pl.nama as nama_pelanggan 
          FROM pemesanan p 
          LEFT JOIN pelanggan pl ON p.pelanggan_id = pl.id 
          WHERE p.id = ?";
$stmt = $db->prepare($query);
$stmt->bindParam(1, $pesanan_id);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    header("Location: riwayat.php");
    exit();
}

// Get order items
$query_items = "SELECT dp.*, m.nama_menu, m.kategori 
                FROM detail_pemesanan dp 
                JOIN menu m ON dp.menu_id = m.id 
                WHERE dp.pemesanan_id = ?";
$stmt_items = $db->prepare($query_items);
$stmt_items->bindParam(1, $pesanan_id);
$stmt_items->execute();
$items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo $order['id']; ?> - Sistem Angkringan</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>📋 Detail Pesanan #<?php echo $order['id']; ?></h1>
            <p>Informasi lengkap pesanan</p>
        </div>
        
        <div class="detail-grid">
            <!-- Order Info -->
            <div class="detail-card">
                <div class="card-header">
                    <h2>ℹ️ Informasi Pesanan</h2>
                </div>
                <div class="card-content">
                    <div class="info-row">
                        <span class="info-label">ID Pesanan:</span>
                        <span class="info-value">#<?php echo $order['id']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Pelanggan:</span>
                        <span class="info-value"><?php echo $order['nama_pelanggan'] ? $order['nama_pelanggan'] : 'Pelanggan'; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Pesan:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i:s', strtotime($order['tanggal_pesan'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Total Harga:</span>
                        <span class="info-value total-price">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span>
                    </div>
                    <?php if($order['catatan']): ?>
                    <div class="info-row">
                        <span class="info-label">Catatan:</span>
                        <span class="info-value"><?php echo $order['catatan']; ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="detail-card">
                <div class="card-header">
                    <h2>🍽️ Item Pesanan</h2>
                </div>
                <div class="card-content">
                    <div class="items-list">
                        <?php foreach($items as $item): ?>
                        <div class="item-row">
                            <div class="item-info">
                                <h4><?php echo $item['nama_menu']; ?></h4>
                                <p class="item-category"><?php echo ucfirst($item['kategori']); ?></p>
                            </div>
                            <div class="item-quantity">
                                <span class="quantity-badge"><?php echo $item['jumlah']; ?>x</span>
                            </div>
                            <div class="item-price">
                                <div class="unit-price">@ Rp <?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?></div>
                                <div class="subtotal">Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="total-summary">
                        <div class="summary-row">
                            <span>Total Item:</span>
                            <span><?php echo array_sum(array_column($items, 'jumlah')); ?> item</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total Harga:</span>
                            <span>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="actions">
            <a href="riwayat.php" class="btn btn-secondary">← Kembali ke Riwayat</a>
            <button onclick="window.print()" class="btn btn-primary">🖨️ Cetak</button>
        </div>
    </div>
    
    <style>
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 2fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .detail-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .info-row:last-child {
        border-bottom: none;
    }
    
    .info-label {
        font-weight: 500;
        color: #666;
    }
    
    .info-value {
        color: #333;
    }
    
    .total-price {
        font-weight: bold;
        color: #28a745;
        font-size: 1.1rem;
    }
    
    .items-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .item-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .item-info {
        flex: 1;
    }
    
    .item-info h4 {
        color: #333;
        margin-bottom: 0.25rem;
    }
    
    .item-category {
        color: #666;
        font-size: 0.9rem;
        margin: 0;
    }
    
    .quantity-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-weight: bold;
        font-size: 0.9rem;
    }
    
    .item-price {
        text-align: right;
    }
    
    .unit-price {
        color: #666;
        font-size: 0.9rem;
    }
    
    .subtotal {
        font-weight: bold;
        color: #333;
    }
    
    .total-summary {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px solid #e9ecef;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
    }
    
    .summary-row.total {
        font-size: 1.1rem;
        font-weight: bold;
        color: #28a745;
        border-top: 1px solid #e9ecef;
        margin-top: 0.5rem;
        padding-top: 1rem;
    }
    
    .actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
    }
    
    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
        
        .item-row {
            flex-direction: column;
            text-align: center;
            gap: 0.5rem;
        }
        
        .item-price {
            text-align: center;
        }
        
        .actions {
            flex-direction: column;
        }
    }
    
    /* Print styles */
    @media print {
        .navbar, .actions {
            display: none;
        }
        
        .container {
            max-width: none;
            margin: 0;
            padding: 1rem;
        }
        
        .detail-card {
            box-shadow: none;
            border: 1px solid #ddd;
        }
    }
    </style>
</body>
</html>