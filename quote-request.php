<?php
declare(strict_types=1);

session_start();

$productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 0;

if ($productId < 1 || $quantity < 1) {
    http_response_code(400);
    exit('Please choose a valid product and quantity.');
}

if (!isset($_SESSION['quote_basket'])) {
    $_SESSION['quote_basket'] = [];
}

if (!isset($_SESSION['quote_basket'][$productId])) {
    $_SESSION['quote_basket'][$productId] = 0;
}

$_SESSION['quote_basket'][$productId] += $quantity;

header('Location: quote-basket.php');
exit;