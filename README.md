# 🍛 Sistem Angkringan - Website Manajemen Warung

Sistem manajemen sederhana untuk angkringan (warung kecil khas Jawa) menggunakan PHP native tanpa framework.

## 🚀 Fitur Utama

- **Dashboard**: Statistik penjualan dan overview bisnis
- **Kelola Menu**: Tambah, edit, hapus menu makanan dan minuman
- **Pencatatan Pesanan**: Catat pesanan pelanggan secara manual
- **Riwayat Pesanan**: Lihat semua pesanan yang pernah dibuat
- **Laporan Penjualan**: Analisis penjualan dengan grafik dan statistik
- **Sistem Login**: Keamanan akses untuk admin

## 📋 Persyaratan Sistem

- **Web Server**: Apache/Nginx
- **PHP**: Versi 7.4 atau lebih baru
- **Database**: MySQL 5.7 atau MariaDB
- **Browser**: Chrome, Firefox, Safari, Edge (versi terbaru)

## 🛠️ Instalasi

### 1. Persiapan Database

1. Buka phpMyAdmin atau tool database lainnya
2. Import file `database.sql` yang sudah disediakan
3. Database `angkringan_db` akan otomatis terbuat dengan data sample

### 2. Konfigurasi

1. **Upload semua file** ke folder web server (htdocs/www)
2. **Edit file `config/database.php`**:
   ```php
   private $host = "localhost";        // Host database
   private $db_name = "angkringan_db"; // Nama database
   private $username = "root";         // Username database
   private $password = "";             // Password database
   ```

### 3. Struktur Folder

```
angkringan/
├── config/
│   └── database.php          
├── includes/
│   └── navbar.php           
├── assets/
│   └── style.css           
├── auth.php                
├── index.php              
├── login.php              
├── logout.php             
├── menu.php               
├── pemesanan.php          
├── riwayat.php            
├── laporan.php            
└── README.md              
```

## 🔐 Login Default

- **Username**: admin
- **Password**: password

*⚠️ Segera ganti password default setelah instalasi pertama!*

## 📊 Cara Penggunaan

### 1. Login
- Akses website melalui browser
- Masukkan username dan password
- Klik "Masuk"

### 2. Dashboard
- Melihat statistik penjualan hari ini dan bulan ini
- Monitor menu paling laris
- Cek pesanan terbaru
- Akses cepat ke fitur lain

### 3. Kelola Menu
- Tambah menu baru dengan nama, harga, dan kategori
- Edit menu yang sudah ada
- Ubah status menu (tersedia/habis)
- Hapus menu yang tidak diperlukan

### 4. Pencatatan Pesanan
- Pilih menu yang dipesan
- Tentukan jumlah
- Input nama pelanggan (opsional)
- Tambah catatan khusus
- Simpan pesanan

### 5. Laporan Penjualan
- Filter berdasarkan periode (hari ini, minggu ini, bulan ini, custom)
- Lihat grafik tren penjualan
- Analisis penjualan per kategori
- Export laporan ke PDF/Excel

## 🎯 Target Pengguna

Website ini **HANYA** untuk **admin/pemilik angkringan**, bukan untuk pelanggan online. Sesuai konsep angkringan tradisional:

- Pelanggan datang langsung ke warung
- Admin mencatat pesanan secara manual
- Tidak ada sistem pembayaran online
- Fokus pada manajemen operasional

## 🔧 Customization

### Menambah Kategori Menu Baru
Edit file yang mengelola menu, tambahkan nilai baru di ENUM kategori:
```sql
ALTER TABLE menu MODIFY kategori ENUM('makanan', 'minuman', 'gorengan', 'kategori_baru');
```

### Mengubah Tema Warna
Edit file `assets/style.css`, ubah nilai pada:
```css
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Menambah Field Pelanggan
Bisa ditambahkan nomor HP, alamat, dll di tabel pelanggan sesuai kebutuhan.

## ❗ Catatan Penting

1. **Backup Data**: Selalu backup database secara rutin
2. **Password**: Ganti password default segera
3. **Permission**: Set permission folder yang tepat (755 untuk folder, 644 untuk file)
4. **Update**: Update PHP dan database secara berkala
5. **Testing**: Test di environment lokal sebelum deploy ke production

## 🐛 Troubleshooting

### Error Koneksi Database
```
Connection error: SQLSTATE[HY000] [1045] Access denied
```
**Solusi**: Periksa username, password, dan nama database di `config/database.php`

### Halaman Blank/Error 500
**Solusi**: 
- Aktifkan error reporting di PHP
- Periksa log error web server
- Pastikan semua file ter-upload dengan benar

### Session Error
**Solusi**: Pastikan folder `tmp` atau `sessions` dapat ditulis oleh web server

## 📞 Support

Jika mengalami kesulitan:
1. Periksa log error web server
2. Pastikan semua persyaratan sistem terpenuhi
3. Coba install di environment lokal dulu (XAMPP/LAMPP)

## 📄 Lisensi

Project ini dibuat untuk pembelajaran dan dapat digunakan secara bebas untuk keperluan komersial maupun non-komersial.

---

**Selamat menggunakan Sistem Angkringan! 🍛**
