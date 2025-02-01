<?php
session_start();
require_once('../config/database.php');
require_once('../config/p24_config.php');

// Odbierz dane od Przelewy24
$input = file_get_contents('php://input');
$notification = json_decode($input, true);

// Zapisz log z powiadomieniem
file_put_contents(
    __DIR__ . '/p24_notifications.log',
    date('Y-m-d H:i:s') . ' - ' . $input . PHP_EOL,
    FILE_APPEND
);

if ($notification) {
    // Przygotuj dane do weryfikacji
    $verify_data = array(
        'merchantId' => P24_MERCHANT_ID,
        'posId' => P24_POS_ID,
        'sessionId' => $notification['sessionId'],
        'amount' => $notification['amount'],
        'currency' => $notification['currency'],
        'orderId' => $notification['orderId']
    );

    // Oblicz sumę kontrolną
    $sign = hash('sha384', 
        json_encode($verify_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . P24_CRC
    );
    $verify_data['sign'] = $sign;

    // Wyślij żądanie weryfikacji
    $ch = curl_init(P24_VERIFY_URL);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($verify_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode(P24_MERCHANT_ID . ':' . P24_API_KEY)
    ));

    $response = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_status === 200) {
        $result = json_decode($response, true);
        
        if (isset($result['data']['status']) && $result['data']['status'] === 'success') {
            try {
                // Aktualizuj status zamówienia
                $stmt = $pdo->prepare("
                    UPDATE orders 
                    SET status = 'completed', 
                        payment_id = ?, 
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE session_id = ?
                ");
                $stmt->execute([$notification['orderId'], $notification['sessionId']]);
                
                // Wyczyść koszyk użytkownika
                $stmt = $pdo->prepare("
                    DELETE FROM cart 
                    WHERE user_id = (
                        SELECT user_id 
                        FROM orders 
                        WHERE session_id = ?
                    )
                ");
                $stmt->execute([$notification['sessionId']]);
                
                http_response_code(200);
                echo json_encode(['status' => 'success']);
                exit;
            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => $e->getMessage()]);
                exit;
            }
        }
    }
}

http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid verification']);
?>