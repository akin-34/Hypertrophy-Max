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
$sql = "SELECT weight, waist, created_at FROM checkins WHERE user_id = ? ORDER BY created_at DESC";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $checkins = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $checkins[] = $row;
        }
        echo json_encode($checkins);
    } else {
        echo json_encode(["error" => "Veri çekme hatası."]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["error" => "Sistem hatası."]);
}
mysqli_close($link);
