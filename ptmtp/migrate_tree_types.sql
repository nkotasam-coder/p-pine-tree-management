USE ptmpt;

CREATE TABLE IF NOT EXISTS tree_types (
  tree_type_id INT AUTO_INCREMENT PRIMARY KEY,
  type_name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255),
  unit_price DECIMAL(15,2) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tree_types_creator FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT IGNORE INTO tree_types(type_name,description,unit_price,is_active,created_by)
SELECT DISTINCT t.species,'Imported from existing tree records',MAX(t.unit_price),1,
  (SELECT user_id FROM users WHERE role='naeb_officer' ORDER BY user_id LIMIT 1)
FROM trees t
WHERE EXISTS (SELECT 1 FROM users WHERE role='naeb_officer')
GROUP BY t.species;

ALTER TABLE trees ADD COLUMN IF NOT EXISTS tree_type_id INT NULL AFTER plantation_id;
UPDATE trees t JOIN tree_types tt ON tt.type_name=t.species SET t.tree_type_id=tt.tree_type_id WHERE t.tree_type_id IS NULL;

ALTER TABLE forest_requests ADD COLUMN IF NOT EXISTS tree_type_id INT NULL AFTER plantation_id;
UPDATE forest_requests fr JOIN tree_types tt ON tt.type_name=fr.species SET fr.tree_type_id=tt.tree_type_id WHERE fr.tree_type_id IS NULL;

SET @fk1=(SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='ptmpt' AND TABLE_NAME='trees' AND CONSTRAINT_NAME='fk_trees_tree_type');
SET @sql=IF(@fk1=0,'ALTER TABLE trees ADD CONSTRAINT fk_trees_tree_type FOREIGN KEY(tree_type_id) REFERENCES tree_types(tree_type_id) ON DELETE RESTRICT','SELECT 1'); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
SET @fk2=(SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA='ptmpt' AND TABLE_NAME='forest_requests' AND CONSTRAINT_NAME='fk_requests_tree_type');
SET @sql=IF(@fk2=0,'ALTER TABLE forest_requests ADD CONSTRAINT fk_requests_tree_type FOREIGN KEY(tree_type_id) REFERENCES tree_types(tree_type_id) ON DELETE RESTRICT','SELECT 1'); PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
