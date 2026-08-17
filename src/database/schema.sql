CREATE DATABASE IF NOT EXISTS eventhub;
USE eventhub;

CREATE TABLE IF NOT EXISTS users (
    user_id        INT AUTO_INCREMENT PRIMARY KEY,
    full_name      VARCHAR(100) NOT NULL,
    email          VARCHAR(100) NOT NULL UNIQUE,
    password       VARCHAR(255) NOT NULL,        -- hashed with password_hash()
    role           ENUM('admin','student') NOT NULL DEFAULT 'student',
    contact_number VARCHAR(20),
    status         ENUM('active','blocked') NOT NULL DEFAULT 'active',
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    category_id   INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(60) NOT NULL UNIQUE,
    description   VARCHAR(255),
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS events (
    event_id    INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(150) NOT NULL,
    description TEXT,
    category_id INT NOT NULL,
    event_date  DATE NOT NULL,
    start_time  TIME NOT NULL,
    end_time    TIME,
    venue       VARCHAR(120) NOT NULL,
    capacity    INT NOT NULL,
    image_path  VARCHAR(255),
    status      ENUM('published','cancelled') NOT NULL DEFAULT 'published',
    created_by  INT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id) REFERENCES categories(category_id),
    FOREIGN KEY (created_by)  REFERENCES users(user_id)
);

    CREATE TABLE IF NOT EXISTS registrations (
    registration_id    INT AUTO_INCREMENT PRIMARY KEY,
    event_id           INT NOT NULL,
    user_id            INT NOT NULL,
    status             ENUM('registered','cancelled') NOT NULL DEFAULT 'registered', -- Remove if cancelation history not available
    registered_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Remove if cancelation history not available

    UNIQUE KEY uq_registrations (event_id, user_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)  REFERENCES users(user_id)   ON DELETE CASCADE

    -- NOTE: cancelling keeps the row, so don't use plain INSERT use DUPLICATE KEY UPDATE also:
    -- INSERT INTO registrations (event_id, user_id) VALUES (?, ?)
    -- ON DUPLICATE KEY UPDATE status='registered';
);

CREATE TABLE IF NOT EXISTS announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(150) NOT NULL,
    message         TEXT NOT NULL,
    event_id        INT NULL,    -- NULL = general announcement
    posted_by       INT NOT NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1, -- works as a bool
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (event_id)  REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (posted_by) REFERENCES users(user_id)
);

INSERT INTO categories (category_name, description) VALUES
    ('Sports',    'Inter-faculty matches, tournaments and fitness sessions'),
    ('Technology','Hackathons, coding competitions and tech talks'),
    ('Cultural',  'Music, dance, drama and festival celebrations'),
    ('Workshop',  'Skill-building sessions and guest lectures'),
    ('Club',      'Club meetings, recruitment drives and society events');