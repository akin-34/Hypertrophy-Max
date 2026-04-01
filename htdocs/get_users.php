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

$current_user_id = $_SESSION['id'];

// Fetch all users except current one + their unread message count
// A user is considered "Online" if active in the last 5 minutes
$sql = "SELECT u.id, u.username, u.last_activity, 
               (u.last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)) as is_online,
               (SELECT COUNT(m.id) FROM messages m WHERE m.sender_id = u.id AND m.receiver_id = ? AND m.is_read = 0) as unread_count
        FROM users u 
        WHERE u.id != ? 
        ORDER BY u.last_activity DESC";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $current_user_id, $current_user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = [
                "id" => $row['id'],
                "username" => $row['username'],
                "last_activity" => $row['last_activity'],
                "is_online" => (bool)$row['is_online'],
                "unread_count" => (int)$row['unread_count']
            ];
        }
        echo json_encode($users);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Fetch failed."]);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(["error" => "System error."]);
}
mysqli_close($link);
?>
