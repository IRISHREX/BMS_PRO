<?php
define('BASEPATH', 'dummy');
define('ENVIRONMENT', 'development');
require_once __DIR__ . '/../application/config/database.php';

$db_cfg = $db['default'];
$conn = new mysqli($db_cfg['hostname'], $db_cfg['username'], $db_cfg['password'], $db_cfg['database'], $db_cfg['port'] ?? 3306);

if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error . "\n";
    exit;
}

echo "=== referral_payment COLUMNS ===\n";
$res = $conn->query("SHOW COLUMNS FROM referral_payment");
while ($row = $res->fetch_assoc()) {
    echo "{$row['Field']} ({$row['Type']})\n";
}

echo "\n=== referral_person COLUMNS ===\n";
$res = $conn->query("SHOW COLUMNS FROM referral_person");
while ($row = $res->fetch_assoc()) {
    echo "{$row['Field']} ({$row['Type']})\n";
}

echo "\n=== referral_type DATA ===\n";
$res = $conn->query("SELECT * FROM referral_type");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\n=== SAMPLE referral_payment DATA ===\n";
$res = $conn->query("SELECT * FROM referral_payment LIMIT 3");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
