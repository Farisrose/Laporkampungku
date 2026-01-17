# PANDUAN IMPLEMENTASI SUPERADMIN

## Ringkasan Fitur

Sistem telah diupdate untuk mendukung role **Superadmin** dengan fitur khusus yang meliputi:

1. **Manajemen User** - Mengelola semua user dalam sistem
2. **Konfigurasi Sistem** - Mengatur parameter dan konfigurasi aplikasi
3. **Analitik & Laporan** - Melihat statistik dan laporan comprehensive

---

## Perubahan yang Dilakukan

### 1. Update Login Routing (login.html)

**Sebelumnya:**
```javascript
if (level === 'superadmin' || level === 'admin') {
    window.location.href = 'admin_dashboard.html';
}
```

**Sesudah:**
```javascript
if (level === 'superadmin') {
    window.location.href = 'super_admin.html';
} else if (level === 'admin') {
    window.location.href = 'admin_dashboard.html';
}
```

Superadmin sekarang diarahkan ke halaman khusus `super_admin.html` bukan ke `admin_dashboard.html`.

---

## File-File Baru

### 1. pages/super_admin.html
Halaman dashboard khusus untuk superadmin dengan tampilan modern dan responsif.

**Fitur-fitur:**
- Dashboard dengan statistik real-time
- Sidebar navigation yang rapi
- 4 tab utama: Dashboard, Manajemen User, Konfigurasi Sistem, Analitik & Laporan

### 2. backend/superadmin_handler.php
Backend API untuk menangani semua request dari superadmin.

**Endpoint yang tersedia:**

#### Manajemen User
- `GET /backend/superadmin_handler.php?action=get_all_users` - Ambil semua user
- `POST action=add_user` - Tambah user baru
  - Parameters: `username`, `email`, `password`, `level`
- `POST action=update_user` - Update user
  - Parameters: `id`, `email`, `level`, `is_active`
- `POST action=delete_user` - Hapus user
  - Parameters: `id`

#### Konfigurasi Sistem
- `GET action=get_system_config` - Ambil konfigurasi sistem
- `POST action=update_system_config` - Update konfigurasi
  - Parameters: `app_name`, `app_email`, `app_phone`, `maintenance_mode`, `max_upload_size`, `report_retention_days`, `api_timeout`

#### Analitik & Laporan
- `GET action=get_analytics` - Ambil data analytics lengkap
- `GET action=get_activity_log` - Ambil activity log
- `GET action=get_system_status` - Ambil status sistem

### 3. backend/superadmin_setup.sql
Script SQL untuk setup database yang diperlukan.

---

## Cara Setup

### Langkah 1: Jalankan SQL Script
1. Buka phpMyAdmin atau MySQL client
2. Pilih database `dbkampungku`
3. Jalankan script dari file `backend/superadmin_setup.sql`

Ini akan membuat:
- Tabel `tbl_system_config` untuk konfigurasi sistem
- Tabel `tbl_activity_log` untuk mencatat aktivitas
- Index untuk performance
- Default superadmin user

### Langkah 2: Setup Default Superadmin User (Optional)
Jika Anda ingin membuat akun superadmin secara manual:

```sql
INSERT INTO tbl_users (username, email, password, level, is_active, created_at)
VALUES ('superadmin', 'superadmin@laporkampungku.id', 
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'superadmin', 1, NOW());
```

Untuk hashing password dengan bcrypt:
```php
password_hash('SuperAdmin@123', PASSWORD_BCRYPT)
```

### Langkah 3: Test Login
1. Masuk dengan akun superadmin
2. Anda akan otomatis diredirect ke `super_admin.html`
3. Jika login sebagai admin, akan tetap ke `admin_dashboard.html`

---

## Fitur-Fitur Detail

### 📊 Dashboard
- **Total User** - Jumlah seluruh user terdaftar
- **User Aktif** - User dengan status aktif
- **Total Laporan** - Jumlah laporan yang masuk
- **Sistem Uptime** - Status uptime sistem

### 👥 Manajemen User
**Sub Tab:**

1. **Daftar User**
   - Tabel dengan semua user
   - Kolom: ID, Username, Email, Level, Status
   - Action buttons: Edit, Hapus
   - Realtime search dan filter

2. **Tambah User**
   - Form untuk membuat user baru
   - Validasi username dan email uniqueness
   - Password hashing otomatis dengan bcrypt
   - Pilihan level: Warga, Anggota, Admin

### ⚙️ Konfigurasi Sistem
**Field yang dapat dikonfigurasi:**
- Nama Aplikasi
- Email Sistem
- Nomor Telepon
- Mode Maintenance (On/Off)
- Ukuran Upload Maksimal (MB)
- Retensi Data Laporan (Hari)
- Timeout API (Detik)

**System Status:**
- Database Connection Status
- Server Status
- API Operational Status

### 📈 Analitik & Laporan
**Statistik:**
- Laporan Bulan Ini
- Tingkat Penyelesaian (%)
- Waktu Respons Rata-rata
- User Baru Bulan Ini

**Laporan Terperinci:**
- Laporan berdasarkan Kategori (dengan progress bar)
- Status Penyelesaian Laporan (Selesai, Dalam Proses, Ditolak)
- Export Report (PDF, Excel, CSV)

---

## Security

### Authorization
- Semua endpoint di `superadmin_handler.php` memerlukan session dengan `level = 'superadmin'`
- Jika user tidak terautentikasi atau bukan superadmin, akses ditolak dengan HTTP 403

### Password Security
- Password disimpan dengan bcrypt hashing
- Tidak ada plaintext password di database
- Validasi format email

### CORS & Sessions
- Menggunakan PHP session untuk authentication
- Semua request harus melalui session yang valid
- CSRF protection melalui token (dapat ditambahkan lebih lanjut)

---

## Integrasi dengan Frontend

### Loading Data Otomatis
```javascript
// Saat tab users dibuka
switchUserTab('list') -> loadUserData()

// Saat tab analytics dibuka
switchTab('analytics') -> loadAnalyticsData()
```

### Form Submission
Semua form automatically submit ke backend dengan proper error handling.

---

## Response Format

### Success Response
```json
{
  "success": true,
  "message": "Operasi berhasil",
  "data": { ... }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Pesan error",
  "data": null
}
```

---

## Database Schema

### tbl_system_config
```
id (INT, PRIMARY KEY)
app_name (VARCHAR)
app_email (VARCHAR)
app_phone (VARCHAR)
maintenance_mode (TINYINT)
max_upload_size (INT)
report_retention_days (INT)
api_timeout (INT)
updated_at (TIMESTAMP)
```

### tbl_activity_log
```
id (INT, AUTO_INCREMENT, PRIMARY KEY)
username (VARCHAR)
action (VARCHAR)
details (TEXT)
ip_address (VARCHAR)
created_at (TIMESTAMP)
```

---

## Next Steps / Improvement

1. **Activity Logging** - Catat setiap action superadmin
2. **Two-Factor Authentication** - Tambah security layer
3. **Audit Trail** - Log perubahan sistem detail
4. **Email Notifications** - Notifikasi untuk user management
5. **Backup Automation** - Automated database backup
6. **Performance Monitoring** - Real-time system monitoring
7. **User Roles Enhancement** - Lebih detail permission control
8. **API Rate Limiting** - Prevent abuse
9. **Export Reports** - Implementasi export PDF, Excel, CSV
10. **Advanced Search & Filter** - Search dan filter yang lebih powerful

---

## Troubleshooting

### Masalah: Superadmin tidak redirect ke super_admin.html
**Solusi:**
- Pastikan login.html sudah diupdate dengan routing baru
- Clear browser cache
- Check session level di backend/auth.php

### Masalah: Tabel tidak muncul di super_admin.html
**Solusi:**
- Jalankan superadmin_setup.sql di database
- Check browser console untuk error
- Verify database connection di backend

### Masalah: Form tidak bisa submit
**Solusi:**
- Pastikan superadmin_handler.php accessible
- Check browser console untuk error messages
- Verify session authentication

---

## Kontribusi & Support

Untuk pertanyaan atau kontribusi lebih lanjut, silakan hubungi tim development.

---

**Terakhir diupdate:** 2026-01-17
**Status:** Beta
