<?php
// Run explicitly from CLI. Existing accounts/passwords are never overwritten.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
if (isset($argv[1]) && !ctype_digit((string) $argv[1])) {
    fwrite(STDERR, "Usage: php scripts/seed_learning_area_accounts.php [region_id]\n");
    exit(1);
}
$region_id = isset($argv[1]) ? (int) $argv[1] : 12;
if ($region_id <= 0) {
    fwrite(STDERR, "The region ID must be a positive integer.\n");
    exit(1);
}
define('BASEPATH', true);
define('ENVIRONMENT', 'production');
require __DIR__ . '/../application/config/database.php';
require __DIR__ . '/../application/config/learning_area_dashboards.php';
$c = $db['default'];
$conn = new mysqli($c['hostname'], $c['username'], $c['password'], $c['database']);
$conn->set_charset('utf8mb4');
$schema = $conn->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'must_change_password'");
$schema->execute();
$schema->bind_result($has_password_change_column);
$schema->fetch();
$schema->close();
if (!$has_password_change_column) {
    $conn->query('ALTER TABLE users ADD must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER password');
}
$conn->begin_transaction();
try {
    foreach ($config['learning_area_dashboards'] as $slug => $area) {
        $username = 'la_' . str_replace('-', '_', $slug)
            . ($region_id === 12 ? '' : '_r' . $region_id);
        $role = 'la_' . $slug;
        $email = $username . '@example.test';
        $check = $conn->prepare('SELECT id, username, position, r_id FROM users WHERE username = ? OR email = ?');
        $check->bind_param('ss', $username, $email);
        $check->execute();
        $existing_accounts = $check->get_result();
        $check->close();
        $account_conflict = false;
        while ($existing = $existing_accounts->fetch_assoc()) {
            if ($existing['username'] === $username && $existing['position'] === $role && $existing['r_id'] === null) {
                $assign_region = $conn->prepare('UPDATE users SET r_id = ? WHERE id = ? AND r_id IS NULL');
                $assign_region->bind_param('ii', $region_id, $existing['id']);
                $assign_region->execute();
                $assign_region->close();
                echo "Assigned existing $username to region $region_id; password unchanged\n";
                $account_conflict = true;
                break;
            }
            echo "Skipped existing account for $username; no account data changed\n";
            $account_conflict = true;
            break;
        }
        if ($account_conflict) {
            continue;
        }
        $hash = password_hash('LearnArea@2026', PASSWORD_DEFAULT);
        $insert = $conn->prepare('INSERT INTO users (username, password, must_change_password, position, fname, lname, r_id, email, virified) VALUES (?, ?, 1, ?, ?, \'Coordinator\', ?, ?, 0)');
        $insert->bind_param('ssssis', $username, $hash, $role, $area['name'], $region_id, $email);
        $insert->execute();
        $insert->close();
        echo "Created $username for region $region_id\n";
    }
    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    $conn->close();
    throw $e;
}
$conn->close();
