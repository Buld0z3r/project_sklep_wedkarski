-- Tworzenie bazy danych
CREATE DATABASE IF NOT EXISTS sklep_wedkarski CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sklep_wedkarski;

-- Tabela użytkowników
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela produktów
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    category ENUM('wędki', 'przynęty', 'kołowrotki') NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela koszyka
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Tabela zamówień
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    session_id VARCHAR(255) NOT NULL,
    payment_id VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabela elementów zamówienia
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Dodanie przykładowego administratora (hasło: admin123)
INSERT INTO users (username, email, password, role) 
VALUES ('admin', 'admin@sklep.pl', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Dodanie produktów z odnośnikami do zdjęć
INSERT INTO products (name, price, description, category, image_url, quantity) VALUES
-- Wędki
('Wędka spinningowa Dragon', 299.99, 'Profesjonalna wędka do połowu spinningowego. Długość: 2.70m, Akcja: Fast', 'wędki', 'images/products/wedka_spinning.jpg', 10),
('Wędka karpiowa Premium', 599.99, 'Wysokiej klasy wędka karpiowa z włókna węglowego. Długość: 3.60m', 'wędki', 'images/products/wedka_karp.jpg', 8),
('Wędka feederowa Shimano', 349.99, 'Idealna wędka do metody feederowej. Długość: 3.30m', 'wędki', 'images/products/wedka_feeder.jpg', 15),
('Wędka muchowa Sage', 449.99, 'Lekka wędka do połowu na muchę. Długość: 2.75m', 'wędki', 'images/products/wedka_muchowa.jpg', 6),

-- Przynęty
('Wobler Rapala', 39.99, 'Skuteczny wobler pstrągowy. Długość: 7cm', 'przynęty', 'images/products/wobler1.jpg', 50),
('Zestaw woblerów Premium', 129.99, 'Komplet 5 woblerów w różnych kolorach', 'przynęty', 'images/products/wobler_set.jpg', 30),
('Przynęty gumowe Dragon', 49.99, 'Zestaw 20 gumowych przynęt. Różne kolory i rozmiary', 'przynęty', 'images/products/gumy.jpg', 100),
('Błystka obrotowa Mepps', 24.99, 'Klasyczna błystka obrotowa. Rozmiar: #3', 'przynęty', 'images/products/blystka.jpg', 75),

-- Kołowrotki
('Kołowrotek Shimano Stradic', 499.99, 'Wysokiej jakości kołowrotek spinningowy. Przełożenie: 6.0:1', 'kołowrotki', 'images/products/kolowrotek_shimano.jpg', 5),
('Kołowrotek Penn Battle', 799.99, 'Profesjonalny kołowrotek morski. Przełożenie: 5.6:1', 'kołowrotki', 'images/products/kolowrotek_penn.jpg', 3),
('Kołowrotek Dragon Mega Baits', 299.99, 'Niezawodny kołowrotek spinningowy. Przełożenie: 5.2:1', 'kołowrotki', 'images/products/kolowrotek_dragon.jpg', 12),
('Kołowrotek Daiwa Ninja', 399.99, 'Uniwersalny kołowrotek spinningowy. Przełożenie: 5.3:1', 'kołowrotki', 'images/products/kolowrotek_daiwa.jpg', 8);