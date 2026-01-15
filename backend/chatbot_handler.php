<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laporkampungku";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Koneksi database gagal']));
}

$conn->set_charset("utf8");

// Get request data
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'send_message') {
    handleSendMessage($input, $conn);
} elseif ($action === 'get_history') {
    handleGetHistory($input, $conn);
} elseif ($action === 'get_conversation') {
    handleGetConversation($input, $conn);
} else {
    echo json_encode(['success' => false, 'error' => 'Action tidak dikenali']);
}

/**
 * Handle sending message
 */
function handleSendMessage($input, $conn) {
    $message = trim($input['message'] ?? '');
    $user_id = $input['user_id'] ?? 'guest';
    $conversation_id = $input['conversation_id'] ?? null;

    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Pesan tidak boleh kosong']);
        return;
    }

    // Create new conversation if not exists
    if (!$conversation_id) {
        $result = $conn->query("INSERT INTO chatbot_conversations (user_id, created_at) VALUES ('$user_id', NOW())");
        $conversation_id = $conn->insert_id;
    }

    // Get bot response
    $response = getBotResponse($message, $conn);

    // Save message to database
    $message_escaped = $conn->real_escape_string($message);
    $response_escaped = $conn->real_escape_string($response);

    $query = "INSERT INTO chatbot_messages (conversation_id, user_id, message, response, created_at) 
              VALUES ($conversation_id, '$user_id', '$message_escaped', '$response_escaped', NOW())";

    if ($conn->query($query)) {
        echo json_encode([
            'success' => true,
            'response' => $response,
            'conversation_id' => $conversation_id
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gagal menyimpan pesan']);
    }
}

/**
 * Get bot response based on user message
 */
function getBotResponse($message, $conn) {
    $message = strtolower(trim($message));

    // Knowledge base responses
    $responses = [
        // Cara membuat laporan
        ['keywords' => ['bagaimana', 'cara', 'membuat', 'laporan', 'laporkan', 'membuat laporan', 'gimana'],
         'response' => 'Cara membuat laporan infrastruktur sangat mudah:

1. **Klik "Laporkan Sekarang"** di halaman utama
2. **Ambil Foto** - Dokumentasikan masalah dengan kamera smartphone
3. **Tandai Lokasi** - GPS otomatis mendeteksi lokasi atau pilih manual di peta
4. **Isi Detail** - Jelaskan masalah dengan deskripsi singkat
5. **Pilih Kategori** - Pilih jenis masalah infrastruktur
6. **Kirim** - Laporan dikirim dan Anda dapat pelacakan real-time

Tips: Foto yang jelas dan lokasi akurat akan mempercepat penanganan! 📸'],

        // Kategori laporan
        ['keywords' => ['kategori', 'laporan', 'jenis', 'macam', 'apa saja', 'kategori laporan', 'laporan apa'],
         'response' => 'Kami menerima laporan untuk berbagai kategori infrastruktur:

📌 **Jalan & Trotoar**
- Jalan berlubang/rusak
- Trotoar rusak/tidak rata

🌊 **Saluran Air**
- Saluran tersumbat
- Banjir berulang

💡 **Penerangan Jalan**
- Lampu mati
- Tiang rusak

🏘️ **Ruang Publik**
- Taman terlantar
- Lapangan tidak terawat

🚰 **Air Bersih**
- Pipa bocor
- Tekanan air rendah

Bisa juga laporan lain terkait infrastruktur komunitas. Laporan Anda sangat berharga! 🙏'],

        // Timeline penyelesaian
        ['keywords' => ['berapa', 'lama', 'proses', 'penyelesaian', 'timeline', 'kapan', 'selesai', 'waktu', 'jam', 'hari'],
         'response' => 'Waktu penyelesaian laporan tergantung dari kategori dan tingkat prioritas:

⚡ **URGENT (1-2 hari)**
- Jalan berlubang besar
- Banjir akut

🔴 **TINGGI (3-5 hari)**
- Lampu mati
- Saluran tersumbat

🟡 **SEDANG (5-10 hari)**
- Perbaikan minor
- Pemeliharaan rutin

🟢 **RENDAH (10+ hari)**
- Laporan non-emergency
- Perencanaan panjang

Rata-rata waktu respons kami adalah **2.5 hari**. Anda dapat melacak progress kapan saja melalui nomor tracking laporan. Transparansi adalah prioritas kami! 📊'],

        // Pelacakan laporan
        ['keywords' => ['lacak', 'tracking', 'status', 'laporan', 'nomor', 'progress', 'pelacakan', 'sudah', 'mana'],
         'response' => 'Cara melacak laporan Anda:

1️⃣ **Login Akun Anda**
   - Masuk ke portal LaporKampungku

2️⃣ **Buka Dashboard**
   - Klik "Dashboard Saya" atau akses dari menu utama

3️⃣ **Lihat Status Laporan**
   - Semua laporan Anda ditampilkan dengan status terkini
   - Warna berbeda menunjukkan tahap penyelesaian

4️⃣ **Cek Detail**
   - Klik laporan untuk melihat:
     • Update terbaru dari pihak berwenang
     • Foto dokumentasi perbaikan
     • Estimasi waktu selesai

💡 **Tips:** Subscribe notifikasi untuk update instant!'],

        // Tentang LaporKampungku
        ['keywords' => ['apa', 'tentang', 'laporkampungku', 'siapa', 'latar belakang', 'tujuan'],
         'response' => 'Selamat datang di **LaporKampungku**! 🏘️

Kami adalah platform pelaporan infrastruktur berbasis komunitas yang mendukung:
- **SDG Goal 11**: Kota dan Komunitas yang Berkelanjutan
- **Gotong Royong Digital**: Kolaborasi masyarakat untuk perubahan nyata

📊 **Statistik Kami:**
- Ribuan laporan diterima
- 87% tingkat penyelesaian
- Waktu respons rata-rata 2.5 hari
- Dipercaya oleh berbagai pemerintah daerah

💪 **Visi Kami:** 
Membangun komunitas yang lebih baik melalui partisipasi warga dalam pelaporan dan penyelesaian masalah infrastruktur.

Mari bersama-sama wujudkan perubahan! 🚀'],

        // Kontak & Support
        ['keywords' => ['hubungi', 'kontak', 'support', 'bantuan', 'help', 'call center', 'help desk', 'customer service'],
         'response' => 'Hubungi kami untuk bantuan lebih lanjut:

📞 **Customer Service**
- Telepon: 1500-LKK (1500-5556)
- Jam: Senin-Jumat, 09:00-17:00 WIB

📧 **Email**
- support@laporkampungku.id
- Respon dalam 24 jam

💬 **Live Chat**
- Tersedia di website (akan datang)

🏢 **Kantor Pusat**
- Gedung Digitalisasi Indonesia
- Jakarta Pusat, DKI Jakarta

📱 **Media Sosial**
- Instagram: @laporkampungku
- Facebook: LaporKampungku
- Twitter: @laporkampungku

Kami siap membantu Anda! 🤝'],

        // Keamanan data
        ['keywords' => ['keamanan', 'data', 'pribadi', 'privasi', 'aman', 'enkripsi', 'digunakan'],
         'response' => 'Keamanan data Anda adalah prioritas utama kami! 🔒

✅ **Perlindungan Data:**
- Enkripsi end-to-end untuk semua komunikasi
- Database terenkripsi dengan standar internasional
- Tidak ada pembagian data pribadi tanpa persetujuan

📋 **Kebijakan Privasi:**
- Data Anda hanya digunakan untuk penanganan laporan
- Tidak ada penjualan data ke pihak ketiga
- Compliance dengan peraturan GDPR dan PLDP

🛡️ **Verifikasi:**
- Login aman dengan 2FA (Two-Factor Authentication)
- Session management yang ketat
- Regular security audit

💡 Laporan Anda aman bersama kami. Percayakan kepada platform yang tepat! 🙏'],

        // Login & Registrasi
        ['keywords' => ['daftar', 'buat akun', 'login', 'registrasi', 'akun', 'password', 'email'],
         'response' => 'Cara membuat akun dan login:

📝 **Registrasi (Daftar Akun):**
1. Klik tombol "Daftar" di halaman utama
2. Isi form dengan data lengkap:
   - Nama lengkap
   - Email aktif
   - Nomor telepon
   - Password yang kuat
3. Verifikasi email Anda
4. Akun siap digunakan!

🔑 **Login:**
1. Klik "Masuk" di halaman utama
2. Masukkan email dan password
3. (Opsional) Centang "Ingat saya"
4. Selamat datang di portal Anda!

💡 **Tips Keamanan:**
- Gunakan password yang kuat (minimal 8 karakter)
- Tidak pernah bagikan password kepada siapa pun
- Logout dari perangkat publik
- Ubah password secara berkala

Butuh bantuan? Chat dengan kami! 💬'],

        // Dashboard
        ['keywords' => ['dashboard', 'profil', 'pengaturan', 'aktivitas', 'laporan saya'],
         'response' => 'Fitur Dashboard Anda:

📊 **Dashboard Utama:**
- Ringkasan statistik laporan Anda
- Grafik progress penyelesaian
- Notifikasi terbaru

📝 **Laporan Saya:**
- Daftar semua laporan yang telah dibuat
- Filter berdasarkan status/kategori
- Akses detail dan dokumentasi setiap laporan

👤 **Profil Saya:**
- Edit informasi pribadi
- Ubah foto profil
- Kelola password

🔔 **Notifikasi:**
- Update status laporan real-time
- Pengumuman penting dari tim
- Newsletter komunitas

⚙️ **Pengaturan:**
- Preferensi notifikasi
- Bahasa dan zona waktu
- Privasi akun

✨ **Pencapaian:**
- Badge dan reward untuk kontributor aktif
- Leaderboard komunitas

Jelajahi dashboard Anda sekarang! 🚀'],

        // Umum
        ['keywords' => ['terima kasih', 'thanks', 'ok', 'baik', 'oke', 'bagus', 'makasih'],
         'response' => 'Sama-sama! 😊 Senang bisa membantu Anda. Jika ada pertanyaan lain, jangan ragu untuk bertanya. Terima kasih telah menjadi bagian dari komunitas LaporKampungku! 🙏'],
    ];

    // Search for matching response
    foreach ($responses as $rule) {
        foreach ($rule['keywords'] as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return $rule['response'];
            }
        }
    }

    // Default response if no match
    return 'Terima kasih atas pertanyaan Anda! 😊

Saat ini pertanyaan Anda belum tersedia di knowledge base kami. Namun, kami terus belajar dan berkembang. 

📌 **Saran Anda sangat berharga!**
Silakan hubungi customer service kami di:
- 📞 1500-LKK (1500-5556)
- 📧 support@laporkampungku.id

Atau silakan tanyakan tentang:
- Cara membuat laporan
- Kategori laporan
- Timeline penyelesaian
- Pelacakan laporan
- Login & Registrasi
- Dan lainnya

Terima kasih! 🙏';
}

/**
 * Get chat history for user
 */
function handleGetHistory($input, $conn) {
    $user_id = $input['user_id'] ?? '';

    if (empty($user_id)) {
        echo json_encode(['success' => false, 'error' => 'User ID tidak valid']);
        return;
    }

    $query = "SELECT DISTINCT cc.id, cc.created_at, 
              (SELECT message FROM chatbot_messages WHERE conversation_id = cc.id ORDER BY id DESC LIMIT 1) as last_message
              FROM chatbot_conversations cc
              WHERE cc.user_id = '$user_id'
              ORDER BY cc.created_at DESC
              LIMIT 20";

    $result = $conn->query($query);

    if ($result) {
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
        echo json_encode(['success' => true, 'conversations' => $conversations]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Query gagal']);
    }
}

/**
 * Get conversation messages
 */
function handleGetConversation($input, $conn) {
    $user_id = $input['user_id'] ?? '';
    $conversation_id = $input['conversation_id'] ?? 0;

    if (empty($user_id) || !$conversation_id) {
        echo json_encode(['success' => false, 'error' => 'Parameter tidak valid']);
        return;
    }

    $query = "SELECT message, response, created_at FROM chatbot_messages 
              WHERE conversation_id = $conversation_id AND user_id = '$user_id'
              ORDER BY id ASC";

    $result = $conn->query($query);

    if ($result) {
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        echo json_encode(['success' => true, 'messages' => $messages]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Query gagal']);
    }
}

$conn->close();
?>
