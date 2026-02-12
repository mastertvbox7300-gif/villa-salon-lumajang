# 🚀 QUICK START - Villa Salon Lumajang

Panduan cepat untuk memulai aplikasi Villa Salon Lumajang dalam 5 langkah.

## ⚡ 5 Langkah Setup Cepat

### 1️⃣ Download Folder Project

Setelah clone repository ke XAMPP:
```bash
# Windows XAMPP
C:\xampp\htdocs\villa-salon-lumajang\

# Windows Laragon  
C:\laragon\www\villa-salon-lumajang\

# Linux
/var/www/html/villa-salon-lumajang/
```

### 2️⃣ Buka phpMyAdmin & Import Database

**URL:** `http://localhost/phpmyadmin`

**Langkah:**
1. Buat database baru: `villa_salon`
2. Klik database → Tab "Import"
3. Upload file: `sql/villa_salon.sql`
4. Klik "Go"

✅ Database ready!

### 3️⃣ Konfigurasi Database

Edit file: `config/config.php`

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Sesuaikan dengan user MySQL Anda
define('DB_PASS', '');            // Sesuaikan password
define('DB_NAME', 'villa_salon');
```

### 4️⃣ Jalankan Server

**XAMPP:**
- Start Apache & MySQL di XAMPP Control Panel

**Laragon:**
- Klik START

### 5️⃣ Akses Aplikasi

Buka di browser:
```
http://localhost/villa-salon-lumajang
atau
http://villa-salon-lumajang.test
```

---

## 🔑 Login Default

| Peran | Username | Password |
|------|----------|----------|
| Admin | `admin` | `1234` |
| Kasir | `kasir1` | `kasir123` |
| Terapis | `siti` | `terapis123` |
| Terapis | `rina` | `terapis123` |
| Terapis | `dewi` | `terapis123` |

---

## 🎯 Panduan Pertama Kali

### Untuk ADMIN:

1. **Login** dengan `admin` / `1234`
2. **Dashboard** → Lihat statistik sistem
3. **Layanan** → Edit/tambah layanan yang ditawarkan
4. **Terapis** → Lihat & atur terapis
5. **Settings** → Ganti password admin ⚠️ PENTING!

### Untuk KASIR:

1. **Login** dengan `kasir1` / `kasir123`
2. **Dashboard** → Klik tombol layanan (KIOSK MODE)
3. **Pilih Layanan** → Pilih Terapis → Pilih Metode Bayar
4. **Laporan** → Lihat transaksi harian

### Untuk TERAPIS:

1. **Login** dengan username Anda
2. **Dashboard** → Lihat saldo komisi
3. **Tugas** → Lihat daftar pelanggan
4. **Tekan SELESAI** → Komisi terakumulasi
5. **Ambil Komisi** → Cair komisi kapan saja

---

## 🐛 Troubleshooting Cepat

### Error: "Connection failed: No such file or directory"
- MySQL belum running
- Solusi: Start MySQL di XAMPP/Laragon

### Error: "Access denied for user 'root'@'localhost'"
- Password database tidak match
- Solusi: Update `DB_PASS` di `config/config.php`

### Halaman Blank
- Error PHP tidak terlihat
- Solusi: Check browser console (F12) atau PHP error log

### Sudah tidak bisa login
- Session expired atau database error
- Solusi: Clear browser cache (Ctrl+Shift+Delete)

---

## 📋 File Penting

| File | Fungsi |
|------|--------|
| `sql/villa_salon.sql` | Database schema |
| `config/config.php` | Setting aplikasi & database |
| `pages/login.php` | Halaman login |
| `index.php` | Entry point aplikasi |
| `public/css/style.css` | Styling utama |

---

## ⚙️ Konfigurasi Lanjutan

### Ganti Nama Salon

Edit `config/config.php` → Cari `APP_NAME`

```php
define('APP_NAME', 'Nama Salon Anda');
```

### Ubah Timeout Session

Edit `config/config.php` → Cari `SESSION_TIMEOUT`

```php
define('SESSION_TIMEOUT', 7200); // 2 jam
```

### Tambah Layanan Baru

1. Login Admin
2. Menu **Layanan** → **Tambah**
3. Isi: Nama, Deskripsi, Fee, Durasi
4. Klik **Simpan**

### Tambah Terapis Baru

1. Login Admin
2. Menu **Terapis** → **Tambah**
3. Isi: Nama, Username, Password
4. Klik **Simpan**

---

## 🔐 Security Checklist

- [ ] Ganti password admin default
- [ ] Update database password jika default
- [ ] Ganti `SESSION_TIMEOUT` ke nilai lebih tinggi
- [ ] Setup HTTPS di production
- [ ] Backup database mingguan
- [ ] Monitor disk space

---

## 📞 Support

Butuh bantuan?

1. **Check README.md** - Dokumentasi lengkap
2. **Check folder `/sql`** - Database schema
3. **Check `/pages`** - Contoh implementasi

---

## 🎉 Selamat!

Aplikasi sudah siap digunakan! 

Sekarang Anda bisa:
- ✅ Login & kelola salon
- ✅ Catat transaksi
- ✅ Kelola terapis & komisi
- ✅ Lihat laporan
- ✅ Manajemen data

**Nikmati aplikasi Villa Salon Lumajang! 🏢✨**
