CREATE TABLE customer_credit (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT(11) NOT NULL,
  payment_id INT(11) NOT NULL,
  item_no varchar(250) NOT NULL,
  `date` date NOT NULL,
  credit_balance DECIMAL(16,2) NOT NULL DEFAULT 0.00,
  debit_balance DECIMAL(16,2) NOT NULL DEFAULT 0.00,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);