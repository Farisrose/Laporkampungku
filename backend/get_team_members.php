<?php
/**
 * API: Get Team Members
 * Fetches development team members from tbl_anggota
 * Returns: JSON array of team members
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbkampungku";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

try {
    // Set charset to UTF-8
    $conn->set_charset("utf8mb4");

    // Query to fetch team members
    $query = "
        SELECT 
            a.id,
            a.nama_lengkap,
            a.peran,
            a.foto_profil,
            a.email_pribadi,
            a.no_telp,
            a.nim_or_id,
            a.created_at
        FROM tbl_anggota a
        WHERE a.id IS NOT NULL
        ORDER BY a.created_at ASC
    ";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query error: " . $conn->error);
    }

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = [
            'id' => $row['id'],
            'nama_lengkap' => $row['nama_lengkap'],
            'peran' => $row['peran'],
            'foto_profil' => $row['foto_profil'],
            'email_pribadi' => $row['email_pribadi'],
            'no_telp' => $row['no_telp'],
            'nim_or_id' => $row['nim_or_id'],
            'created_at' => $row['created_at']
        ];
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $members,
        'count' => count($members)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    $conn->close();
}
?>
