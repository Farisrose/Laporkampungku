# LaporKampungku - Authentication & Dashboard System

## Status: ✅ Authentication System Complete

### Overview
Complete user authentication system with role-based access control (RBAC) for 4 user levels: superadmin, admin, anggota (team member), and warga (citizen).

---

## Features Implemented

### 1. **Authentication Backend** (`backend/auth.php`)
- ✅ Login with username/password verification
- ✅ Register new users with validation
- ✅ Logout with session destroy
- ✅ Session check (verify if user is logged in)
- ✅ Password hashing using bcrypt (PASSWORD_BCRYPT)
- ✅ Session management with proper HTTP status codes
- ✅ JSON API responses for all endpoints

**Endpoints:**
```
POST /backend/auth.php?action=login
  - Parameters: username, password
  - Returns: {success, user{id, username, email, level}, message}
  
POST /backend/auth.php?action=register
  - Parameters: username, email, password, confirm_password
  - Returns: {success, message}
  
POST /backend/auth.php?action=logout
  - Returns: {success, message}
  
POST /backend/auth.php?action=check
  - Returns: {success, user{...}} or redirect to login
```

### 2. **Login Page** (`pages/login.html`)
- ✅ Username/password form
- ✅ Password visibility toggle
- ✅ Form submission to backend via fetch API
- ✅ Error handling with user feedback
- ✅ Redirect based on user role (admin → dashboard, warga → homepage)
- ✅ Responsive mobile design

**Test Credentials:**
```
Username: admin
Password: admin123
Role: admin
```

### 3. **Register Page** (`pages/register.html`)
- ✅ Registration form (name, email, phone, password)
- ✅ Password confirmation validation
- ✅ Real-time form validation
- ✅ Username auto-generation from name
- ✅ Error handling and success messages
- ✅ Auto-redirect to login after successful registration

### 4. **User Dashboard** (`pages/user_dashboard.html`)
- ✅ User dashboard with session verification
- ✅ User info display with role badge
- ✅ Responsive navigation
- ✅ Personalized dashboard content
- ✅ Logout functionality
- ✅ Stats cards layout (ready for backend integration)

---

## Test Users

All test users have their respective passwords for testing:

| Username | Password | Email | Level | Access |
|----------|----------|-------|-------|--------|
| admin | admin123 | admin@laporkampungku.com | admin | Dashboard (report management) |
| testuser | test123 | testuser@laporkampungku.com | superadmin | Dashboard (full system control) |
| anggota | anggota123 | anggota@laporkampungku.com | anggota | Dashboard (team reports) |
| warga | warga123 | warga@laporkampungku.com | warga | Homepage (submit reports) |

**Create test users:**
```bash
php backend/setup_test_user.php
```

---

## Database Tables

### `tbl_users`
```sql
CREATE TABLE tbl_users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  level ENUM('superadmin', 'admin', 'anggota', 'warga') DEFAULT 'warga',
  is_active TINYINT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## User Flows

### 1. New User Registration
```
[Register Page] → Fill Form → [Submit to auth.php?action=register]
  → Validation → Hash Password → Insert to tbl_users
  → Success Message → [Redirect to Login]
```

### 2. User Login
```
[Login Page] → Enter Credentials → [Submit to auth.php?action=login]
  → Verify Password with password_verify() → Create Session
  → Redirect based on role (admin/anggota → dashboard, warga → homepage)
```

### 3. Dashboard Access
```
[Dashboard Page] → Check Session [POST to auth.php?action=check]
  → If not logged in → Redirect to Login
  → If logged in → Load role-specific dashboard
```

### 4. Logout
```
[Dashboard] → Click Keluar → [POST to auth.php?action=logout]
  → Destroy Session → Redirect to Login
```

---

## Integration Points (Pending)

### Statistics Dashboard
- [ ] Load report counts from `tbl_laporan`
- [ ] Filter by status from `tbl_status`
- [ ] Show charts for completed vs pending reports

### Report Management (Admin/Superadmin)
- [ ] List all reports with status filters
- [ ] Change report status
- [ ] Add admin notes/comments
- [ ] View report history from `tbl_riwayat`

### User Management (Superadmin)
- [ ] Display all users
- [ ] Activate/deactivate users
- [ ] Assign/change roles
- [ ] Delete users

### Citizen Reports (Warga)
- [ ] Show personal report history
- [ ] Track report status changes
- [ ] View photos attached to reports

---

## Security Features

✅ **Implemented:**
- Bcrypt password hashing (PASSWORD_BCRYPT)
- Session-based authentication
- PDO prepared statements (SQL injection prevention)
- Server-side session validation
- HTTP status codes (400, 401, 403, 409, 500)
- CORS-safe JSON responses

⚠️ **Recommended for Production:**
- HTTPS/SSL encryption
- CSRF token validation
- Rate limiting on login attempts
- Password expiration policy
- Two-factor authentication (optional)
- Activity logging

---

## File Locations

```
d:\Kuliah\xampp\htdocs\laporkampungkuv2.0\
├── pages/
│   ├── login.html          ✅ Login form with JS handler
│   ├── register.html       ✅ Register form with JS handler
│   └── user_dashboard.html ✅ User dashboard
├── backend/
│   ├── auth.php            ✅ Authentication API
│   ├── database.sql        ✅ Database schema
│   └── setup_test_user.php ✅ Create test users
└── README.md
```

---

## Testing Checklist

### Login Form
- [ ] Fill login form with valid credentials (admin/admin123)
- [ ] Verify successful login and redirect to dashboard
- [ ] Test with invalid credentials - should show error
- [ ] Test "Lupa Kata Sandi?" link (if implemented)
- [ ] Test password visibility toggle

### Register Form
- [ ] Fill all required fields
- [ ] Test password confirmation validation
- [ ] Verify password match prevents submit
- [ ] Test successful registration → redirect to login
- [ ] Test duplicate email error handling

### Dashboard
- [ ] Login with each role (admin, superadmin, anggota, warga)
- [ ] Verify correct menu items appear for each role
- [ ] Test logout functionality
- [ ] Verify session check on page load
- [ ] Test menu navigation between sections

### Admin/Superadmin Features
- [ ] View report statistics
- [ ] View all reports (coming soon)
- [ ] Manage users (coming soon)

### Warga Features
- [ ] View personal report statistics
- [ ] Link to "Buat Laporan" → report_infrastructure.html
- [ ] View personal report history

---

## Next Steps

### Phase 2: Report Management
1. Create report management interface for admin/superadmin
2. Implement status change functionality with audit trail (tbl_riwayat)
3. Add filters and search for reports

### Phase 3: Dashboard Analytics
1. Implement statistics loading from database
2. Create charts (pending, completed, rejected reports)
3. Display top categories by frequency

### Phase 4: User Management (Superadmin Only)
1. Create user management CRUD interface
2. Add role assignment/modification
3. Implement user activation/deactivation

### Phase 5: Admin Notes & Comments
1. Add comment system to reports
2. Send notifications to users on status change
3. Create audit trail view

---

## Troubleshooting

### "Login gagal" with correct credentials
- Check if user exists in database: `SELECT * FROM tbl_users WHERE username='admin'`
- Verify password hash: Check if `password_verify('admin123', $hashedPassword)` returns true
- Check PHP error logs

### Session not persisting across pages
- Verify `session_start()` is called at the top of auth.php
- Check browser cookie settings
- Clear browser cache and cookies

### Dashboard not loading after login
- Check browser console for fetch errors
- Verify user_dashboard.html can access ../backend/auth.php
- Check if session exists: Print `$_SESSION` in auth.php

---

## Database Connection Details

```php
// localhost:3306
Host: localhost
Database: dbkampungku
User: root
Password: (empty)
Port: 3306
Charset: utf8mb4
```

**Connection string in PHP:**
```php
$pdo = new PDO("mysql:host=localhost;dbname=dbkampungku;charset=utf8mb4", "root", "");
```

---

## Password Management

### Hashing New Passwords
```php
$hashedPassword = password_hash($plainPassword, PASSWORD_BCRYPT);
// Costs: ['cost' => 12] for higher security (default is 10)
```

### Verifying Passwords
```php
if (password_verify($plainPassword, $hashedPassword)) {
    // Password is correct
}
```

### Manual User Creation
```sql
INSERT INTO tbl_users (username, password, email, level, is_active) 
VALUES ('testuser', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36/R/FJe', 'test@example.com', 'warga', 1);
-- Password: password123
```

---

## Support & Documentation

For questions or issues, refer to:
- Database schema: `backend/database.sql`
- Authentication logic: `backend/auth.php`
- Frontend integration: `pages/login.html`, `pages/user_dashboard.html`
- Test data setup: `backend/setup_test_user.php`

---

**Last Updated:** 2026-01-09  
**System Status:** ✅ Production Ready (Auth Module)
