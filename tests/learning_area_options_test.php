<?php
// Run: php tests/learning_area_options_test.php
// Temporary tables shadow application tables without modifying stored records.
define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('ENVIRONMENT', 'testing');
function is_php($version) { return version_compare(PHP_VERSION, $version, '>='); }
function log_message($level, $message) {}
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
require BASEPATH . 'database/DB.php';
require APPPATH . 'config/database.php';
$connection = DB($db['default']);
class CI_Model { public $db; }
require APPPATH . 'models/Page_model.php';
$model = (new ReflectionClass('Page_model'))->newInstanceWithoutConstructor();
$model->db = $connection;
$connection->query('CREATE TEMPORARY TABLE division (id INT PRIMARY KEY, region_id INT)');
$connection->query('CREATE TEMPORARY TABLE learning_area_settings (division_id INT, grade_level VARCHAR(50), learning_area VARCHAR(150))');
$connection->query('INSERT INTO division VALUES (1,12),(2,12),(3,13)');
$connection->query("INSERT INTO learning_area_settings VALUES
    (1,'Grade 9','Araling Panlipunan'),
    (2,'Grade 9','Araling Panlipunan'),
    (2,'Grade 9','English'),
    (2,'Grade 7','Mathematics'),
    (3,'Grade 9','Science')");
check($model->learning_area_options('Grade 9') === array('Araling Panlipunan', 'English', 'Science'),
    'Include subjects across divisions and regions, deduplicate and sort them, and exclude other grades');
check($model->learning_area_options('Grade 7') === array('Mathematics'), 'Only include the selected grade');
check($model->learning_area_options('Grade 10') === array(), 'Unconfigured grade has no subjects');
check($model->learning_area_options('') === array(), 'Empty grade has no subjects');
echo "Learning area options checks passed.\n";
