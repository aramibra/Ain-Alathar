SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS ae_assessment_images;
DROP TABLE IF EXISTS ae_field_assessments;
DROP TABLE IF EXISTS ae_report_assignments;
DROP TABLE IF EXISTS ae_report_attachments;
DROP TABLE IF EXISTS ae_incident_reports;
DROP TABLE IF EXISTS ae_site_risks;
DROP TABLE IF EXISTS ae_site_images;
DROP TABLE IF EXISTS ae_risk_types;
DROP TABLE IF EXISTS ae_heritage_sites;
DROP TABLE IF EXISTS ae_users;
DROP TABLE IF EXISTS ae_schema_meta;

CREATE TABLE ae_schema_meta (
  id TINYINT PRIMARY KEY,
  schema_version VARCHAR(20) NOT NULL,
  installed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ae_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','archaeologist','user') NOT NULL DEFAULT 'user',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  phone VARCHAR(30) NULL,
  preferred_language ENUM('ar','en') NOT NULL DEFAULT 'ar',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ae_heritage_sites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(160) NOT NULL,
  city VARCHAR(100) NOT NULL,
  region VARCHAR(100) NOT NULL DEFAULT 'تبوك',
  site_type VARCHAR(100) NOT NULL,
  description TEXT,
  latitude DECIMAL(10,6) NOT NULL,
  longitude DECIMAL(10,6) NOT NULL,
  risk_percentage INT NOT NULL DEFAULT 0,
  risk_level ENUM('منخفض','متوسط','مرتفع') NOT NULL DEFAULT 'منخفض',
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ae_sites_status (status),
  INDEX idx_ae_sites_risk (risk_percentage),
  FOREIGN KEY(created_by) REFERENCES ae_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ae_site_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  site_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  caption VARCHAR(255),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(site_id) REFERENCES ae_heritage_sites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ae_risk_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ae_site_risks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  site_id INT NOT NULL,
  risk_type_id INT NOT NULL,
  severity TINYINT NOT NULL DEFAULT 1,
  FOREIGN KEY(site_id) REFERENCES ae_heritage_sites(id) ON DELETE CASCADE,
  FOREIGN KEY(risk_type_id) REFERENCES ae_risk_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ae_incident_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  site_id INT NOT NULL,
  user_id INT NULL,
  title VARCHAR(180) NOT NULL,
  description TEXT NOT NULL,
  status ENUM('new','assigned','under_review','closed') NOT NULL DEFAULT 'new',
  priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ae_reports_status (status),
  FOREIGN KEY(site_id) REFERENCES ae_heritage_sites(id) ON DELETE CASCADE,
  FOREIGN KEY(user_id) REFERENCES ae_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ae_report_attachments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(report_id) REFERENCES ae_incident_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ae_report_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_id INT NOT NULL,
  archaeologist_id INT NOT NULL,
  assigned_by INT NOT NULL,
  status ENUM('assigned','completed','cancelled') NOT NULL DEFAULT 'assigned',
  notes TEXT,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(report_id) REFERENCES ae_incident_reports(id) ON DELETE CASCADE,
  FOREIGN KEY(archaeologist_id) REFERENCES ae_users(id) ON DELETE CASCADE,
  FOREIGN KEY(assigned_by) REFERENCES ae_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ae_field_assessments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  report_id INT NULL,
  site_id INT NOT NULL,
  archaeologist_id INT NOT NULL,
  visit_date DATE NOT NULL,
  risk_type VARCHAR(120) NOT NULL,
  damage_severity TINYINT NOT NULL,
  risk_probability TINYINT NOT NULL,
  spread_speed TINYINT NOT NULL,
  affected_area TINYINT NOT NULL,
  heritage_impact TINYINT NOT NULL,
  urgency_level TINYINT NOT NULL,
  risk_percentage INT NOT NULL,
  risk_level ENUM('منخفض','متوسط','مرتفع') NOT NULL,
  notes TEXT,
  recommendations TEXT,
  status ENUM('submitted','approved','returned','rejected') NOT NULL DEFAULT 'submitted',
  reviewed_by INT NULL,
  reviewed_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_ae_assessments_status (status),
  FOREIGN KEY(report_id) REFERENCES ae_incident_reports(id) ON DELETE SET NULL,
  FOREIGN KEY(site_id) REFERENCES ae_heritage_sites(id) ON DELETE CASCADE,
  FOREIGN KEY(archaeologist_id) REFERENCES ae_users(id) ON DELETE CASCADE,
  FOREIGN KEY(reviewed_by) REFERENCES ae_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ae_assessment_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  assessment_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY(assessment_id) REFERENCES ae_field_assessments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO ae_schema_meta(id, schema_version) VALUES(1, '16');
SET FOREIGN_KEY_CHECKS = 1;
