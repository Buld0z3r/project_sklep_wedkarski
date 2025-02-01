<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once('../config/database.php');

// Ustawienie nagłówków dla pliku CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="users_export_' . date('Y-m-d') . '.csv"');

// Utworzenie handlera pliku do zapisu
$output = fopen('php://output', 'w');

// Ustawienie znacznika BOM dla poprawnego kodowania UTF-8
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Nagłówki kolumn
fputcsv($output, array('ID', 'Nazwa użytkownika', 'Email', 'Rola', 'Data utworzenia'));

try {
    $stmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $row);
    }
} catch(PDOException $e) {
    die("Błąd eksportu danych: " . $e->getMessage());
}

fclose($output);
?>