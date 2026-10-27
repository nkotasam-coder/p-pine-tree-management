USE ptmpt;
ALTER TABLE plantations ADD COLUMN IF NOT EXISTS plant_request_id INT NULL AFTER area_size;
ALTER TABLE trees ADD COLUMN IF NOT EXISTS sale_quantity INT NOT NULL DEFAULT 0 AFTER estimated_value,
  ADD COLUMN IF NOT EXISTS reserved_quantity INT NOT NULL DEFAULT 0 AFTER sale_quantity;
ALTER TABLE forest_requests ADD COLUMN IF NOT EXISTS tree_id INT NULL AFTER tree_type_id,
  ADD COLUMN IF NOT EXISTS fulfillment_status ENUM('none','awaiting_registration','awaiting_payment','payment_pending','completed','payment_rejected') NOT NULL DEFAULT 'none' AFTER reviewed_at,
  ADD COLUMN IF NOT EXISTS completed_at TIMESTAMP NULL AFTER fulfillment_status;
UPDATE forest_requests fr JOIN trees t ON t.plantation_id=fr.plantation_id AND t.tree_type_id=fr.tree_type_id SET fr.tree_id=t.tree_id WHERE fr.tree_id IS NULL AND fr.request_type IN ('cut_license','buy_trees');
ALTER TABLE payments ADD COLUMN IF NOT EXISTS request_id INT NULL AFTER quantity;
CREATE UNIQUE INDEX IF NOT EXISTS uq_plantation_request ON plantations(plant_request_id);
CREATE UNIQUE INDEX IF NOT EXISTS uq_payment_request ON payments(request_id);
CREATE TABLE IF NOT EXISTS audit_log (audit_id BIGINT AUTO_INCREMENT PRIMARY KEY,user_id INT NULL,action VARCHAR(80) NOT NULL,entity_type VARCHAR(50) NOT NULL,entity_id INT NOT NULL,details TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,CONSTRAINT fk_audit_user FOREIGN KEY(user_id) REFERENCES users(user_id) ON DELETE SET NULL) ENGINE=InnoDB;
SET @x=(SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='ptmpt' AND TABLE_NAME='plantations' AND CONSTRAINT_NAME='fk_plantation_request'); SET @s=IF(@x=0,'ALTER TABLE plantations ADD CONSTRAINT fk_plantation_request FOREIGN KEY(plant_request_id) REFERENCES forest_requests(request_id) ON DELETE RESTRICT','SELECT 1'); PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;
SET @x=(SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='ptmpt' AND TABLE_NAME='forest_requests' AND CONSTRAINT_NAME='fk_request_tree'); SET @s=IF(@x=0,'ALTER TABLE forest_requests ADD CONSTRAINT fk_request_tree FOREIGN KEY(tree_id) REFERENCES trees(tree_id) ON DELETE RESTRICT','SELECT 1'); PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;
SET @x=(SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='ptmpt' AND TABLE_NAME='payments' AND CONSTRAINT_NAME='fk_payment_request'); SET @s=IF(@x=0,'ALTER TABLE payments ADD CONSTRAINT fk_payment_request FOREIGN KEY(request_id) REFERENCES forest_requests(request_id) ON DELETE RESTRICT','SELECT 1'); PREPARE q FROM @s; EXECUTE q; DEALLOCATE PREPARE q;
