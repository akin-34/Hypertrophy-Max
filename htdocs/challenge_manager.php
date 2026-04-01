<?php
// Challenge Manager: Invite, Accept, Sync Challenges
header('Content-Type: application/json');

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    exit;
}

require_once "config.php";

$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = $_SESSION['id'];

switch ($action) {
    case 'invite':
        $opponent_id = intval($_POST['opponent_id']);
        
        // Instead of blocking, just delete any OLD pending invites from this challenger to ANYONE
        // to ensure we only have one active invitation at a time.
        mysqli_query($link, "DELETE FROM quiz_challenges WHERE challenger_id = $user_id AND status = 'PENDING'");

        $questions = $_POST['questions'] ?? null; 
        
        $sql = "INSERT INTO quiz_challenges (challenger_id, opponent_id, questions, status) VALUES (?, ?, ?, 'PENDING')";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "iis", $user_id, $opponent_id, $questions);
        if(mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "challenge_id" => mysqli_insert_id($link)]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database insert failed: " . mysqli_error($link)]);
        }
        mysqli_stmt_close($stmt);
        break;

    case 'get_questions':
        $challenge_id = intval($_GET['challenge_id']);
        $sql = "SELECT questions FROM quiz_challenges WHERE id = $challenge_id AND (challenger_id = $user_id OR opponent_id = $user_id)";
        $res = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($res);
        if($row && !empty($row['questions'])) {
            echo $row['questions'];
        } else {
            echo json_encode(["error" => "Access denied, not found, or questions empty", "debug_id" => $challenge_id, "debug_user" => $user_id]);
        }
        break;

    case 'check_invites':
        // Auto-cleanup: Delete pending invitations older than 5 minutes (increased from 30s)
        mysqli_query($link, "DELETE FROM quiz_challenges WHERE status = 'PENDING' AND created_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");

        // Only return an invite if the user isn't ALREADY in an ACTIVE challenge created in the last 2 minutes (reduced from 10m)
        $activeCheck = mysqli_query($link, "SELECT id FROM quiz_challenges WHERE (challenger_id = $user_id OR opponent_id = $user_id) AND status = 'ACTIVE' AND created_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE) LIMIT 1");
        if(mysqli_num_rows($activeCheck) > 0) {
            echo json_encode(["none" => true, "status" => "engaged"]);
            break;
        }

        // Only return an invite if the user isn't ALREADY in an active/finished challenge they haven't seen yet
        $sql = "SELECT c.id, u.username as challenger_name FROM quiz_challenges c 
                JOIN users u ON c.challenger_id = u.id 
                WHERE c.opponent_id = $user_id AND c.status = 'PENDING' 
                ORDER BY c.created_at DESC LIMIT 1";
        $res = mysqli_query($link, $sql);
        $row = mysqli_fetch_assoc($res);
        echo json_encode($row ?: ["none" => true]);
        break;

    case 'respond':
        $challenge_id = intval($_POST['challenge_id']);
        $accept = $_POST['accept'] === '1' ? 'ACTIVE' : 'FINISHED'; 
        $questions = $_POST['questions'] ?? null;
        
        if ($questions) {
            $sql = "UPDATE quiz_challenges SET status = ?, questions = ?, opponent_ready = 1 WHERE id = ? AND opponent_id = ?";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "ssii", $accept, $questions, $challenge_id, $user_id);
        } else {
            $sql = "UPDATE quiz_challenges SET status = ?, opponent_ready = 1 WHERE id = ? AND opponent_id = ?";
            $stmt = mysqli_prepare($link, $sql);
            mysqli_stmt_bind_param($stmt, "sii", $accept, $challenge_id, $user_id);
        }
        
        mysqli_stmt_execute($stmt);
        echo json_encode(["success" => true]);
        break;

    case 'submit_score':
        $challenge_id = intval($_POST['challenge_id']);
        $score = intval($_POST['score']);
        $step = isset($_POST['step']) ? intval($_POST['step']) : 0;
        
        $sql = "SELECT challenger_id, challenger_score, opponent_score, challenger_step, opponent_step, status FROM quiz_challenges WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $challenge_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $cid, $c_score, $o_score, $c_step, $o_step, $status);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        $is_challenger = ($cid == $user_id);
        
        // Determine new status based on steps and existing status
        $final_step = 10;
        $will_finish = ($status === 'FINISHED');
        
        if ($is_challenger) {
            if ($o_step >= $final_step || $step >= $final_step) $will_finish = true;
            $sql = "UPDATE quiz_challenges SET challenger_score = ?, challenger_step = ?, status = ? WHERE id = ?";
        } else {
            if ($c_step >= $final_step || $step >= $final_step) $will_finish = true;
            $sql = "UPDATE quiz_challenges SET opponent_score = ?, opponent_step = ?, status = ? WHERE id = ?";
        }
        
        $new_status = $will_finish ? 'FINISHED' : $status;
        
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "iisi", $score, $step, $new_status, $challenge_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo json_encode(["success" => true, "status" => $new_status]);
        break;

    case 'mark_ready':
        $challenge_id = intval($_POST['challenge_id']);
        $sql = "SELECT challenger_id FROM quiz_challenges WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $challenge_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $cid);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        if($cid == $user_id) {
            $sql = "UPDATE quiz_challenges SET challenger_ready = 1 WHERE id = ?";
        } else {
            $sql = "UPDATE quiz_challenges SET opponent_ready = 1 WHERE id = ?";
        }
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $challenge_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        echo json_encode(["success" => true]);
        break;

    case 'sync_duel':
        $challenge_id = intval($_GET['challenge_id']);
        $score = intval($_GET['score']);
        $step = intval($_GET['step']);
        
        $sql = "SELECT challenger_id FROM quiz_challenges WHERE id = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "i", $challenge_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $cid);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        if($cid == $user_id) {
            $sql = "UPDATE quiz_challenges SET challenger_score = ?, challenger_step = ? WHERE id = ?";
        } else {
            $sql = "UPDATE quiz_challenges SET opponent_score = ?, opponent_step = ? WHERE id = ?";
        }
        $ustmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($ustmt, "iii", $score, $step, $challenge_id);
        mysqli_stmt_execute($ustmt);
        mysqli_stmt_close($ustmt);
        
        // Fetch opponent's status with mysqli_query instead of get_result for compatibility
        $res = mysqli_query($link, "SELECT * FROM quiz_challenges WHERE id = $challenge_id");
        $data = mysqli_fetch_assoc($res);
        
        echo json_encode($data);
        break;

    case 'get_result':
        $challenge_id = intval($_GET['challenge_id']);
        $sql = "SELECT c.*, u1.username as challenger_name, u2.username as opponent_name 
                FROM quiz_challenges c
                JOIN users u1 ON c.challenger_id = u1.id
                JOIN users u2 ON c.opponent_id = u2.id
                WHERE c.id = $challenge_id";
        $res = mysqli_query($link, $sql);
        echo json_encode(mysqli_fetch_assoc($res) ?: ["error" => "Challenge not found"]);
        break;
}

mysqli_close($link);
?>
