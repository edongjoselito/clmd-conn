-- Table for storing TA report unlock requests
CREATE TABLE IF NOT EXISTS `unlock_request` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
