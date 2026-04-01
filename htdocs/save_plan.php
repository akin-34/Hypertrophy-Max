<?php
header('Content-Type: application/json');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');

session_start();

// If not logged in, return 401 JSON instead of redirect
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized. Please log in."]);
    exit;
}

require_once "config.php";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $data = json_decode(file_get_contents('php://input'), true);
    if(!$data || !isset($data['plan'])){
        http_response_code(400);
        echo json_encode(["error" => "Invalid request payload."]);
        exit;
    }

    // Validate plan structure
    if(!isset($data['plan']['data']) || !isset($data['plan']['data']['macros']) || !isset($data['plan']['data']['days'])){
        http_response_code(400);
        echo json_encode(["error" => "Invalid plan structure."]);
        exit;
    }
    
    // Encode plan data (no need for mysqli_real_escape_string with prepared statements)
    $plan_json = json_encode($data['plan']);
    if($plan_json === false){
        http_response_code(400);
        echo json_encode(["error" => "Invalid plan data format."]);
        exit;
    }
    
    $user_id = $_SESSION["id"];

    $sql = "INSERT INTO plans (user_id, plan_data) VALUES (?, ?)";

    if($stmt = mysqli_prepare($link, $sql)){
        mysqli_stmt_bind_param($stmt, "is", $user_id, $plan_json);

        if(mysqli_stmt_execute($stmt)){
            echo json_encode(["success" => "Plan saved successfully."]);
        } else{
            error_log("[save_plan] Insert failed: " . mysqli_error($link));
            http_response_code(500);
            echo json_encode(["error" => "Something went wrong. Please try again later."]);
        }
        mysqli_stmt_close($stmt);
    } else {
        error_log("[save_plan] Prepare failed: " . mysqli_error($link));
        http_response_code(500);
        echo json_encode(["error" => "Database error. Please try again later."]);
    }

    mysqli_close($link);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed. Use POST."]);
}
?>