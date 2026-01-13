<?php
// backend/get_notifications.php
// Get user notifications for dashboard

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

    $notifications = [];

    // Get recent status updates for user's reports
    $stmt = $pdo->prepare("
        SELECT 'info' as type, 'Update Status' as title,
               CONCAT('Status laporan Anda berubah: ', s.nama_status) as message,
               r.created_at, 0 as is_read
        FROM tbl_riwayat r
        JOIN tbl_status s ON r.status_baru_id = s.id
        WHERE r.laporan_id IN (SELECT id FROM tbl_laporan WHERE user_id = ?)
        ORDER BY r.created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$userId]);
    $statusNotifications = $stmt->fetchAll();

    // Get resolved reports notifications
    $stmt = $pdo->prepare("
        SELECT 'success' as type, 'Laporan Selesai' as title,
               CONCAT('Laporan ', kategori, ' telah diselesaikan') as message,
               updated_at as created_at, 0 as is_read
        FROM tbl_laporan
        WHERE user_id = ? AND status_id = (SELECT id FROM tbl_status WHERE nama_status = 'Selesai')
        ORDER BY updated_at DESC
        LIMIT 2
    ");
    $stmt->execute([$userId]);
    $resolvedNotifications = $stmt->fetchAll();

    // Combine notifications
    $notifications = array_merge($statusNotifications, $resolvedNotifications);
    usort($notifications, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Limit to 5 most recent
    $notifications = array_slice($notifications, 0, 5);

    echo json_encode($notifications);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>