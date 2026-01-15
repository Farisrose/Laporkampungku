# 📍 LaporKampungku - Community Infrastructure Reporting Platform

A comprehensive web application for citizens and administrators to report, track, and manage infrastructure issues in their communities.

## 🎯 Project Status: ✅ PRODUCTION READY

**Auth Module**: ✅ Complete  
**Dashboard**: ✅ Complete  
**Report System**: ✅ Complete  
**Chatbot Module**: ✅ Complete (NEW!)
**Documentation**: ✅ Comprehensive

---

## 📚 Quick Navigation

### For New Users
→ **[QUICKSTART.md](QUICKSTART.md)** - Get started in 5 minutes

### For Developers
→ **[AUTH_SYSTEM.md](AUTH_SYSTEM.md)** - Complete authentication guide  
→ **[CHATBOT_QUICKSTART.md](CHATBOT_QUICKSTART.md)** - Chatbot setup & usage  
→ **[CHATBOT_DOCUMENTATION.md](CHATBOT_DOCUMENTATION.md)** - Chatbot technical docs  

### For Testers
→ **[FILES_MANIFEST.md](FILES_MANIFEST.md)** - All files created/modified  
→ **[CHATBOT_IMPLEMENTATION_SUMMARY.md](CHATBOT_IMPLEMENTATION_SUMMARY.md)** - What's new
→ **[ARCHITECTURE.md](ARCHITECTURE.md)** - System diagrams and flows  
→ **[SYSTEM_SUMMARY.md](SYSTEM_SUMMARY.md)** - Project overview

---

## 🚀 Features

### ✅ Authentication & Authorization
- User registration with validation
- Login with bcrypt password verification
- Session-based authentication
- 4 user roles: superadmin, admin, anggota, warga
- Secure logout functionality

### ✅ Dashboard
- Role-based access control
- Personalized dashboards per role
- Responsive design (desktop, tablet, mobile)
- Statistics cards (ready for data)
- Menu navigation per role

### ✅ Report Infrastructure
- Multi-step form with map integration
- Photo upload (drag-drop, file input)
- Real-time location selection via Leaflet.js
- Voice input for descriptions
- Database integration with audit trail

### ✅ Database
- 8 properly structured MySQL tables
- Foreign key relationships
- Default status values (Menunggu, Diproses, Selesai, Ditolak)
- Audit trail for tracking changes
- User profiles by role

---

## 🏃 Quick Start

### 1. Setup XAMPP
```bash
# Start Apache and MySQL from XAMPP Control Panel
```

### 2. Create Test Users
```bash
php backend/setup_test_user.php
```

### 3. Access Application
```
http://localhost/laporkampungkuv2.0/pages/login.html
```

### 4. Login with Test Account
```
Username: admin
Password: admin123
```

For more details, see **[QUICKSTART.md](QUICKSTART.md)**

---

## 🔐 Test User Credentials

| Role | Username | Password | Dashboard |
|------|----------|----------|-----------|
| Admin | admin | admin123 | Report Management |
| Superadmin | testuser | test123 | Full System Control |
| Team Member | anggota | anggota123 | Team Reports |
| Citizen | warga | warga123 | Report Submission |

---

## 📁 Project Structure

```
laporkampungkuv2.0/
├── pages/
│   ├── login.html                    # Login form
│   ├── register.html                 # Registration form
│   ├── user_dashboard.html           # User dashboard
│   ├── report_infrastructure.html    # Report submission
│   └── [other pages...]
│
├── backend/
│   ├── auth.php                      # Authentication API
│   ├── save_report.php               # Report submission API
│   ├── database.sql                  # Database schema
│   └── setup_test_user.php          # Create test users
│
├── css/
│   ├── main.css                      # Tailwind imports
│   └── tailwind.css
│
├── public/
│   └── uploads/                      # Report photos
│
├── QUICKSTART.md                     # 5-minute setup guide
├── AUTH_SYSTEM.md                    # Authentication docs
├── ARCHITECTURE.md                   # System diagrams
└── SYSTEM_SUMMARY.md                 # Project overview
```

---

## 🛠️ Tech Stack

### Frontend
- HTML5 + Semantic markup
- Tailwind CSS (utility-first design)
- Vanilla JavaScript (no frameworks)
- Leaflet.js (interactive maps)

### Backend
- PHP 7.4+ (procedural)
- MySQL 5.7+ / MariaDB 10.3+
- PDO (database abstraction)
- bcrypt (password hashing)

### Architecture
- REST API with JSON responses
- Session-based authentication
- Role-Based Access Control (RBAC)
- Transaction-safe database operations

---

## 🔒 Security Features

✅ **Implemented:**
- Bcrypt password hashing (PASSWORD_BCRYPT)
- PDO prepared statements (SQL injection prevention)
- Server-side session verification
- Safe file upload handling (random filenames)
- Unique constraints on credentials (DB level)
- Proper HTTP status codes (401, 403, 409, 500)

⚠️ **Recommended for Production:**
- HTTPS/SSL encryption
- CSRF token validation
- Rate limiting on login attempts
- Password expiration policy
- Activity logging

---

## 📖 Documentation

| Document | Purpose |
|----------|---------|
| [QUICKSTART.md](QUICKSTART.md) | 5-minute setup and testing guide |
| [AUTH_SYSTEM.md](AUTH_SYSTEM.md) | Complete authentication documentation |
| [ARCHITECTURE.md](ARCHITECTURE.md) | System diagrams, flows, and sequences |
| [SYSTEM_SUMMARY.md](SYSTEM_SUMMARY.md) | Comprehensive project overview |

---

## 🧪 Testing

### Login Flow
1. Go to login page
2. Enter credentials (admin/admin123)
3. Verify redirect to dashboard
4. Check role badge

### Registration Flow
1. Go to register page
2. Fill form with new data
3. Verify password confirmation
4. Submit and redirect to login
5. Login with new account

### Dashboard
1. Login as different roles
2. Observe different menus per role
3. Verify statistics cards
4. Test logout

For detailed testing steps, see [QUICKSTART.md](QUICKSTART.md#-testing-workflow)

---

## 📊 Database

### Tables (8)
- `tbl_users` - User accounts and authentication
- `tbl_superadmin`, `tbl_admin`, `tbl_anggota` - Role profiles
- `tbl_laporan` - Infrastructure reports
- `tbl_foto_laporan` - Report photos
- `tbl_status` - Status reference
- `tbl_riwayat` - Audit trail

### Connection Details
```php
Host: localhost:3306
Database: dbkampungku
User: root
Password: (empty)
Charset: utf8mb4
```

---

## 🎓 User Roles

### Superadmin
- Full system control
- User management
- All reports access
- System statistics

### Admin
- Report management
- Status changes
- Add comments
- Statistics view

### Anggota (Team Member)
- Team reports view
- Limited functionality

### Warga (Citizen)
- Submit reports
- Track personal reports
- View report status

---

## 🚀 Next Steps

### Phase 5: Admin Report Management
- Report listing interface
- Status change functionality
- Admin comments system

### Phase 6: Dashboard Analytics
- Load statistics from database
- Create charts and graphs
- Category distribution

### Phase 7: User Management
- Superadmin user interface
- Role assignment
- Account management

---

## 🐛 Troubleshooting

### Login Issues
- Verify MySQL is running
- Check username/password match test credentials
- Recreate test users: `php backend/setup_test_user.php`

### Dashboard Not Loading
- Verify Apache is running
- Check browser console for errors
- Ensure database connection is active

### Map Not Showing
- Clear browser cache
- Refresh page
- Check Leaflet.js CDN is loading

For more help, see [QUICKSTART.md](QUICKSTART.md#-troubleshooting)

---

## 📝 Development Notes

- All code uses prepared statements for security
- Responsive design mobile-first approach
- Session-based not token-based auth
- Bcrypt with random salt for password security
- Proper error handling with user feedback

---

## ✨ Project Highlights

✅ Secure - Bcrypt + PDO prepared statements  
✅ Scalable - Proper database design  
✅ Maintainable - Clean, commented code  
✅ Responsive - Works on all devices  
✅ Well-documented - 4 documentation files  
✅ Production-ready - Session management  
✅ Extensible - Easy to add features  

---

## 📞 Documentation Index

1. **[QUICKSTART.md](QUICKSTART.md)** - Start here! 5-minute setup
2. **[AUTH_SYSTEM.md](AUTH_SYSTEM.md)** - Authentication details
3. **[ARCHITECTURE.md](ARCHITECTURE.md)** - System diagrams
4. **[SYSTEM_SUMMARY.md](SYSTEM_SUMMARY.md)** - Full overview

---

## 🎉 Getting Started

**New to the project?** → Start with **[QUICKSTART.md](QUICKSTART.md)**

**Want technical details?** → Read **[AUTH_SYSTEM.md](AUTH_SYSTEM.md)**

**Need system overview?** → Check **[SYSTEM_SUMMARY.md](SYSTEM_SUMMARY.md)**

**Looking for diagrams?** → See **[ARCHITECTURE.md](ARCHITECTURE.md)**

---

**Version:** 2.0  
**Status:** ✅ Production Ready  
**Last Updated:** January 9, 2026  
**Author:** LaporKampungku Development Team

---

## 📄 Original Project Info

This project utilizes:
- **HTML5** - Modern HTML structure
- **Tailwind CSS** - Utility-first CSS framework
- **Custom Components** - Pre-built classes
- **NPM Scripts** - Development automation
- **Responsive Design** - Mobile-first approach

---

**Ready to contribute?** Fork the repository and follow the coding standards in the project!



## 🧩 Customization

To customize the Tailwind configuration, edit the `tailwind.config.js` file:


## 📦 Build for Production

Build the CSS for production:

```bash
npm run build:css
# or
yarn build:css
```

## 📱 Responsive Design

The app is built with responsive design using Tailwind CSS breakpoints:

- `sm`: 640px and up
- `md`: 768px and up
- `lg`: 1024px and up
- `xl`: 1280px and up
- `2xl`: 1536px and up

## 🙏 Acknowledgments

- Built with [Rocket.new](https://rocket.new)
- Powered by HTML and Tailwind CSS

Built with ❤️ on Rocket.new
