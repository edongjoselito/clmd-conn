<?php
// Migration script to create audit_trail table
// Run this file once to create the table: http://localhost/sbm/create_audit_trail.php

$mysqli = new mysqli("localhost", "root", "", "depedmis_ftad");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS `audit_trail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `user_position` varchar(50) DEFAULT NULL,
  `action` varchar(50) NOT NULL COMMENT 'ADD, UPDATE, DELETE',
  `table_name` varchar(100) NOT NULL,
  `record_id` varchar(100) DEFAULT NULL COMMENT 'ID of the affected record',
  `old_values` text DEFAULT NULL COMMENT 'JSON string of old values (for UPDATE/DELETE)',
  `new_values` text DEFAULT NULL COMMENT 'JSON string of new values (for ADD/UPDATE)',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `table_name` (`table_name`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if ($mysqli->query($sql) === TRUE) {
    echo "Audit trail table created successfully or already exists.";
} else {
    echo "Error creating table: " . $mysqli->error;
}

$mysqli->close();
?>
