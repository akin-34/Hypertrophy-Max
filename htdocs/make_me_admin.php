<?php
/**
 * Admin Yetkilendirme Scripti
 * Bu dosyayı çalıştırdığınızda şu anki oturumunuzdaki kullanıcıyı admin yapar.
 * İşlem bittikten sonra BU DOSYAYI SİLMEYİ UNUTMAYIN!
 */
session_start();
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    die("Hata: Önce sisteme giriş yapmalısınız.");
}

$id = $_SESSION["id"];
$username = $_SESSION["username"];

$sql = "UPDATE users SET is_admin = 1 WHERE id = $id";

if (mysqli_query($link, $sql)) {
    $_SESSION["is_admin"] = true;
    echo "<h1>Tebrikler, $username!</h1>";
    echo "<p>Artık Admin yetkisine sahipsiniz. <a href='/admin.php'>Admin Paneline Git</a></p>";
    echo "<p><strong>GÜVENLİK UYARISI:</strong> Lütfen <u>make_me_admin.php</u> dosyasını sunucunuzdan HEMEN silin.</p>";
} else {
    echo "Hata: " . mysqli_error($link);
}

mysqli_close($link);
?>
