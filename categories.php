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

$categories = $pdo->query(
    'SELECT id, name, description
     FROM categories
     ORDER BY name'
)->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Product Categories | DR Technology</title>
</head>
<body>
    <main>
        <h1>Product Categories</h1>
        <p>Select a category to view products available for quotation.</p>

        <?php if (!$categories): ?>
            <p>No categories have been added yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($categories as $category): ?>
                    <li>
                        <a href="category.php?id=<?= (int) $category['id'] ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </a>

                        <?php if (!empty($category['description'])): ?>
                            <br>
                            <?= htmlspecialchars($category['description']) ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
