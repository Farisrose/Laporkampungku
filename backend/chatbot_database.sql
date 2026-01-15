-- Tabel untuk menyimpan percakapan chatbot
CREATE TABLE IF NOT EXISTS `chatbot_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` varchar(50) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk menyimpan pesan dalam percakapan
CREATE TABLE IF NOT EXISTS `chatbot_messages` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk menyimpan feedback dari user terhadap respons chatbot
CREATE TABLE IF NOT EXISTS `chatbot_feedback` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk analytics dan statistics
CREATE TABLE IF NOT EXISTS `chatbot_analytics` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `total_conversations` int(11) DEFAULT 0,
  `total_messages` int(11) DEFAULT 0,
  `average_satisfaction` decimal(3,2) DEFAULT 0.00,
  `popular_keywords` text,
  `date` date DEFAULT CURRENT_DATE,
  UNIQUE KEY `date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
