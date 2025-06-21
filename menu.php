<?php
// menu.php
require_once 'auth.php';
requireLogin();
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        
        // Tambah menu baru
        if($_POST['action'] == 'add') {
            $nama_menu = $_POST['nama_menu'];
            $harga = $_POST['harga'];
            $kategori = $_POST['kategori'];
            
            $query = "INSERT INTO menu (nama_menu, harga, kategori) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            
            if($stmt->execute([$nama_menu, $harga, $kategori])) {
                $message = "Menu berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan menu!";
            }
        }
        
        // Edit menu
        if($_POST['action'] == 'edit') {
            $id = $_POST['id'];
            $nama_menu = $_POST['nama_menu'];
            $harga = $_POST['harga'];
            $kategori = $_POST['kategori'];
            $status = $_POST['status'];
            
            $query = "UPDATE menu SET nama_menu = ?, harga = ?, kategori = ?, status = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if($stmt->execute([$nama_menu, $harga, $kategori, $status, $id])) {
                $message = "Menu berhasil diupdate!";
            } else {
                $error = "Gagal mengupdate menu!";
            }
        }
        
        // Hapus menu
        if($_POST['action'] == 'delete') {
            $id = $_POST['id'];
            
            $query = "DELETE FROM menu WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if($stmt->execute([$id])) {
                $message = "Menu berhasil dihapus!";
            } else {
                $error = "Gagal menghapus menu!";
            }
        }
        
        // Update status menu
        if($_POST['action'] == 'toggle_status') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            
            $query = "UPDATE menu SET status = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if($stmt->execute([$status, $id])) {
                $message = "Status menu berhasil diupdate!";
            } else {
                $error = "Gagal mengupdate status menu!";
            }
        }
    }
}

// Ambil semua menu
$query = "SELECT * FROM menu ORDER BY kategori, nama_menu";
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
    <title>Kelola Menu - Sistem Angkringan</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>🍽️ Kelola Menu</h1>
            <p>Tambah, edit, dan kelola menu angkringan Anda</p>
        </div>
        
        <?php if($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Form Tambah Menu -->
        <div class="form-container">
            <h2>➕ Tambah Menu Baru</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama_menu">Nama Menu:</label>
                        <input type="text" id="nama_menu" name="nama_menu" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="harga">Harga (Rp):</label>
                        <input type="number" id="harga" name="harga" min="0" step="500" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori">Kategori:</label>
                        <select id="kategori" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="makanan">Makanan</option>
                            <option value="minuman">Minuman</option>
                            <option value="gorengan">Gorengan</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Tambah Menu</button>
            </form>
        </div>
        
        <!-- Daftar Menu -->
        <?php foreach($menu_by_category as $kategori => $menus): ?>
            <div class="table-container">
                <h2 style="padding: 1rem; background: #f8f9fa; margin: 0; text-transform: capitalize;">
                    <?php 
                    $icons = ['makanan' => '🍛', 'minuman' => '🥤', 'gorengan' => '🍟'];
                    echo $icons[$kategori] . ' ' . ucfirst($kategori); 
                    ?>
                </h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Menu</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach($menus as $menu): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $menu['nama_menu']; ?></td>
                                <td>Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="id" value="<?php echo $menu['id']; ?>">
                                        <input type="hidden" name="status" value="<?php echo $menu['status'] == 'tersedia' ? 'habis' : 'tersedia'; ?>">
                                        <button type="submit" class="btn <?php echo $menu['status'] == 'tersedia' ? 'btn-success' : 'btn-warning'; ?>" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                            <?php echo $menu['status'] == 'tersedia' ? '✅ Tersedia' : '❌ Habis'; ?>
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <button onclick="editMenu(<?php echo htmlspecialchars(json_encode($menu)); ?>)" class="btn btn-warning" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; margin-right: 0.5rem;">
                                        ✏️ Edit
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus menu ini?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $menu['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.8rem;">
                                            🗑️ Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
        
        <?php if(empty($menu_list)): ?>
            <div class="no-data">
                <h3>Belum ada menu</h3>
                <p>Silakan tambahkan menu pertama Anda!</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal Edit Menu -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 10px; min-width: 400px;">
            <h3>✏️ Edit Menu</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label for="edit_nama_menu">Nama Menu:</label>
                    <input type="text" id="edit_nama_menu" name="nama_menu" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_harga">Harga (Rp):</label>
                    <input type="number" id="edit_harga" name="harga" min="0" step="500" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_kategori">Kategori:</label>
                    <select id="edit_kategori" name="kategori" required>
                        <option value="makanan">Makanan</option>
                        <option value="minuman">Minuman</option>
                        <option value="gorengan">Gorengan</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="edit_status">Status:</label>
                    <select id="edit_status" name="status" required>
                        <option value="tersedia">Tersedia</option>
                        <option value="habis">Habis</option>
                    </select>
                </div>
                
                <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" onclick="closeEditModal()" class="btn" style="background: #6c757d; color: white;">Batal</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function editMenu(menu) {
            document.getElementById('edit_id').value = menu.id;
            document.getElementById('edit_nama_menu').value = menu.nama_menu;
            document.getElementById('edit_harga').value = menu.harga;
            document.getElementById('edit_kategori').value = menu.kategori;
            document.getElementById('edit_status').value = menu.status;
            document.getElementById('editModal').style.display = 'block';
        }
        
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>