<?php
session_start();
require_once('../config/database.php');

try {
    $stmt = $pdo->query("SELECT * FROM products ORDER BY category, name");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Błąd bazy danych: " . $e->getMessage();
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
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produkty - Sklep Wędkarski</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .products-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        .product-card {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: transform 0.2s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }

        .product-name {
            font-size: 1.2em;
            font-weight: bold;
            margin: 10px 0;
            color: #333;
        }

        .product-price {
            color: #007BFF;
            font-size: 1.1em;
            font-weight: bold;
            margin: 10px 0;
        }

        .product-category {
            background-color: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            color: #495057;
        }

        .add-to-cart {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: background-color 0.2s;
        }

        .add-to-cart:hover {
            background-color: #0056b3;
        }

        .category-filter {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 8px;
            margin: 20px;
        }

        .category-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .category-button {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: #e9ecef;
            color: #495057;
            transition: all 0.3s ease;
        }

        .category-button.active {
            background-color: #007BFF;
            color: white;
        }
    </style>
</head>
<body>
    <div id="container">
        <header>
            <div class="nav-buttons">
                <a href="../index.html">Strona główna</a>
                <a href="cart.php">Koszyk</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php">Wyloguj</a>
                <?php else: ?>
                    <a href="login.php">Zaloguj</a>
                <?php endif; ?>
            </div>
            <h1>Nasze Produkty</h1>
        </header>

        <main>
            <div class="category-filter">
                <div class="category-buttons">
                    <button class="category-button active" data-category="all">Wszystkie</button>
                    <button class="category-button" data-category="wędki">Wędki</button>
                    <button class="category-button" data-category="przynęty">Przynęty</button>
                    <button class="category-button" data-category="kołowrotki">Kołowrotki</button>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php else: ?>
                <div class="products-container">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-category="<?php echo htmlspecialchars($product['category']); ?>">
                            <img src="<?php echo getImagePath($product); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-image"
                                 onerror="this.src='../images/products/placeholder.jpg'">
                            <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-price"><?php echo number_format($product['price'], 2); ?> zł</p>
                            <span class="product-category"><?php echo htmlspecialchars($product['category']); ?></span>
                            <p><?php echo htmlspecialchars($product['description']); ?></p>
                            <button class="add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                Dodaj do koszyka
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; 2024 Sklep Wędkarski. Wszelkie prawa zastrzeżone.</p>
        </footer>
    </div>

    <script>
        function addToCart(productId) {
            if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
                alert('Musisz być zalogowany, aby dodać produkt do koszyka!');
                window.location.href = 'login.php';
                return;
            }

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + productId
            })
            .then(response => response.text())
            .then(result => {
                alert(result);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Wystąpił błąd podczas dodawania do koszyka');
            });
        }

        document.querySelectorAll('.category-button').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelectorAll('.category-button').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                button.classList.add('active');
                
                const category = button.dataset.category;
                const products = document.querySelectorAll('.product-card');
                
                products.forEach(product => {
                    if (category === 'all' || product.dataset.category === category) {
                        product.style.display = 'block';
                    } else {
                        product.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>