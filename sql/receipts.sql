CREATE TABLE IF NOT EXISTS receipts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  receipt_no VARCHAR(30) NOT NULL,
  receipt_date DATE NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0,
  method ENUM('Cash','Card','Transfer','Other') NOT NULL,
  notes VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_receipt_no (receipt_no),
  KEY student_id (student_id),
  CONSTRAINT fk_receipts_student FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
