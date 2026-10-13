<?php


class Page_model extends CI_Model{

    private $learning_gap_archive_schema_ready = false;
    private $password_change_schema_ready = false;

    public function __construct(){
        $this->load->database();
        // Run lightweight, idempotent application migrations automatically.
        // The migration marker makes this a metadata lookup after first deploy.
        $this->ensure_password_change_schema();
        $this->ensure_email_username_schema();
        $this->ensure_signup_field_sizes();
        $this->ensure_learning_gap_archive_schema();
    }

private function ensure_signup_field_sizes()
{
    $migration = '20261002_signup_field_sizes';
    if ($this->db->where('migration', $migration)->count_all_results('app_schema_migrations') > 0) {
        return;
    }
    // Signup accepts full school names and standard email addresses.
    $schools = $this->db->query("ALTER TABLE schools MODIFY schoolName VARCHAR(255) NOT NULL DEFAULT '', MODIFY schoolEmail VARCHAR(254) NOT NULL DEFAULT ''");
    $users = $this->db->query("ALTER TABLE users MODIFY fname VARCHAR(255) NOT NULL DEFAULT ''");
    if ($schools && $users) {
        $this->db->query('INSERT IGNORE INTO app_schema_migrations (migration) VALUES (?)', array($migration));
    }
}

private function ensure_email_username_schema()
{
    $migration = '20261001_users_email_username';
    if ($this->db->where('migration', $migration)->count_all_results('app_schema_migrations') > 0) {
        return;
    }

    if ($this->db->query("ALTER TABLE users MODIFY username VARCHAR(254) NOT NULL DEFAULT ''")) {
        $this->db->query(
            'INSERT INTO app_schema_migrations (migration)
             SELECT ? WHERE NOT EXISTS (
                 SELECT 1 FROM app_schema_migrations WHERE migration = ?
             )',
            array($migration, $migration)
        );
    }
}

private function ensure_password_change_schema()
{
    if ($this->password_change_schema_ready) {
        return;
    }

    $this->db->query("CREATE TABLE IF NOT EXISTS `app_schema_migrations` (
        `migration` VARCHAR(190) NOT NULL,
        `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`migration`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $migration = '20260922_users_must_change_password';
    if ($this->db->where('migration', $migration)->count_all_results('app_schema_migrations') > 0) {
        $this->password_change_schema_ready = true;
        return;
    }

    if (!$this->db->field_exists('must_change_password', 'users')) {
        $this->db->query('ALTER TABLE users ADD must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER password');
    }

    $this->db->query(
        'INSERT INTO app_schema_migrations (migration)
         SELECT ? WHERE NOT EXISTS (
             SELECT 1 FROM app_schema_migrations WHERE migration = ?
         )',
        array($migration, $migration)
    );
    $this->password_change_schema_ready = true;
}


public function profile_insert(){
    
    $data = array(
        'name' => $this->input->post('name'), 
        'docType' => $this->input->post('docType'), 
        'docNo' => $this->input->post('docNo'), 
        'dateReleased' => $this->input->post('dateReleased'), 
        'description' => $this->input->post('description')
    ); 

    return $this->db->insert('profile', $data);
    
}

function random_password(){
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $password = array();
    $alpha_length = strlen($alphabet) - 1;

    for ($i = 0; $i < 16; $i++) {
        $password[] = $alphabet[random_int(0, $alpha_length)];
    }

    return implode($password);
}


public function user_insert(){
    $file = $this->upload->data();
    $filename = $file['file_name']; 
    $division_id = $this->input->post('division_id');

    if (in_array($this->session->position, array('division', 'ict', 'district'), true)) {
        $division_id = $this->session->division;
    }

    $password = $this->input->post('password');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $data = array(
    'username' => trim((string) $this->input->post('email')),
    'email' => trim((string) $this->input->post('email')),
    'password' => $hash,
    'must_change_password' => 1,
    'position' => $this->input->post('position'),
    'fname' => $this->input->post('fname'),
    'mname' => $this->input->post('mname'),
    'lname' => $this->input->post('lname'),
    'gender' => $this->input->post('gender'),
    'r_id' => $this->session->region,
    'p_id' => $division_id,
    'd_id' => $this->session->position === 'district' ? (int) $this->session->district : $this->input->post('d_id'),
    'image' => $filename,
    'virified' => 0
    ); 

    return $this->db->insert('users', $data);
    
}

private $signup_failure_message = '';

public function signup_failure_message()
{
    return $this->signup_failure_message;
}

private function record_signup_failure($stage)
{
    $error = $this->db->error();
    $code = isset($error['code']) ? (int) $error['code'] : 0;
    $reference = bin2hex(random_bytes(6));
    $message = 'School signup database failure [' . $reference . '] stage=' . $stage
        . ' code=' . $code . ' ' . (isset($error['message']) ? $error['message'] : '');
    log_message('error', $message);
    // Hosting may not allow writes to application/logs; also use the PHP error log.
    error_log($message);
    $this->signup_failure_message = $code === 1062
        ? 'This school ID or email is already registered. Please check your details or sign in.'
        : 'Registration could not be saved. Please contact the administrator with reference ' . $reference . '.';
}

// Save the school and its immediately active account together.
public function register_school_account()
{
    $this->signup_failure_message = '';
    $debug = $this->db->db_debug;
    $this->db->db_debug = false;
    if (!$this->db->trans_begin()) {
        $this->record_signup_failure('begin');
        $this->db->db_debug = $debug;
        return false;
    }
    $school_saved = $this->insert_school();
    $user_id = $school_saved ? $this->insert_user() : false;
    if (!$school_saved || !$user_id || !$this->db->trans_status()) {
        $this->record_signup_failure(!$school_saved ? 'schools insert' : 'users insert');
        $this->db->trans_rollback();
        $this->db->db_debug = $debug;
        return false;
    }
    $committed = $this->db->trans_commit();
    if (!$committed) {
        $this->record_signup_failure('commit');
        $this->db->trans_rollback();
    }
    $this->db->db_debug = $debug;
    return $committed ? array('user_id' => $user_id) : false;
}

public function insert_user(){


    $password = $this->input->post('password');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $data = array(
    'username' => $this->input->post('schoolID'),
    'password' => $hash,
    'position' => 'school',
    'mname' => '',
    'lname' => '',
    'gender' => 0,
    'image' => '',
    'stat' => 0,
    'sec' => 0,
    'fname' => $this->input->post('schoolName'),
    'r_id' => 12,
    'p_id' => $this->input->post('division_id'),
    'd_id' => $this->input->post('d_id'),
    'email' => $this->input->post('schoolEmail'),
    // Legacy flag: zero means active and permitted to sign in.
    'virified' => 0
    ); 

    return $this->db->insert('users', $data) ? $this->db->insert_id() : false;
    
}

public function insert_district_user(){

    $district = $this->Common->one_cond_row('district', 'id',$this->input->post('d_id'));


    $password = $this->input->post('password');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $data = array(
    'username' => $this->input->post('schoolID'),
    'password' => $hash,
    'must_change_password' => 1,
    'position' => 'district',
    'fname' => $district->description,
    'r_id' => 12,
    'p_id' => $this->input->post('division_id'),
    'd_id' => $this->input->post('d_id'),
    'email' => $this->input->post('schoolEmail'),
    //'virified' => 1
    'virified' => 0
    ); 

    $this->db->insert('users', $data);
    return $this->db->insert_id(); 
}

private function ensure_user_verification_tokens_table()
{
    $this->db->query('CREATE TABLE IF NOT EXISTS `user_verification_tokens` (
        `user_id` INT UNSIGNED NOT NULL,
        `token_hash` CHAR(64) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`user_id`),
        UNIQUE KEY `uniq_user_verification_token_hash` (`token_hash`),
        KEY `idx_user_verification_expires` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}

public function create_signup_verification_token($user_id)
{
    $this->ensure_user_verification_tokens_table();
    $token = bin2hex(random_bytes(32));
    $this->db->replace('user_verification_tokens', array(
        'user_id' => (int) $user_id,
        'token_hash' => hash('sha256', $token),
        'expires_at' => date('Y-m-d H:i:s', time() + 86400),
    ));
    return $token;
}

public function confirm_signup($user_id, $token)
{
    if ((int) $user_id <= 0 || !preg_match('/^[a-f0-9]{64}$/', (string) $token)) {
        return false;
    }

    $this->ensure_user_verification_tokens_table();
    $row = $this->db->where('user_id', (int) $user_id)
        ->where('expires_at >=', date('Y-m-d H:i:s'))
        ->get('user_verification_tokens')->row();
    if (!$row || !hash_equals((string) $row->token_hash, hash('sha256', (string) $token))) {
        return false;
    }

    $this->db->trans_start();
    $this->db->where('id', (int) $user_id)->update('users', array('virified' => 0));
    $this->db->where('user_id', (int) $user_id)->delete('user_verification_tokens');
    $this->db->trans_complete();
    return $this->db->trans_status();
}

public function user_update(){

    $id = $this->input->post('id'); 

    $data = array(
        'fname' => $this->input->post('fname'),
        'mname' => $this->input->post('mname'),
        'lname' => $this->input->post('lname'),
        'gender' => $this->input->post('gender')
        );

    $this->db->where('id', $id);
    return $this->db->update('users', $data);
}

public function add_school_user($school_id, $schoolName, $district, $division, $password){
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $data = array(
    'username' => $school_id,
    'password' => $hash,
    'must_change_password' => 1,
    'position' => 'school',
    'fname' => $schoolName,
    'r_id' => 12,
    'p_id' => $division,
    'd_id' => $district,
    'email' => "",
    'virified' => 0
    ); 

    $this->db->insert('users', $data);
    return $this->db->insert_id();
    
}

public function user_password_change(){
    $user = $this->db->select('id, password')->where('id', $this->session->id)->get('users')->row();
    if (!$user || !password_verify((string) $this->input->post('current_password'), $user->password)) {
        return 'incorrect_current';
    }
    if (password_verify((string) $this->input->post('password'), $user->password)) {
        return 'same_password';
    }
    $hash = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
    
    $data = array(
    'password' => $hash,
    'must_change_password' => 0,
    ); 

    $this->db->where('id', $user->id);
    return $this->db->update('users', $data) ? 'updated' : 'failed';
    
}

public function division_user_password_change(){

    $password = $this->input->post('password');
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    $data = array(
    'password' => $hash,
    ); 

    $this->db->where('username', $this->input->post('school_id'));
    return $this->db->update('users', $data);
    
}


public function user_pass(){

    $id = $this->input->post('id'); 

    $password = $this->input->post('password');
    $hash = password_hash($password, PASSWORD_DEFAULT);


    $data = array(
        'password' => $hash,
        'must_change_password' => 1,
        );

    $this->db->where('id', $id);
    return $this->db->update('users', $data);
}

public function reset_user_password($id, $password){
    $data = array(
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'must_change_password' => 1
    );

    $this->db->where('id', $id);
    $updated = $this->db->update('users', $data);
    if ($updated) {
        $this->log_audit_trail('PASSWORD_RESET', 'users', (int) $id, null, array('password_reset' => true));
    }
    return $updated;
}

public function user_update_profile(){

    $id = $this->input->post('id');

    $file = $this->upload->data();
    $filename = $file['file_name']; 

    $data = array(
        'image' => $filename
        );

    $this->db->where('id', $id);
    return $this->db->update('users', $data);
}

public function users_update_profile(){

    $id = $this->session->id;

    $file = $this->upload->data();
    $filename = $file['file_name']; 

    $data = array(
        'image' => $filename
        );

    $this->db->where('id', $id);
    return $this->db->update('users', $data);
}

public function login(){

    $password = $this->input->post('password');
    $login_input = trim((string) $this->input->post('username', true));
    
    $this->db->where('virified', 0);
    $this->db->group_start();
    $this->db->where('username', $login_input);
    $this->db->or_where('email', $login_input);
    $this->db->group_end();
    $result = $this->db->get('users');

    if($result->num_rows() == 1){

        $data = $result->row();

       if (password_verify($password, $data->password)) {
            return $result->row_array();
       }

       // return $result->row_array();

    }else{
        return false;
    }

}
public function lock_screen(){

    $password = $this->input->post('password');
    
    $this->db->where('username', $this->session->username);
    //$this->db->where('status', 0);
    //$this->db->where('Password', $this->input->post('Password', true));
    $result = $this->db->get('users');

    if($result->num_rows() == 1){
      
        $data = $result->row(); 

       if (password_verify($password, $data->password)) {
            return $result->row_array();
       }

       // return $result->row_array();
        
    }else{
        return false;
    }

}

public function check_dup_user($fname,$lname,$username){
    $result = $this->db->where("fname",$fname);
    $result = $this->db->where('lname',$lname);
    $result = $this->db->or_where('username',$username);
    $result = $this->db->get('users');
    return $result;
}




public function school_insert(){

    $data = array(
    'schoolID' => $this->input->post('schoolID'),
    'schoolName' => $this->input->post('schoolName'),
    'course' => $this->input->post('course'),
    'yearEstab' => $this->input->post('yearEstab'),
    'schoolEmail' => $this->input->post('schoolEmail'),
    'congDist' => $this->input->post('congDist'),
    'province' => $this->input->post('province'),
    'city' => $this->input->post('city'),
    'brgy' => $this->input->post('brgy'),
    'sitio' => $this->input->post('sitio'),
    'adminFName' => $this->input->post('adminFName'),
    'adminMName' => $this->input->post('adminMName'),
    'adminLName' => $this->input->post('adminLName'),
    'adminMobile' => $this->input->post('adminMobile'),
    'adminTel' => $this->input->post('adminTel'),
    'adminEmail' => $this->input->post('adminEmail'),
    'adminDesignation' => $this->input->post('adminDesignation'),
    'permitNo' => $this->input->post('permitNo'),
    //'recogNo' => $this->input->post('recogNo'),
    //'offers' => $this->input->post('offers'),
    //'schoolLogo' => $this->input->post('schoolLogo'),
    //'type' => $this->input->post('type'),
    'electricity' => $this->input->post('electricity'),
    'internet' => $this->input->post('internet'),
    'mb' => $this->input->post('mb'),
    'provider' => $this->input->post('provider'),
    'coor' => $this->input->post('coor'),
    'r_id' => $this->session->r_id,
    'p_id' => $this->session->p_id,
    'd_id' => $this->input->post('district'),
    'schoolType' => $this->input->post('schoolType'),
    'sitio' => '',
    'adminTel' => ''
    );

    $result = $this->db->insert('schools', $data);

    // Log audit trail
    if ($result) {
        $this->log_audit_trail('ADD', 'schools', $this->input->post('schoolID'), null, $data);
    }

    return $result;

}



// common functions loop

public function no_cond($table){
    $query = $this->db->get($table);
    return $query->result();
}

public function no_cond_ne($table,$necol,$neval){
    $this->db->where($necol.' !=', $neval);
    $query = $this->db->get($table);
    return $query->result();
}

public function one_cond($table,$col,$val){
    $this->db->where($col, $val);
    $query = $this->db->get($table);
    return $query->result();
}

public function two_cond($table,$col,$val,$col2,$val2){
    $this->db->where($col, $val);
    $this->db->where($col2, $val2);
    $query = $this->db->get($table);
    return $query->result();
}
public function three_cond($table,$col,$val,$col2,$val2,$col3,$val3){
    $this->db->where($col, $val);
    $this->db->where($col2, $val2);
    $this->db->where($col3, $val3);
    $query = $this->db->get($table);
    return $query->result();
}

public function district_submission_counts($table, $division, $fy){
    $allowed_tables = array('sgod_action_plan', 'sbm', 'sbm_ta');

    if (!in_array($table, $allowed_tables, true)) {
        return array();
    }

    $query = $this->db
        ->select('b.district_id AS district, COUNT(DISTINCT CAST(a.school_id AS CHAR)) AS total', false)
        ->from($table . ' a')
        ->join('schools b', 'TRIM(CAST(a.school_id AS CHAR)) = TRIM(b.schoolID)', 'inner', false)
        ->where('b.division_id', $division)
        ->where('a.fy', $fy)
        ->group_by('b.district_id')
        ->get();

    $counts = array();
    foreach ($query->result() as $row) {
        $counts[(string) $row->district] = (int) $row->total;
    }

    return $counts;
}

public function submission_school_ids($table, $fy, $school_ids){
    $allowed_tables = array('sgod_action_plan', 'sbm', 'sbm_ta');

    if (!in_array($table, $allowed_tables, true) || empty($school_ids)) {
        return array();
    }

    // Normalize school_ids to trimmed strings for comparison
    $normalized_ids = array_map('trim', array_map('strval', $school_ids));

    // Build placeholders for parameterized query
    $placeholders = implode(',', array_fill(0, count($normalized_ids), '?'));
    $params = array_merge(array($fy), $normalized_ids);
    $query = $this->db->query(
        "SELECT DISTINCT TRIM(school_id) AS school_id FROM `{$table}` WHERE fy = ? AND TRIM(school_id) IN ({$placeholders})",
        $params
    );

    $submitted = array();
    foreach ($query->result() as $row) {
        $submitted[trim((string) $row->school_id)] = true;
    }

    return $submitted;
}

public function division_sgc_counts($division_id){
    $this->db->select('sgc, COUNT(*) AS total', false);
    $this->db->where('division_id', $division_id);
    $this->db->group_by('sgc');
    $query = $this->db->get('schools');

    $counts = array(1 => 0, 2 => 0, 3 => 0);
    foreach ($query->result() as $row) {
        $status = (int) $row->sgc;
        if (isset($counts[$status])) {
            $counts[$status] = (int) $row->total;
        }
    }

    return $counts;
}

public function district_sgc_counts($district_id){
    $this->db->select('sgc, COUNT(*) AS total', false);
    $this->db->where('district_id', $district_id);
    $this->db->group_by('sgc');
    $query = $this->db->get('schools');

    $counts = array(1 => 0, 2 => 0, 3 => 0);
    foreach ($query->result() as $row) {
        $status = (int) $row->sgc;
        if (isset($counts[$status])) {
            $counts[$status] = (int) $row->total;
        }
    }

    return $counts;
}

public function region_division_count($region_id){
    return (int) $this->db
        ->where('region_id', $region_id)
        ->count_all_results('division');
}

public function region_district_count($region_id){
    $row = $this->db
        ->select('COUNT(d.id) AS total', false)
        ->from('district d')
        ->join('division v', 'v.id = d.division_id', 'inner')
        ->where('v.region_id', $region_id)
        ->get()
        ->row();

    return $row ? (int) $row->total : 0;
}

public function region_school_count($region_id){
    return (int) $this->db
        ->where('region_id', $region_id)
        ->count_all_results('schools');
}

public function region_user_count($region_id){
    return (int) $this->db
        ->where('r_id', $region_id)
        ->count_all_results('users');
}

public function district_school_count($district_id){
    return (int) $this->db
        ->where('district_id', $district_id)
        ->count_all_results('schools');
}

public function district_submission_count($table, $district_id, $fy){
    $allowed_tables = array('sgod_action_plan', 'sbm', 'sbm_ta');

    if (!in_array($table, $allowed_tables, true)) {
        return 0;
    }

    $row = $this->db
        ->select('COUNT(DISTINCT CAST(a.school_id AS CHAR)) AS total', false)
        ->from($table . ' a')
        ->join('schools b', 'TRIM(CAST(a.school_id AS CHAR)) = TRIM(b.schoolID)', 'inner', false)
        ->where('b.district_id', $district_id)
        ->where('a.fy', $fy)
        ->get()
        ->row();

    return $row ? (int) $row->total : 0;
}

public function region_division_setup_summary($region_id){
    $row = $this->db
        ->select('SUM(COALESCE(total_schools, 0)) AS encoded_total_schools', false)
        ->select('SUM(CASE WHEN total_schools IS NOT NULL AND total_schools > 0 THEN 1 ELSE 0 END) AS configured_division_count', false)
        ->where('region_id', $region_id)
        ->get('division')
        ->row();

    return array(
        'encoded_total_schools' => $row ? (int) $row->encoded_total_schools : 0,
        'configured_division_count' => $row ? (int) $row->configured_division_count : 0,
    );
}

public function region_sgc_counts($region_id){
    $this->db->select('sgc, COUNT(*) AS total', false);
    $this->db->where('region_id', $region_id);
    $this->db->group_by('sgc');
    $query = $this->db->get('schools');

    $counts = array(1 => 0, 2 => 0, 3 => 0);
    foreach ($query->result() as $row) {
        $status = (int) $row->sgc;
        if (isset($counts[$status])) {
            $counts[$status] = (int) $row->total;
        }
    }

    return $counts;
}

public function division_sbm_rate_counts($division_id, $fy, $indicator_numbers){
    if (empty($indicator_numbers)) {
        return array();
    }

    $select = array();
    foreach ($indicator_numbers as $indicator_number) {
        $indicator_number = (int) $indicator_number;
        if ($indicator_number < 1) {
            continue;
        }

        for ($rate = 1; $rate <= 4; $rate++) {
            $alias = 'q' . $indicator_number . '_r' . $rate;
            $select[] = 'COUNT(DISTINCT CASE WHEN q' . $indicator_number . ' = ' . $rate . ' THEN school_id END) AS ' . $alias;
        }
    }

    if (empty($select)) {
        return array();
    }

    $row = $this->db
        ->select(implode(', ', $select), false)
        ->where('division', $division_id)
        ->where('fy', $fy)
        ->where('stat', 1)
        ->get('sbm')
        ->row();

    $counts = array();
    foreach ($indicator_numbers as $indicator_number) {
        $indicator_number = (int) $indicator_number;
        for ($rate = 1; $rate <= 4; $rate++) {
            $alias = 'q' . $indicator_number . '_r' . $rate;
            $counts[$indicator_number][$rate] = $row && isset($row->$alias) ? (int) $row->$alias : 0;
        }
    }

    error_log("division_sbm_rate_counts - division_id: $division_id, fy: $fy, counts: " . json_encode($counts));

    return $counts;
}

public function district_sbm_rate_counts($district_id, $fy, $indicator_numbers){
    if (empty($indicator_numbers)) {
        return array();
    }

    $select = array();
    foreach ($indicator_numbers as $indicator_number) {
        $indicator_number = (int) $indicator_number;
        if ($indicator_number < 1) {
            continue;
        }

        for ($rate = 1; $rate <= 4; $rate++) {
            $alias = 'q' . $indicator_number . '_r' . $rate;
            $select[] = 'COUNT(DISTINCT CASE WHEN a.q' . $indicator_number . ' = ' . $rate . ' THEN CAST(a.school_id AS CHAR) END) AS ' . $alias;
        }
    }

    if (empty($select)) {
        return array();
    }

    $row = $this->db
        ->select(implode(', ', $select), false)
        ->from('sbm a')
        ->join('schools b', 'TRIM(CAST(a.school_id AS CHAR)) = TRIM(b.schoolID)', 'inner', false)
        ->where('b.district_id', $district_id)
        ->where('a.fy', $fy)
        ->get()
        ->row();

    $counts = array();
    foreach ($indicator_numbers as $indicator_number) {
        $indicator_number = (int) $indicator_number;
        for ($rate = 1; $rate <= 4; $rate++) {
            $alias = 'q' . $indicator_number . '_r' . $rate;
            $counts[$indicator_number][$rate] = $row && isset($row->$alias) ? (int) $row->$alias : 0;
        }
    }

    return $counts;
}

public function region_sbm_rate_counts($region_id, $fy, $indicator_numbers){
    if (empty($indicator_numbers)) {
        return array();
    }

    $select = array();
    foreach ($indicator_numbers as $indicator_number) {
        $indicator_number = (int) $indicator_number;
        if ($indicator_number < 1) {
            continue;
        }

        for ($rate = 1; $rate <= 4; $rate++) {
            $alias = 'q' . $indicator_number . '_r' . $rate;
            $select[] = 'COUNT(DISTINCT CASE WHEN q' . $indicator_number . ' = ' . $rate . ' THEN school_id END) AS ' . $alias;
        }
    }

    if (empty($select)) {
        return array();
    }

    $row = $this->db
        ->select(implode(', ', $select), false)
        ->where('region', $region_id)
        ->where('fy', $fy)
        ->where('stat', 1)
        ->get('sbm')
        ->row();

    $counts = array();
    foreach ($indicator_numbers as $indicator_number) {
        $indicator_number = (int) $indicator_number;
        for ($rate = 1; $rate <= 4; $rate++) {
            $alias = 'q' . $indicator_number . '_r' . $rate;
            $counts[$indicator_number][$rate] = $row && isset($row->$alias) ? (int) $row->$alias : 0;
        }
    }

    error_log("region_sbm_rate_counts - region_id: $region_id, fy: $fy, counts: " . json_encode($counts));

    return $counts;
}

public function division_sbm_completed_count($division_id, $fy){
    $row = $this->db
        ->select('COUNT(DISTINCT school_id) AS total', false)
        ->where('division', $division_id)
        ->where('fy', $fy)
        ->where('stat', 1)
        ->get('sbm')
        ->row();

    return $row ? (int) $row->total : 0;
}

public function district_sbm_completed_count($district_id, $fy){
    $row = $this->db
        ->select('COUNT(DISTINCT CAST(a.school_id AS CHAR)) AS total', false)
        ->from('sbm a')
        ->join('schools b', 'TRIM(CAST(a.school_id AS CHAR)) = TRIM(b.schoolID)', 'inner', false)
        ->where('b.district_id', $district_id)
        ->where('a.fy', $fy)
        ->where('a.stat', 1)
        ->get()
        ->row();

    return $row ? (int) $row->total : 0;
}

public function region_sbm_completed_count($region_id, $fy){
    $row = $this->db
        ->select('COUNT(DISTINCT school_id) AS total', false)
        ->where('region', $region_id)
        ->where('fy', $fy)
        ->where('stat', 1)
        ->get('sbm')
        ->row();

    return $row ? (int) $row->total : 0;
}

public function district_tech_entry_count($district_id, $fy){
    return (int) $this->db
        ->where('district', $district_id)
        ->where('fy', $fy)
        ->count_all_results('sbm_tech');
}

public function division_completed_checklist_schools($division_id, $fy){
    return $this->db
        ->select("b.recID, b.schoolID, b.schoolName, d.description AS district_name, 'Finalized' AS detail_status", false)
        ->from('sbm a')
        ->join('schools b', 'a.school_id = b.schoolID', 'inner')
        ->join('district d', 'd.id = b.district_id', 'left')
        ->where('a.division', $division_id)
        ->where('a.fy', $fy)
        ->where('a.stat', 1)
        ->group_by(array('b.recID', 'b.schoolID', 'b.schoolName', 'd.description'))
        ->order_by('b.schoolName', 'ASC')
        ->get()
        ->result();
}

public function division_completed_checklist_report_rows($division_id, $fy){
    return $this->db
        ->select("
            MAX(b.recID) AS recID,
            CAST(a.school_id AS CHAR) AS school_id,
            COALESCE(MAX(NULLIF(TRIM(b.schoolID), '')), CAST(a.school_id AS CHAR)) AS schoolID,
            COALESCE(MAX(NULLIF(TRIM(b.schoolName), '')), '') AS schoolName,
            MAX(b.division_id) AS division_id,
            COALESCE(MAX(NULLIF(TRIM(v.description), '')), 'Division') AS division_name,
            MAX(b.district_id) AS district_id,
            COALESCE(MAX(NULLIF(TRIM(d.description), '')), 'Unassigned District') AS district_name,
            'Finalized' AS detail_status
        ", false)
        ->from('sbm a')
        ->join('schools b', 'TRIM(CAST(a.school_id AS CHAR)) = TRIM(b.schoolID)', 'left', false)
        ->join('district d', 'd.id = b.district_id', 'left')
        ->join('division v', 'v.id = b.division_id', 'left')
        ->where('a.division', $division_id)
        ->where('a.fy', $fy)
        ->where('a.stat', 1)
        ->group_by('a.school_id')
        ->order_by('district_name', 'ASC')
        ->order_by('schoolName', 'ASC')
        ->get()
        ->result();
}

public function region_completed_checklist_report_rows($region_id, $fy){
    return $this->db
        ->select("
            MAX(b.recID) AS recID,
            CAST(a.school_id AS CHAR) AS school_id,
            COALESCE(MAX(NULLIF(TRIM(b.schoolID), '')), CAST(a.school_id AS CHAR)) AS schoolID,
            COALESCE(MAX(NULLIF(TRIM(b.schoolName), '')), '') AS schoolName,
            MAX(b.division_id) AS division_id,
            COALESCE(MAX(NULLIF(TRIM(v.description), '')), 'Division') AS division_name,
            MAX(b.district_id) AS district_id,
            COALESCE(MAX(NULLIF(TRIM(d.description), '')), 'Unassigned District') AS district_name,
            'Finalized' AS detail_status
        ", false)
        ->from('sbm a')
        ->join('schools b', 'TRIM(CAST(a.school_id AS CHAR)) = TRIM(b.schoolID)', 'left', false)
        ->join('district d', 'd.id = b.district_id', 'left')
        ->join('division v', 'v.id = b.division_id', 'left')
        ->where('a.region', $region_id)
        ->where('a.fy', $fy)
        ->where('a.stat', 1)
        ->group_by('a.school_id')
        ->order_by('district_name', 'ASC')
        ->order_by('schoolName', 'ASC')
        ->get()
        ->result();
}

public function division_schools_by_sgc_status($division_id, $sgc_status){
    $status_labels = array(
        1 => 'Not Yet Organized',
        2 => 'Organized, Not Functional',
        3 => 'Functional'
    );

    $detail_status = isset($status_labels[(int) $sgc_status])
        ? $status_labels[(int) $sgc_status]
        : 'Unknown';

    return $this->db
        ->select('s.recID, s.schoolID, s.schoolName, s.category, d.description AS district_name, ' . $this->db->escape($detail_status) . ' AS detail_status', false)
        ->from('schools s')
        ->join('district d', 'd.id = s.district_id', 'left')
        ->where('s.division_id', $division_id)
        ->where('s.sgc', $sgc_status)
        ->order_by('s.schoolName', 'ASC')
        ->get()
        ->result();
}

public function division_account_overview($division_id){
    $schools = $this->db
        ->select('schoolName, district_id, schoolID, schoolType, recID, division_id')
        ->where('division_id', $division_id)
        ->order_by('schoolName', 'ASC')
        ->get('schools')
        ->result();

    $users = $this->db
        ->select('id, username, position, d_id')
        ->where('p_id', $division_id)
        ->get('users')
        ->result();

    $schools_by_district = array();
    foreach ($schools as $school) {
        $schools_by_district[(string) $school->district_id][] = $school;
    }

    $school_usernames = array();
    $school_account_ids = array();
    $district_user_counts = array();
    foreach ($users as $user) {
        $school_usernames[(string) $user->username] = true;
        $school_account_ids[(string) $user->username] = (int) $user->id;

        if ($user->position === 'district') {
            $district_id = (string) $user->d_id;
            $district_user_counts[$district_id] = isset($district_user_counts[$district_id])
                ? $district_user_counts[$district_id] + 1
                : 1;
        }
    }

    return array(
        'schools_by_district' => $schools_by_district,
        'school_usernames' => $school_usernames,
        'school_account_ids' => $school_account_ids,
        'district_user_counts' => $district_user_counts,
        'school_count' => count($schools)
    );
}

public function ensure_division_setup_schema(){
    /*
     * Keep lightweight application migrations in the database.  The metadata
     * query runs when this feature is used, while the schema statements below
     * are executed only once for each database.
     */
    if (!$this->db->table_exists('app_schema_migrations')) {
        $this->db->query("CREATE TABLE `app_schema_migrations` (
            `migration` VARCHAR(190) NOT NULL,
            `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`migration`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    $migration = '20260907_division_homepage_fields';
    $already_applied = $this->db
        ->where('migration', $migration)
        ->count_all_results('app_schema_migrations') > 0;

    if ($already_applied) {
        return;
    }

    if (!$this->db->field_exists('total_schools', 'division')) {
        $this->db->query("ALTER TABLE division ADD COLUMN total_schools INT DEFAULT NULL AFTER region_id");
    }

    if (!$this->db->field_exists('homepage_logo', 'division')) {
        $this->db->query("ALTER TABLE division ADD COLUMN homepage_logo VARCHAR(255) DEFAULT NULL AFTER total_schools");
    }

    $this->db->insert('app_schema_migrations', array('migration' => $migration));
}

public function get_division_setup($division_id){
    $this->ensure_division_setup_schema();

    return $this->db
        ->where('id', $division_id)
        ->get('division')
        ->row();
}

public function update_division_setup($division_id, $homepage_logo = null){
    $this->ensure_division_setup_schema();

    $data = array(
        'description' => trim($this->input->post('description')),
        'total_schools' => (int) $this->input->post('total_schools')
    );

    if ($homepage_logo !== null) {
        $data['homepage_logo'] = $homepage_logo;
    }

    $this->db->where('id', $division_id);
    return $this->db->update('division', $data);
}

public function homepage_divisions($region_id){
    $this->ensure_division_setup_schema();

    return $this->db
        ->select('id, description, homepage_logo')
        ->where('region_id', (int) $region_id)
        ->order_by('id', 'ASC')
        ->get('division')
        ->result();
}

public function division_school_count($division_id){
    return (int) $this->db
        ->where('division_id', $division_id)
        ->count_all_results('schools');
}

public function division_district_count($division_id){
    return (int) $this->db
        ->where('division_id', $division_id)
        ->count_all_results('district');
}

public function division_names_by_ids($division_ids){
    $division_ids = array_values(array_unique(array_filter($division_ids)));

    if (empty($division_ids)) {
        return array();
    }

    $this->db->select('id, description');
    $this->db->where_in('id', $division_ids);
    $query = $this->db->get('division');

    $names = array();
    foreach ($query->result() as $row) {
        $names[(string) $row->id] = $row->description;
    }

    return $names;
}

public function one_cond_loop_order_by($table,$col,$val,$orderby,$orderbyvalue){
    $this->db->where($col, $val);
    $this->db->order_by($orderby, $orderbyvalue);
    $query = $this->db->get($table);
    return $query->result();
}



// common function single row
public function one_cond_row($table, $col, $val){
    $this->db->where($col, $val);
    $result = $this->db->get($table)->row();
    return $result;
}

public function two_cond_row_select($table,$select, $col, $val,$col2, $val2)
    {
        $this->db->select($select);
        $this->db->where($col, $val);
        $this->db->where($col2, $val2);
        $result = $this->db->get($table)->row();
        return $result;
}


//common function

public function delete($table,$col_id,$segment){
    $id = $this->uri->segment($segment);
    $this->db->where($col_id,$id);
    $this->db->delete($table);
    return true;
}

public function delete_two_cond($table,$col,$val,$col2,$val2){
    $this->db->where($col,$val);
    $this->db->where($col2,$val2);
    $this->db->delete($table);
    return true;
}

function delete_with_attach($table,$segment,$attach){
    $this->db->where('id', $segment);
    $file = FCPATH . 'uploads/' . basename((string) $attach);
    if (!empty($attach) && file_exists($file)) {
        unlink($file);
    }
    $this->db->delete($table);
}


// Special query
public function schools_with_district($id)
{
    $this->db->select('a.*, b.description');
    $this->db->from('schools a');
    $this->db->join('district b', 'b.id = a.district_id', 'left');
    $this->db->where('district_id', $id);
    $query = $this->db->get();
    return $query->result();
}


public function get_districts_by_division($division_id) {
    return $this->db->get_where('district', ['division_id' => $division_id])->result();
}

public function district_insert() {
    $data = array(
        'description' => $this->input->post('description'),
        'division_id' => $this->input->post('division_id')
    );
    return $this->db->insert('district', $data);
}

public function district_update() {
    $id = $this->input->post('id');
    $data = array(
        'description' => $this->input->post('description'),
        'division_id' => $this->input->post('division_id')
    );
    $this->db->where('id', $id);
    return $this->db->update('district', $data);
}

public function district_delete($id) {
    $this->db->where('id', $id);
    return $this->db->delete('district');
}

public function action_plan_insert()
	{
		$data = array(
			'activity' => $this->input->post('activity'),
			'objective' => $this->input->post('objective'),
			'ex_output' => $this->input->post('ex_output'),
			'metho_strategy' => $this->input->post('metho_strategy'),
			'time_frame' => $this->input->post('time_frame'),
			'person_involved' => $this->input->post('person_involved'),
			'bud_req' => $this->input->post('bud_req'),
			'remarks' => $this->input->post('remarks'),
			'fy' => $this->session->fy,
			'school_id' => $this->session->username,
            'region' => $this->session->region,
            'division' => $this->session->division,
            'district' => $this->session->district,

		);

		return $this->db->insert('sgod_action_plan', $data);
}

public function action_plan_update()
	{

		$data = array(
			'activity' => $this->input->post('activity'),
			'objective' => $this->input->post('objective'),
			'ex_output' => $this->input->post('ex_output'),
			'metho_strategy' => $this->input->post('metho_strategy'),
			'time_frame' => $this->input->post('time_frame'),
			'person_involved' => $this->input->post('person_involved'),
			'bud_req' => $this->input->post('bud_req'),
			'remarks' => $this->input->post('remarks'),

		);

		$this->db->where('id', $this->input->post('id'));
		$this->db->where('school_id', (string) $this->session->username);
		return $this->db->update('sgod_action_plan', $data);
}

public function sbm_checklist_insert()
{
    $data = [];

    // Loop through q1 to q42
    for ($i = 1; $i <= 42; $i++) {
        $data["q$i"] = $this->input->post("q$i");
    }

    // Add fixed values
    $data['school_id'] = $this->session->username;
    $data['fy'] = $this->session->fy;
    $data['district'] = $this->input->post('district');
    $data['region'] = $this->session->region;
    $data['division'] = $this->session->division;

    return $this->db->insert('sbm', $data);
}

public function sbm_checklist_update()
{
    $data = [];

    // Loop through q1 to q42
    for ($i = 1; $i <= 42; $i++) {
        $data["q$i"] = $this->input->post("q$i");
    }


   $this->db->where('id', $this->input->post('id'));
   $this->db->where('school_id', (string) $this->session->username);
   return $this->db->update('sbm', $data);
}

public function sbm_cecklist_lock_unloc($stat){
	$data = array(
		'stat' => $stat
	);

	$this->db->where('id', $this->uri->segment(3));
	return $this->db->update('sbm', $data);
}

public function sbm_checklist_lock_unloc_by_id($id, $stat, $school_id = null)
{
    $this->db->where('id', (int) $id);
    if ($school_id !== null) {
        $this->db->where('school_id', (string) $school_id);
    }
    return $this->db->update('sbm', array('stat' => (int) $stat));
}

    public function sbm_ta_insert()
	{
		$data = [];

		// Collect data for 'q', 'qq', 'a', and 'f' fields
		foreach (['q', 'qq', 'a', 'f'] as $prefix) {
			for ($i = 1; $i <= 42; $i++) {
				$data["{$prefix}{$i}"] = $this->input->post("{$prefix}{$i}");
			}
		}

		// Add additional fields
		$data['school_id'] = $this->session->username;
		$data['fy'] = $this->session->fy;
		$data['district'] = $this->session->district;
        $data['region'] = $this->session->region;
        $data['division'] = $this->session->division;
        $data['stat'] = 0;

		return $this->db->insert('sbm_ta', $data);
	}

    public function sbm_tana_insert()
	{
		$data = [];

		foreach (['a', 'b', 'c', 'd'] as $prefix) {
			for ($i = 1; $i <= 42; $i++) {
				$data["{$prefix}{$i}"] = $this->input->post("{$prefix}{$i}");
			}
		}

		$data['school_id'] = $this->session->username;
		$data['fy'] = $this->session->fy;
		$data['district'] = $this->session->district;
        $data['region'] = $this->session->region;
        $data['division'] = $this->session->division;
        $data['stat'] = 0;

		return $this->db->insert('tana', $data);
	}

    

	public function sbm_ta_update()
	{
		$data = [];

		foreach (['q', 'qq', 'a', 'f'] as $prefix) {
			for ($i = 1; $i <= 42; $i++) {
				$data["{$prefix}{$i}"] = $this->input->post("{$prefix}{$i}");
			}
		}

			$this->db->where('id', $this->input->post('id'));
			$this->db->where('school_id', (string) $this->session->username);
			return $this->db->update('sbm_ta', $data);
	}

    public function sbm_tana_update()
	{
		$data = [];

		foreach (['a', 'b', 'c', 'd'] as $prefix) {
			for ($i = 1; $i <= 42; $i++) {
				$data["{$prefix}{$i}"] = $this->input->post("{$prefix}{$i}");
			}
		}

		$this->db->where('id', $this->input->post('id'));
		$this->db->where('school_id', (string) $this->session->username);
		return $this->db->update('tana', $data);
	}

    public function sbm_ta_lock_unloc($stat)
	{
		$data = array(
			'stat' => $stat
		);

		$this->db->where('id', $this->uri->segment(3));
		return $this->db->update('sbm_ta', $data);
	}

    public function sbm_ta_lock_unloc_by_id($id, $stat, $school_id = null)
	{
		$this->db->where('id', (int) $id);
        if ($school_id !== null) {
            $this->db->where('school_id', (string) $school_id);
        }
		return $this->db->update('sbm_ta', array('stat' => (int) $stat));
	}
    
    public function sbm_cecklist_admin_insert()
	{
		$data = [];

		for ($i = 1; $i <= 42; $i++) {
			$data["q$i"] = $this->input->post("r$i");
		}

		for ($i = 1; $i <= 42; $i++) {
			$data["fs$i"] = $this->input->post("fs$i");
		}

		$data['school_id'] = $this->input->post('school_id');
		$data['fy'] = date('Y');

		return $this->db->insert('sbm_remark_admin', $data);
	}

    public function sbm_cecklist_admin_update()
	{
		$data = [];

		for ($i = 1; $i <= 42; $i++) {
			$data["q$i"] = $this->input->post("r$i");
		}

		for ($i = 1; $i <= 42; $i++) {
			$data["fs$i"] = $this->input->post("fs$i");
		}


        $this->db->where('id', $this->input->post('id'));
		return $this->db->update('sbm_remark_admin', $data);
	}

    public function sbm_tech_insert()
	{

		$data = array(
			'ta_rec' => $this->input->post('ta_rec'),
			'sa' => $this->input->post('sa'),
			'cd' => $this->input->post('cd'),
			'mtd' => $this->input->post('mtd'),
			'schedule' => $this->input->post('schedule'),
			'ct' => $this->input->post('ct'),
			'district' => $this->session->district,
			'fy' => date('Y'),

		);

		return $this->db->insert('sbm_tech', $data);
	}

    public function sbm_tech_update()
	{

		$data = array(
			'ta_rec' => $this->input->post('ta_rec'),
			'sa' => $this->input->post('sa'),
			'cd' => $this->input->post('cd'),
			'mtd' => $this->input->post('mtd'),
			'schedule' => $this->input->post('schedule'),
			'ct' => $this->input->post('ct'),
			'district' => $this->session->district,
			'fy' => date('Y'),

		);

        $this->db->where('id', $this->input->post('id'));
		return $this->db->update('sbm_tech', $data);
	}

    public function insert_school()
	{
		$data = array(
			'schoolID' => $this->input->post('schoolID'),
			'schoolName' => $this->input->post('schoolName'),
			'division_id' => $this->input->post('division_id'),
			'district_id' => $this->input->post('d_id'),
            'region_id' => 12,
			'schoolEmail' => $this->input->post('schoolEmail'),
            // Profile details are not collected during signup. Zero denotes unset values.
            'schoolType' => 0,
            'category' => 0,
            'sgc' => 0,
			'schoolLogo' => 'logo.png'
		);

        // Explicit empty profile values also support schemas without column defaults.
        foreach (array('yearEstab', 'congDist', 'province', 'city', 'brgy', 'sitio',
            'adminFName', 'adminMName', 'adminLName', 'adminMobile', 'adminTel',
            'adminEmail', 'adminDesignation', 'recogNo') as $field) {
            $data[$field] = '';
        }
        return $this->db->insert('schools', $data);
	}

    public function all_fields_positive($id)
    {
        $this->db->from('sbm');
        $this->db->where('id', $id);

        for ($i = 1; $i <= 42; $i++) {
            $this->db->where("q{$i} >", 0);
        }

        $query = $this->db->get();
        return $query->num_rows() > 0; 
    }


    public function get_averages($school_id, $fy) {
        $this->db->where('school_id', $school_id);
        $this->db->where('fy', $fy);
        $query = $this->db->get('tana');

        if ($query->num_rows() > 0) {
            $row = $query->row();

            $averages = [];
            for ($i = 1; $i <= 42; $i++) {
                $a = "a$i";
                $b = "b$i";
                $c = "c$i";
                $d = "d$i";

                $averages[$i] = ($row->$a + $row->$b + $row->$c + $row->$d) / 4;
            }

            return $averages;
        }

        return [];
    }

    public function sbm_tana_summary_insert()
    {
        $concern_id = $this->input->post('concern_id'); 
        $average    = $this->input->post('average');
        $sequence   = $this->input->post('sequence');

        if (!is_array($concern_id) || !is_array($average) || !is_array($sequence)) {
            return 0;
        }

        $fy       = $this->session->fy;
        $school   = $this->session->username;
        $region   = $this->session->region;
        $division = $this->session->division;
        $district = $this->session->district;

        $rows  = [];
        $count = min(count($concern_id), count($average), count($sequence));

        for ($i = 0; $i < $count; $i++) {
            if ($concern_id[$i] === '' || $average[$i] === '' || $average[$i] === null) {
                continue;
            }

            if (!isset($sequence[$i]) || $sequence[$i] === '' || $sequence[$i] === null) {
                continue;
            }

            $rows[] = [
                'fy'         => $fy,
                'school_id'  => $school,
                'region'     => $region,
                'division'   => $division,
                'district'   => $district,
                'stat'       => 0,
                'concern_id' => $concern_id[$i],
                'average'    => $average[$i],
                'sequence'   => (int) $sequence[$i],
            ];
        }

        if (empty($rows)) return 0;

        $this->db->trans_start();
        $this->db->insert_batch('tana_summary', $rows);
        $this->db->trans_complete();

        return $this->db->trans_status() ? $this->db->affected_rows() : 0;
    }

    public function get_seq_one_two()
    {
        $fy       = $this->session->fy;
        $region   = $this->session->region;
        $division = $this->session->division;

        $select = array(
            'tana_summary.school_id',
            'tana_summary.fy',
            'tana_summary.concern_id',
            'tana_summary.average',
            'tana_summary.sequence'
        );

        for ($i = 1; $i <= 42; $i++) {
            $select[] = 'sbm_ta.q' . $i;
        }

        return $this->db
            ->select(implode(', ', $select))
            ->from('tana_summary')
            ->join(
                'sbm_ta',
                'sbm_ta.school_id = tana_summary.school_id AND sbm_ta.fy = tana_summary.fy',
                'left'
            )
            ->where_in('tana_summary.sequence', [1, 2])
            ->where('tana_summary.fy', $fy)
            ->where('tana_summary.division', $division)
            ->where('tana_summary.region', $region)
            ->order_by('tana_summary.fy', 'ASC')
            ->order_by('tana_summary.school_id', 'ASC')
            ->order_by('tana_summary.sequence', 'ASC')
            ->get()
            ->result();
    }

    public function tana_division_insert(){
            $fy       = $this->session->fy;
            $region   = $this->session->region;
            $division = $this->session->division;
        
            $data = array(
                'tana' => $this->input->post('tana'), 
                'sequence' => $this->input->post('sequence'), 
                'region' => $region,
                'division' => $division, 
                'fy' => $fy
            ); 

        return $this->db->insert('division_tana', $data);
    }

    public function tana_division_autogenerate()
    {
        $fy       = $this->session->fy;
        $region   = $this->session->region;
        $division = $this->session->division;
        $source_rows = $this->get_seq_one_two();
        $themes = array();
        $source_count = 0;
        $order = 0;

        foreach ($source_rows as $row) {
            $question = 'q' . $row->concern_id;
            $text = isset($row->$question) ? trim((string) $row->$question) : '';

            if ($text === '') {
                continue;
            }

            $text = preg_replace('/\s+/', ' ', $text);
            $key = strtolower($text);
            $source_count++;

            if (!isset($themes[$key])) {
                $themes[$key] = array(
                    'tana' => $text,
                    'count' => 0,
                    'order' => $order,
                );
                $order++;
            }

            $themes[$key]['count']++;
        }

        if (empty($themes)) {
            return array(
                'status' => false,
                'count' => 0,
                'truncated' => false,
                'message' => 'No priority concerns with values were found for auto-generation.',
            );
        }

        $theme_rows = array_values($themes);

        usort($theme_rows, function ($a, $b) {
            if ($a['count'] === $b['count']) {
                return $a['order'] <=> $b['order'];
            }

            return $b['count'] <=> $a['count'];
        });

        $rows = array();
        $sequence = 1;

        foreach ($theme_rows as $theme) {
            if ($sequence > 20) {
                break;
            }

            $rows[] = array(
                'tana' => $theme['tana'],
                'sequence' => $sequence,
                'region' => $region,
                'division' => $division,
                'fy' => $fy,
            );

            $sequence++;
        }

        $this->db->trans_start();
        $this->db->where('fy', $fy);
        $this->db->where('region', $region);
        $this->db->where('division', $division);
        $this->db->delete('division_tana');
        $this->db->insert_batch('division_tana', $rows);
        $this->db->trans_complete();

        return array(
            'status' => $this->db->trans_status(),
            'count' => count($rows),
            'truncated' => count($theme_rows) > count($rows),
            'source_count' => $source_count,
            'message' => $this->db->trans_status() ? '' : 'Unable to save the auto-generated thematic analysis.',
        );
    }

    public function tana_region_insert(){
            $fy       = $this->session->fy;
            $region   = $this->session->region;
            $division = $this->session->division;
        
            $data = array(
                'tana' => $this->input->post('tana'), 
                'sequence' => $this->input->post('sequence'), 
                'region' => $region,
                'fy' => $fy
            ); 

        return $this->db->insert('region_tana', $data);
    }

public function school_updates()
	{
		$old_school_id = $this->input->post('old_schoolID');
		$new_school_id = $this->input->post('schoolID');
		$rec_id = $this->input->post('recID');

		// Get old values for audit trail
		$old_record = $this->one_cond_row('schools', 'recID', $rec_id);

		$data = array(
			'schoolID' => $new_school_id,
			'schoolName' => $this->input->post('schoolName'),
            'adminFName' => $this->input->post('adminFName'),
            'adminMName' => $this->input->post('adminMName'),
            'adminLName' => $this->input->post('adminLName'),
            'adminDesignation' => $this->input->post('adminDesignation'),
            'schoolEmail' => $this->input->post('schoolEmail'),
            'adminEmail' => $this->input->post('adminEmail'),
            'adminMobile' => $this->input->post('adminMobile'),
            'sgc' => $this->input->post('sgc'),
            'category' => $this->input->post('category'),
            'schoolType' => $this->input->post('schoolType'),
            'province' => $this->input->post('province'),
            'city' => $this->input->post('city'),
            'brgy' => $this->input->post('brgy'),
            'sitio' => $this->input->post('sitio'),
            'division_id' => $this->input->post('division_id'),
            'district_id' => $this->input->post('d_id'),

		);

		$this->db->where('recID', $rec_id);
		$result = $this->db->update('schools', $data);

		// Update users table if schoolID changed
		if ($result && $old_school_id && $new_school_id && $old_school_id != $new_school_id) {
			$this->db->where('username', $old_school_id);
			$this->db->update('users', array('username' => $new_school_id));
		}

		// Log audit trail
		if ($result) {
			$this->log_audit_trail('UPDATE', 'schools', $new_school_id, $old_record, $data);
		}

		return $result;
}

public function update_district_id()
{
    $tables = ['tana', 'sbm_ta', 'sbm','tana_summary'];

    $this->db->trans_start();

    foreach ($tables as $table) {
        $this->db->where('school_id', $this->input->post('old_schoolID'));
        $this->db->update($table, [
            'district' => $this->input->post('d_id'),
            'division' => $this->input->post('division_id')
        ]);
    }

    $this->db->trans_complete();

    if ($this->db->trans_status() === FALSE) {
        return false;
    }

    return true;
}

public function sgc_count($c)
	{
$this->db->where('sgc', $c);
$this->db->where('division_id', $this->session->division);
$this->db->from('schools');
return $this->db->count_all_results();
}

public function sgc_count_region($c)
	{
$this->db->where('sgc', $c);
$this->db->where('region_id', $this->session->region);
$this->db->from('schools');
return $this->db->count_all_results();
}

public function sgc_count_district($c)
	{
$this->db->where('sgc', $c);
$this->db->where('district_id', $this->session->district);
$this->db->from('schools');
return $this->db->count_all_results();
}




public function update_request_password(){
    
    $email = $this->input->post('email');
    $user = $this->Common->one_cond_row('users','email',$email);
        
       
    $password = $this->Page_model->random_password();

    $fname = 'Maam/Sir';

                //Email Notification
                $this->load->config('email');
                $this->load->library('email');
                $mail_message = '
                <!doctype html>
                <html>
                <head>
                  <meta charset="utf-8">
                  <meta name="viewport" content="width=device-width,initial-scale=1">
                </head>
                <body style="margin:0; padding:0; background:#f3f5f7; font-family:Arial, Helvetica, sans-serif;">
                  <div style="padding:24px 12px;">
                    <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="max-width:640px; margin:0 auto; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 10px 30px rgba(15,23,42,.10);">
                      
                      <!-- Header -->
                      <tr>
                        <td style="background:linear-gradient(135deg,#a00000,#b90404); padding:22px 26px; color:#ffffff;">
                          <div style="font-size:18px; font-weight:700; letter-spacing:.2px;">DepEd FTAD - Online</div>
                          <div style="font-size:13px; opacity:.95; margin-top:4px;">Password Reset Notification</div>
                        </td>
                      </tr>

                      <!-- Body -->
                      <tr>
                        <td style="padding:26px;">
                          <div style="font-size:15px; color:#111827; line-height:1.6;">
                            <div style="font-size:16px; font-weight:700; margin-bottom:10px;">Dear '.$fname.',</div>

                            <p style="margin:0 0 14px 0;">
                              You have successfully reset your password. Please use the temporary password below to log in.
                            </p>

                            <div style="margin:18px 0; padding:16px; border:1px solid #e5e7eb; border-radius:12px; background:#f9fafb;">
                              <div style="font-size:12px; color:#6b7280; margin-bottom:6px;">Temporary Password</div>
                              <div style="font-size:20px; font-weight:800; color:#dc2626; letter-spacing:.8px;">'.$password.'</div>
                            </div>

                            <p style="margin:0 0 14px 0; color:#374151;">
                              For your security, please change your password immediately after logging in.
                            </p>

                            <div style="margin-top:18px; padding-top:16px; border-top:1px solid #e5e7eb; color:#111827;">
                              <div style="font-weight:700;">Thanks &amp; Regards,</div>
                              <div>DepEd FTAD - Online</div>
                            </div>
                          </div>
                        </td>
                      </tr>

                      <!-- Footer -->
                      <tr>
                        <td style="padding:16px 26px; background:#f9fafb; color:#6b7280; font-size:12px; line-height:1.5;">
                          This email was generated automatically. If you did not request a password reset, please contact your system administrator immediately.
                        </td>
                      </tr>

                    </table>
                  </div>
                </body>
                </html>
                ';

                $this->email->from('no-reply@ftad.depedmis.com', 'FTAD')
                    ->to($email)
                    ->subject('Password Changed')
                    ->message($mail_message);
                $this->email->send();

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $data = array(
        'Password' => $hash,
        'must_change_password' => 1,
    );

    $this->db->where('email', $email);
    return $this->db->update('users', $data);
}

public function user_updates(){
    $data = array(
        'p_id' => $this->input->post('division_id'),
        'd_id' => $this->input->post('d_id'),
    );

    $this->db->where('username', $this->input->post('schoolID'));
    return $this->db->update('users', $data);
}

public function dd_updates()
{
    $data = [
        'division' => $this->input->post('division_id'),
        'district' => $this->input->post('d_id'),
    ];

    $old_school_id = $this->input->post('old_schoolID');
    $new_school_id = $this->input->post('schoolID');
    $tables = ['sgod_action_plan', 'sbm', 'sbm_ta', 'tana'];

    $this->db->trans_start();

    foreach ($tables as $table) {
        // Update division and district using old schoolID
        $this->db->where('school_id', $old_school_id);
        $this->db->update($table, $data);

        // If schoolID changed, update the school_id field in these tables too
        if ($old_school_id && $new_school_id && $old_school_id != $new_school_id) {
            $this->db->where('school_id', $old_school_id);
            $this->db->update($table, array('school_id' => $new_school_id));
        }
    }

    $this->db->trans_complete();

    return $this->db->trans_status();
}

public function tana_summary_del(){
    $this->db->where('school_id',$this->session->username);
    $this->db->delete('tana_summary');
    return true;
}

public function log_audit_trail($action, $table_name, $record_id = null, $old_values = null, $new_values = null)
{
    $data = array(
        'user_id' => isset($this->session->id) ? $this->session->id : null,
        'username' => isset($this->session->username) ? $this->session->username : null,
        'user_position' => isset($this->session->position) ? $this->session->position : null,
        'action' => $action,
        'table_name' => $table_name,
        'record_id' => $record_id,
        'old_values' => $old_values ? json_encode($old_values) : null,
        'new_values' => $new_values ? json_encode($new_values) : null,
        'ip_address' => $this->input->ip_address(),
        'user_agent' => $this->input->user_agent()
    );

    return $this->db->insert('audit_trail', $data);
}

/* Learning Gap Monitoring ------------------------------------------------ */
private function ensure_learning_gap_archive_schema()
{
    if ($this->learning_gap_archive_schema_ready) {
        return;
    }

    $migration = '20260921_learning_gap_fiscal_year_archives';
    $this->db->query("CREATE TABLE IF NOT EXISTS `app_schema_migrations` (
        `migration` VARCHAR(190) NOT NULL,
        `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`migration`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    if ($this->db->where('migration', $migration)->count_all_results('app_schema_migrations') > 0) {
        $this->learning_gap_archive_schema_ready = true;
        return;
    }

    // Serialize the first production requests so two users cannot run the
    // same ALTER TABLE statements while the application is being deployed.
    $lock = $this->db->query(
        "SELECT GET_LOCK(CONCAT('ap_lead_learning_gap_archive_', DATABASE()), 30) AS acquired"
    )->row();
    $lock_acquired = $lock && (int) $lock->acquired === 1;

    if (!$lock_acquired) {
        throw new RuntimeException('The Learning Gap database upgrade is currently busy. Please reload the page.');
    }

    try {
        // Another request may have completed the migration while this request
        // was waiting for the database lock.
        if ($this->db->where('migration', $migration)->count_all_results('app_schema_migrations') > 0) {
            $this->learning_gap_archive_schema_ready = true;
            return;
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `learning_gap_records` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `school_id` VARCHAR(45) NOT NULL,
            `region_id` INT DEFAULT NULL,
            `division_id` INT DEFAULT NULL,
            `district_id` INT DEFAULT NULL,
            `fiscal_year` INT NOT NULL,
            `grade_level` VARCHAR(50) NOT NULL,
            `learning_area` VARCHAR(150) NOT NULL,
            `term` VARCHAR(50) NOT NULL,
            `melc_competency` TEXT NULL,
            `least_learned_competency` TEXT NOT NULL,
            `class_proficiency_level` DECIMAL(5,2) DEFAULT NULL,
            `proficiency_level` VARCHAR(100) DEFAULT NULL,
            `percent_not_meeting` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `learners_assessed` INT UNSIGNED NOT NULL DEFAULT 0,
            `learners_with_gap` INT UNSIGNED NOT NULL DEFAULT 0,
            `learning_difficulty` TEXT NULL,
            `possible_causes` TEXT NULL,
            `intervention_action` TEXT NULL,
            `intervention_status` VARCHAR(50) DEFAULT NULL,
            `remarks` TEXT NULL,
            `created_by` VARCHAR(45) NOT NULL,
            `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_lgr_school` (`school_id`),
            KEY `idx_lgr_division` (`division_id`),
            KEY `idx_lgr_region` (`region_id`),
            KEY `idx_lgr_scope` (`region_id`, `division_id`, `school_id`),
            KEY `idx_learning_gap_fiscal_scope` (`fiscal_year`, `region_id`, `division_id`, `school_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        if (!$this->db->field_exists('fiscal_year', 'learning_gap_records')) {
            $this->db->query('ALTER TABLE learning_gap_records ADD fiscal_year INT NULL AFTER district_id');
        }

        // Records created before archive support inherit the year they were encoded.
        $this->db->query('UPDATE learning_gap_records SET fiscal_year = COALESCE(YEAR(created_at), YEAR(CURRENT_DATE)) WHERE fiscal_year IS NULL OR fiscal_year = 0');

        $column = $this->db->query(
            "SELECT IS_NULLABLE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learning_gap_records'
               AND COLUMN_NAME = 'fiscal_year' LIMIT 1"
        )->row();
        if ($column && strtoupper((string) $column->IS_NULLABLE) === 'YES') {
            $this->db->query('ALTER TABLE learning_gap_records MODIFY fiscal_year INT NOT NULL');
        }

        $index_exists = $this->db->query(
            "SELECT 1 FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'learning_gap_records'
               AND INDEX_NAME = 'idx_learning_gap_fiscal_scope' LIMIT 1"
        )->num_rows() > 0;
        if (!$index_exists) {
            $this->db->query('ALTER TABLE learning_gap_records ADD KEY idx_learning_gap_fiscal_scope (fiscal_year, region_id, division_id, school_id)');
        }

        $this->db->query(
            'INSERT IGNORE INTO app_schema_migrations (migration) VALUES (?)',
            array($migration)
        );
        $this->learning_gap_archive_schema_ready = true;
    } finally {
        $this->db->query("SELECT RELEASE_LOCK(CONCAT('ap_lead_learning_gap_archive_', DATABASE()))");
    }
}

private function learning_gap_year($year = null)
{
    return $year === null ? (int) $this->session->fy : (int) $year;
}

private function apply_learning_gap_scope($scope, $alias = 'lgr')
{
    // District accounts must never receive a wider scope from a caller.
    if ($this->session->position === 'district') {
        $district_id = (int) $this->session->district;
        if ($district_id <= 0) {
            show_error('Your account has no assigned district.', 403);
            return;
        }
        $scope = array('type' => 'district', 'id' => $district_id);
    }
    $prefix = $alias === '' ? '' : $alias . '.';
    if ($scope['type'] === 'school') {
        $this->db->where($prefix . 'school_id', $scope['id']);
    } elseif ($scope['type'] === 'district') {
        $this->db->where($prefix . 'district_id', (int) $scope['id']);
        // A saved submission may retain an old district after a school moves.
        // Only include schools that still belong to the viewer's district.
        $this->db->where(
            $prefix . 'school_id IN (SELECT district_school.schoolID FROM schools district_school'
            . ' WHERE district_school.district_id = ' . (int) $scope['id'] . ')',
            null,
            false
        );
    } elseif ($scope['type'] === 'division') {
        $this->db->where($prefix . 'division_id', $scope['id']);
    } elseif ($scope['type'] === 'region' && (int) $scope['id'] > 0) {
        $this->db->where($prefix . 'region_id', $scope['id']);
    }
}

public function learning_gap_records($scope, $year = null)
{
    $this->ensure_learning_gap_archive_schema();
    $this->ensure_learning_gap_summative_schema();
    $this->db->select('lgr.*, s.schoolName, d.description AS division_name')
        ->from('learning_gap_records lgr')
        ->join('schools s', 's.schoolID = lgr.school_id', 'left')
        ->join('division d', 'd.id = lgr.division_id', 'left')
        ->where('lgr.fiscal_year', $this->learning_gap_year($year));

    $this->apply_learning_gap_scope($scope);

    return $this->db->order_by('lgr.created_at', 'DESC')->get()->result();
}

public function learning_gap_archive_years($scope)
{
    $this->ensure_learning_gap_archive_schema();
    $this->db->select('lgr.fiscal_year, COUNT(*) AS record_count, COUNT(DISTINCT lgr.school_id) AS school_count, COALESCE(SUM(lgr.learners_assessed), 0) AS learners_assessed, COALESCE(SUM(lgr.learners_with_gap), 0) AS learners_with_gap, MAX(lgr.created_at) AS latest_submission')
        ->from('learning_gap_records lgr');
    $this->apply_learning_gap_scope($scope);
    return $this->db->where('lgr.fiscal_year IS NOT NULL', null, false)
        ->group_by('lgr.fiscal_year')
        ->order_by('lgr.fiscal_year', 'DESC')
        ->get()->result();
}

public function learning_gap_school_competency_summary($scope, $grade = '')
{
    $competencies = array();
    $grades = array();
    $school_ids = array();
    foreach ($this->learning_gap_records($scope) as $record) {
        $record_grade = trim((string) $record->grade_level);
        if ($record_grade !== '') $grades[$record_grade] = $record_grade;
        if ($grade !== '' && $record_grade !== $grade) continue;
        foreach (preg_split('/\r\n|\r|\n/', (string) $record->least_learned_competency) as $text) {
            $text = trim($text);
            if ($text === '') continue;
            $key = json_encode(array($record_grade, trim((string) $record->learning_area), $text));
            if (!isset($competencies[$key])) {
                $competencies[$key] = array('text' => $text, 'grade' => $record_grade,
                    'area' => $record->learning_area, 'schools' => array());
            }
            // One school contributes one count, even across repeated records or trimesters.
            $school_id = (string) $record->school_id;
            $competencies[$key]['schools'][$school_id] = true;
            $school_ids[$school_id] = true;
        }
    }
    foreach ($competencies as &$competency) {
        $competency['school_count'] = count($competency['schools']);
        unset($competency['schools']);
    }
    unset($competency);
    $competencies = array_values($competencies);
    usort($competencies, function ($a, $b) {
        return $b['school_count'] <=> $a['school_count']
            ?: strnatcasecmp($a['grade'], $b['grade'])
            ?: strnatcasecmp($a['text'], $b['text']);
    });
    natcasesort($grades);
    return array('competency_rows' => $competencies, 'grade_options' => array_values($grades),
        'reporting_school_count' => count($school_ids));
}

public function learning_gap_summary($scope)
{
    $this->ensure_learning_gap_archive_schema();
    $this->db->select('COUNT(*) AS record_count, COUNT(DISTINCT lgr.school_id) AS school_count, COALESCE(SUM(lgr.learners_assessed), 0) AS learners_assessed, COALESCE(SUM(lgr.learners_with_gap), 0) AS learners_with_gap')
        ->from('learning_gap_records lgr')
        ->where('lgr.fiscal_year', $this->learning_gap_year());
    $this->apply_learning_gap_scope($scope);
    return $this->db->get()->row();
}

/**
 * Assessment totals and weighted class proficiency level for each trimester.
 * CPL is weighted by the number of assessed learners so larger classes have a
 * proportionate contribution to division and regional results.
 */
public function learning_gap_term_performance($scope)
{
    $this->ensure_learning_gap_archive_schema();
    $this->ensure_learning_gap_summative_schema();
    $terms = array(
        'Term 1' => array('term' => 'Term 1', 'record_count' => 0, 'learners_assessed' => 0, 'learners_with_gap' => 0, 'class_proficiency_level' => null, 'cpl_record_count' => 0, 'cpl_summative_1' => null, 'cpl_summative_2' => null, 'cpl_summative_avg' => null, 's1_record_count' => 0, 's2_record_count' => 0),
        'Term 2' => array('term' => 'Term 2', 'record_count' => 0, 'learners_assessed' => 0, 'learners_with_gap' => 0, 'class_proficiency_level' => null, 'cpl_record_count' => 0, 'cpl_summative_1' => null, 'cpl_summative_2' => null, 'cpl_summative_avg' => null, 's1_record_count' => 0, 's2_record_count' => 0),
        'Term 3' => array('term' => 'Term 3', 'record_count' => 0, 'learners_assessed' => 0, 'learners_with_gap' => 0, 'class_proficiency_level' => null, 'cpl_record_count' => 0, 'cpl_summative_1' => null, 'cpl_summative_2' => null, 'cpl_summative_avg' => null, 's1_record_count' => 0, 's2_record_count' => 0),
    );

    $this->db->select('lgr.school_id, lgr.grade_level, lgr.term, lgr.learners_assessed, lgr.learners_with_gap, lgr.class_proficiency_level, lgr.cpl_summative_1, lgr.cpl_summative_2')
        ->from('learning_gap_records lgr')
        ->where_in('lgr.term', array_keys($terms))
        ->where('lgr.fiscal_year', $this->learning_gap_year());
    $this->apply_learning_gap_scope($scope);

    $cpl_weighted_totals = array('Term 1' => 0.0, 'Term 2' => 0.0, 'Term 3' => 0.0);
    $cpl_learner_totals = array('Term 1' => 0, 'Term 2' => 0, 'Term 3' => 0);
    $s1_weighted = array('Term 1' => 0.0, 'Term 2' => 0.0, 'Term 3' => 0.0);
    $s1_learners = array('Term 1' => 0, 'Term 2' => 0, 'Term 3' => 0);
    $s2_weighted = array('Term 1' => 0.0, 'Term 2' => 0.0, 'Term 3' => 0.0);
    $s2_learners = array('Term 1' => 0, 'Term 2' => 0, 'Term 3' => 0);
    $savg_weighted = array('Term 1' => 0.0, 'Term 2' => 0.0, 'Term 3' => 0.0);
    $savg_learners = array('Term 1' => 0, 'Term 2' => 0, 'Term 3' => 0);
    foreach ($this->db->get()->result() as $record) {
        $term = (string) $record->term;
        $assessed = (int) $record->learners_assessed;
        $terms[$term]['record_count']++;
        $terms[$term]['learners_assessed'] += $assessed;
        $terms[$term]['learners_with_gap'] += (int) $record->learners_with_gap;
        if ($record->class_proficiency_level !== null && $record->class_proficiency_level !== '') {
            $weight = max(1, $assessed);
            $cpl_weighted_totals[$term] += (float) $record->class_proficiency_level * $weight;
            $cpl_learner_totals[$term] += $weight;
            $terms[$term]['cpl_record_count']++;
        }
        $weight = max(1, $assessed);
        // Each record's summative CPL values are pooled with their assessed
        // count so larger classes contribute proportionately at scope level.
        $summative_parts = array();
        if ($record->cpl_summative_1 !== null && $record->cpl_summative_1 !== '') {
            $s1_weighted[$term] += (float) $record->cpl_summative_1 * $weight;
            $s1_learners[$term] += $weight;
            $terms[$term]['s1_record_count']++;
            $summative_parts[] = (float) $record->cpl_summative_1;
        }
        if ($record->cpl_summative_2 !== null && $record->cpl_summative_2 !== '') {
            $s2_weighted[$term] += (float) $record->cpl_summative_2 * $weight;
            $s2_learners[$term] += $weight;
            $terms[$term]['s2_record_count']++;
            $summative_parts[] = (float) $record->cpl_summative_2;
        }
        if (!empty($summative_parts)) {
            $savg_weighted[$term] += (array_sum($summative_parts) / count($summative_parts)) * $weight;
            $savg_learners[$term] += $weight;
        }
    }

    foreach ($terms as $term => &$row) {
        if ($cpl_learner_totals[$term] > 0) {
            $row['class_proficiency_level'] = round(
                $cpl_weighted_totals[$term] / $cpl_learner_totals[$term],
                2
            );
        }
        if ($s1_learners[$term] > 0) {
            $row['cpl_summative_1'] = round($s1_weighted[$term] / $s1_learners[$term], 2);
        }
        if ($s2_learners[$term] > 0) {
            $row['cpl_summative_2'] = round($s2_weighted[$term] / $s2_learners[$term], 2);
        }
        if ($savg_learners[$term] > 0) {
            $row['cpl_summative_avg'] = round($savg_weighted[$term] / $savg_learners[$term], 2);
        }
        $row = (object) $row;
    }
    unset($row);

    return array_values($terms);
}

/**
 * Produces an in-system thematic analysis of intervention and remarks text.
 * This keeps school-submitted text inside the application and groups common
 * planning themes using a transparent education-focused keyword taxonomy.
 */
public function learning_gap_thematic_analysis($scope, $limit = 6)
{
    $this->ensure_learning_gap_archive_schema();
    $catalog = array(
        'Remediation and academic support' => array(
            'remedial', 'remediation', 'tutorial', 'tutoring', 'reteach', 're-teach',
            'catch-up', 'reading program', 'literacy', 'numeracy', 'learning support',
            'learner support', 'enrichment', 'intervention class', 'aral'
        ),
        'Teacher coaching and capacity building' => array(
            'teacher training', 'training', 'workshop', 'seminar', 'lac', 'inset',
            'coaching', 'mentoring', 'peer coaching', 'capacity building',
            'professional development', 'instructional strategy', 'pedagogy'
        ),
        'Assessment and progress monitoring' => array(
            'assessment', 'assess', 'monitor', 'progress', 'evaluate', 'evaluation',
            'test analysis', 'item analysis', 'diagnostic', 'formative', 'tracking',
            'mastery', 'feedback'
        ),
        'Learning resources and technology' => array(
            'learning material', 'instructional material', 'module', 'worksheet',
            'resource', 'book', 'library', 'computer', 'internet', 'ict',
            'technology', 'device', 'digital'
        ),
        'Parent and community engagement' => array(
            'parent', 'guardian', 'family', 'community', 'stakeholder', 'barangay',
            'home visit', 'home visitation', 'pta', 'lgu', 'partnership'
        ),
        'Learner wellbeing and inclusion' => array(
            'mental health', 'psychosocial', 'wellbeing', 'well-being', 'counseling',
            'guidance', 'inclusive', 'inclusion', 'sped', 'sned', 'disability',
            'special needs', 'safe space', 'child protection'
        ),
        'Attendance and learner engagement' => array(
            'attendance', 'absent', 'absenteeism', 'dropout', 'drop-out',
            'participation', 'engagement', 'motivation', 'study habit', 'retention'
        ),
        'Implementation and coordination' => array(
            'implement', 'implementation', 'to be implemented', 'planned', 'ongoing',
            'schedule', 'timeline', 'coordinate', 'coordination', 'follow-up',
            'follow up', 'sustain', 'continue', 'strengthen'
        ),
    );
    $themes = array();
    foreach ($catalog as $name => $keywords) {
        $themes[$name] = array(
            'theme' => $name,
            'record_count' => 0,
            'school_ids' => array(),
            'intervention_count' => 0,
            'remarks_count' => 0,
            'examples' => array(),
        );
    }

    $this->db->select('lgr.school_id, lgr.intervention_action, lgr.remarks')
        ->from('learning_gap_records lgr')
        ->where('lgr.fiscal_year', $this->learning_gap_year());
    $this->apply_learning_gap_scope($scope);

    $source_count = 0;
    $source_schools = array();
    $unclassified_count = 0;
    foreach ($this->db->get()->result() as $record) {
        $fields = array(
            'intervention' => trim((string) $record->intervention_action),
            'remarks' => trim((string) $record->remarks),
        );
        if ($fields['intervention'] === '' && $fields['remarks'] === '') {
            continue;
        }
        $source_count++;
        $source_schools[(string) $record->school_id] = true;
        $record_themes = array();

        foreach ($fields as $field => $text) {
            if ($text === '') {
                continue;
            }
            $normalized = mb_strtolower(preg_replace('/\s+/u', ' ', $text), 'UTF-8');
            foreach ($catalog as $name => $keywords) {
                $matched = false;
                foreach ($keywords as $keyword) {
                    if (mb_strpos($normalized, $keyword, 0, 'UTF-8') !== false) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    continue;
                }
                $themes[$name][$field . '_count']++;
                $themes[$name]['school_ids'][(string) $record->school_id] = true;
                $record_themes[$name] = true;
                if (count($themes[$name]['examples']) < 2 && !in_array($text, $themes[$name]['examples'], true)) {
                    $themes[$name]['examples'][] = mb_strlen($text, 'UTF-8') > 150
                        ? mb_substr($text, 0, 147, 'UTF-8') . '...'
                        : $text;
                }
            }
        }

        if (empty($record_themes)) {
            $unclassified_count++;
        } else {
            foreach (array_keys($record_themes) as $name) {
                $themes[$name]['record_count']++;
            }
        }
    }

    $themes = array_values(array_filter($themes, function ($theme) {
        return $theme['record_count'] > 0;
    }));
    foreach ($themes as &$theme) {
        $theme['school_count'] = count($theme['school_ids']);
        unset($theme['school_ids']);
        $theme = (object) $theme;
    }
    unset($theme);
    usort($themes, function ($left, $right) {
        return $right->record_count <=> $left->record_count
            ?: $right->school_count <=> $left->school_count
            ?: strcasecmp($left->theme, $right->theme);
    });

    return array(
        'themes' => array_slice($themes, 0, max(1, (int) $limit)),
        'source_count' => $source_count,
        'school_count' => count($source_schools),
        'unclassified_count' => $unclassified_count,
    );
}

/** Overall CPL and learner-gap percentage for every division in a region. */
public function learning_gap_division_performance($region_id)
{
    $this->ensure_learning_gap_archive_schema();
    $rows = array();
    foreach ($this->regional_divisions($region_id) as $division) {
        $rows[(int) $division->id] = array(
            'division_id' => (int) $division->id,
            'division_name' => (string) $division->description,
            'record_count' => 0,
            'learners_assessed' => 0,
            'learners_with_gap' => 0,
            'class_proficiency_level' => null,
            'cpl_record_count' => 0,
            'cpl_weighted_total' => 0.0,
            'cpl_learner_total' => 0,
        );
    }

    $records = $this->db
        ->select('division_id, school_id, grade_level, learners_assessed, learners_with_gap, class_proficiency_level')
        ->where('region_id', (int) $region_id)
        ->where('fiscal_year', $this->learning_gap_year())
        ->get('learning_gap_records')
        ->result();
    foreach ($records as $record) {
        $division_id = (int) $record->division_id;
        if (!isset($rows[$division_id])) {
            continue;
        }
        $assessed = (int) $record->learners_assessed;
        $rows[$division_id]['record_count']++;
        $rows[$division_id]['learners_assessed'] += $assessed;
        $rows[$division_id]['learners_with_gap'] += (int) $record->learners_with_gap;
        if ($record->class_proficiency_level !== null && $record->class_proficiency_level !== '') {
            $weight = max(1, $assessed);
            $rows[$division_id]['cpl_weighted_total'] += (float) $record->class_proficiency_level * $weight;
            $rows[$division_id]['cpl_learner_total'] += $weight;
            $rows[$division_id]['cpl_record_count']++;
        }
    }

    foreach ($rows as &$row) {
        if ($row['cpl_learner_total'] > 0) {
            $row['class_proficiency_level'] = round($row['cpl_weighted_total'] / $row['cpl_learner_total'], 2);
        }
        unset($row['cpl_weighted_total'], $row['cpl_learner_total']);
        $row = (object) $row;
    }
    unset($row);

    return array_values($rows);
}

public function learning_gap_division_summary($region_id)
{
    $this->ensure_learning_gap_archive_schema();
    $this->db->select('d.id AS division_id, d.description AS division_name, COUNT(lgr.id) AS record_count, COUNT(DISTINCT lgr.school_id) AS school_count, (SELECT COUNT(*) FROM schools school_totals WHERE school_totals.division_id = d.id) AS total_school_count, COALESCE(SUM(lgr.learners_assessed), 0) AS learners_assessed, COALESCE(SUM(lgr.learners_with_gap), 0) AS learners_with_gap')
        ->from('division d')
        ->join('learning_gap_records lgr', 'lgr.division_id = d.id AND lgr.fiscal_year = ' . (int) $this->learning_gap_year(), 'left')
        ->where('d.region_id', (int) $region_id)
        ->group_by('d.id, d.description')
        ->order_by('learners_with_gap', 'DESC');
    return $this->db->get()->result();
}

/**
 * Ranks competencies by the number of submitted school records that selected
 * them. A competency selected by the same school in separate submissions is
 * counted for each submission and the distinct-school count is shown as well.
 */
public function learning_gap_competency_ranking($division_id, $learning_area = '', $term = '')
{
    $this->ensure_learning_gap_archive_schema();
    $records = $this->db->select('id, school_id, grade_level, learning_area, term, least_learned_competency')
        ->where('division_id', (int) $division_id)
        ->where('fiscal_year', $this->learning_gap_year())
        ->get('learning_gap_records')->result();
    $ranked = array();

    foreach ($records as $record) {
        if ($learning_area !== '' && $record->learning_area !== $learning_area) {
            continue;
        }
        if ($term !== '' && $record->term !== $term) {
            continue;
        }
        $competencies = array_unique(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $record->least_learned_competency))));
        foreach ($competencies as $competency) {
            $key = json_encode(array($record->grade_level, $record->learning_area, $record->term, $competency));
            if (!isset($ranked[$key])) {
                $ranked[$key] = array(
                    'grade_level' => $record->grade_level,
                    'learning_area' => $record->learning_area,
                    'term' => $record->term,
                    'competency' => $competency,
                    'submission_count' => 0,
                    'school_ids' => array(),
                );
            }
            $ranked[$key]['submission_count']++;
            $ranked[$key]['school_ids'][(string) $record->school_id] = true;
        }
    }

    foreach ($ranked as &$row) {
        $row['school_count'] = count($row['school_ids']);
        unset($row['school_ids']);
    }
    unset($row);

    $ranked = array_values($ranked);
    usort($ranked, function ($left, $right) {
        if ($left['submission_count'] !== $right['submission_count']) {
            return $right['submission_count'] - $left['submission_count'];
        }
        if ($left['school_count'] !== $right['school_count']) {
            return $right['school_count'] - $left['school_count'];
        }
        return strcasecmp($left['competency'], $right['competency']);
    });

    $rank = 0;
    $last_submission_count = null;
    foreach ($ranked as $index => &$row) {
        if ($last_submission_count === null || $row['submission_count'] !== $last_submission_count) {
            $rank = $index + 1;
            $last_submission_count = $row['submission_count'];
        }
        $row['rank'] = $rank;
        $row = (object) $row;
    }
    unset($row);

    return $ranked;
}

/** Learning areas available to a division's school submissions and setup. */
public function division_learning_area_options($division_id)
{
    $areas = $this->db->distinct()->select('learning_area')
        ->where('division_id', (int) $division_id)
        ->order_by('learning_area', 'ASC')
        ->get('learning_area_settings')->result_array();
    return array_map(function ($area) {
        return $area['learning_area'];
    }, $areas);
}

/**
 * School submission status for a division dashboard. A school is considered
 * submitted once it has at least one encoded learning-gap record.
 */
public function learning_gap_school_submission_summary($division_id)
{
    $this->ensure_learning_gap_archive_schema();
    return $this->db
        ->select('s.schoolID, s.schoolName, COUNT(lgr.id) AS record_count, MAX(lgr.created_at) AS latest_submission')
        ->from('schools s')
        ->join('learning_gap_records lgr', 'lgr.school_id = s.schoolID AND lgr.fiscal_year = ' . (int) $this->learning_gap_year(), 'left')
        ->where('s.division_id', (int) $division_id)
        ->group_by('s.schoolID, s.schoolName')
        ->order_by('s.schoolName', 'ASC')
        ->get()
        ->result();
}

public function save_learning_gap_record($school_id)
{
    $this->ensure_learning_gap_proficiency_columns();
    $this->ensure_learning_gap_archive_schema();
    $this->ensure_learning_gap_summative_schema();
    $school = $this->Common->one_cond_row('schools', 'schoolID', $school_id);
    if (!$school) {
        return false;
    }
    $assessed = (int) $this->input->post('learners_assessed', true);
    $with_gap = (int) $this->input->post('learners_with_gap', true);
    $class_proficiency_level = trim((string) $this->input->post('class_proficiency_level', true));
    $proficiency_level = trim((string) $this->input->post('proficiency_level', true));
    // Summative CPL columns are only written while the school has the feature
    // enabled; disabling the feature never wipes values already encoded.
    $summative_enabled = (int) $school->cpl_summative_enabled === 1;
    $cpl_summative_1 = trim((string) $this->input->post('cpl_summative_1', true));
    $cpl_summative_2 = trim((string) $this->input->post('cpl_summative_2', true));
    $selected_competencies = $this->input->post('least_learned_competency', true);
    $least_learned_competency = is_array($selected_competencies)
        ? implode("\n", array_values(array_filter(array_map('trim', $selected_competencies))))
        : trim((string) $selected_competencies);
    $data = array(
        'school_id' => $school_id,
        'region_id' => (int) $school->region_id,
        'division_id' => (int) $school->division_id,
        'district_id' => (int) $school->district_id,
        'fiscal_year' => $this->learning_gap_year(),
        'grade_level' => $this->input->post('grade_level', true),
        'learning_area' => $this->input->post('learning_area', true),
        'term' => $this->input->post('term', true),
        'melc_competency' => null,
        'least_learned_competency' => $least_learned_competency,
        'class_proficiency_level' => $class_proficiency_level === '' ? null : $class_proficiency_level,
        'proficiency_level' => $proficiency_level === '' ? null : $proficiency_level,
        'percent_not_meeting' => $assessed > 0 ? round(($with_gap / $assessed) * 100, 2) : 0,
        'learners_assessed' => $assessed,
        'learners_with_gap' => $with_gap,
        'intervention_action' => $this->input->post('intervention_action', true),
        'intervention_status' => $this->input->post('intervention_status', true),
        'remarks' => $this->input->post('remarks', true),
        'created_by' => (string) $this->session->username,
    );
    if ($summative_enabled) {
        $data['cpl_summative_1'] = $cpl_summative_1 === '' ? null : $cpl_summative_1;
        $data['cpl_summative_2'] = $cpl_summative_2 === '' ? null : $cpl_summative_2;
    }
    $record_id = (int) $this->input->post('record_id', true);
    if ($record_id > 0) {
        return $this->db->where('id', $record_id)->where('school_id', $school_id)
            ->where('fiscal_year', $this->learning_gap_year())
            ->update('learning_gap_records', $data);
    }
    return $this->db->insert('learning_gap_records', $data);
}

/** Keep existing Learning Gap Monitoring databases compatible with new fields. */
private function ensure_learning_gap_proficiency_columns()
{
    if (!$this->db->field_exists('class_proficiency_level', 'learning_gap_records')) {
        $this->db->query('ALTER TABLE learning_gap_records ADD class_proficiency_level DECIMAL(5,2) NULL AFTER least_learned_competency');
    }
    if (!$this->db->field_exists('proficiency_level', 'learning_gap_records')) {
        $this->db->query('ALTER TABLE learning_gap_records ADD proficiency_level VARCHAR(100) NULL AFTER class_proficiency_level');
    }
    // MELC is retained for legacy records, but is no longer captured in entry.
    $this->db->query('ALTER TABLE learning_gap_records MODIFY melc_competency TEXT NULL');
}

private $summative_cpl_schema_ready = false;

/**
 * Summative CPL feature schema:
 *  - schools.cpl_summative_enabled is the per-school toggle (default OFF)
 *  - learning_gap_records.cpl_summative_1 / cpl_summative_2 hold the values
 * Runs once per database via app_schema_migrations.
 */
public function ensure_learning_gap_summative_schema()
{
    if ($this->summative_cpl_schema_ready) {
        return;
    }

    $migration = '20261001_summative_cpl';
    $this->db->query("CREATE TABLE IF NOT EXISTS `app_schema_migrations` (
        `migration` VARCHAR(190) NOT NULL,
        `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`migration`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    if ($this->db->where('migration', $migration)->count_all_results('app_schema_migrations') > 0) {
        $this->summative_cpl_schema_ready = true;
        return;
    }

    $lock = $this->db->query(
        "SELECT GET_LOCK(CONCAT('ap_lead_summative_cpl_', DATABASE()), 30) AS acquired"
    )->row();
    if (!$lock || (int) $lock->acquired !== 1) {
        throw new RuntimeException('The Learning Gap database upgrade is currently busy. Please reload the page.');
    }

    try {
        // Another request may have completed the migration while this request
        // was waiting for the database lock.
        if ($this->db->where('migration', $migration)->count_all_results('app_schema_migrations') > 0) {
            $this->summative_cpl_schema_ready = true;
            return;
        }

        if (!$this->db->field_exists('cpl_summative_enabled', 'schools')) {
            $this->db->query('ALTER TABLE schools ADD cpl_summative_enabled TINYINT(1) NOT NULL DEFAULT 0');
        }
        if (!$this->db->field_exists('cpl_summative_1', 'learning_gap_records')) {
            $this->db->query('ALTER TABLE learning_gap_records ADD cpl_summative_1 DECIMAL(5,2) NULL AFTER class_proficiency_level');
        }
        if (!$this->db->field_exists('cpl_summative_2', 'learning_gap_records')) {
            $this->db->query('ALTER TABLE learning_gap_records ADD cpl_summative_2 DECIMAL(5,2) NULL AFTER cpl_summative_1');
        }

        $this->db->query(
            'INSERT IGNORE INTO app_schema_migrations (migration) VALUES (?)',
            array($migration)
        );
        $this->summative_cpl_schema_ready = true;
    } finally {
        $this->db->query("SELECT RELEASE_LOCK(CONCAT('ap_lead_summative_cpl_', DATABASE()))");
    }
}

/** Whether the school is allowed to encode Summative 1/2 CPL per term. */
public function school_summative_cpl_enabled($school_id)
{
    $this->ensure_learning_gap_summative_schema();
    $school = $this->db->select('cpl_summative_enabled')
        ->where('schoolID', (string) $school_id)
        ->get('schools')->row();
    return $school && (int) $school->cpl_summative_enabled === 1;
}

/** All schools with their Summative CPL flag for the admin toggle page. */
public function summative_cpl_schools()
{
    $this->ensure_learning_gap_summative_schema();
    return $this->db
        ->select('s.schoolID, s.schoolName, s.cpl_summative_enabled, d.description AS division_name, dist.description AS district_name')
        ->from('schools s')
        ->join('division d', 'd.id = s.division_id', 'left')
        ->join('district dist', 'dist.id = s.district_id', 'left')
        ->order_by('d.description', 'ASC')
        ->order_by('s.schoolName', 'ASC')
        ->get()
        ->result();
}

/** Toggle Summative CPL encoding for one school. */
public function set_school_summative_cpl($school_id, $enabled)
{
    $this->ensure_learning_gap_summative_schema();
    return $this->db->where('schoolID', (string) $school_id)
        ->update('schools', array('cpl_summative_enabled' => $enabled ? 1 : 0));
}

public function learning_gap_record($id, $school_id)
{
    $this->ensure_learning_gap_archive_schema();
    return $this->db->where('id', (int) $id)->where('school_id', $school_id)
        ->where('fiscal_year', $this->learning_gap_year())
        ->get('learning_gap_records')->row();
}

public function delete_learning_gap_record($id, $school_id)
{
    $this->ensure_learning_gap_archive_schema();
    return $this->db->where('id', (int) $id)->where('school_id', $school_id)
        ->where('fiscal_year', $this->learning_gap_year())
        ->delete('learning_gap_records');
}

public function learning_area_settings($division_id)
{
    return $this->db->where('division_id', (int) $division_id)
        ->order_by('grade_level', 'ASC')->order_by('learning_area', 'ASC')
        ->get('learning_area_settings')->result();
}

public function regional_divisions($region_id)
{
    return $this->db->where('region_id', (int) $region_id)
        ->order_by('description', 'ASC')->get('division')->result();
}

public function add_learning_area_setting($division_id)
{
    $grade = trim((string) $this->input->post('grade_level', true));
    $area = trim((string) $this->input->post('learning_area', true));
    $exists = $this->db->where('division_id', (int) $division_id)
        ->where('grade_level', $grade)->where('learning_area', $area)
        ->count_all_results('learning_area_settings') > 0;
    if ($exists) {
        return false;
    }
    return $this->db->insert('learning_area_settings', array(
        'division_id' => (int) $division_id,
        'grade_level' => $grade,
        'learning_area' => $area,
        'created_by' => (string) $this->session->username,
    ));
}

public function delete_learning_area_setting($id, $division_id)
{
    $area = $this->learning_area_setting($id, $division_id);
    if (!$area) {
        return false;
    }
    // Learning competencies are maintained as a regional catalog and remain
    // available to other divisions when one division removes this area setting.
    return $this->db->where('id', (int) $id)->where('division_id', (int) $division_id)
        ->delete('learning_area_settings');
}

public function learning_area_setting($id, $division_id)
{
    return $this->db->where('id', (int) $id)->where('division_id', (int) $division_id)
        ->get('learning_area_settings')->row();
}

/**
 * Encoded competency totals for a whole region, keyed by "grade|learning area".
 * The setup screen needs a count beside every configured area, so this answers
 * all of them in one query instead of one per row.
 */
public function learning_competency_counts($region_id)
{
    $this->ensure_learning_competencies_table();
    $rows = $this->db
        ->select('grade_level, learning_area, COUNT(*) AS competency_count, COUNT(DISTINCT term) AS term_count', false)
        ->where('region_id', (int) $region_id)
        ->group_by('grade_level, learning_area')
        ->get('learning_competencies')
        ->result();

    $counts = array();
    foreach ($rows as $row) {
        $counts[$row->grade_level . '|' . $row->learning_area] = array(
            'competency_count' => (int) $row->competency_count,
            'term_count' => (int) $row->term_count,
        );
    }

    return $counts;
}

public function learning_competencies($region_id, $grade_level, $learning_area)
{
    $this->ensure_learning_competencies_table();
    $this->seed_grade_10_ap_term_1_competencies($region_id, $grade_level, $learning_area);
    $this->seed_grade_9_ap_term_1_competencies($region_id, $grade_level, $learning_area);
    $this->seed_grade_8_ap_term_1_competencies($region_id, $grade_level, $learning_area);
    $this->seed_grade_7_ap_term_1_competencies($region_id, $grade_level, $learning_area);
    return $this->db->where('region_id', (int) $region_id)
        ->where('grade_level', $grade_level)->where('learning_area', $learning_area)
        ->order_by('term', 'ASC')->order_by('competency', 'ASC')
        ->get('learning_competencies')->result();
}

/**
 * Initial regional catalog entries for Grade 10 Araling Panlipunan, Term 1.
 * This is idempotent: existing regional entries are retained and never
 * duplicated. The rows remain available to every division in the region.
 */
private function seed_grade_10_ap_term_1_competencies($region_id, $grade_level, $learning_area)
{
    if ((int) $region_id <= 0 || $grade_level !== 'Grade 10' || $learning_area !== 'Araling Panlipunan') {
        return;
    }

    $area = $this->db->select('las.id, las.division_id')
        ->from('learning_area_settings las')
        ->join('division d', 'd.id = las.division_id')
        ->where('d.region_id', (int) $region_id)
        ->where('las.grade_level', $grade_level)
        ->where('las.learning_area', $learning_area)
        ->order_by('las.id', 'ASC')->limit(1)->get()->row();
    if (!$area) {
        return;
    }

    $competencies = array(
        'Natatalakay ang kahalagahan ng kaalaman sa mga kontemporaryong isyu',
        'Nasusuri ang mga sanhi at epekto ng mga suliraning pangkapaligiran ng daigdig',
        'Natatalakay ang mga programa at inisyatiba upang mapangalagaan ang kapaligiran',
        'Nasusuri ang pagkakaiba ng top-down at bottom-up approach sa pagharap sa suliraning pangkapaligiran',
        'Nasusuri ang kahalagahan ng Community-Based Disaster Risk Reduction and Management Approach sa pagtugon sa mga hamon at suliraning pangkapaligiran',
        'Naipaliliwanag ang kalagayang pang-ekonomiya ng bansa at ang mga isyung kinakaharap nito bunga ng globalisasyon',
        'Natataya ang implikasyon ng iba’t ibang suliranin sa paggawa, pamumuhay at sa pag-unlad ng ekonomiya ng bansa'
    );

    foreach ($competencies as $competency) {
        $exists = $this->db->where('region_id', (int) $region_id)
            ->where('grade_level', $grade_level)->where('learning_area', $learning_area)
            ->where('term', 'Term 1')->where('competency', $competency)
            ->count_all_results('learning_competencies') > 0;
        if (!$exists) {
            $this->db->insert('learning_competencies', array(
                'learning_area_setting_id' => (int) $area->id,
                'division_id' => (int) $area->division_id,
                'region_id' => (int) $region_id,
                'grade_level' => $grade_level,
                'learning_area' => $learning_area,
                'competency' => $competency,
                'term' => 'Term 1',
                'created_by' => 'system',
            ));
        }
    }
}

/** Initial regional catalog entries for Grade 9 Araling Panlipunan, Term 1. */
private function seed_grade_9_ap_term_1_competencies($region_id, $grade_level, $learning_area)
{
    if ((int) $region_id <= 0 || $grade_level !== 'Grade 9' || $learning_area !== 'Araling Panlipunan') {
        return;
    }

    $area = $this->db->select('las.id, las.division_id')
        ->from('learning_area_settings las')
        ->join('division d', 'd.id = las.division_id')
        ->where('d.region_id', (int) $region_id)
        ->where('las.grade_level', $grade_level)
        ->where('las.learning_area', $learning_area)
        ->order_by('las.id', 'ASC')->limit(1)->get()->row();
    if (!$area) {
        return;
    }

    $competencies = array(
        'Natatalakay ang mga konsepto at batayan ng pag-aaral ng ekonomiks tungo sa likas-kayang pag-unlad',
        'Natataya ang kahalagahan ng pagsusulong ng likas-kayang pag-unlad bilang mekanismo sa pagtamo ng pambansa at pandaigdigang kaunlaran',
        'Nasusuri ang ugnayan ng alokasyon at sistemang pang-ekonomiya bilang mekanismo sa pagtugon sa hamon ng kakapusan',
        'Natatalakay ang mga salik ng produksiyon at ang implikasyon nito sa pang-araw-araw na pamumuhay',
        'Naipaliliwanag ang mga katangian ng isang entrepreneur at mga organisasyon ng negosyo',
        'Nasusuri ang konsepto at ang mga salik na nakaaapekto sa pagkonsumo',
        'Naipamamalas ang talino sa pagkonsumo sa pamamagitan ng paggamit ng mga katangian ng isang matalinong mamimili',
        'Naisusulong ang mga karapatan at mga tungkulin ng isang mamimili',
        'Natatalakay ang konsepto ng demand',
        'Nasusuri ang mga salik na nakaaapekto sa demand sa pang-araw-araw na pamumuhay',
        'Natatalakay ang konsepto ng suplay',
        'Nasusuri ang mga salik na nakaaapekto sa suplay sa pang-araw-araw na pamumuhay'
    );

    foreach ($competencies as $competency) {
        $exists = $this->db->where('region_id', (int) $region_id)
            ->where('grade_level', $grade_level)->where('learning_area', $learning_area)
            ->where('term', 'Term 1')->where('competency', $competency)
            ->count_all_results('learning_competencies') > 0;
        if (!$exists) {
            $this->db->insert('learning_competencies', array(
                'learning_area_setting_id' => (int) $area->id,
                'division_id' => (int) $area->division_id,
                'region_id' => (int) $region_id,
                'grade_level' => $grade_level,
                'learning_area' => $learning_area,
                'competency' => $competency,
                'term' => 'Term 1',
                'created_by' => 'system',
            ));
        }
    }
}

/** Initial regional catalog entries for Grade 8 Araling Panlipunan, Term 1. */
private function seed_grade_8_ap_term_1_competencies($region_id, $grade_level, $learning_area)
{
    if ((int) $region_id <= 0 || $grade_level !== 'Grade 8' || $learning_area !== 'Araling Panlipunan') {
        return;
    }

    $area = $this->db->select('las.id, las.division_id')
        ->from('learning_area_settings las')
        ->join('division d', 'd.id = las.division_id')
        ->where('d.region_id', (int) $region_id)
        ->where('las.grade_level', $grade_level)
        ->where('las.learning_area', $learning_area)
        ->order_by('las.id', 'ASC')->limit(1)->get()->row();
    if (!$area) {
        return;
    }

    $competencies = array(
        'Nailalarawan ang katangiang pisikal ng daigdig at implikasyon nito sa pamumuhay ng mga tao',
        'Nasusuri ang kalagayang heograpikal ng mga sinaunang kabihasnan sa Asya at iba pang bahagi ng daigdig',
        'Napatutunayan ang kahalagahan ng pakikipag-ugnayan ng mga tao sa pag-unlad ng mga kabihasnan',
        'Natataya ang epekto ng estrukturang panlipunan sa pag-unlad ng pamumuhay ng tao',
        'Naipaliliwanag ang papel ng relihiyon at ibang paniniwala sa paghubog sa pagkakakilanlang kultural ng tao',
        'Napahahalagahan ang interaksiyon ng tao sa kaniyang kapaligiran',
        'Natatalakay ang mahahalagang pangyayari noong ika-15 at ika-16 siglo bago ang panahon ng paggalugad ng mga lupain',
        'Nasusuri ang mga pangyayari at kinahinatnan ng paggalugad at kolonyalismo ng mga Europeo sa mga bagong lupain sa America',
        'Nasusuri ang mga naging unang tugon ng mga Asyano sa panahon ng paggalugad at kolonyalismo'
    );

    foreach ($competencies as $competency) {
        $exists = $this->db->where('region_id', (int) $region_id)
            ->where('grade_level', $grade_level)->where('learning_area', $learning_area)
            ->where('term', 'Term 1')->where('competency', $competency)
            ->count_all_results('learning_competencies') > 0;
        if (!$exists) {
            $this->db->insert('learning_competencies', array(
                'learning_area_setting_id' => (int) $area->id,
                'division_id' => (int) $area->division_id,
                'region_id' => (int) $region_id,
                'grade_level' => $grade_level,
                'learning_area' => $learning_area,
                'competency' => $competency,
                'term' => 'Term 1',
                'created_by' => 'system',
            ));
        }
    }
}

/** Initial regional catalog entries for Grade 7 Araling Panlipunan, Term 1. */
private function seed_grade_7_ap_term_1_competencies($region_id, $grade_level, $learning_area)
{
    if ((int) $region_id <= 0 || $grade_level !== 'Grade 7' || $learning_area !== 'Araling Panlipunan') {
        return;
    }

    $area = $this->db->select('las.id, las.division_id')
        ->from('learning_area_settings las')
        ->join('division d', 'd.id = las.division_id')
        ->where('d.region_id', (int) $region_id)
        ->where('las.grade_level', $grade_level)
        ->where('las.learning_area', $learning_area)
        ->order_by('las.id', 'ASC')->limit(1)->get()->row();
    if (!$area) {
        return;
    }

    $competencies = array(
        'Naipaliliwanag ang mahalagang ginampanan ng katangiang pisikal ng Pilipinas at ng rehiyon sa pagbuo ng sinaunang kasaysayan at kalinangan ng mga mamamayan sa Pilipinas at Timog Silangang Asya',
        'Nasusuri ang heograpiyang pantao ng Timog Silangang Asya batay sa pangkat-etnolinggwistiko, pananampalataya, estrukturang panlipunan, at ugnayang pangkapangyarihan',
        'Naiuugnay ang katangian ng sinaunang lipunan sa pagkakamag-anak, pamilya at kasarian (kinship, family and gender) sa Timog Silangang Asya',
        'Nasusuri ang kalinangang Austronesyano at Imperyong Maritima kaugnay sa pagbuo ng kalinangan ng Pilipinas at Timog Silangang Asya',
        'Naiuugnay ang sinaunang kabihasnan ng Pilipinas sa mga bansa sa Timog Silangang Asya, China, at India',
        'Napahahalagahan ang ugnayan ng heograpiya at sinaunang kasaysayan ng mga bansa sa Timog Silangang Asya',
        'Naipaliliwanag ang konsepto ng kolonyalismo at imperyalismo',
        'Naipaghahambing ang una at ikalawang yugto ng imperyalismong Kanluranin'
    );

    foreach ($competencies as $competency) {
        $exists = $this->db->where('region_id', (int) $region_id)
            ->where('grade_level', $grade_level)->where('learning_area', $learning_area)
            ->where('term', 'Term 1')->where('competency', $competency)
            ->count_all_results('learning_competencies') > 0;
        if (!$exists) {
            $this->db->insert('learning_competencies', array(
                'learning_area_setting_id' => (int) $area->id,
                'division_id' => (int) $area->division_id,
                'region_id' => (int) $region_id,
                'grade_level' => $grade_level,
                'learning_area' => $learning_area,
                'competency' => $competency,
                'term' => 'Term 1',
                'created_by' => 'system',
            ));
        }
    }
}

public function add_learning_competency($region_id, $area)
{
    $this->ensure_learning_competencies_table();
    $competency = trim((string) $this->input->post('competency', true));
    $term = trim((string) $this->input->post('term', true));
    $exists = $this->db->where('region_id', (int) $region_id)
        ->where('grade_level', (string) $area->grade_level)
        ->where('learning_area', (string) $area->learning_area)->where('term', $term)
        ->where('competency', $competency)
        ->count_all_results('learning_competencies') > 0;
    if ($exists) {
        return 'duplicate';
    }
    // Returns FALSE only when the write itself fails, which the caller reports
    // separately so a failed save is never mistaken for a duplicate entry.
    return $this->db->insert('learning_competencies', array(
        // Retained for compatibility with older installations; regional fields
        // below are the source of truth for competency lookup.
        'learning_area_setting_id' => (int) $area->id,
        'division_id' => (int) $area->division_id,
        'region_id' => (int) $region_id,
        'grade_level' => (string) $area->grade_level,
        'learning_area' => (string) $area->learning_area,
        'competency' => $competency,
        'term' => $term,
        'created_by' => (string) $this->session->username,
    ));
}

public function delete_learning_competency($id, $region_id)
{
    $this->ensure_learning_competencies_table();
    return $this->db->where('id', (int) $id)
        ->where('region_id', (int) $region_id)
        ->delete('learning_competencies');
}

public function update_learning_competency_term($id, $region_id)
{
    $this->ensure_learning_competencies_table();
    return $this->db->where('id', (int) $id)
        ->where('region_id', (int) $region_id)
        ->update('learning_competencies', array(
            'term' => trim((string) $this->input->post('term', true)),
        ));
}

private function ensure_learning_competencies_table()
{
    $this->db->query('CREATE TABLE IF NOT EXISTS `learning_competencies` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `learning_area_setting_id` INT UNSIGNED NOT NULL,
        `division_id` INT NOT NULL,
        `region_id` INT NOT NULL DEFAULT 0,
        `grade_level` VARCHAR(50) NOT NULL DEFAULT \'\',
        `learning_area` VARCHAR(150) NOT NULL DEFAULT \'\',
        `competency` VARCHAR(1000) NOT NULL,
        `term` VARCHAR(50) NOT NULL DEFAULT \'All Terms\',
        `created_by` VARCHAR(45) NOT NULL DEFAULT \'system\',
        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_learning_competency_area` (`learning_area_setting_id`),
        KEY `idx_learning_competency_division` (`division_id`),
        KEY `idx_learning_competency_region_grade_area` (`region_id`, `grade_level`, `learning_area`),
        KEY `idx_learning_competency_term` (`term`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    // Installations restored from a dump can arrive without the primary key, so
    // `id` carries neither AUTO_INCREMENT nor a default. Under strict mode every
    // insert then fails, and rows written while strict mode was off all share
    // id = 0, which leaves them impossible to remove or retag. Restore the key
    // before anything else touches the table.
    $id_column = $this->db->query('SELECT EXTRA, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = \'learning_competencies\'
        AND COLUMN_NAME = \'id\'')->row();
    if ($id_column && stripos((string) $id_column->EXTRA, 'auto_increment') === false) {
        // Give every unusable id (0, or one shared by several rows) a fresh value
        // above the highest id already in use so the primary key can be added.
        $this->db->query('SET @learning_competency_id = COALESCE((SELECT MAX(id) FROM learning_competencies), 0)');
        $this->db->query('UPDATE learning_competencies lc
            LEFT JOIN (SELECT id FROM learning_competencies GROUP BY id HAVING COUNT(*) > 1) dupes
                ON dupes.id = lc.id
            SET lc.id = (@learning_competency_id := @learning_competency_id + 1)
            WHERE lc.id = 0 OR dupes.id IS NOT NULL');
        if ((string) $id_column->COLUMN_KEY === '') {
            $this->db->query('ALTER TABLE learning_competencies ADD PRIMARY KEY (id)');
        }
        $this->db->query('ALTER TABLE learning_competencies MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
    }

    $central_columns_added = false;
    if (!$this->db->field_exists('term', 'learning_competencies')) {
        $this->db->query('ALTER TABLE learning_competencies ADD term VARCHAR(50) NOT NULL DEFAULT \'All Terms\' AFTER competency');
        $this->db->query('ALTER TABLE learning_competencies ADD KEY idx_learning_competency_term (term)');
    }
    if (!$this->db->field_exists('region_id', 'learning_competencies')) {
        $this->db->query('ALTER TABLE learning_competencies ADD region_id INT NOT NULL DEFAULT 0 AFTER division_id');
        $central_columns_added = true;
    }
    if (!$this->db->field_exists('grade_level', 'learning_competencies')) {
        $this->db->query('ALTER TABLE learning_competencies ADD grade_level VARCHAR(50) NOT NULL DEFAULT \'\' AFTER region_id');
        $central_columns_added = true;
    }
    if (!$this->db->field_exists('learning_area', 'learning_competencies')) {
        $this->db->query('ALTER TABLE learning_competencies ADD learning_area VARCHAR(150) NOT NULL DEFAULT \'\' AFTER grade_level');
        $central_columns_added = true;
    }
    if ($central_columns_added) {
        $this->db->query('ALTER TABLE learning_competencies ADD KEY idx_learning_competency_region_grade_area (region_id, grade_level, learning_area)');
    }
    // Convert division-scoped legacy rows to their regional catalog key.
    $this->db->query('UPDATE learning_competencies lc
        JOIN learning_area_settings las ON las.id = lc.learning_area_setting_id
        JOIN division d ON d.id = las.division_id
        SET lc.region_id = d.region_id, lc.grade_level = las.grade_level, lc.learning_area = las.learning_area
        WHERE lc.region_id = 0 OR lc.grade_level = \'\' OR lc.learning_area = \'\'');
    // A restore that drops the primary key drops the lookup indexes with it.
    $indexes = array(
        'idx_learning_competency_area' => '(learning_area_setting_id)',
        'idx_learning_competency_division' => '(division_id)',
        'idx_learning_competency_region_grade_area' => '(region_id, grade_level, learning_area)',
        'idx_learning_competency_term' => '(term)',
    );
    foreach ($indexes as $index_name => $index_columns) {
        $index_exists = $this->db->query('SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = \'learning_competencies\'
            AND INDEX_NAME = ?', array($index_name))->num_rows() > 0;
        if (!$index_exists) {
            $this->db->query('ALTER TABLE learning_competencies ADD KEY ' . $index_name . ' ' . $index_columns);
        }
    }
    // Competencies encoded before term-based filtering are usable in every term
    // until the regional user assigns their precise term in the setup screen.
    $this->db->where('term', '')->update('learning_competencies', array('term' => 'All Terms'));
}

public function learning_area_options($division_id, $grade_level)
{
    $areas = $this->db->select('learning_area')->where('division_id', (int) $division_id)
        ->where('grade_level', $grade_level)->order_by('learning_area', 'ASC')
        ->get('learning_area_settings')->result_array();
    return array_map(function ($area) { return $area['learning_area']; }, $areas);
}

public function learning_competency_options($region_id, $grade_level, $learning_area, $term)
{
    $this->ensure_learning_competencies_table();
    $this->seed_grade_10_ap_term_1_competencies($region_id, $grade_level, $learning_area);
    $this->seed_grade_9_ap_term_1_competencies($region_id, $grade_level, $learning_area);
    $this->seed_grade_8_ap_term_1_competencies($region_id, $grade_level, $learning_area);
    $this->seed_grade_7_ap_term_1_competencies($region_id, $grade_level, $learning_area);
    $competencies = $this->db->distinct()->select('lc.competency')
        ->from('learning_competencies lc')
        ->where('lc.region_id', (int) $region_id)
        ->where('lc.grade_level', $grade_level)
        ->where('lc.learning_area', $learning_area)
        ->where_in('lc.term', array($term, 'All Terms'))
        ->order_by('lc.competency', 'ASC')->get()->result_array();
    return array_map(function ($competency) { return $competency['competency']; }, $competencies);
}


public function tana_summary_final(){
    $data = array(
        'stat' => 1
    );

    $this->db->where('school_id', $this->session->username);
    return $this->db->update('tana_summary', $data);
}






    












}
