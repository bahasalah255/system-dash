CREATE DATABASE IF NOT EXISTS academic_events
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE academic_events;

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    full_name  VARCHAR(100)                  NOT NULL,
    email      VARCHAR(100) UNIQUE           NOT NULL,
    password   VARCHAR(255)                  NOT NULL,
    role       ENUM('student', 'manager')    DEFAULT 'student',
    created_at TIMESTAMP                     DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150)  NOT NULL,
    description TEXT,
    event_date  DATE          NOT NULL,
    location    VARCHAR(150),
    created_by  INT,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS password_resets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(100) NOT NULL,
    token      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
);
