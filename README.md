# 🏢 Villa Salon Lumajang - Sistem Informasi & Manajemen

Aplikasi web berbasis PHP + MySQL untuk manajemen salon modern dengan sistem kiosk, POS, manajemen terapis, dan akuntansi terintegrasi.

## ✨ Fitur Utama

### 👥 Multi-Role System
- **Admin** - Panel kontrol penuh, manajemen semua aspek
- **Kasir** - Transaksi, mode kiosk, laporan harian
- **Terapis** - Dashboard komisi, daftar tugas, riwayat pelanggan

### 🎯 Mode Kiosk
- Tamilan seperti ATM/mesin kasir di mall
- Tombol besar dan mudah digunakan
- Pelanggan bisa memilih layanan langsung

### 💰 Sistem Komisi (100% dari Fee)
- Komisi otomatis terkumpul saat layanan selesai
- Terapis bisa ambil komisi kapan saja
- Bukti pencairan tersimpan otomatis
- Laporan komisi detail per terapis

### 💳 Pembayaran 2 Metode
- **Tunai** - Langsung masuk kas salon
- **QRIS** - Non-tunai, terpisah pencatatannya

### 📊 Dashboard & Laporan Lengkap
- Laporan harian (transaksi, pendapatan, pengeluaran)
- Laporan per terapis (komisi, saldo)
- Laporan keuangan (arus kas)
- Laporan layanan (terlaris, distribusi)

### ⚙️ Panel Pengaturan
- Manajemen layanan (nama, fee, durasi)
- Manajemen terapis & status kerja
- Manajemen kasir
- Backup/restore data
- Ganti password admin

## 🛠️ Teknologi

| Aspek | Teknologi |
|-------|-----------|
| **Backend** | PHP 7.4+ |
| **Database** | MySQL 5.7+ |
| **Frontend** | HTML5, CSS3, JavaScript |
| **UI Framework** | Custom Bootstrap-like |
| **Server** | Apache (XAMPP/Laragon) |

## 📋 Requirement

- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau MariaDB 10.1+
- Apache web server
- 50MB disk space (untuk database)

## 🚀 Instalasi & Setup

### 1. Download/Clone Repository

```bash
# Jika via Git
git clone https://github.com/mastertvbox7300-gif/villa-salon-lumajang.git

# Kemudian navigasi ke folder
cd villa-salon-lumajang
```

### 2. Setup Database

#### Option A: Menggunakan phpMyAdmin
1. Buka `http://localhost/phpmyadmin`
2. Buat database baru dengan nama: `villa_salon`
3. Pilih database → Tab "SQL" → Paste isi file `sql/villa_salon.sql`
4. Klik "Go" untuk execute

#### Option B: Menggunakan Command Line
```bash
# Login ke MySQL
mysql -u root -p

# Execute SQL file
mysql -u root -p villa_salon < sql/villa_salon.sql
```

### 3. Konfigurasi Database

Edit file `config/config.php` dan sesuaikan dengan setting MySQL Anda:

```php
define('DB_HOST', 'localhost');   // Host MySQL
define('DB_USER', 'root');        // Username MySQL
define('DB_PASS', '');            // Password MySQL
define('DB_NAME', 'villa_salon'); // Nama database
define('DB_PORT', 3306);          // Port MySQL
```

### 4. Setup di XAMPP/Laragon

**XAMPP:**
```bash
# Copy folder ke htdocs
cp -r villa-salon-lumajang C:/xampp/htdocs/

# Jalankan Apache & MySQL dari XAMPP Control Panel

# Akses via browser
http://localhost/villa-salon-lumajang
```

**Laragon:**
```bash
# Copy folder ke www
cp -r villa-salon-lumajang C:/laragon/www/

# Jalankan Laragon

# Akses via browser
http://villa-salon-lumajang.test
```

### 5. Login Pertama Kali

**Akun Default:**

| Role | Username | Password | Akses |
|------|----------|----------|-------|
| Admin | `admin` | `1234` | Semua fitur |
| Kasir | `kasir1` | `kasir123` | Transaksi, laporan |
| Terapis | `siti` | `terapis123` | Dashboard, komisi |
| Terapis | `rina` | `terapis123` | Dashboard, komisi |
| Terapis | `dewi` | `terapis123` | Dashboard, komisi |

⚠️ **PENTING:** Ganti password admin setelah setup di `http://localhost/villa-salon-lumajang/pages/admin/settings.php`

## 📁 Struktur Folder

```
villa-salon-lumajang/
├── config/              # Konfigurasi aplikasi
│   ├── config.php       # Setting umum
│   └── db.php           # Koneksi database
├── database/            # Database scripts
├── pages/               # Halaman aplikasi
│   ├── login.php        # Login form
│   ├── logout.php       # Logout
│   ├── admin/           # Panel admin
│   │   ├── dashboard.php
│   │   ├── services.php
│   │   ├── therapists.php
│   │   └── ...
│   ├── kasir/           # Interface kasir
│   │   ├── dashboard.php
│   │   ├── transaction.php
│   │   └── ...
│   └── terapis/         # Dashboard terapis
│       ├── dashboard.php
│       ├── commission.php
│       └── ...
├── public/              # File publik
│   ├── css/             # Stylesheet
│   │   ├── style.css
│   │   └── login.css
│   ├── js/              # JavaScript
│   ├── images/          # Gambar
│   └── uploads/         # Upload files
├── includes/            # Helper functions
│   ├── functions.php    # Fungsi umum
│   └── session.php      # Session management
├── sql/                 # Database schema
│   └── villa_salon.sql  # Dump database
├── index.php            # Entry point
└── README.md           # Dokumentasi ini
```

## 🔐 Keamanan

Beberapa praktik keamanan yang sudah diimplementasikan:

1. **SQL Injection Prevention** - Menggunakan prepared statements
2. **Session Management** - Session timeout 1 jam
3. **Password Hashing** - Password di-hash dengan MD5 (bisa upgrade ke bcrypt)
4. **Role-Based Access** - Validasi permission di setiap halaman
5. **Input Sanitization** - Sanitasi input dari user

**Tips Keamanan Lanjutan:**
- Gunakan HTTPS di production
- Upgrade password hashing ke bcrypt
- Implement CSRF token
- Regular backup database
- Update PHP ke version terbaru

## 📊 Workflow Sistem

### Alur Transaksi Kasir
```
1. Kasir buka halaman transaksi
2. Kasir pilih layanan di kiosk mode
3. Kasir pilih terapis yang tersedia
4. Kasir pilih metode pembayaran (Tunai/QRIS)
5. Transaksi masuk status "PENDING"
6. Terapis menerima tugas → status berubah "SEDANG TUGAS"
7. Setelah selesai, terapis tekan "SELESAI"
8. Komisi otomatis terakumulasi
9. Status terapis kembali "TERSEDIA"
10. Transaksi masuk laporan harian
```

### Alur Komisi Terapis
```
1. Terapis selesai layanan → tekan SELESAI
2. Komisi otomatis tersimpan (status: ACCUMULATED)
3. Komisi bisa dilihat di "Dashboard → Komisi Saya"
4. Terapis klik "AMBIL KOMISI"
5. Input nominal yang mau diambil
6. Bukti pencairan tersimpan otomatis
7. Saldo komisi berkurang, status berubah WITHDRAWN
```

## 🔧 Maintenance

### Backup Database
```bash
# Manual backup
mysqldump -u root -p villa_salon > backup_date.sql

# Restore
mysql -u root -p villa_salon < backup_date.sql
```

### Clear Sessions (jika ada masalah)
```bash
# Hapus semua session files
rm -rf /tmp/php*

# Atau di Windows
del %temp%\php*
```

### Update Layanan/Fee
1. Login sebagai Admin
2. Ke menu "Layanan"
3. Edit/tambah layanan
4. Fee akan berlaku ke transaksi baru

## 🐛 Troubleshooting

### Error: "Connection refused"
- Pastikan MySQL service sudah jalan
- Cek setting database di `config/config.php`
- Verifikasi username/password MySQL

### Error: "Access denied for user"
- Database user tidak match
- Password database salah
- User tidak punya privilege

### Session hilang/logout tiba-tiba
- Check session timeout di `config/config.php`
- Clear temp files
- Check server disk space

### Halaman blank
- Check PHP error log
- Pastikan database terhubung
- Verify file permissions (775 untuk folder, 644 untuk file)

## 📞 Support & Kontak

Untuk pertanyaan atau issue:
- 📧 Email: [support@villasalon.local]
- 💬 Hubungi: Admin Villa Salon

## 📄 Lisensi

© 2024 Villa Salon Lumajang. Hak cipta dilindungi.

## 📝 Catatan Versi

### v1.0 (Initial Release)
- ✅ Login multi-role system
- ✅ Kiosk mode untuk pelanggan
- ✅ Sistem komisi terapis
- ✅ Pembayaran Tunai & QRIS
- ✅ Dashboard & laporan
- ✅ Admin panel manajemen
- ✅ Terapis dashboard

### v1.1 (Coming Soon)
- 🔄 Mobile app (Android/iOS)
- 🔄 Integration dengan payment gateway
- 🔄 Sistem membership/loyalty
- 🔄 WhatsApp notifikasi
- 🔄 Export laporan ke Excel/PDF
- 🔄 Upgrade ke bcrypt password

## 🤝 Kontribusi

Jika ingin berkontribusi, silakan:
1. Fork repository
2. Buat branch baru
3. Commit changes
4. Push ke branch
5. Buat Pull Request

---

**Made with ❤️ for Villa Salon Lumajang**