<?php
session_start();
require_once('../config/database.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Pobierz dane zamówienia
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price 
    FROM cart c 
    JOIN products p ON c.product_id = p.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Oblicz sumę zamówienia
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Dane do Przelewy24
$merchantId = "YOUR_MERCHANT_ID"; // Zastąp swoim ID
$crc = "YOUR_CRC_KEY"; // Zastąp swoim kluczem CRC
$sessionId = uniqid();
$amount = $total * 100; // Kwota w groszach

// Zapisz zamówienie w bazie danych
try {
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("
        INSERT INTO orders (user_id, total_amount, status, session_id) 
        VALUES (?, ?, 'pending', ?)
    ");
    $stmt->execute([$user_id, $total, $sessionId]);
    $order_id = $pdo->lastInsertId();
    
    // Zapisz szczegóły zamówienia
    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }
    
    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Błąd podczas zapisywania zamówienia: " . $e->getMessage());
}

// Parametry do Przelewy24
$p24_params = array(
    'p24_merchant_id' => $merchantId,
    'p24_pos_id' => $merchantId,
    'p24_session_id' => $sessionId,
    'p24_amount' => $amount,
    'p24_currency' => 'PLN',
    'p24_description' => 'Zamówienie nr ' . $order_id,
    'p24_email' => $_SESSION['email'],
    'p24_client' => $_SESSION['username'],
    'p24_url_return' => 'https://twojsklep.pl/payment_success.php',
    'p24_url_status' => 'https://twojsklep.pl/payment_verify.php',
    'p24_api_version' => '3.2'
);

// Wygeneruj sumę kontrolną
$p24_params['p24_sign'] = hash('sha384', 
    $p24_params['p24_session_id'] . '|' . 
    $p24_params['p24_merchant_id'] . '|' . 
    $p24_params['p24_amount'] . '|' . 
    $p24_params['p24_currency'] . '|' . 
    $crc
);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Przekierowanie do płatności</title>
    <style>
        .payment-container {
            text-align: center;
            margin: 50px auto;
            max-width: 600px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>Trwa przekierowanie do płatności</h2>
        <div class="spinner"></div>
        <p>Proszę czekać, za chwilę nastąpi przekierowanie do systemu płatności...</p>
        <form id="payment_form" action="https://secure.przelewy24.pl/trnRequest" method="POST">
            <?php foreach ($p24_params as $key => $value): ?>
                <input type="hidden" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($value); ?>">
            <?php endforeach; ?>
        </form>
        <script>
            document.getElementById('payment_form').submit();
        </script>
    </div>
</body>
</html>