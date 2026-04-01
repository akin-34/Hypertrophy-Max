<?php
// Simple token-protected log viewer for deploy/logs
// Usage: https://yourdomain/deploy/view_logs.php?token=THE_TOKEN

$tokenFile = __DIR__ . '/log_token.txt';
$logsDir = __DIR__ . '/logs';
$allowed = false;
$token = file_exists($tokenFile) ? trim(file_get_contents($tokenFile)) : 'SET_A_TOKEN_IN_LOG_TOKEN_TXT';

if(isset($_GET['token']) && $_GET['token'] === $token){
    $allowed = true;
}

function readLog($path){
    if(!file_exists($path)) return "[Dosya bulunamadı: " . basename($path) . "]";
    $content = file_get_contents($path);
    return htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // allow clearing logs with POST and valid token
    $postToken = $_POST['token'] ?? '';
    $action = $_POST['action'] ?? '';
    if($postToken === $token && $action === 'clear'){
        foreach(['register_hit.log','register_debug.log','php_errors.log'] as $f){
            $p = $logsDir . '/' . $f;
            if(file_exists($p)) @file_put_contents($p, "");
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

if(!$allowed){
    // show simple token entry form
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Log Viewer - Giriş</title></head><body style="background:#0b0b0b;color:#fff;font-family:Arial;padding:20px">';
    echo '<h2>Log Viewer</h2>';
    echo '<p>Görmek için token girin.</p>';
    echo '<form method="get"><input name="token" style="width:320px;padding:8px" placeholder="token" /> <button type="submit">Göster</button></form>';
    echo '</body></html>';
    exit;
}

// Allowed - display logs
header('Content-Type: text/html; charset=utf-8');
echo '<!doctype html><html><head><meta charset="utf-8"><title>Log Viewer</title></head><body style="background:#0b0b0b;color:#fff;font-family:Arial;padding:20px">';
echo '<h1>Deploy Logs</h1>';
echo '<p><a href="/">Ana sayfa</a> — Token doğrulandı.</p>';

foreach(['php_errors.log', 'logs/register_hit.log', 'logs/register_debug.log'] as $file){
    $path = __DIR__ . '/' . $file;
    echo '<h2 style="margin-top:18px">' . htmlspecialchars($file) . '</h2>';
    echo '<pre style="background:#111;padding:12px;border-radius:6px;max-height:360px;overflow:auto;color:#ddd">' . readLog($path) . '</pre>';
}

// Clear logs form
echo '<form method="post" style="margin-top:18px">';
echo '<input type="hidden" name="token" value="' . htmlspecialchars($token) . '" />';
echo '<input type="hidden" name="action" value="clear" />';
echo '<button type="submit" style="padding:8px 12px;border-radius:6px">Tüm Logları Temizle</button>';
echo '</form>';

echo '<p style="margin-top:18px;color:#888">Not: İşiniz bittikten sonra bu dosyayı silmeniz güvenlik açısından önerilir.</p>';
echo '</body></html>';

?>
