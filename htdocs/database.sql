CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_admin TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS plans (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan_data JSON NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS messages (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_text TEXT,
    file_path VARCHAR(255),
    message_type ENUM('TEXT', 'IMAGE') DEFAULT 'TEXT',
    is_read TINYINT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (receiver_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS checkins (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    waist DECIMAL(5,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS quiz_scores (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    score INT DEFAULT 0,
    difficulty VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS quiz_challenges (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    challenger_id INT NOT NULL,
    opponent_id INT NOT NULL,
    status ENUM('PENDING', 'ACTIVE', 'FINISHED') DEFAULT 'PENDING',
    questions JSON,
    challenger_score INT DEFAULT 0,
    opponent_score INT DEFAULT 0,
    challenger_ready BOOLEAN DEFAULT 0,
    opponent_ready BOOLEAN DEFAULT 0,
    challenger_step INT DEFAULT 0,
    opponent_step INT DEFAULT 0,
    winner_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (challenger_id) REFERENCES users(id),
    FOREIGN KEY (opponent_id) REFERENCES users(id)
);