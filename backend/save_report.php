<?php
// backend/save_report.php
// Receives multipart/form-data to save a report and uploaded photos

session_start();

header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$userId = $_SESSION['user_id'];

// Basic config - adjust as needed for your environment
$dbHost = '127.0.0.1';
$dbName = 'dbkampungku';
$dbUser = 'root';
$dbPass = '';
$uploadDir = __DIR__ . '/../public/uploads';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'error' => $e->getMessage()]);
    exit;
}

// Validate required fields
$kategori = $_POST['category'] ?? null;
$deskripsi = $_POST['description'] ?? null;
$latitude = $_POST['latitude'] ?? null;
$longitude = $_POST['longitude'] ?? null;
$alamat = $_POST['address'] ?? null;
$prioritas = $_POST['priority'] ?? 'Sedang';
$judul = $_POST['judul'] ?? null;

if (!$kategori || !$latitude || !$longitude || !$alamat) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Auto-generate judul if not provided or empty
if (empty($judul)) {
    // Create title from category and first part of description or address
    if (!empty($deskripsi)) {
        // Take first 50 characters of description
        $judul = $kategori . ' - ' . substr($deskripsi, 0, 50);
        if (strlen($deskripsi) > 50) {
            $judul .= '...';
        }
    } else {
        // Use category with address
        $judul = $kategori . ' di ' . substr($alamat, 0, 40);
        if (strlen($alamat) > 40) {
            $judul .= '...';
        }
    }
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO tbl_laporan (user_id, kategori, judul, deskripsi, latitude, longitude, alamat, prioritas) VALUES (:user_id, :kategori, :judul, :deskripsi, :latitude, :longitude, :alamat, :prioritas)");
    $stmt->execute([
        ':user_id' => $userId,
        ':kategori' => $kategori,
        ':judul' => $judul,
        ':deskripsi' => $deskripsi,
        ':latitude' => $latitude,
        ':longitude' => $longitude,
        ':alamat' => $alamat,
        ':prioritas' => $prioritas
    ]);

    $laporanId = $pdo->lastInsertId();

    // Handle uploaded files (photos)
    $savedFiles = [];
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (!empty($_FILES['photos'])) {
        // If multiple files, structure is arrays
        $files = $_FILES['photos'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            $originalName = basename($files['name'][$i]);
            $ext = pathinfo($originalName, PATHINFO_EXTENSION);
            $safeName = bin2hex(random_bytes(8)) . '.' . $ext;
            $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

            if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                $relativePath = 'public/uploads/' . $safeName;
                $stmtFile = $pdo->prepare("INSERT INTO tbl_foto_laporan (laporan_id, file_path) VALUES (:laporan_id, :file_path)");
                $stmtFile->execute([':laporan_id' => $laporanId, ':file_path' => $relativePath]);
                $savedFiles[] = $relativePath;
            }
        }
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'laporan_id' => $laporanId, 'files' => $savedFiles]);
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save report', 'error' => $e->getMessage()]);
    exit;
}

?>
