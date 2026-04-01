<?php
// Cerebras AI Proxy for Nutrition Plan (Alternate Location)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: https://assetexplorer.gt.tc');

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');

require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}

$client_data = json_decode(file_get_contents('php://input'), true);
if (!$client_data || !isset($client_data['userData'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid user data."]);
    exit;
}

$user_data = $client_data['userData'];

$system_prompt = <<<PROMPT
Sen profesyonel bir vücut geliştirme koçusun (Guray Training Protocol). 
Kayıtlı verilere dayanarak kullanıcı için 7 günlük beslenme planı ve makro hesaplamaları yap.
Yanıtı SADECE geçerli bir JSON formatında ver. 

JSON ŞEMASI:
{
  "macros": {"p": "GRAM", "c": "GRAM", "f": "GRAM", "cal": "KCAL"},
  "days": { 
     "Pazartesi": [{"meal": "Öğün", "time": "Saat", "foods": ["Besin 1"]}],
     ... (tüm günler)
  }
}
PROMPT;

$user_prompt = "Kullanıcı Verileri:\n" . json_encode($user_data, JSON_UNESCAPED_UNICODE);

// Cerebras API Configuration
$url = "https://api.cerebras.ai/v1/chat/completions";
$model = "llama3.1-8b"; 

$payload = [
    "model" => $model,
    "messages" => [
        ["role" => "system", "content" => $system_prompt],
        ["role" => "user", "content" => $user_prompt]
    ],
    "response_format" => ["type" => "json_object"],
    "temperature" => 0.7
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . CEREBRAS_API_KEY
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200 && $response) {
    $data = json_decode($response, true);
    $content = $data['choices'][0]['message']['content'] ?? null;
    
    if ($content) {
        echo $content;
    } else {
        http_response_code(500);
        echo json_encode(["error" => "AI yanıt üretemedi."]);
    }
} else {
    http_response_code($http_code ?: 500);
    $err = json_decode($response ?? '', true);
    echo json_encode([
        "error" => "AI Servis Hatası: " . ($err['error']['message'] ?? "Bağlantı hatası"),
        "debug_code" => $http_code
    ]);
}
?>
