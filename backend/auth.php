<?php
// backend/auth.php
// Authentication handler for login, logout, and session management

session_start();

header('Content-Type: application/json; charset=utf-8');

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

// LOGIN
if ($action === 'login') {
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$username || !$password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username dan password diperlukan']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, username, email, password, level, is_active FROM tbl_users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Username atau password salah']);
        exit;
    }

    if (!$user['is_active']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Akun Anda tidak aktif']);
        exit;
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['level'] = $user['level'];
    $_SESSION['logged_in'] = true;

    echo json_encode([
        'success' => true,
        'message' => 'Login berhasil',
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'level' => $user['level']
        ]
    ]);
    exit;
}

// LOGOUT
elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
    exit;
}

// CHECK SESSION
elseif ($action === 'check') {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        echo json_encode([
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'level' => $_SESSION['level']
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    }
    exit;
}

// REGISTER
elseif ($action === 'register') {
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;
    $confirm_password = $_POST['confirm_password'] ?? null;
    $level = $_POST['level'] ?? 'warga';

    if (!$username || !$email || !$password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username, email, dan password diperlukan']);
        exit;
    }

    // If confirm_password provided (from public register), check it matches
    if ($confirm_password && $password !== $confirm_password) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password tidak cocok']);
        exit;
    }

    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Password minimal 6 karakter']);
        exit;
    }

    // Check if username/email already exists
    $checkStmt = $pdo->prepare("SELECT id FROM tbl_users WHERE username = :username OR email = :email");
    $checkStmt->execute([':username' => $username, ':email' => $email]);
    if ($checkStmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Username atau email sudah terdaftar']);
        exit;
    }

    try {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $pdo->prepare("INSERT INTO tbl_users (username, email, password, level, is_active) VALUES (:username, :email, :password, :level, TRUE)");
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $hashedPassword,
            ':level' => $level
        ]);

        $userId = $pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'message' => 'User berhasil ditambahkan',
            'user_id' => $userId
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal membuat akun: ' . $e->getMessage()]);
    }
    exit;
}

// UPDATE USER (SuperAdmin only)
elseif ($action === 'update') {
    if ($_SESSION['level'] !== 'superadmin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $id = $_POST['id'] ?? null;
    $email = $_POST['email'] ?? null;
    $level = $_POST['level'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID diperlukan']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE tbl_users SET email = :email, level = :level, is_active = :status WHERE id = :id");
        $stmt->execute([
            ':email' => $email,
            ':level' => $level,
            ':status' => $status,
            ':id' => $id
        ]);

        echo json_encode(['success' => true, 'message' => 'User berhasil diupdate']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal update user: ' . $e->getMessage()]);
    }
    exit;
}

// BAN/UNBAN USER (SuperAdmin only)
elseif ($action === 'ban') {
    if ($_SESSION['level'] !== 'superadmin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$id || $status === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'User ID dan status diperlukan']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE tbl_users SET is_active = :status WHERE id = :id");
        $stmt->execute([
            ':status' => $status,
            ':id' => $id
        ]);

        $statusText = $status == 1 ? 'unbanned' : 'banned';
        echo json_encode(['success' => true, 'message' => 'User berhasil ' . $statusText]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal ban/unban user: ' . $e->getMessage()]);
    }
    exit;
}

else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Action tidak dikenali']);
    exit;
}

?>
