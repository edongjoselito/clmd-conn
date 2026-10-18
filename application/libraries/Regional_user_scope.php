<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Regional_user_scope
{
    public function apply($db, $region_id)
    {
        $region_id = (int) $region_id;
        // Division membership is authoritative for division/district/school accounts.
        // Region-only accounts must have an explicit regional assignment.
        $db->where('position !=', 'admin');
        if ($region_id <= 0) { $db->where('1 = 0', null, false); return; }
        $db->where('(p_id IN (SELECT id FROM division WHERE region_id = ' . $region_id . ')'
            . ' OR ((p_id IS NULL OR p_id = 0) AND r_id = ' . $region_id . '))', null, false);
    }
}
