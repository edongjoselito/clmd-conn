<?php
// Run: php tests/focal_persons_test.php
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
class CI_Model { public $db; }
require APPPATH . 'models/Focal_person_model.php';
$model = new Focal_person_model;
$model->db = $testdb;
$model->initialize();
$testdb->query('CREATE TEMPORARY TABLE division (id INT PRIMARY KEY, region_id INT, description VARCHAR(150))');
$testdb->insert_batch('division', array(array('id' => 1, 'region_id' => 1, 'description' => 'One'), array('id' => 2, 'region_id' => 2, 'description' => 'Two')));
$data = array('name' => 'Test Focal', 'position' => 'Coordinator', 'division_id' => 1, 'picture' => 'test-picture', 'learning_area' => 'filipino');
check($model->save(1, 0, $data), 'Create focal person');
$id = $testdb->insert_id();
check(count($model->directory(1)) === 1, 'Regional directory');
check(count($model->directory(2)) === 0, 'Regional isolation');
check(count($model->directory()) === 1, 'Learning-area directory');
check(count($model->directory(null, 'filipino', 1)) === 1, 'Match subject and division together');
check(count($model->directory(null, 'english', 1)) === 0, 'Exclude other subjects in same division');
check(count($model->directory(null, 'filipino', 2)) === 0, 'Exclude other divisions');
check(count($model->directory(2, 'filipino')) === 0, 'Region and subject filters compose');
$data['name'] = 'Updated Focal';
unset($data['picture']);
check($model->save(1, $id, $data), 'Update focal person');
check($model->person(1, $id)->picture === 'test-picture', 'Preserve picture when not replaced');
check(!$model->save(2, $id, $data), 'Reject cross-region update');
$data['division_id'] = 2;
check(!$model->save(1, $id, $data), 'Reject division from another region');
check(!$model->delete(2, $id), 'Reject cross-region delete');
check($model->delete(1, $id), 'Delete focal person');
check(count($model->directory()) === 0, 'Deleted record absent');
echo "Focal person CRUD and regional scope checks passed.\n";
