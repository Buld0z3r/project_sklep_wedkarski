<?php
session_start();
require_once('../config/database.php');

if (!isset($_SESSION['order_id']) || !isset($_SESSION['order_total'])) {
    header('Location: cart.php');
    exit;
}

$order_id = $_SESSION['order_id'];
$total = $_SESSION['order_total'];

// Obsługa formularza płatności
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if ($payment_method) {
        try {
            // Aktualizuj status zamówienia
            $stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
            $stmt->execute([$order_id]);
            
            // Wyczyść koszyk
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            // Wyczyść dane zamówienia z sesji
            unset($_SESSION['order_id']);
            unset($_SESSION['order_total']);
            
            // Przekieruj do strony sukcesu
            header('Location: payment_success.php');
            exit;
        } catch (Exception $e) {
            $error = "Wystąpił błąd podczas przetwarzania płatności: " . $e->getMessage();
        }
    } else {
        $error = "Wybierz metodę płatności";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Płatność - Sklep Wędkarski</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .payment-amount {
            font-size: 24px;
            color: #007BFF;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }

        .payment-methods {
            display: grid;
            gap: 15px;
            margin: 20px 0;
        }

        .payment-method {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            border-color: #007BFF;
            background-color: #f8f9fa;
        }

        .payment-method input[type="radio"] {
            margin-right: 15px;
        }

        .payment-method img {
            height: 30px;
            margin-left: auto;
        }

        .submit-payment {
            width: 100%;
            padding: 12px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-payment:hover {
            background-color: #218838;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div id="container">
        <header>
            <div class="nav-buttons">
                <a href="../index.html">Strona główna</a>
                <a href="products.php">Produkty</a>
                <a href="cart.php">Koszyk</a>
            </div>
            <h1>Płatność</h1>
        </header>

        <main>
            <div class="payment-container">
                <div class="payment-header">
                    <h2>Wybierz metodę płatności</h2>
                </div>

                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div class="payment-amount">
                    Kwota do zapłaty: <?php echo number_format($total, 2); ?> zł
                </div>

                <form method="POST" action="">
                    <div class="payment-methods">
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="blik" required>
                            BLIK
                            <img src="../images/blik.png" alt="BLIK">
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="card" required>
                            Karta płatnicza
                            <div>
                                <img src="../images/visa.png" alt="Visa">
                                <img src="../images/mastercard.png" alt="Mastercard">
                            </div>
                        </label>
                        <label class="payment-method">
                            <input type="radio" name="payment_method" value="transfer" required>
                            Przelew online
                            <img src="../images/przelewy24.png" alt="Przelewy24">
                        </label>
                    </div>

                    <button type="submit" class="submit-payment">Zapłać</button>
                </form>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Sklep Wędkarski. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
</body>
</html>