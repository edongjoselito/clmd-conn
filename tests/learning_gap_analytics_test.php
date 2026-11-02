<?php
// Run: CLMD_INTEGRATION_TESTS=1 php tests/learning_gap_analytics_test.php
// Only a disposable database is written; application records are untouched.
if (PHP_SAPI !== 'cli' || getenv('CLMD_INTEGRATION_TESTS') !== '1') {
    exit("Run with CLMD_INTEGRATION_TESTS=1 php tests/learning_gap_analytics_test.php\n");
}

define('BASEPATH', dirname(__DIR__) . '/system/');
define('APPPATH', dirname(__DIR__) . '/application/');
define('ENVIRONMENT', 'testing');
function log_message($level, $message) {}
function check($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
require BASEPATH . 'core/Common.php';
require BASEPATH . 'database/DB.php';
require BASEPATH . 'core/Model.php';
require APPPATH . 'models/Page_model.php';
require APPPATH . 'helpers/learning_gap_helper.php';
require APPPATH . 'config/database.php';

$settings = $db['default'];
if (!in_array($settings['hostname'], array('localhost', '127.0.0.1', '::1'), true)) {
    exit("These tests require a local MySQL/MariaDB connection.\n");
}
$settings['db_debug'] = false;
$settings['pconnect'] = false;

$name = 'clmd_analytics_test_' . bin2hex(random_bytes(6));
$admin = DB($settings);
check($admin->query('CREATE DATABASE `' . $name . '`'), 'Could not create disposable test database.');
$source_database = $settings['database'];
$settings['database'] = $name;
$connection = DB($settings);
$GLOBALS['analytics_app'] = (object) array('db' => $connection,
    'session' => (object) array('position' => 'division', 'division' => 61, 'district' => 0, 'username' => 'division', 'fy' => 2026));
function &get_instance() { return $GLOBALS['analytics_app']; }
$model = (new ReflectionClass('Page_model'))->newInstanceWithoutConstructor();
$property = new ReflectionProperty('Page_model', 'learning_gap_archive_schema_ready');
$property->setAccessible(true);
$property->setValue($model, true);
function add_record($connection, $school, $division, $district, $assessed, $gap, $cpl, $extra = array()) {
    check($connection->insert('learning_gap_records', array_merge(array('school_id' => $school, 'region_id' => 12,
        'division_id' => $division, 'district_id' => $district, 'fiscal_year' => 2026, 'grade_level' => 'Grade 7',
        'learning_area' => 'Araling Panlipunan', 'term' => 'Term 1', 'least_learned_competency' => 'Competency',
        'class_proficiency_level' => $cpl, 'learners_assessed' => $assessed, 'learners_with_gap' => $gap,
        'intervention_action' => 'Practice', 'intervention_status' => 'Planned', 'created_by' => $school,
        'created_at' => '2026-06-01 08:00:00'), $extra)), 'Could not insert a record fixture.');
}
function rollup_for($model, $scope) {
    return lg_scope_rollup(
        $model->learning_gap_scope_districts($scope),
        $model->learning_gap_scope_schools($scope),
        lg_unique_records($model->learning_gap_records($scope))
    );
}
try {
    // Use the installed column definitions, but never copy real reports or accounts.
    foreach (array('learning_gap_records', 'schools', 'district', 'division') as $table) {
        check($connection->query('CREATE TABLE `' . $table . '` LIKE `' . str_replace('`', '``', $source_database) . '`.`' . $table . '`'), 'Could not create fixture ' . $table);
    }
    $connection->query('ALTER TABLE learning_gap_records MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT, ADD UNIQUE KEY qa_record_id (id)');
    $connection->insert('division', array('id' => 61, 'description' => 'Division A', 'region_id' => 12));
    $connection->insert('division', array('id' => 62, 'description' => 'Division B', 'region_id' => 12));
    foreach (array(array(11, 'North', 61), array(12, 'South', 61), array(13, 'Quiet', 61), array(21, 'Other', 62)) as $district) {
        $connection->insert('district', array('id' => $district[0], 'description' => $district[1], 'division_id' => $district[2], 'region_id' => 12));
    }
    foreach (array(array('N1', 61, 11), array('N2', 61, 11), array('N3', 61, 11), array('S1', 61, 12), array('NO-DISTRICT', 61, 0), array('O1', 62, 21)) as $school) {
        check($connection->insert('schools', array('schoolID' => $school[0], 'schoolName' => 'School ' . $school[0], 'region_id' => 12,
            'division_id' => $school[1], 'district_id' => $school[2], 'sgc' => 0)), 'Could not insert a school fixture.');
    }
    add_record($connection, 'N1', 61, 11, 100, 20, 80);
    add_record($connection, 'N1', 61, 11, 50, 25, null, array('term' => 'Term 2'));
    add_record($connection, 'N2', 61, 11, 300, 30, 60, array('learning_area' => 'English', 'grade_level' => 'Grade 2'));
    add_record($connection, 'N3', 61, 11, 50, 25, 50, array('learning_area' => 'Science', 'grade_level' => 'Grade 10'));
    add_record($connection, 'S1', 61, 12, 40, 20, 50);
    add_record($connection, 'NO-DISTRICT', 61, 0, 10, 1, 90, array('grade_level' => 'Kindergarten'));
    add_record($connection, 'O1', 62, 21, 999, 999, 10);
    add_record($connection, 'N3', 61, 11, 700, 700, 5, array('fiscal_year' => 2025));

    $division = array('type' => 'division', 'id' => 61);
    $records = lg_unique_records($model->learning_gap_records($division));
    $rollup = rollup_for($model, $division);
    check((int) $rollup['totals']->record_count === 6, 'Division totals dropped a subject, or kept another year or division.');
    check((int) $rollup['totals']->learners_assessed === 550 && (int) $rollup['totals']->learners_with_gap === 121, 'Division totals are wrong.');
    check((int) $rollup['totals']->school_total === 5 && (int) $rollup['totals']->schools_reporting === 5, 'A school reporting only another subject was not counted.');
    check((int) $rollup['totals']->district_total === 3 && (int) $rollup['totals']->districts_reporting === 2, 'District coverage counted the unassigned bucket.');
    $areas = lg_group_rollup($records, 'learning_area');
    check(array_map(function ($row) { return $row->name; }, $areas) === array('Araling Panlipunan', 'English', 'Science'), 'Learning area breakdown is wrong.');
    check((int) $areas[0]->record_count === 4 && (float) $areas[1]->gap_rate === 10.0, 'Learning area figures are wrong.');
    $grades = lg_group_rollup($records, 'grade_level');
    check(array_map(function ($row) { return $row->name; }, $grades) === array('Kindergarten', 'Grade 2', 'Grade 7', 'Grade 10'), 'Grades are not in school order.');
    echo "PASS: every learning area is counted and broken down, within the selected year and division\n";

    $north = $rollup['districts']['11'];
    check((int) $north->school_total === 3 && (int) $north->schools_reporting === 3 && (int) $north->record_count === 4, 'District rollup is wrong.');
    // Only records with a CPL carry weight: (80*100 + 60*300 + 50*50) / 450.
    check((float) $north->class_proficiency_level === round(28500 / 450, 2), 'District CPL is not weighted by assessed count.');
    check((int) $rollup['districts']['13']->school_total === 0 && $rollup['districts']['13']->gap_rate === null, 'An empty district lost its row.');
    check((int) $rollup['districts']['unassigned']->record_count === 1, 'A school outside the listed districts was lost.');
    $district_sum = 0;
    foreach ($rollup['districts'] as $row) $district_sum += (int) $row->learners_assessed;
    check($district_sum === (int) $rollup['totals']->learners_assessed, 'District rows do not add up to the division total.');
    $connection->where('schoolID', 'S1')->delete('schools');
    $orphaned = rollup_for($model, $division);
    check($orphaned['schools']['S1']->missing_profile === true && (int) $orphaned['schools']['S1']->record_count === 1, 'A submission with no school profile disappeared.');
    $connection->insert('schools', array('schoolID' => 'S1', 'schoolName' => 'School S1', 'region_id' => 12, 'division_id' => 61, 'district_id' => 12, 'sgc' => 0));
    echo "PASS: per-district math, empty districts, the unassigned bucket and missing school profiles\n";

    $app = get_instance();
    $app->session->position = 'district';
    $app->session->district = 11;
    $own = rollup_for($model, array('type' => 'district', 'id' => 11));
    check(array_keys($own['districts']) === array(11) && count($own['schools']) === 3 && !isset($own['schools']['S1']), 'A district account sees outside its district.');
    check((int) $own['totals']->record_count === 4, 'District account totals disagree with the division view of that district.');
    check(count($model->learning_gap_records(array('type' => 'school', 'id' => 'N2'))) === 1, 'A district account could not open its own school.');
    check(count($model->learning_gap_records(array('type' => 'school', 'id' => 'S1'))) === 0, 'A district account read a school in another district.');
    check(count($model->learning_gap_records(array('type' => 'division', 'id' => 62))) === 4, 'A district account widened its scope.');
    $app->session->position = 'division';
    echo "PASS: a district account reaches its own schools only\n";

    $terms = lg_term_rollup($records);
    check((int) $terms[0]->record_count === 5 && (int) $terms[1]->record_count === 1, 'Term rollup is wrong.');
    check(strpos(lg_term_coverage_caution($terms)[0], 'Term 2 has only 1 record against 5 in Term 1') === 0, 'A thinly covered term was not flagged.');
    check(!empty(lg_term_interpretation($terms, 6)), 'Term rows do not fit lg_term_interpretation().');
    $lines = lg_analytics_interpretation(array_values(array_diff_key($rollup['districts'], array('unassigned' => true))), 'district');
    check(strpos($lines[0], '2 of 3 districts have submitted') === 0 && strpos($lines[0], 'Quiet') !== false, 'Coverage sentence is wrong.');
    echo "PASS: per-term rollup and plain-language interpretation\n";

    $levels = function ($rows) {
        $counts = array();
        foreach ($rows as $row) $counts[$row->label] = (int) $row->count;
        return $counts;
    };
    // N1 80 is Proficient; N2 60 and N3 50 are Nearly Proficient.
    $north_schools = array_values(array_filter($rollup['schools'], function ($row) { return $row->district_key === '11'; }));
    $north_levels = lg_proficiency_distribution($north_schools);
    check($levels($north_levels) === array('Highly Proficient' => 0, 'Proficient' => 1, 'Nearly Proficient' => 2, 'Low Proficient' => 0, 'Not Proficient' => 0, 'No CPL encoded' => 0), 'District proficiency counts are wrong.');
    check(round($north_levels[2]->share, 2) === 66.67, 'A proficiency share is wrong.');
    check($north_levels[0]->range === '90–100%' && $north_levels[1]->range === '75–89.99%' && $north_levels[4]->range === '0–24.99%', 'A CPL range does not match the proficiency levels.');
    $division_levels = lg_proficiency_distribution(array_values($rollup['schools']));
    check(array_sum($levels($division_levels)) === (int) $rollup['totals']->school_total, 'Proficiency counts do not add up to every school.');
    // Level boundaries, and a school whose only record has no CPL.
    $edge_levels = lg_proficiency_distribution(array_map(function ($cpl) {
        return (object) array('class_proficiency_level' => $cpl);
    }, array(90.0, 89.99, 75.0, 0.0, null)));
    check($levels($edge_levels) === array('Highly Proficient' => 1, 'Proficient' => 2, 'Nearly Proficient' => 0, 'Low Proficient' => 0, 'Not Proficient' => 1, 'No CPL encoded' => 1), 'A school landed in the wrong proficiency level.');
    check(lg_proficiency_distribution(array())[0]->share === 0, 'An empty district divided by zero.');
    echo "PASS: schools are counted per proficiency level and add up to every school\n";

    $table = lg_report_performance_table($terms, 'Term', 'term');
    check($table[0] === array('Term', 'Records', 'Assessed', 'With gap', 'Gap rate (%)', 'CPL (%)', 'Proficiency level', 'Mastery'), 'Report table headings are wrong.');
    check(count($table) === 4 && $table[1][0] === 'Term 1' && $table[1][1] === 5, 'Report table rows are wrong.');
    $expected_cpl = round($terms[0]->class_proficiency_level, 2);
    check($table[1][5] === $expected_cpl && $table[1][6] === lg_proficiency_level($expected_cpl), 'Report table CPL and level disagree.');
    // Term 2 has an assessed count but no CPL; Term 3 has no records at all.
    check($table[2][4] === 50.0 && $table[2][5] === null && $table[2][6] === null && $table[2][7] === null, 'A missing CPL was given a level.');
    check($table[3][4] === null && $table[3][5] === null, 'An empty term was given a value.');
    echo "PASS: report tables keep measures numeric and leave missing values empty\n";
} finally {
    $connection->close();
    $admin->query('DROP DATABASE `' . $name . '`');
    $admin->close();
}
