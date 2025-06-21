<?php
// pemesanan.php
require_once 'auth.php';
requireLogin();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_order'])) {
    $nama_pelanggan = $_POST['nama_pelanggan'];
    $catatan = $_POST['catatan'];
    $menu_items = $_POST['menu_items'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    
    if(empty($menu_items)) {
        $error = "Silakan pilih minimal 1 menu!";
    } else {
        try {
            $db->beginTransaction();
            
            // Insert pelanggan (opsional)
            $pelanggan_id = null;
            if(!empty($nama_pelanggan)) {
                $query = "INSERT INTO pelanggan (nama) VALUES (?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$nama_pelanggan]);
                $pelanggan_id = $db->lastInsertId();
            }
            
            // Calculate total
            $total_harga = 0;
            $order_details = [];
            
            foreach($menu_items as $menu_id) {
                $quantity = $quantities[$menu_id] ?? 0;
                if($quantity > 0) {
                    // Get menu price
                    $query = "SELECT harga FROM menu WHERE id = ? AND status = 'tersedia'";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$menu_id]);
                    $menu = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if($menu) {
                        $subtotal = $menu['harga'] * $quantity;
                        $total_harga += $subtotal;
                        $order_details[] = [
                            'menu_id' => $menu_id,
                            'quantity' => $quantity,
                            'harga_satuan' => $menu['harga'],
                            'subtotal' => $subtotal
                        ];
                    }
                }
            }
            
            if($total_harga > 0) {
                // Insert pemesanan
                $query = "INSERT INTO pemesanan (pelanggan_id, total_harga, catatan) VALUES (?, ?, ?)";
                $stmt = $db->prepare($query);
                $stmt->execute([$pelanggan_id, $total_harga, $catatan]);
                $pemesanan_id = $db->lastInsertId();
                
                // Insert detail pemesanan
                foreach($order_details as $detail) {
                    $query = "INSERT INTO detail_pemesanan (pemesanan_id, menu_id, jumlah, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($query);
                    $stmt->execute([$pemesanan_id, $detail['menu_id'], $detail['quantity'], $detail['harga_satuan'], $detail['subtotal']]);
                }
                
                $db->commit();
                $message = "Pesanan berhasil disimpan! Total: Rp " . number_format($total_harga, 0, ',', '.');
                
                // Clear form by redirecting
                header("Location: pemesanan.php?success=1");
                exit();
            } else {
                $db->rollback();
                $error = "Tidak ada menu yang valid dipilih!";
            }
            
        } catch(Exception $e) {
            $db->rollback();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}

// Check for success message
if(isset($_GET['success'])) {
    $message = "Pesanan berhasil disimpan!";
}

// Ambil menu yang tersedia, dikelompokkan per kategori
$query = "SELECT * FROM menu WHERE status = 'tersedia' ORDER BY kategori, nama_menu";
$stmt = $db->prepare($query);
$stmt->execute();
$menu_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group menu by category
$menu_by_category = [];
foreach($menu_list as $menu) {
    $menu_by_category[$menu['kategori']][] = $menu;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan - Sistem Angkringan</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .menu-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .menu-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            font-size: 1.2rem;
            font-weight: bold;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
            padding: 1.5rem;
        }
        
        .menu-item {
            border: 2px solid #eee;
            border-radius: 8px;
            padding: 1rem;
            transition: all 0.3s;
        }
        
        .menu-item:hover {
            border-color: #667eea;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.1);
        }
        
        .menu-item.selected {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .menu-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .menu-name {
            font-weight: bold;
            color: #333;
        }
        
        .menu-price {
            color: #28a745;
            font-weight: bold;
        }
        
        .menu-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .quantity-input {
            width: 60px;
            padding: 0.25rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        
        .order-summary {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            padding: 1.5rem;
            position: sticky;
            top: 100px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            padding-top: 1rem;
            border-top: 2px solid #667eea;
            margin-top: 1rem;
        }
        
        .page-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            align-items: start;
        }
        
        @media (max-width: 768px) {
            .page-layout {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: static;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>🛒 Buat Pesanan Baru</h1>
            <p>Pilih menu untuk pelanggan dan catat pesanannya</p>
        </div>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" id="orderForm">
            <div class="page-layout">
                <!-- Menu Selection -->
                <div class="menu-selection">
                    <?php if(empty($menu_list)): ?>
                        <div class="no-data">
                            <h3>Tidak ada menu tersedia</h3>
                            <p>Silakan tambahkan menu terlebih dahulu di halaman <a href="menu.php">Kelola Menu</a></p>
                        </div>
                    <?php else: ?>
                        <?php foreach($menu_by_category as $kategori => $menus): ?>
                            <div class="menu-section">
                                <div class="menu-header">
                                    <?php 
                                    $icons = ['makanan' => '🍛', 'minuman' => '🥤', 'gorengan' => '🍟'];
                                    echo $icons[$kategori] . ' ' . ucfirst($kategori); 
                                    ?>
                                </div>
                                <div class="menu-grid">
                                    <?php foreach($menus as $menu): ?>
                                        <div class="menu-item" data-menu-id="<?php echo $menu['id']; ?>">
                                            <div class="menu-item-header">
                                                <span class="menu-name"><?php echo $menu['nama_menu']; ?></span>
                                                <span class="menu-price">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></span>
                                            </div>
                                            <div class="menu-controls">
                                                <input type="checkbox" name="menu_items[]" value="<?php echo $menu['id']; ?>" 
                                                       onchange="toggleMenuItem(this)" style="margin-right: 0.5rem;">
                                                <label>Jumlah:</label>
                                                <input type="number" name="quantities[<?php echo $menu['id']; ?>]" 
                                                       class="quantity-input" min="0" max="100" value="0" 
                                                       onchange="updateOrderSummary()" disabled>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Order Summary -->
                <div class="order-summary">
                    <h3>📋 Ringkasan Pesanan</h3>
                    
                    <div class="form-group">
                        <label for="nama_pelanggan">Nama Pelanggan (opsional):</label>
                        <input type="text" id="nama_pelanggan" name="nama_pelanggan" placeholder="Contoh: Pak Budi">
                    </div>
                    
                    <div class="form-group">
                        <label for="catatan">Catatan (opsional):</label>
                        <textarea id="catatan" name="catatan" rows="3" placeholder="Catatan khusus..."></textarea>
                    </div>
                    
                    <div id="orderSummary">
                        <p class="no-data" style="padding: 1rem 0;">Belum ada menu dipilih</p>
                    </div>
                    
                    <div class="order-total" id="orderTotal" style="display: none;">
                        Total: Rp <span id="totalAmount">0</span>
                    </div>
                    
                    <button type="submit" name="submit_order" class="btn btn-primary" id="submitBtn" 
                            style="width: 100%; margin-top: 1rem;" disabled>
                        💾 Simpan Pesanan
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        // Menu data from PHP
        const menuData = <?php echo json_encode($menu_list); ?>;
        const menuMap = {};
        menuData.forEach(menu => {
            menuMap[menu.id] = menu;
        });
        
        function toggleMenuItem(checkbox) {
            const menuItem = checkbox.closest('.menu-item');
            const quantityInput = menuItem.querySelector('.quantity-input');
            
            if(checkbox.checked) {
                quantityInput.disabled = false;
                quantityInput.value = 1;
                menuItem.classList.add('selected');
            } else {
                quantityInput.disabled = true;
                quantityInput.value = 0;
                menuItem.classList.remove('selected');
            }
            
            updateOrderSummary();
        }
        
        function updateOrderSummary() {
            const checkboxes = document.querySelectorAll('input[name="menu_items[]"]:checked');
            const summaryDiv = document.getElementById('orderSummary');
            const totalDiv = document.getElementById('orderTotal');
            const totalAmountSpan = document.getElementById('totalAmount');
            const submitBtn = document.getElementById('submitBtn');
            
            if(checkboxes.length === 0) {
                summaryDiv.innerHTML = '<p class="no-data" style="padding: 1rem 0;">Belum ada menu dipilih</p>';
                totalDiv.style.display = 'none';
                submitBtn.disabled = true;
                return;
            }
            
            let html = '';
            let total = 0;
            let hasValidItems = false;
            
            checkboxes.forEach(checkbox => {
                const menuId = checkbox.value;
                const quantityInput = document.querySelector(`input[name="quantities[${menuId}]"]`);
                const quantity = parseInt(quantityInput.value) || 0;
                
                if(quantity > 0) {
                    const menu = menuMap[menuId];
                    const subtotal = menu.harga * quantity;
                    total += subtotal;
                    hasValidItems = true;
                    
                    html += `
                        <div class="order-item">
                            <div>
                                <strong>${menu.nama_menu}</strong><br>
                                <small>${quantity} × Rp ${menu.harga.toLocaleString('id-ID')}</small>
                            </div>
                            <div>Rp ${subtotal.toLocaleString('id-ID')}</div>
                        </div>
                    `;
                }
            });
            
            summaryDiv.innerHTML = html || '<p class="no-data" style="padding: 1rem 0;">Silakan atur jumlah menu</p>';
            
            if(hasValidItems) {
                totalAmountSpan.textContent = total.toLocaleString('id-ID');
                totalDiv.style.display = 'block';
                submitBtn.disabled = false;
            } else {
                totalDiv.style.display = 'none';
                submitBtn.disabled = true;
            }
        }
        
        // Auto-check checkbox when quantity is changed
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('input', function() {
                const menuItem = this.closest('.menu-item');
                const checkbox = menuItem.querySelector('input[type="checkbox"]');
                
                if(parseInt(this.value) > 0) {
                    checkbox.checked = true;
                    menuItem.classList.add('selected');
                    this.disabled = false;
                } else {
                    checkbox.checked = false;
                    menuItem.classList.remove('selected');
                    this.disabled = true;
                }
                
                updateOrderSummary();
            });
        });
    </script>
</body>
</html>