<?php
header('Content-Type: application/json');
ini_set('display_errors','0');
ini_set('log_errors','1');

ini_set('error_log', __DIR__ . '/php_errors.log');

session_start();
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    echo json_encode([
        "username" => $_SESSION["username"],
        "id" => $_SESSION["id"],
        "is_admin" => $_SESSION["is_admin"] ?? false
    ]);
} else {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
}
?>