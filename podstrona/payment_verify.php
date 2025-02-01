<?php
session_start();
require_once('../config/database.php');

// Odbierz dane od Przelewy24
$merchantId = "YOUR_MERCHANT_ID"; // Zastąp swoim ID
$crc = "YOUR_CRC_KEY"; // Zastąp swoim kluczem CRC

$received_data = file_get_contents('php://input');
$payment_data = json_decode($received_data, true);

if ($payment_data) {
    // Weryfikuj podpis
    $computed_sign = hash('sha384', 
        $payment_data['p24_session_id'] . '|' . 
        $payment_data['p24_order_id'] . '|' . 
        $payment_data['p24_amount'] . '|' . 
        $payment_data['p24_currency'] . '|' . 
        $crc
    );

    if ($computed_sign === $payment_data['p24_sign']) {
        // Aktualizuj status zamówienia
        try {
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET status = 'completed', 
                    payment_id = ?, 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE session_id = ?
            ");
            $stmt->execute([$payment_data['p24_order_id'], $payment_data['p24_session_id']]);
            
            // Wyczyść koszyk użytkownika
            $stmt = $pdo->prepare("
                DELETE FROM cart 
                WHERE user_id = (
                    SELECT user_id 
                    FROM orders 
                    WHERE session_id = ?
                )
            ");
            $stmt->execute([$payment_data['p24_session_id']]);
            
            http_response_code(200);
            echo "OK";
        } catch (Exception $e) {
            http_response_code(500);
            error_log("Błąd weryfikacji płatności: " . $e->getMessage());
            echo "ERROR";
        }
    } else {
        http_response_code(400);
        echo "INVALID SIGNATURE";
    }
} else {
    http_response_code(400);
    echo "INVALID DATA";
}
?>