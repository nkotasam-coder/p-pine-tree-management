USE ptmpt;

ALTER TABLE trees
  ADD COLUMN IF NOT EXISTS unit_price DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER growth_status;

ALTER TABLE payments
  ADD COLUMN IF NOT EXISTS buyer_id INT NULL AFTER plantation_id,
  ADD COLUMN IF NOT EXISTS tree_id INT NULL AFTER buyer_id,
  ADD COLUMN IF NOT EXISTS quantity INT NOT NULL DEFAULT 1 AFTER tree_id;

SET @buyer_fk = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='ptmpt' AND TABLE_NAME='payments' AND CONSTRAINT_NAME='fk_payments_buyer');
SET @sql = IF(@buyer_fk=0, 'ALTER TABLE payments ADD CONSTRAINT fk_payments_buyer FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE RESTRICT', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @tree_fk = (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='ptmpt' AND TABLE_NAME='payments' AND CONSTRAINT_NAME='fk_payments_tree');
SET @sql = IF(@tree_fk=0, 'ALTER TABLE payments ADD CONSTRAINT fk_payments_tree FOREIGN KEY (tree_id) REFERENCES trees(tree_id) ON DELETE RESTRICT', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
