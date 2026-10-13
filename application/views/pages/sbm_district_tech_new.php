<?php
$entry = null;
$form_action = base_url() . 'Pages/sbm_district_tech_new';
$submit_label = 'Save Entry';
$submit_icon = 'mdi-content-save-outline';
$back_url = base_url() . 'Pages/sbm_district_tech';
$hero_title = 'Create District Technical Assistance Entry';
$hero_description = 'Capture a new recommendation, supporting activities, implementation schedule, and team assignments in one cleaner workspace.';

$this->load->view('pages/_sbm_district_tech_form', compact(
    'entry',
    'form_action',
    'submit_label',
    'submit_icon',
    'back_url',
    'hero_title',
    'hero_description'
));
?>
