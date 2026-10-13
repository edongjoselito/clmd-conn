<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Monitoring_model extends CI_Model
{
    public function assessment_schema()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS monitoring_assessments (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            region_id INT UNSIGNED NOT NULL,
            school_id VARCHAR(255) NOT NULL,
            sy VARCHAR(9) NOT NULL,
            payload LONGTEXT NOT NULL,
            version INT UNSIGNED NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            KEY region_records (region_id, id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function assessment($region, $id)
    {
        return $this->db->where(array('region_id' => $region, 'id' => $id))->get('monitoring_assessments')->row();
    }

    public function assessments($region)
    {
        return $this->db->where('region_id', $region)->order_by('id', 'DESC')->get('monitoring_assessments')->result();
    }

    public function save_assessment($region, $id, $version, $payload)
    {
        $data = array('payload' => json_encode($payload), 'updated_at' => date('Y-m-d H:i:s'));
        if ($id) {
            $data['version'] = $version + 1;
            $ok = $this->db->where(array('region_id' => $region, 'id' => $id, 'version' => $version))
                ->update('monitoring_assessments', $data);
            return $ok && $this->db->affected_rows() === 1 ? $id : false;
        }
        $data += array('region_id' => $region, 'school_id' => $payload['school_id'], 'sy' => $payload['sy']);
        return $this->db->insert('monitoring_assessments', $data) ? $this->db->insert_id() : false;
    }

    public function delete_assessment($region, $id, $version)
    {
        return $this->db->where(array('region_id' => $region, 'id' => $id, 'version' => $version))
            ->delete('monitoring_assessments') && $this->db->affected_rows() === 1;
    }

    public function defaults()
    {
        return json_decode(file_get_contents(APPPATH . 'data/monitoring_indicators.json'), true);
    }

    public function initialize($region_id)
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS monitoring_indicators (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            region_id INT UNSIGNED NOT NULL,
            area_index TINYINT UNSIGNED NOT NULL,
            subgroup VARCHAR(255) NOT NULL DEFAULT '',
            indicator TEXT NOT NULL,
            sort_order INT UNSIGNED NOT NULL DEFAULT 1,
            active TINYINT UNSIGNED NOT NULL DEFAULT 1,
            seed_key VARCHAR(32) DEFAULT NULL,
            PRIMARY KEY (id), UNIQUE KEY region_seed (region_id, seed_key),
            KEY region_area (region_id, area_index, sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $this->db->query("CREATE TABLE IF NOT EXISTS monitoring_tools (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            region_id INT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            seed_key VARCHAR(32) DEFAULT NULL,
            initialized TINYINT UNSIGNED NOT NULL DEFAULT 0,
            deleted_at DATETIME DEFAULT NULL,
            UNIQUE KEY region_seed (region_id, seed_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (!$this->db->field_exists('tool_id', 'monitoring_indicators')) {
            $this->db->query('ALTER TABLE monitoring_indicators ADD tool_id INT UNSIGNED DEFAULT NULL, ADD KEY tool_area (tool_id, area_index, sort_order)');
            unset($this->db->data_cache['field_names']['monitoring_indicators']);
        }
        $this->db->query("INSERT INTO monitoring_tools (region_id, name, seed_key)
            VALUES (?, 'Unified Learning Area Monitoring Tool', 'legacy') ON DUPLICATE KEY UPDATE id = id", array($region_id));
        $this->db->trans_start();
        $tool = $this->db->query("SELECT * FROM monitoring_tools WHERE region_id = ? AND seed_key = 'legacy' FOR UPDATE", array($region_id))->row();
        if (!(int) $tool->initialized) {
            $this->db->where('region_id', $region_id)->where('tool_id', null)->update('monitoring_indicators', array('tool_id' => $tool->id));
            foreach ($this->defaults() as $area => $group) {
                foreach ($group['indicators'] as $order => $indicator) {
                    $this->db->query('INSERT INTO monitoring_indicators
                        (region_id, tool_id, area_index, subgroup, indicator, sort_order, seed_key)
                        VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id = id',
                        array($region_id, $tool->id, $area, $indicator['subgroup'], $indicator['text'], $order + 1, $area . ':' . $order));
                }
            }
            $this->db->where('id', $tool->id)->update('monitoring_tools', array('initialized' => 1));
        }
        $this->db->trans_complete();
    }

    public function tools($region)
    {
        return $this->db->where('region_id', $region)->where('deleted_at', null)->order_by('id')->get('monitoring_tools')->result();
    }

    public function tool($region, $id)
    {
        return $this->db->where(array('region_id' => $region, 'id' => $id, 'deleted_at' => null))->get('monitoring_tools')->row();
    }

    public function save_tool($region, $id, $name)
    {
        if ($id) {
            if (!$this->tool($region, $id)) { return false; }
            return $this->db->where(array('region_id' => $region, 'id' => $id, 'deleted_at' => null))
                ->update('monitoring_tools', array('name' => $name));
        }
        return $this->db->insert('monitoring_tools', array('region_id' => $region, 'name' => $name, 'initialized' => 1));
    }

    public function delete_tool($region, $id)
    {
        // Retain the source association and saved assessment snapshots after removal.
        return $this->db->where(array('region_id' => $region, 'id' => $id, 'deleted_at' => null))
            ->update('monitoring_tools', array('deleted_at' => date('Y-m-d H:i:s'))) && $this->db->affected_rows() === 1;
    }

    public function indicators($region_id, $tool_id)
    {
        return $this->db->where('region_id', $region_id)->where('tool_id', $tool_id)->order_by('area_index')->order_by('sort_order')->order_by('id')->get('monitoring_indicators')->result();
    }

    public function save($region_id, $tool_id, $id, $data)
    {
        if (!$this->tool($region_id, $tool_id)) { return false; }
        if ($id) {
            if (!$this->db->where(array('id' => $id, 'region_id' => $region_id, 'tool_id' => $tool_id))->count_all_results('monitoring_indicators')) {
                return false;
            }
            return $this->db->where(array('id' => $id, 'region_id' => $region_id, 'tool_id' => $tool_id))->update('monitoring_indicators', $data);
        }
        $data['region_id'] = $region_id;
        $data['tool_id'] = $tool_id;
        return $this->db->insert('monitoring_indicators', $data);
    }

    public function delete_indicator($region, $tool_id, $id)
    {
        if (!$this->tool($region, $tool_id)) { return false; }
        return $this->db->where(array('region_id' => $region, 'tool_id' => $tool_id, 'id' => $id))
            ->delete('monitoring_indicators') && $this->db->affected_rows() === 1;
    }
}
