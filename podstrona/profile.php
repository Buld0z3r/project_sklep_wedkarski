<?php
session_start();
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
    <title>Profil - Sklep Wędkarski</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .profile-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 20px;
            margin: 20px;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-header h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .profile-info {
            max-width: 600px;
            margin: 0 auto;
        }

        .info-group {
            margin-bottom: 15px;
            padding: 10px;
            background-color: rgba(0, 123, 255, 0.1);
            border-radius: 4px;
        }

        .info-group strong {
            color: #007BFF;
        }

        .profile-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .profile-button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
        }

        .view-orders {
            background-color: #28a745;
        }

        .edit-profile {
            background-color: #007BFF;
        }

        .logout {
            background-color: #dc3545;
        }

        .profile-button:hover {
            opacity: 0.9;
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
                <a href="logout.php">Wyloguj</a>
            </div>
            <h1>Twój Profil</h1>
        </header>

        <main>
            <div class="profile-container">
                <div class="profile-header">
                    <h2>Witaj, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <p>Status: Administrator</p>
                    <?php endif; ?>
                </div>

                <div class="profile-info">
                    <div class="info-group">
                        <p><strong>Nazwa użytkownika:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></p>
                    </div>
                </div>

                <div class="profile-actions">
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <a href="admin.php" class="profile-button edit-profile">Panel Administratora</a>
                    <?php endif; ?>
                    <a href="logout.php" class="profile-button logout">Wyloguj się</a>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Sklep Wędkarski. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
</body>
</html>