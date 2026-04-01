<?php
/**
 * Database Setup Script
 * Çalıştır: https://assetexplorer.gt.tc/setup.php
 * Bu script tabloları otomatik oluşturur
 * 
 * GÜVENLİK UYARISI: Production'da bu dosyayı korumalı tutun veya silin!
 */

// Basit güvenlik kontrolü - sadece belirli domain'den erişime izin ver
$allowed_hosts = ['programim.gt.tc', 'www.programim.gt.tc', 'assetexplorer.gt.tc', 'www.assetexplorer.gt.tc'];
$current_host = $_SERVER['HTTP_HOST'] ?? '';
if (!in_array($current_host, $allowed_hosts) && !in_array($_SERVER['SERVER_NAME'] ?? '', $allowed_hosts)) {
    // Localhost'ta çalıştırıyorsanız bu kontrolü atlayabilirsiniz
    if ($current_host !== 'localhost' && $current_host !== '127.0.0.1') {
        die('Access denied. This script can only be run from the authorized domain.');
    }
}

// Use config.php for database connection
require_once "config.php";

if(!$link){
    die("Connection failed: " . mysqli_connect_error());
}

mysqli_set_charset($link, 'utf8mb4');

echo "<h1>AssetAgent Database Setup</h1>";
echo "<p>Setting up database tables...</p>";

// Set connection charset again to be sure
mysqli_query($link, "SET NAMES 'utf8mb4'");
mysqli_query($link, "SET CHARACTER SET utf8mb4");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if(mysqli_query($link, $sql)){
    echo "<p>✅ <strong>users</strong> table created or already exists.</p>";
} else {
    echo "<p>❌ Error creating users table: " . mysqli_error($link) . "</p>";
}

// Check if last_activity column exists, if not add it
$checkColumn = mysqli_query($link, "SHOW COLUMNS FROM users LIKE 'last_activity'");
if (mysqli_num_rows($checkColumn) == 0) {
    if (mysqli_query($link, "ALTER TABLE users ADD COLUMN last_activity DATETIME DEFAULT CURRENT_TIMESTAMP")) {
        echo "<p>✅ Added <strong>last_activity</strong> column to users table.</p>";
    } else {
        echo "<p>❌ Error adding last_activity column: " . mysqli_error($link) . "</p>";
    }
}

// Check if is_admin column exists, if not add it
$checkAdmin = mysqli_query($link, "SHOW COLUMNS FROM users LIKE 'is_admin'");
if (mysqli_num_rows($checkAdmin) == 0) {
    if (mysqli_query($link, "ALTER TABLE users ADD COLUMN is_admin TINYINT(1) DEFAULT 0")) {
        echo "<p>✅ Added <strong>is_admin</strong> column to users table.</p>";
    } else {
        echo "<p>❌ Error adding is_admin column: " . mysqli_error($link) . "</p>";
    }
}

// Create plans table
$sql = "CREATE TABLE IF NOT EXISTS plans (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan_data JSON NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if(mysqli_query($link, $sql)){
    echo "<p>✅ <strong>plans</strong> table created or already exists.</p>";
} else {
    echo "<p>❌ Error creating plans table: " . mysqli_error($link) . "</p>";
}

// Create checkins table
$sql = "CREATE TABLE IF NOT EXISTS checkins (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    weight DECIMAL(5,2) NOT NULL,
    waist DECIMAL(5,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if(mysqli_query($link, $sql)){
    echo "<p>✅ <strong>checkins</strong> table created or already exists.</p>";
} else {
    echo "<p>❌ Error creating checkins table: " . mysqli_error($link) . "</p>";
}

// Create messages table
$sql = "CREATE TABLE IF NOT EXISTS messages (
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
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if(mysqli_query($link, $sql)){
    echo "<p>✅ <strong>messages</strong> table created or already exists.</p>";
} else {
    echo "<p>❌ Error creating messages table: " . mysqli_error($link) . "</p>";
}

// Create quiz_scores table
$sql = "CREATE TABLE IF NOT EXISTS quiz_scores (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    score INT DEFAULT 0,
    difficulty VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if(mysqli_query($link, $sql)){
    echo "<p>✅ <strong>quiz_scores</strong> table created or already exists.</p>";
} else {
    echo "<p>❌ Error creating quiz_scores table: " . mysqli_error($link) . "</p>";
}

// Create quiz_challenges table
$sql = "CREATE TABLE IF NOT EXISTS quiz_challenges (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    challenger_id INT NOT NULL,
    opponent_id INT NOT NULL,
    status ENUM('PENDING', 'ACTIVE', 'FINISHED') DEFAULT 'PENDING',
    questions LONGTEXT,
    challenger_score INT DEFAULT 0,
    opponent_score INT DEFAULT 0,
    winner_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (challenger_id) REFERENCES users(id),
    FOREIGN KEY (opponent_id) REFERENCES users(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

if(mysqli_query($link, $sql)){
    echo "<p>✅ <strong>quiz_challenges</strong> table created or already exists.</p>";
    // Ensure LONGTEXT for stability
    mysqli_query($link, "ALTER TABLE quiz_challenges MODIFY COLUMN questions LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
} else {
    echo "<p>❌ Error creating quiz_challenges table: " . mysqli_error($link) . "</p>";
}

// Add Arena v2 sync columns if missing
$cols = [
    'challenger_ready' => "BOOLEAN DEFAULT 0",
    'opponent_ready' => "BOOLEAN DEFAULT 0",
    'challenger_step' => "INT DEFAULT 0",
    'opponent_step' => "INT DEFAULT 0"
];

foreach ($cols as $col => $def) {
    if (!mysqli_num_rows(mysqli_query($link, "SHOW COLUMNS FROM quiz_challenges LIKE '$col'"))) {
        mysqli_query($link, "ALTER TABLE quiz_challenges ADD $col $def");
    }
}

// --- GLOBAL COLLATIONS FIX ---
echo "<h3>Character Encoding Fix (utf8mb4)...</h3>";
$tables = ['users', 'plans', 'checkins', 'messages', 'quiz_scores', 'quiz_challenges'];
mysqli_query($link, "ALTER DATABASE " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
foreach($tables as $t) {
    if(mysqli_query($link, "ALTER TABLE $t CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        echo "<p>✅ Converted table <strong>$t</strong> to utf8mb4.</p>";
    } else {
        echo "<p>❌ Failed to convert table <strong>$t</strong>: " . mysqli_error($link) . "</p>";
    }
}

// Verify tables exist
$result = mysqli_query($link, "SHOW TABLES");
echo "<h3>Existing Tables:</h3><ul>";
while($row = mysqli_fetch_row($result)){
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";

mysqli_close($link);

echo "<h3>Setup Complete! ✅</h3>";
echo "<p><strong>NOT:</strong> Türkçe karakterlerin düzelmesi için bu script'i çalıştırdıktan sonra yeni bir Arena düellosu başlatmanız önerilir.</p>";
echo "<p><a href='/'>Back to Home</a></p>";
?>
