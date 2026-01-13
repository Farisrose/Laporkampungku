<?php
// backend/get_user_stats.php
// Get user statistics for dashboard

session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

$dbHost = '127.0.0.1';
$dbName = 'dbkampungku';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Get total reports
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_laporan WHERE user_id = ?");
    $stmt->execute([$userId]);
    $totalReports = $stmt->fetch()['total'];

    // Get resolved reports
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_laporan WHERE user_id = ? AND status_id = (SELECT id FROM tbl_status WHERE nama_status = 'Selesai')");
    $stmt->execute([$userId]);
    $resolvedReports = $stmt->fetch()['total'];

    // Get pending reports
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_laporan WHERE user_id = ? AND status_id = (SELECT id FROM tbl_status WHERE nama_status = 'Diproses')");
    $stmt->execute([$userId]);
    $pendingReports = $stmt->fetch()['total'];

    // Calculate points (simplified - you can implement a proper points system)
    $points = $totalReports * 10 + $resolvedReports * 50;

    echo json_encode([
        'total_reports' => $totalReports,
        'resolved_reports' => $resolvedReports,
        'pending_reports' => $pendingReports,
        'points' => $points
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>