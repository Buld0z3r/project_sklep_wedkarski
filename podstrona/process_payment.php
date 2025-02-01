<?php
session_start();
require_once('../config/database.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    // Pobierz dane zamówienia
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cart_items)) {
        throw new Exception("Koszyk jest pusty");
    }

    // Oblicz sumę zamówienia
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Generuj unikalny identyfikator sesji
    $sessionId = uniqid('ORDER_');

    // Zapisz zamówienie w bazie danych
    $pdo->beginTransaction();
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, status, session_id) 
            VALUES (?, ?, 'pending', ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $total, $sessionId]);
        $order_id = $pdo->lastInsertId();
        
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }
        
        $pdo->commit();

        // Przekieruj do strony wyboru metody płatności
        $_SESSION['order_id'] = $order_id;
        $_SESSION['order_total'] = $total;
        header('Location: sandbox_payment.php');
        exit;
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    die("Wystąpił błąd: " . $e->getMessage());
}
?>