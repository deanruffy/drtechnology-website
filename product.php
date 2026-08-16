<?php
declare(strict_types=1);

$config = require '/home/deanruffy/config/db_config.php';

$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    $config['host'],
    $config['port'],
    $config['dbname'],
    $config['charset']
);

$pdo = new PDO(
    $dsn,
    $config['user'],
    $config['pass'],
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($productId < 1) {
    http_response_code(400);
    exit('Please choose a product.');
}

$productStatement = $pdo->prepare(
    'SELECT id, sku, name, short_description, description
     FROM products
     WHERE id = ?
       AND active = 1'
);

$productStatement->execute([$productId]);
$product = $productStatement->fetch();

if (!$product) {
    http_response_code(404);
    exit('Product not found.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($product['name']) ?> | DR Technology</title>
</head>
<body>
    <main>
        <p><a href="categories.php">← Back to product categories</a></p>

        <h1><?= htmlspecialchars($product['name']) ?></h1>

        <p>
            <strong>Product code:</strong>
            <?= htmlspecialchars((string) $product['sku']) ?>
        </p>

        <?php if (!empty($product['short_description'])): ?>
            <p><?= htmlspecialchars($product['short_description']) ?></p>
        <?php endif; ?>

        <?php if (!empty($product['description'])): ?>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <?php endif; ?>

        <h2>Request a quote</h2>

        <p>
            Submit this item to your quote request.
            We will confirm final price, availability, and lead time before sending a quote.
        </p>

        <form method="post" action="quote-request.php">
            <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">

            <label for="quantity">Quantity</label>
            <input
                id="quantity"
                name="quantity"
                type="number"
                min="1"
                value="1"
                required
            >

            <button type="submit">Add to quote request</button>
        </form>
    </main>
</body>
</html>
