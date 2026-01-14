<?php
// setup_database.php
// Execute database setup and insert test data

$host = 'localhost';
$db = 'dbkampungku';
$user = 'root';
$pass = '';

try {
    // Create database if not exists
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` DEFAULT CHARACTER SET = utf8mb4 DEFAULT COLLATE = utf8mb4_unicode_ci");
    echo "✓ Database '$db' created/verified\n";

    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Read and execute SQL file
    $sql = file_get_contents('database.sql');

    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "✗ Error executing statement: " . $e->getMessage() . "\n";
                echo "Statement: " . substr($statement, 0, 100) . "...\n";
            }
        }
    }

    echo "\n✓ Database setup completed successfully!\n";

    // Verify data
    echo "\n--- Verification ---\n";

    $tables = ['tbl_users', 'tbl_laporan', 'tbl_foto_laporan', 'tbl_status'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
        $count = $stmt->fetch()['count'];
        echo "Table $table: $count records\n";
    }

    // Show sample user reports
    echo "\n--- Sample User Reports ---\n";
    $stmt = $pdo->query("
        SELECT u.username, l.judul, l.kategori, s.nama_status, l.created_at
        FROM tbl_laporan l
        JOIN tbl_users u ON l.user_id = u.id
        JOIN tbl_status s ON l.status_id = s.id
        ORDER BY l.created_at DESC
        LIMIT 5
    ");
    while ($row = $stmt->fetch()) {
        echo "User: {$row['username']}, Title: {$row['judul']}, Status: {$row['nama_status']}\n";
    }

} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
}
?>