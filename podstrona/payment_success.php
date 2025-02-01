<?php
session_start();
require_once('../config/database.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Pobierz ostatnie zamówienie użytkownika
$stmt = $pdo->prepare("
    SELECT o.*, 
           oi.quantity as item_quantity,
           oi.price as item_price,
           p.name as product_name,
           p.category as product_category
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Grupuj przedmioty według zamówienia
$orders = [];
foreach ($order_items as $item) {
    $order_id = $item['id'];
    if (!isset($orders[$order_id])) {
        $orders[$order_id] = [
            'id' => $order_id,
            'total_amount' => $item['total_amount'],
            'status' => $item['status'],
            'created_at' => $item['created_at'],
            'items' => []
        ];
    }
    $orders[$order_id]['items'][] = [
        'name' => $item['product_name'],
        'category' => $item['product_category'],
        'quantity' => $item['item_quantity'],
        'price' => $item['item_price']
    ];
}

// Weź najnowsze zamówienie
$latest_order = reset($orders);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Potwierdzenie zamówienia - Sklep Wędkarski</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .success-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
        }

        .success-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon {
            color: #28a745;
            font-size: 48px;
            margin-bottom: 20px;
        }

        .order-details {
            margin-top: 30px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
        }

        .order-summary {
            margin-bottom: 20px;
        }

        .order-summary h3 {
            color: #007BFF;
            margin-bottom: 10px;
        }

        .order-items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .order-items th,
        .order-items td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        .order-items th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .status-completed {
            background-color: #28a745;
            color: white;
        }

        .status-pending {
            background-color: #ffc107;
            color: #000;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 30px;
        }

        .action-button {
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            background-color: #007bff;
            transition: background-color 0.3s;
        }

        .action-button:hover {
            background-color: #0056b3;
        }

        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .info-item {
            text-align: center;
        }

        .info-item strong {
            display: block;
            color: #007BFF;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div id="container">
        <header>
            <div class="nav-buttons">
                <a href="../index.html">Strona główna</a>
                <a href="products.php">Produkty</a>
                <a href="profile.php">Moje konto</a>
            </div>
            <h1>Potwierdzenie zamówienia</h1>
        </header>

        <main>
            <div class="success-container">
                <div class="success-header">
                    <div class="success-icon">✓</div>
                    <h2>Dziękujemy za zakupy!</h2>
                    <p>Twoje zamówienie zostało przyjęte do realizacji.</p>
                </div>

                <?php if ($latest_order): ?>
                    <div class="order-details">
                        <div class="order-info">
                            <div class="info-item">
                                <strong>Numer zamówienia</strong>
                                <span>#<?php echo $latest_order['id']; ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Data zamówienia</strong>
                                <span><?php echo date('d.m.Y H:i', strtotime($latest_order['created_at'])); ?></span>
                            </div>
                            <div class="info-item">
                                <strong>Status</strong>
                                <span class="status-badge status-<?php echo $latest_order['status']; ?>">
                                    <?php echo $latest_order['status'] === 'completed' ? 'Opłacone' : 'Oczekujące'; ?>
                                </span>
                            </div>
                            <div class="info-item">
                                <strong>Wartość zamówienia</strong>
                                <span><?php echo number_format($latest_order['total_amount'], 2); ?> zł</span>
                            </div>
                        </div>

                        <div class="order-summary">
                            <h3>Szczegóły zamówienia</h3>
                            <table class="order-items">
                                <thead>
                                    <tr>
                                        <th>Produkt</th>
                                        <th>Kategoria</th>
                                        <th>Ilość</th>
                                        <th>Cena jedn.</th>
                                        <th>Suma</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($latest_order['items'] as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['category']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><?php echo number_format($item['price'], 2); ?> zł</td>
                                            <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> zł</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="total-row">
                                        <td colspan="4">Razem</td>
                                        <td><?php echo number_format($latest_order['total_amount'], 2); ?> zł</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="action-buttons">
                    <a href="products.php" class="action-button">Kontynuuj zakupy</a>
                    <a href="profile.php" class="action-button">Moje zamówienia</a>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Sklep Wędkarski. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
</body>
</html>