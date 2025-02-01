<?php
session_start();
require_once('../config/database.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

function getImagePath($product) {
    $category = strtolower($product['category']);
    $name = strtolower($product['name']);
    
    // Mapowanie nazw produktów na nazwy plików
    $imageMap = [
        'wędka spinningowa dragon' => 'wedka_spinning.jpg',
        'wędka karpiowa premium' => 'wedka_karp.jpg',
        'wędka feederowa shimano' => 'wedka_feeder.jpg',
        'wędka muchowa sage' => 'wedka_muchowa.jpg',
        'wobler rapala' => 'wobler1.jpg',
        'zestaw woblerów premium' => 'wobler_set.jpg',
        'błystka obrotowa mepps' => 'blystka.jpg',
        'przynęty gumowe dragon' => 'gumy.jpg',
        'kołowrotek shimano stradic' => 'kolowrotek_shimano.jpg',
        'kołowrotek penn battle' => 'kolowrotek_penn.jpg',
        'kołowrotek dragon mega baits' => 'kolowrotek_dragon.jpg',
        'kołowrotek daiwa ninja' => 'kolowrotek_daiwa.jpg'
    ];
    
    $fileName = $imageMap[strtolower($name)] ?? 'placeholder.jpg';
    return "../images/products/$fileName";
}

try {
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, p.*, c.quantity 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Błąd bazy danych: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koszyk - Sklep Wędkarski</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .cart-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 20px;
            margin: 20px;
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 2fr 1fr 1fr 1fr auto;
            gap: 20px;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        .item-details h3 {
            margin: 0;
            color: #333;
        }

        .item-price {
            color: #007BFF;
            font-weight: bold;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-button {
            background-color: #e9ecef;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }

        .quantity-button:hover {
            background-color: #dee2e6;
        }

        .remove-item {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .remove-item:hover {
            background-color: #c82333;
        }

        .cart-summary {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: right;
        }

        .total-price {
            font-size: 1.5em;
            color: #007BFF;
            font-weight: bold;
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .payment-methods {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin: 20px 0;
        }

        .payment-method {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            background-color: white;
            transition: all 0.3s ease;
        }

        .payment-method:hover {
            transform: translateY(-2px);
            border-color: #007BFF;
        }

        .payment-method img {
            height: 40px;
            object-fit: contain;
        }

        .checkout-button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .checkout-button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div id="container">
        <header>
            <div class="nav-buttons">
                <a href="../index.html">Strona główna</a>
                <a href="products.php">Produkty</a>
                <a href="logout.php">Wyloguj</a>
            </div>
            <h1>Twój Koszyk</h1>
        </header>

        <main>
            <div class="cart-container">
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo $error; ?></div>
                <?php elseif (empty($cart_items)): ?>
                    <div class="empty-cart">
                        <h2>Twój koszyk jest pusty</h2>
                        <p>Przejdź do <a href="products.php">sklepu</a> aby dodać produkty do koszyka.</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $total = 0;
                    foreach ($cart_items as $item): 
                        $item_total = $item['price'] * $item['quantity'];
                        $total += $item_total;
                    ?>
                        <div class="cart-item">
                            <img src="<?php echo getImagePath($item); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <div class="item-details">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p><?php echo htmlspecialchars($item['category']); ?></p>
                            </div>
                            <div class="item-price">
                                <?php echo number_format($item['price'], 2); ?> zł
                            </div>
                            <div class="quantity-controls">
                                <button class="quantity-button" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, -1)">-</button>
                                <span><?php echo $item['quantity']; ?></span>
                                <button class="quantity-button" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, 1)">+</button>
                            </div>
                            <div class="item-total">
                                <?php echo number_format($item_total, 2); ?> zł
                            </div>
                            <button class="remove-item" onclick="removeItem(<?php echo $item['cart_id']; ?>)">
                                Usuń
                            </button>
                        </div>
                    <?php endforeach; ?>

                    <div class="cart-summary">
                        <p>Suma całkowita: <span class="total-price"><?php echo number_format($total, 2); ?> zł</span></p>
                        <button class="checkout-button" onclick="proceedToCheckout()">Przejdź do kasy</button>
                    </div>

                    <div class="payment-methods">
                        <div class="payment-method">
                            <img src="../images/przelewy24.png" alt="Przelewy24">
                        </div>
                        <div class="payment-method">
                            <img src="../images/blik.png" alt="BLIK">
                        </div>
                        <div class="payment-method">
                            <img src="../images/visa.png" alt="Visa">
                            <img src="../images/mastercard.png" alt="Mastercard">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Sklep Wędkarski. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>

    <script>
        function updateQuantity(cartId, change) {
            fetch('update_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cart_id=${cartId}&change=${change}`
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'success') {
                    location.reload();
                } else {
                    alert(result);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Wystąpił błąd podczas aktualizacji koszyka');
            });
        }

        function removeItem(cartId) {
            if (confirm('Czy na pewno chcesz usunąć ten produkt z koszyka?')) {
                fetch('remove_from_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `cart_id=${cartId}`
                })
                .then(response => response.text())
                .then(result => {
                    if (result === 'success') {
                        location.reload();
                    } else {
                        alert(result);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Wystąpił błąd podczas usuwania produktu');
                });
            }
        }

        function proceedToCheckout() {
            window.location.href = 'process_payment.php';
        }
    </script>
</body>
</html>