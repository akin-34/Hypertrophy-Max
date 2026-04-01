<?php
header('Content-Type: application/json');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(["error" => "Yetkisiz erişim."]);
    exit;
}

require_once "config.php";

$sender_id = $_SESSION['id'];
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$message_text = isset($_POST['message_text']) ? trim($_POST['message_text']) : '';
$message_type = 'TEXT';
$file_path = null;

if ($receiver_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Alıcı belirtilmedi."]);
    exit;
}

// Handle Image Upload
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/uploads/chat/';
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0755, true);
    }
    
    // Create .htaccess in uploads to prevent script execution
    if (!file_exists($upload_dir . '.htaccess')) {
        file_put_contents($upload_dir . '.htaccess', "Options -Indexes\nphp_flag engine off\n<Files \"*.php\">\nOrder Deny,Allow\nDeny from all\n</Files>");
    }

    $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($file_ext, $allowed_exts)) {
        $file_name = uniqid('msg_') . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $file_path = 'uploads/chat/' . $file_name;
            $message_type = 'IMAGE';
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Resim yüklenemedi."]);
            exit;
        }
    } else {
        http_response_code(400);
        echo json_encode(["error" => "Geçersiz dosya formatı."]);
        exit;
    }
}

if ($message_type === 'TEXT' && empty($message_text)) {
    http_response_code(400);
    echo json_encode(["error" => "Mesaj boş olamaz."]);
    exit;
}

$sql = "INSERT INTO messages (sender_id, receiver_id, message_text, file_path, message_type) VALUES (?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "iisss", $sender_id, $receiver_id, $message_text, $file_path, $message_type);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => "Mesaj gönderildi."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Gönderim hatası."]);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Sistem hatası."]);
}
mysqli_close($link);
?>
