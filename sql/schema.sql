-- ============================================
-- CV/Resume Builder — Database Schema
-- MySQL 8.x | InnoDB | utf8mb4_unicode_ci
-- ============================================

CREATE DATABASE IF NOT EXISTS cv_builder
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cv_builder;

-- ============================================
-- 1. USERS
-- ============================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(512) DEFAULT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_created (created_at)
) ENGINE=InnoDB;

-- ============================================
-- 2. CV PROFILES
-- ============================================
CREATE TABLE cv_profiles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    linkedin VARCHAR(512) DEFAULT NULL,
    website VARCHAR(512) DEFAULT NULL,
    summary TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cv_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_cv_user (user_id),
    INDEX idx_cv_created (created_at)
) ENGINE=InnoDB;

-- ============================================
-- 3. EDUCATION
-- ============================================
CREATE TABLE education (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cv_id BIGINT UNSIGNED NOT NULL,
    institution VARCHAR(255) NOT NULL,
    degree VARCHAR(150) NOT NULL,
    field_of_study VARCHAR(200) DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    grade VARCHAR(50) DEFAULT NULL,
    sort_order SMALLINT UNSIGNED DEFAULT 0,
    CONSTRAINT fk_edu_cv FOREIGN KEY (cv_id) REFERENCES cv_profiles(id) ON DELETE CASCADE,
    INDEX idx_edu_cv (cv_id),
    INDEX idx_edu_sort (cv_id, sort_order)
) ENGINE=InnoDB;

-- ============================================
-- 4. EXPERIENCE
-- ============================================
CREATE TABLE experience (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cv_id BIGINT UNSIGNED NOT NULL,
    company VARCHAR(255) NOT NULL,
    job_title VARCHAR(200) NOT NULL,
    location VARCHAR(200) DEFAULT NULL,
    start_date DATE NOT NULL,
    end_date DATE DEFAULT NULL,
    description TEXT DEFAULT NULL,
    sort_order SMALLINT UNSIGNED DEFAULT 0,
    CONSTRAINT fk_exp_cv FOREIGN KEY (cv_id) REFERENCES cv_profiles(id) ON DELETE CASCADE,
    INDEX idx_exp_cv (cv_id),
    INDEX idx_exp_sort (cv_id, sort_order)
) ENGINE=InnoDB;

-- ============================================
-- 5. SKILLS
-- ============================================
CREATE TABLE skills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cv_id BIGINT UNSIGNED NOT NULL,
    skill_name VARCHAR(100) NOT NULL,
    proficiency_level TINYINT UNSIGNED NOT NULL DEFAULT 3,
    sort_order SMALLINT UNSIGNED DEFAULT 0,
    CONSTRAINT fk_skill_cv FOREIGN KEY (cv_id) REFERENCES cv_profiles(id) ON DELETE CASCADE,
    CONSTRAINT chk_proficiency CHECK (proficiency_level BETWEEN 1 AND 5),
    INDEX idx_skill_cv (cv_id)
) ENGINE=InnoDB;

-- ============================================
-- 6. CERTIFICATIONS
-- ============================================
CREATE TABLE certifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cv_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    issuer VARCHAR(200) DEFAULT NULL,
    issue_date DATE DEFAULT NULL,
    credential_url VARCHAR(512) DEFAULT NULL,
    sort_order SMALLINT UNSIGNED DEFAULT 0,
    CONSTRAINT fk_cert_cv FOREIGN KEY (cv_id) REFERENCES cv_profiles(id) ON DELETE CASCADE,
    INDEX idx_cert_cv (cv_id)
) ENGINE=InnoDB;

-- ============================================
-- 7. PROJECTS
-- ============================================
CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cv_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    tech_stack VARCHAR(500) DEFAULT NULL,
    project_url VARCHAR(512) DEFAULT NULL,
    sort_order SMALLINT UNSIGNED DEFAULT 0,
    CONSTRAINT fk_proj_cv FOREIGN KEY (cv_id) REFERENCES cv_profiles(id) ON DELETE CASCADE,
    INDEX idx_proj_cv (cv_id)
) ENGINE=InnoDB;

-- ============================================
-- 8. TEMPLATES (Metadata)
-- ============================================
CREATE TABLE templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(50) NOT NULL,
    description TEXT DEFAULT NULL,
    thumbnail VARCHAR(512) DEFAULT NULL,
    has_dark_variant TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    UNIQUE KEY uq_template_slug (slug)
) ENGINE=InnoDB;

-- ============================================
-- 9. CV DOWNLOADS (Analytics)
-- ============================================
CREATE TABLE cv_downloads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    cv_id BIGINT UNSIGNED NOT NULL,
    template_id BIGINT UNSIGNED DEFAULT NULL,
    format ENUM('pdf', 'print') NOT NULL DEFAULT 'pdf',
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_dl_cv FOREIGN KEY (cv_id) REFERENCES cv_profiles(id) ON DELETE CASCADE,
    CONSTRAINT fk_dl_template FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE SET NULL,
    INDEX idx_dl_cv (cv_id),
    INDEX idx_dl_template (template_id),
    INDEX idx_dl_date (downloaded_at)
) ENGINE=InnoDB;

-- ============================================
-- 10. LOGIN ATTEMPTS (Rate Limiting)
-- ============================================
CREATE TABLE login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(255) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_attempt_ip (ip_address, attempted_at),
    INDEX idx_attempt_email (email, attempted_at)
) ENGINE=InnoDB;

-- ============================================
-- SEED DATA
-- ============================================

-- Default templates
INSERT INTO templates (name, slug, description, has_dark_variant) VALUES
('Classic', 'classic', 'Professional sidebar layout with blue accent', 1),
('Modern', 'modern', 'Clean two-column layout with icon-based sections', 1),
('Creative', 'creative', 'Bold header with timeline layout', 1);

-- Default admin account (password: Admin@123 — CHANGE IN PRODUCTION)
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin', 'admin@cvbuilder.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
