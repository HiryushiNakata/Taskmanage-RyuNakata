-- Academic Task Management System
-- Run this whole file in phpMyAdmin (SQL tab) or with: mysql -u root < database.sql

CREATE DATABASE IF NOT EXISTS student_planner_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE student_planner_db;

-- One row per registered student
CREATE TABLE IF NOT EXISTS users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    full_name     VARCHAR(100) NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,   -- UNIQUE: no two accounts can share an email
    password_hash VARCHAR(255) NOT NULL,          -- never store the real password
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- One row per assignment; each assignment belongs to exactly one user
CREATE TABLE IF NOT EXISTS assignments (
    task_id      INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT          NOT NULL,
    subject      VARCHAR(50)  NOT NULL,
    task_details TEXT         NOT NULL,
    deadline     DATE         NOT NULL,
    status       VARCHAR(20)  NOT NULL DEFAULT 'Pending',

    -- Speeds up "show me this user's tasks sorted by deadline"
    INDEX idx_user_deadline (user_id, deadline),

    -- If a user is deleted, their tasks are deleted too
    CONSTRAINT fk_assignments_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON DELETE CASCADE
);
