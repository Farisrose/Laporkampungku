<?php
// backend/get_user_reports.php
// Get user's recent reports for dashboard

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

    // Get recent reports (limit 5 for dashboard)
    $stmt = $pdo->prepare("
        SELECT l.id, l.judul, l.kategori, l.alamat, l.created_at,
               s.nama_status as status,
               COALESCE(f.file_path, '') as foto
        FROM tbl_laporan l
        LEFT JOIN tbl_status s ON l.status_id = s.id
        LEFT JOIN tbl_foto_laporan f ON l.id = f.laporan_id AND f.id = (
            SELECT MIN(id) FROM tbl_foto_laporan WHERE laporan_id = l.id
        )
        WHERE l.user_id = ?
        ORDER BY l.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$userId]);
    $reports = $stmt->fetchAll();

    // Add progress for reports in process
    foreach ($reports as &$report) {
        if ($report['status'] === 'Diproses') {
            // Simulate progress - in real app, this would come from database
            $report['progress'] = rand(10, 90);
        }
    }

    echo json_encode($reports);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>