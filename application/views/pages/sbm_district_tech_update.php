<?php
$entry = isset($data) ? $data : null;
$form_action = base_url() . 'Pages/sbm_district_tech_edit';
$submit_label = 'Update Entry';
$submit_icon = 'mdi-pencil-outline';
$back_url = base_url() . 'Pages/sbm_district_tech';
$hero_title = 'Update District Technical Assistance Entry';
$hero_description = 'Refine the recommendation, activity plan, coordination scope, and team assignments without losing the existing record.';

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
