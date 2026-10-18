<?php
// Run explicitly from CLI. Existing accounts/passwords are never overwritten.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
define('BASEPATH', true);
define('ENVIRONMENT', 'production');
require __DIR__ . '/../application/config/database.php';
require __DIR__ . '/../application/config/learning_area_dashboards.php';
$c = $db['default'];
$conn = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
$conn->set_charset('utf8mb4');
$conn->begin_transaction();
try {
    foreach ($config['learning_area_dashboards'] as $slug => $area) {
        $username = 'la_' . str_replace('-', '_', $slug);
        $check = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $check->bind_param('s', $username); $check->execute();
        if ($check->get_result()->num_rows) { echo "Skipped existing $username\n"; continue; }
        $hash = password_hash('LearnArea@2026', PASSWORD_DEFAULT);
        $role = 'la_' . $slug;
        $email = $username . '@example.test';
        $insert = $conn->prepare('INSERT INTO users (username, password, position, fname, lname, email, virified, must_change_password) VALUES (?, ?, ?, ?, \'Coordinator\', ?, 0, 0)');
        $insert->bind_param('sssss', $username, $hash, $role, $area['name'], $email);
        $insert->execute();
        echo "Created $username\n";
    }
    $conn->commit();
} catch (Throwable $e) { $conn->rollback(); throw $e; }
