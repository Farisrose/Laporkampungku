# ⚡ Chatbot Quick Start Guide

## Apa itu Chatbot LaporKampungku?
Platform chat 24/7 yang membantu user mendapatkan informasi tentang LaporKampungku **tanpa menunggu**. Semua chat disimpan di database untuk tracking riwayat.

---

## ✅ Setup (Sudah Selesai!)

Database tables sudah dibuat otomatis:
- ✓ `chatbot_conversations` - Menyimpan percakapan
- ✓ `chatbot_messages` - Menyimpan pesan & response
- ✓ `chatbot_feedback` - Untuk rating response
- ✓ `chatbot_analytics` - Untuk analytics

---

## 🎮 Cara Menggunakan

### Akses Chatbot
1. Buka: **http://localhost/projek/pages/chatbot.html**
2. Atau klik "💬 Chat Assistant" di navbar

### Chat dengan Bot
1. Ketik pertanyaan di input box (cth: "Bagaimana cara membuat laporan?")
2. Klik "Kirim" atau tekan Enter
3. Bot akan merespons **instan** (tidak perlu menunggu)

### Lihat Riwayat Chat
1. Login dengan akun Anda
2. Klik tombol "🕐 Riwayat Chat"
3. Pilih percakapan lama untuk memload ulang

### Quick Actions
4 tombol cepat untuk pertanyaan umum:
- 📋 Cara Lapor
- 📑 Kategori
- ⏱️ Timeline
- 🔍 Pelacakan

---

## 🧠 Topik yang Bisa Ditanya

Bot bisa menjawab tentang:

| Topik | Contoh Pertanyaan |
|-------|-------------------|
| 📝 Membuat Laporan | "Bagaimana cara membuat laporan?" |
| 📑 Kategori | "Apa saja kategori laporan?" |
| ⏱️ Timeline | "Berapa lama penyelesaian?" |
| 🔍 Tracking | "Bagaimana cara melacak laporan?" |
| ℹ️ Tentang | "Apa itu LaporKampungku?" |
| 📞 Support | "Bagaimana cara hubungi support?" |
| 🔐 Keamanan | "Apakah data saya aman?" |
| 👤 Account | "Bagaimana cara daftar?" |
| 📊 Dashboard | "Apa saja fitur dashboard?" |

---

## 📁 File Structure

```
projek/
├── pages/
│   └── chatbot.html                    # Halaman chatbot
├── backend/
│   ├── chatbot_handler.php             # API backend
│   ├── chatbot_database.sql            # SQL schema
│   └── setup_chatbot_db.php            # Setup script
└── CHATBOT_DOCUMENTATION.md            # Dokumentasi lengkap
```

---

## 💾 Database Structure

### Tabel: chatbot_conversations
```
id              | user_id      | created_at
1               | user123      | 2024-01-15 10:30:00
2               | guest        | 2024-01-15 10:45:00
```

### Tabel: chatbot_messages
```
id | conversation_id | user_id  | message | response | created_at
1  | 1              | user123  | "Cara membuat laporan?" | "Berikut panduannya..." | 2024-01-15 10:30:00
2  | 1              | user123  | "Kategori apa saja?" | "Kami menerima..." | 2024-01-15 10:35:00
```

---

## 🎨 Interface Preview

```
┌─────────────────────────────────────────────────┐
│ 💬 Chat Assistant LaporKampungku | 🕐 Riwayat  │
├─────────────────────────────────────────────────┤
│                                                 │
│  👤 Bagaimana cara membuat laporan?             │
│                                                 │
│  🤖 Cara membuat laporan infrastruktur sangat   │
│     mudah:                                      │
│     1. Klik "Laporkan Sekarang"                 │
│     2. Ambil Foto...                            │
│                                                 │
│  👤 Berapa kategori laporan?                    │
│                                                 │
│  🤖 Kami menerima laporan untuk berbagai        │
│     kategori infrastruktur...                   │
│                                                 │
├─────────────────────────────────────────────────┤
│ [Ketik pertanyaan...] [Kirim]                   │
└─────────────────────────────────────────────────┘
```

---

## 🔄 Fitur Real-time

✅ **Instant Response** - Respons langsung tanpa delay
✅ **Auto Scroll** - Chat scroll otomatis ke pesan baru
✅ **Loading Animation** - Animasi loading saat bot merespons
✅ **Clean History** - Riwayat tersimpan dan bisa diakses kembali

---

## 🚀 Performance

- **Response Time:** < 100ms (instant)
- **Database Query:** Optimized dengan indexes
- **UI Responsiveness:** Smooth animations
- **Mobile Friendly:** Fully responsive design

---

## 🔒 Keamanan

- ✓ Input validation pada semua field
- ✓ Database escaped untuk prevent SQL injection
- ✓ CORS ready untuk API calls
- ✓ User data terpisah per session

---

## ⚙️ Konfigurasi

### Mengubah Knowledge Base
Edit file `backend/chatbot_handler.php`, cari function `getBotResponse()`:

```php
$responses = [
    ['keywords' => ['keyword1', 'keyword2'], 'response' => 'Jawaban...'],
    // Tambah jawaban baru di sini
];
```

### Database Settings
Di file `chatbot_handler.php`:
```php
$servername = "localhost";   // Change if needed
$username = "root";          // Change if needed
$password = "";              // Change if needed
$dbname = "laporkampungku";  // Change if needed
```

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| Database tidak terkoneksi | Pastikan MySQL running, database sudah dibuat |
| Pesan tidak terkirim | Buka F12 > Console untuk error message |
| History tidak muncul | Login dulu, atau gunakan guest user |
| Response tidak sesuai | Cek keywords di knowledge base |

---

## 📊 Monitoring

### Melihat Database Records
```sql
-- Lihat semua percakapan
SELECT * FROM chatbot_conversations;

-- Lihat semua pesan
SELECT * FROM chatbot_messages;

-- Hitung total messages per user
SELECT user_id, COUNT(*) as total FROM chatbot_messages GROUP BY user_id;
```

---

## 🎯 Next Steps

1. ✅ Test chatbot dengan berbagai pertanyaan
2. ✅ Cek riwayat chat tersimpan di database
3. ✅ Tambahkan lebih banyak knowledge base jika perlu
4. ✅ Monitor dengan query database untuk analytics

---

## 📞 Support

Untuk bantuan atau saran:
- 📧 Email: support@laporkampungku.id
- 📞 Telepon: 1500-LKK
- 🌐 Website: www.laporkampungku.id

---

**Version:** 1.0  
**Last Updated:** 15 January 2026  
**Status:** ✅ Production Ready
