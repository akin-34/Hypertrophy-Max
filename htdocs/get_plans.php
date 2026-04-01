<?php
header('Content-Type: application/json');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');

session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Please log in."]);
    exit;
}

require_once "config.php";

$user_id = $_SESSION["id"];
$sql = "SELECT plan_data, created_at FROM plans WHERE user_id = ? ORDER BY created_at DESC";

$plans = [];

if($stmt = mysqli_prepare($link, $sql)){
    if(!$stmt){
        error_log("[get_plans] Prepare failed: " . mysqli_error($link));
        http_response_code(500);
        echo json_encode(["error" => "Database error. Please try again later."]);
        mysqli_close($link);
        exit;
    }
    
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if(mysqli_stmt_execute($stmt)){
        mysqli_stmt_bind_result($stmt, $plan_data, $created_at);
        while(mysqli_stmt_fetch($stmt)){
            $decoded = json_decode($plan_data, true);
            if($decoded !== null){
                $plans[] = ["plan_data" => $decoded, "created_at" => $created_at];
            } else {
                error_log("[get_plans] Failed to decode plan_data for user_id: $user_id");
            }
        }
        echo json_encode($plans);
    } else {
        error_log("[get_plans] Execute failed: " . mysqli_error($link));
        http_response_code(500);
        echo json_encode(["error" => "Unable to fetch plans."]);
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("[get_plans] Prepare failed: " . mysqli_error($link));
    http_response_code(500);
    echo json_encode(["error" => "Database error. Please try again later."]);
}

mysqli_close($link);
?>