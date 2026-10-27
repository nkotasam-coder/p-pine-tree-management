USE ptmpt;

ALTER TABLE users
  MODIFY role ENUM('admin', 'forestry_officer', 'naeb_officer', 'owner', 'buyer') NOT NULL DEFAULT 'owner';

ALTER TABLE reports
  MODIFY report_type ENUM('plantation','owner','payment','timber_value','requests') NOT NULL;

CREATE TABLE IF NOT EXISTS forest_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  requester_id INT NOT NULL,
  plantation_id INT NULL,
  request_type ENUM('plant_license','cut_license','buy_trees') NOT NULL,
  species VARCHAR(100),
  quantity INT,
  area_size DECIMAL(10,2),
  reason TEXT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  reviewed_by INT NULL,
  reviewed_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (requester_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  FOREIGN KEY (plantation_id) REFERENCES plantations(plantation_id) ON DELETE SET NULL,
  FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO users(full_name,email,phone,password,role) VALUES
('Admin User','admin@ptmpt.local','0788000001','$2y$12$8OtV6wbXV1c9m5oiHzYuZOyjOFRoRz/y92RstdQ4vn66j5HZz0BTm','admin'),
('NAEB Officer','naeb@ptmpt.local','0788000002','$2y$12$UPo4tQw5Btrcpmt.y2PX6eFj4sY74zcIeRxeVv9pYXvodNy54qpPS','naeb_officer'),
('Forestry Officer','forestry@ptmpt.local','0788000003','$2y$12$Z1cd4GDupz4FWtiMFZ6G..TFZ5KJM.Bwtiug1pytvY.ZCafA7YYUu','forestry_officer'),
('Plantation Owner','owner@ptmpt.local','0788000004','$2y$12$6i9sxx.vBpIvCQeQJdoZKO/I2erildK.CRDltWJOokA11Al/r/i1i','owner'),
('Tree Buyer','buyer@ptmpt.local','0788000005','$2y$12$.p0W9/0IhjiYQfUghnxHsO6XySNIzuVrFU2krZSTpF691vG.OAypO','buyer')
ON DUPLICATE KEY UPDATE
  full_name=VALUES(full_name),
  phone=VALUES(phone),
  role=VALUES(role);

INSERT INTO plantations(owner_id,plantation_name,district,sector,latitude,longitude,area_size)
SELECT u.user_id,'Green Hills Pine Estate','Musanze','Kinigi',-1.4362000,29.5951000,12.50
FROM users u
WHERE u.email='owner@ptmpt.local'
  AND NOT EXISTS (SELECT 1 FROM plantations p WHERE p.plantation_name='Green Hills Pine Estate');

INSERT INTO tree_types(type_name,description,unit_price,is_active,created_by)
SELECT 'Pinus patula','Fast-growing pine used for timber',45000,1,u.user_id FROM users u WHERE u.email='naeb@ptmpt.local'
ON DUPLICATE KEY UPDATE description=VALUES(description),unit_price=VALUES(unit_price),is_active=1;

INSERT INTO trees(plantation_id,tree_type_id,species,quantity,age_years,growth_status,unit_price,estimated_value)
SELECT p.plantation_id,tt.tree_type_id,tt.type_name,1200,6,'ready_for_harvest',tt.unit_price,1200*tt.unit_price
FROM plantations p
JOIN tree_types tt ON tt.type_name='Pinus patula'
WHERE p.plantation_name='Green Hills Pine Estate'
  AND NOT EXISTS (SELECT 1 FROM trees t WHERE t.plantation_id=p.plantation_id AND t.species='Pinus patula');

INSERT INTO payments(owner_id,plantation_id,amount,payment_date,payment_method,status)
SELECT p.owner_id,p.plantation_id,750000,CURDATE(),'Bank transfer','pending'
FROM plantations p
WHERE p.plantation_name='Green Hills Pine Estate'
  AND NOT EXISTS (SELECT 1 FROM payments pay WHERE pay.plantation_id=p.plantation_id AND pay.amount=750000);

INSERT INTO forest_requests(requester_id,plantation_id,tree_type_id,tree_id,request_type,species,quantity,area_size,reason,status)
SELECT u.user_id,p.plantation_id,t.tree_type_id,t.tree_id,'cut_license',t.species,150,NULL,'Request permission to cut mature pine trees for sale.','pending'
FROM users u
JOIN plantations p ON p.plantation_name='Green Hills Pine Estate'
JOIN trees t ON t.plantation_id=p.plantation_id AND t.species='Pinus patula'
WHERE u.email='owner@ptmpt.local'
  AND NOT EXISTS (SELECT 1 FROM forest_requests fr WHERE fr.requester_id=u.user_id AND fr.request_type='cut_license');

INSERT INTO forest_requests(requester_id,plantation_id,tree_type_id,tree_id,request_type,species,quantity,area_size,reason,status)
SELECT u.user_id,p.plantation_id,t.tree_type_id,t.tree_id,'buy_trees',t.species,100,NULL,'Buyer wants to purchase approved pine trees from this plantation.','pending'
FROM users u
JOIN plantations p ON p.plantation_name='Green Hills Pine Estate'
JOIN trees t ON t.plantation_id=p.plantation_id AND t.species='Pinus patula'
WHERE u.email='buyer@ptmpt.local'
  AND NOT EXISTS (SELECT 1 FROM forest_requests fr WHERE fr.requester_id=u.user_id AND fr.request_type='buy_trees');
