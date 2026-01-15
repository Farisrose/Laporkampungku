<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laporkampungku";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// SQL queries
$sql_queries = array(
    // Tabel untuk percakapan
    "CREATE TABLE IF NOT EXISTS `chatbot_conversations` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `user_id` varchar(50) NOT NULL,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      KEY `user_id` (`user_id`),
      KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabel untuk pesan
    "CREATE TABLE IF NOT EXISTS `chatbot_messages` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `conversation_id` int(11) NOT NULL,
      `user_id` varchar(50) NOT NULL,
      `message` longtext NOT NULL COMMENT 'Pesan dari user',
      `response` longtext NOT NULL COMMENT 'Respon dari chatbot',
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`conversation_id`) REFERENCES `chatbot_conversations`(`id`) ON DELETE CASCADE,
      KEY `conversation_id` (`conversation_id`),
      KEY `user_id` (`user_id`),
      KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabel untuk feedback
    "CREATE TABLE IF NOT EXISTS `chatbot_feedback` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `message_id` int(11) NOT NULL,
      `conversation_id` int(11) NOT NULL,
      `user_id` varchar(50) NOT NULL,
      `rating` int(1) COMMENT '1=Tidak Membantu, 2=Cukup, 3=Sangat Membantu',
      `feedback` text,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`conversation_id`) REFERENCES `chatbot_conversations`(`id`) ON DELETE CASCADE,
      KEY `user_id` (`user_id`),
      KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    // Tabel analytics
    "CREATE TABLE IF NOT EXISTS `chatbot_analytics` (
      `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `total_conversations` int(11) DEFAULT 0,
      `total_messages` int(11) DEFAULT 0,
      `average_satisfaction` decimal(3,2) DEFAULT 0.00,
      `popular_keywords` text,
      `date` date DEFAULT CURRENT_DATE,
      UNIQUE KEY `date` (`date`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
);

// Execute queries
$success = true;
foreach ($sql_queries as $query) {
    if (!$conn->query($query)) {
        echo "Error: " . $conn->error . "\n";
        $success = false;
    }
}

if ($success) {
    echo "✓ Database tables berhasil dibuat!\n";
    echo "✓ Tabel yang dibuat:\n";
    echo "  - chatbot_conversations\n";
    echo "  - chatbot_messages\n";
    echo "  - chatbot_feedback\n";
    echo "  - chatbot_analytics\n";
} else {
    echo "✗ Ada error saat membuat tabel\n";
}

$conn->close();
?>
