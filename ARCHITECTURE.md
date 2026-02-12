# 🏗️ ARCHITECTURE - Villa Salon Lumajang

Dokumentasi arsitektur teknis aplikasi Villa Salon Lumajang.

## System Overview

```
┌─────────────────────────────────────────────────────┐
│           CLIENT LAYER (Browser)                    │
│  ┌──────────────┬───────────────┬──────────────┐   │
│  │  Kiosk Mode  │  Admin Panel   │   Dashboard  │   │
│  │  (HTML/CSS)  │  (HTML/CSS)    │  (HTML/CSS)  │   │
│  └──────────────┴───────────────┴──────────────┘   │
└────────────────┬────────────────────────────────────┘
                 │ HTTP/AJAX
┌────────────────▼────────────────────────────────────┐
│         WEB LAYER (PHP)                             │
│  ┌──────────────────────────────────────────────┐  │
│  │  Pages (pages/*.php)                         │  │
│  │  ├─ Login Page                               │  │
│  │  ├─ Admin Pages (admin/*)                    │  │
│  │  ├─ Kasir Pages (kasir/*)                    │  │
│  │  └─ Terapis Pages (terapis/*)                │  │
│  └──────────────────────────────────────────────┘  │
│  ┌──────────────────────────────────────────────┐  │
│  │  Business Logic Layer                        │  │
│  │  ├─ functions.php (Helper functions)         │  │
│  │  ├─ session.php (Session management)         │  │
│  │  └─ config.php (Configuration)               │  │
│  └──────────────────────────────────────────────┘  │
└────────────────┬────────────────────────────────────┘
                 │ Query Execution
┌────────────────▼────────────────────────────────────┐
│         DATA LAYER (MySQL Database)                 │
│  ┌──────────────────────────────────────────────┐  │
│  │ Database: villa_salon                        │  │
│  │ ├─ users                                     │  │
│  │ ├─ services                                  │  │
│  │ ├─ transactions                              │  │
│  │ ├─ therapist_commission                      │  │
│  │ ├─ therapist_status                          │  │
│  │ ├─ members                                   │  │
│  │ ├─ expenses                                  │  │
│  │ ├─ cash_register                             │  │
│  │ └─ settings                                  │  │
│  └──────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────┘
```

## Technology Stack

### Frontend
- **HTML5** - Markup structure
- **CSS3** - Styling & responsive design
- **JavaScript** - Client-side interaktivitas
- **Bootstrap-like Grid** - Custom responsive grid

### Backend
- **PHP 7.4+** - Server-side logic
- **MySQLi** - Database query execution
- **Session** - User session management

### Database
- **MySQL 5.7+** or **MariaDB 10.1+**
- **Storage Engine**: InnoDB (default)

### Server
- **Apache** (XAMPP/Laragon)
- **Port**: 80 (default)

## Database Schema

### Core Tables

#### `users`
Menyimpan semua pengguna sistem (Admin, Kasir, Terapis)

```sql
- id (PK)
- name
- username
- password (hashed MD5)
- role (admin, kasir, terapis)
- email
- phone
- status (active, inactive)
- created_at, updated_at
```

#### `services`
Daftar layanan yang ditawarkan salon

```sql
- id (PK)
- name (Creambath, Pijat, dll)
- description
- fee (Nominal komisi = biaya layanan)
- duration_minutes
- status
- created_at, updated_at
```

#### `transactions`
Record semua transaksi yang terjadi

```sql
- id (PK)
- transaction_date
- member_id (FK - optional)
- service_id (FK)
- therapist_id (FK)
- kasir_id (FK)
- fee
- payment_method (cash, qris)
- status (pending, completed, cancelled)
- created_at, updated_at
```

#### `therapist_commission`
Tracking komisi setiap terapis

```sql
- id (PK)
- therapist_id (FK)
- transaction_id (FK)
- commission_amount
- status (accumulated, withdrawn)
- accumulated_date
- withdrawal_date
```

#### `therapist_status`
Status real-time terapis (tersedia/sibuk)

```sql
- id (PK)
- user_id (FK) UNIQUE
- status (available, busy, off)
- last_updated
```

#### `members`
Data pelanggan salon

```sql
- id (PK)
- name
- phone
- email
- notes
- created_at, updated_at
```

#### `expenses`
Pengeluaran operational salon

```sql
- id (PK)
- expense_date
- description
- amount
- category
- recorded_by (FK)
- notes
- created_at
```

#### `cash_register`
Summary kas harian

```sql
- id (PK)
- cash_date UNIQUE
- opening_balance
- total_cash_income
- total_qris_income
- total_expenses
- closing_balance
- notes
```

#### `settings`
Konfigurasi aplikasi

```sql
- id (PK)
- setting_key UNIQUE
- setting_value
- updated_at
```

## Application Flow

### 1. Login Flow
```
User Access → pages/login.php
    ↓
Input username & password
    ↓
Query DB: SELECT * FROM users WHERE username = ?
    ↓
Validate password dengan MD5
    ↓
If Match:
  ├─ Set session (user_id, role, name)
  └─ Redirect ke dashboard sesuai role
    
If No Match:
  └─ Show error message
```

### 2. Transaction Flow
```
Kasir Login → pages/kasir/dashboard.php
    ↓
Click Service (KIOSK MODE)
    ↓
SELECT Available Therapists
    ↓
Choose Therapist + Payment Method
    ↓
INSERT INTO transactions
  ├─ service_id
  ├─ therapist_id
  ├─ payment_method
  └─ status = 'pending'
    ↓
UPDATE therapist_status SET status = 'busy'
    ↓
Show Success Message
```

### 3. Commission Flow
```
Therapist Finishes Service
    ↓
Click "SELESAI" Button
    ↓
UPDATE therapist_status SET status = 'available'
    ↓
INSERT INTO therapist_commission
  ├─ therapist_id
  ├─ transaction_id
  ├─ commission_amount = service.fee
  └─ status = 'accumulated'
    ↓
Commission Visible in Dashboard
    ↓
Therapist Click "AMBIL KOMISI"
    ↓
INSERT INTO therapist_withdrawal
    ↓
UPDATE therapist_commission SET status = 'withdrawn'
    ↓
Saldo berkurang, bukti tersimpan
```

## Function Architecture

### Core Functions (functions.php)

**Database Functions:**
- `query_select()` - Execute SELECT query
- `query_select_one()` - Get single row
- `query_execute()` - Execute INSERT/UPDATE/DELETE
- `get_last_insert_id()` - Get last ID

**User Functions:**
- `get_user($id)` - Get user data
- `get_therapists()` - Get all therapists
- `get_available_therapists()` - Get therapists dengan status available

**Data Retrieval:**
- `get_services()` - Get all services
- `get_therapist_commission_balance()` - Get saldo komisi
- `get_today_transactions()` - Get hari ini
- `get_monthly_report()` - Get laporan bulan

**Helper Functions:**
- `format_currency()` - Format Rp
- `format_date()` - Format tanggal Indonesia
- `sanitize()` - Sanitize input
- `generate_random_string()` - Generate password

### Session Functions (session.php)

**Session Management:**
- `set_session()` - Set session value
- `get_session()` - Get session value
- `has_session()` - Check session exists
- `delete_session()` - Delete session
- `clear_session()` - Clear all session

**Authentication:**
- `is_logged_in()` - Check login status
- `has_permission()` - Check role permission
- `require_login()` - Redirect if not login
- `require_permission()` - Redirect if no permission

**Message Handling:**
- `set_error()` - Set error message
- `get_error()` - Get & clear error
- `set_success()` - Set success message
- `get_success()` - Get & clear success

## File Structure Detail

```
villa-salon-lumajang/
│
├── 📄 index.php                 # Entry point
├── 📄 .gitignore               # Git ignore rules
├── 📄 README.md                # Main documentation
├── 📄 QUICK_START.md           # Quick setup guide
├── 📄 ARCHITECTURE.md          # This file
│
├── 📁 config/                  # Configuration
│   ├── config.php              # Main config
│   └── db.php                  # DB connection & query functions
│
├── 📁 includes/                # Helper & utility
│   ├── functions.php           # Business logic functions
│   └── session.php             # Session management
│
├── 📁 pages/                   # Web pages
│   ├── login.php               # Login page
│   ├── logout.php              # Logout handler
│   ├── 📁 admin/               # Admin pages
│   │   ├── dashboard.php
│   │   ├── services.php
│   │   ├── therapists.php
│   │   ├── users.php
│   │   ├── members.php
│   │   ├── expenses.php
│   │   └── settings.php
│   ├── 📁 kasir/               # Kasir pages
│   │   ├── dashboard.php
│   │   ├── transaction.php
│   │   ├── history.php
│   │   └── report.php
│   └── 📁 terapis/             # Terapis pages
│       ├── dashboard.php
│       ├── task.php
│       ├── commission.php
│       └── history.php
│
├── 📁 public/                  # Public assets
│   ├── 📁 css/                 # Stylesheets
│   │   ├── style.css           # Main styling
│   │   └── login.css           # Login styling
│   ├── 📁 js/                  # JavaScript files
│   ├── 📁 images/              # Images/icons
│   └── 📁 uploads/             # User uploads (proof, etc)
│
├── 📁 sql/                     # Database
│   └── villa_salon.sql         # Database schema & init data
│
└── 📁 database/                # Database backups (future)
    └── backup/
```

## Security Implementation

### 1. SQL Injection Prevention
Menggunakan **prepared statements**:
```php
$stmt = $connection->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
```

### 2. Password Security
```php
// Current: MD5 hash (basic)
$hash = md5($password);

// Future: bcrypt (recommended)
$hash = password_hash($password, PASSWORD_BCRYPT);
```

### 3. Session Security
```php
- Session timeout: 1 jam
- Session validasi user_id & role
- Destroy session on logout
- HTTPS recommended di production
```

### 4. Input Sanitization
```php
$safe_input = sanitize($_POST['field']);
// Strip whitespace & escape special chars
```

### 5. Role-Based Access Control (RBAC)
```php
// Check permission sebelum akses resource
require_permission('admin');  // Hanya admin
require_permission('kasir');  // Admin & kasir
```

## Performance Optimization

### Database Indexes
```sql
- users: role, status
- services: status
- transactions: date, therapist_id, status
- therapist_commission: therapist_id, status
```

### Query Optimization
- Gunakan prepared statements
- Limit hasil query (pagination)
- Select hanya kolom yang perlu
- Join table dengan efficient

### Caching (Future)
- Cache layanan di session
- Cache user data
- Cache monthly report

## API Endpoints (Future)

```
POST /api/auth/login
GET  /api/user/profile
POST /api/transaction/create
GET  /api/transaction/list
POST /api/commission/withdraw
GET  /api/report/daily
```

## Deployment Checklist

- [ ] Update password admin
- [ ] Update database credentials
- [ ] Enable HTTPS
- [ ] Setup backup schedule
- [ ] Configure error logging
- [ ] Setup monitoring
- [ ] Create admin account production
- [ ] Test all functionality
- [ ] Setup email notifications

## Future Enhancements

1. **REST API** untuk mobile app
2. **Mobile App** (Android/iOS)
3. **Payment Gateway** integration (Stripe, iPaymu)
4. **WhatsApp Notification** untuk reminder
5. **Report Export** ke PDF/Excel
6. **Multi-branch** support
7. **Inventory Management**
8. **Customer Loyalty Program**
9. **Staff Performance Analytics**
10. **Integration dengan accounting software**

---

**Architecture Documentation - Villa Salon Lumajang v1.0**
