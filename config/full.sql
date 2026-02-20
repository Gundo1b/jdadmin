-- Full Database Schema for School Management System
-- This file combines all tables and data from programs.sql, students.sql, and tutor.sql

-- Programs Table
CREATE TABLE IF NOT EXISTS `programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `program_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default programs
INSERT INTO `programs` (`program_name`, `description`) VALUES
('Academic Support', 'General academic support and tutoring'),
('Exam Preparation', 'Focused exam preparation and revision'),
('Homework Help', 'Assistance with homework and assignments'),
('Full Tutoring', 'Comprehensive tutoring across all subjects');

-- Grades Table
CREATE TABLE IF NOT EXISTS `grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default grades
INSERT INTO `grades` (`name`, `description`) VALUES
('Grade 1', 'Primary School Grade 1'),
('Grade 2', 'Primary School Grade 2'),
('Grade 3', 'Primary School Grade 3'),
('Grade 4', 'Primary School Grade 4'),
('Grade 5', 'Primary School Grade 5'),
('Grade 6', 'Primary School Grade 6'),
('Grade 7', 'Primary School Grade 7'),
('Grade 8', 'Primary School Grade 8'),
('Grade 9', 'High School Grade 9'),
('Grade 10', 'High School Grade 10'),
('Grade 11', 'High School Grade 11'),
('Grade 12', 'High School Grade 12');


-- Classes Table (Curriculums)
CREATE TABLE IF NOT EXISTS `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_name` varchar(100) NOT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `grade_id` (`grade_id`),
  CONSTRAINT `fk_class_grade` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default classes
INSERT INTO `classes` (`class_name`, `description`) VALUES
('Cambridge', 'Cambridge International Curriculum'),
('CAPS', 'Curriculum and Assessment Policy Statement'),
('IEB', 'Independent Examinations Board'),
('IB', 'International Baccalaureate'),
('Montessori', 'Montessori Curriculum');


-- Subjects Table
CREATE TABLE IF NOT EXISTS `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default subjects
INSERT INTO `subjects` (`subject_name`, `description`) VALUES
('Mathematics', 'Mathematics subject'),
('English', 'English language and literature'),
('Afrikaans', 'Afrikaans language'),
('isiZulu', 'isiZulu language'),
('isiXhosa', 'isiXhosa language'),
('Sesotho', 'Sesotho language'),
('Setswana', 'Setswana language'),
('Sepedi', 'Sepedi language'),
('Tswana', 'Tswana language'),
('Xitsonga', 'Xitsonga language'),
('siSwati', 'siSwati language'),
('Ndebele', 'Ndebele language'),
('Physical Science', 'Physics and Chemistry'),
('Life Sciences', 'Biology'),
('Geography', 'Geography'),
('History', 'History'),
('Economics', 'Economics'),
('Business Studies', 'Business Studies'),
('Accounting', 'Accounting'),
('Physical Education', 'Physical Education'),
('Life Orientation', 'Life Orientation');

-- Expanded Students Table
CREATE TABLE IF NOT EXISTS `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,

  -- Student Details
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `physical_address` text DEFAULT NULL,
  `home_language` varchar(100) DEFAULT NULL,

  -- Academic Information
  `program` varchar(100) DEFAULT NULL,
  `grade_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `learning_mode` enum('Face to Face','Online','Home Tutoring') DEFAULT NULL,
  `school_name` varchar(200) DEFAULT NULL,

  -- File Uploads
  `latest_results_file` varchar(255) DEFAULT NULL,
  `id_document_file` varchar(255) DEFAULT NULL,

  -- Next of Kin 1
  `kin1_name` varchar(100) DEFAULT NULL,
  `kin1_surname` varchar(100) DEFAULT NULL,
  `kin1_personal_contact` varchar(50) DEFAULT NULL,
  `kin1_work_contact` varchar(50) DEFAULT NULL,
  `kin1_email` varchar(150) DEFAULT NULL,
  `kin1_physical_address` text DEFAULT NULL,
  `kin1_relation` varchar(100) DEFAULT NULL,
  `kin1_occupation` varchar(100) DEFAULT NULL,
  `kin1_home_language` varchar(100) DEFAULT NULL,

  -- Next of Kin 2 (Optional)
  `kin2_name` varchar(100) DEFAULT NULL,
  `kin2_surname` varchar(100) DEFAULT NULL,
  `kin2_personal_contact` varchar(50) DEFAULT NULL,
  `kin2_work_contact` varchar(50) DEFAULT NULL,
  `kin2_email` varchar(150) DEFAULT NULL,
  `kin2_physical_address` text DEFAULT NULL,
  `kin2_relation` varchar(100) DEFAULT NULL,
  `kin2_occupation` varchar(100) DEFAULT NULL,
  `kin2_home_language` varchar(100) DEFAULT NULL,

  -- Additional Information
  `referral_source` varchar(100) DEFAULT NULL,
  `referral_other` varchar(255) DEFAULT NULL,
  `additional_notes` text DEFAULT NULL,

  -- System Fields
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),
  KEY `grade_id` (`grade_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `fk_student_grade` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_student_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Student Subjects Table (Many-to-Many)
CREATE TABLE IF NOT EXISTS `student_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `fk_student_subjects_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_student_subjects_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tutors Table
CREATE TABLE IF NOT EXISTS `tutors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `qualifications` text DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL,
  `specialization` varchar(255) DEFAULT NULL,
  `availability` text DEFAULT NULL,
  `hourly_rate` decimal(10,2) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tutor Subjects Table (Many-to-Many)
CREATE TABLE IF NOT EXISTS `tutor_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutor_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `fk_tutor_subjects_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tutor_subjects_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tutor Classes Table (Many-to-Many)
CREATE TABLE IF NOT EXISTS `tutor_classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutor_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `fk_tutor_classes_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tutor_classes_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Timetable Table
CREATE TABLE IF NOT EXISTS `timetable` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `class_id` (`class_id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `fk_timetable_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_timetable_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_timetable_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tutor Grades Table (Many-to-Many)
CREATE TABLE IF NOT EXISTS `tutor_grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tutor_id` int(11) NOT NULL,
  `grade_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tutor_id` (`tutor_id`),
  KEY `grade_id` (`grade_id`),
  CONSTRAINT `fk_tutor_grades_tutor` FOREIGN KEY (`tutor_id`) REFERENCES `tutors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_tutor_grades_grade` FOREIGN KEY (`grade_id`) REFERENCES `grades` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Direct Messages Table
CREATE TABLE IF NOT EXISTS `direct_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipient_type` enum('tutor','student') NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message_body` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recipient` (`recipient_type`, `recipient_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
