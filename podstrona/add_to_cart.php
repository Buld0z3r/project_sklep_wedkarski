<?php
session_start();
require_once('../config/database.php');

if (!isset($_SESSION['user_id'])) {
    echo "Musisz być zalogowany, aby dodać produkt do koszyka!";
    exit;
}

if (!isset($_POST['product_id'])) {
    echo "Nieprawidłowe żądanie!";
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];

try {
    // Sprawdź, czy produkt już jest w koszyku
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing_item = $stmt->fetch();

    if ($existing_item) {
        // Aktualizuj ilość
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        $stmt->execute([$existing_item['id']]);
    } else {
        // Dodaj nowy produkt do koszyka
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $product_id]);
    }

    echo "Produkt został dodany do koszyka!";
} catch(PDOException $e) {
    echo "Wystąpił błąd podczas dodawania do koszyka: " . $e->getMessage();
}
?>