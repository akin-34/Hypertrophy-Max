<?php
// Cerebras AI Proxy for Sports Quiz (Alternate Location)
header('Content-Type: application/json');

ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/php_errors.log');

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(["error" => "Yetkisiz erişim."]);
    exit;
}

require_once "config.php";

$system_prompt = <<<PROMPT
Sen bir spor uzmanısın. Kullanıcı için tam olarak 10 adet spor sorusu hazırla. 
Konular: Fitness, Vücut Geliştirme, Olimpiyatlar, Futbol, Basketbol ve Sporcu Beslenmesi.
ZORLUK SEVİYESİ KARŞIMI: 4xBASIT, 4xORTA, 2xZOR.

Yanıtı SADECE geçerli bir JSON formatında ver.
JSON ŞEMASI:
{
  "questions": [
    {
      "q": "Soru metni?",
      "o": ["Seçenek 1", "Seçenek 2", "Seçenek 3", "Seçenek 4"],
      "a": 0,
      "difficulty": "BASIT"
    }
  ]
}
* "a" alanı doğru seçeneğin index'idir (0-3 arası).
PROMPT;

// Cerebras API Configuration
$url = "https://api.cerebras.ai/v1/chat/completions";
$model = "llama3.1-8b"; 

$payload = [
    "model" => $model,
    "messages" => [
        ["role" => "system", "content" => $system_prompt]
    ],
    "response_format" => ["type" => "json_object"],
    "temperature" => 0.9
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . CEREBRAS_API_KEY
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
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
        echo json_encode(["error" => "AI soru üretemedi."]);
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
