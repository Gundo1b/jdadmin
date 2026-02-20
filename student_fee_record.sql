CREATE TABLE IF NOT EXISTS student_fee_records (
  id INT(11) NOT NULL AUTO_INCREMENT,
  student_id INT(11) NOT NULL,
  period VARCHAR(100) NOT NULL,
  amount_due DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  notes VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_student_fee_records_student (student_id),
  CONSTRAINT fk_student_fee_records_student
    FOREIGN KEY (student_id) REFERENCES students(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- student dropdown
SELECT id, first_name, last_name
FROM students
ORDER BY first_name, last_name;

-- list records
SELECT sfr.id,
       CONCAT(s.first_name, ' ', s.last_name) AS student_name,
       sfr.period,
       sfr.amount_due,
       sfr.amount_paid,
       sfr.notes
FROM student_fee_records sfr
JOIN students s ON s.id = sfr.student_id
ORDER BY sfr.id DESC;
