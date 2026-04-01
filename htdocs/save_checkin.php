<?php
header('Content-Type: application/json');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(["error" => "Yetkisiz erişim. Lütfen giriş yapın."]);
    exit;
}

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);
    
    
    $weight = isset($data['weight']) ? floatval($data['weight']) : null;
    $waist = isset($data['waist']) ? floatval($data['waist']) : null;
    $user_id = $_SESSION['id'];

    if (!$weight || !$waist || $weight <= 0 || $waist <= 0) {
        http_response_code(400);
        echo json_encode(["error" => "Kilo ve bel ölçüsü geçerli pozitif değerler olmalıdır."]);
        exit;
    }

    $sql = "INSERT INTO checkins (user_id, weight, waist) VALUES (?, ?, ?)";
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "idd", $user_id, $weight, $waist);
        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => "Check-in başarıyla kaydedildi."]);
        } else {
            http_response_code(500);
            $mysqli_error = mysqli_error($link);
            error_log("save_checkin.php - MySQL Error: " . $mysqli_error);
            echo json_encode(["error" => "Kaydetme hatası. Lütfen tekrar deneyin."]);
        }
        mysqli_stmt_close($stmt);
    } else {
        http_response_code(500);
        $prepare_error = mysqli_error($link);
        error_log("save_checkin.php - Prepare Error: " . $prepare_error);
        echo json_encode(["error" => "Sistem hatası. Veritabanı bağlantısı kontrol ediliyor."]);
    }
    mysqli_close($link);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Geçersiz istek metodu. POST kullanın."]);
}
