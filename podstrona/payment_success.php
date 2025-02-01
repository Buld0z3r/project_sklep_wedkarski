<?php
session_start();
require_once('../config/database.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Płatność zakończona - Sklep Wędkarski</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .success-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 40px;
            margin: 20px auto;
            max-width: 600px;
            text-align: center;
        }

        .success-container h2 {
            color: #28a745;
            margin-bottom: 20px;
        }

        .buttons {
            margin-top: 30px;
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007BFF;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #0056b3;
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
            <h1>Płatność zakończona</h1>
        </header>

        <main>
            <div class="success-container">
                <h2>Dziękujemy za zakupy!</h2>
                <p>Twoje zamówienie zostało przyjęte do realizacji.</p>
                <p>Szczegóły zamówienia zostały wysłane na Twój adres email.</p>
                <div class="buttons">
                    <a href="products.php" class="button">Kontynuuj zakupy</a>
                    <a href="profile.php" class="button">Sprawdź status zamówienia</a>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Sklep Wędkarski. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
</body>
</html>