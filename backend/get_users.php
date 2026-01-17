<?php
/**
 * API: Get Users (for Admin Management)
 * Fetches all users from tbl_users
 * Returns: JSON array of users
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

// Check if user is authenticated and is superadmin
if (!isset($_SESSION['logged_in']) || $_SESSION['level'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Database connection
$dbHost = '127.0.0.1';
$dbName = 'dbkampungku';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Set charset
    $pdo->exec("SET NAMES utf8mb4");

    // Query to fetch all users
    $query = "SELECT id, username, email, level, is_active as status, created_at FROM tbl_users ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => array_map(function($user) {
            return [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'level' => $user['level'],
                'status' => (int)$user['status'],
                'created_at' => $user['created_at']
            ];
        }, $users),
        'count' => count($users)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
