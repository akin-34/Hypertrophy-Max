<?php
// Strategic Nutrition V1 (Programim) - Veritabanı & AI Şablonu
// Bu dosyayı config.php yaparak kendi bilgilerinizi girin.

date_default_timezone_set('Europe/Istanbul');

// Veritabanı Bilgileri (Çevresel değişkenler veya direkt değerler)
define('DB_SERVER',   getenv('DB_SERVER')   ?: 'YOUR_DB_SERVER');
define('DB_USERNAME', getenv('DB_USERNAME') ?: 'YOUR_DB_USERNAME');
define('DB_PASSWORD', getenv('DB_PASSWORD') ?: 'YOUR_DB_PASSWORD');
define('DB_NAME',     getenv('DB_NAME')     ?: 'YOUR_DB_NAME');
define('DB_PORT',     getenv('DB_PORT')     ?: 3306);

// Cerebras AI API Anahtarı (cloud.cerebras.ai üzerinden alabilirsiniz)
define('CEREBRAS_API_KEY', getenv('CEREBRAS_API_KEY') ?: 'YOUR_CEREBRAS_API_KEY_HERE');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

if($link === false){
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

mysqli_set_charset($link, 'utf8mb4');
mysqli_query($link, "SET time_zone = '+03:00'");
?>
