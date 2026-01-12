<?php
// Setup test users for development

$host = 'localhost';
$db = 'dbkampungku';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Clear existing test users (optional)
    // $pdo->exec("DELETE FROM tbl_users WHERE username IN ('admin', 'testuser', 'testanggota', 'testwarga')");

    // Insert test admin user
    $stmt = $pdo->prepare("INSERT IGNORE INTO tbl_users (username, password, email, level, is_active) VALUES (?, ?, ?, ?, ?)");
    
    $users = [
        ['admin', password_hash('admin123', PASSWORD_BCRYPT), 'admin@laporkampungku.com', 'admin', 1],
        ['testuser', password_hash('test123', PASSWORD_BCRYPT), 'testuser@laporkampungku.com', 'superadmin', 1],
        ['anggota', password_hash('anggota123', PASSWORD_BCRYPT), 'anggota@laporkampungku.com', 'anggota', 1],
        ['warga', password_hash('warga123', PASSWORD_BCRYPT), 'warga@laporkampungku.com', 'warga', 1],
    ];

    foreach ($users as $userData) {
        try {
            $stmt->execute($userData);
            echo "✓ User '{$userData[0]}' created/updated\n";
        } catch (PDOException $e) {
            echo "✗ User '{$userData[0]}' already exists or error: " . $e->getMessage() . "\n";
        }
    }

    // Display all users
    echo "\n--- Current Users ---\n";
    $result = $pdo->query("SELECT id, username, email, level, is_active FROM tbl_users");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, Username: {$row['username']}, Email: {$row['email']}, Level: {$row['level']}, Active: {$row['is_active']}\n";
    }

} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>
