CREATE DATABASE IF NOT EXISTS ptmpt CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ptmpt;

CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  phone VARCHAR(20),
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'forestry_officer', 'naeb_officer', 'owner', 'buyer') NOT NULL DEFAULT 'owner',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS plantations (
  plantation_id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  plantation_name VARCHAR(150) NOT NULL,
  district VARCHAR(100) NOT NULL,
  sector VARCHAR(100) NOT NULL,
  latitude DECIMAL(10,7) NOT NULL,
  longitude DECIMAL(10,7) NOT NULL,
  area_size DECIMAL(10,2) NOT NULL,
  plant_request_id INT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tree_types (
  tree_type_id INT AUTO_INCREMENT PRIMARY KEY,
  type_name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255),
  unit_price DECIMAL(15,2) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS trees (
  tree_id INT AUTO_INCREMENT PRIMARY KEY,
  plantation_id INT NOT NULL,
  tree_type_id INT NOT NULL,
  species VARCHAR(100) NOT NULL,
  quantity INT NOT NULL,
  age_years INT NOT NULL,
  growth_status ENUM('seedling','growing','mature','ready_for_harvest') NOT NULL,
  unit_price DECIMAL(15,2) NOT NULL DEFAULT 0,
  estimated_value DECIMAL(15,2) NOT NULL DEFAULT 0,
  sale_quantity INT NOT NULL DEFAULT 0,
  reserved_quantity INT NOT NULL DEFAULT 0,
  FOREIGN KEY (plantation_id) REFERENCES plantations(plantation_id) ON DELETE CASCADE
  ,FOREIGN KEY (tree_type_id) REFERENCES tree_types(tree_type_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  plantation_id INT NOT NULL,
  buyer_id INT NULL,
  tree_id INT NULL,
  quantity INT NOT NULL DEFAULT 1,
  request_id INT NULL UNIQUE,
  amount DECIMAL(15,2) NOT NULL,
  payment_date DATE NOT NULL,
  payment_method VARCHAR(50) NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  FOREIGN KEY (tree_id) REFERENCES trees(tree_id) ON DELETE RESTRICT,
  FOREIGN KEY (plantation_id) REFERENCES plantations(plantation_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forest_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  requester_id INT NOT NULL,
  plantation_id INT NULL,
  tree_type_id INT NULL,
  tree_id INT NULL,
  request_type ENUM('plant_license','cut_license','buy_trees') NOT NULL,
  species VARCHAR(100),
  quantity INT,
  area_size DECIMAL(10,2),
  reason TEXT NOT NULL,
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  reviewed_by INT NULL,
  reviewed_at TIMESTAMP NULL,
  fulfillment_status ENUM('none','awaiting_registration','awaiting_payment','payment_pending','completed','payment_rejected') NOT NULL DEFAULT 'none',
  completed_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (requester_id) REFERENCES users(user_id) ON DELETE RESTRICT,
  FOREIGN KEY (plantation_id) REFERENCES plantations(plantation_id) ON DELETE SET NULL,
  FOREIGN KEY (tree_type_id) REFERENCES tree_types(tree_type_id) ON DELETE RESTRICT,
  FOREIGN KEY (tree_id) REFERENCES trees(tree_id) ON DELETE RESTRICT,
  FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS audit_log (
  audit_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  action VARCHAR(80) NOT NULL,
  entity_type VARCHAR(50) NOT NULL,
  entity_id INT NOT NULL,
  details TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reports (
  report_id INT AUTO_INCREMENT PRIMARY KEY,
  report_type ENUM('plantation','owner','payment','timber_value','requests') NOT NULL,
  generated_by INT NOT NULL,
  generated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (generated_by) REFERENCES users(user_id) ON DELETE RESTRICT
) ENGINE=InnoDB;
