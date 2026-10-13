<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_signature_model extends CI_Model
{
    public function initialize()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS user_signatures (
            user_id INT UNSIGNED NOT NULL PRIMARY KEY,
            image_data MEDIUMTEXT NOT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    public function get($user_id)
    {
        $row = $this->db->where('user_id', (int) $user_id)->get('user_signatures')->row();
        return $row ? $row->image_data : '';
    }

    public function save($user_id, $image)
    {
        return $this->db->query('INSERT INTO user_signatures (user_id, image_data) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE image_data = VALUES(image_data), updated_at = CURRENT_TIMESTAMP', array((int) $user_id, $image));
    }
}
