# Panduan Testing Superadmin Dashboard

## 📋 Pre-requisites
- XAMPP/WAMP running
- Database `dbkampungku` sudah created
- Project di `/xampp/htdocs/projek/`

---

## ✅ Step 1: Setup Database

### Opsi A: Auto Setup (Termudah)
```
1. Buka browser: http://localhost/projek/backend/setup_superadmin_user.php
2. Tunggu hingga muncul "Setup Complete!"
3. Catat credentials yang ditampilkan
```

### Opsi B: Manual Setup
```sql
-- 1. Buka phpMyAdmin
-- 2. Select database dbkampungku
-- 3. Jalankan query berikut:

-- Create tbl_system_config
CREATE TABLE IF NOT EXISTS `tbl_system_config` (
  `id` INT PRIMARY KEY,
  `app_name` VARCHAR(100),
  `app_email` VARCHAR(100),
  `app_phone` VARCHAR(20),
  `maintenance_mode` TINYINT(1) DEFAULT 0,
  `max_upload_size` INT DEFAULT 50,
  `report_retention_days` INT DEFAULT 365,
  `api_timeout` INT DEFAULT 30,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create tbl_activity_log
CREATE TABLE IF NOT EXISTS `tbl_activity_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50),
  `action` VARCHAR(100),
  `details` TEXT,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert config
INSERT IGNORE INTO `tbl_system_config` 
VALUES (1, 'LaporKampungku', 'info@laporkampungku.id', '+62 887-4373-52670', 0, 50, 365, 30, NOW());

-- Insert superadmin user (password: SuperAdmin@123)
INSERT IGNORE INTO `tbl_users` (username, email, password, level, is_active, created_at)
VALUES ('superadmin', 'superadmin@laporkampungku.id', 
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
        'superadmin', 1, NOW());
```

---

## 🧪 Test 1: Login Redirect

### Test Case 1A: Superadmin Login
**Input:**
- Username: `superadmin`
- Password: `SuperAdmin@123`

**Expected Output:**
- ✅ Login berhasil
- ✅ Redirect ke `super_admin.html`
- ✅ Halaman menampilkan "Super Administrator" di sidebar

**Actual Result:**
- [ ] Pass / [ ] Fail

---

### Test Case 1B: Admin Login
**Input:**
- Username: `admin` (atau admin yang sudah ada)
- Password: `admin` (atau password yang sesuai)

**Expected Output:**
- ✅ Login berhasil
- ✅ Redirect ke `admin_dashboard.html` (bukan super_admin.html)

**Actual Result:**
- [ ] Pass / [ ] Fail

---

## 📊 Test 2: Dashboard

### Test Case 2A: Dashboard Statistics
**Steps:**
1. Login sebagai superadmin
2. Berada di tab "Dashboard"

**Expected Output:**
- ✅ Card "Total User" menampilkan angka
- ✅ Card "User Aktif" menampilkan angka
- ✅ Card "Total Laporan" menampilkan angka
- ✅ Card "Sistem Uptime" menampilkan 99.8%

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

## 👥 Test 3: Manajemen User

### Test Case 3A: View User List
**Steps:**
1. Klik menu "Manajemen User"
2. Klik tab "Daftar User"

**Expected Output:**
- ✅ Tabel muncul dengan columns: ID, Username, Email, Level, Status, Aksi
- ✅ Minimal ada 1 user (superadmin atau admin)
- ✅ Button "Edit" dan "Hapus" visible

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

### Test Case 3B: Add New User
**Steps:**
1. Klik menu "Manajemen User"
2. Klik tab "Tambah User"
3. Isi form:
   - Username: `testuser`
   - Email: `test@example.com`
   - Password: `Test@123456`
   - Level: `Warga`
4. Klik "Simpan User"

**Expected Output:**
- ✅ Alert success muncul
- ✅ Form di-reset
- ✅ User baru ada di database

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

### Test Case 3C: Edit User
**Steps:**
1. Di tab "Daftar User", klik button "Edit" pada user manapun
2. Modal muncul
3. Ubah email atau level
4. Klik "Simpan Perubahan"

**Expected Output:**
- ✅ Modal muncul dengan data user
- ✅ Field username disabled
- ✅ Alert success muncul
- ✅ Modal tertutup
- ✅ Perubahan tersimpan

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

### Test Case 3D: Delete User
**Steps:**
1. Di tab "Daftar User", klik button "Hapus"
2. Confirm dialog muncul
3. Klik OK

**Expected Output:**
- ✅ Confirm dialog muncul
- ✅ Alert success muncul
- ✅ User dihapus dari tabel dan database

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

## ⚙️ Test 4: Konfigurasi Sistem

### Test Case 4A: View System Config
**Steps:**
1. Klik menu "Konfigurasi Sistem"

**Expected Output:**
- ✅ Form muncul dengan field:
  - Nama Aplikasi: LaporKampungku
  - Email Sistem: info@laporkampungku.id
  - Nomor Telepon: +62 887-4373-52670
  - Mode Maintenance: Tidak Aktif
  - Ukuran Upload Maksimal: 50
  - Retensi Data Laporan: 365
  - Timeout API: 30
- ✅ System Status card menampilkan status semua komponen

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

### Test Case 4B: Update System Config
**Steps:**
1. Ubah "Nama Aplikasi" menjadi: `LaporKampungku v2`
2. Ubah "Email Sistem" menjadi: `admin@newdomain.com`
3. Klik "Simpan Konfigurasi"

**Expected Output:**
- ✅ Alert success muncul
- ✅ Nilai tersimpan di database
- ✅ Saat refresh, nilai masih ada

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

### Test Case 4C: Reset Config Form
**Steps:**
1. Ubah beberapa field
2. Klik "Reset"

**Expected Output:**
- ✅ Semua field kembali ke nilai sebelumnya (dari database)

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

## 📈 Test 5: Analitik & Laporan

### Test Case 5A: View Analytics Dashboard
**Steps:**
1. Klik menu "Analitik & Laporan"

**Expected Output:**
- ✅ 4 stat cards muncul:
  - Laporan Bulan Ini
  - Tingkat Penyelesaian (%)
  - Waktu Respons Rata-rata
  - User Baru (Bulan Ini)

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

### Test Case 5B: View Category Report
**Steps:**
1. Scroll ke bawah section "Laporan Berdasarkan Kategori"

**Expected Output:**
- ✅ Progress bars muncul untuk kategori laporan
- ✅ Menampilkan: Jalan & Trotoar, Drainase & Saluran Air, Penerangan Jalan
- ✅ Percentage dan progress bar konsisten

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

### Test Case 5C: View Status Report
**Steps:**
1. Scroll ke section "Status Penyelesaian Laporan"

**Expected Output:**
- ✅ Progress bars muncul untuk status: Selesai, Dalam Proses, Ditolak
- ✅ Percentage dan progress bar konsisten

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

### Test Case 5D: Export Report
**Steps:**
1. Scroll ke section "Export Laporan"
2. Klik button "Export PDF"

**Expected Output:**
- ✅ Alert muncul: "Laporan sedang diexport dalam format PDF..."

**Note:** Fitur export akan diimplementasikan lebih lanjut

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

## 🔐 Test 6: Security

### Test Case 6A: Session Expiry
**Steps:**
1. Login sebagai superadmin
2. Close browser atau clear session
3. Refresh halaman super_admin.html

**Expected Output:**
- ✅ Redirect ke login.html
- ✅ Session tidak bisa diakses

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

### Test Case 6B: Direct URL Access
**Steps:**
1. Logout
2. Akses langsung: `http://localhost/projek/pages/super_admin.html`

**Expected Output:**
- ✅ Redirect ke login.html
- ✅ Tidak bisa akses halaman

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

## 🎨 Test 7: UI/UX

### Test Case 7A: Responsive Design
**Steps:**
1. Buka super_admin.html di desktop
2. Resize browser ke ukuran mobile (320px)
3. Test semua fitur di mobile

**Expected Output:**
- ✅ Layout responsive
- ✅ Sidebar kolaps di mobile
- ✅ Tabel scrollable
- ✅ Button accessible

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

### Test Case 7B: Tab Navigation
**Steps:**
1. Click semua tab di sidebar
2. Verify content berubah

**Expected Output:**
- ✅ Page title berubah sesuai tab
- ✅ Content fade-in smooth
- ✅ Active tab highlighted

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

## 📝 Test 8: Logout

### Test Case 8A: Logout Function
**Steps:**
1. Klik button "Logout" di top-right
2. Confirm logout dialog

**Expected Output:**
- ✅ Confirm dialog muncul
- ✅ Session dihapus
- ✅ Redirect ke login.html

**Actual Result:**
- [ ] Pass / [ ] Fail

**Notes:**
_________________________________

---

## 🐛 Bug Report

Gunakan template ini untuk melaporkan bugs:

```
Bug #[nomor]:
Title: [Deskripsi singkat]
Severity: [Critical/High/Medium/Low]
Steps to Reproduce:
1. ...
2. ...
3. ...

Expected Result:
...

Actual Result:
...

Screenshots:
[Attach screenshot jika ada]

Notes:
...
```

---

## ✨ Summary

| Test Case | Status | Notes |
|-----------|--------|-------|
| 1A - Superadmin Login | [ ] | |
| 1B - Admin Login | [ ] | |
| 2A - Dashboard Stats | [ ] | |
| 3A - View Users | [ ] | |
| 3B - Add User | [ ] | |
| 3C - Edit User | [ ] | |
| 3D - Delete User | [ ] | |
| 4A - View Config | [ ] | |
| 4B - Update Config | [ ] | |
| 4C - Reset Config | [ ] | |
| 5A - Analytics Dashboard | [ ] | |
| 5B - Category Report | [ ] | |
| 5C - Status Report | [ ] | |
| 5D - Export Report | [ ] | |
| 6A - Session Expiry | [ ] | |
| 6B - Direct URL Access | [ ] | |
| 7A - Responsive Design | [ ] | |
| 7B - Tab Navigation | [ ] | |
| 8A - Logout Function | [ ] | |

**Total Pass:** ___/19
**Total Fail:** ___/19

---

## 📞 Support

Jika ada pertanyaan atau issues, silakan hubungi tim development.

---

**Testing Date:** _______________
**Tested By:** _______________
**Status:** [ ] PASS [ ] FAIL
