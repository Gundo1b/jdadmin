CREATE TABLE IF NOT EXISTS outstanding_balances (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  grade_id INT NULL,
  class_id INT NULL,
  total_due DECIMAL(10,2) NOT NULL DEFAULT 0,
  total_paid DECIMAL(10,2) NOT NULL DEFAULT 0,
  balance DECIMAL(10,2) NOT NULL DEFAULT 0,
  status ENUM('Overdue','Due','Cleared') NOT NULL DEFAULT 'Due',
  last_payment_date DATE NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY student_id (student_id),
  KEY grade_id (grade_id),
  KEY class_id (class_id),
  CONSTRAINT fk_outstanding_balances_student FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE,
  CONSTRAINT fk_outstanding_balances_grade FOREIGN KEY (grade_id) REFERENCES grades (id) ON DELETE SET NULL,
  CONSTRAINT fk_outstanding_balances_class FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
