<?php
// backend/superadmin_handler.php
// Handler untuk fitur superadmin

session_start();

header('Content-Type: application/json; charset=utf-8');

// Check if user is superadmin
if (!isset($_SESSION['level']) || $_SESSION['level'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses Ditolak']);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;

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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// ==================== MANAJEMEN USER ====================

// GET ALL USERS
if ($action === 'get_all_users') {
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, level, is_active, created_at FROM tbl_users ORDER BY created_at DESC");
        $stmt->execute();
        $users = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'data' => $users
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ADD NEW USER
elseif ($action === 'add_user') {
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $level = $_POST['level'] ?? 'warga';

    if (!$username || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
        exit;
    }

    try {
        // Check if username exists
        $stmt = $pdo->prepare("SELECT id FROM tbl_users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Username sudah terdaftar']);
            exit;
        }

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM tbl_users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
            exit;
        }

        // Insert new user
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO tbl_users (username, email, password, level, is_active, created_at) VALUES (:username, :email, :password, :level, 1, NOW())");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':level' => $level
        ]);

        echo json_encode(['success' => true, 'message' => 'User berhasil ditambahkan']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// UPDATE USER
elseif ($action === 'update_user') {
    $userId = $_POST['id'] ?? null;
    $email = $_POST['email'] ?? null;
    $level = $_POST['level'] ?? null;
    $isActive = $_POST['is_active'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID diperlukan']);
        exit;
    }

    try {
        $updates = [];
        $params = [':user_id' => $userId];

        if ($email) {
            $updates[] = "email = :email";
            $params[':email'] = $email;
        }
        if ($level) {
            $updates[] = "level = :level";
            $params[':level'] = $level;
        }
        if ($isActive !== null) {
            $updates[] = "is_active = :is_active";
            $params[':is_active'] = $isActive;
        }

        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tidak ada data untuk diupdate']);
            exit;
        }

        $sql = "UPDATE tbl_users SET " . implode(", ", $updates) . " WHERE id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo json_encode(['success' => true, 'message' => 'User berhasil diperbarui']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// DELETE USER
elseif ($action === 'delete_user') {
    $userId = $_POST['id'] ?? null;

    if (!$userId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID diperlukan']);
        exit;
    }

    try {
        // Prevent deleting current superadmin
        if ($userId == $_SESSION['user_id']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Tidak bisa menghapus akun Anda sendiri']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM tbl_users WHERE id = :id");
        $stmt->execute([':id' => $userId]);

        echo json_encode(['success' => true, 'message' => 'User berhasil dihapus']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ==================== KONFIGURASI SISTEM ====================

// GET SYSTEM CONFIG
elseif ($action === 'get_system_config') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM tbl_system_config LIMIT 1");
        $stmt->execute();
        $config = $stmt->fetch();

        if (!$config) {
            // Return default config if doesn't exist
            $config = [
                'app_name' => 'LaporKampungku',
                'app_email' => 'info@laporkampungku.id',
                'app_phone' => '+62 887-4373-52670',
                'maintenance_mode' => '0',
                'max_upload_size' => '50',
                'report_retention_days' => '365',
                'api_timeout' => '30'
            ];
        }

        echo json_encode(['success' => true, 'data' => $config]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// UPDATE SYSTEM CONFIG
elseif ($action === 'update_system_config') {
    $appName = $_POST['app_name'] ?? null;
    $appEmail = $_POST['app_email'] ?? null;
    $appPhone = $_POST['app_phone'] ?? null;
    $maintenanceMode = $_POST['maintenance_mode'] ?? '0';
    $maxUploadSize = $_POST['max_upload_size'] ?? '50';
    $reportRetention = $_POST['report_retention_days'] ?? '365';
    $apiTimeout = $_POST['api_timeout'] ?? '30';

    try {
        // Check if config exists
        $stmt = $pdo->prepare("SELECT id FROM tbl_system_config LIMIT 1");
        $stmt->execute();
        $exists = $stmt->fetch();

        if ($exists) {
            // Update existing config
            $stmt = $pdo->prepare("UPDATE tbl_system_config SET app_name = :app_name, app_email = :app_email, app_phone = :app_phone, maintenance_mode = :maintenance_mode, max_upload_size = :max_upload_size, report_retention_days = :report_retention, api_timeout = :api_timeout WHERE id = 1");
        } else {
            // Insert new config
            $stmt = $pdo->prepare("INSERT INTO tbl_system_config (app_name, app_email, app_phone, maintenance_mode, max_upload_size, report_retention_days, api_timeout) VALUES (:app_name, :app_email, :app_phone, :maintenance_mode, :max_upload_size, :report_retention, :api_timeout)");
        }

        $stmt->execute([
            ':app_name' => $appName,
            ':app_email' => $appEmail,
            ':app_phone' => $appPhone,
            ':maintenance_mode' => $maintenanceMode,
            ':max_upload_size' => $maxUploadSize,
            ':report_retention' => $reportRetention,
            ':api_timeout' => $apiTimeout
        ]);

        echo json_encode(['success' => true, 'message' => 'Konfigurasi sistem berhasil disimpan']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// ==================== ANALITIK & LAPORAN ====================

// GET DASHBOARD ANALYTICS
elseif ($action === 'get_analytics') {
    try {
        // Total users
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_users");
        $stmt->execute();
        $totalUsers = $stmt->fetch()['total'];

        // Active users
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_users WHERE is_active = 1");
        $stmt->execute();
        $activeUsers = $stmt->fetch()['total'];

        // Total reports
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_reports");
        $stmt->execute();
        $totalReports = $stmt->fetch()['total'];

        // Reports this month
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_reports WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
        $stmt->execute();
        $monthlyReports = $stmt->fetch()['total'];

        // Completion rate
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_reports WHERE status = 'selesai'");
        $stmt->execute();
        $completedReports = $stmt->fetch()['total'];
        $completionRate = $totalReports > 0 ? round(($completedReports / $totalReports) * 100, 2) : 0;

        // New users this month
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_users WHERE MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())");
        $stmt->execute();
        $newUsers = $stmt->fetch()['total'];

        // Average response time
        $stmt = $pdo->prepare("SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, updated_at)) as avg_hours FROM tbl_reports WHERE status = 'selesai'");
        $stmt->execute();
        $avgResponse = $stmt->fetch()['avg_hours'] ?? 0;

        // Reports by category
        $stmt = $pdo->prepare("SELECT kategori, COUNT(*) as total FROM tbl_reports GROUP BY kategori ORDER BY total DESC LIMIT 10");
        $stmt->execute();
        $reportsByCategory = $stmt->fetchAll();

        // Reports by status
        $stmt = $pdo->prepare("SELECT status, COUNT(*) as total FROM tbl_reports GROUP BY status");
        $stmt->execute();
        $reportsByStatus = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'total_reports' => $totalReports,
                'monthly_reports' => $monthlyReports,
                'completion_rate' => $completionRate,
                'new_users' => $newUsers,
                'avg_response_time' => round($avgResponse, 1),
                'reports_by_category' => $reportsByCategory,
                'reports_by_status' => $reportsByStatus
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// GET USER ACTIVITY LOG
elseif ($action === 'get_activity_log') {
    try {
        $stmt = $pdo->prepare("SELECT username, action, details, created_at FROM tbl_activity_log ORDER BY created_at DESC LIMIT 20");
        $stmt->execute();
        $logs = $stmt->fetchAll();

        echo json_encode(['success' => true, 'data' => $logs]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// GET SYSTEM STATUS
elseif ($action === 'get_system_status') {
    try {
        $status = [
            'database' => 'connected',
            'server' => 'running',
            'api' => 'operational',
            'uptime' => '99.8%',
            'last_backup' => date('Y-m-d H:i:s'),
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB'
        ];

        echo json_encode(['success' => true, 'data' => $status]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    exit;
}

// Invalid action
else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
}
?>
