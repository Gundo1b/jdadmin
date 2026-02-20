-- messages.sql
CREATE TABLE IF NOT EXISTS `tutor_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutor_id` int(11) NOT NULL,
  `recipient_type` enum('student','admin') NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `message_subject` varchar(255) NOT NULL,
  `message_body` text NOT NULL,
  `attachment_original_name` varchar(255) DEFAULT NULL,
  `attachment_stored_name` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(500) DEFAULT NULL,
  `attachment_size` int(11) NOT NULL DEFAULT 0,
  `direction` enum('outbound','inbound') NOT NULL DEFAULT 'outbound',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_tutor_messages_tutor` (`tutor_id`),
  KEY `idx_tutor_messages_student` (`student_id`),
  CONSTRAINT `fk_tutor_messages_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tutor_messages_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TRIGGER IF EXISTS `trg_tutor_messages_before_insert`;
DROP TRIGGER IF EXISTS `trg_tutor_messages_before_update`;

DELIMITER $$
CREATE TRIGGER `trg_tutor_messages_before_insert`
BEFORE INSERT ON `tutor_messages`
FOR EACH ROW
BEGIN
  IF NEW.recipient_type = 'admin' THEN
    SET NEW.student_id = NULL;
  ELSEIF NEW.recipient_type = 'student' AND (NEW.student_id IS NULL OR NEW.student_id = 0) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'student_id is required when recipient_type is student';
  END IF;
END$$

CREATE TRIGGER `trg_tutor_messages_before_update`
BEFORE UPDATE ON `tutor_messages`
FOR EACH ROW
BEGIN
  IF NEW.recipient_type = 'admin' THEN
    SET NEW.student_id = NULL;
  ELSEIF NEW.recipient_type = 'student' AND (NEW.student_id IS NULL OR NEW.student_id = 0) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'student_id is required when recipient_type is student';
  END IF;
END$$
DELIMITER ;
