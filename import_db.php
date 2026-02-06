<?php
// Protected import script - only accessible with secret key
// Usage: import_db.php?key=smartorder2026
if (!isset($_GET['key']) || $_GET['key'] !== 'smartorder2026') {
    http_response_code(403);
    die("Access denied.");
}

$dbHost = getenv('MYSQLHOST') ?: "localhost";
$dbName = getenv('MYSQLDATABASE') ?: "practice";
$user = getenv('MYSQLUSER') ?: "root";
$password = getenv('MYSQLPASSWORD') ?: "";
$dbPort = getenv('MYSQLPORT') ?: "3306";

try {
    $pdo = new PDO(
        'mysql:host='.$dbHost.';port='.$dbPort.';dbname='.$dbName,
        $user, $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "Connected to database successfully.<br>";

    $sql = file_get_contents(__DIR__ . '/database_utf8.sql');
    if (!$sql) {
        die("Could not read database_utf8.sql");
    }

    // Remove UTF-8 BOM
    $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);
    // Remove MySQL conditional comments /*!...*/
    $sql = preg_replace('/\/\*!\d+.*?\*\//', '', $sql);
    // Remove SQL comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    // Remove LOCK/UNLOCK TABLES
    $sql = preg_replace('/LOCK TABLES.*?;/i', '', $sql);
    $sql = preg_replace('/UNLOCK TABLES.*?;/i', '', $sql);

    // Split into statements and execute
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($s) { return !empty($s); }
    );

    $count = 0;
    foreach ($statements as $stmt) {
        $pdo->exec($stmt);
        $count++;
    }
    echo "Database imported successfully! ($count statements executed)<br>";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Failed statement: " . htmlspecialchars(substr($stmt, 0, 200));
}
