<?php
session_start();
require_once('../config/database.php');

if (!isset($_SESSION['user_id'])) {
    echo "Musisz być zalogowany!";
    exit;
}

if (!isset($_POST['cart_id']) || !isset($_POST['change'])) {
    echo "Nieprawidłowe żądanie!";
    exit;
}

$cart_id = $_POST['cart_id'];
$change = $_POST['change'];

try {
    // Sprawdź, czy przedmiot należy do użytkownika
    $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);
    $cart_item = $stmt->fetch();

    if (!$cart_item) {
        echo "Nieprawidłowy przedmiot w koszyku!";
        exit;
    }

    $new_quantity = $cart_item['quantity'] + $change;

    if ($new_quantity <= 0) {
        // Usuń przedmiot z koszyka
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->execute([$cart_id]);
    } else {
        // Aktualizuj ilość
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$new_quantity, $cart_id]);
    }

    echo "success";
} catch(PDOException $e) {
    echo "Wystąpił błąd: " . $e->getMessage();
}
?>