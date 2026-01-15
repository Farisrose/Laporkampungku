# 📱 Chatbot LaporKampungku - Dokumentasi Lengkap

## Ringkasan
Chatbot LaporKampungku adalah asisten AI berbasis knowledge base yang membantu pengguna mengakses informasi tentang platform LaporKampungku 24/7 tanpa perlu menunggu response dari customer service. Sistem ini menyimpan semua percakapan dalam database untuk tracking riwayat chat.

---

## 🎯 Fitur Utama

### 1. **Real-time Chat Interface**
- Interface chat modern yang responsif
- Animasi smooth untuk setiap pesan
- Emoji support untuk visual yang menarik
- Typing indicator (loading animation) saat chatbot merespons

### 2. **Instant Response**
- Respons instan tanpa delay (tidak perlu menunggu)
- Knowledge base dengan 10+ kategori pertanyaan
- Fallback response yang helpful jika pertanyaan tidak cocok

### 3. **Chat History Management**
- Semua percakapan disimpan ke database
- User dapat melihat riwayat chat sebelumnya
- Modal untuk browsing history percakapan
- Quick load percakapan lama

### 4. **Database Integration**
- Struktur database yang scalable
- Support untuk guest dan registered users
- Analytics untuk tracking penggunaan

---

## 🗂️ Struktur File

```
projek/
├── pages/
│   └── chatbot.html                    # Interface chatbot
├── backend/
│   ├── chatbot_handler.php             # Backend API handler
│   ├── chatbot_database.sql            # SQL schema
│   └── setup_chatbot_db.php            # Setup script
└── js/
    └── navbar.js                        # Navbar functionality
```

---

## 📊 Database Schema

### Tabel: `chatbot_conversations`
Menyimpan data percakapan

```sql
id                  INT PRIMARY KEY AUTO_INCREMENT
user_id            VARCHAR(50)
created_at         TIMESTAMP
updated_at         TIMESTAMP
```

### Tabel: `chatbot_messages`
Menyimpan pesan dan respons chatbot

```sql
id                  INT PRIMARY KEY AUTO_INCREMENT
conversation_id    INT FOREIGN KEY
user_id            VARCHAR(50)
message            LONGTEXT (pesan dari user)
response           LONGTEXT (respons dari chatbot)
created_at         TIMESTAMP
```

### Tabel: `chatbot_feedback`
Untuk mengumpulkan feedback user (opsional)

```sql
id                  INT PRIMARY KEY AUTO_INCREMENT
message_id         INT
conversation_id    INT FOREIGN KEY
user_id            VARCHAR(50)
rating             INT (1-3)
feedback           TEXT
created_at         TIMESTAMP
```

### Tabel: `chatbot_analytics`
Untuk tracking analytics

```sql
id                        INT PRIMARY KEY AUTO_INCREMENT
total_conversations      INT
total_messages           INT
average_satisfaction     DECIMAL
popular_keywords         TEXT
date                     DATE UNIQUE
```

---

## 🔌 API Endpoints

### 1. **Send Message**
**Endpoint:** `POST /backend/chatbot_handler.php`

**Request:**
```json
{
  "action": "send_message",
  "message": "Bagaimana cara membuat laporan?",
  "user_id": "user123",
  "conversation_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "response": "Cara membuat laporan infrastruktur sangat mudah...",
  "conversation_id": 1
}
```

### 2. **Get History**
**Endpoint:** `POST /backend/chatbot_handler.php`

**Request:**
```json
{
  "action": "get_history",
  "user_id": "user123"
}
```

**Response:**
```json
{
  "success": true,
  "conversations": [
    {
      "id": 1,
      "created_at": "2024-01-15 10:30:00",
      "last_message": "Bagaimana cara membuat laporan?"
    }
  ]
}
```

### 3. **Get Conversation**
**Endpoint:** `POST /backend/chatbot_handler.php`

**Request:**
```json
{
  "action": "get_conversation",
  "user_id": "user123",
  "conversation_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "messages": [
    {
      "message": "Bagaimana cara membuat laporan?",
      "response": "Cara membuat laporan infrastruktur sangat mudah...",
      "created_at": "2024-01-15 10:30:00"
    }
  ]
}
```

---

## 🤖 Knowledge Base

Chatbot memiliki knowledge base untuk kategori berikut:

### 1. **Cara Membuat Laporan**
Panduan lengkap 5 langkah pembuatan laporan

### 2. **Kategori Laporan**
Daftar semua kategori laporan yang tersedia (Jalan, Air, Penerangan, dll)

### 3. **Timeline Penyelesaian**
Informasi tentang waktu penyelesaian berdasarkan prioritas

### 4. **Pelacakan Laporan**
Cara melacak status laporan yang sudah dibuat

### 5. **Tentang LaporKampungku**
Informasi umum tentang platform

### 6. **Contact & Support**
Informasi kontak customer service

### 7. **Keamanan Data**
Penjelasan tentang keamanan dan privasi data

### 8. **Login & Registrasi**
Panduan membuat akun dan login

### 9. **Dashboard**
Penjelasan fitur-fitur dashboard

### 10. **Response Default**
Jika pertanyaan tidak match dengan knowledge base

---

## 💻 Frontend Features

### Interface Elements

```
┌─────────────────────────────────────┐
│         Chat Header                 │
│ 💬 Chat Assistant | Riwayat Chat   │
├─────────────────────────────────────┤
│                                     │
│  [Chat Messages Area]               │
│  👤 User message                    │
│  🤖 Bot response                    │
│  👤 User message                    │
│                                     │
├─────────────────────────────────────┤
│ [Input box] ┌────────────────────┐  │
│             │ Kirim (button)     │  │
│             └────────────────────┘  │
└─────────────────────────────────────┘
```

### Fitur JavaScript

- **Real-time Messaging:** AJAX POST untuk mengirim pesan
- **Auto-scroll:** Otomatis scroll ke pesan terbaru
- **Input Management:** Clear input setelah mengirim
- **Loading State:** Disable button saat loading
- **Modal History:** Tampilkan riwayat dalam modal popup
- **Quick Actions:** 4 tombol cepat untuk pertanyaan umum

---

## 🚀 Cara Implementasi

### 1. Setup Database
Jalankan setup script:
```bash
php backend/setup_chatbot_db.php
```

Atau import SQL manual:
```bash
mysql -u root laporkampungku < backend/chatbot_database.sql
```

### 2. Akses Interface
Buka di browser:
```
http://localhost/projek/pages/chatbot.html
```

### 3. Testing
- Ketik pesan di input box
- Klik "Kirim" atau tekan Enter
- Bot akan merespons instan
- Klik "Riwayat Chat" untuk melihat percakapan sebelumnya (jika sudah login)

---

## 🎨 Design Details

### Color Scheme
- **Primary:** #2563EB (Biru)
- **User Message:** Gradient biru
- **Bot Message:** Putih dengan border abu
- **Avatar User:** 👤 (Blue background)
- **Avatar Bot:** 🤖 (Light gray background)

### Typography
- **Font:** Sesuai dengan theme homepage
- **Heading:** Font headline, bold
- **Body:** Font secondary, regular

### Animations
- **Message Slide In:** 0.3s ease-out
- **Loading Dots:** Infinite 1.4s animation
- **Modal Fade:** 0.3s ease
- **Button Hover:** Transform + shadow

---

## 📝 Usage Examples

### Contoh 1: Guest User
```
Input: "Bagaimana cara membuat laporan?"
Output: Menampilkan panduan 5 langkah + tips
Database: Tersimpan dengan user_id = "guest"
```

### Contoh 2: Registered User
```
Input: "Berapa lama waktu penyelesaian?"
Output: Penjelasan timeline + status rata-rata
Database: Tersimpan dengan user_id = actual_user_id
Riwayat: Bisa dilihat di modal history
```

### Contoh 3: Load History
```
Click: "Riwayat Chat"
Result: Modal terbuka dengan daftar percakapan sebelumnya
Click: Percakapan lama
Result: Semua pesan dari percakapan itu ditampilkan
```

---

## ⚙️ Konfigurasi

### Database Connection (chatbot_handler.php)
```php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laporkampungku";
```

### Modifikasi Knowledge Base
Edit fungsi `getBotResponse()` di `chatbot_handler.php`:
```php
function getBotResponse($message, $conn) {
    $responses = [
        ['keywords' => [...], 'response' => '...'],
        // Tambah response baru di sini
    ];
    // ...
}
```

---

## 🐛 Troubleshooting

### Issue: Database tidak terkoneksi
**Solution:** Pastikan MySQL running dan database `laporkampungku` sudah dibuat

### Issue: Pesan tidak terkirim
**Solution:** Buka browser console (F12) untuk melihat error detail

### Issue: History tidak muncul
**Solution:** Pastikan sudah login dengan user yang valid, atau gunakan guest user

### Issue: Response tidak sesuai
**Solution:** Check keywords di `getBotResponse()` function, tambahkan keyword baru jika diperlukan

---

## 📈 Fitur Tambahan (Future)

- [ ] Image support dalam chat
- [ ] Quick reply buttons
- [ ] Chatbot training dari feedback
- [ ] Multi-language support
- [ ] Export chat history ke PDF
- [ ] Rating system untuk setiap response
- [ ] Analytics dashboard untuk admin
- [ ] Integration dengan helpdesk system

---

## 👨‍💻 Developer Notes

### Performance Optimization
- Gunakan prepared statements untuk query (jika diperluas)
- Implement pagination untuk history dengan banyak data
- Cache knowledge base jika traffic tinggi
- Implement rate limiting untuk prevent abuse

### Security
- Validate semua input dari user
- Escape output dengan `real_escape_string()`
- Implement CORS jika API diakses dari domain lain
- Hash user_id jika berisi sensitive data

### Testing
- Test dengan berbagai keyword variations
- Test dengan unicode dan special characters
- Test mobile responsiveness
- Test browser compatibility (Chrome, Firefox, Safari, Edge)

---

## 📞 Support & Feedback

Untuk feedback atau saran improvement:
- Email: support@laporkampungku.id
- Hubungi tim development

---

**Dibuat:** 15 January 2026
**Version:** 1.0
**Status:** Production Ready
