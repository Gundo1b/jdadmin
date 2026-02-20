CREATE TABLE IF NOT EXISTS teacher_attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tutor_id INT NOT NULL,
  attendance_date DATE NOT NULL,
  status ENUM('Present','Absent') NOT NULL DEFAULT 'Absent',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_tutor_date (tutor_id, attendance_date),
  KEY tutor_id (tutor_id),
  CONSTRAINT fk_teacher_attendance_tutor FOREIGN KEY (tutor_id) REFERENCES tutors (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
