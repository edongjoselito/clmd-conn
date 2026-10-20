<?php
// Run: php tests/learning_area_scope_test.php
// Uses a connection-local temporary table; application records are untouched.
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('ENVIRONMENT', 'testing');
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function log_message($level, $message) {}
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
require BASEPATH . 'database/DB.php';
require APPPATH . 'config/database.php';
$database_config = $db['default'];
$database_config['db_debug'] = false;
$connection = DB($database_config);

class CI_Model {
    public $db;
    public $session;
    public $config;
}

class LearningAreaTestConfig {
    private $areas;
    public function __construct($areas) { $this->areas = $areas; }
    public function load($name) {}
    public function item($name) { return $this->areas; }
}

require APPPATH . 'models/Page_model.php';
require APPPATH . 'config/learning_area_dashboards.php';
$model = (new ReflectionClass('Page_model'))->newInstanceWithoutConstructor();
$model->db = $connection;
$model->session = (object) array('position' => 'la_filipino', 'region' => 12, 'division' => 0);
$model->config = new LearningAreaTestConfig($config['learning_area_dashboards']);

$connection->query('CREATE TEMPORARY TABLE learning_gap_records (id INT PRIMARY KEY, region_id INT, division_id INT, learning_area VARCHAR(150))');
$connection->query("INSERT INTO learning_gap_records VALUES
    (1,12,61,'Filipino'),(2,12,62,'Filipino'),(3,12,61,'English'),(4,13,61,'Filipino')");

$apply_scope = new ReflectionMethod('Page_model', 'apply_learning_gap_scope');
$apply_scope->setAccessible(true);
$connection->select('*')->from('learning_gap_records');
$apply_scope->invoke($model, array('type' => 'region', 'id' => 12), '');
$records = $connection->order_by('id')->get()->result();
$ids = array_map(function ($record) { return (int) $record->id; }, $records);
check($ids === array(1, 2), 'Regional learning-area users see matching records across all divisions in their region');

$model->session->division = 61;
$connection->select('*')->from('learning_gap_records');
$apply_scope->invoke($model, array('type' => 'region', 'id' => 12), '');
$records = $connection->order_by('id')->get()->result();
$ids = array_map(function ($record) { return (int) $record->id; }, $records);
check($ids === array(1), 'Division learning-area users see matching records only in their assigned division and region');

echo "Learning area division and regional scope checks passed.\n";
