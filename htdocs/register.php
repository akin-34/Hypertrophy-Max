<?php
// Return JSON for all responses and log PHP errors to a file instead of outputting them.
header('Content-Type: application/json');
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');

// Start session with secure cookie settings
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Unconditional hit log to confirm the script is executed for each request
// @file_put_contents(__DIR__ . '/register_hit.log', date('[Y-m-d H:i:s]') . " HIT from " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n", FILE_APPEND);

// Log raw input early when X-Debug header present
if(!empty($_SERVER['HTTP_X_DEBUG']) && $_SERVER['HTTP_X_DEBUG'] === '1'){
    file_put_contents(__DIR__ . '/register_debug.log', date('[Y-m-d H:i:s]') . " RAW_INPUT: " . file_get_contents('php://input') . "\n", FILE_APPEND);
}

require_once "config.php";

$username = $password = "";
$username_err = $password_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate JSON input
    if(!$data || !is_array($data)){
        http_response_code(400);
        echo json_encode(["error" => "Invalid request format."]);
        exit;
    }

    // Validate username
    if(empty(trim($data["username"] ?? ""))){
        $username_err = "Please enter a username.";
    } else {
        $username_trimmed = trim($data["username"]);
        // Username validation: 3-50 characters, alphanumeric and underscore only
        if(strlen($username_trimmed) < 3 || strlen($username_trimmed) > 50){
            $username_err = "Username must be between 3 and 50 characters.";
        } elseif(!preg_match('/^[a-zA-Z0-9_]+$/', $username_trimmed)){
            $username_err = "Username can only contain letters, numbers, and underscores.";
        } else {
            $sql = "SELECT id FROM users WHERE username = ?";
            
            if($stmt = mysqli_prepare($link, $sql)){
                if(!$stmt){
                    error_log("[register] Prepare failed for user check: " . mysqli_error($link));
                    $username_err = "Database error. Please try again later.";
                } else {
                    mysqli_stmt_bind_param($stmt, "s", $param_username);
                    $param_username = $username_trimmed;
                    
                    if(mysqli_stmt_execute($stmt)){
                        mysqli_stmt_store_result($stmt);
                        
                        if(mysqli_stmt_num_rows($stmt) == 1){
                            $username_err = "This username is already taken.";
                        } else{
                            $username = $username_trimmed;
                        }
                    } else{
                        error_log("[register] DB check execute failed for user check: " . mysqli_error($link));
                        $username_err = "Database error. Please try again later.";
                    }
                    mysqli_stmt_close($stmt);
                }
            } else {
                error_log("[register] Prepare failed for user check: " . mysqli_error($link));
                $username_err = "Database error. Please try again later.";
            }
        }
    }
    
    // Validate password
    if(empty(trim($data["password"] ?? ""))){
        $password_err = "Please enter a password.";     
    } else {
        $password = trim($data["password"]);
        // Password validation: minimum 6 characters
        if(strlen($password) < 6){
            $password_err = "Password must be at least 6 characters long.";
        } elseif(strlen($password) > 255){
            $password_err = "Password is too long.";
        }
    }
    
    if(empty($username_err) && empty($password_err)){
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            if(!$stmt){
                error_log("[register] Prepare failed for insert: " . mysqli_error($link));
                http_response_code(500);
                echo json_encode(["error" => "Database error. Please try again later."]);
                mysqli_close($link);
                exit;
            }
            
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);
            
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            if(mysqli_stmt_execute($stmt)){
                $new_id = mysqli_insert_id($link);
                // auto-login after registration
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $new_id;
                $_SESSION['username'] = $username;
                echo json_encode(["success" => "User registered successfully.", "username" => $username]);
            } else{
                error_log("[register] Insert execute failed: " . mysqli_error($link));
                http_response_code(500);
                echo json_encode(["error" => "Database error. Please try again later."]);
            }
            mysqli_stmt_close($stmt);
        } else {
            error_log("[register] Prepare failed for insert: " . mysqli_error($link));
            http_response_code(500);
            echo json_encode(["error" => "Database error. Please try again later."]);
        }
    } else {
        $errors = array_filter([$username_err, $password_err]);
        http_response_code(400);
        echo json_encode(["error" => implode(" ", $errors)]);
    }
    
    mysqli_close($link);
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed. Use POST."]);
}
?>