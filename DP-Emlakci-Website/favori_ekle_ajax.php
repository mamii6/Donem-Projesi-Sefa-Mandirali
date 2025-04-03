<?php
session_start();
require_once "db.php";
header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'action' => ''];

// Check if user is logged in
if (!isset($_SESSION["kullanici_id"])) {
    $response['message'] = 'login_required';
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ilan_id"])) {
    $kullanici_id = $_SESSION["kullanici_id"];
    $ilan_id = $_POST["ilan_id"];

    try {
        // Check if favorite already exists
        $stmt = $pdo->prepare("SELECT * FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
        $stmt->execute([$kullanici_id, $ilan_id]);

        if ($stmt->rowCount() == 0) {
            // Add to favorites
            $stmt = $pdo->prepare("INSERT INTO favoriler (kullanici_id, ilan_id, eklenme_tarihi) VALUES (?, ?, NOW())");
            if ($stmt->execute([$kullanici_id, $ilan_id])) {
                $response['success'] = true;
                $response['action'] = 'add';
                $response['message'] = 'İlan favorilere eklendi';
            } else {
                $response['message'] = 'Veritabanı hatası';
            }
        } else {
            // Remove from favorites
            $stmt = $pdo->prepare("DELETE FROM favoriler WHERE kullanici_id = ? AND ilan_id = ?");
            if ($stmt->execute([$kullanici_id, $ilan_id])) {
                $response['success'] = true;
                $response['action'] = 'remove';
                $response['message'] = 'İlan favorilerden kaldırıldı';
            } else {
                $response['message'] = 'Veritabanı hatası';
            }
        }
    } catch (PDOException $e) {
        $response['message'] = 'Veritabanı hatası: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Geçersiz istek';
}

echo json_encode($response);