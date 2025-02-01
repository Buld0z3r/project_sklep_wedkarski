<?php
// Konfiguracja testowa (sandbox) Przelewy24
define('P24_MERCHANT_ID', '64319');
define('P24_POS_ID', '64319');
define('P24_API_KEY', '9c11e705e5e7c8');
define('P24_CRC', '76a3e29094c');
define('P24_TEST_MODE', true);

// URL-e dla środowiska testowego
define('P24_API_URL', 'https://sandbox.przelewy24.pl');
define('P24_VERIFY_URL', P24_API_URL . '/api/v1/transaction/verify');
define('P24_TRANSACTION_URL', P24_API_URL . '/api/v1/transaction/register');

// Włącz logowanie dla debugowania
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/p24_errors.log');

// Funkcja do generowania sumy kontrolnej
function calculateP24Sign($data, $crc) {
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return hash('sha384', $json . $crc);
}
?>