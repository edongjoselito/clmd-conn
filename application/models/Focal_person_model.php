<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Focal_person_model extends CI_Model
{
    public function initialize()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS focal_persons (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            region_id INT NOT NULL,
            division_id INT NOT NULL,
            learning_area VARCHAR(40) NULL,
            name VARCHAR(150) NOT NULL,
            position VARCHAR(150) NOT NULL,
            picture MEDIUMTEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            KEY region_division (region_id, division_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        if (!$this->db->field_exists('learning_area', 'focal_persons')) {
            $this->db->query('ALTER TABLE focal_persons ADD learning_area VARCHAR(40) NULL AFTER division_id');
        }
    }

    public function directory($region_id = null, $learning_area = '', $division_id = 0)
    {
        $this->db->select('f.*, d.description AS division_name')->from('focal_persons f')
            ->join('division d', 'd.id = f.division_id', 'left');
        if ($region_id !== null) { $this->db->where('f.region_id', (int) $region_id); }
        if ($learning_area !== '') { $this->db->where('f.learning_area', $learning_area); }
        if ($division_id > 0) { $this->db->where('f.division_id', (int) $division_id); }
        return $this->db->order_by('d.description', 'ASC')->order_by('f.name', 'ASC')->get()->result();
    }

    public function person($region_id, $id)
    {
        return $this->db->where('region_id', (int) $region_id)->where('id', (int) $id)->get('focal_persons')->row();
    }

    public function save($region_id, $id, $data)
    {
        if ($region_id <= 0 || ($id && !$this->person($region_id, $id))) { return false; }
        if (!$this->db->where('id', $data['division_id'])->where('region_id', $region_id)->get('division')->row()) { return false; }
        $data = array_intersect_key($data, array_flip(array('name', 'position', 'division_id', 'picture', 'learning_area')));
        $data['region_id'] = (int) $region_id;
        if ($id) {
            return $this->db->where('id', (int) $id)->where('region_id', (int) $region_id)->update('focal_persons', $data);
        }
        return $this->db->insert('focal_persons', $data);
    }

    public function delete($region_id, $id)
    {
        $this->db->where('region_id', (int) $region_id)->where('id', (int) $id)->delete('focal_persons');
        return $this->db->affected_rows() === 1;
    }
}
