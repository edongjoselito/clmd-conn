<?php


class Pages extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Deny by default. Public routes must be deliberately listed here;
        // every other controller action requires an authenticated session.
        $method = strtolower((string) $this->router->fetch_method());
        $public_methods = array(
            // The default controller lands on view(), which performs its own
            // guest-only redirect to homepage before rendering any data.
            'view', 'homepage', 'log_in', 'signup', 'signup_email_available', 'forgot_password', 'confirm_signup',
            'lock_user_screen', 'logout',
            'get_provinces', 'get_districts', 'get_district_by_division',
            'data_privacy', 'authors', 'about', 'verify',
            'division_checklist_completed_shared'
        );

        if (!in_array($method, $public_methods, true) && !$this->session->logged_in) {
            show_error('Authentication is required to access this resource.', 401);
        }

        // Accounts issued a temporary password may only change that password
        // or sign out. This guard also protects direct dashboard URLs.
        $password_change_methods = array('password_change_required', 'change_password_user', 'logout');
        if ($this->session->logged_in && (int) $this->session->must_change_password === 1
            && !in_array($method, $password_change_methods, true)) {
            redirect(base_url('password-change-required'));
            return;
        }

        // High-risk endpoints receive an explicit role policy here in addition
        // to their record-level checks below.
        $role_policies = array(
            // Scope dashboards. Each role reaches only its own level; the site
            // root dispatches users here so the URL names the scope on screen.
            'region' => array('region'),
            'division' => array('division', 'division_head', 'ict'),
            'district' => array('district'),
            'school_dashboard' => array('school'),
            'learning_gap_archives' => array('school', 'division', 'division_head', 'region', 'district'),
            'profilelist' => array('admin'),
            'profile_new' => array('admin'),
            'qr' => array('admin'),
            'school_by_district' => array('admin', 'region'),
            'school_new' => array('admin'),
            'schools' => array('admin', 'region', 'division', 'ict'),
            'schools_district' => array('admin', 'region', 'division', 'ict', 'district'),
            'schools_division' => array('admin', 'region', 'division', 'ict'),
            'school_list' => array('admin', 'district'),
            'school_list_region' => array('region'),
            'school_list_division_only' => array('division'),
            // District dashboards link to this scoped submission status list;
            // require_district_scope() below enforces the exact district ID.
            'school_list_division' => array('division', 'district'),
            'district_list_division' => array('admin', 'region'),
            'district_list' => array('division'),
            'division_list' => array('admin', 'region'),
            'report_division_submission' => array('admin', 'region'),
            'report_overall_accomplishments' => array('admin', 'region'),
            'report_sgc' => array('admin', 'region'),
            'sbm_rate_divisions_list' => array('admin', 'region'),
            'district_account' => array('division', 'ict'),
            'update_total_schools' => array('admin', 'region'),
            'action_plan_new' => array('school'),
            'sbm_action_plan' => array('school'),
            'sbm_action_plan_pview' => array('school'),
            'sbm_action_plan_pview_district' => array('district', 'division'),
            'sbm_list' => array('district'),
            'sbm_action_plan_update' => array('school'),
            'action_plan_delete' => array('school'),
            'sbm_checklist' => array('school'),
            'sbm_checklist_update' => array('school'),
            'sbm_checklist_final' => array('school'),
            'checklist_district' => array('district', 'division'),
            'sbm_checklist_pdf' => array('school', 'district', 'division', 'region', 'admin'),
            'tapr_form' => array('school'),
            'tapr_form_update' => array('school'),
            'sbm_ta_final' => array('school'),
            'tana_form' => array('school'),
            'tana_form_update' => array('school'),
            'tana_summary' => array('school'),
            'tana_summary_update' => array('school'),
            'update_tana_summary' => array('school'),
            'final_tana_summary' => array('school'),
            'tana_summary_division' => array('division'),
            'tana_division' => array('division'),
            'tana_division_autogenerate' => array('division'),
            'tana_division_delete' => array('division'),
            'tana_summary_region' => array('region'),
            'tana_region' => array('region'),
            'tana_region_delete' => array('region'),
            'sbm_district_tech' => array('district'),
            'sbm_district_tech_new' => array('district'),
            'sbm_district_tech_edit' => array('district'),
            'sbm_district_tech_del' => array('district'),
            'tapr_admin' => array('district', 'division'),
            'tapr_district_update' => array('district', 'division'),
            'tapr_form_district' => array('district', 'division'),
            'clear_unlock_requests' => array('division'),
            'unlock_requests' => array('division'),
            'unlock_request_view' => array('division'),
            'sbm_ta_unlock' => array('division'),
            'sbm_checklist_unlock' => array('division'),
            'division_setup' => array('division'),
            'signup_district' => array('admin', 'division'),
            'add_school_user' => array('admin', 'division', 'ict'),
            'change_password_user_division' => array('admin', 'division', 'ict'),
            // Per-school Summative CPL feature is controlled by admin (sa) only.
            'summative_cpl_access' => array('admin'),
            'summative_cpl_toggle' => array('admin'),
        );

        if (isset($role_policies[$method])) {
            $position = strtolower(trim((string) $this->session->position));
            if (!$this->session->logged_in || !in_array($position, $role_policies[$method], true)) {
                show_error('You are not authorized to perform this operation.', 403);
            }
        }

        // Legacy SBM modules are not available in the Learning Gap system.
        // This prevents direct URL access after their menus are removed.
        $learning_gap_roles = array('school', 'division', 'division_head', 'region', 'admin', 'district');
        $legacy_sbm_methods = array(
            'district_list', 'school_list_division_only', 'division_checklist_completed_details',
            'division_list', 'school_list_region', 'report_division_submission',
            'report_overall_accomplishments', 'region_checklist_completed_report', 'report_sgc'
        );
        if (in_array($this->session->position, array('admin', 'district'), true)) {
            $legacy_sbm_methods = array_merge($legacy_sbm_methods, array(
                'school_profile_region', 'school_profile_division', 'checklist_district',
                'action_plan_new', 'action_plan_delete', 'update_tana_summary', 'final_tana_summary',
                'division_sgc_details', 'division_sgc_category_printable',
                'division_checklist_completed_printable', 'division_checklist_completed_shared',
                'unlock_requests', 'unlock_request_view', 'clear_unlock_requests'
            ));
        }
        if ($this->session->position === 'district') {
            $legacy_sbm_methods = array_merge($legacy_sbm_methods, array('school_list', 'school_list_division'));
        }
        if ($this->session->logged_in && in_array($this->session->position, $learning_gap_roles, true)
            && (strpos($method, 'sbm_') === 0 || strpos($method, 'tapr_') === 0
                || strpos($method, 'tana_') === 0 || in_array($method, $legacy_sbm_methods, true))) {
            show_error('This legacy SBM feature is not available in the Least Learned Competencies Monitoring system.', 403);
        }
    }

    private function require_post()
    {
        if (strtoupper((string) $this->input->method()) !== 'POST') {
            show_error('This operation requires a POST request.', 405);
        }
    }

    private function require_school_scope($school)
    {
        if (!$school) {
            show_404();
        }

        $position = strtolower(trim((string) $this->session->position));
        $allowed = false;

        if ($position === 'admin') {
            $allowed = true;
        } elseif ($position === 'region') {
            $allowed = (int) $school->region_id === (int) $this->session->region;
        } elseif (in_array($position, array('division', 'division_head', 'ict'), true)) {
            $allowed = (int) $school->division_id === (int) $this->session->division;
        } elseif ($position === 'district') {
            $allowed = (int) $school->district_id === (int) $this->session->district;
        } elseif ($position === 'school') {
            $allowed = (string) $school->schoolID === (string) $this->session->username;
        }

        if (!$allowed) {
            show_error('You are not authorized to access this school record.', 403);
        }

        return $school;
    }

    private function require_division_scope($division)
    {
        if (!$division) {
            show_404();
        }

        $position = strtolower(trim((string) $this->session->position));
        $allowed = $position === 'admin'
            || ($position === 'region' && (int) $division->region_id === (int) $this->session->region)
            || (in_array($position, array('division', 'division_head', 'ict'), true)
                && (int) $division->id === (int) $this->session->division);

        if (!$allowed) {
            show_error('You are not authorized to access this division.', 403);
        }
        return $division;
    }

    private function require_district_scope($district)
    {
        if (!$district) {
            show_404();
        }

        $division = $this->Page_model->one_cond_row('division', 'id', (int) $district->division_id);
        $position = strtolower(trim((string) $this->session->position));
        $allowed = $position === 'admin'
            || ($position === 'region' && $division
                && (int) $division->region_id === (int) $this->session->region)
            || (in_array($position, array('division', 'division_head', 'ict'), true)
                && (int) $district->division_id === (int) $this->session->division)
            || ($position === 'district' && (int) $district->id === (int) $this->session->district);

        if (!$allowed) {
            show_error('You are not authorized to access this district.', 403);
        }
        return $district;
    }

    private function school_for_record($table, $record_id)
    {
        $allowed_tables = array('sgod_action_plan', 'sbm', 'sbm_ta', 'tana');
        if (!in_array($table, $allowed_tables, true)) {
            show_error('Invalid record type.', 400);
        }

        $record = $this->db->where('id', (int) $record_id)->get($table)->row();
        if (!$record || empty($record->school_id)) {
            show_404();
        }

        $school = $this->Page_model->one_cond_row('schools', 'schoolID', $record->school_id);
        $this->require_school_scope($school);
        return array($record, $school);
    }

    private function login_throttle_file($username)
    {
        $key = hash('sha256', $this->input->ip_address() . '|' . mb_strtolower(trim((string) $username), 'UTF-8'));
        return APPPATH . 'cache/login-rate-' . $key . '.json';
    }

    private function login_throttle_state($username)
    {
        $file = $this->login_throttle_file($username);
        $state = is_file($file) ? json_decode((string) @file_get_contents($file), true) : null;
        if (!is_array($state) || empty($state['first_attempt']) || time() - (int) $state['first_attempt'] > 900) {
            return array('attempts' => 0, 'first_attempt' => time());
        }
        return $state;
    }

    private function login_is_rate_limited($username)
    {
        $state = $this->login_throttle_state($username);
        return (int) $state['attempts'] >= 5;
    }

    private function record_login_failure($username)
    {
        $state = $this->login_throttle_state($username);
        $state['attempts'] = (int) $state['attempts'] + 1;
        @file_put_contents($this->login_throttle_file($username), json_encode($state), LOCK_EX);
        log_message('error', 'Failed login for account identifier hash '
            . hash('sha256', mb_strtolower(trim((string) $username), 'UTF-8'))
            . ' from ' . $this->input->ip_address());
    }

    private function clear_login_failures($username)
    {
        $file = $this->login_throttle_file($username);
        if (is_file($file)) {
            @unlink($file);
        }
    }

    private function is_user_manager()
    {
        return in_array($this->session->position, array('admin', 'division', 'ict', 'district'), true);
    }

    private function is_division_user_manager()
    {
        return in_array($this->session->position, array('division', 'ict'), true);
    }

    private function require_user_manager()
    {
        if (!$this->session->logged_in || !$this->is_user_manager()) {
            show_error('You are not authorized to manage users.', 403);
        }
    }

    private function apply_district_user_scope()
    {
        $scope = $this->district_learning_gap_scope();
        $this->db->where('d_id', $scope['id']);
        $this->db->where('p_id', (int) $this->session->division);
        $this->db->where_in('position', array('district', 'school'));
    }

    private function require_division_settings_access()
    {
        if (!$this->session->logged_in || $this->session->position !== 'division') {
            show_error('Only division users can access division settings.', 403);
        }
    }

    private function require_regional_learning_area_access()
    {
        if (!$this->session->logged_in || $this->session->position !== 'region') {
            show_error('Only regional users can manage learning areas and competencies.', 403);
        }
    }

    private function regional_learning_area_division_id($requested_id = 0)
    {
        $divisions = $this->Page_model->regional_divisions((int) $this->session->region);
        if (empty($divisions)) {
            show_error('No divisions are assigned to your regional account.', 403);
        }
        $requested_id = (int) $requested_id;
        foreach ($divisions as $division) {
            if ($requested_id === (int) $division->id) {
                return $requested_id;
            }
        }
        return (int) $divisions[0]->id;
    }

    private function require_division_dashboard_access()
    {
        if (!$this->session->logged_in || $this->session->position !== 'division') {
            show_error('Only division users can access this page.', 403);
        }
    }

    private function require_region_dashboard_access()
    {
        if (!$this->session->logged_in || $this->session->position !== 'region') {
            show_error('Only regional users can access this page.', 403);
        }
    }

    private function build_checklist_report_groups($records)
    {
        $groups = array();

        foreach ($records as $record) {
            $district_name = trim((string) (isset($record->district_name) ? $record->district_name : ''));
            $division_name = trim((string) (isset($record->division_name) ? $record->division_name : ''));
            $district_id = isset($record->district_id) ? (string) $record->district_id : '';
            $division_id = isset($record->division_id) ? (string) $record->division_id : '';

            if ($district_name === '') {
                $district_name = 'Unassigned District';
            }

            if ($division_name === '') {
                $division_name = 'Division';
            }

            $group_key = $division_id . ':' . $district_id . ':' . mb_strtolower($district_name, 'UTF-8');

            if (!isset($groups[$group_key])) {
                $groups[$group_key] = array(
                    'district_name' => $district_name,
                    'division_name' => $division_name,
                    'district_id' => $district_id,
                    'division_id' => $division_id,
                    'records' => array()
                );
            }

            $groups[$group_key]['records'][] = $record;
        }

        foreach ($groups as &$group) {
            usort($group['records'], function ($left, $right) {
                return strcasecmp(
                    trim((string) $left->schoolName),
                    trim((string) $right->schoolName)
                );
            });
        }
        unset($group);

        $group_list = array_values($groups);
        usort($group_list, function ($left, $right) {
            $district_compare = strcasecmp($left['district_name'], $right['district_name']);

            if ($district_compare !== 0) {
                return $district_compare;
            }

            return strcasecmp($left['division_name'], $right['division_name']);
        });

        return $group_list;
    }

    private function build_checklist_filter_options($records, $id_field, $name_field, $fallback_label)
    {
        $options = array();

        foreach ($records as $record) {
            $option_id = trim((string) (isset($record->{$id_field}) ? $record->{$id_field} : ''));
            $option_name = trim((string) (isset($record->{$name_field}) ? $record->{$name_field} : ''));

            if ($option_name === '') {
                $option_name = $fallback_label;
            }

            $option_key = $option_id . ':' . mb_strtolower($option_name, 'UTF-8');

            if (!isset($options[$option_key])) {
                $options[$option_key] = array(
                    'id' => $option_id,
                    'name' => $option_name,
                );
            }
        }

        $option_list = array_values($options);

        usort($option_list, function ($left, $right) {
            return strcasecmp($left['name'], $right['name']);
        });

        return $option_list;
    }

    private function filter_checklist_records($records, $field_name, $selected_value)
    {
        if ($selected_value === '') {
            return $records;
        }

        return array_values(array_filter($records, function ($record) use ($field_name, $selected_value) {
            return trim((string) (isset($record->{$field_name}) ? $record->{$field_name} : '')) === $selected_value;
        }));
    }

    private function get_application_signing_secret()
    {
        $secret = trim((string) config_item('encryption_key'));

        if (strlen($secret) < 32) {
            log_message('error', 'APP_ENCRYPTION_KEY is missing or too short for signed links.');
            show_error('Secure link generation is not configured.', 503);
        }
        return $secret;
    }

    private function build_division_checklist_share_token($division_id, $fy, $expires)
    {
        $payload = trim((string) $division_id) . '|' . trim((string) $fy) . '|' . (int) $expires;

        return hash_hmac('sha256', $payload, $this->get_application_signing_secret());
    }

    private function build_profile_verification_token($profile)
    {
        $payload = 'profile|' . (int) $profile->id . '|' . (string) $profile->docNo
            . '|' . (string) $profile->dateReleased;
        return hash_hmac('sha256', $payload, $this->get_application_signing_secret());
    }

    private function build_division_checklist_share_url($division_id, $fy, $selected_filter = '')
    {
        $expires = time() + 604800;
        $query = array(
            'expires' => $expires,
            'token' => $this->build_division_checklist_share_token($division_id, $fy, $expires),
        );
        $selected_filter = trim((string) $selected_filter);

        if ($selected_filter !== '') {
            $query['district_id'] = $selected_filter;
        }

        return base_url(
            'Pages/division_checklist_completed_shared/'
            . rawurlencode((string) $division_id)
            . '/'
            . rawurlencode((string) $fy)
        ) . '?' . http_build_query($query);
    }

    private function validate_division_checklist_share_request($division_id, $fy)
    {
        $token = trim((string) $this->input->get('token', true));
        $expires = (int) $this->input->get('expires', true);

        if ($division_id === '' || $fy === '' || $token === '' || $expires < time() || $expires > time() + 691200) {
            show_error('This shared checklist link is invalid.', 403);
        }

        $expected_token = $this->build_division_checklist_share_token($division_id, $fy, $expires);

        if (!hash_equals($expected_token, $token)) {
            show_error('This shared checklist link is invalid.', 403);
        }
    }

    private function build_division_checklist_completed_report_data($division_id, $fy, $options = array())
    {
        $division_id = trim((string) $division_id);
        $fy = trim((string) $fy);
        $print_mode = !empty($options['print_mode']);
        $share_mode = !empty($options['share_mode']);
        $share_expires = $share_mode ? (int) $this->input->get('expires', true) : 0;
        $share_token = $share_mode
            ? $this->build_division_checklist_share_token($division_id, $fy, $share_expires)
            : '';
        $records = $this->Page_model->division_completed_checklist_report_rows($division_id, $fy);
        $filter_options = $this->build_checklist_filter_options(
            $records,
            'district_id',
            'district_name',
            'Unassigned District'
        );
        $selected_filter = trim((string) $this->input->get('district_id', true));
        $valid_filter_ids = array_map(function ($option) {
            return (string) $option['id'];
        }, $filter_options);

        if ($selected_filter !== '' && !in_array($selected_filter, $valid_filter_ids, true)) {
            $selected_filter = '';
        }

        $filtered_records = $this->filter_checklist_records($records, 'district_id', $selected_filter);
        $query_string = $selected_filter !== ''
            ? '?district_id=' . rawurlencode($selected_filter)
            : '';
        $default_title = $print_mode
            ? 'Division Checklist Completion Printable Version'
            : 'Division Checklist Completion Report';
        $default_hero_description = $print_mode
            ? 'Printable version of finalized Self-Assessment Checklist submissions in your division, grouped by district and arranged alphabetically by school.'
            : 'Review finalized Self-Assessment Checklist submissions in your division, grouped by district and arranged alphabetically by school.';
        $default_back_url = $print_mode
            ? base_url('Pages/division_checklist_completed_details') . $query_string
            : base_url();
        $default_back_label = $print_mode ? 'Back to Interactive Report' : 'Back to Dashboard';
        $default_filter_action_url = $print_mode
            ? base_url('Pages/division_checklist_completed_printable')
            : base_url('Pages/division_checklist_completed_details');
        $default_printable_url = $print_mode
            ? ''
            : base_url('Pages/division_checklist_completed_printable') . $query_string;
        $default_shareable_url = (!$print_mode && !$share_mode)
            ? $this->build_division_checklist_share_url($division_id, $fy, $selected_filter)
            : '';
        $default_filter_reset_url = $default_filter_action_url;
        $default_filter_hidden_fields = array();

        if ($share_mode) {
            $default_title = 'Shared Division Checklist Completion Report';
            $default_hero_description = '';
            $default_back_url = '';
            $default_back_label = '';
            $default_filter_action_url = base_url(
                'Pages/division_checklist_completed_shared/'
                . rawurlencode((string) $division_id)
                . '/'
                . rawurlencode((string) $fy)
            );
            $default_filter_reset_url = $default_filter_action_url . '?' . http_build_query(array(
                'expires' => $share_expires,
                'token' => $share_token,
            ));
            $default_filter_hidden_fields = array(
                'expires' => $share_expires,
                'token' => $share_token,
            );
            $default_printable_url = '';
        }

        return array(
            'title' => array_key_exists('title', $options)
                ? (string) $options['title']
                : $default_title,
            'hero_title' => array_key_exists('hero_title', $options)
                ? (string) $options['hero_title']
                : 'Completed Self-Assessment Checklist Report',
            'hero_description' => array_key_exists('hero_description', $options)
                ? (string) $options['hero_description']
                : $default_hero_description,
            'report_scope' => 'division',
            'report_badge' => array_key_exists('report_badge', $options)
                ? (string) $options['report_badge']
                : 'Fiscal Year ' . $fy,
            'back_url' => array_key_exists('back_url', $options)
                ? (string) $options['back_url']
                : $default_back_url,
            'back_label' => array_key_exists('back_label', $options)
                ? (string) $options['back_label']
                : $default_back_label,
            'records' => $filtered_records,
            'district_groups' => $this->build_checklist_report_groups($filtered_records),
            'filter_title' => 'Filter By District',
            'filter_description' => 'Select one district to narrow the completed checklist report for this division.',
            'filter_action_url' => array_key_exists('filter_action_url', $options)
                ? (string) $options['filter_action_url']
                : $default_filter_action_url,
            'filter_reset_url' => array_key_exists('filter_reset_url', $options)
                ? (string) $options['filter_reset_url']
                : $default_filter_reset_url,
            'filter_param' => 'district_id',
            'filter_placeholder' => 'All Districts',
            'filter_icon' => 'mdi-map-marker-multiple',
            'filter_options' => $filter_options,
            'filter_hidden_fields' => array_key_exists('filter_hidden_fields', $options) && is_array($options['filter_hidden_fields'])
                ? $options['filter_hidden_fields']
                : $default_filter_hidden_fields,
            'selected_filter' => $selected_filter,
            'print_mode' => $print_mode,
            'printable_url' => array_key_exists('printable_url', $options)
                ? (string) $options['printable_url']
                : $default_printable_url,
            'share_mode' => $share_mode,
            'shareable_url' => array_key_exists('shareable_url', $options)
                ? (string) $options['shareable_url']
                : $default_shareable_url,
        );
    }

    private function get_division_checklist_completed_report_data($print_mode = false)
    {
        return $this->build_division_checklist_completed_report_data(
            $this->session->division,
            $this->session->fy,
            array(
                'print_mode' => $print_mode,
            )
        );
    }

    private function get_rate_indicator_context($question_key)
    {
        $indicator_number = (int) preg_replace('/\D+/', '', (string) $question_key);
        $indicator = $indicator_number > 0
            ? $this->Page_model->one_cond_row('sbm_sub_indicator', 'i_no', $indicator_number)
            : null;
        $principle = ($indicator && !empty($indicator->priciple_id))
            ? $this->Page_model->one_cond_row('sbm_indicator', 'id', $indicator->priciple_id)
            : null;

        return array(
            'rate_indicator_description' => ($indicator && !empty($indicator->description))
                ? $indicator->description
                : '',
            'rate_indicator_principle' => ($principle && !empty($principle->indicator))
                ? $principle->indicator
                : '',
        );
    }

    private function get_managed_user($id)
    {
        $user = $this->Page_model->one_cond_row('users', 'id', $id);

        if (!$user) {
            show_404();
        }

        if ($this->is_division_user_manager() && (string) $user->p_id !== (string) $this->session->division) {
            show_error('You can only manage users under your division.', 403);
        }

        if ($this->session->position === 'district') {
            $scope = $this->district_learning_gap_scope();
            if ((int) $user->d_id !== $scope['id']
                || (int) $user->p_id !== (int) $this->session->division
                || !$this->is_allowed_managed_position($user->position)) {
                show_error('You can only manage school and district users in your assigned district.', 403);
            }
        }
        return $user;
    }

    private function is_allowed_managed_position($position)
    {
        if ($this->session->position === 'admin') {
            return true;
        }

        if ($this->session->position === 'district') {
            return in_array($position, array('district', 'school'), true);
        }
        $position_record = $this->Page_model->one_cond_row('position', 'pos', $position);

        return $position_record && !in_array($position, array('admin', 'region', 'division'), true);
    }

    private function validate_managed_district($district_id)
    {
        if ($this->session->position === 'district') {
            $scope = $this->district_learning_gap_scope();
            if ((int) $district_id !== $scope['id']) {
                show_error('You can only manage users in your assigned district.', 403);
            }
            return;
        }
        if (!$this->is_division_user_manager() || empty($district_id)) {
            return;
        }

        $district = $this->Page_model->one_cond_row('district', 'id', $district_id);

        if (!$district || (string) $district->division_id !== (string) $this->session->division) {
            show_error('The selected district is not under your division.', 403);
        }
    }


    /**
     * SBM indicator lists shared by every scope dashboard.
     */
    private function dashboard_indicator_data()
    {
        $data = array();
        $data['sbm'] = $this->Common->no_cond('sbm_indicator');
        $data['sbm_sub'] = $this->Common->no_cond('sbm_sub_indicator');
        $data['sbm_sub_by_principle'] = array();

        foreach ($data['sbm_sub'] as $indicator) {
            $data['sbm_sub_by_principle'][(string) $indicator->priciple_id][] = $indicator;
        }

        $data['title'] = "Dashboard";

        return $data;
    }

    private function dashboard_indicator_numbers($data)
    {
        return array_map(function ($indicator) {
            return (int) $indicator->i_no;
        }, $data['sbm_sub']);
    }

    /**
     * Regional figures. The administrator dashboard requests management counts
     * only, without loading legacy SBM assessments or governance statistics.
     */
    private function region_scope_data($include_sbm = true)
    {
        $data = $include_sbm ? $this->dashboard_indicator_data() : array();

        $region_id = (int) $this->session->region;
        $setup_summary = $this->Page_model->region_division_setup_summary($region_id);

        $data['division_count'] = $this->Page_model->region_division_count($region_id);
        $data['district_count'] = $this->Page_model->region_district_count($region_id);
        $data['registered_school_count'] = $this->Page_model->region_school_count($region_id);
        $data['user_count'] = $this->Page_model->region_user_count($region_id);
        if ($include_sbm) {
            $indicator_numbers = $this->dashboard_indicator_numbers($data);
            $data['sgc_counts'] = $this->Page_model->region_sgc_counts($region_id);
            $data['sbm_rate_counts'] = $this->Page_model->region_sbm_rate_counts(
                $region_id,
                $this->session->fy,
                $indicator_numbers
            );
            $data['completed_checklist_count'] = $this->Page_model->region_sbm_completed_count(
                $region_id,
                $this->session->fy
            );
        }
        $data['encoded_total_schools'] = isset($setup_summary['encoded_total_schools'])
            ? (int) $setup_summary['encoded_total_schools']
            : 0;
        $data['configured_division_count'] = isset($setup_summary['configured_division_count'])
            ? (int) $setup_summary['configured_division_count']
            : 0;
        $data['signup_percentage'] = $data['encoded_total_schools'] > 0
            ? ($data['registered_school_count'] / $data['encoded_total_schools']) * 100
            : 0;
        $data['checklist_completion_percentage'] = $include_sbm && $data['encoded_total_schools'] > 0
            ? ($data['completed_checklist_count'] / $data['encoded_total_schools']) * 100
            : 0;

        return $data;
    }

    private function division_scope_data()
    {
        $data = $this->dashboard_indicator_data();
        $indicator_numbers = $this->dashboard_indicator_numbers($data);

        $division_id = $this->session->division;

        $data['sgc_counts'] = $this->Page_model->division_sgc_counts($division_id);
        $data['sbm_rate_counts'] = $this->Page_model->division_sbm_rate_counts(
            $division_id,
            $this->session->fy,
            $indicator_numbers
        );
        $data['district_count'] = count(
            $this->Page_model->one_cond('district', 'division_id', $division_id)
        );
        $data['division'] = $this->Page_model->get_division_setup($division_id);
        $data['registered_school_count'] = $this->Page_model->division_school_count($division_id);
        $data['completed_checklist_count'] = $this->Page_model->division_sbm_completed_count(
            $division_id,
            $this->session->fy
        );
        $data['encoded_total_schools'] = !empty($data['division']->total_schools)
            ? (int) $data['division']->total_schools
            : 0;
        $data['signup_percentage'] = $data['encoded_total_schools'] > 0
            ? ($data['registered_school_count'] / $data['encoded_total_schools']) * 100
            : 0;
        $data['checklist_completion_percentage'] = $data['encoded_total_schools'] > 0
            ? ($data['completed_checklist_count'] / $data['encoded_total_schools']) * 100
            : 0;

        return $data;
    }

    private function district_scope_data()
    {
        $data = $this->dashboard_indicator_data();
        $indicator_numbers = $this->dashboard_indicator_numbers($data);

        $district_id = (int) $this->session->district;

        $data['district'] = $this->Page_model->one_cond_row('district', 'id', $district_id);
        $data['division'] = $this->Page_model->one_cond_row('division', 'id', $this->session->division);
        $data['school_total'] = $this->Page_model->district_school_count($district_id);
        $data['sgc_counts'] = $this->Page_model->district_sgc_counts($district_id);
        $data['checklist_submission_count'] = $this->Page_model->district_submission_count(
            'sbm',
            $district_id,
            $this->session->fy
        );
        $data['ta_submission_count'] = $this->Page_model->district_submission_count(
            'sbm_ta',
            $district_id,
            $this->session->fy
        );
        $data['action_plan_submission_count'] = $this->Page_model->district_submission_count(
            'sgod_action_plan',
            $district_id,
            $this->session->fy
        );
        $data['completed_checklist_count'] = $this->Page_model->district_sbm_completed_count(
            $district_id,
            $this->session->fy
        );
        $data['tech_entry_count'] = $this->Page_model->district_tech_entry_count(
            $district_id,
            $this->session->fy
        );
        $data['sbm_rate_counts'] = $this->Page_model->district_sbm_rate_counts(
            $district_id,
            $this->session->fy,
            $indicator_numbers
        );

        return $data;
    }

    private function render_dashboard($page, $data)
    {
        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    /**
     * Scope dashboard path for a role, or NULL when the role has none.
     * Administrators are absent on purpose: they are served at the site root so
     * that no URL advertises the privileged role.
     */
    private function dashboard_path_for_position($position)
    {
        $paths = array(
            'region'        => 'region',
            'division'      => 'division',
            'division_head' => 'division',
            'ict'           => 'division',
            'district'      => 'district',
            'school'        => 'school',
        );

        return isset($paths[$position]) ? $paths[$position] : null;
    }

    /**
     * Site root. Guests go to the public homepage, administrators are served
     * here, and every other role is redirected to its own scope URL.
     */
    public function view()
    {
        if (!$this->session->logged_in) {
            redirect(base_url('homepage'));
            return;
        }

        $position = strtolower(trim((string) $this->session->position));

        if ($position === 'admin') {
            $this->render_dashboard('dashboard', $this->region_scope_data(false));
            return;
        }

        $path = $this->dashboard_path_for_position($position);

        if ($path === null) {
            show_error('No dashboard is available for this account type.', 403);
        }

        redirect(base_url($path));
    }

    /**
     * Scope dashboards. Role access is enforced centrally by $role_policies in
     * the constructor, so each method only loads and renders.
     */
    public function region()
    {
        $this->render_dashboard('dashboard_region_learning_gap', $this->region_scope_data());
    }

    public function division()
    {
        $this->render_dashboard('dashboard_scope_learning_gap', $this->division_scope_data());
    }

    public function district()
    {
        $this->learning_gap();
    }

    /**
     * Named school_dashboard() because school() is already the school/(:any)
     * profile handler. The public URL is still /school.
     */
    public function school_dashboard()
    {
        $this->render_dashboard('dashboard_school_learning_gap', array('title' => 'Dashboard'));
    }

    /**
     * Learning Gap Monitoring form and role-scoped summaries.
     * School users encode only their own records. Division and regional users
     * can review aggregate data within the organisational scope of their account.
     */
    private function district_learning_gap_scope()
    {
        $district_id = (int) $this->session->district;
        if ($district_id <= 0) {
            show_error('Your account has no assigned district.', 403);
        }
        return array('type' => 'district', 'id' => $district_id);
    }

    public function learning_gap($entry_mode = false)
    {
        if (!$this->session->logged_in) {
            redirect(base_url('log_in'));
        }

        $position = (string) $this->session->position;
        if (!in_array($position, array('school', 'division', 'division_head', 'region', 'admin', 'district'), true)) {
            show_error('You are not authorized to access Learning Gap Monitoring.', 403);
        }

        $scope = array('type' => 'school', 'id' => (string) $this->session->username);
        if (in_array($position, array('division', 'division_head'), true)) {
            $scope = array('type' => 'division', 'id' => (int) $this->session->division);
        } elseif (in_array($position, array('region', 'admin'), true)) {
            $scope = array('type' => 'region', 'id' => (int) $this->session->region);
        }

        if ($position === 'district') {
            $scope = $this->district_learning_gap_scope();
        }

        $data['title'] = 'Learning Gap Monitoring';
        $data['entry_mode'] = (bool) $entry_mode;
        $data['scope'] = $scope;
        $data['is_school'] = $position === 'school';
        $data['summative_cpl_enabled'] = $data['is_school']
            ? $this->Page_model->school_summative_cpl_enabled((string) $this->session->username)
            : false;
        $data['is_archive_year'] = (int) $this->session->fy < (int) date('Y');
        if ($data['is_school'] && $data['entry_mode'] && $data['is_archive_year']) {
            $this->session->set_flashdata('danger', 'Archived fiscal years are read-only. Return to the current fiscal year to encode or edit records.');
            redirect('Pages/learning_gap_archives');
            return;
        }
        $data['grade_filter'] = trim((string) $this->input->get('grade_level', true));
        $data['records'] = $this->Page_model->learning_gap_records($scope);
        if ($data['grade_filter'] !== '') {
            $data['records'] = array_values(array_filter($data['records'], function ($record) use ($data) {
                return (string) $record->grade_level === $data['grade_filter'];
            }));
        }
        $data['summary'] = $this->Page_model->learning_gap_summary($scope);
        $data['term_performance'] = $this->Page_model->learning_gap_term_performance($scope);
        $data['learning_area_filter'] = $scope['type'] === 'division'
            ? trim((string) $this->input->get('learning_area', true))
            : '';
        $data['term_filter'] = $scope['type'] === 'division'
            ? trim((string) $this->input->get('term', true))
            : '';
        $data['learning_area_filters'] = $scope['type'] === 'division'
            ? $this->Page_model->division_learning_area_options((int) $scope['id'])
            : array();
        $data['has_ranking_filter'] = $scope['type'] === 'division'
            && $data['learning_area_filter'] !== '' && $data['term_filter'] !== '';
        $data['competency_ranking'] = $data['has_ranking_filter']
            ? $this->Page_model->learning_gap_competency_ranking(
                (int) $scope['id'],
                $data['learning_area_filter'],
                $data['term_filter']
            )
            : array();
        $data['division_summary'] = $scope['type'] === 'region'
            ? $this->Page_model->learning_gap_division_summary($scope['id'])
            : array();
        $data['school'] = $data['is_school']
            ? $this->Common->one_cond_row('schools', 'schoolID', $this->session->username)
            : null;
        $data['division'] = $data['school'] && !empty($data['school']->division_id)
            ? $this->Page_model->one_cond_row('division', 'id', $data['school']->division_id)
            : null;
        $data['district'] = $data['school'] && !empty($data['school']->district_id)
            ? $this->Page_model->one_cond_row('district', 'id', $data['school']->district_id)
            : null;
        $data['edit_record'] = null;
        $edit_id = (int) $this->input->get('edit', true);
        if ($data['is_school'] && $edit_id > 0 && !$data['entry_mode']) {
            redirect('Pages/learning_gap_entry?edit=' . $edit_id);
            return;
        }
        if ($data['is_school'] && $edit_id > 0) {
            $data['edit_record'] = $this->Page_model->learning_gap_record($edit_id, (string) $this->session->username);
            if (!$data['edit_record']) {
                $this->session->set_flashdata('danger', 'The selected record was not found in your school account.');
                redirect('Pages/learning_gap');
                return;
            }
        }

        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/learning_gap', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function learning_gap_entry()
    {
        if (!$this->session->logged_in || $this->session->position !== 'school') {
            show_error('Only school accounts can access Learning Gap data entry.', 403);
        }
        $this->learning_gap(true);
    }

    public function learning_gap_school_summary()
    {
        if (!$this->session->logged_in) {
            redirect(base_url('log_in'));
            return;
        }
        $position = (string) $this->session->position;
        if (!in_array($position, array('division', 'division_head', 'region', 'district'), true)) {
            show_error('You are not authorized to access this summary.', 403);
            return;
        }
        $is_region = $position === 'region';
        $scope = array('type' => $is_region ? 'region' : 'division',
            'id' => (int) ($is_region ? $this->session->region : $this->session->division));
        if ($position === 'district') {
            $scope = $this->district_learning_gap_scope();
        }
        if ($scope['id'] <= 0) {
            show_error('Your account has no assigned reporting scope.', 403);
            return;
        }
        $grade = trim((string) $this->input->get('grade_level', true));
        $data = $this->Page_model->learning_gap_school_competency_summary($scope, $grade);
        $data['title'] = 'Learning Gap Summary';
        $data['grade_filter'] = $grade;
        $data['is_region'] = $is_region;
        $data['is_district'] = $position === 'district';
        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/learning_gap_school_summary', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    /** Display submitted learning-gap records separately from the summary page. */
    public function learning_gap_archives()
    {
        $position = (string) $this->session->position;
        $scope = array('type' => 'school', 'id' => (string) $this->session->username);
        if (in_array($position, array('division', 'division_head'), true)) {
            $scope = array('type' => 'division', 'id' => (int) $this->session->division);
        } elseif ($position === 'region') {
            $scope = array('type' => 'region', 'id' => (int) $this->session->region);
        }

        if ($position === 'district') {
            $scope = $this->district_learning_gap_scope();
        }

        $data['title'] = 'Archived Learning Gap Records';
        $data['archive_years'] = $this->Page_model->learning_gap_archive_years($scope);
        $data['is_school'] = $position === 'school';
        $data['current_year'] = (int) date('Y');

        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/learning_gap_archives', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function learning_gap_records()
    {
        if (!$this->session->logged_in) {
            redirect(base_url('log_in'));
        }

        $position = (string) $this->session->position;
        if (!in_array($position, array('school', 'division', 'division_head', 'region', 'admin', 'district'), true)) {
            show_error('You are not authorized to access learning gap records.', 403);
        }

        $scope = array('type' => 'school', 'id' => (string) $this->session->username);
        if (in_array($position, array('division', 'division_head'), true)) {
            $scope = array('type' => 'division', 'id' => (int) $this->session->division);
        } elseif (in_array($position, array('region', 'admin'), true)) {
            $scope = array('type' => 'region', 'id' => (int) $this->session->region);
        }

        if ($position === 'district') {
            $scope = $this->district_learning_gap_scope();
        }

        $data['title'] = 'Submitted Learning Gap Records';
        $data['is_school'] = $position === 'school';
        $requested_year = (int) $this->input->get('year', true);
        if ($requested_year !== 0 && ($requested_year < 2000 || $requested_year > (int) date('Y') + 1)) {
            show_error('The requested archive year is invalid.', 400);
            return;
        }
        $data['record_year'] = $requested_year > 0 ? $requested_year : (int) $this->session->fy;
        $data['has_year_filter'] = $requested_year > 0;
        $data['is_archive_year'] = $data['record_year'] < (int) date('Y');
        $data['grade_filter'] = trim((string) $this->input->get('grade_level', true));
        $data['division_filter'] = $scope['type'] === 'region'
            ? (int) $this->input->get('division_id', true)
            : 0;
        $data['learning_area_filter'] = trim((string) $this->input->get('learning_area', true));
        $data['term_filter'] = trim((string) $this->input->get('term', true));
        $data['competency_filter'] = trim((string) $this->input->get('competency', true));
        $data['has_record_filters'] = $data['division_filter'] > 0
            || $data['grade_filter'] !== ''
            || $data['learning_area_filter'] !== ''
            || $data['term_filter'] !== ''
            || $data['competency_filter'] !== '';
        $data['records'] = $this->Page_model->learning_gap_records($scope, $data['record_year']);
        // Build filter choices from this user's authorized scope before applying filters.
        $data['record_filter_options'] = array('grade_level' => array(), 'learning_area' => array(), 'term' => array());
        foreach ($data['records'] as $record) {
            foreach ($data['record_filter_options'] as $field => $options) {
                $value = trim((string) $record->$field);
                if ($value !== '') {
                    $data['record_filter_options'][$field][$value] = $value;
                }
            }
        }
        foreach ($data['record_filter_options'] as &$options) {
            natcasesort($options);
        }
        unset($options);
        if ($data['division_filter'] > 0) {
            $data['records'] = array_values(array_filter($data['records'], function ($record) use ($data) {
                return (int) $record->division_id === $data['division_filter'];
            }));
        }
        if ($data['grade_filter'] !== '') {
            $data['records'] = array_values(array_filter($data['records'], function ($record) use ($data) {
                return (string) $record->grade_level === $data['grade_filter'];
            }));
        }
        if ($data['learning_area_filter'] !== '') {
            $data['records'] = array_values(array_filter($data['records'], function ($record) use ($data) {
                return (string) $record->learning_area === $data['learning_area_filter'];
            }));
        }
        if ($data['term_filter'] !== '') {
            $data['records'] = array_values(array_filter($data['records'], function ($record) use ($data) {
                return (string) $record->term === $data['term_filter'];
            }));
        }
        if ($data['competency_filter'] !== '') {
            $data['records'] = array_values(array_filter($data['records'], function ($record) use ($data) {
                $competencies = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $record->least_learned_competency)));
                return in_array($data['competency_filter'], $competencies, true);
            }));
        }

        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/learning_gap_records', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    /** Division-only school submission coverage for Learning Gap Monitoring. */
    public function school_submission_monitoring()
    {
        if (!$this->session->logged_in || $this->session->position !== 'division') {
            show_error('Only division users can access school submission monitoring.', 403);
        }

        $data['title'] = 'School Submission Monitoring';
        $data['schools'] = $this->Page_model->learning_gap_school_submission_summary(
            (int) $this->session->division
        );
        $data['submitted_school_count'] = count(array_filter($data['schools'], function ($school) {
            return (int) $school->record_count > 0;
        }));
        $data['total_school_count'] = count($data['schools']);
        $data['division'] = $this->Page_model->one_cond_row(
            'division',
            'id',
            (int) $this->session->division
        );

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/school_submission_monitoring', $data);
        $this->load->view('templates/footer_dt');
    }

    /**
     * Admin-only switchboard for the Summative CPL feature. Schools default to
     * OFF; enabling one lets that school encode CPL for Summative 1 and
     * Summative 2 on each learning gap term record, which then rolls up in the
     * division views.
     */
    public function summative_cpl_access()
    {
        $data['title'] = 'Summative CPL Access';
        $data['schools'] = $this->Page_model->summative_cpl_schools();
        $data['enabled_count'] = count(array_filter($data['schools'], function ($school) {
            return (int) $school->cpl_summative_enabled === 1;
        }));

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/summative_cpl_schools', $data);
        $this->load->view('templates/footer_dt');
    }

    public function summative_cpl_toggle()
    {
        $this->require_post();
        $school_id = trim((string) $this->input->post('school_id', true));
        $enabled = (int) $this->input->post('enabled') === 1;
        $school = $this->Page_model->one_cond_row('schools', 'schoolID', $school_id);
        if (!$school) {
            show_404();
            return;
        }
        $this->Page_model->set_school_summative_cpl($school_id, $enabled);
        $this->session->set_flashdata('success', 'Summative CPL encoding '
            . ($enabled ? 'enabled' : 'disabled') . ' for '
            . html_escape($school->schoolName) . '.');
        redirect('Pages/summative_cpl_access');
    }

    public function learning_gap_save()
    {
        if (!$this->session->logged_in || $this->session->position !== 'school') {
            show_error('Only school users can encode learning gap records.', 403);
        }
        $this->require_post();
        if ((int) $this->session->fy < (int) date('Y')) {
            show_error('Archived fiscal years are read-only.', 403);
            return;
        }

        $this->form_validation->set_rules('grade_level', 'Grade Level', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('learning_area', 'Learning Area / Subject', 'trim|required|max_length[150]');
        $this->form_validation->set_rules('term', 'Trimester', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('class_proficiency_level', 'Class Proficiency Level', 'trim|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
        $this->form_validation->set_rules('proficiency_level', 'Proficiency Level', 'trim|max_length[100]');
        if ($this->Page_model->school_summative_cpl_enabled((string) $this->session->username)) {
            $this->form_validation->set_rules('cpl_summative_1', 'CPL - Summative 1', 'trim|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
            $this->form_validation->set_rules('cpl_summative_2', 'CPL - Summative 2', 'trim|numeric|greater_than_equal_to[0]|less_than_equal_to[100]');
        }
        $this->form_validation->set_rules('learners_assessed', 'Number of Learners Assessed', 'trim|required|integer|greater_than_equal_to[0]');
        $this->form_validation->set_rules('learners_with_gap', 'Number of Learners with Learning Gap', 'trim|required|integer|greater_than_equal_to[0]');

        $selected_competencies = $this->input->post('least_learned_competency', true);
        $has_competency = is_array($selected_competencies)
            ? count(array_filter($selected_competencies, function ($competency) {
                return trim((string) $competency) !== '';
            })) > 0
            : trim((string) $selected_competencies) !== '';
        if ($this->form_validation->run() === FALSE || !$has_competency) {
            $message = validation_errors('', ' ');
            if (!$has_competency) {
                $message .= ' Select at least one least learned competency.';
            }
            $this->session->set_flashdata('danger', trim($message));
            redirect('Pages/learning_gap');
            return;
        }

        $assessed = (int) $this->input->post('learners_assessed', true);
        $with_gap = (int) $this->input->post('learners_with_gap', true);
        if ($with_gap > $assessed) {
            $this->session->set_flashdata('danger', 'Learners with a learning gap cannot exceed learners assessed.');
            redirect('Pages/learning_gap');
            return;
        }

        $is_update = (int) $this->input->post('record_id', true) > 0;
        if (!$this->Page_model->save_learning_gap_record((string) $this->session->username)) {
            $this->session->set_flashdata('danger', 'Your school profile is not set up. Please contact your division administrator.');
            redirect('Pages/learning_gap');
            return;
        }
        $this->session->set_flashdata('success', $is_update
            ? 'Learning gap record updated for your school account.'
            : 'Learning gap record saved for your school account.');
        redirect('Pages/learning_gap');
    }

    public function learning_gap_delete()
    {
        if (!$this->session->logged_in || $this->session->position !== 'school') {
            show_error('Only school users can remove learning gap records.', 403);
        }

        $this->require_post();
        if ((int) $this->session->fy < (int) date('Y')) {
            show_error('Archived fiscal years are read-only.', 403);
            return;
        }
        $id = (int) $this->input->post('id', true);
        if ($id < 1) {
            show_404();
        }
        $this->Page_model->delete_learning_gap_record($id, (string) $this->session->username);
        $this->session->set_flashdata('success', 'Learning gap record removed.');
        redirect('Pages/learning_gap');
    }

    private function monitoring_settings_region()
    {
        if ($this->session->position !== 'region' || (int) $this->session->region < 1) {
            show_error('Only regional users can manage monitoring tools and indicators.', 403);
        }
        $this->load->model('Monitoring_model');
        $region_id = (int) $this->session->region;
        $this->Monitoring_model->initialize($region_id);
        return $region_id;
    }

    public function monitoring_tool()
    {
        $region_id = $this->monitoring_settings_region();
        $tools = $this->Monitoring_model->tools($region_id);
        $requested_tool = $this->input->get('tool_id');
        if ($requested_tool === null || $requested_tool === '') {
            $edit_id = (int) $this->input->get('edit');
            $edit = $edit_id ? $this->Monitoring_model->tool($region_id, $edit_id) : null;
            if ($edit_id && !$edit) { show_404(); return; }
            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/monitoring_tool_list', array('tools' => $tools, 'edit' => $edit));
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
            return;
        }
        if (!is_scalar($requested_tool) || !ctype_digit((string) $requested_tool) || (int) $requested_tool < 1) {
            show_404(); return;
        }
        $tool = $requested_tool ? $this->Monitoring_model->tool($region_id, (int) $requested_tool) : null;
        if ($requested_tool && !$tool) { show_404(); return; }
        $data = array('tools' => $tools, 'tool' => $tool, 'groups' => $this->Monitoring_model->defaults(),
            'indicators' => array_filter($tool ? $this->Monitoring_model->indicators($region_id, $tool->id) : array(), function ($indicator) {
                return (int) $indicator->active === 1;
            }));
        $data['school_years'] = array();
        for ($year = (int) date('Y') + 1; $year >= 2023; $year--) {
            $data['school_years'][] = $year . '-' . ($year + 1);
        }
        $requested_sy = $this->input->get('sy');
        $data['selected_sy'] = in_array($requested_sy, $data['school_years'], true) ? $requested_sy : '';
        $data['divisions'] = $this->Page_model->regional_divisions($region_id);
        $data['selected_division'] = 0;
        foreach ($data['divisions'] as $division) {
            if ((string) $division->id === $this->input->get('division_id')) {
                $data['selected_division'] = (int) $division->id;
            }
        }
        $data['districts'] = $data['selected_division']
            ? $this->db->where('division_id', $data['selected_division'])->order_by('description')->get('district')->result() : array();
        $data['selected_district'] = 0;
        foreach ($data['districts'] as $district) {
            if ((string) $district->id === $this->input->get('district_id')) {
                $data['selected_district'] = (int) $district->id;
            }
        }
        $data['schools'] = $data['selected_district']
            ? $this->db->select('schoolID, schoolName')->where('division_id', $data['selected_division'])
                ->where('district_id', $data['selected_district'])->order_by('schoolName')->get('schools')->result() : array();
        $data['selected_school'] = '';
        foreach ($data['schools'] as $school) {
            if ((string) $school->schoolID === $this->input->get('school_id')) {
                $data['selected_school'] = (string) $school->schoolID;
            }
        }
        $this->Monitoring_model->assessment_schema();
        $data['records'] = $this->Monitoring_model->assessments($region_id);
        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/monitoring_tool', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    private function monitoring_assessment_context()
    {
        $region = $this->monitoring_settings_region();
        $this->Monitoring_model->assessment_schema();
        return $region;
    }

    private function monitoring_assessment_render($payload, $record = null, $error = '', $print = false)
    {
        if (!$print) { $payload = $this->monitoring_monitor_identity($payload); }
        $data = array('assessment' => $payload, 'record' => $record, 'error' => $error, 'print_mode' => $print);
        if (!$print) {
            $this->load->view('templates/header');
            $this->load->view('templates/menu');
        }
        $this->load->view('pages/monitoring_assessment', $data);
        if (!$print) {
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        }
    }

    public function monitoring_assessment($id = 0)
    {
        $region = $this->monitoring_assessment_context();
        if ($id) {
            $record = $this->Monitoring_model->assessment($region, (int) $id);
            if (!$record) { show_404(); return; }
            $this->monitoring_assessment_render(json_decode($record->payload, true), $record);
            return;
        }
        $payload = $this->monitoring_new_assessment($region, $this->input->get());
        if (!$payload) { show_error('Select a valid monitoring tool, school year, division, district, and school.', 422); return; }
        $this->monitoring_assessment_render($payload);
    }

    private function monitoring_new_assessment($region, $input)
    {
        if (!is_array($input) || !isset($input['sy']) || !is_string($input['sy'])
            || !preg_match('/^(20[0-9]{2})-(20[0-9]{2})$/', $input['sy'], $years)
            || (int) $years[2] !== (int) $years[1] + 1) { return false; }
        foreach (array('school_id', 'division_id', 'district_id') as $key) {
            if (!isset($input[$key]) || !is_scalar($input[$key])) { return false; }
        }
        if (!isset($input['tool_id']) || !is_scalar($input['tool_id'])) { return false; }
        $tool = $this->Monitoring_model->tool($region, (int) $input['tool_id']);
        if (!$tool) { return false; }
        $school = $this->db->select('s.schoolID, s.schoolName, d.description AS division_name, dist.description AS district_name')
            ->from('schools s')->join('division d', 'd.id = s.division_id')
            ->join('district dist', 'dist.id = s.district_id AND dist.division_id = d.id')
            ->where('d.region_id', $region)->where('d.id', $input['division_id'])
            ->where('dist.id', $input['district_id'])->where('s.schoolID', $input['school_id'])->get()->row();
        if (!$school) { return false; }
        $payload = array('tool_id' => (int) $tool->id, 'tool_name' => $tool->name, 'sy' => $input['sy'], 'school_id' => (string) $school->schoolID,
            'school_name' => $school->schoolName, 'division_id' => (int) $input['division_id'],
            'district_id' => (int) $input['district_id'], 'division_name' => $school->division_name,
            'district_name' => $school->district_name, 'groups' => $this->Monitoring_model->defaults(), 'items' => array(),
            'details' => array(), 'comments' => array());
        foreach ($this->Monitoring_model->indicators($region, $tool->id) as $item) {
            if ((int) $item->active !== 1) { continue; }
            $payload['items'][] = array('id' => (int) $item->id, 'area' => (int) $item->area_index,
                'subgroup' => $item->subgroup, 'text' => $item->indicator, 'rating' => '', 'remarks' => '');
        }
        return $payload['items'] ? $payload : false;
    }

    public function monitoring_assessment_save()
    {
        $this->require_post();
        $region = $this->monitoring_assessment_context();
        $id = (int) $this->input->post('id');
        $record = $id ? $this->Monitoring_model->assessment($region, $id) : null;
        if ($id && !$record) { show_404(); return; }
        $payload = $record ? json_decode($record->payload, true) : $this->monitoring_new_assessment($region, $this->input->post());
        if (!$payload) { show_error('Invalid school selection or no active indicators.', 422); return; }
        $error = '';
        $ratings = $this->input->post('rating'); $remarks = $this->input->post('remarks');
        $ratings = is_array($ratings) ? $ratings : array(); $remarks = is_array($remarks) ? $remarks : array();
        foreach ($payload['items'] as &$item) {
            $key = $item['id'];
            $rating = isset($ratings[$key]) ? $ratings[$key] : '';
            if (!in_array($rating, array('', 'evident', 'not_evident'), true)) { $error = 'Invalid indicator response.'; $rating = ''; }
            $item['rating'] = $rating;
            $value = isset($remarks[$key]) ? $remarks[$key] : '';
            if (!is_string($value) || strlen($value) > 10000) { $error = 'Remarks must be text of at most 10,000 bytes.'; $value = ''; }
            $item['remarks'] = $value;
        }
        unset($item);
        $fields = array('school_type', 'school_head', 'contact', 'psds', 'monitor_date', 'monitor_two',
            'conforme_date', 'special_programs', 'strengths', 'technical_assistance', 'recommendations');
        foreach ($fields as $field) {
            $value = $this->input->post($field);
            if ($value === null) { $value = ''; }
            if (!is_string($value) || strlen($value) > 10000) { $error = 'Details must be text of at most 10,000 bytes.'; $value = ''; }
            $payload['details'][$field] = trim($value);
        }
        if (!in_array($payload['details']['school_type'], array('', 'Public', 'Private'), true)) { $error = 'Invalid school type.'; }
        if (!in_array($payload['details']['special_programs'], array('', '1'), true)) { $error = 'Invalid special program selection.'; }
        foreach (array('monitor_date', 'conforme_date') as $field) {
            $value = $payload['details'][$field];
            $date = DateTime::createFromFormat('!Y-m-d', $value);
            if ($value !== '' && (!$date || $date->format('Y-m-d') !== $value)) { $error = 'Enter a valid date.'; }
        }
        $comments = $this->input->post('comments');
        foreach ($payload['groups'] as $area => $group) {
            $value = is_array($comments) && isset($comments[$area]) ? $comments[$area] : '';
            if (!is_string($value) || strlen($value) > 10000) { $error = 'Area comments are too long or invalid.'; $value = ''; }
            $payload['comments'][$area] = $value;
        }
        $payload = $this->monitoring_monitor_identity($payload);
        $version = (int) $this->input->post('version');
        if ($record && $version !== (int) $record->version) {
            $error = 'This assessment changed in another window. Your entries are shown below; reload the saved assessment before applying them.';
        }
        if (!$error) {
            $saved = $this->Monitoring_model->save_assessment($region, $id, $version, $payload);
            if ($saved) { $this->session->set_flashdata('success', 'Monitoring assessment saved.'); redirect('Pages/monitoring_assessment/' . $saved); return; }
            $error = 'Unable to save. The assessment may have changed in another window. Copy your entries before reloading.';
        }
        if ($record) { $record->version = $version; }
        $this->monitoring_assessment_render($payload, $record, $error);
    }

    public function monitoring_assessment_print($id = 0)
    {
        $region = $this->monitoring_assessment_context();
        $record = $this->Monitoring_model->assessment($region, (int) $id);
        if (!$record) { show_404(); return; }
        $this->monitoring_assessment_render(json_decode($record->payload, true), $record, '', true);
    }

    public function monitoring_assessment_delete()
    {
        $this->require_post();
        $region = $this->monitoring_assessment_context();
        $ok = $this->Monitoring_model->delete_assessment($region, (int) $this->input->post('id'), (int) $this->input->post('version'));
        $this->session->set_flashdata($ok ? 'success' : 'danger', $ok ? 'Assessment deleted.' : 'Assessment not deleted. It may have changed; refresh and try again.');
        redirect('monitoring/tool');
    }

    public function monitoring_tools()
    {
        $region = $this->monitoring_settings_region();
        $data = array('tools' => $this->Monitoring_model->tools($region), 'edit' => null);
        $id = (int) $this->input->get('edit');
        if ($id) {
            $data['edit'] = $this->Monitoring_model->tool($region, $id);
            if (!$data['edit']) { show_404(); return; }
        }
        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/monitoring_tools', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function monitoring_tool_save()
    {
        $this->require_post();
        $region = $this->monitoring_settings_region();
        $this->form_validation->set_rules('id', 'Tool ID', 'required|integer|greater_than_equal_to[0]');
        $this->form_validation->set_rules('name', 'Tool name', 'trim|required|max_length[255]');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('danger', validation_errors('', ' '));
            $this->session->set_flashdata('monitoring_tool_input', $this->input->post(NULL, false));
        } else {
            $ok = $this->Monitoring_model->save_tool($region, (int) $this->input->post('id'), trim((string) $this->input->post('name')));
            $this->session->set_flashdata($ok ? 'success' : 'danger', $ok ? 'Monitoring tool saved.' : 'Monitoring tool could not be saved.');
        }
        redirect($this->input->post('return_to') === 'monitoring/tool' ? 'monitoring/tool' : 'monitoring-tool/settings/tools');
    }

    public function monitoring_tool_delete()
    {
        $this->require_post();
        $region = $this->monitoring_settings_region();
        $ok = $this->Monitoring_model->delete_tool($region, (int) $this->input->post('id'));
        $this->session->set_flashdata($ok ? 'success' : 'danger', $ok ? 'Monitoring tool removed. Saved assessments remain available.' : 'Monitoring tool could not be removed.');
        redirect($this->input->post('return_to') === 'monitoring/tool' ? 'monitoring/tool' : 'monitoring-tool/settings/tools');
    }

    public function monitoring_indicator_delete()
    {
        $this->require_post();
        $region = $this->monitoring_settings_region();
        $tool_id = (int) $this->input->post('tool_id');
        if (!$this->Monitoring_model->tool($region, $tool_id)) { show_404(); return; }
        $ok = $this->Monitoring_model->delete_indicator($region, $tool_id, (int) $this->input->post('id'));
        $this->session->set_flashdata($ok ? 'success' : 'danger', $ok ? 'Indicator deleted.' : 'Indicator could not be deleted.');
        redirect('monitoring-tool/settings/indicators?tool_id=' . $tool_id);
    }

    public function monitoring_indicators()
    {
        $region_id = $this->monitoring_settings_region();
        $tool_id = (int) $this->input->get('tool_id');
        if (!$tool_id) { redirect('monitoring-tool/settings/tools'); return; }
        $tool = $this->Monitoring_model->tool($region_id, $tool_id);
        if (!$tool) { show_404(); return; }
        $data = array('tool' => $tool, 'groups' => $this->Monitoring_model->defaults(),
            'indicators' => $this->Monitoring_model->indicators($region_id, $tool_id));
        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/monitoring_indicators', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function monitoring_indicator_save()
    {
        $this->require_post();
        $region_id = $this->monitoring_settings_region();
        $tool_id = (int) $this->input->post('tool_id');
        if (!$this->Monitoring_model->tool($region_id, $tool_id)) { show_404(); return; }
        $this->form_validation->set_rules('id', 'Indicator ID', 'required|integer|greater_than_equal_to[0]');
        $this->form_validation->set_rules('area_index', 'Area', 'required|integer|greater_than_equal_to[0]|less_than_equal_to[7]');
        $this->form_validation->set_rules('indicator', 'Indicator', 'trim|required|max_length[5000]');
        $this->form_validation->set_rules('subgroup', 'Group heading', 'trim|max_length[255]');
        $this->form_validation->set_rules('sort_order', 'Display order', 'required|integer|greater_than[0]|less_than_equal_to[9999]');
        $this->form_validation->set_rules('active', 'Status', 'required|in_list[0,1]');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('danger', validation_errors('', ' '));
            $this->session->set_flashdata('monitoring_input', $this->input->post(NULL, false));
        } else {
            $saved = $this->Monitoring_model->save($region_id, $tool_id, (int) $this->input->post('id'), array(
                'area_index' => (int) $this->input->post('area_index'),
                'indicator' => trim((string) $this->input->post('indicator')),
                'subgroup' => trim((string) $this->input->post('subgroup')),
                'sort_order' => (int) $this->input->post('sort_order'),
                'active' => (int) $this->input->post('active')
            ));
            $this->session->set_flashdata($saved ? 'success' : 'danger', $saved ? 'Indicator saved.' : 'Indicator could not be saved.');
        }
        redirect('monitoring-tool/settings/indicators?tool_id=' . $tool_id);
    }

    public function learning_area_setup()
    {
        $this->require_regional_learning_area_access();
        $division_id = $this->regional_learning_area_division_id($this->input->get('division_id', true));
        $data['title'] = 'Learning Area Setup';
        $data['divisions'] = $this->Page_model->regional_divisions((int) $this->session->region);
        $data['selected_division_id'] = $division_id;
        $data['areas'] = $this->Page_model->learning_area_settings($division_id);
        $data['competency_counts'] = $this->Page_model->learning_competency_counts(
            (int) $this->session->region
        );
        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/learning_area_setup', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function learning_area_setup_save()
    {
        $this->require_regional_learning_area_access();
        $this->require_post();
        $division_id = $this->regional_learning_area_division_id($this->input->post('division_id', true));
        $this->form_validation->set_rules('grade_level', 'Grade Level', 'trim|required|max_length[50]');
        $this->form_validation->set_rules('learning_area', 'Learning Area', 'trim|required|max_length[150]');
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('danger', validation_errors('', ' '));
        } elseif ($this->Page_model->add_learning_area_setting($division_id)) {
            $this->session->set_flashdata('success', 'Learning area added to the grade-level setup.');
        } else {
            $this->session->set_flashdata('danger', 'That learning area is already set up for the selected grade level.');
        }
        redirect('Pages/learning_area_setup?division_id=' . $division_id);
    }

    public function learning_area_setup_delete()
    {
        $this->require_regional_learning_area_access();
        $this->require_post();
        $division_id = $this->regional_learning_area_division_id($this->input->post('division_id', true));
        $id = (int) $this->input->post('id', true);
        if ($id < 1) {
            show_404();
        }
        $this->Page_model->delete_learning_area_setting($id, $division_id);
        $this->session->set_flashdata('success', 'Learning area removed from this division setup.');
        redirect('Pages/learning_area_setup?division_id=' . $division_id);
    }

    public function learning_competency_setup($area_id = 0)
    {
        $this->require_regional_learning_area_access();
        $division_id = $this->regional_learning_area_division_id($this->input->get('division_id', true));
        $data['area'] = $this->Page_model->learning_area_setting((int) $area_id, $division_id);
        if (!$data['area']) {
            show_404();
        }
        $data['title'] = 'Learning Competencies';
        $data['competencies'] = $this->Page_model->learning_competencies(
            (int) $this->session->region,
            (string) $data['area']->grade_level,
            (string) $data['area']->learning_area
        );
        $data['view_only'] = false;
        $data['selected_division_id'] = $division_id;
        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/learning_competency_setup', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function learning_competencies_view($area_id = 0)
    {
        $this->require_regional_learning_area_access();
        $division_id = $this->regional_learning_area_division_id($this->input->get('division_id', true));
        $data['area'] = $this->Page_model->learning_area_setting((int) $area_id, $division_id);
        if (!$data['area']) {
            show_404();
        }
        $data['title'] = 'Learning Competencies';
        $data['competencies'] = $this->Page_model->learning_competencies(
            (int) $this->session->region,
            (string) $data['area']->grade_level,
            (string) $data['area']->learning_area
        );
        $data['view_only'] = true;
        $data['selected_division_id'] = $division_id;
        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/learning_competency_setup', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function learning_competency_setup_save($area_id = 0)
    {
        $this->require_regional_learning_area_access();
        $this->require_post();
        $division_id = $this->regional_learning_area_division_id($this->input->post('division_id', true));
        $area = $this->Page_model->learning_area_setting((int) $area_id, $division_id);
        if (!$area) {
            show_404();
        }
        $this->form_validation->set_rules('competency', 'Learning Competency', 'trim|required|max_length[1000]');
        $this->form_validation->set_rules('term', 'Term', 'trim|required|in_list[Term 1,Term 2,Term 3]');
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('danger', validation_errors('', ' '));
        } else {
            $saved = $this->Page_model->add_learning_competency((int) $this->session->region, $area);
            if ($saved === 'duplicate') {
                $this->session->set_flashdata('danger', 'That learning competency is already added for the selected term.');
            } elseif ($saved) {
                $this->session->set_flashdata('success', 'Learning competency added.');
            } else {
                $this->session->set_flashdata('danger', 'The learning competency could not be saved. Please try again, and report it to the system administrator if it keeps failing.');
            }
        }
        redirect('Pages/learning_competency_setup/' . (int) $area->id . '?division_id=' . $division_id);
    }

    public function learning_competency_setup_delete($area_id = 0)
    {
        $this->require_regional_learning_area_access();
        $this->require_post();
        $division_id = $this->regional_learning_area_division_id($this->input->post('division_id', true));
        $area = $this->Page_model->learning_area_setting((int) $area_id, $division_id);
        if (!$area) {
            show_404();
        }
        $competency_id = (int) $this->input->post('id', true);
        if ($competency_id < 1) {
            show_404();
        }
        $this->Page_model->delete_learning_competency((int) $competency_id, (int) $this->session->region);
        $this->session->set_flashdata('success', 'Learning competency removed.');
        redirect('Pages/learning_competency_setup/' . (int) $area->id . '?division_id=' . $division_id);
    }

    public function learning_competency_setup_update_term($area_id = 0, $competency_id = 0)
    {
        $this->require_regional_learning_area_access();
        $this->require_post();
        $division_id = $this->regional_learning_area_division_id($this->input->post('division_id', true));
        $area = $this->Page_model->learning_area_setting((int) $area_id, $division_id);
        if (!$area) {
            show_404();
        }

        $this->form_validation->set_rules('term', 'Term', 'trim|required|in_list[Term 1,Term 2,Term 3]');
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('danger', validation_errors('', ' '));
        } elseif ($this->Page_model->update_learning_competency_term((int) $competency_id, (int) $this->session->region)) {
            $this->session->set_flashdata('success', 'Learning competency term updated.');
        } else {
            $this->session->set_flashdata('danger', 'The learning competency could not be updated.');
        }
        redirect('Pages/learning_competency_setup/' . (int) $area->id . '?division_id=' . $division_id);
    }

    public function learning_area_options()
    {
        if (!$this->session->logged_in || $this->session->position !== 'school') {
            show_error('Only school accounts can request learning area options.', 403);
        }
        $school = $this->Common->one_cond_row('schools', 'schoolID', $this->session->username);
        $grade = trim((string) $this->input->get('grade_level', true));
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(array(
                'areas' => $school && $grade !== ''
                    ? $this->Page_model->learning_area_options((int) $school->division_id, $grade)
                    : array()
            )));
    }

    public function learning_competency_options()
    {
        if (!$this->session->logged_in || $this->session->position !== 'school') {
            show_error('Only school accounts can request learning competency options.', 403);
        }
        $school = $this->Common->one_cond_row('schools', 'schoolID', $this->session->username);
        $grade = trim((string) $this->input->get('grade_level', true));
        $area = trim((string) $this->input->get('learning_area', true));
        $term = trim((string) $this->input->get('term', true));
        $this->output->set_content_type('application/json')
            ->set_output(json_encode(array(
                'competencies' => $school && $grade !== '' && $area !== '' && $term !== ''
                    ? $this->Page_model->learning_competency_options((int) $school->region_id, $grade, $area, $term)
                    : array()
            )));
    }


    public function profilelist()
    {

        $page = "profile_list";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Profile List";

        $data['data'] = $this->Page_model->no_cond('profile');

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function qr()
    {

        $page = "qr";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "QR Code";

        $data['data'] = $this->Page_model->one_cond_row('profile', 'id', (int) $this->uri->segment(3));
        if (!$data['data']) {
            show_404();
        }
        $data['verification_url'] = base_url('Pages/verify/' . (int) $data['data']->id)
            . '?token=' . rawurlencode($this->build_profile_verification_token($data['data']));

        $this->load->view('pages/' . $page, $data);
    }

    public function verify()
    {

        $page = "qr_verify";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "QR Code";

        $data['data'] = $this->Page_model->one_cond_row('profile', 'id', (int) $this->uri->segment(3));
        $token = trim((string) $this->input->get('token', true));
        if (!$data['data'] || !preg_match('/^[a-f0-9]{64}$/', $token)
            || !hash_equals($this->build_profile_verification_token($data['data']), $token)) {
            show_404();
        }

        $this->load->view('pages/' . $page, $data);
    }

    public function profile_new()
    {

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');
        $this->form_validation->set_rules('name', 'Full name', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('docType', 'Document type', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('docNo', 'Document number', 'trim|required|max_length[100]');
        $this->form_validation->set_rules('description', 'Description', 'trim|max_length[1000]');

        if ($this->form_validation->run() == FALSE) {

            $page = "profile_add";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "New Entry";


            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->profile_insert();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/profilelist');
        }
    }



    // user settings area //

    public function userlist()
    {
        $this->require_user_manager();

        if ($this->is_division_user_manager()) {
            redirect(base_url() . 'pages/userlist_division');
        }

        $page = "user_list";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "User List";
        $data['division_scope'] = false;

        // Fetch all users for client-side DataTables
        if ($this->session->position === 'district') {
            $this->apply_district_user_scope();
            $data['users'] = $this->db->get('users')->result();
            $data['title'] = 'District User List';
        } else {
            $data['users'] = $this->Page_model->no_cond('users');
        }

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function userlist_ajax()
    {
        $this->require_user_manager();

        // DataTables parameters
        $draw = $this->input->post('draw');
        $start = $this->input->post('start');
        $length = $this->input->post('length');
        $search = $this->input->post('search')['value'];
        $order = $this->input->post('order');
        $columns = $this->input->post('columns');

        error_log("userlist_ajax called - draw: $draw, start: $start, length: $length, search: $search");

        // Build query
        $this->db->select('id, fname, mname, lname, username, position');
        if ($this->is_division_user_manager()) {
            $this->db->where('p_id', (int) $this->session->division);
        }

        if ($this->session->position === 'district') {
            $this->apply_district_user_scope();
        }

        // Search
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('fname', $search);
            $this->db->or_like('mname', $search);
            $this->db->or_like('lname', $search);
            $this->db->or_like('username', $search);
            $this->db->or_like('position', $search);
            $this->db->group_end();
        }

        // Get total records before filtering
        $total_records = $this->db->count_all_results('users', false);
        error_log("Total records: $total_records");

        // Order
        if (!empty($order)) {
            $column_index = $order[0]['column'];
            $column_name = $columns[$column_index]['data'];
            $direction = $order[0]['dir'];

            // Map column names to database fields
            $column_map = array(
                'account' => 'lname',
                'username' => 'username',
                'position' => 'position'
            );

            if (isset($column_map[$column_name])) {
                $this->db->order_by($column_map[$column_name], $direction);
            }
        } else {
            $this->db->order_by('lname, fname', 'ASC');
        }

        // Get filtered records count
        $filtered_records = $this->db->count_all_results('', false);
        error_log("Filtered records: $filtered_records");

        // Apply pagination
        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        // Get data
        $data = $this->db->get('users')->result();
        error_log("Data count: " . count($data));

        // Format data for DataTables
        $formatted_data = array();
        foreach ($data as $row) {
            $display_name = mb_convert_case(
                trim((!empty($row->lname) ? $row->lname . ', ' : '') . $row->fname . (!empty($row->mname) ? ' ' . substr($row->mname, 0, 1) . '.' : '')),
                MB_CASE_TITLE,
                'UTF-8'
            );
            $initials = strtoupper(substr($row->fname, 0, 1) . (!empty($row->lname) ? substr($row->lname, 0, 1) : ''));

            $formatted_data[] = array(
                'account' => $display_name,
                'initials' => $initials,
                'username' => $row->username,
                'position' => $row->position,
                'id' => $row->id
            );
        }

        $response = array(
            'draw' => intval($draw),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $filtered_records,
            'data' => $formatted_data
        );

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function userlist_division()
    {
        $this->require_user_manager();
        if ($this->session->position === 'district') {
            $this->userlist();
            return;
        }

        $page = "user_list";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Division User List";
        $data['division_scope'] = true;

        $data['users'] = $this->Page_model->one_cond('users','p_id',$this->session->division);

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function school_by_district()
    {

        $page = "school_list_by_district";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Division List";

        // Get divisions with total_schools field
        $divisions = $this->Common->one_cond_select('division', 'description,id,total_schools', 'region_id', $this->session->region);

        // Calculate signup statistics for each division
        foreach ($divisions as $division) {
            // Count signed up schools for this division
            $this->db->where('division_id', $division->id);
            $signed_up_count = $this->db->count_all_results('schools');

            $total_schools = $division->total_schools ? (int) $division->total_schools : 0;

            // Calculate percentages
            if ($total_schools > 0) {
                $signup_percentage = round(($signed_up_count / $total_schools) * 100, 1);
                $not_signup_percentage = round((($total_schools - $signed_up_count) / $total_schools) * 100, 1);
            } else {
                $signup_percentage = 0;
                $not_signup_percentage = 0;
            }

            $division->signed_up_count = $signed_up_count;
            $division->signup_percentage = $signup_percentage;
            $division->not_signup_percentage = $not_signup_percentage;
        }

        $data['data'] = $divisions;

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function update_total_schools()
    {
        $this->require_post();
        $division_id = (int) $this->input->post('division_id', true);
        $total_schools = filter_var($this->input->post('total_schools', true), FILTER_VALIDATE_INT);
        $division = $this->Page_model->one_cond_row('division', 'id', $division_id);

        if (!$division || $total_schools === false || $total_schools < 0 || $total_schools > 100000) {
            $this->output->set_status_header(422)->set_content_type('application/json')
                ->set_output(json_encode(array('success' => false, 'message' => 'Invalid parameters.')));
            return;
        }

        if ($this->session->position === 'region'
            && (int) $division->region_id !== (int) $this->session->region) {
            show_error('You can only update divisions in your region.', 403);
        }

        $result = $this->db->where('id', $division_id)
            ->update('division', array('total_schools' => $total_schools));
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success' => (bool) $result,
            'message' => $result ? '' : 'Database update failed.',
        )));
    }

    public function schools()
    {
        $page = "schools";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School List";
        $division_id = (int) $this->uri->segment(3);
        $this->require_division_scope($this->Page_model->one_cond_row('division', 'id', $division_id));

        $data['data'] = $this->Common->two_join_one_cond_not_gb('schools','district', 'a.recID,id,description,schoolID,schoolName,a.division_id,a.district_id','a.district_id = id', 'a.division_id', $division_id,'schoolName','ASC');

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function schools_district()
    {
        $page = "schools";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School List";
        $district_id = (int) $this->uri->segment(3);
        $this->require_district_scope($this->Page_model->one_cond_row('district', 'id', $district_id));

        $data['data'] = $this->Common->two_join_one_cond_not_gb('schools','district', 'a.recID,id,description,schoolID,schoolName,a.division_id,a.district_id','a.district_id = id', 'a.district_id', $district_id,'schoolName','ASC');

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function schools_division()
    {
        $page = "schools";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School List";
        $division_id = (int) $this->uri->segment(3);
        $this->require_division_scope($this->Page_model->one_cond_row('division', 'id', $division_id));

        $data['data'] = $this->Common->two_join_one_cond_not_gb('schools','district', 'a.recID,id,description,schoolID,schoolName,a.division_id,a.district_id','a.district_id = id', 'a.division_id', $division_id,'schoolName','ASC');

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function sbm_rate_list()
    {
        $page = "sbm_list_rate";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School List";
        $fy  = $this->session->fy;
        $q   = $this->uri->segment(3);
        $val = $this->uri->segment(4);
        $district = $this->session->district;

        if (!preg_match('/^q([1-9]|[1-3][0-9]|4[0-2])$/', $q) || !in_array((int) $val, array(1, 2, 3, 4), true)) {
            show_404();
        }

        $data['data'] = $this->db
            ->select("CAST(a.school_id AS CHAR) AS school_id, COALESCE(MAX(NULLIF(TRIM(b.schoolID), '')), CAST(a.school_id AS CHAR)) AS schoolID, MAX(b.division_id) AS division_id, COALESCE(MAX(NULLIF(TRIM(b.schoolName), '')), '') AS schoolName", false)
            ->from('sbm a')
            ->join('schools b', 'TRIM(CAST(a.school_id AS CHAR)) = TRIM(b.schoolID)', 'left', false)
            ->where('a.fy', $fy)
            ->where('a.' . $q, $val)
            ->where('b.district_id', $district)
            ->group_by('a.school_id')
            ->order_by('schoolName', 'ASC')
            ->get()
            ->result();
        $data['rate_scope'] = 'district';
        $data['rate_question'] = $q;
        $data['rate_value'] = (int) $val;
        $data = array_merge($data, $this->get_rate_indicator_context($q));
        $data['division_names'] = $this->Page_model->division_names_by_ids(array_map(function ($row) {
            return $row->division_id;
        }, $data['data']));

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    function data_privacy()
    {
        $page = "pages/data_privacy";

        if (!file_exists(APPPATH . 'views/' . $page . '.php')) {
            show_404();
        }
        $data['title'] = "Data Privacy";
        $this->load->view($page, $data);
    }
    public function sbm_rate_list_division()
    {
        $page = "sbm_list_rate";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School List";
        $fy  = $this->session->fy;
        $q   = $this->uri->segment(3);
        $val = $this->uri->segment(4);
        $division = $this->session->division;

        if (!preg_match('/^q([1-9]|[1-3][0-9]|4[0-2])$/', $q) || !in_array((int) $val, array(1, 2, 3, 4), true)) {
            show_404();
        }

        $data['data'] = $this->db
            ->select("CAST(a.school_id AS CHAR) AS school_id, COALESCE(MAX(NULLIF(TRIM(b.schoolID), '')), CAST(a.school_id AS CHAR)) AS schoolID, MAX(b.division_id) AS division_id, COALESCE(MAX(NULLIF(TRIM(b.schoolName), '')), '') AS schoolName", false)
            ->from('sbm a')
            ->join('schools b', 'TRIM(CAST(a.school_id AS CHAR)) = TRIM(b.schoolID)', 'left', false)
            ->where('a.fy', $fy)
            ->where('a.' . $q, $val)
            ->where('a.division', $division)
            ->where('a.stat', 1)
            ->group_by('a.school_id')
            ->order_by('schoolName', 'ASC')
            ->get()
            ->result();
        $data['rate_scope'] = 'division';
        $data['rate_question'] = $q;
        $data['rate_value'] = (int) $val;
        $data = array_merge($data, $this->get_rate_indicator_context($q));
        $division_row = $this->Page_model->one_cond_row('division', 'id', $division);
        $data['division_names'] = array(
            (string) $division => $division_row ? $division_row->description : 'Division'
        );

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function sbm_rate_list_region()
    {
        $page = "sbm_list_rate";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School List";
        $fy  = $this->session->fy;
        $q   = $this->uri->segment(3);
        $val = $this->uri->segment(4);
        $region = $this->session->region;

        if (!preg_match('/^q([1-9]|[1-3][0-9]|4[0-2])$/', $q) || !in_array((int) $val, array(1, 2, 3, 4), true)) {
            show_404();
        }

        $data['data'] = $this->db
            ->select("CAST(a.school_id AS CHAR) AS school_id, COALESCE(MAX(NULLIF(TRIM(b.schoolID), '')), CAST(a.school_id AS CHAR)) AS schoolID, MAX(b.division_id) AS division_id, COALESCE(MAX(NULLIF(TRIM(b.schoolName), '')), '') AS schoolName", false)
            ->from('sbm a')
            ->join('schools b', 'TRIM(CAST(a.school_id AS CHAR)) = TRIM(b.schoolID)', 'left', false)
            ->where('a.fy', $fy)
            ->where('a.' . $q, $val)
            ->where('a.region', $region)
            ->where('a.stat', 1)
            ->group_by('a.school_id')
            ->order_by('schoolName', 'ASC')
            ->get()
            ->result();
        error_log("sbm_rate_list_region - fy: $fy, q: $q, val: $val, region: $region, count: " . count($data['data']));
        $data['rate_scope'] = 'region';
        $data['rate_question'] = $q;
        $data['rate_value'] = (int) $val;
        $data = array_merge($data, $this->get_rate_indicator_context($q));
        $data['division_names'] = $this->Page_model->division_names_by_ids(array_map(function ($row) {
            return $row->division_id;
        }, $data['data']));

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function user_new()
    {
        $this->require_user_manager();

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[128]');
        $this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email|max_length[254]|is_unique[users.email]|is_unique[users.username]');
        $this->form_validation->set_rules('gender', 'Gender', 'required');

        if ($this->form_validation->run() == FALSE) {

            $page = "user_add";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "New User";
            $data['division'] = $this->Page_model->one_cond('division', 'region_id', 12);
            if ($this->session->position === 'district') {
                $this->district_learning_gap_scope();
                $data['pos'] = array_values(array_filter($this->Page_model->no_cond('position'), function ($position) {
                    return $this->is_allowed_managed_position($position->pos);
                }));
                $data['districts'] = $this->Page_model->one_cond('district', 'id', $this->session->district);
            } elseif ($this->is_division_user_manager()) {
                $data['pos'] = array_values(array_filter(
                    $this->Page_model->no_cond('position'),
                    function ($position) {
                        return $this->is_allowed_managed_position($position->pos);
                    }
                ));
                $data['districts'] = $this->Page_model->one_cond('district', 'division_id', $this->session->division);
            }else{
                $data['pos'] = $this->Page_model->no_cond('position');  
            }


            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $fname = $this->input->post('fname');
            $lname = $this->input->post('lname');
            $username = trim((string) $this->input->post('email'));
            $position = $this->input->post('position');

            if (!$this->is_allowed_managed_position($position)) {
                show_error('You are not authorized to create this type of user.', 403);
            }

            $this->validate_managed_district($this->input->post('d_id'));

            $config['allowed_types'] = 'jpg|png';
            $config['upload_path'] = './uploads/';
            $config['max_size'] = 2048;
            $config['encrypt_name'] = TRUE;
            $config['file_ext_tolower'] = TRUE;
            $this->load->library('upload', $config);

            $this->upload->do_upload('file');
            $check = $this->Page_model->check_dup_user($fname, $lname, $username);
            if ($check->num_rows() >= 1) {
                $this->session->set_flashdata('danger', 'Dubplicate Entry.');
                redirect(base_url() . 'pages/user_new');
            } else {
                $this->Page_model->user_insert();
                $this->session->set_flashdata('success', 'Successfully saved.');
                redirect(base_url() . ($this->is_division_user_manager() ? 'pages/userlist_division' : 'pages/userlist'));
            }
        }
    }
    private function signature_model()
    {
        $this->load->model('User_signature_model');
        $this->User_signature_model->initialize();
    }

    public function edit_profile()
    {
        $user = $this->db->where('id', (int) $this->session->id)->get('users')->row();
        if (!$user) { show_404(); return; }
        $this->signature_model();
        $data = array('profile_user' => $user, 'signature' => $this->User_signature_model->get($user->id), 'self_profile' => true);
        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/edit_profile', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function edit_profile_save()
    {
        $this->require_post();
        foreach (array('fname' => 'First name', 'lname' => 'Last name') as $key => $label) {
            $this->form_validation->set_rules($key, $label, 'trim|required|max_length[100]');
        }
        $this->form_validation->set_rules('mname', 'Middle name', 'trim|max_length[100]');
        if (!$this->form_validation->run()) {
            $this->session->set_flashdata('danger', validation_errors('', ' '));
        } else {
            $data = array('fname' => trim($this->input->post('fname')), 'mname' => trim((string) $this->input->post('mname')), 'lname' => trim($this->input->post('lname')));
            if ($this->db->where('id', (int) $this->session->id)->update('users', $data)) {
                $this->session->set_userdata('user', trim(implode(' ', $data)));
                $this->session->set_flashdata('success', 'Profile updated.');
            } else { $this->session->set_flashdata('danger', 'Profile could not be updated.'); }
        }
        redirect('Pages/edit_profile');
    }

    public function user_signature_save()
    {
        $this->require_post();
        $id = (int) $this->input->post('user_id');
        $self = $id === (int) $this->session->id;
        if (!$self) { $this->require_user_manager(); $this->get_managed_user($id); }
        elseif (!$this->db->where('id', $id)->count_all_results('users')) { show_404(); return; }
        $this->signature_model();
        try {
            if (!isset($_FILES['signature']) || $_FILES['signature']['error'] !== UPLOAD_ERR_OK
                || !is_uploaded_file($_FILES['signature']['tmp_name'])) {
                throw new InvalidArgumentException('Choose a PNG or JPEG signature image of at most 1 MB.');
            }
            $this->load->library('Signature_image');
            $image = $this->signature_image->encode($_FILES['signature']['tmp_name']);
            if (!$this->User_signature_model->save($id, $image)) { throw new RuntimeException('Unable to save the signature.'); }
            $this->session->set_flashdata('success', 'E-signature saved. Return to the assessment and save it to include the signature.');
        } catch (Exception $exception) {
            $this->session->set_flashdata('danger', $exception->getMessage());
        }
        redirect($self ? 'Pages/edit_profile' : 'Pages/user_update/' . $id);
    }

    private function monitoring_monitor_identity($payload)
    {
        // A saved monitor belongs to the original user, not whoever opens the form later.
        $owner = isset($payload['monitor_user_id']) ? (int) $payload['monitor_user_id'] : (int) $this->session->id;
        if ($owner !== (int) $this->session->id) { return $payload; }
        $user = $this->db->where('id', $owner)->get('users')->row();
        if (!$user) { show_error('Your user profile could not be found.', 403); }
        $this->signature_model();
        $payload['monitor_user_id'] = $owner;
        $payload['details']['monitor_one'] = trim(implode(' ', array_filter(array((string) $user->fname, (string) $user->mname, (string) $user->lname), 'strlen')));
        if (empty($payload['monitor_signature'])) { $payload['monitor_signature'] = $this->User_signature_model->get($owner); }
        return $payload;
    }

    public function user_update()
    {
        $this->require_user_manager();

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');
        $this->form_validation->set_rules('gender', 'Gender', 'required');

        if ($this->form_validation->run() == FALSE) {

            $page = "user_edit";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Update User";
            $data['data'] = $this->get_managed_user($this->uri->segment(3));
            $this->signature_model();
            $data['signature'] = $this->User_signature_model->get($data['data']->id);


            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->get_managed_user($this->input->post('id'));
            $this->Page_model->user_update();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . ($this->is_division_user_manager() ? 'pages/userlist_division' : 'pages/userlist'));
        }
    }

    public function user_delete()
    {
        $this->require_user_manager();
        $this->require_post();
        $id = (int) $this->input->post('id', true);
        $user = $this->get_managed_user($id);

        if ((string) $user->id === (string) $this->session->id) {
            $this->session->set_flashdata('danger', 'You cannot delete your own account.');
            redirect(base_url() . ($this->is_division_user_manager() ? 'pages/userlist_division' : 'pages/userlist'));
        }

        $this->Page_model->delete_with_attach('users', $id, $user->image);
        $this->session->set_flashdata('danger', 'Successfully deleted.');
        redirect(base_url() . ($this->is_division_user_manager() ? 'pages/userlist_division' : 'pages/userlist'));
    }

    public function school_delete()
    {
        if (!$this->session->logged_in || ($this->session->position !== 'admin' && $this->session->position !== 'division')) {
            show_error('Only admin and division users can delete schools.', 403);
        }

        $this->require_post();
        $rec_id = (int) $this->input->post('id', true);

        if (empty($rec_id)) {
            show_404();
        }

        $school = $this->Page_model->one_cond_row('schools', 'recID', $rec_id);

        if (!$school) {
            show_404();
        }

        $school_id = $school->schoolID;

        // For division users, verify school belongs to their division
        if ($this->session->position === 'division' && $school->division_id != $this->session->division) {
            show_error('You can only delete schools from your own division.', 403);
        }

        // Check if school has completed Self-Assessment and Action Plan
        $has_action_plan = $this->Page_model->submission_school_ids('sgod_action_plan', $this->session->fy, [$school_id]);
        $has_self_assessment = $this->Page_model->submission_school_ids('sbm', $this->session->fy, [$school_id]);

        if (!empty($has_action_plan[$school_id]) && !empty($has_self_assessment[$school_id])) {
            $this->session->set_flashdata('danger', 'Cannot delete school: School has completed Self-Assessment and Action Plan.');
            redirect(base_url() . 'pages/school_list');
        }

        // Log audit trail before deletion
        $this->Page_model->log_audit_trail('DELETE', 'schools', $school_id, $school, null);

        // Delete school and associated user
        $this->db->where('schoolID', $school_id);
        $this->db->delete('schools');
        $this->db->where('username', $school_id);
        $this->db->delete('users');

        $this->session->set_flashdata('success', 'Successfully deleted.');
        redirect(base_url() . 'pages/school_list');
    }

    public function confirm_signup()
    {
        $user_id = (int) $this->uri->segment(3);
        $token = rawurldecode((string) $this->uri->segment(4));
        if ($user_id <= 0 || !$this->Page_model->confirm_signup($user_id, $token)) {
            show_error('This verification link is invalid or has expired.', 400);
        }
        $this->session->set_flashdata('success', 'Your account has been verified. You may now sign in.');
        redirect(base_url() . 'pages/log_in');
    }

    public function cp()
    {
        // Retired in favor of user_reset_password(), which generates a strong
        // temporary password, checks manager scope, and writes an audit event.
        show_error('This legacy password endpoint has been disabled.', 410);
    }

    public function user_reset_password()
    {
        $this->require_user_manager();
        $this->require_post();

        $id = $this->input->post('id');
        $user = $this->get_managed_user($id);
        $password = $this->Page_model->random_password();
        $redirect_url = base_url() . ($this->is_division_user_manager() ? 'pages/userlist_division' : 'pages/userlist');

        if (
            $this->is_division_user_manager()
            && $this->input->post('return_to') === 'district_account'
        ) {
            $redirect_url = base_url() . 'pages/district_account/' . rawurlencode($this->session->division);
        }

        if ($this->Page_model->reset_user_password($id, $password)) {
            $message = 'Password reset for <strong>' . html_escape($user->username)
                . '</strong>. Temporary password: <strong><code>' . html_escape($password) . '</code></strong>';
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('danger', 'Unable to reset the password.');
        }

        redirect($redirect_url);
    }

    public function profile()
    {
        if (!$this->session->logged_in || $this->session->position !== 'admin') {
            show_error('You are not authorized to update user profile pictures.', 403);
        }

        $this->require_post();
        $id = (int) $this->input->post('id', true);
        $user = $this->Page_model->one_cond_row('users', 'id', $id);
        if (!$user) {
            show_404();
        }
        $config['allowed_types'] = 'jpg|png|jpeg|gif';
        $config['upload_path'] = './uploads/';
        $config['max_size'] = 2048;
        $config['encrypt_name'] = TRUE;
        $config['file_ext_tolower'] = TRUE;
        $this->load->library('upload', $config);

        if ($this->upload->do_upload('file')) {
            $file = FCPATH . 'uploads/' . basename((string) $user->image);

            if (!empty($user->image) && is_file($file)) {
                unlink($file);
            }
            $this->Page_model->user_update_profile();
            $this->session->set_flashdata('success', 'Successfully updated.');
            redirect(base_url() . 'pages/profilelist');
        } else {
            $this->session->set_flashdata('danger', strip_tags($this->upload->display_errors('', '')));
            redirect(base_url() . 'pages/profilelist');
        }
    }

    public function user_profile()
    {
        $this->require_post();
        $id = $this->session->username;
        $user = $this->Page_model->one_cond_row('users', 'username', $id);
        $config['allowed_types'] = 'jpg|png|jpeg|gif';
        $config['upload_path'] = './uploads/';
        $config['max_size']      = 1024; // 1MB
        $config['encrypt_name'] = TRUE;
        $config['file_ext_tolower'] = TRUE;
        $this->load->library('upload', $config);

        if ($this->upload->do_upload('file')) {
            $file = FCPATH . 'uploads/' . basename((string) $user->image);

            if (!empty($user->image) && is_file($file)) {
                unlink($file);
            }
            $this->Page_model->users_update_profile();
            $this->session->set_flashdata('success', 'Successfully updated.');
            redirect(base_url());
        } else {
            $this->session->set_flashdata('danger', strip_tags($this->upload->display_errors('', '')));
            redirect(base_url());
        }
    }

    public function log_in()
    {

        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('username', 'School ID, username or email address', 'trim|required');
        $this->form_validation->set_rules('password', 'Password', 'required');

        if ($this->form_validation->run() == FALSE) {
            $page = 'home';

            if (!file_exists(APPPATH . 'views/' . $page . '.php')) {
                show_404();
            }

            $this->load->view($page, $this->homepage_data());
        } else {
            $login_identifier = trim((string) $this->input->post('username', true));
            if ($this->login_is_rate_limited($login_identifier)) {
                log_message('error', 'Rate-limited login attempt from ' . $this->input->ip_address());
                $message = 'Too many sign-in attempts. Please wait 15 minutes and try again.';
                if ($this->input->is_ajax_request()) {
                    $this->output->set_status_header(429)->set_content_type('application/json')
                        ->set_output(json_encode(array('success' => false, 'message' => $message)));
                    return;
                }
                $this->session->set_flashdata('failed', $message);
                redirect(base_url() . 'homepage');
                return;
            }
            $user_id = $this->Page_model->login();

            if ($user_id) {
                $this->clear_login_failures($login_identifier);
                $this->session->sess_regenerate(TRUE);
                $user_data = array(
                    'id' => $user_id['id'],
                    'username' => $user_id['username'],
                    'position' => $user_id['position'],
                    'user' => $user_id['fname'] . ' ' . $user_id['mname'] . ' ' . $user_id['lname'],
                    'region' => $user_id['r_id'],
                    'division' => $user_id['p_id'],
                    'district' => $user_id['d_id'],
                    'virified' => $user_id['virified'],
                    'must_change_password' => isset($user_id['must_change_password'])
                        ? (int) $user_id['must_change_password']
                        : 0,
                    'logged_in' => true

                );

                $this->session->set_userdata($user_data);
                $this->session->set_userdata('fy', date('Y'));
                $this->session->set_flashdata('user_log', 'You are now loged in as '
                    . $this->session->position);
                
                if ($this->input->is_ajax_request()) {
                    echo json_encode(['success' => true]);
                    return;
                }
                
                redirect(base_url());
            } else {
                $this->record_login_failure($login_identifier);
                if ($this->input->is_ajax_request()) {
                    echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
                    return;
                }
                
                $this->session->set_flashdata('failed', 'Invalid username or password. Please try again.');
                redirect(base_url() . 'homepage');
            }
        }
    }
    public function lock_user_screen()
    {
        // A locked session deliberately has logged_in removed, but it must
        // still be tied to the user who initiated the lock operation.
        if (!$this->session->username) {
            redirect(base_url('homepage'));
            return;
        }

        $this->form_validation->set_error_delimiters('<div class="error">', '</div>');
        $this->form_validation->set_rules('password', 'password', 'required');

        if ($this->form_validation->run() == FALSE) {

            $page = "lock_screen";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $this->load->view('pages/' . $page);
        } else {

            $user_id = $this->Page_model->lock_screen();

            if ($user_id) {

                $user_data = array(
                    'id' => $user_id['id'],
                    'username' => $user_id['username'],
                    'user' => $user_id['fname'] . ' ' . $user_id['mname'] . ' ' . $user_id['lname'],
                    'position' => $user_id['position'],
                    'region' => $user_id['r_id'],
                    'division' => $user_id['p_id'],
                    'district' => $user_id['d_id'],
                    'virified' => $user_id['virified'],
                    'must_change_password' => isset($user_id['must_change_password'])
                        ? (int) $user_id['must_change_password']
                        : 0,
                    'logged_in' => true

                );

                $this->session->sess_regenerate(TRUE);
                $this->session->set_userdata($user_data);
                $this->session->set_userdata('fy', date('Y'));
                $this->session->set_flashdata('user_log', 'You are now loged in as '
                    . $this->session->position);
                redirect(base_url());
            } else {
                $this->session->set_flashdata('failed', 'Password not match');
                redirect(base_url() . 'lock_user_screen');
            }
        }
    }
    public function logout()
    {
        // Repeated logout requests and expired sessions should return to login.
        if (!$this->session->logged_in) {
            redirect(base_url('homepage'));
            return;
        }
        // Visiting a URL must not end an active session; the Logout form posts.
        if (strtoupper((string) $this->input->method()) === 'GET') {
            redirect(base_url());
            return;
        }
        $this->require_post();
        $this->session->sess_destroy();
        redirect(base_url() . 'homepage');
    }
    public function lock()
    {
        $this->require_post();
        $this->session->unset_userdata('id');
        $this->session->unset_userdata('position');
        $this->session->unset_userdata('logged_in');

        $this->session->set_flashdata('danger', 'You are now Lock Screen Mode');
        redirect(base_url() . 'lock_user_screen');
    }

    public function get_provinces()
    {
        $region_id = $this->input->post('region_id');
        $provinces = $this->db->get_where('province', ['region_id' => $region_id])->result();
        echo json_encode($provinces);
    }

    public function get_districts()
    {
        $province_id = $this->input->post('province_id');
        $districts = $this->db->get_where('district', ['province_id' => $province_id])->result();
        echo json_encode($districts);
    }

    public function school_list()
    {

        $page = "district_schools";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School List";
        $data['division_school_scope'] = false;

        if ($this->session->position == 'admin') {
            // Load all schools for admin without filters - optimized with district and division joins
            $this->db->select('s.recID, s.schoolID, s.schoolName, s.district_id, s.division_id, d.description as district_name, div.description as division_name');
            $this->db->from('schools s');
            $this->db->join('district d', 's.district_id = d.id', 'left');
            $this->db->join('division div', 's.division_id = div.id', 'left');
            $this->db->order_by('s.schoolName', 'ASC');
            $data['data'] = $this->db->get()->result();
            
            $school_ids = array_map(function ($school) {
                return $school->schoolID;
            }, $data['data']);

            $data['submission_status'] = array(
                'sgod_action_plan' => $this->Page_model->submission_school_ids('sgod_action_plan', $this->session->fy, $school_ids),
                'sbm' => $this->Page_model->submission_school_ids('sbm', $this->session->fy, $school_ids),
                'sbm_ta' => $this->Page_model->submission_school_ids('sbm_ta', $this->session->fy, $school_ids)
            );
            $data['district'] = null;
            $data['selected_submission'] = 'sbm';
            $data['is_admin_view'] = true;
        } else {
            // Original behavior for non-admin users
            $data['data'] = $this->Common->two_join_two_cond('sbm', 'schools', 'a.school_id,a.district,b.district_id, b.schoolID,b.schoolName,a.fy', 'a.school_id = b.schoolID', 'a.fy', $this->session->fy, 'a.district', $this->session->district, 'b.schoolName', 'ASC');
            $school_ids = array_map(function ($school) {
                return $school->schoolID;
            }, $data['data']);

            $data['submission_status'] = array(
                'sgod_action_plan' => $this->Page_model->submission_school_ids('sgod_action_plan', $this->session->fy, $school_ids),
                'sbm' => $this->Page_model->submission_school_ids('sbm', $this->session->fy, $school_ids),
                'sbm_ta' => $this->Page_model->submission_school_ids('sbm_ta', $this->session->fy, $school_ids)
            );
            $data['district'] = $this->Page_model->one_cond_row('district', 'id', $this->session->division);
            $data['selected_submission'] = 'sbm';
            $data['is_admin_view'] = false;
        }

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function school_list_region()
    {
        $this->require_region_dashboard_access();

        $page = "school_list_region";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School List";

        // Load all schools with division info
        $data['data'] = $this->db
            ->select('s.schoolID, s.schoolName, d.description as division_name')
            ->from('schools s')
            ->join('division d', 's.division_id = d.id', 'left')
            ->where('s.region_id', (int) $this->session->region)
            ->order_by('s.schoolName', 'ASC')
            ->get()
            ->result();

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function school_list_division_only()
    {
        $this->require_division_dashboard_access();

        $page = "school_list_division_only";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School List";

        // Load schools for this division only with district info
        $data['data'] = $this->db
            ->select('s.recID, s.schoolID, s.schoolName, d.description as district_name')
            ->from('schools s')
            ->join('district d', 's.district_id = d.id', 'left')
            ->where('s.division_id', $this->session->division)
            ->order_by('s.schoolName', 'ASC')
            ->get()
            ->result();

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function school_profile_region()
    {
        $this->require_region_dashboard_access();

        $school_id = $this->uri->segment(3);

        if (!$school_id) {
            show_404();
        }

        $data['title'] = "School Profile";
        $data['school'] = $this->Common->one_cond_row('schools', 'schoolID', $school_id);

        if (!$data['school']) {
            show_404();
        }
        $this->require_school_scope($data['school']);

        // Get school details with division
        $data['school']->division = $this->Common->one_cond_row('division', 'id', $data['school']->division_id);
        $data['school']->district = $this->Common->one_cond_row('district', 'id', $data['school']->district_id);

        // Get SBM checklist data
        $data['sbm'] = $this->Common->two_cond_row('sbm', 'school_id', $school_id, 'fy', $this->session->fy);

        // Get TA form data
        $data['ta'] = $this->Common->two_cond_row('sbm_ta', 'school_id', $school_id, 'fy', $this->session->fy);

        // Get TANA form data
        $data['tana'] = $this->Common->two_cond_row('tana', 'school_id', $school_id, 'fy', $this->session->fy);

        // Get Action Plan data
        $data['action_plan'] = $this->Common->two_cond_row('sgod_action_plan', 'school_id', $school_id, 'fy', $this->session->fy);

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/school_profile_region', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function school_profile_division()
    {
        $this->require_division_dashboard_access();

        $school_id = $this->uri->segment(3);

        if (!$school_id) {
            show_404();
        }

        $data['title'] = "School Profile";
        $data['school'] = $this->Common->one_cond_row('schools', 'schoolID', $school_id);

        if (!$data['school']) {
            show_404();
        }

        // Verify school belongs to this division
        if ($data['school']->division_id != $this->session->division) {
            show_404();
        }

        // Get school details with division
        $data['school']->division = $this->Common->one_cond_row('division', 'id', $data['school']->division_id);
        $data['school']->district = $this->Common->one_cond_row('district', 'id', $data['school']->district_id);

        // Get SBM checklist data
        $data['sbm'] = $this->Common->two_cond_row('sbm', 'school_id', $school_id, 'fy', $this->session->fy);

        // Get TA form data
        $data['ta'] = $this->Common->two_cond_row('sbm_ta', 'school_id', $school_id, 'fy', $this->session->fy);

        // Get TANA form data
        $data['tana'] = $this->Common->two_cond_row('tana', 'school_id', $school_id, 'fy', $this->session->fy);

        // Get Action Plan data
        $data['action_plan'] = $this->Common->two_cond_row('sgod_action_plan', 'school_id', $school_id, 'fy', $this->session->fy);

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/school_profile_region', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function school_list_division()
    {

        $page = "district_schools";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School List";
        $table = $this->uri->segment(4);
        $allowed_tables = array('sgod_action_plan', 'sbm', 'sbm_ta');

        if (!in_array($table, $allowed_tables, true)) {
            show_404();
        }

        $district_id = (int) $this->uri->segment(3);
        $district = $this->Page_model->one_cond_row('district', 'id', $district_id);
        $this->require_district_scope($district);

        // Get all schools in the district, not just those with submissions to the selected table
        $data['data'] = $this->db
            ->select('schoolID, schoolName')
            ->from('schools')
            ->where('district_id', $district_id)
            ->order_by('schoolName', 'ASC')
            ->get()
            ->result();
        $school_ids = array_map(function ($school) {
            return $school->schoolID;
        }, $data['data']);

        $data['submission_status'] = array(
            'sgod_action_plan' => $this->Page_model->submission_school_ids('sgod_action_plan', $this->session->fy, $school_ids),
            'sbm' => $this->Page_model->submission_school_ids('sbm', $this->session->fy, $school_ids),
            'sbm_ta' => $this->Page_model->submission_school_ids('sbm_ta', $this->session->fy, $school_ids)
        );
        $data['district'] = $district;
        $data['selected_submission'] = $table;
        $data['division_school_scope'] = true;


        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function district_list_division()
    {

        $page = "division_district";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School List";

        $table = $this->uri->segment(4);
        $allowed_tables = array('sgod_action_plan', 'sbm', 'sbm_ta');

        if (!in_array($table, $allowed_tables, true)) {
            show_404();
        }

        $division_id = (int) $this->uri->segment(3);
        $division = $this->Page_model->one_cond_row('division', 'id', $division_id);
        $this->require_division_scope($division);

        //$data['data'] = $this->Page_model->one_cond('schools','p_id',$this->session->p_id);
        //$data['data'] = $this->Page_model->schools_with_district($this->uri->segment(3));
        $data['data'] = $this->Common->two_join_two_cond_gb($table, 'schools', 'a.school_id,a.district,b.district_id, b.schoolID,b.schoolName,a.division', 'a.school_id = b.schoolID', 'fy', $this->session->fy, 'a.division', $division_id, 'b.schoolName', 'ASC','a.school_id');
        $school_ids = array_map(function ($school) {
            return $school->schoolID;
        }, $data['data']);

        $data['submission_status'] = array(
            'sgod_action_plan' => $this->Page_model->submission_school_ids('sgod_action_plan', $this->session->fy, $school_ids),
            'sbm' => $this->Page_model->submission_school_ids('sbm', $this->session->fy, $school_ids),
            'sbm_ta' => $this->Page_model->submission_school_ids('sbm_ta', $this->session->fy, $school_ids)
        );
        $data['selected_submission'] = $table;
        $data['division'] = $division;
        $data['districts'] = $this->Page_model->get_districts_by_division($division_id);


        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function change_fy()
    {
        $this->require_post();
        $new_fy = (int) $this->input->post('new_fy', true);
        if ($new_fy >= 2000 && $new_fy <= (int) date('Y') + 1) {
            $this->session->set_userdata('fy', $new_fy);
        }
        $return_to = trim((string) $this->input->post('return_to', true), '/');
        $has_safe_return = $return_to !== ''
            && strpos($return_to, '..') === false
            && strpos($return_to, '//') === false
            && preg_match('/^[A-Za-z0-9_\.\/-]+$/', $return_to);
        redirect($has_safe_return ? base_url($return_to) : base_url());
    }

    public function school_new()
    {

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');
        $this->form_validation->set_rules('schoolID', 'School ID', 'required');

        if ($this->form_validation->run() == FALSE) {

            $page = "school_add";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Add New School";
            $data['district'] = $this->Page_model->one_cond('district', 'division_id', $this->session->p_id);


            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->school_insert();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/school_list/' . $this->input->post('schoolType'));
        }
    }

    public function get_district_by_division()
    {
        $division_id = $this->input->post('division_id');

        $districts = $this->Page_model->get_districts_by_division($division_id);
        echo json_encode($districts);
    }

    function sbm_action_plan()
    {

        $page = "action_plan_list";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Action Plan for Implementation of SBM";

        $data['data'] = $this->Common->two_cond('sgod_action_plan', 'school_id', $this->session->username, 'fy', $this->session->fy);

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }


    function sbm_action_plan_pview()
    {

        $page = "action_plan_pview";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Action Plan for Implementation of SBM";

        $data['school'] = $this->Common->one_cond_row('schools', 'schoolID', $this->session->username);
        $data['data'] = $this->Common->two_cond('sgod_action_plan', 'fy', $this->session->fy, 'school_id', $this->session->username);

        $this->load->view('pages/' . $page, $data);
    }

    function sbm_action_plan_pview_district()
    {

        $page = "action_plan_pview";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Action Plan for Implementation of SBM";

        $school_id = (string) $this->uri->segment(3);
        $data['school'] = $this->Common->one_cond_row('schools', 'schoolID', $school_id);
        $this->require_school_scope($data['school']);
        $data['data'] = $this->Common->two_cond('sgod_action_plan', 'fy', $this->session->fy, 'school_id', $school_id);

        $this->load->view('pages/' . $page, $data);
    }

    public function sbm_action_plan_list()
    {
        $this->require_region_dashboard_access();

        $school_id = $this->uri->segment(3);

        if (!$school_id) {
            show_404();
        }

        $page = "action_plan_list";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Action Plan for Implementation of SBM";
        $data['school'] = $this->Common->one_cond_row('schools', 'schoolID', $school_id);
        $data['data'] = $this->Common->two_cond('sgod_action_plan', 'school_id', $school_id, 'fy', $this->session->fy);

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function action_plan_new()
    {

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');
        $this->form_validation->set_rules('activity', 'Activity', 'required');

        if ($this->form_validation->run() == FALSE) {

            $page = "action_plan_new";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "New Action Plan for Implementation of SBM";


            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->action_plan_insert();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/sbm_action_plan');
        }
    }

    public function sbm_action_plan_update()
    {
        $record_id = strtoupper((string) $this->input->method()) === 'POST'
            ? (int) $this->input->post('id', true)
            : (int) $this->uri->segment(3);
        if ($record_id > 0) {
            $this->school_for_record('sgod_action_plan', $record_id);
        }

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');
        $this->form_validation->set_rules('activity', 'Activity', 'required');

        if ($this->form_validation->run() == FALSE) {

            $page = "action_plan_edit";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Update Action Plan for Implementation of SBM";
            $data['data'] = $this->Common->one_cond_row('sgod_action_plan', 'id', $this->uri->segment(3));

            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->action_plan_update();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/sbm_action_plan');
        }
    }

    public function sbm_checklist()
    {

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');

        $this->form_validation->set_rules('district', 'District', 'required');

        if ($this->form_validation->run() == FALSE) {

            $data['sbmc'] = $this->Common->two_cond_row('sbm', 'school_id', $this->session->username, 'fy', $this->session->fy);

            $page = !$data['sbmc'] ? 'sbm_form' : 'sbm_form_update';



            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "New Action Plan";

            $data['sbm'] = $this->Common->no_cond('sbm_indicator');
            $data['sbm_sub'] = $this->Common->no_cond('sbm_sub_indicator');


            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->sbm_checklist_insert();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/sbm_checklist');
        }
    }

    public function checklist_district()
    {

        $school_id = $this->uri->segment(3);
        $school = $this->Common->one_cond_row('schools', 'schoolID', $school_id);
        $this->require_school_scope($school);
        $data['sbmc'] = $this->Common->two_cond_row('sbm', 'school_id', $school_id, 'fy', $this->session->fy);

        $page = 'sbm_form_update';

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "New Action Plan";

        $data['sbm'] = $this->Common->no_cond('sbm_indicator');
        $data['sbm_sub'] = $this->Common->no_cond('sbm_sub_indicator');

        // Get school information
        $data['school_name'] = !empty($school) ? $school->schoolName : '';

        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function sbm_checklist_update()
    {
        $this->require_post();
        $this->school_for_record('sbm', (int) $this->input->post('id', true));
        $this->Page_model->sbm_checklist_update();
        $this->session->set_flashdata('success', 'Successfully saved.');
        redirect(base_url() . 'pages/sbm_checklist');
    }
    function sbm_checklist_final()
    {
        $this->require_post();
        $checklist_id = (int) $this->input->post('id', true);
        $this->school_for_record('sbm', $checklist_id);
        $this->Page_model->sbm_checklist_lock_unloc_by_id($checklist_id, 1, (string) $this->session->username);
        $this->session->set_flashdata('success', 'Checklist finalized. PDF download is starting.');
        redirect(base_url() . 'Pages/sbm_checklist_pdf');
    }

    public function sbm_checklist_pdf()
    {
        $page = 'sbm_checklist_pdf';

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $position = strtolower(trim((string) $this->session->position));
        $is_school_user = $position === 'school';
        $view_school_id = $is_school_user ? (string) $this->session->username : (string) $this->uri->segment(3);

        if ($view_school_id === '') {
            show_404();
        }

        $view_school = $this->Common->one_cond_row('schools', 'schoolID', $view_school_id);
        $this->require_school_scope($view_school);

        $data['sbmc'] = $this->Common->two_cond_row('sbm', 'school_id', $view_school_id, 'fy', $this->session->fy);

        if (!$data['sbmc'] || !isset($data['sbmc']->stat) || (int) $data['sbmc']->stat !== 1) {
            $this->session->set_flashdata('danger', 'Finalize the checklist first before generating its PDF.');

            if ($is_school_user) {
                redirect(base_url() . 'Pages/sbm_checklist');
            } else {
                redirect(base_url() . 'Pages/checklist_district/' . rawurlencode($view_school_id));
            }

            return;
        }

        $data['title'] = 'SBM Checklist PDF';
        $data['view_school_id'] = $view_school_id;
        $data['school'] = $this->Common->one_cond_row('schools', 'schoolID', $view_school_id);
        $data['division'] = ($data['school'] && !empty($data['school']->division_id))
            ? $this->Page_model->one_cond_row('division', 'id', $data['school']->division_id)
            : null;
        $data['district'] = ($data['school'] && !empty($data['school']->district_id))
            ? $this->Page_model->one_cond_row('district', 'id', $data['school']->district_id)
            : null;
        $data['sbm'] = $this->Common->no_cond('sbm_indicator');
        $data['sbm_sub'] = $this->Common->no_cond('sbm_sub_indicator');

        $this->load->view('pages/' . $page, $data);
    }

    public function tapr_form()
    {
        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');

        $this->form_validation->set_rules('district', 'District', 'required');

        if ($this->form_validation->run() == FALSE) {

            $data['sbmc'] = $this->Common->two_cond_row('sbm_ta', 'school_id', $this->session->username, 'fy', $this->session->fy);

            $page = !$data['sbmc'] ? 'sbm_ta' : 'sbm_ta_update';



            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Technical Assistance Needs Assessment Form";

            $data['sbm'] = $this->Common->no_cond('sbm_indicator');
            $data['sbm_sub'] = $this->Common->no_cond('sbm_sub_indicator');


            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->sbm_ta_insert();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/tapr_form');
        }
    }

    public function tapr_form_update()
    {
        $this->require_post();
        $this->school_for_record('sbm_ta', (int) $this->input->post('id', true));
        $record = $this->Common->two_cond_row('sbm_ta', 'school_id', $this->session->username, 'fy', $this->session->fy);

        if ($record && isset($record->stat) && (int) $record->stat === 1) {
            $this->session->set_flashdata('danger', 'This TA form is finalized and locked. Coordinate with your division reviewer if revisions are needed.');
            redirect(base_url() . 'pages/tapr_form');
            return;
        }

        $this->Page_model->sbm_ta_update();
        $this->session->set_flashdata('success', 'Successfully saved.');
        redirect(base_url() . 'pages/tapr_form');
    }

    public function tana_form()
    {

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');

        $this->form_validation->set_rules('district', 'District', 'required');

        if ($this->form_validation->run() == FALSE) {

            $data['tana'] = $this->Common->two_cond_row('tana', 'school_id', $this->session->username, 'fy', $this->session->fy);

            $page = !$data['tana'] ? 'sbm_tana' : 'sbm_tana_update';



            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Technical Assistance Needs Assessment Form";

            $data['sbm'] = $this->Common->no_cond('sbm_indicator');
            $data['sbm_sub'] = $this->Common->no_cond('sbm_sub_indicator');


            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->sbm_tana_insert();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/tana_form');
        }
    }

    public function tana_summary()
    {

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');

        $this->form_validation->set_rules('district', 'District', 'required');

        if ($this->form_validation->run() == FALSE) {

            //$data['tana'] = $this->Common->two_cond_row('tana', 'school_id', $this->session->username, 'fy', $this->session->fy);
            //$data['indicator'] = $this->Common->no_cond('sbm_sub_indicator');

            $page = 'sbm_tana_top_list';



            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Technical Assistance Needs Assessment Priority Basis";

            $data['sbm'] = $this->Common->no_cond('sbm_indicator');
            $data['sbm_sub'] = $this->Common->no_cond('sbm_sub_indicator');

            $ta = $this->Common->two_cond_row('sbm_ta', 'school_id', $this->session->username, 'fy', $this->session->fy);
            $averages = $this->Page_model->get_averages($this->session->username, $this->session->fy);
            arsort($averages);

            $top20 = array();
            foreach ($averages as $index => $avg) {
                $col = 'q' . $index;
                $text = ($ta && isset($ta->$col) && $ta->$col !== null) ? trim((string) $ta->$col) : '';

                if ($text === '') {
                    continue;
                }

                $top20[$index] = $avg;

                if (count($top20) >= 20) {
                    break;
                }
            }

            $data['averages'] = $top20;


            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->sbm_tana_summary_insert();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/tana_summary');
        }
    }

    public function tana_summary_division()
    {

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');

        $this->form_validation->set_rules('district', 'District', 'required');

        if ($this->form_validation->run() == FALSE) {

            //$data['tana'] = $this->Common->two_cond_row('tana', 'school_id', $this->session->username, 'fy', $this->session->fy);
            //$data['indicator'] = $this->Common->no_cond('sbm_sub_indicator');

            $page = 'sbm_tana_top_list_division';



            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Technical Assistance Needs Assessment Priority Basis";
            $data['data'] = $this->Page_model->get_seq_one_two();
            $data['ivy'] = $this->Common->two_cond_order_by('division_tana','fy',$this->session->fy,'division',$this->session->division,'sequence','ASC');



            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->sbm_tana_summary_insert();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/tana_summary');
        }
    }

    public function tana_summary_region()
    {

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');

        $this->form_validation->set_rules('district', 'District', 'required');

        if ($this->form_validation->run() == FALSE) {

            //$data['tana'] = $this->Common->two_cond_row('tana', 'school_id', $this->session->username, 'fy', $this->session->fy);
            //$data['indicator'] = $this->Common->no_cond('sbm_sub_indicator');

            $page = 'tana_region';



            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Technical Assistance Needs Assessment Priority Basis";
            $data['data'] = $this->Page_model->two_cond('division_tana','region',$this->session->region, 'fy', $this->session->fy);
            $data['ivy'] = $this->Common->two_cond_order_by('region_tana','fy',$this->session->fy,'region',$this->session->region,'sequence','ASC');



            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->sbm_tana_summary_region_insert();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/tana_summary');
        }
    }

    public function tana_summary_update()
    {
        $this->require_post();
        $this->Page_model->delete_two_cond('tana_summary','fy',$this->session->fy,'school_id',$this->session->username);
        $this->Page_model->sbm_tana_summary_insert();
        $this->session->set_flashdata('success', 'Successfully saved.');
        redirect(base_url() . 'pages/tana_summary');
    }

    public function tana_division()
    {
        $this->require_post();
        $this->Page_model->tana_division_insert();
        $this->session->set_flashdata('success', 'Successfully saved.');
        redirect(base_url() . 'pages/tana_summary_division');
    }

    public function tana_division_autogenerate()
    {
        $this->require_post();
        $result = $this->Page_model->tana_division_autogenerate();

        if (!empty($result['status'])) {
            $message = 'Successfully generated ' . $result['count'] . ' thematic ' . ($result['count'] === 1 ? 'analysis' : 'analyses') . ' from ' . $result['source_count'] . ' priority ' . ($result['source_count'] === 1 ? 'concern' : 'concerns') . '.';

            if (!empty($result['truncated'])) {
                $message .= ' Only the top 20 recurring concerns were kept.';
            }

            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('danger', !empty($result['message']) ? $result['message'] : 'Unable to auto-generate thematic analysis.');
        }

        redirect(base_url() . 'pages/tana_summary_division');
    }

    public function tana_region()
    {
        $this->require_post();
        $this->Page_model->tana_region_insert();
        $this->session->set_flashdata('success', 'Successfully saved.');
        redirect(base_url() . 'pages/tana_summary_region');
    }

    public function tana_form_update()
    {
        $this->require_post();
        $this->school_for_record('tana', (int) $this->input->post('id', true));
        $position = strtolower(trim((string) $this->session->position));
        $is_school_user = $position === 'school';

        if ($is_school_user) {
            $this->Page_model->sbm_tana_update();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/tana_form');
            return;
        }

        $this->Page_model->sbm_tana_update();
        $this->session->set_flashdata('success', 'Successfully saved.');
        redirect(base_url() . 'pages/tana_form');
    }

    function sbm_ta_final()
    {
        $this->require_post();
        $ta_id = (int) $this->input->post('id', true);
        $this->school_for_record('sbm_ta', $ta_id);
        $this->Page_model->sbm_ta_lock_unloc_by_id($ta_id, 1, (string) $this->session->username);
        $this->session->set_flashdata('success', 'TA report finalized successfully. It is now locked until a division reviewer unlocks it.');
        redirect(base_url() . 'Pages/tapr_form');
    }

    public function sbm_ta_unlock()
    {
        $position = strtolower(trim((string) $this->session->position));
        $is_division_user = $position === 'division';

        if (!$is_division_user) {
            show_error('Only division users can unlock TA reports.', 403);
        }

        $this->ensure_unlock_request_table();

        $this->require_post();
        $ta_id = (int) $this->input->post('id', true);
        list($ta_record, $school) = $this->school_for_record('sbm_ta', $ta_id);
        if ($ta_id) {
            // Mark any pending unlock requests for this TA as approved
            $this->db->where('ta_id', $ta_id)
                ->where('status', 'pending')
                ->update('unlock_request', array(
                    'status' => 'approved',
                    'processed_date' => date('Y-m-d H:i:s'),
                    'processed_by' => $this->session->username
                ));
        }

        $this->Page_model->sbm_ta_lock_unloc_by_id($ta_id, 0);
        $this->session->set_flashdata('success', 'TA report unlocked successfully. The school can now edit it again.');

        // Check if this is called from division view (has school_id parameter)
        $school_id = (string) $school->schoolID;
        if ($school_id) {
            redirect(base_url() . 'Pages/tapr_form_district/' . rawurldecode($school_id));
        } else {
            redirect(base_url() . 'Pages/tapr_form');
        }
    }

    private function ensure_unlock_request_table()
    {
        // Create table if it doesn't exist
        $this->db->query("CREATE TABLE IF NOT EXISTS `unlock_request` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `ta_id` int(11) NOT NULL,
          `school_id` varchar(255) NOT NULL,
          `division_id` int(11) NOT NULL,
          `requested_by` varchar(255) NOT NULL,
          `request_date` datetime NOT NULL,
          `status` enum('pending','approved','cleared') DEFAULT 'pending',
          `processed_date` datetime DEFAULT NULL,
          `processed_by` varchar(255) DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `ta_id` (`ta_id`),
          KEY `school_id` (`school_id`),
          KEY `division_id` (`division_id`),
          KEY `status` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Check if request_type column exists, if not add it
        $columns = $this->db->query("SHOW COLUMNS FROM `unlock_request` LIKE 'request_type'")->num_rows();
        if ($columns == 0) {
            $this->db->query("ALTER TABLE `unlock_request` ADD COLUMN `request_type` enum('ta','checklist') NOT NULL DEFAULT 'ta' AFTER `id`");
            $this->db->query("ALTER TABLE `unlock_request` ADD INDEX `request_type` (`request_type`)");
        }

        // Check if checklist_id column exists, if not add it
        $columns = $this->db->query("SHOW COLUMNS FROM `unlock_request` LIKE 'checklist_id'")->num_rows();
        if ($columns == 0) {
            $this->db->query("ALTER TABLE `unlock_request` ADD COLUMN `checklist_id` int(11) DEFAULT NULL AFTER `ta_id`");
            $this->db->query("ALTER TABLE `unlock_request` ADD INDEX `checklist_id` (`checklist_id`)");
        }

        // Make ta_id nullable if it's not
        $this->db->query("ALTER TABLE `unlock_request` MODIFY COLUMN `ta_id` int(11) DEFAULT NULL");
    }

    public function sbm_ta_unlock_request()
    {
        $position = strtolower(trim((string) $this->session->position));
        $is_school_user = $position === 'school';

        if (!$is_school_user) {
            show_error('Only school users can request TA report unlocks.', 403);
        }

        $this->require_post();
        $ta_id = (int) $this->input->post('id', true);
        if (empty($ta_id)) {
            show_404();
        }

        // Ensure table exists
        $this->ensure_unlock_request_table();

        // Get the TA record to get school_id
        $ta_record = $this->Common->one_cond_row('sbm_ta', 'id', $ta_id);
        if (!$ta_record) {
            show_404();
        }
        if ((string) $ta_record->school_id !== (string) $this->session->username) {
            show_error('You can only request an unlock for your own TA report.', 403);
        }

        // Get school to get division_id
        $school = $this->Common->one_cond_row('schools', 'schoolID', $ta_record->school_id);
        if (!$school) {
            show_error('School not found.', 404);
        }

        // Check if request already exists
        $existing_request = $this->db->where('ta_id', $ta_id)
            ->where('status', 'pending')
            ->get('unlock_request')
            ->row();

        if ($existing_request) {
            $this->session->set_flashdata('danger', 'An unlock request is already pending for this TA report.');
            redirect(base_url() . 'Pages/tapr_form');
        }

        // Create unlock request
        $request_data = array(
            'request_type' => 'ta',
            'ta_id' => $ta_id,
            'school_id' => $ta_record->school_id,
            'division_id' => $school->division_id,
            'requested_by' => $this->session->username,
            'request_date' => date('Y-m-d H:i:s'),
            'status' => 'pending'
        );

        $this->db->insert('unlock_request', $request_data);

        $this->session->set_flashdata('success', 'Unlock request sent to your division reviewer. You will be notified when it is approved.');
        redirect(base_url() . 'Pages/tapr_form');
    }

    public function sbm_checklist_unlock_request()
    {
        $position = strtolower(trim((string) $this->session->position));
        $is_school_user = $position === 'school';

        if (!$is_school_user) {
            show_error('Only school users can request checklist unlocks.', 403);
        }

        $this->require_post();
        $checklist_id = (int) $this->input->post('id', true);
        if (empty($checklist_id)) {
            show_404();
        }

        $this->ensure_unlock_request_table();

        // Get the checklist record to get school_id
        $checklist_record = $this->Common->one_cond_row('sbm', 'id', $checklist_id);
        if (!$checklist_record) {
            show_404();
        }
        if ((string) $checklist_record->school_id !== (string) $this->session->username) {
            show_error('You can only request an unlock for your own checklist.', 403);
        }

        // Get school to get division_id
        $school = $this->Common->one_cond_row('schools', 'schoolID', $checklist_record->school_id);
        if (!$school) {
            show_error('School not found.', 404);
        }

        // Check if request already exists
        $existing_request = $this->db->where('checklist_id', $checklist_id)
            ->where('status', 'pending')
            ->get('unlock_request')
            ->row();

        if ($existing_request) {
            $this->session->set_flashdata('danger', 'An unlock request is already pending for this checklist.');
            redirect(base_url() . 'Pages/sbm_checklist');
        }

        // Create unlock request
        $request_data = array(
            'request_type' => 'checklist',
            'checklist_id' => $checklist_id,
            'school_id' => $checklist_record->school_id,
            'division_id' => $school->division_id,
            'requested_by' => $this->session->username,
            'request_date' => date('Y-m-d H:i:s'),
            'status' => 'pending'
        );

        $this->db->insert('unlock_request', $request_data);

        $this->session->set_flashdata('success', 'Unlock request sent to your division reviewer. You will be notified when it is approved.');
        redirect(base_url() . 'Pages/sbm_checklist');
    }

    public function clear_unlock_requests()
    {
        $position = strtolower(trim((string) $this->session->position));
        $is_division_user = $position === 'division';

        if (!$is_division_user) {
            show_error('Only division users can clear unlock requests.', 403);
        }
        $this->require_post();

        $this->ensure_unlock_request_table();

        $this->db->where('division_id', $this->session->division)
            ->where('status', 'pending')
            ->update('unlock_request', array('status' => 'cleared'));

        $this->session->set_flashdata('success', 'All pending unlock requests have been cleared.');
        redirect(base_url());
    }

    public function unlock_requests()
    {
        $position = strtolower(trim((string) $this->session->position));
        $is_division_user = $position === 'division';

        if (!$is_division_user) {
            show_error('Only division users can view unlock requests.', 403);
        }

        $this->ensure_unlock_request_table();

        $page = "unlock_requests";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "TA Unlock Requests";
        $data['requests'] = $this->db->where('division_id', $this->session->division)
            ->order_by('request_date', 'DESC')
            ->get('unlock_request')
            ->result();

        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function unlock_request_view()
    {
        $position = strtolower(trim((string) $this->session->position));
        $is_division_user = $position === 'division';

        if (!$is_division_user) {
            show_error('Only division users can view unlock requests.', 403);
        }

        $this->ensure_unlock_request_table();

        $request_id = $this->uri->segment(3);
        if (empty($request_id)) {
            show_404();
        }

        $request = $this->db->where('id', $request_id)
            ->where('division_id', $this->session->division)
            ->get('unlock_request')
            ->row();

        if (!$request) {
            show_404();
        }

        $school = $this->Common->one_cond_row('schools', 'schoolID', $request->school_id);
        $ta_record = $this->Common->one_cond_row('sbm_ta', 'id', $request->ta_id);

        $page = "unlock_request_view";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "TA Unlock Request Details";
        $data['request'] = $request;
        $data['school'] = $school;
        $data['ta_record'] = $ta_record;

        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }





    public function action_plan_delete()
    {
        $this->require_post();
        $record_id = (int) $this->input->post('id', true);
        $this->school_for_record('sgod_action_plan', $record_id);
        $this->db->where('id', $record_id)
            ->where('school_id', (string) $this->session->username)
            ->delete('sgod_action_plan');
        $this->session->set_flashdata('danger', 'Successfully deleted.');
        redirect(base_url() . 'pages/sbm_action_plan');
    }

    public function tapr_form_district()
    {
        if ($this->form_validation->run() == FALSE) {
            $view_school_id = rawurldecode((string) $this->uri->segment(3));
            $scope_school = $this->Common->one_cond_row('schools', 'schoolID', $view_school_id);
            $this->require_school_scope($scope_school);

            $data['sbm_remark'] = $this->Common->two_cond_row('sbm_remark_admin', 'school_id', $view_school_id, 'fy', $this->session->fy);

            $page = !$data['sbm_remark'] ? 'sbm_ta_district' : 'sbm_ta_district_update';

            //$page = 'sbm_ta_district';

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Technical Assistance Provision Review";

            $data['sbm'] = $this->Common->no_cond('sbm_indicator');
            $data['sbm_sub'] = $this->Common->no_cond('sbm_sub_indicator');
            $data['sbmc_count'] = $this->Common->two_cond_count_row('sbm_ta', 'school_id', $view_school_id, 'fy', $this->session->fy);
            $data['sbmc'] = $this->Common->two_cond_row('sbm_ta', 'school_id', $view_school_id, 'fy', $this->session->fy);
            $data['view_school_id'] = $view_school_id;
            $data['school'] = $this->db->query(
                "SELECT * FROM schools WHERE TRIM(schoolID) = ? LIMIT 1",
                array(trim($view_school_id))
            )->row();
            if (!$data['school'] && $data['sbmc']) {
                $data['school'] = $this->db->query(
                    "SELECT * FROM schools WHERE TRIM(schoolID) = ? LIMIT 1",
                    array(trim((string) $data['sbmc']->school_id))
                )->row();
            }
            $data['checklist_record'] = $this->Common->two_cond_row('sbm', 'school_id', $view_school_id, 'fy', $this->session->fy);
            $data['division'] = ($data['school'] && !empty($data['school']->division_id))
                ? $this->Page_model->one_cond_row('division', 'id', $data['school']->division_id)
                : null;
            $data['district'] = ($data['school'] && !empty($data['school']->district_id))
                ? $this->Page_model->one_cond_row('district', 'id', $data['school']->district_id)
                : null;

            //$data['lock'] = $this->Common->three_cond_count_row('sbm_ta', 'school_id', $this->uri->segment(3), 'fy', $this->session->fy,'stat',1);



            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        }
    }

    function tapr_admin()
    {
        $this->require_post();
        $school = $this->Common->one_cond_row('schools', 'schoolID', $this->input->post('school_id', true));
        $this->require_school_scope($school);
        $this->Page_model->sbm_cecklist_admin_insert();
        $this->session->set_flashdata('success', 'Saved successfully.');
        redirect(base_url() . 'Pages/tapr_form_district/' . $this->input->post('school_id'));
    }
    function tapr_district_update()
    {
        $this->require_post();
        $remark = $this->Common->one_cond_row('sbm_remark_admin', 'id', (int) $this->input->post('id', true));
        if (!$remark) {
            show_404();
        }
        $school = $this->Common->one_cond_row('schools', 'schoolID', $remark->school_id);
        $this->require_school_scope($school);
        $this->Page_model->sbm_cecklist_admin_update();
        $this->session->set_flashdata('success', 'Saved successfully.');
        redirect(base_url() . 'Pages/tapr_form_district/' . $this->input->post('school_id'));
    }


    public function sbm_district_tech()
    {

        $page = 'sbm_district_tech';

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Technical Assistance Provision Form";

        $data['data'] = $this->Common->two_cond('sbm_tech', 'district', $this->session->district, 'fy', $this->session->fy);
        $data['district'] = $this->Page_model->one_cond_row('district', 'id', $this->session->district);


        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function sbm_district_tech_new()
    {

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');

        $this->form_validation->set_rules('ta_rec', 'Technical Assisstance', 'required');

        if ($this->form_validation->run() == FALSE) {

            $page = 'sbm_district_tech_new';

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }


            $data['title'] = "Create Technical Assistance Entry";
            $data['district'] = $this->Page_model->one_cond_row('district', 'id', $this->session->district);

            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->sbm_tech_insert();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/sbm_district_tech');
        }
    }

    public function sbm_district_tech_edit()
    {
        $is_post = strtoupper((string) $this->input->method()) === 'POST';
        $entry_id = (int) ($is_post
            ? $this->input->post('id', true)
            : $this->uri->segment(3));
        $entry = $this->Common->one_cond_row('sbm_tech', 'id', $entry_id);
        if (!$entry || (int) $entry->district !== (int) $this->session->district) {
            show_404();
        }

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');

        $this->form_validation->set_rules('ta_rec', 'Technical Assisstance', 'required');

        if ($this->form_validation->run() == FALSE) {

            $page = 'sbm_district_tech_update';

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['data'] = $entry;
            $data['title'] = "Update Technical Assistance Entry";
            $data['district'] = $this->Page_model->one_cond_row('district', 'id', $this->session->district);

            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $this->Page_model->sbm_tech_update();
            $this->session->set_flashdata('success', 'Successfully saved.');
            redirect(base_url() . 'pages/sbm_district_tech');
        }
    }

    public function sbm_district_tech_del()
    {
        $this->require_post();
        $id = (int) $this->input->post('id', true);
        $entry = $this->Common->one_cond_row('sbm_tech', 'id', $id);

        if (!$entry || (int) $entry->district !== (int) $this->session->district) {
            $this->session->set_flashdata('danger', 'Technical assistance entry not found.');
            redirect(base_url() . 'pages/sbm_district_tech');
            return;
        }

        $this->Page_model->delete('sbm_tech', 'id', $id);
        $this->session->set_flashdata('danger', 'Successfully deleted.');
        redirect(base_url() . 'pages/sbm_district_tech');
    }



    // function sbm_list()
    // {
    // 	$_SESSION['sbm_fy'] = $this->input->post('fy');
    // 	$result['title'] = "SCHOOL-BASED FEEDING PROGRAM";
    // 	$result['sbm'] = $this->Common->no_cond('sbm_indicator');
    // 	$result['sbm_sub'] = $this->Common->no_cond('sbm_sub_indicator');

    // 	$sbm = $this->Common->one_cond_count_row('sbm', 'school_id', $this->uri->segment(3));
    // 	$result['sbmc'] = $this->Common->one_cond_row('sbm', 'school_id', $this->uri->segment(3));

    // 	$result['sbmcr'] = $this->Common->one_cond_row('sbm_remark', 'school_id', $this->uri->segment(3));
    // 	$sbmr = $this->Common->one_cond_count_row('sbm_remark', 'school_id', $this->uri->segment(3));


    // 	$this->load->view('sbm_list', $result);
    // }

    public function sbm_list()
    {

        $page = 'sbm_list';

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Technical Assisstance Provision Form";

        $data['sbm'] = $this->Common->no_cond('sbm_indicator');
        $data['sbm_sub'] = $this->Common->no_cond('sbm_sub_indicator');

        $data['data'] = $this->Common->two_cond('sbm_tech', 'district', $this->session->district, 'fy', $this->session->fy);


        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    public function district_list()
    {

        $page = "district";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "List Of District";

        $data['data'] = $this->db
            ->where('division_id', $this->session->division)
            ->order_by('description', 'ASC')
            ->get('district')
            ->result();
        $data['submission_counts'] = array(
            'sgod_action_plan' => $this->Page_model->district_submission_counts(
                'sgod_action_plan',
                $this->session->division,
                $this->session->fy
            ),
            'sbm' => $this->Page_model->district_submission_counts(
                'sbm',
                $this->session->division,
                $this->session->fy
            ),
            'sbm_ta' => $this->Page_model->district_submission_counts(
                'sbm_ta',
                $this->session->division,
                $this->session->fy
            )
        );

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function division_list()
    {

        $page = "division";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Division List";

        //$data['data'] = $this->Page_model->one_cond('schools','p_id',$this->session->p_id);
        $region_id = $this->session->position === 'region' ? (int) $this->session->region : 12;
        $divisions = $this->Page_model->one_cond('division', 'region_id', $region_id);

        // Calculate signup statistics for each division
        foreach ($divisions as $division) {
            // Count signed up schools for this division
            $this->db->where('division_id', $division->id);
            $signed_up_count = $this->db->count_all_results('schools');

            $total_schools = $division->total_schools ? (int) $division->total_schools : 0;

            // Calculate percentages
            if ($total_schools > 0) {
                $signup_percentage = round(($signed_up_count / $total_schools) * 100, 1);
                $not_signup_percentage = round((($total_schools - $signed_up_count) / $total_schools) * 100, 1);
            } else {
                $signup_percentage = 0;
                $not_signup_percentage = 0;
            }

            $division->signed_up_count = $signed_up_count;
            $division->signup_percentage = $signup_percentage;
            $division->not_signup_percentage = $not_signup_percentage;

            // Count Self-Assessment (sbm) submissions for this division
            $this->db->where('division', $division->id);
            $this->db->where('fy', $this->session->fy);
            $sbm_count = $this->db->count_all_results('sbm');

            // Count TNA (sbm_ta) submissions for this division
            $this->db->reset_query();
            $this->db->where('division', $division->id);
            $this->db->where('fy', $this->session->fy);
            $sbm_ta_count = $this->db->count_all_results('sbm_ta');

            error_log("Division ID: {$division->id}, SBM count: {$sbm_count}, SBM_TA count: {$sbm_ta_count}");

            // Calculate completion percentages
            if ($total_schools > 0) {
                $sbm_completion_percentage = round(($sbm_count / $total_schools) * 100, 1);
                $sbm_ta_completion_percentage = round(($sbm_ta_count / $total_schools) * 100, 1);
            } else {
                $sbm_completion_percentage = 0;
                $sbm_ta_completion_percentage = 0;
            }

            $division->sbm_count = $sbm_count;
            $division->sbm_completion_percentage = $sbm_completion_percentage;
            $division->sbm_ta_count = $sbm_ta_count;
            $division->sbm_ta_completion_percentage = $sbm_ta_completion_percentage;
        }

        $data['data'] = $divisions;

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function report_division_submission()
    {
        $page = "report_division_submission";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Division Submission Report";

        // Get divisions based on user role
        if ($this->session->position == 'region') {
            $divisions = $this->Page_model->one_cond('division', 'region_id', $this->session->region);
        } elseif ($this->session->position == 'admin') {
            $divisions = $this->Page_model->no_cond('division');
        } else {
            $divisions = array();
        }

        // Calculate statistics for each division
        foreach ($divisions as $division) {
            // Count signed up schools
            $this->db->where('division_id', $division->id);
            $signed_up_count = $this->db->count_all_results('schools');

            $total_schools = $division->total_schools ? (int) $division->total_schools : 0;

            // Calculate signup percentages
            if ($total_schools > 0) {
                $signup_percentage = round(($signed_up_count / $total_schools) * 100, 1);
                $not_signup_percentage = round((($total_schools - $signed_up_count) / $total_schools) * 100, 1);
            } else {
                $signup_percentage = 0;
                $not_signup_percentage = 0;
            }

            $division->signed_up_count = $signed_up_count;
            $division->signup_percentage = $signup_percentage;
            $division->not_signup_percentage = $not_signup_percentage;

            // Count submissions for each form type
            $this->db->reset_query();
            $this->db->where('division', $division->id);
            $this->db->where('fy', $this->session->fy);
            $action_plan_count = $this->db->count_all_results('sgod_action_plan');

            $this->db->reset_query();
            $this->db->where('division', $division->id);
            $this->db->where('fy', $this->session->fy);
            $sbm_count = $this->db->count_all_results('sbm');

            $this->db->reset_query();
            $this->db->where('division', $division->id);
            $this->db->where('fy', $this->session->fy);
            $sbm_ta_count = $this->db->count_all_results('sbm_ta');

            // Calculate completion percentages
            if ($total_schools > 0) {
                $action_plan_percentage = round(($action_plan_count / $total_schools) * 100, 1);
                $sbm_percentage = round(($sbm_count / $total_schools) * 100, 1);
                $sbm_ta_percentage = round(($sbm_ta_count / $total_schools) * 100, 1);
            } else {
                $action_plan_percentage = 0;
                $sbm_percentage = 0;
                $sbm_ta_percentage = 0;
            }

            $division->action_plan_count = $action_plan_count;
            $division->action_plan_percentage = $action_plan_percentage;
            $division->sbm_count = $sbm_count;
            $division->sbm_percentage = $sbm_percentage;
            $division->sbm_ta_count = $sbm_ta_count;
            $division->sbm_ta_percentage = $sbm_ta_percentage;
        }

        $data['data'] = $divisions;

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function report_overall_accomplishments()
    {
        $page = "report_overall_accomplishments";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Overall Accomplishments Comparison";

        // Get divisions based on user role
        if ($this->session->position == 'region') {
            $divisions = $this->Page_model->one_cond('division', 'region_id', $this->session->region);
        } elseif ($this->session->position == 'admin') {
            $divisions = $this->Page_model->no_cond('division');
        } else {
            $divisions = array();
        }

        // Calculate statistics for each division
        foreach ($divisions as $division) {
            // Count signed up schools
            $this->db->where('division_id', $division->id);
            $signed_up_count = $this->db->count_all_results('schools');

            $total_schools = $division->total_schools ? (int) $division->total_schools : 0;

            // Calculate signup percentages
            if ($total_schools > 0) {
                $signup_percentage = round(($signed_up_count / $total_schools) * 100, 1);
            } else {
                $signup_percentage = 0;
            }

            // Count submissions for each form type
            $this->db->reset_query();
            $this->db->where('division', $division->id);
            $this->db->where('fy', $this->session->fy);
            $action_plan_count = $this->db->count_all_results('sgod_action_plan');

            $this->db->reset_query();
            $this->db->where('division', $division->id);
            $this->db->where('fy', $this->session->fy);
            $sbm_count = $this->db->count_all_results('sbm');

            $this->db->reset_query();
            $this->db->where('division', $division->id);
            $this->db->where('fy', $this->session->fy);
            $sbm_ta_count = $this->db->count_all_results('sbm_ta');

            // Calculate completion percentages
            if ($total_schools > 0) {
                $action_plan_percentage = round(($action_plan_count / $total_schools) * 100, 1);
                $sbm_percentage = round(($sbm_count / $total_schools) * 100, 1);
                $sbm_ta_percentage = round(($sbm_ta_count / $total_schools) * 100, 1);
            } else {
                $action_plan_percentage = 0;
                $sbm_percentage = 0;
                $sbm_ta_percentage = 0;
            }

            $division->signup_percentage = $signup_percentage;
            $division->action_plan_percentage = $action_plan_percentage;
            $division->sbm_percentage = $sbm_percentage;
            $division->sbm_ta_percentage = $sbm_ta_percentage;
        }

        $data['data'] = $divisions;

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function report_sgc()
    {
        $page = "report_sgc";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "School Governance Council Report";

        // Get divisions based on user role
        if ($this->session->position == 'region') {
            $divisions = $this->Page_model->one_cond('division', 'region_id', $this->session->region);
        } elseif ($this->session->position == 'admin') {
            $divisions = $this->Page_model->no_cond('division');
        } else {
            $divisions = array();
        }

        // Calculate SGC statistics for each division
        foreach ($divisions as $division) {
            // Count schools by SGC status
            $this->db->where('division_id', $division->id);
            $this->db->where('sgc', 1);
            $not_yet_organized = $this->db->count_all_results('schools');

            $this->db->reset_query();
            $this->db->where('division_id', $division->id);
            $this->db->where('sgc', 2);
            $organized_not_functional = $this->db->count_all_results('schools');

            $this->db->reset_query();
            $this->db->where('division_id', $division->id);
            $this->db->where('sgc', 3);
            $functional = $this->db->count_all_results('schools');

            // Total schools with SGC data
            $total_sgc = $not_yet_organized + $organized_not_functional + $functional;

            // Total schools in division
            $total_schools = $division->total_schools ? (int) $division->total_schools : 0;
            $not_yet_responded = max(0, $total_schools - $total_sgc);

            $division->not_yet_organized = $not_yet_organized;
            $division->organized_not_functional = $organized_not_functional;
            $division->functional = $functional;
            $division->total_sgc = $total_sgc;
            $division->total_schools = $total_schools;
            $division->not_yet_responded = $not_yet_responded;
        }

        $data['data'] = $divisions;

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function sbm_rate_divisions_list()
    {

        $page = "sbm_rate_division_list";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Division List";

        //$data['data'] = $this->Page_model->one_cond('schools','p_id',$this->session->p_id);
        $data['data'] = $this->Page_model->one_cond('division', 'region_id', 12);

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    // public function signup(){

    //     $page = "school_signup";

    //     if(!file_exists(APPPATH.'views/pages/'.$page.'.php')){
    //         show_404();
    //     }

    //     $data['title'] = "Division List"; 

    //     //$data['data'] = $this->Page_model->one_cond('schools','p_id',$this->session->p_id);
    //     $data['division'] = $this->Page_model->one_cond('division','region_id',12);

    //     $this->load->view('pages/'.$page, $data);

    // }

    public function signup_email_available()
    {
        if ($this->input->method() !== 'post') {
            show_error('POST required.', 405);
            return;
        }
        $email = $this->input->post('email');
        $email = is_string($email) ? trim($email) : '';
        $valid = strlen($email) <= 254 && filter_var($email, FILTER_VALIDATE_EMAIL);
        $available = $valid && $this->db->group_start()->where('email', $email)
            ->or_where('username', $email)->group_end()->count_all_results('users') === 0;
        $this->output->set_header('Cache-Control: no-store, private')->set_content_type('application/json')
            ->set_output(json_encode(array('available' => (bool) $available,
                'message' => !$valid ? 'Enter a valid email address.' : ($available
                    ? 'Email address is available.' : 'This email address is already registered.'))));
    }

    private function render_school_signup($error = '', $show_validation = true, $account_saved = false)
    {
        $division_id = (int) $this->input->post('division_id');
        $data = array(
            'show_validation_errors' => $show_validation,
            'captcha_site_key' => $this->signup_captcha->site_key(),
            'captcha_error' => $error,
            'account_saved' => $account_saved,
            'signup_values' => $this->input->post(NULL, false),
            'division' => $this->Page_model->one_cond('division', 'region_id', 12),
            'districts' => $division_id > 0 ? $this->Page_model->get_districts_by_division($division_id) : array(),
        );
        $this->load->view('pages/school_signup', $data);
    }

    public function signup()
    {

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        ', '</div>');
        $this->form_validation->set_rules('schoolID', 'School ID', 'trim|required|max_length[45]|regex_match[/^[A-Za-z0-9._-]+$/]|is_unique[schools.schoolID]|is_unique[users.username]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[128]');
        $this->form_validation->set_rules('schoolName', 'School Name', 'trim|required|max_length[255]');
        $this->form_validation->set_rules('schoolEmail', 'School Email', 'trim|required|valid_email|max_length[254]|is_unique[users.email]|is_unique[users.username]');
        $this->form_validation->set_rules('division_id', 'Division', 'trim|required|is_natural_no_zero');
        $this->form_validation->set_rules('d_id', 'District/Cluster', 'trim|required|is_natural_no_zero');

        $this->form_validation->set_rules('termsAccepted', 'Declaration and Attestation', 'required|in_list[on]');

        $this->load->library('signup_captcha');
        $this->output->set_header('Cache-Control: no-store, private');
        $is_submission = $this->input->method() === 'post';
        $valid_form = $this->input->method() === 'post' && $this->form_validation->run();
        // Correcting form fields should not use up CAPTCHA attempts.
        $captcha_result = $is_submission && $valid_form
            ? $this->signup_captcha->verify($this->input->post('g-recaptcha-response'))
            : true;

        if (!$is_submission || !$valid_form || $captcha_result !== true) {

            $this->render_school_signup($captcha_result === true ? '' : $captcha_result, $is_submission);
            } else {

            $renren = $this->input->post('renren');
            $ivykate = $this->input->post('ivykate');
            $ivankyle = $this->input->post('ivankyle');
            $ic = $this->input->post('ic');

            $schoolID = $this->input->post('schoolID');
            $user_email = $this->input->post('schoolEmail');

            if (!empty($renren) || !empty($ivykate) || !empty($ivankyle) || !empty($ic)) {
                $this->render_school_signup('Registration could not be completed. Please try again.', true);
                return;
            }

            $division = $this->Page_model->one_cond_row('division', 'id', (int) $this->input->post('division_id'));
            if (!$division || (int) $division->region_id !== 12) {
                $this->render_school_signup('Please select a valid Region XI division.', true);
                return;
            }

            $district = $this->Page_model->one_cond_row('district', 'id', (int) $this->input->post('d_id', true));
            if (!$district || (int) $district->division_id !== (int) $this->input->post('division_id', true)) {
                $this->render_school_signup('The selected district does not belong to the selected division. Please select it again.', true);
                return;
            }

            $check = $this->Common->one_cond_count_row('schools', 'schoolID', $schoolID)->num_rows();
            $user_check = $this->Common->one_cond_count_row('users', 'email', $user_email)->num_rows();

            if ($check == 0 && $user_check == 0) {
                $registration = $this->Page_model->register_school_account();
                if (!$registration) {
                    $this->render_school_signup($this->Page_model->signup_failure_message(), true);
                    return;
                }
            } else {
                $this->render_school_signup('Duplicate entry found. The record already exists.', true);
                return;
            }

            $this->session->set_flashdata('success', 'School account created and activated. You can now sign in using your School ID or registered email address and password.');
            redirect(base_url('signup') . '#signup-feedback');
        }
    }


    public function signup_district()
    {

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        ', '</div>');
        $this->form_validation->set_rules('schoolID', 'Username', 'trim|required|max_length[45]|regex_match[/^[A-Za-z0-9._-]+$/]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[12]|max_length[128]');
        $this->form_validation->set_rules('schoolEmail', 'Email', 'trim|required|valid_email|max_length[254]');
        $this->form_validation->set_rules('division_id', 'Division', 'required|integer');
        $this->form_validation->set_rules('d_id', 'District', 'required|integer');

        if ($this->form_validation->run() == FALSE) {

            $page = "district_signup";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }
            $data['division'] = $this->Page_model->one_cond('division', 'region_id', 12);


            $this->load->view('pages/' . $page, $data);
            } else {

            $renren = $this->input->post('renren');
            $ivykate = $this->input->post('ivykate');
            $ivankyle = $this->input->post('ivankyle');
            $ic = $this->input->post('ic');

            $districtID = $this->input->post('d_id');
            $username = $this->input->post('schoolID');

            if (!empty($renren) || !empty($ivykate) || !empty($ivankyle) || !empty($ic)) {
                $this->session->set_flashdata('danger', 'I Got you');
                redirect(base_url() . 'private');
                return;
            }

            $division_id = (int) $this->input->post('division_id', true);
            $district = $this->Page_model->one_cond_row('district', 'id', (int) $districtID);
            if (!$district || (int) $district->division_id !== $division_id) {
                show_error('The selected district does not belong to the selected division.', 422);
            }
            if ($this->session->position === 'division' && $division_id !== (int) $this->session->division) {
                show_error('You can only create accounts for your division.', 403);
            }

            $user = $this->Common->one_cond_count_row('users', 'username', $username)->num_rows();

            if ($user > 0) { 
                $this->session->set_flashdata('failed', 'Duplicate entry found. The record already exists.');
                redirect(base_url('log_in'));
                return;
            }

            $check = $this->Common->two_cond_count_row('users', 'd_id', $districtID,'position','district')->num_rows();

            if ($check == 0) {
                $this->Page_model->insert_district_user(); 
            } else {
                $this->session->set_flashdata('failed', 'Duplicate entry found. The record already exists.');
                redirect(base_url() . 'log_in');
                return;
            }

            $district = $this->Common->one_cond_row('district', 'id',$this->input->post('d_id'));

            $email = $this->input->post('schoolEmail');
            $name = $district->description;
            $username = $this->input->post('schoolID');
            

            //Email Notification
            $this->load->config('email');
            $this->load->library('email');
            $mail_message = '
                    <html>
                    <head>
                    <style>
                        body {
                        font-family: "Segoe UI", Roboto, Arial, sans-serif;
                        background-color: #f0f4f8;
                        margin: 0;
                        padding: 20px;
                        }
                        .email-wrapper {
                        max-width: 600px;
                        margin: auto;
                        background-color: #ffffff;
                        border-radius: 10px;
                        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                        overflow: hidden;
                        }
                        .email-header {
                        background-color: #a00000;
                        color: white;
                        padding: 20px;
                        text-align: center;
                        }
                        .email-header h2 {
                        margin: 0;
                        font-size: 24px;
                        }
                        .email-body {
                        padding: 30px 25px;
                        color: #333333;
                        }
                        .email-body p {
                        font-size: 16px;
                        line-height: 1.6;
                        }
                        .credentials-box {
                        background-color: #e9f2ff;
                        padding: 15px;
                        border-left: 4px solid #a00000;
                        margin: 20px 0;
                        border-radius: 6px;
                        }
                        .credentials-box p {
                        margin: 0;
                        font-weight: bold;
                        color: #a00000;
                        }
                        .email-footer {
                        background-color: #f7f7f7;
                        padding: 15px;
                        text-align: center;
                        font-size: 14px;
                        color: #666666;
                        }
                        .cb{
                            margin-top:20px;
                            background-color:#a00000;
                            color:#ffffff !important;
                            padding:7px 15px;
                            text-decoration:none;
                            border-radius:6px;
                            font-size:16px;
                            font-weight:bold;
                            display:inline-block;
                        }
                    </style>
                    </head>
                    <body>
                    <div class="email-wrapper">
                        <div class="email-header">
                        <h2>Welcome to FTAD OneView</h2>
                        </div>
                        <div class="email-body">
                        <p>Dear ' . htmlspecialchars($name) . ',</p>
                        <p>Your profile has been successfully encoded into the <strong>FTAD OneView</strong> system. Please find your login credentials below:</p>

                        <div class="credentials-box">
                            <p>Username: ' . htmlspecialchars($username) . '</p>
                            <p>Your district account has been created by an authorized administrator.</p>
                        </div>

                        <p>Kindly keep this information secure and do not share it with anyone.</p>
                        <p>Should you have any issues accessing your account, please contact your system administrator.</p>

                        <p style="margin-top: 30px;">Thanks & Regards,<br><strong>FTAD OneView Team</strong></p>
                        </div>
                        <div class="email-footer">
                        © ' . date('Y') . ' Department of Education
                        </div>
                    </div>
                    </body>
                    </html>';

            $this->email->from('no-reply@lxeinfotechsolutions.com', 'FTAD OneView Team')
                ->to($email)
                ->subject('Account Created')
                ->message($mail_message);
            $this->email->send();

            //$this->session->set_flashdata('success', 'School account has been registered successfully. Your username and password have been sent to your email.');
            $this->session->set_flashdata('success', 'The district account has been successfully registered. You may now sign in using your credentials.');
            redirect(base_url() . 'log_in');
        }
    }


    function school()
    {

        $page = "school_info";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Action Plan";

        $data['data'] = $this->Common->one_cond_row('schools', 'schoolID', $this->uri->segment(2));

        if (!$data['data']) {
            show_404();
        }

        $this->require_school_scope($data['data']);

        $data['division'] = $this->Page_model->one_cond_row('division', 'id', $data['data']->division_id);
        $data['district'] = $this->Page_model->one_cond_row('district', 'id', $data['data']->district_id);

        $this->load->view('templates/header');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_basic');
    }

    


    function homepage()
    {

        $page = "home";

        if (!file_exists(APPPATH . 'views/' . $page . '.php')) {
            show_404();
        }
        $data = $this->homepage_data();
        $this->load->view($page, $data);
    }

    private function homepage_data()
    {
        $region_id = 12;

        return array(
            'title' => 'CLMD Learning Management Portal | DepEd Region XI',
            'region' => $this->Page_model->one_cond_row('region', 'id', $region_id),
            'divisions' => $this->Page_model->homepage_divisions($region_id),
        );
    }

    function authors()
    {

        $page = "authors";
        $this->load->view('templates/css_author');
        if (!file_exists(APPPATH . 'views/' . $page . '.php')) {
            show_404();
        }
        $data['title'] = "Authors";
        $this->load->view('templates/nav');
        $this->load->view($page, $data);
    }

    function about()
    {

        $page = "about";

        if (!file_exists(APPPATH . 'views/' . $page . '.php')) {
            show_404();
        }
        $data['title'] = "About";
        $this->load->view('templates/css_about');
        $this->load->view('templates/nav');
        $this->load->view($page, $data);
    }

    public function tana_division_delete()
    {
        $this->require_post();
        $id = (int) $this->input->post('id', true);
        $record = $this->db->where('id', $id)
            ->where('division', (int) $this->session->division)
            ->where('region', (int) $this->session->region)
            ->where('fy', $this->session->fy)
            ->get('division_tana')->row();
        if (!$record) {
            show_404();
        }
        $this->Page_model->delete('division_tana', 'id', $id);
        $this->session->set_flashdata('danger', 'Successfully deleted.');
        redirect(base_url() . 'pages/tana_summary_division');
    }

    public function tana_region_delete()
    {
        $this->require_post();
        $id = (int) $this->input->post('id', true);
        $record = $this->db->where('id', $id)
            ->where('region', (int) $this->session->region)
            ->where('fy', $this->session->fy)
            ->get('region_tana')->row();
        if (!$record) {
            show_404();
        }
        $this->Page_model->delete('region_tana', 'id', $id);
        $this->session->set_flashdata('danger', 'Successfully deleted.');
        redirect(base_url() . 'pages/tana_summary_region');
    }

    public function forgot_password()
    {

        // Delimiters match the alert styling shared by the homepage portal and the reset page.
        $this->form_validation->set_error_delimiters('<div class="alert alert-danger" role="alert">', '</div>');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

        if ($this->form_validation->run() == FALSE) {

            // The homepage posts this form from its portal modal, so validation has to answer
            // in JSON too - otherwise the modal receives a full HTML page it cannot read.
            if ($this->input->is_ajax_request()) {
                $message = trim(strip_tags(validation_errors('', ' ')));
                echo json_encode(['success' => false, 'message' => $message !== '' ? $message : 'Please enter a valid email address.']);
                return;
            }

            $page = "fp";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $this->load->view('pages/' . $page);
        } else {
            // Public password resets previously changed the password immediately,
            // enabling account enumeration and reset-denial attacks. Until a
            // proper single-use reset-token workflow is deployed, keep the
            // response generic and route resets through authorized managers.
            $email_hash = hash('sha256', mb_strtolower(trim((string) $this->input->post('email', true)), 'UTF-8'));
            log_message('error', 'Password assistance requested for email hash ' . $email_hash
                . ' from ' . $this->input->ip_address());
            $message = 'If the address is registered, password assistance instructions will be provided by your authorized system administrator.';
            if ($this->input->is_ajax_request()) {
                $this->output->set_content_type('application/json')
                    ->set_output(json_encode(array('success' => true, 'message' => $message)));
                return;
            }
            $this->session->set_flashdata('success', $message);
            redirect(base_url() . 'log_in');
        }
    }

    public function school_update()
    {
        $is_post = strtoupper((string) $this->input->method()) === 'POST';
        $school_id = $is_post
            ? (string) $this->input->post('recID', true)
            : (string) $this->uri->segment(3);
        $school_record = $this->Common->one_cond_row('schools', $is_post ? 'recID' : 'schoolID', $school_id);
        if (!$school_record && !$is_post) {
            $school_record = $this->Common->one_cond_row('schools', 'recID', $school_id);
        }
        $this->require_school_scope($school_record);

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');
        $this->form_validation->set_rules('schoolName', 'School Name', 'required');

        if ($this->form_validation->run() == FALSE) {

            $page = "school_update";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Update School Information";
            $data['data'] = $school_record;

            $data['division'] = $this->Page_model->one_cond('division', 'region_id', 12);
            $data['districts'] = $this->Page_model->get_districts_by_division($data['data']->division_id);

            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            $new_school_id = trim((string) $this->input->post('schoolID', true));
            if (!preg_match('/^[A-Za-z0-9._-]{1,45}$/', $new_school_id)) {
                show_error('The school ID contains invalid characters.', 422);
            }
            if ((string) $school_record->schoolID !== $new_school_id) {
                $duplicate = $this->Page_model->one_cond_row('schools', 'schoolID', $new_school_id);
                if ($duplicate) {
                    show_error('That school ID is already in use.', 422);
                }
            }

            $division_id = (int) $this->input->post('division_id', true);
            $district_id = (int) $this->input->post('d_id', true);
            $position = strtolower((string) $this->session->position);
            if ($position === 'school') {
                $division_id = (int) $school_record->division_id;
                $district_id = (int) $school_record->district_id;
            } elseif (in_array($position, array('division', 'division_head', 'ict'), true)) {
                $division_id = (int) $this->session->division;
            } elseif ($position === 'district') {
                $division_id = (int) $this->session->division;
                $district_id = (int) $this->session->district;
            }
            $district = $this->Page_model->one_cond_row('district', 'id', $district_id);
            $division = $this->Page_model->one_cond_row('division', 'id', $division_id);
            if (!$district || !$division || (int) $district->division_id !== $division_id) {
                show_error('The selected district and division do not match.', 422);
            }
            if ($position === 'region' && (int) $division->region_id !== (int) $this->session->region) {
                show_error('You can only move schools within your region.', 403);
            }

            // Models consume the legacy POST fields; replace security-sensitive
            // identifiers only after deriving them from the authorized scope.
            $_POST['old_schoolID'] = (string) $school_record->schoolID;
            $_POST['division_id'] = $division_id;
            $_POST['d_id'] = $district_id;
            $this->Page_model->school_updates();
            $this->Page_model->update_district_id();
            $this->Page_model->user_updates();
            $this->Page_model->dd_updates();
            $this->session->set_flashdata('success', 'Successfully saved.');

            $school_id = trim((string) $this->input->post('schoolID', true));
            $division_id = trim((string) $this->input->post('division_id', true));
            $district_id = trim((string) $this->input->post('d_id', true));
            $redirect_url = base_url();

            if ($this->session->position === 'division') {
                $redirect_url = base_url() . 'pages/schools_division/' . rawurlencode($division_id !== '' ? $division_id : (string) $this->session->division);
            } elseif ($this->session->position === 'ict') {
                $redirect_url = base_url() . 'pages/schools/' . rawurlencode($division_id !== '' ? $division_id : (string) $this->session->division);
            } elseif ($this->session->position === 'district') {
                $redirect_url = base_url() . 'pages/schools_district/' . rawurlencode($district_id !== '' ? $district_id : (string) $this->session->district);
            } elseif (in_array($this->session->position, array('admin', 'region'), true)) {
                $redirect_url = base_url() . 'pages/school_by_district';
            } elseif ($school_id !== '') {
                $redirect_url = base_url() . 'school/' . rawurlencode($school_id);
            }

            redirect($redirect_url);
        }
    }

    public function district_account()
    {

        $page = "district_list_division";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = "Profile List";

        $data['district'] = $this->Page_model->one_cond('district','division_id',$this->session->division);
        $overview = $this->Page_model->division_account_overview($this->session->division);
        $data['schools_by_district'] = $overview['schools_by_district'];
        $data['school_usernames'] = $overview['school_usernames'];
        $data['school_account_ids'] = $overview['school_account_ids'];
        $data['district_user_counts'] = $overview['district_user_counts'];
        $data['school_count'] = $overview['school_count'];
        $data['division'] = $this->Page_model->get_division_setup($this->session->division);
        $data['encoded_total_schools'] = !empty($data['division']->total_schools)
            ? (int) $data['division']->total_schools
            : 0;
        $data['signup_percentage'] = $data['encoded_total_schools'] > 0
            ? ($data['school_count'] / $data['encoded_total_schools']) * 100
            : 0;

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function district_new()
    {
        if (!$this->session->logged_in || !in_array($this->session->position, array('division', 'admin'), true)) {
            show_error('Only division and admin users can access district settings.', 403);
        }

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');
        $this->form_validation->set_rules('description', 'District Name', 'required');

        if ($this->form_validation->run() == FALSE) {
            $page = "district_add";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Add New District";
            
            if ($this->session->position === 'admin') {
                $data['divisions'] = $this->Page_model->no_cond('division');
            } else {
                $data['division'] = $this->Page_model->one_cond_row('division', 'id', $this->session->division);
            }

            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            if ($this->session->position === 'division') {
                $_POST['division_id'] = (int) $this->session->division;
            }
            $this->Page_model->district_insert();
            $this->session->set_flashdata('success', 'Successfully saved.');
            
            $division_id = $this->input->post('division_id');
            redirect(base_url() . 'pages/district_account/' . $division_id);
        }
    }

    public function district_update()
    {
        if (!$this->session->logged_in || !in_array($this->session->position, array('division', 'admin'), true)) {
            show_error('Only division and admin users can access district settings.', 403);
        }

        $district_id = $this->uri->segment(3);

        if (empty($district_id)) {
            $district_id = $this->input->post('id', true);
        }
        $district_record = $this->Page_model->one_cond_row('district', 'id', $district_id);
        if (!$district_record) {
            show_404();
        }
        if ($this->session->position === 'division'
            && (int) $district_record->division_id !== (int) $this->session->division) {
            show_error('You can only update districts under your division.', 403);
        }

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');
        $this->form_validation->set_rules('description', 'District Name', 'required');

        if ($this->form_validation->run() == FALSE) {
            $page = "district_update";

            if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
                show_404();
            }

            $data['title'] = "Update District";
            $data['district'] = $district_record;

            $data['division'] = $this->Page_model->one_cond_row('division', 'id', $data['district']->division_id);

            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
        } else {
            $this->require_post();
            if ($this->session->position === 'division') {
                $_POST['division_id'] = (int) $this->session->division;
            }
            $this->Page_model->district_update();
            $this->session->set_flashdata('success', 'Successfully updated.');
            
            $division_id = $this->input->post('division_id');
            redirect(base_url() . 'pages/district_account/' . $division_id);
        }
    }

    public function district_delete()
    {
        if (!$this->session->logged_in || !in_array($this->session->position, array('division', 'admin'), true)) {
            show_error('Only division and admin users can delete districts.', 403);
        }

        $this->require_post();
        $district_id = (int) $this->input->post('id', true);

        if (empty($district_id)) {
            show_404();
        }

        $district = $this->Page_model->one_cond_row('district', 'id', $district_id);

        if (!$district) {
            show_404();
        }

        // Division users can only delete districts in their own division
        if ($this->session->position === 'division' && (int) $district->division_id !== (int) $this->session->division) {
            show_error('You can only delete districts within your own division.', 403);
        }

        $this->Page_model->district_delete($district_id);
        $this->session->set_flashdata('success', 'Successfully deleted.');
        redirect(base_url() . 'pages/district_account/' . $district->division_id);
    }

    public function division_setup()
    {
        $this->require_division_settings_access();

        $this->form_validation->set_error_delimiters('<div class="alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>', '</div>');
        $this->form_validation->set_rules('description', 'Division Name', 'trim|required');
        $this->form_validation->set_rules('total_schools', 'Total Number of Schools', 'trim|required|regex_match[/^[0-9]+$/]');

        $page = "division_setup";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $division_id = (int) $this->session->division;
        $data['division'] = $this->Page_model->get_division_setup($division_id);

        if (!$data['division']) {
            show_404();
        }

        $data['title'] = "Division Setup";
        $data['region'] = $this->Page_model->one_cond_row('region', 'id', $data['division']->region_id);
        $data['district_count'] = $this->Page_model->division_district_count($division_id);
        $data['actual_school_count'] = $this->Page_model->division_school_count($division_id);

        if ($this->form_validation->run() == FALSE) {
            $this->load->view('templates/header');
            $this->load->view('templates/menu');
            $this->load->view('pages/' . $page, $data);
            $this->load->view('templates/footer');
            $this->load->view('templates/footer_basic');
            return;
        }

        $new_logo = null;
        $old_logo = !empty($data['division']->homepage_logo)
            ? (string) $data['division']->homepage_logo
            : '';

        if (!empty($_FILES['homepage_logo']['name'])) {
            $upload_directory = FCPATH . 'uploads/division_logos/';

            if (!is_dir($upload_directory) && !mkdir($upload_directory, 0755, true)) {
                $data['upload_error'] = 'The division logo directory could not be created.';
                $this->load->view('templates/header');
                $this->load->view('templates/menu');
                $this->load->view('pages/' . $page, $data);
                $this->load->view('templates/footer');
                $this->load->view('templates/footer_basic');
                return;
            }

            $upload_config = array(
                'upload_path' => $upload_directory,
                'allowed_types' => 'jpg|jpeg|png',
                'max_size' => 2048,
                'max_width' => 2000,
                'max_height' => 2000,
                'encrypt_name' => true,
                'file_ext_tolower' => true,
            );
            $this->load->library('upload');
            $this->upload->initialize($upload_config);

            if (!$this->upload->do_upload('homepage_logo')) {
                $data['upload_error'] = strip_tags($this->upload->display_errors('', ''));
                $this->load->view('templates/header');
                $this->load->view('templates/menu');
                $this->load->view('pages/' . $page, $data);
                $this->load->view('templates/footer');
                $this->load->view('templates/footer_basic');
                return;
            }

            $uploaded_file = $this->upload->data();
            $new_logo = 'uploads/division_logos/' . $uploaded_file['file_name'];
        }

        $updated = $this->Page_model->update_division_setup($division_id, $new_logo);

        if (!$updated) {
            if ($new_logo !== null && is_file(FCPATH . $new_logo)) {
                unlink(FCPATH . $new_logo);
            }
            $this->session->set_flashdata('danger', 'The division setup could not be updated. Please try again.');
            redirect(base_url() . 'pages/division_setup');
            return;
        }

        if ($new_logo !== null && strpos($old_logo, 'uploads/division_logos/') === 0) {
            $old_logo_file = FCPATH . 'uploads/division_logos/' . basename($old_logo);
            if (is_file($old_logo_file)) {
                unlink($old_logo_file);
            }
        }

        $this->session->set_flashdata('success', 'Division setup updated successfully.');
        redirect(base_url() . 'pages/division_setup');
    }

    public function division_sgc_details()
    {
        $this->require_division_dashboard_access();

        $status = (int) $this->uri->segment(3);
        $status_labels = array(
            1 => 'Not Yet Organized',
            2 => 'Organized, Not Functional',
            3 => 'Functional'
        );

        if (!isset($status_labels[$status])) {
            show_404();
        }

        $page = "division_count_details";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data['title'] = 'SGC Details';
        $data['hero_title'] = $status_labels[$status] . ' Schools';
        $data['hero_description'] = 'View schools in your division filtered by School Governance Council status.';
        $data['detail_label'] = 'SGC Status';
        $data['detail_badge'] = $status_labels[$status];
        $data['detail_type'] = 'sgc';
        $data['back_url'] = base_url();
        $data['records'] = $this->Page_model->division_schools_by_sgc_status($this->session->division, $status);

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function division_sgc_category_printable()
    {
        $this->require_division_dashboard_access();

        $status = (int) $this->uri->segment(3);
        $category_id = (int) $this->uri->segment(4);

        $status_labels = array(
            1 => 'Not Yet Organized',
            2 => 'Organized, Not Functional',
            3 => 'Functional'
        );

        $category_labels = array(
            1 => 'Elementary',
            2 => 'Integrated (Elem & JHS)',
            3 => 'Integrated (Elem, JHS, & SHS)',
            4 => 'Secondary (JHS only)',
            5 => 'Secondary (JHS & SHS)',
            6 => 'SHS - Stand Alone'
        );

        if (!isset($status_labels[$status]) || !isset($category_labels[$category_id])) {
            show_404();
        }

        $all_records = $this->Page_model->division_schools_by_sgc_status($this->session->division, $status);

        // Filter by category
        $filtered_records = array();
        foreach ($all_records as $record) {
            if ((int) $record->category === $category_id) {
                $filtered_records[] = $record;
            }
        }

        $data['title'] = 'SGC Category Schools';
        $data['status_label'] = $status_labels[$status];
        $data['category_label'] = $category_labels[$category_id];
        $data['records'] = $filtered_records;
        $data['division'] = $this->Page_model->one_cond_row('division', 'id', $this->session->division);

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/division_sgc_category_printable', $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function division_checklist_completed_details()
    {
        $this->require_division_dashboard_access();

        $page = "checklist_completion_report";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data = $this->get_division_checklist_completed_report_data(false);

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function division_checklist_completed_printable()
    {
        $this->require_division_dashboard_access();

        $page = "checklist_completion_report";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $data = $this->get_division_checklist_completed_report_data(true);

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    public function division_checklist_completed_shared($division_id = null, $fy = null)
    {
        $page = "checklist_completion_report";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $division_id = trim((string) $division_id);
        $fy = trim((string) $fy);

        if ($division_id === '' || $fy === '' || !ctype_digit($division_id) || !ctype_digit($fy)) {
            show_404();
        }

        $this->validate_division_checklist_share_request($division_id, $fy);

        $data = $this->build_division_checklist_completed_report_data(
            $division_id,
            $fy,
            array(
                'share_mode' => true,
                'back_url' => '',
                'back_label' => '',
                'printable_url' => '',
                'shareable_url' => '',
            )
        );

        $this->load->view('templates/header_public', $data);
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer_public');
        $this->load->view('templates/footer_basic');
    }

    public function region_checklist_completed_report()
    {
        $this->require_region_dashboard_access();

        $page = "checklist_completion_report";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $records = $this->Page_model->region_completed_checklist_report_rows(
            $this->session->region,
            $this->session->fy
        );
        $filter_options = $this->build_checklist_filter_options(
            $records,
            'division_id',
            'division_name',
            'Division'
        );
        $selected_filter = trim((string) $this->input->get('division_id', true));
        $valid_filter_ids = array_map(function ($option) {
            return (string) $option['id'];
        }, $filter_options);

        if ($selected_filter !== '' && !in_array($selected_filter, $valid_filter_ids, true)) {
            $selected_filter = '';
        }

        $filtered_records = $this->filter_checklist_records($records, 'division_id', $selected_filter);

        $data['title'] = 'Regional Checklist Completion Report';
        $data['hero_title'] = 'Completed Self-Assessment Checklist Report';
        $data['hero_description'] = 'Review finalized Self-Assessment Checklist submissions across the region, grouped by district and arranged alphabetically by school.';
        $data['report_scope'] = 'region';
        $data['report_badge'] = 'Fiscal Year ' . $this->session->fy;
        $data['back_url'] = base_url();
        $data['records'] = $filtered_records;
        $data['district_groups'] = $this->build_checklist_report_groups($filtered_records);
        $data['filter_title'] = 'Filter By Division';
        $data['filter_description'] = 'Select one division to narrow the completed checklist report for this region.';
        $data['filter_action_url'] = base_url('Pages/region_checklist_completed_report');
        $data['filter_param'] = 'division_id';
        $data['filter_placeholder'] = 'All Divisions';
        $data['filter_icon'] = 'mdi-domain';
        $data['filter_options'] = $filter_options;
        $data['selected_filter'] = $selected_filter;

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    function sbm_checklist_unlock()
		{
        $position = strtolower(trim((string) $this->session->position));
        $is_division_user = $position === 'division';

        if (!$is_division_user) {
            show_error('Only division users can unlock checklists.', 403);
        }

        $this->ensure_unlock_request_table();

	        $this->require_post();
	        $checklist_id = (int) $this->input->post('id', true);
	        list($checklist_record, $school) = $this->school_for_record('sbm', $checklist_id);
        if ($checklist_id) {
            // Mark any pending unlock requests for this checklist as approved
            $this->db->where('checklist_id', $checklist_id)
                ->where('status', 'pending')
                ->update('unlock_request', array(
                    'status' => 'approved',
                    'processed_date' => date('Y-m-d H:i:s'),
                    'processed_by' => $this->session->username
                ));
        }

		$this->Page_model->sbm_checklist_lock_unloc_by_id($checklist_id, 0);
		$this->session->set_flashdata('success', 'Checklist unlocked successfully. The school can now edit it again.');
        redirect(base_url() . 'Pages/unlock_requests');
	}

    public function district_userlist_by_division()
    {
        $this->require_user_manager();
        if ($this->session->position === 'district') {
            $this->userlist();
            return;
        }

        $page = "user_list";

        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }

        $id = $this->uri->segment(3);
        $this->validate_managed_district($id);
        $district = $this->Page_model->one_cond_row('district', 'id', $id);

        if (!$district) {
            show_404();
        }

        $data['title'] = "District User Accounts";
        $data['division_scope'] = true;
        $data['district_user_scope'] = true;
        $data['district'] = $district;
        $data['district_user_back_url'] = base_url() . 'pages/district_account/' . rawurlencode($district->division_id);
        $data['users'] = $this->Page_model->two_cond('users','position','district','d_id',$id);

        $this->load->view('templates/header_dt');
        $this->load->view('templates/menu');
        $this->load->view('pages/' . $page, $data);
        $this->load->view('templates/footer');
        $this->load->view('templates/footer_dt');
    }

    function update_tana_summary()
	{
		$this->require_post();
		$this->Page_model->tana_summary_del();
		$this->session->set_flashdata('success', 'Saved successfully.');
		redirect(base_url() . 'pages/tana_summary');
	}

    function final_tana_summary()
	{
		$this->require_post();
		$this->session->set_flashdata('success', 'TANA summary updates are always editable for school accounts.');
		redirect(base_url());
	}

    public function password_change_required()
    {
        if (!$this->session->logged_in) {
            redirect(base_url('homepage'));
            return;
        }

        if ((int) $this->session->must_change_password !== 1) {
            redirect(base_url());
            return;
        }

        $this->load->view('pages/change_password_required');
    }

    public function change_password_user()
    {
        if (!$this->session->logged_in) {
            redirect(base_url('homepage'));
            return;
        }

        $this->require_post();
        $this->form_validation->set_rules('current_password', 'Current Password', 'required');
        $this->form_validation->set_rules('password', 'New Password', 'required|min_length[8]|max_length[128]');
        $this->form_validation->set_rules('password_confirm', 'Confirm New Password', 'required|matches[password]');
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('danger', 'Provide your current password and matching new passwords of at least 8 characters.');
        } else {
            $result = $this->Page_model->user_password_change();
            if ($result === 'incorrect_current') {
                $this->session->set_flashdata('danger', 'Your current password is incorrect.');
            } elseif ($result === 'same_password') {
                $this->session->set_flashdata('danger', 'Your new password must be different from your temporary password.');
            } elseif ($result !== 'updated') {
                $this->session->set_flashdata('danger', 'The password could not be changed. Please try again.');
            } else {
                $this->session->set_userdata('must_change_password', 0);
                $this->session->set_flashdata('success', 'Password successfully changed.');
            }
        }
        redirect((int) $this->session->must_change_password === 1
            ? base_url('password-change-required')
            : base_url());
    }

    function change_password_user_division()
		{
		// Retired: this legacy endpoint allowed a caller to select an arbitrary
		// username. Managers must use user_reset_password(), which enforces scope.
		show_error('This legacy password endpoint has been disabled.', 410);
		}

    function add_school_user()
		{
		$this->require_post();
	        $school_id = trim((string) $this->input->post('school_id', true));
	        $school = $this->Page_model->one_cond_row('schools', 'schoolID', $school_id);
	        $this->require_school_scope($school);
	        if ($this->Page_model->one_cond_row('users', 'username', $school_id)) {
	            $this->session->set_flashdata('danger', 'This school already has an account.');
	            redirect(base_url() . 'pages/district_account/' . (int) $school->division_id);
	            return;
	        }

	        $temporary_password = $this->Page_model->random_password();
		$this->Page_model->add_school_user(
		    $school_id,
		    (string) $school->schoolName,
		    (int) $school->district_id,
		    (int) $school->division_id,
		    $temporary_password
		);
		$this->session->set_flashdata('success', 'School account created. Temporary password: <strong><code>'
		    . html_escape($temporary_password) . '</code></strong>');
		redirect(base_url() . 'pages/district_account/' . (int) $school->division_id);
		}
    





















}
