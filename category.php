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

$categoryId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($categoryId < 1) {
    http_response_code(400);
    exit('Please choose a product category.');
}

$categoryStatement = $pdo->prepare(
    'SELECT id, name, description
     FROM categories
     WHERE id = ?'
);

$categoryStatement->execute([$categoryId]);
$category = $categoryStatement->fetch();

if (!$category) {
    http_response_code(404);
    exit('Category not found.');
}

$productStatement = $pdo->prepare(
    'SELECT id, sku, name, short_description
     FROM products
     WHERE category_id = ?
       AND active = 1
     ORDER BY name'
);

$productStatement->execute([$categoryId]);
$products = $productStatement->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($category['name']) ?> | DR Technology</title>
</head>
<body>
    <main>
        <p><a href="categories.php">← Back to all categories</a></p>

        <h1><?= htmlspecialchars($category['name']) ?></h1>

        <?php if (!empty($category['description'])): ?>
            <p><?= htmlspecialchars($category['description']) ?></p>
        <?php endif; ?>

        <?php if (!$products): ?>
            <p>No products have been added to this category yet.</p>
        <?php else: ?>
            <h2>Products available for quotation</h2>

            <ul>
                <?php foreach ($products as $product): ?>
                    <li>
                        <a href="product.php?id=<?= (int) $product['id'] ?>">
                            <?= htmlspecialchars($product['name']) ?>
                        </a>
                        <br>
                        <small>SKU: <?= htmlspecialchars((string) $product['sku']) ?></small>

                        <?php if (!empty($product['short_description'])): ?>
                            <br>
                            <?= htmlspecialchars($product['short_description']) ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
