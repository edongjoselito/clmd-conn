<?php
// Run: php -n tests/monitoring_tools_test.php
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
class TemporaryMonitoringDatabase extends CI_DB_mysqli_driver {
    public function query($sql, $binds = false, $return_object = null) {
        // Shadow real tables for every model query, including the migration.
        $sql = preg_replace('/^CREATE TABLE IF NOT EXISTS /', 'CREATE TEMPORARY TABLE IF NOT EXISTS ', $sql);
        $result = parent::query($sql, $binds, $return_object);
        if ($result === false) { throw new RuntimeException($this->error()['message']); }
        return $result;
    }
}
$testdb = new TemporaryMonitoringDatabase($config);
$testdb->initialize();
class CI_Model { public $db; }
require APPPATH . 'models/Monitoring_model.php';
$model = new Monitoring_model;
$model->db = $testdb;
// Start with the old indicator table to exercise migration of existing edited data.
$testdb->query("CREATE TEMPORARY TABLE monitoring_indicators (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, region_id INT UNSIGNED NOT NULL,
    area_index TINYINT UNSIGNED NOT NULL, subgroup VARCHAR(255) NOT NULL DEFAULT '',
    indicator TEXT NOT NULL, sort_order INT UNSIGNED NOT NULL DEFAULT 1,
    active TINYINT UNSIGNED NOT NULL DEFAULT 1, seed_key VARCHAR(32) DEFAULT NULL,
    UNIQUE KEY region_seed(region_id, seed_key)) ENGINE=InnoDB");
$testdb->insert('monitoring_indicators', array('region_id' => 1, 'area_index' => 0, 'indicator' => 'Existing customized indicator', 'seed_key' => '0:0', 'active' => 0));
$model->initialize(1);
$legacy = $model->tools(1)[0];
$items = $model->indicators(1, $legacy->id);
check(count($items) === 48, 'Import original indicators once');
check($items[0]->indicator === 'Existing customized indicator' && (int) $items[0]->active === 0, 'Preserve edits and inactive status during migration');
check((int) $items[0]->tool_id === (int) $legacy->id, 'Associate legacy indicators with original tool');
check($model->save_tool(1, 0, 'Second tool'), 'Create another tool');
$second = $model->tools(1)[1];
check(count($model->indicators(1, $second->id)) === 0, 'New tools start empty');
check($model->save_tool(1, $second->id, 'Renamed tool'), 'Rename tool');
check($model->tool(1, $second->id)->name === 'Renamed tool', 'Persist name');
check(!$model->save_tool(2, $second->id, 'Wrong region'), 'Reject cross-region rename');
check(!$model->delete_tool(2, $second->id), 'Reject cross-region removal');
check($model->save(1, $second->id, 0, array('area_index' => 0, 'indicator' => 'Second tool only')), 'Create associated indicator');
$item = $model->indicators(1, $second->id)[0];
check(count($model->indicators(1, $legacy->id)) === 48, 'Separate indicator lists');
check(!$model->save(1, $legacy->id, $item->id, array('indicator' => 'Wrong tool')), 'Reject cross-tool update');
check(!$model->delete_indicator(1, $legacy->id, $item->id), 'Reject cross-tool delete');
check(!$model->save(2, $second->id, $item->id, array('indicator' => 'Wrong region')), 'Reject cross-region indicator update');
check($model->save(1, $second->id, $item->id, array('indicator' => 'Edited')), 'Edit indicator');
check($model->indicators(1, $second->id)[0]->indicator === 'Edited', 'Persist indicator edit');
// Exercise assessment creation with a valid school fixture and real tool storage.
class CI_Controller {}
require APPPATH . 'controllers/Pages.php';
class SchoolFixture {
    public function select($value) { return $this; }
    public function from($value) { return $this; }
    public function join($table, $on) { return $this; }
    public function where($key, $value) { return $this; }
    public function get() { return $this; }
    public function row() { return (object) array('schoolID' => '00123', 'schoolName' => 'School', 'division_name' => 'Division', 'district_name' => 'District'); }
}
class MonitoringPagesFixture extends Pages {
    public $db;
    public $Monitoring_model;
    public function __construct() {}
}
$controller = new MonitoringPagesFixture;
$controller->db = new SchoolFixture;
$controller->Monitoring_model = $model;
$create = new ReflectionMethod('Pages', 'monitoring_new_assessment');
$create->setAccessible(true);
$selection = array('sy' => '2026-2027', 'division_id' => 1, 'district_id' => 1, 'school_id' => '00123', 'tool_id' => $second->id);
$assessment = $create->invoke($controller, 1, $selection);
check(count($assessment['items']) === 1 && $assessment['items'][0]['text'] === 'Edited', 'Assessment includes only selected tool indicators');
check($assessment['tool_name'] === 'Renamed tool' && $assessment['tool_id'] === (int) $second->id, 'Assessment snapshots tool identity');
check(!$create->invoke($controller, 2, $selection), 'Assessment rejects another region tool');
unset($selection['tool_id']);
check(!$create->invoke($controller, 1, $selection), 'Assessment requires tool selection');
check($model->delete_indicator(1, $legacy->id, $items[0]->id), 'Delete seeded indicator');
$model->initialize(1);
check(count($model->indicators(1, $legacy->id)) === 47, 'Deleted seed does not reappear');
check($model->delete_indicator(1, $second->id, $item->id), 'Delete custom indicator');
check(count($model->indicators(1, $second->id)) === 0, 'Custom indicator absent');
check($model->delete_tool(1, $legacy->id), 'Remove original tool');
$model->initialize(1);
check(count($model->tools(1)) === 1 && !$model->tool(1, $legacy->id), 'Removed original tool does not reappear');
check(!$model->save(1, $legacy->id, 0, array('area_index' => 0, 'indicator' => 'Invalid')), 'Reject indicators for removed tools');
$model->initialize(2);
check(count($model->tools(2)) === 1 && count($model->tools(1)) === 1, 'Independent region initialization');
echo "PASS: migration, preserved legacy edits, tool CRUD, indicator CRUD, tool/region isolation, assessment selection, and durable deletions.\n";
