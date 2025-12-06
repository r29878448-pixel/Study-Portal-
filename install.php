<?php
$host = '127.0.0.1';
$user = 'root';
$pass = 'root';
$dbname = 'studyportal';

// Connect without database
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// Create tables - removed payment-related tables, added notes table
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    )",
    
    "CREATE TABLE IF NOT EXISTS banners (
        id INT AUTO_INCREMENT PRIMARY KEY,
        image VARCHAR(255) NOT NULL,
        link VARCHAR(255) DEFAULT ''
    )",
    
    "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS chapters (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS videos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chapter_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        filename VARCHAR(255) DEFAULT '',
        video_url VARCHAR(500) DEFAULT '',
        FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS notes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        chapter_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT,
        file_path VARCHAR(255) DEFAULT '',
        external_url VARCHAR(500) DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
    )",
    
    "CREATE TABLE IF NOT EXISTS imports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        source_url VARCHAR(500) NOT NULL,
        import_type ENUM('video', 'notes', 'course', 'playlist') DEFAULT 'video',
        status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
        course_id INT DEFAULT NULL,
        chapter_id INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        app_name VARCHAR(100) DEFAULT 'Study Portal',
        support_email VARCHAR(100) DEFAULT 'support@studyportal.com',
        support_phone VARCHAR(20) DEFAULT '+91 9876543210'
    )"
];

foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Insert default admin
$admin_check = $conn->query("SELECT id FROM admin LIMIT 1");
if ($admin_check->num_rows == 0) {
    $admin_pass = password_hash('123456', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO admin (username, password) VALUES ('admin', '$admin_pass')");
}

// Insert default settings
$settings_check = $conn->query("SELECT id FROM settings LIMIT 1");
if ($settings_check->num_rows == 0) {
    $conn->query("INSERT INTO settings (app_name, support_email, support_phone) VALUES ('Study Portal', 'support@studyportal.com', '+91 9876543210')");
}

// Create upload directories - added notes folder
$dirs = ['uploads/banners', 'uploads/courses', 'uploads/videos', 'uploads/notes'];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - Study Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 max-w-md w-full text-center">
        <div class="w-20 h-20 bg-green-500 text-white flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-check text-4xl"></i>
        </div>
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Installation Complete!</h1>
        <p class="text-gray-600 mb-6">Study Portal has been successfully installed.</p>
        <div class="bg-gray-50 p-4 text-left mb-6">
            <p class="text-sm text-gray-600 mb-2"><strong>Admin Login:</strong></p>
            <p class="text-sm text-gray-600">Username: <code class="bg-gray-200 px-2 py-1">admin</code></p>
            <p class="text-sm text-gray-600">Password: <code class="bg-gray-200 px-2 py-1">123456</code></p>
        </div>
        <div class="flex gap-4">
            <a href="index.php" class="flex-1 bg-sky-600 text-white py-3 font-semibold">User Panel</a>
            <a href="admin/login.php" class="flex-1 bg-gray-800 text-white py-3 font-semibold">Admin Panel</a>
        </div>
    </div>
</body>
</html>
