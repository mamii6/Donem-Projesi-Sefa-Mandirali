<?php
$log_dosya = "log.txt";
file_put_contents($log_dosya, "Log kaydı başlatıldı.\n", FILE_APPEND);
echo "Log dosyası oluşturuldu.";
?>
