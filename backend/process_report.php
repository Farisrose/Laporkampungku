<?php
// backend/process_report.php
// Process report (verify/approve or reject) with status update and audit trail

session_start();

header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in and is admin/superadmin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$userLevel = $_SESSION['level'] ?? null;
if (!in_array($userLevel, ['admin', 'superadmin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Get action (verify, reject, etc)
$action = $_POST['action'] ?? null;
$reportId = intval($_POST['report_id'] ?? 0);
$newStatus = $_POST['status'] ?? null; // e.g., 'Diproses', 'Selesai', 'Ditolak'
$catatan = $_POST['catatan'] ?? '';

if (!$action || !$reportId || !$newStatus) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

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

try {
    $pdo->beginTransaction();

    // Get current report status
    $stmtReport = $pdo->prepare("SELECT status_id FROM tbl_laporan WHERE id = :id");
    $stmtReport->execute([':id' => $reportId]);
    $currentReport = $stmtReport->fetch();

    if (!$currentReport) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Report not found']);
        exit;
    }

    $oldStatusId = $currentReport['status_id'];

    // Get new status ID
    $stmtStatus = $pdo->prepare("SELECT id FROM tbl_status WHERE nama_status = :nama_status");
    $stmtStatus->execute([':nama_status' => $newStatus]);
    $statusData = $stmtStatus->fetch();

    if (!$statusData) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    $newStatusId = $statusData['id'];

    // Update report status
    $stmtUpdate = $pdo->prepare("UPDATE tbl_laporan SET status_id = :status_id, updated_at = NOW() WHERE id = :id");
    $stmtUpdate->execute([
        ':status_id' => $newStatusId,
        ':id' => $reportId
    ]);

    // Record in history/audit trail
    $stmtHistory = $pdo->prepare("INSERT INTO tbl_riwayat (laporan_id, status_lama_id, status_baru_id, admin_id, catatan, created_at) 
        VALUES (:laporan_id, :status_lama_id, :status_baru_id, :admin_id, :catatan, NOW())");
    
    // Get admin ID from user if exists
    $stmtAdmin = $pdo->prepare("SELECT id FROM tbl_admin WHERE user_id = :user_id");
    $stmtAdmin->execute([':user_id' => $_SESSION['user_id']]);
    $adminData = $stmtAdmin->fetch();
    $adminId = $adminData ? $adminData['id'] : null;

    $stmtHistory->execute([
        ':laporan_id' => $reportId,
        ':status_lama_id' => $oldStatusId,
        ':status_baru_id' => $newStatusId,
        ':admin_id' => $adminId,
        ':catatan' => $catatan
    ]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Report status updated successfully',
        'report_id' => $reportId,
        'new_status' => $newStatus,
        'old_status_id' => $oldStatusId,
        'new_status_id' => $newStatusId
    ]);
    exit;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error processing report', 'error' => $e->getMessage()]);
    exit;
}
?>
