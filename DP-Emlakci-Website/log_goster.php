<?php
$log_dosya = "log.txt";

if (file_exists($log_dosya)) {
    echo "<pre>" . htmlspecialchars(file_get_contents($log_dosya)) . "</pre>";
} else {
    echo "Log dosyası bulunamadı.";
}
?>
