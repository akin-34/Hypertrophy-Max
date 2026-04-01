<?php
header('Content-Type: application/json');
require_once "config.php";
session_start();

// Security check: Only admins allowed
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] !== true) {
    http_response_code(403);
    echo json_encode(["error" => "Yetkisiz erişim. Admin yetkisi gerekli."]);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'stats':
        $stats = [];
        
        // Total Users
        $res = mysqli_query($link, "SELECT COUNT(*) as total FROM users");
        $stats['total_users'] = mysqli_fetch_assoc($res)['total'];
        
        // Online Users (last 5 mins)
        $res = mysqli_query($link, "SELECT COUNT(*) as total FROM users WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
        $stats['online_users'] = mysqli_fetch_assoc($res)['total'];
        
        // Total Plans
        $res = mysqli_query($link, "SELECT COUNT(*) as total FROM plans");
        $stats['total_plans'] = mysqli_fetch_assoc($res)['total'];
        
        // Total Messages
        $res = mysqli_query($link, "SELECT COUNT(*) as total FROM messages");
        $stats['total_messages'] = mysqli_fetch_assoc($res)['total'];

        echo json_encode($stats);
        break;

    case 'list_users':
        $sql = "SELECT id, username, created_at, last_activity, is_admin FROM users ORDER BY created_at DESC";
        $result = mysqli_query($link, $sql);
        $users = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = [
                "id" => (int)$row['id'],
                "username" => $row['username'],
                "created_at" => $row['created_at'],
                "last_activity" => $row['last_activity'],
                "is_admin" => (bool)$row['is_admin'],
                "is_online" => (strtotime($row['last_activity']) > strtotime('-5 minutes'))
            ];
        }
        echo json_encode($users);
        break;

    case 'delete_user':
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = (int)($data['user_id'] ?? 0);

        if ($user_id <= 0) {
            echo json_encode(["error" => "Geçersiz kullanıcı ID."]);
            exit;
        }

        if ($user_id === (int)$_SESSION['id']) {
            echo json_encode(["error" => "Kendi hesabınızı silemezsiniz."]);
            exit;
        }

        // Transaction for clean deletion
        mysqli_begin_transaction($link);
        try {
            mysqli_query($link, "DELETE FROM plans WHERE user_id = $user_id");
            mysqli_query($link, "DELETE FROM checkins WHERE user_id = $user_id");
            mysqli_query($link, "DELETE FROM quiz_scores WHERE user_id = $user_id");
            mysqli_query($link, "DELETE FROM messages WHERE sender_id = $user_id OR receiver_id = $user_id");
            mysqli_query($link, "DELETE FROM quiz_challenges WHERE challenger_id = $user_id OR opponent_id = $user_id");
            mysqli_query($link, "DELETE FROM users WHERE id = $user_id");
            
            mysqli_commit($link);
            echo json_encode(["success" => "Kullanıcı ve tüm verileri başarıyla silindi."]);
        } catch (Exception $e) {
            mysqli_rollback($link);
            echo json_encode(["error" => "Silme işlemi sırasında hata oluştu: " . $e->getMessage()]);
        }
        break;

    case 'make_admin':
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }
        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = (int)($data['user_id'] ?? 0);
        $status = (int)($data['status'] ?? 0);

        $sql = "UPDATE users SET is_admin = $status WHERE id = $user_id";
        if (mysqli_query($link, $sql)) {
            echo json_encode(["success" => "Kullanıcı yetkisi güncellendi."]);
        } else {
            echo json_encode(["error" => "Hata: " . mysqli_error($link)]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(["error" => "Geçersiz işlem."]);
        break;
}

mysqli_close($link);
?>
