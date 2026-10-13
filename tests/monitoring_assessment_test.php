<?php
// Run: php -n tests/monitoring_assessment_test.php
// Isolated checks: no application database or authenticated session required.
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__) . '/application/');
define('FCPATH', dirname(__DIR__) . '/');
class CI_Model { public $db; }
require APPPATH . 'models/Monitoring_model.php';
require APPPATH . 'libraries/Monitoring_summary.php';
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
class MemoryResult {
    private $rows;
    public function __construct($rows) { $this->rows = array_values($rows); }
    public function row() { return isset($this->rows[0]) ? (object) $this->rows[0] : null; }
    public function result() { return array_map(function ($row) { return (object) $row; }, $this->rows); }
}
class MemoryDatabase {
    public $rows = array(); private $filters = array(); private $affected = 0; private $last = 0;
    public function where($key, $value = null) { $this->filters += is_array($key) ? $key : array($key => $value); return $this; }
    public function order_by($key, $direction = '') { return $this; }
    private function matches($row) { foreach ($this->filters as $key => $value) { if ($row[$key] != $value) return false; } return true; }
    public function get($table) { $rows = array_filter($this->rows, array($this, 'matches')); $this->filters = array(); return new MemoryResult($rows); }
    public function insert($table, $data) { $data += array('id' => ++$this->last, 'version' => 1); $this->rows[$this->last] = $data; return true; }
    public function insert_id() { return $this->last; }
    public function update($table, $data) { $this->affected = 0; foreach ($this->rows as &$row) { if ($this->matches($row)) { $row = array_merge($row, $data); $this->affected++; } } unset($row); $this->filters = array(); return true; }
    public function delete($table) { $this->affected = 0; foreach ($this->rows as $id => $row) { if ($this->matches($row)) { unset($this->rows[$id]); $this->affected++; } } $this->filters = array(); return true; }
    public function affected_rows() { return $this->affected; }
}
$model = new Monitoring_model; $model->db = new MemoryDatabase;
$groups = $model->defaults(); $items = array(); $id = 0;
foreach ($groups as $area => $group) foreach ($group['indicators'] as $item) {
    $items[] = array('id' => ++$id, 'area' => $area, 'text' => $item['text'], 'subgroup' => $item['subgroup'], 'rating' => 'evident', 'remarks' => 'Evidence checked.');
}
$payload = array('sy' => '2026-2027', 'school_id' => '00123', 'school_name' => 'Example School', 'division_id' => 1, 'district_id' => 2,
    'division_name' => 'Division', 'district_name' => 'District', 'groups' => $groups, 'items' => $items, 'details' => array(), 'comments' => array());
$id = $model->save_assessment(1, 0, 0, $payload);
check($id === 1, 'Create assessment');
check($model->assessment(2, $id) === null, 'Region isolation for read');
check(count($model->assessments(1)) === 1 && count($model->assessments(2)) === 0, 'Region isolation for list');
$stored = json_decode($model->assessment(1, $id)->payload, true);
check($stored === $payload, 'Snapshot and leading-zero school ID round trip');
$payload['items'][0]['rating'] = 'not_evident';
check($model->save_assessment(2, $id, 1, $payload) === false, 'Cross-region update rejected');
check($model->save_assessment(1, $id, 1, $payload) === 1, 'Update assessment');
check($model->save_assessment(1, $id, 1, $stored) === false, 'Stale update rejected');
check(!$model->delete_assessment(2, $id, 2), 'Cross-region delete rejected');
check(!$model->delete_assessment(1, $id, 1), 'Stale delete rejected');
check($model->delete_assessment(1, $id, 2), 'Delete assessment');
check($model->assessment(1, $id) === null, 'Deleted record absent');
foreach (array(0 => 'Needs Improvement', 49 => 'Needs Improvement', 50 => 'Not defined in source scale', 51 => 'Fairly Meets Expectation', 62 => 'Fairly Meets Expectation', 63 => 'Satisfactorily Meets Expectation', 75 => 'Satisfactorily Meets Expectation', 76 => 'Very Satisfactory', 88 => 'Very Satisfactory', 89 => 'Outstanding', 100 => 'Outstanding') as $score => $description) {
    $rated = array(); for ($i = 0; $i < 100; $i++) $rated[] = array('rating' => $i < $score ? 'evident' : 'not_evident');
    check(Monitoring_summary::calculate($rated)['description'] === $description, 'Rating boundary ' . $score);
}
check(Monitoring_summary::calculate(array(array('rating' => '')))['description'] === 'Incomplete', 'Unanswered is not Not Evident');
check(Monitoring_summary::calculate(array())['description'] === 'Not assessed', 'Empty area');
check(Monitoring_summary::calculate(array(array('rating' => ''), array('rating' => 'evident')))['score'] === '50.00%', 'Provisional percentage uses all indicators');
function base_url($path = '') { return '/clmd-conn/' . $path; }
function html_escape($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function form_open($path, $attributes = array()) { return '<form method="post">'; }
function form_close() { return '</form>'; }
class RenderSession { public function flashdata($key) { return null; } }
class RenderAssessment {
    public $session;
    public function render($assessment, $print_mode) {
        $this->session = new RenderSession; $error = ''; $record = (object) array('id' => 1, 'version' => 1);
        ob_start(); include APPPATH . 'views/pages/monitoring_assessment.php'; return ob_get_clean();
    }
}
$payload['items'][0]['remarks'] = '<script>alert("x")</script>';
$render = new RenderAssessment;
$html = $render->render($payload, false); $print = $render->render($payload, true);
check(substr_count($html, '<input type="radio"') === 96, 'Two mutually exclusive choices for 48 indicators');
check(substr_count($html, 'name="remarks[') === 48, '48 remarks inputs');
check(strpos($html, '<script>alert') === false && strpos($print, '<script>alert') === false, 'Escape saved remarks in edit and print');
check(strpos($print, 'type="radio"') === false && strpos($print, '<textarea') === false, 'Print uses text, not clipped inputs');
foreach (array('Summary', 'Technical Assistance Needed', 'Recommendations', 'Conforme', 'Name and Signature of Monitor', '2026-2027', '00123') as $text) check(strpos($print, $text) !== false, 'Print section: ' . $text);
check(strpos($print, '&#10003;') !== false, 'Print shows selected ratings');
echo "PASS: CRUD, snapshot round trip, region isolation, stale writes/deletes, rating boundaries, incomplete results, and edit/print rendering.\n";
