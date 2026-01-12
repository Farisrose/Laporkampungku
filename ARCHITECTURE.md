# 🗺️ LaporKampungku System Architecture & Flow Diagrams

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    CLIENT SIDE (Browser)                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────────┐  ┌──────────────────┐  ┌────────────────┐  │
│  │  login.html      │  │ register.html    │  │user_dashboard. │  │
│  │                  │  │                  │  │    html        │  │
│  │ - Form submit    │  │ - Form submit    │  │ - Session      │  │
│  │ - Fetch API      │  │ - Validation     │  │   check        │  │
│  │ - Role redirect  │  │ - Fetch API      │  │ - User menus   │  │
│  └────────┬─────────┘  └────────┬─────────┘  │ - Statistics   │  │
│           │                     │             └────────────────┘  │
│           │  form data          │                                │
│           └─────────┬───────────┘                                │
│                     │                                            │
└─────────────────────┼────────────────────────────────────────────┘
                      │
                      ├─ ../backend/auth.php
                      │  (action=login|register|logout|check)
                      │
┌─────────────────────┼────────────────────────────────────────────┐
│                     ▼                                             │
│              SERVER SIDE (PHP/MySQL)                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  backend/auth.php                                        │   │
│  │  ────────────────────────────────────────────────────    │   │
│  │  POST handler processes:                                 │   │
│  │  • action=login      → password_verify()               │   │
│  │  • action=register   → password_hash() + insert         │   │
│  │  • action=logout     → session_destroy()               │   │
│  │  • action=check      → isset($_SESSION['user_id'])     │   │
│  │                                                          │   │
│  │  Returns: JSON {success, user, message}                 │   │
│  └──────────────┬───────────────────────────────────────────┘   │
│                 │                                                 │
│                 │ PDO queries                                    │
│                 ▼                                                 │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │  MySQL Database: dbkampungku                             │   │
│  │  ────────────────────────────────────────────────────    │   │
│  │  Tables:                                                  │   │
│  │  • tbl_users           (id, username, password, email)   │   │
│  │  • tbl_superadmin      (user_id, ...)                    │   │
│  │  • tbl_admin           (user_id, ...)                    │   │
│  │  • tbl_anggota         (user_id, ...)                    │   │
│  │  • tbl_laporan         (id, user_id, status_id, ...)    │   │
│  │  • tbl_foto_laporan    (id, laporan_id, foto_path)      │   │
│  │  • tbl_status          (id, status_name, warna)         │   │
│  │  • tbl_riwayat         (id, laporan_id, status_id)      │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## User Registration Flow

```
START
  │
  ├─→ [Register Page] register.html
  │         │
  │         ├─ Collect: Name, Email, Phone, Password, Confirm
  │         │
  │         ├─ Validate:
  │         │  • Password length >= 6
  │         │  • Password == Confirm Password
  │         │
  │         ├─ SUBMIT via fetch POST
  │         │         │
  │         ▼         │
  │    [Backend: auth.php?action=register]
  │         │◄────────┘
  │         │
  │         ├─ Validate:
  │         │  • Email not exists
  │         │  • Username not exists
  │         │
  │         ├─ Hash password: password_hash(pwd, PASSWORD_BCRYPT)
  │         │
  │         ├─ INSERT INTO tbl_users
  │         │  (username, password, email, level, is_active)
  │         │
  │         ├─ On Success: JSON {success: true, message: "..."}
  │         │
  │         ├─ On Error: JSON {success: false, message: "error"}
  │         │
  │         ▼
  │   [Response to Browser]
  │         │
  │         ├─ If success → Redirect to login.html
  │         │
  │         ├─ If error → Display error message
  │         │
  │         ▼
  │    [Login Page]
  │
  END
```

---

## User Login Flow

```
START
  │
  ├─→ [Login Page] login.html
  │         │
  │         ├─ Collect: Username, Password
  │         │
  │         ├─ Validate:
  │         │  • Username not empty
  │         │  • Password not empty
  │         │
  │         ├─ SUBMIT via fetch POST
  │         │         │
  │         ▼         │
  │    [Backend: auth.php?action=login]
  │         │◄────────┘
  │         │
  │         ├─ Query: SELECT * FROM tbl_users
  │         │  WHERE username = ?
  │         │
  │         ├─ Verify: password_verify(pwd, db_hash)
  │         │
  │         ├─ On Success:
  │         │  • Set $_SESSION['user_id'] = user.id
  │         │  • Set $_SESSION['username'] = user.username
  │         │  • Set $_SESSION['email'] = user.email
  │         │  • Set $_SESSION['level'] = user.level
  │         │  • Set $_SESSION['logged_in'] = true
  │         │  • Return JSON {success: true, user: {...}}
  │         │
  │         ├─ On Error: JSON {success: false, message: "error"}
  │         │
  │         ▼
  │   [Response to Browser]
  │         │
  │         ├─ If success:
  │         │  • Check user.level
  │         │  • If admin/anggota/superadmin
  │         │    → Redirect to user_dashboard.html
  │         │  • If warga
  │         │    → Redirect to homepage.html
  │         │
  │         ├─ If error:
  │         │  • Display error message
  │         │  • Stay on login page
  │         │
  │         ▼
  │   [Dashboard OR Homepage]
  │
  END
```

---

## Session Check & Dashboard Access Flow

```
START
  │
  ├─→ [User opens] user_dashboard.html
  │         │
  │         ├─ Page Load Event
  │         │
  │         ├─ fetch POST to ../backend/auth.php?action=check
  │         │
  │         ▼
  │    [Backend: auth.php?action=check]
  │         │
  │         ├─ Check: isset($_SESSION['user_id'])
  │         │
  │         ├─ If Session EXISTS:
  │         │  • Return JSON {success: true, user: {...}}
  │         │
  │         ├─ If Session NOT EXISTS:
  │         │  • Return JSON {success: false}
  │         │
  │         ▼
  │   [Response to Browser]
  │         │
  │         ├─ If session valid:
  │         │  • Get user.level
  │         │  • Call loadDashboard(level, user)
  │         │  • Generate role-specific UI
  │         │  • Display sidebar menu
  │         │  • Show statistics cards
  │         │
  │         ├─ If session invalid:
  │         │  • Redirect to login.html
  │         │
  │         ▼
  │   [Dashboard Ready]
  │
  END
```

---

## Role-Based Dashboard Access

```
┌──────────────────────────────────────────────────┐
│         User Level Check                         │
├──────────────────────────────────────────────────┤
│                                                  │
│  user.level = ?                                  │
│  │                                               │
│  ├─→ "superadmin"                              │
│  │       │                                       │
│  │       ├─→ Sidebar Menu:                      │
│  │       │   • 📊 Dashboard                     │
│  │       │   • 📋 Semua Laporan                │
│  │       │   • 👥 Kelola Pengguna             │
│  │       │   • 📈 Statistik                     │
│  │       │                                       │
│  │       └─→ Stats Cards:                      │
│  │           • Total Laporan                    │
│  │           • Total Pengguna                   │
│  │           • Menunggu Diproses               │
│  │           • Selesai                          │
│  │                                               │
│  ├─→ "admin"                                    │
│  │       │                                       │
│  │       ├─→ Sidebar Menu:                      │
│  │       │   • 📊 Dashboard                     │
│  │       │   • 📋 Laporan                       │
│  │       │   • 📈 Statistik                     │
│  │       │                                       │
│  │       └─→ Stats Cards:                      │
│  │           • Total Laporan                    │
│  │           • Menunggu                        │
│  │           • Diproses                         │
│  │           • Selesai                          │
│  │                                               │
│  ├─→ "anggota"                                  │
│  │       │                                       │
│  │       ├─→ Sidebar Menu:                      │
│  │       │   • 📊 Dashboard                     │
│  │       │   • 📋 Laporan Tim                  │
│  │       │                                       │
│  │       └─→ Content:                          │
│  │           • Team reports table               │
│  │                                               │
│  ├─→ "warga"                                    │
│  │       │                                       │
│  │       ├─→ Sidebar Menu:                      │
│  │       │   • 📊 Dashboard                     │
│  │       │   • 📝 Buat Laporan (→ report form) │
│  │       │   • 📋 Laporan Saya                │
│  │       │                                       │
│  │       └─→ Stats Cards:                      │
│  │           • Laporan Saya                     │
│  │           • Menunggu                        │
│  │           • Selesai                          │
│  │                                               │
│  └─→ (Unknown/Invalid)                         │
│          └─→ Redirect to login.html             │
│                                                  │
└──────────────────────────────────────────────────┘
```

---

## Report Submission Flow

```
START
  │
  ├─→ [Report Page] report_infrastructure.html
  │         │
  │         ├─ STEP 1: Category Selection
  │         │   (Jalan Rusak, Air Bersih, etc.)
  │         │
  │         ├─ STEP 2: Location Selection
  │         │   • Map initialization: L.map('mapContainer')
  │         │   • User clicks to set coordinates
  │         │   • Stores: latitude, longitude
  │         │
  │         ├─ STEP 3: Photo Upload & Description
  │         │   • Drag-drop or file input
  │         │   • Multiple files (max 3)
  │         │   • Store File objects in array
  │         │   • Voice input for description
  │         │
  │         ├─ STEP 4: Review & Submit
  │         │   • Summary of all data
  │         │   • Final confirmation
  │         │
  │         ├─ SUBMIT FormData via fetch POST
  │         │         │
  │         ▼         │
  │    [Backend: save_report.php]
  │         │◄────────┘
  │         │
  │         ├─ Validate:
  │         │  • kategori not empty
  │         │  • latitude/longitude valid
  │         │  • Files are images
  │         │
  │         ├─ START TRANSACTION
  │         │
  │         ├─ INSERT INTO tbl_laporan
  │         │  (user_id, kategori, lat, lng, deskripsi, status_id)
  │         │  → Get $laporan_id = lastInsertId()
  │         │
  │         ├─ For each photo file:
  │         │  • Generate safe filename: bin2hex(random_bytes(8))
  │         │  • Save to public/uploads/
  │         │  • INSERT INTO tbl_foto_laporan
  │         │    (laporan_id, foto_path)
  │         │
  │         ├─ COMMIT TRANSACTION (all or nothing)
  │         │
  │         ├─ On Success:
  │         │  JSON {success: true, laporan_id: X, message: "..."}
  │         │
  │         ├─ On Error:
  │         │  • ROLLBACK transaction
  │         │  JSON {success: false, message: "..."}
  │         │
  │         ▼
  │   [Response to Browser]
  │         │
  │         ├─ If success:
  │         │  • Show success message
  │         │  • Display laporan_id for tracking
  │         │  • Redirect to dashboard or homepage
  │         │
  │         ├─ If error:
  │         │  • Display error message
  │         │  • Keep user on form
  │         │
  │         ▼
  │   [Confirm/Success Page]
  │
  END
```

---

## Database Relationships

```
tbl_users
  ├─ id (PK)
  ├─ username (UNIQUE)
  ├─ password (BCRYPT)
  ├─ email (UNIQUE)
  ├─ level (ENUM: superadmin, admin, anggota, warga)
  └─ is_active (TINYINT)
        │
        ├─→ tbl_superadmin (user_id FK)
        ├─→ tbl_admin (user_id FK)
        ├─→ tbl_anggota (user_id FK)
        └─→ tbl_laporan (user_id FK)
                  │
                  ├─ id (PK)
                  ├─ user_id (FK)
                  ├─ kategori
                  ├─ latitude, longitude
                  ├─ deskripsi
                  ├─ status_id (FK)
                  └─ created_at
                        │
                        ├─→ tbl_foto_laporan (laporan_id FK)
                        │         ├─ id (PK)
                        │         ├─ laporan_id (FK)
                        │         └─ foto_path
                        │
                        └─→ tbl_riwayat (laporan_id FK)
                                  ├─ id (PK)
                                  ├─ laporan_id (FK)
                                  ├─ status_lama_id (FK)
                                  ├─ status_baru_id (FK)
                                  ├─ admin_id (FK → tbl_users)
                                  └─ tanggal_ubah

tbl_status (Reference)
  ├─ id (PK)
  ├─ status_name
  └─ warna
```

---

## Session & Authentication Timeline

```
Time │ Event
─────┼──────────────────────────────────────────────────────
 T0  │ User opens login.html
 T1  │ User enters username/password
 T2  │ Frontend: fetch POST to auth.php?action=login
 T3  │ Backend: Query tbl_users for username
 T4  │ Backend: password_verify() check
 T5  │ Backend: $_SESSION created (user_id, username, email, level)
 T6  │ Backend: JSON response {success: true, user: {...}}
 T7  │ Frontend: Parse response, check user.level
 T8  │ Frontend: Redirect to user_dashboard.html
 T9  │ User opens user_dashboard.html
 T10 │ Frontend: fetch POST to auth.php?action=check
 T11 │ Backend: Check isset($_SESSION['user_id'])
 T12 │ Backend: Return {success: true, user: {...}}
 T13 │ Frontend: Generate role-specific UI
 T14 │ User views personalized dashboard
 T15 │ User clicks logout
 T16 │ Frontend: fetch POST to auth.php?action=logout
 T17 │ Backend: session_destroy()
 T18 │ Backend: JSON response {success: true}
 T19 │ Frontend: Redirect to login.html
 T20 │ User back at login page
```

---

## Password Hashing & Verification Process

```
REGISTRATION
  │
  Plain Password: "admin123"
  │
  ├─→ password_hash("admin123", PASSWORD_BCRYPT)
  │
  ├─→ Bcrypt Algorithm:
  │   • Generate random salt (22 characters)
  │   • Apply 2^10 rounds of Blowfish
  │   • Hash result: $2y$10$... (60+ characters)
  │
  ├─→ Store in tbl_users.password
  │   "$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36/R/FJe"
  │
  END

LOGIN VERIFICATION
  │
  User enters: "admin123"
  │
  Retrieve hash from database:
  "$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36/R/FJe"
  │
  ├─→ password_verify("admin123", $hash)
  │
  ├─→ Blowfish verification:
  │   • Extract salt from stored hash
  │   • Hash input with extracted salt
  │   • Compare with stored hash
  │
  ├─→ Return: true or false
  │
  ├─→ If true: Create session, redirect to dashboard
  ├─→ If false: Return error, stay on login
  │
  END

KEY BENEFITS
  ├─ Same plaintext → Different hashes (random salt each time)
  ├─ Computationally expensive (slow to brute force)
  ├─ Collision resistant (impossible to reverse)
  └─ Industry standard for password storage
```

---

## Error Handling Flows

```
LOGIN ERRORS

Invalid Username
  ├─ Query returns no rows
  ├─ Response: {success: false, message: "Username tidak ditemukan"}
  ├─ HTTP: 401 Unauthorized
  └─ Frontend: Alert + stay on login

Invalid Password
  ├─ password_verify() returns false
  ├─ Response: {success: false, message: "Password salah"}
  ├─ HTTP: 401 Unauthorized
  └─ Frontend: Alert + stay on login

Account Inactive
  ├─ User.is_active = 0
  ├─ Response: {success: false, message: "Akun tidak aktif"}
  ├─ HTTP: 403 Forbidden
  └─ Frontend: Alert + contact admin message

Database Error
  ├─ PDO exception
  ├─ Response: {success: false, message: "Database error"}
  ├─ HTTP: 500 Internal Server Error
  └─ Frontend: Alert + try again message


REGISTER ERRORS

Email Already Exists
  ├─ Unique constraint violation
  ├─ Response: {success: false, message: "Email sudah terdaftar"}
  ├─ HTTP: 409 Conflict
  └─ Frontend: Alert + suggest login

Username Already Exists
  ├─ Unique constraint violation
  ├─ Response: {success: false, message: "Username sudah digunakan"}
  ├─ HTTP: 409 Conflict
  └─ Frontend: Alert + suggest different username

Password Too Short
  ├─ Validation in backend
  ├─ Response: {success: false, message: "Password minimal 6 karakter"}
  ├─ HTTP: 400 Bad Request
  └─ Frontend: Alert + show requirement

Password Mismatch
  ├─ Frontend validation first
  ├─ Button disabled, cannot submit
  └─ Error message: "Kata sandi tidak cocok"


DASHBOARD ERRORS

Session Expired/Invalid
  ├─ $_SESSION['user_id'] not set
  ├─ Response: {success: false}
  ├─ HTTP: 401 Unauthorized
  └─ Frontend: Redirect to login.html

Unknown User Level
  ├─ user.level not in (superadmin, admin, anggota, warga)
  ├─ Redirect to login.html
  └─ Error log: Unknown user level: X
```

---

## File Upload Security Process

```
USER UPLOADS PHOTO
  │
  User selects: "photo.jpg" (50 KB, JPEG)
  │
  ├─ Frontend Validation:
  │  ├─ File type check (image/*,video/mp4)
  │  ├─ File size limit (5 MB)
  │  ├─ Max 3 files
  │  └─ Store in uploadedPhotoFiles array
  │
  ├─ Submit via FormData:
  │  └─ new FormData()
  │     .append('files', fileObject)
  │     .append('laporan_id', id)
  │
  ├─→ Backend: save_report.php
  │
  ├─ Server Validation:
  │  ├─ $_FILES['files']['error'] == 0
  │  ├─ mime_type is image/* or video/mp4
  │  ├─ File size < 5 MB
  │  ├─ Filename doesn't contain path traversal
  │  └─ is_uploaded_file() confirms upload
  │
  ├─ Safe Filename Generation:
  │  ├─ Generate: bin2hex(random_bytes(8))
  │  │  Result: "a3f7b2c9e1d6f4a8"
  │  ├─ Append extension: ".jpg"
  │  │  Result: "a3f7b2c9e1d6f4a8.jpg"
  │  └─ Save to: public/uploads/a3f7b2c9e1d6f4a8.jpg
  │
  ├─ Database Storage:
  │  └─ INSERT INTO tbl_foto_laporan
  │     (laporan_id, foto_path)
  │     VALUES (123, 'uploads/a3f7b2c9e1d6f4a8.jpg')
  │
  └─ Return to Frontend:
     └─ JSON {success: true, foto_path: "uploads/..."}

SECURITY MEASURES
  ├─ Random filename (prevents path traversal)
  ├─ Extension whitelist (.jpg, .png, .gif, .mp4)
  ├─ Mime type check (not just extension)
  ├─ File size limit (5 MB max)
  ├─ is_uploaded_file() verification
  ├─ Stored outside web root (optional)
  └─ No execute permissions on upload dir
```

---

## API Response Examples

### Successful Login
```json
{
  "success": true,
  "user": {
    "id": 1,
    "username": "admin",
    "email": "admin@laporkampungku.com",
    "level": "admin"
  },
  "message": "Login berhasil"
}
```

### Failed Login
```json
{
  "success": false,
  "message": "Password salah"
}
```

### Successful Registration
```json
{
  "success": true,
  "message": "Akun berhasil dibuat, silakan masuk"
}
```

### Session Check (Valid)
```json
{
  "success": true,
  "user": {
    "id": 1,
    "username": "admin",
    "email": "admin@laporkampungku.com",
    "level": "admin"
  }
}
```

### Session Check (Invalid)
```json
{
  "success": false,
  "message": "Session tidak valid"
}
```

### Successful Report Submission
```json
{
  "success": true,
  "laporan_id": 42,
  "message": "Laporan berhasil disimpan"
}
```

---

## Testing Matrix

```
┌─────────────────┬──────────┬──────────┬──────────────┐
│ Test Case       │ Input    │ Expected │ Status       │
├─────────────────┼──────────┼──────────┼──────────────┤
│ Login - Valid   │ admin    │ Success  │ ✅ PASS      │
│ Login - Invalid │ wrongpwd │ Error    │ ✅ PASS      │
│ Register - New  │ New data │ Success  │ ✅ PASS      │
│ Register - Dup  │ Dup mail │ Error    │ ✅ PASS      │
│ Session Check   │ Valid    │ Success  │ ✅ PASS      │
│ Session Check   │ Invalid  │ Redirect │ ✅ PASS      │
│ Role Access     │ All 4    │ Correct  │ ✅ PASS      │
│ Report Submit   │ Valid    │ Success  │ ✅ PASS      │
│ Photo Upload    │ Valid    │ Success  │ ✅ PASS      │
│ Logout          │ Valid    │ Success  │ ✅ PASS      │
└─────────────────┴──────────┴──────────┴──────────────┘
```

---

**Diagram Generated:** January 9, 2026  
**System Version:** 2.0  
**Auth Module Status:** ✅ Complete
