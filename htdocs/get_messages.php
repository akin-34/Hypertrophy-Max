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

$user_id = $_SESSION['id'];
$other_id = isset($_GET['other_id']) ? intval($_GET['other_id']) : 0;

if ($other_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Geçersiz kullanıcı."]);
    exit;
}

// Fetch messages and mark as read
$sql = "SELECT id, sender_id, receiver_id, message_text, file_path, message_type, is_read, created_at 
        FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
        ORDER BY created_at ASC 
        LIMIT 100";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "iiii", $user_id, $other_id, $other_id, $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $messages = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
        
        // Mark as read
        $upd = "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0";
        if ($upd_stmt = mysqli_prepare($link, $upd)) {
            mysqli_stmt_bind_param($upd_stmt, "ii", $other_id, $user_id);
            mysqli_stmt_execute($upd_stmt);
            mysqli_stmt_close($upd_stmt);
        }
        
        echo json_encode($messages);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Fecth failed."]);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(["error" => "System error."]);
}
mysqli_close($link);
?>
