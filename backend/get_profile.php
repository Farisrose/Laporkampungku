<?php
// backend/get_profile.php
// Get user profile data based on user level

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];
$userLevel = $_SESSION['level'];

$dbHost = '127.0.0.1';
$dbName = 'dbkampungku';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $profileData = [];

    // Get profile data based on user level
    if ($userLevel === 'superadmin') {
        $stmt = $pdo->prepare("SELECT nama_lengkap, foto_profil FROM tbl_superadmin WHERE user_id = ?");
        $stmt->execute([$userId]);
        $profileData = $stmt->fetch();
    } elseif ($userLevel === 'admin') {
        $stmt = $pdo->prepare("SELECT nama_lengkap, foto_profil FROM tbl_admin WHERE user_id = ?");
        $stmt->execute([$userId]);
        $profileData = $stmt->fetch();
    } elseif ($userLevel === 'anggota') {
        $stmt = $pdo->prepare("SELECT nama_lengkap, foto_profil FROM tbl_anggota WHERE user_id = ?");
        $stmt->execute([$userId]);
        $profileData = $stmt->fetch();
    }

    echo json_encode($profileData ?: []);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>