-- Database schema for Monogram (Instagram Clone)
-- Create database
CREATE DATABASE IF NOT EXISTS if0_38518765_monogram_db;

USE if0_38518765_monogram_db;

-- Users table
CREATE TABLE
    IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(30) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100),
        bio TEXT,
        profile_pic VARCHAR(255),
        is_verified BOOLEAN DEFAULT 0,
        is_admin BOOLEAN DEFAULT 0,
        created_at DATETIME NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

-- Posts table
CREATE TABLE
    IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        caption TEXT,
        created_at DATETIME NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    );

-- Comments table
CREATE TABLE
    IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE
    );

-- Likes table
CREATE TABLE
    IF NOT EXISTS likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        created_at DATETIME NOT NULL,
        UNIQUE KEY user_post_unique (user_id, post_id),
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE
    );

-- Follows table
CREATE TABLE
    IF NOT EXISTS follows (
        id INT AUTO_INCREMENT PRIMARY KEY,
        follower_id INT NOT NULL,
        following_id INT NOT NULL,
        created_at DATETIME NOT NULL,
        UNIQUE KEY follower_following_unique (follower_id, following_id),
        FOREIGN KEY (follower_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (following_id) REFERENCES users (id) ON DELETE CASCADE
    );

-- Saved posts table
CREATE TABLE
    IF NOT EXISTS saved_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        created_at DATETIME NOT NULL,
        UNIQUE KEY user_post_saved_unique (user_id, post_id),
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE
    );

-- Notifications table
CREATE TABLE
    IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        from_user_id INT NOT NULL,
        type ENUM (
            'like',
            'comment',
            'follow',
            'mention',
            'share',
            'story_like',
            'story_reply'
        ) NOT NULL,
        post_id INT,
        comment_id INT,
        story_id INT,
        message TEXT,
        is_read BOOLEAN DEFAULT 0,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (from_user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
        FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE
    );

-- Reports table
CREATE TABLE
    IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reporter_id INT NOT NULL,
        reported_user_id INT,
        reported_post_id INT,
        reported_comment_id INT,
        reason TEXT NOT NULL,
        status ENUM ('pending', 'reviewed', 'resolved') DEFAULT 'pending',
        created_at DATETIME NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (reporter_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (reported_user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (reported_post_id) REFERENCES posts (id) ON DELETE CASCADE,
        FOREIGN KEY (reported_comment_id) REFERENCES comments (id) ON DELETE CASCADE
    );

-- Stories table
CREATE TABLE
    IF NOT EXISTS stories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        media_path VARCHAR(255) NOT NULL,
        type ENUM ('image', 'video') DEFAULT 'image',
        created_at DATETIME NOT NULL,
        expires_at DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    );

-- Story likes table
CREATE TABLE
    IF NOT EXISTS story_likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        story_id INT NOT NULL,
        created_at DATETIME NOT NULL,
        UNIQUE KEY user_story_unique (user_id, story_id),
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (story_id) REFERENCES stories (id) ON DELETE CASCADE
    );

-- Story views table
CREATE TABLE
    IF NOT EXISTS story_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        story_id INT NOT NULL,
        created_at DATETIME NOT NULL,
        UNIQUE KEY user_story_view_unique (user_id, story_id),
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (story_id) REFERENCES stories (id) ON DELETE CASCADE
    );

-- Direct messages table
CREATE TABLE
    IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT 0,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (sender_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users (id) ON DELETE CASCADE
    );

-- Story replies table
CREATE TABLE
    IF NOT EXISTS story_replies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        story_id INT NOT NULL,
        reply TEXT NOT NULL,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (story_id) REFERENCES stories (id) ON DELETE CASCADE
    );

-- User settings table for dark mode preference
CREATE TABLE
    IF NOT EXISTS user_settings (
        user_id INT PRIMARY KEY,
        dark_mode BOOLEAN DEFAULT 0,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    );

-- Shared posts table
CREATE TABLE
    IF NOT EXISTS shared_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        post_id INT NOT NULL,
        shared_with_id INT NOT NULL,
        message TEXT,
        created_at DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
        FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE,
        FOREIGN KEY (shared_with_id) REFERENCES users (id) ON DELETE CASCADE
    );

-- Create admin user
INSERT INTO
    users (
        username,
        email,
        password,
        full_name,
        is_admin,
        created_at
    )
VALUES
    (
        'admin',
        'admin@monogram.com',
        '$2y$10$8mnOFLg78tJLIJQ.pF/C1eQm4KFn1XlP9OGcbVGCibUAjbLCqc.4G',
        'Admin User',
        1,
        NOW ()
    ) ON DUPLICATE KEY
UPDATE id = id;

-- Password is 'admin123' - for demo purposes only
-- Create some sample users
INSERT INTO
    users (
        username,
        email,
        password,
        full_name,
        bio,
        is_verified,
        created_at
    )
VALUES
    (
        'user1',
        'user1@example.com',
        '$2y$10$8mnOFLg78tJLIJQ.pF/C1eQm4KFn1XlP9OGcbVGCibUAjbLCqc.4G',
        'User One',
        'This is a sample bio for User One',
        1,
        NOW ()
    ),
    (
        'user2',
        'user2@example.com',
        '$2y$10$8mnOFLg78tJLIJQ.pF/C1eQm4KFn1XlP9OGcbVGCibUAjbLCqc.4G',
        'User Two',
        'This is a sample bio for User Two',
        0,
        NOW ()
    ),
    (
        'user3',
        'user3@example.com',
        '$2y$10$8mnOFLg78tJLIJQ.pF/C1eQm4KFn1XlP9OGcbVGCibUAjbLCqc.4G',
        'User Three',
        'This is a sample bio for User Three',
        1,
        NOW ()
    ) ON DUPLICATE KEY
UPDATE id = id;

-- Password is 'admin123' - for demo purposes only