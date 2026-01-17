<?php
// backend/setup_superadmin_user.php
// File untuk setup superadmin user (jalankan sekali saja)

// Database config
$dbHost = '127.0.0.1';
$dbName = 'dbkampungku';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Create system config table if not exists
$sqlCreateConfig = "CREATE TABLE IF NOT EXISTS `tbl_system_config` (
  `id` INT PRIMARY KEY,
  `app_name` VARCHAR(100),
  `app_email` VARCHAR(100),
  `app_phone` VARCHAR(20),
  `maintenance_mode` TINYINT(1) DEFAULT 0,
  `max_upload_size` INT DEFAULT 50,
  `report_retention_days` INT DEFAULT 365,
  `api_timeout` INT DEFAULT 30,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

// Create activity log table if not exists
$sqlCreateLog = "CREATE TABLE IF NOT EXISTS `tbl_activity_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50),
  `action` VARCHAR(100),
  `details` TEXT,
  `ip_address` VARCHAR(45),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    $pdo->exec($sqlCreateConfig);
    $pdo->exec($sqlCreateLog);
    echo "✅ Tables created successfully<br>";
} catch (Exception $e) {
    echo "⚠️ " . $e->getMessage() . "<br>";
}

// Insert default system config
try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO `tbl_system_config` VALUES (1, 'LaporKampungku', 'info@laporkampungku.id', '+62 887-4373-52670', 0, 50, 365, 30, NOW())");
    $stmt->execute();
    echo "✅ System config inserted<br>";
} catch (Exception $e) {
    echo "⚠️ " . $e->getMessage() . "<br>";
}

// Insert default superadmin user
$superadminPassword = password_hash('SuperAdmin@123', PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO `tbl_users` (username, email, password, level, is_active, created_at) VALUES ('superadmin', 'superadmin@laporkampungku.id', :password, 'superadmin', 1, NOW())");
    $stmt->execute([':password' => $superadminPassword]);
    echo "✅ Superadmin user created<br>";
} catch (Exception $e) {
    echo "⚠️ " . $e->getMessage() . "<br>";
}

echo "<br><strong>Setup Complete!</strong><br>";
echo "Login dengan:<br>";
echo "Username: <strong>superadmin</strong><br>";
echo "Password: <strong>SuperAdmin@123</strong><br>";
?>
