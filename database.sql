-- Voters System Database
-- Run this in phpMyAdmin or MySQL CLI

CREATE DATABASE IF NOT EXISTS voters_system;
USE voters_system;

-- Admin table
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Voters table
CREATE TABLE IF NOT EXISTS voters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voter_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    has_voted TINYINT(1) DEFAULT 0
);

-- Candidates table
CREATE TABLE IF NOT EXISTS candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    position VARCHAR(100) NOT NULL,
    votes INT DEFAULT 0
);

-- Votes table (audit log)
CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voter_id VARCHAR(20) NOT NULL,
    candidate_id INT NOT NULL,
    voted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: username = admin, password = admin123
INSERT INTO admin (username, password) VALUES ('admin', MD5('admin123'));

-- Sample voters
INSERT INTO voters (voter_id, full_name) VALUES
('VOT-001', 'Alice Johnson'),
('VOT-002', 'Bob Smith'),
('VOT-003', 'Carol White'),
('VOT-004', 'David Brown'),
('VOT-005', 'Eve Davis');

-- Sample candidates
INSERT INTO candidates (full_name, position) VALUES
('James Mwangi', 'President'),
('Grace Wanjiku', 'President'),
('Peter Kamau', 'Vice President'),
('Mary Njeri', 'Vice President');
