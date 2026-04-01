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

    if(empty(trim($data["username"] ?? ""))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($data["username"]);
        // Basic username validation
        if(strlen($username) > 50){
            $username_err = "Invalid username format.";
        }
    }
    
    if(empty(trim($data["password"] ?? ""))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($data["password"]);
        // Basic password validation
        if(strlen($password) > 255){
            $password_err = "Invalid password format.";
        }
    }
    
    if(empty($username_err) && empty($password_err)){
        $sql = "SELECT id, username, password, is_admin FROM users WHERE username = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            if(!$stmt){
                error_log("[login] Prepare failed: " . mysqli_error($link));
                http_response_code(500);
                echo json_encode(["error" => "Database error. Please try again later."]);
                mysqli_close($link);
                exit;
            }
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);

                if(mysqli_stmt_num_rows($stmt) == 1){
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $is_admin);
                    if(mysqli_stmt_fetch($stmt)){
                        if(password_verify($password, $hashed_password)){
                            // session already started above
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["is_admin"] = (bool)$is_admin;

                            echo json_encode([
                                "success" => "Logged in successfully.", 
                                "username" => $username,
                                "is_admin" => (bool)$is_admin
                            ]);
                        } else{
                            echo json_encode(["error" => "The password you entered was not valid."]);
                        }
                    } else {
                        error_log("[login] fetch failed: " . mysqli_error($link));
                        echo json_encode(["error" => "Oops! Something went wrong. Please try again later."]);
                    }
                } else{
                    echo json_encode(["error" => "No account found with that username."]);
                }
            } else{
                error_log("[login] Statement execute failed: " . mysqli_error($link));
                echo json_encode(["error" => "Oops! Something went wrong. Please try again later."]);
            }
            mysqli_stmt_close($stmt);
        } else {
            error_log("[login] Prepare failed: " . mysqli_error($link));
            http_response_code(500);
            echo json_encode(["error" => "Database error. Please try again later."]);
        }
    } else {
        $errors = array_filter([$username_err, $password_err]);
        http_response_code(400);
        echo json_encode(["error" => implode(" ", $errors)]);
    }
    
    mysqli_close($link);
}
else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed. Use POST."]);
}
?>