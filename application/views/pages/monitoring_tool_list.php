<?php
$this->load->view('pages/monitoring_tools', array(
    'tools' => $tools,
    'edit' => $edit,
    'list_path' => 'monitoring/tool'
));
