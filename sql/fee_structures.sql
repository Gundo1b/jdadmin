CREATE TABLE IF NOT EXISTS fee_structures (
  id INT AUTO_INCREMENT PRIMARY KEY,
  structure_name VARCHAR(100) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  grade_id INT NULL,
  class_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY grade_id (grade_id),
  KEY class_id (class_id),
  CONSTRAINT fk_fee_structures_grade FOREIGN KEY (grade_id) REFERENCES grades (id) ON DELETE SET NULL,
  CONSTRAINT fk_fee_structures_class FOREIGN KEY (class_id) REFERENCES classes (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
