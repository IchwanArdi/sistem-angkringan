CREATE DATABASE angkringan_db;
USE angkringan_db;

-- Tabel admin untuk login
CREATE TABLE admin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert admin default
INSERT INTO admin (username, password, nama) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tukang Angkringan');

-- Tabel menu
CREATE TABLE menu (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_menu VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL,
    kategori ENUM('makanan', 'minuman', 'gorengan') NOT NULL,
    status ENUM('tersedia', 'habis') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert menu default
INSERT INTO menu (nama_menu, harga, kategori) VALUES 
('Nasi Kucing', 3000, 'makanan'),
('Sate Usus', 1000, 'makanan'),
('Tempe Goreng', 1500, 'gorengan'),
('Tahu Goreng', 1500, 'gorengan'),
('Mendoan', 2000, 'gorengan'),
('Es Teh Manis', 3000, 'minuman'),
('Kopi Tubruk', 5000, 'minuman'),
('Wedang Jahe', 4000, 'minuman');

-- Tabel pelanggan
CREATE TABLE pelanggan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel pemesanan
CREATE TABLE pemesanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pelanggan_id INT,
    total_harga DECIMAL(10,2) NOT NULL,
    tanggal_pesan TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    catatan TEXT,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE SET NULL
);

-- Tabel detail pemesanan
CREATE TABLE detail_pemesanan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    pemesanan_id INT NOT NULL,
    menu_id INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pemesanan_id) REFERENCES pemesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
);
