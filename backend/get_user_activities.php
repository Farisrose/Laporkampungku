<?php
// backend/get_user_activities.php
// Get user's recent activities for dashboard

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

    $activities = [];

    // Get recent reports created
    $stmt = $pdo->prepare("
        SELECT 'report_created' as type, judul as title, CONCAT('Laporan ', kategori, ' dibuat') as description,
               id as reference_id, CONCAT('LPR-', LPAD(id, 3, '0')) as reference, created_at
        FROM tbl_laporan
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 3
    ");
    $stmt->execute([$userId]);
    $reportActivities = $stmt->fetchAll();

    // Get status updates from history
    $stmt = $pdo->prepare("
        SELECT 'status_update' as type, 'Update Status' as title,
               CONCAT('Status laporan berubah menjadi ', s.nama_status) as description,
               r.laporan_id as reference_id, CONCAT('LPR-', LPAD(r.laporan_id, 3, '0')) as reference,
               r.created_at
        FROM tbl_riwayat r
        JOIN tbl_status s ON r.status_baru_id = s.id
        WHERE r.laporan_id IN (SELECT id FROM tbl_laporan WHERE user_id = ?)
        ORDER BY r.created_at DESC
        LIMIT 2
    ");
    $stmt->execute([$userId]);
    $statusActivities = $stmt->fetchAll();

    // Combine and sort activities
    $activities = array_merge($reportActivities, $statusActivities);
    usort($activities, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Limit to 5 most recent
    $activities = array_slice($activities, 0, 5);

    echo json_encode($activities);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>