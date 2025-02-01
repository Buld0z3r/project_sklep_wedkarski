<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejestracja - Sklep Wędkarski</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .auth-container {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-group label {
            font-weight: bold;
            color: #333;
        }

        .form-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }

        .auth-submit {
            background-color: #007BFF;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }

        .auth-submit:hover {
            background-color: #0056b3;
        }

        .auth-links {
            margin-top: 15px;
            text-align: center;
        }

        .auth-links a {
            color: #007BFF;
            text-decoration: none;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div id="container">
        <header>
            <div class="nav-buttons">
                <a href="../index.html">Strona główna</a>
                <a href="login.php">Logowanie</a>
            </div>
            <h1>Rejestracja</h1>
        </header>

        <main>
            <div class="auth-container">
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    require_once('../config/database.php');

                    $username = $_POST['username'];
                    $email = $_POST['email'];
                    $password = $_POST['password'];

                    try {
                        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR username = ?");
                        $stmt->execute([$email, $username]);

                        if ($stmt->rowCount() > 0) {
                            echo '<div class="error-message">Użytkownik z takim e-mailem lub nazwą użytkownika już istnieje.</div>';
                        } else {
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                            $stmt->execute([$username, $email, $hashedPassword]);

                            echo '<div class="success-message">Rejestracja zakończona sukcesem! Możesz się teraz <a href="login.php">zalogować</a>.</div>';
                        }
                    } catch(PDOException $e) {
                        echo '<div class="error-message">Wystąpił błąd podczas rejestracji: ' . htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
                ?>

                <form class="auth-form" method="POST" action="">
                    <div class="form-group">
                        <label for="username">Nazwa użytkownika:</label>
                        <input type="text" id="username" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Hasło:</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <button type="submit" class="auth-submit">Zarejestruj się</button>
                </form>

                <div class="auth-links">
                    <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Sklep Wędkarski. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>
</body>
</html>