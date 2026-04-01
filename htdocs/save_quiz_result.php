<?php
// Save Quiz Result
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

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['id'];
$score = isset($data['score']) ? intval($data['score']) : 0;
$difficulty = isset($data['difficulty']) ? $data['difficulty'] : 'ORTA';

if ($score < 0) {
    http_response_code(400);
    echo json_encode(["error" => "Geçersiz skor."]);
    exit;
}

$sql = "INSERT INTO quiz_scores (user_id, score, difficulty) VALUES (?, ?, ?)";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "iis", $user_id, $score, $difficulty);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true, "message" => "Skor kaydedildi."]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Kaydedilemedi."]);
    }
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Sistem hatası."]);
}
mysqli_close($link);
?>
