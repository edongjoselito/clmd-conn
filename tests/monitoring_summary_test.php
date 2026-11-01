<?php
// Run: php -n tests/monitoring_summary_test.php
// Isolated checks: no application database or authenticated session required.
define('BASEPATH', __DIR__);
define('APPPATH', dirname(__DIR__) . '/application/');
define('FCPATH', dirname(__DIR__) . '/');
class CI_Model { public $db; }
class CI_Controller {}
require APPPATH . 'models/Monitoring_model.php';
require APPPATH . 'libraries/Monitoring_summary.php';
require APPPATH . 'controllers/Pages.php';
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
function base_url($path = '') { return '/clmd-conn/' . $path; }
function html_escape($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
function show_404() { throw new DomainException('404'); }
function show_error($message, $code = 500) { throw new DomainException((string) $code); }

$groups = json_decode(file_get_contents(APPPATH . 'data/monitoring_indicators.json'), true);
function monitoring_form($groups, $division, $school, $evident_per_area, $extra = array()) {
    $items = array(); $id = 0;
    foreach ($groups as $area => $group) {
        foreach (array(0, 1, 2, 3) as $index) {
            $items[] = array('id' => ++$id, 'area' => $area, 'rating' => $index < $evident_per_area ? 'evident' : 'not_evident');
        }
    }
    return $extra + array('division_id' => $division, 'school_id' => $school, 'school_name' => 'School ' . $school,
        'district_name' => 'District ' . $division, 'items' => $items, 'details' => array());
}
$divisions = array((object) array('id' => 1, 'description' => 'Alpha'), (object) array('id' => 2, 'description' => 'Beta'), (object) array('id' => 3, 'description' => 'Gamma'));

// Roll-up: 2 forms at 100% and 50% average to 75%; in-progress forms stay out of the ratings.
$in_progress = monitoring_form($groups, 1, '003', 4);
$in_progress['items'][0]['rating'] = '';
$rows = Monitoring_summary::by_division(array(
    monitoring_form($groups, 1, '001', 4, array('details' => array('strengths' => 'Strong LAC', 'monitor_date' => '2026-10-01'))),
    monitoring_form($groups, 1, '002', 2),
    $in_progress,
    monitoring_form($groups, 2, '004', 1),
    monitoring_form($groups, 99, '005', 4),
    array('division_id' => 1),
), $groups, $divisions);
check(count($rows) === 3, 'One row per regional division, excluding other divisions');
$alpha = $rows[0];
check($alpha['assessed'] === 2 && $alpha['in_progress'] === 1 && $alpha['school_count'] === 2, 'Separate fully assessed and in-progress forms');
check(count($alpha['areas']) === 8, 'Eight domains');
foreach ($alpha['areas'] as $area) {
    check($area['percent'] === 75.0 && $area['total'] === 8 && $area['evident'] === 6, 'Domain rating ignores in-progress forms');
    check($area['description'] === 'Satisfactorily Meets Expectation', 'Domain uses the assessment rating scale');
}
check($alpha['overall']['score'] === '75.00%', 'Overall rating');
check(count($alpha['forms']) === 3 && $alpha['forms'][0]['strengths'] === 'Strong LAC' && $alpha['forms'][0]['monitor_date'] === '2026-10-01', 'Forms carry details');
check($rows[1]['areas'][0]['percent'] === 25.0 && $rows[1]['areas'][0]['description'] === 'Needs Improvement', 'Each division is rated separately');
check($rows[2]['assessed'] === 0 && $rows[2]['overall']['percent'] === null && $rows[2]['areas'][0]['score'] === '—', 'Unmonitored division has no rating');

// A division with only in-progress forms shows no rating rather than a low score.
$only_pending = Monitoring_summary::by_division(array($in_progress), $groups, $divisions)[0];
check($only_pending['assessed'] === 0 && $only_pending['in_progress'] === 1 && $only_pending['overall']['percent'] === null, 'In-progress forms never produce a rating');

// Controller: filters, defaults, tool rule, access, and the rendered page.
class SummaryInput { public $query = array(); public function get($key) { return isset($this->query[$key]) ? $this->query[$key] : null; } }
class SummarySession { public $position = 'region'; public $region = 1; }
class SummaryModel extends Monitoring_model {
    public $records = array();
    public function initialize($region_id) {}
    public function assessment_schema() {}
    public function assessments($region) { return $this->records; }
    public function tools($region) { return array((object) array('id' => 7, 'name' => 'Unified', 'seed_key' => 'legacy'), (object) array('id' => 8, 'name' => 'Second', 'seed_key' => null)); }
    public function tool($region, $id) { foreach ($this->tools($region) as $tool) { if ((int) $tool->id === (int) $id) return $tool; } return null; }
}
class SummaryPageModel { public $divisions; public function regional_divisions($region) { return $this->divisions; } }
class SummaryLoader {
    public $data = null; public $html = '';
    public function model($name) {}
    public function view($path, $data = array()) {
        if (strpos($path, 'pages/') !== 0) return;
        $this->data = $data;
        $this->html = (function ($__path, $__data) { extract($__data); ob_start(); include APPPATH . 'views/' . $__path . '.php'; return ob_get_clean(); })($path, $data);
    }
}
class SummaryPages extends Pages {
    public $input; public $session; public $load; public $Monitoring_model; public $Page_model;
    public function __construct() {}
}
$record = function ($id, $sy, $payload) { return (object) array('id' => $id, 'sy' => $sy, 'payload' => json_encode($payload)); };
$legacy = monitoring_form($groups, 1, '101', 4);
$second = monitoring_form($groups, 2, '102', 2, array('tool_id' => 8, 'tool_name' => 'Second'));
$second['details'] = array('recommendations' => '<script>alert("x")</script>');
$older = monitoring_form($groups, 1, '103', 1, array('tool_id' => 7));
$page = new SummaryPages;
$page->input = new SummaryInput; $page->session = new SummarySession; $page->load = new SummaryLoader;
$page->Monitoring_model = new SummaryModel; $page->Page_model = new SummaryPageModel; $page->Page_model->divisions = $divisions;
$page->Monitoring_model->records = array($record(3, '2026-2027', $second), $record(2, '2026-2027', $legacy), $record(1, '2025-2026', $older), $record(4, '2026-2027', 'not json'));

$page->monitoring_summary();
$view = $page->load->data;
check($view['selected_sy'] === '2026-2027' && array_keys($view['school_years']) === array('2026-2027', '2025-2026'), 'Default to the latest school year');
check($view['selected']['id'] === 1 && $view['selected']['assessed'] === 1, 'Default to the first division with forms; skip unreadable payloads');
check($view['selected']['forms'][0]['record_id'] === 2, 'Forms keep their record link');
$html = $page->load->html;
check(strpos($html, 'id="ms-domain-chart"') !== false && strpos($html, 'id="ms-download"') !== false, 'Graph and Word download are rendered');

$page->input->query = array('sy' => '2025-2026');
$page->monitoring_summary();
check($page->load->data['selected']['forms'][0]['school_id'] === '103' && $page->load->data['selected']['overall']['percent'] === 25.0, 'School year filter');

$page->input->query = array('division_id' => '2');
$page->monitoring_summary();
check($page->load->data['selected']['id'] === 2, 'Division filter');
check(strpos($page->load->html, '<script>alert') === false, 'Saved remarks are escaped in the page and its JSON');
check(strpos($page->load->html, '&lt;script&gt;alert') !== false, 'Saved remarks stay visible as text');

$page->input->query = array('tool_id' => '7');
$page->monitoring_summary();
check(count($page->load->data['summary'][0]['forms']) === 1 && $page->load->data['summary'][1]['forms'] === array(), 'Legacy assessments belong to the legacy tool');
$page->input->query = array('tool_id' => '8');
$page->monitoring_summary();
check($page->load->data['summary'][0]['forms'] === array() && count($page->load->data['summary'][1]['forms']) === 1 && $page->load->data['selected']['id'] === 2, 'Tool filter');

$page->input->query = array('division_id' => '3');
$page->monitoring_summary();
check($page->load->data['selected']['id'] === 3 && strpos($page->load->html, 'No fully assessed forms yet') !== false && strpos($page->load->html, 'id="ms-download"') === false, 'Division without forms shows an empty state and no download');

foreach (array(array('tool_id' => '999'), array('sy' => array('x'), 'division_id' => array('1'), 'tool_id' => '7')) as $index => $query) {
    $page->input->query = $query;
    try {
        $page->monitoring_summary();
        check($index === 1, 'Unknown tool is rejected');
    } catch (DomainException $error) { check($index === 0 && $error->getMessage() === '404', 'Unknown tool returns 404'); }
}

$page->Monitoring_model->records = array();
$page->input->query = array();
$page->Page_model->divisions = array();
$page->monitoring_summary();
check($page->load->data['selected'] === null && strpos($page->load->html, 'No assessments to summarize') !== false, 'Empty state without assessments');
check(strpos($page->load->html, 'Chart.bundle') === false, 'Empty state skips chart libraries');

foreach (array('division', 'division_head', 'district', 'school', 'admin', 'la_filipino') as $position) {
    $page->session->position = $position;
    try { $page->monitoring_summary(); throw new RuntimeException('Allowed ' . $position); }
    catch (DomainException $error) { check($error->getMessage() === '403', 'Only regional users: ' . $position); }
}
echo "PASS: domain roll-up, in-progress handling, school year/tool/division filters, escaping, empty states, and regional access.\n";
