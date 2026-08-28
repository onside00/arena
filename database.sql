CREATE DATABASE IF NOT EXISTS sports_live
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE sports_live;

CREATE TABLE IF NOT EXISTS matches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team1_name VARCHAR(100) NOT NULL,
    team1_logo VARCHAR(255) DEFAULT NULL,
    team2_name VARCHAR(100) NOT NULL,
    team2_logo VARCHAR(255) DEFAULT NULL,
    league VARCHAR(100) NOT NULL,
    match_time DATETIME NOT NULL,
    redirect_url VARCHAR(500) NOT NULL,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    display_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_match_time (match_time),
    INDEX idx_status_time (status, match_time),
    INDEX idx_display_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
