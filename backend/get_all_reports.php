<?php
// backend/get_all_reports.php
// Fetch all reports for admin verification with filtering and search

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

// Get filter parameters
$status = $_GET['status'] ?? null;          // Filter by status
$kategori = $_GET['kategori'] ?? null;      // Filter by category
$prioritas = $_GET['prioritas'] ?? null;    // Filter by priority
$search = $_GET['search'] ?? null;          // Search by title or description
$limit = intval($_GET['limit'] ?? 20);      // Pagination limit
$offset = intval($_GET['offset'] ?? 0);     // Pagination offset
$sort = $_GET['sort'] ?? 'created_at';      // Sort field (created_at, prioritas, status)
$order = strtoupper($_GET['order'] ?? 'DESC'); // ASC or DESC

// Validate sort field to prevent SQL injection
$allowedSortFields = ['created_at', 'prioritas', 'kategori', 'judul'];
if (!in_array($sort, $allowedSortFields)) {
    $sort = 'created_at';
}

// Validate order
if (!in_array($order, ['ASC', 'DESC'])) {
    $order = 'DESC';
}

try {
    // Base query with joins to get full information
    $query = "SELECT 
        l.id,
        l.user_id,
        l.kategori,
        l.judul,
        l.deskripsi,
        l.latitude,
        l.longitude,
        l.alamat,
        l.prioritas,
        l.status_id,
        l.created_at,
        l.updated_at,
        s.nama_status,
        s.warna,
        u.username,
        u.email,
        (SELECT COUNT(*) FROM tbl_foto_laporan WHERE laporan_id = l.id) as foto_count
    FROM tbl_laporan l
    LEFT JOIN tbl_status s ON l.status_id = s.id
    LEFT JOIN tbl_users u ON l.user_id = u.id
    WHERE 1=1";
    
    $params = [];

    // Apply filters
    if ($status) {
        $query .= " AND s.nama_status = :status";
        $params[':status'] = $status;
    }

    if ($kategori) {
        $query .= " AND l.kategori = :kategori";
        $params[':kategori'] = $kategori;
    }

    if ($prioritas) {
        $query .= " AND l.prioritas = :prioritas";
        $params[':prioritas'] = $prioritas;
    }

    if ($search) {
        $query .= " AND (l.judul LIKE :search OR l.deskripsi LIKE :search OR l.alamat LIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }

    // Add sorting
    $query .= " ORDER BY l.$sort $order";

    // Get total count before pagination
    $countQuery = "SELECT COUNT(*) as total FROM tbl_laporan l
        LEFT JOIN tbl_status s ON l.status_id = s.id
        LEFT JOIN tbl_users u ON l.user_id = u.id
        WHERE 1=1";
    
    if ($status) {
        $countQuery .= " AND s.nama_status = :status";
    }
    if ($kategori) {
        $countQuery .= " AND l.kategori = :kategori";
    }
    if ($prioritas) {
        $countQuery .= " AND l.prioritas = :prioritas";
    }
    if ($search) {
        $countQuery .= " AND (l.judul LIKE :search OR l.deskripsi LIKE :search OR l.alamat LIKE :search)";
    }

    $stmtCount = $pdo->prepare($countQuery);
    $stmtCount->execute($params);
    $totalCount = $stmtCount->fetch()['total'];

    // Add pagination
    $query .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;

    $stmt = $pdo->prepare($query);
    
    // Bind all parameters except limit and offset (which must be int)
    foreach ($params as $key => $value) {
        if ($key !== ':limit' && $key !== ':offset') {
            $stmt->bindValue($key, $value);
        } else {
            $stmt->bindValue($key, $value, PDO::PARAM_INT);
        }
    }
    
    $stmt->execute();
    $reports = $stmt->fetchAll();

    // Get photos for each report
    foreach ($reports as &$report) {
        $photoStmt = $pdo->prepare("SELECT id, file_path FROM tbl_foto_laporan WHERE laporan_id = :laporan_id ORDER BY created_at ASC");
        $photoStmt->execute([':laporan_id' => $report['id']]);
        $report['fotos'] = $photoStmt->fetchAll();
    }

    echo json_encode([
        'success' => true,
        'data' => $reports,
        'pagination' => [
            'total' => $totalCount,
            'limit' => $limit,
            'offset' => $offset,
            'page' => intval($offset / $limit) + 1,
            'totalPages' => ceil($totalCount / $limit)
        ]
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching reports', 'error' => $e->getMessage()]);
    exit;
}
?>
