<?php
// Run: php tests/regional_user_scope_test.php
// Uses connection-local temporary MySQL tables; application records are untouched.
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('ENVIRONMENT', 'testing');
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function log_message($level, $message) {}
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
require BASEPATH . 'database/DB.php';
require APPPATH . 'config/database.php';
$config = $db['default'];
$config['db_debug'] = false;
$connection = DB($config);
class TemporaryFocalDatabase extends CI_DB_mysqli_driver {
    public function query($sql, $binds = false, $return_object = null) {
        // Shadow real tables for every model query, including the migration.
        $sql = preg_replace('/^CREATE TABLE IF NOT EXISTS /', 'CREATE TEMPORARY TABLE IF NOT EXISTS ', $sql);
        $result = parent::query($sql, $binds, $return_object);
        if ($result === false) { throw new RuntimeException($this->error()['message']); }
        return $result;
    }
}
$testdb = new TemporaryFocalDatabase($config);
$testdb->initialize();
require APPPATH . 'libraries/Regional_user_scope.php';
$scope = new Regional_user_scope;
$testdb->query('CREATE TEMPORARY TABLE division (id INT PRIMARY KEY, region_id INT)');
$testdb->query('CREATE TEMPORARY TABLE users (id INT PRIMARY KEY, r_id INT NULL, p_id INT NULL, position VARCHAR(40))');
$testdb->query('INSERT INTO division VALUES (1,1),(2,2)');
$testdb->query("INSERT INTO users VALUES (1,1,1,'division'),(2,2,2,'division'),(3,1,NULL,'region'),(4,1,1,'admin'),(5,1,2,'school'),(6,NULL,1,'district'),(7,NULL,NULL,'la_filipino')");
$scope->apply($testdb, 1);
$ids = array_map('intval', array_column($testdb->order_by('id')->get('users')->result_array(), 'id'));
check($ids === array(1,3,6), 'Include all regional levels, exclude admins, other divisions and unassigned accounts');
$scope->apply($testdb, 0);
check($testdb->count_all_results('users') === 0, 'Missing region fails closed');
echo "Regional user list scope checks passed.\n";
