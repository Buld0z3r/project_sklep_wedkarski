<?php
session_start();
require_once('../config/database.php');

if (!isset($_SESSION['user_id'])) {
    echo "Musisz być zalogowany!";
    exit;
}

if (!isset($_POST['cart_id'])) {
    echo "Nieprawidłowe żądanie!";
    exit;
}

$cart_id = $_POST['cart_id'];

try {
    // Sprawdź, czy przedmiot należy do użytkownika
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $_SESSION['user_id']]);

    if ($stmt->rowCount() > 0) {
        echo "success";
    } else {
        echo "Nie można usunąć przedmiotu z koszyka!";
    }
} catch(PDOException $e) {
    echo "Wystąpił błąd: " . $e->getMessage();
}
?>