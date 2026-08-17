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
    'SELECT id, parent_id, name, description
     FROM categories
     WHERE id = ?
       AND active = 1'
);

$categoryStatement->execute([$categoryId]);
$category = $categoryStatement->fetch();

if (!$category) {
    http_response_code(404);
    exit('Category not found.');
}

$childStatement = $pdo->prepare(
    'SELECT id, name, description
     FROM categories
     WHERE parent_id = ?
       AND active = 1
     ORDER BY sort_order, name'
);

$childStatement->execute([$categoryId]);
$children = $childStatement->fetchAll();

$products = [];

if ($children === []) {
    $productStatement = $pdo->prepare(
        'SELECT
            p.id,
            p.sku,
            p.name,
            p.short_description,
            pi.image_path,
            pi.alt_text
         FROM products p
         LEFT JOIN product_images pi
            ON pi.product_id = p.id
           AND pi.is_primary = 1
         WHERE p.category_id = ?
           AND p.active = 1
         ORDER BY p.name'
    );

    $productStatement->execute([$categoryId]);
    $products = $productStatement->fetchAll();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?> | DR Technology</title>
  <meta name="description" content="Browse <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?> products available from DR Technology.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="/Media/Favicon.png">
</head>
<body>
  <a class="skip-link" href="#main-content">Skip to content</a>

  <header class="site-header">
    <div class="container nav-wrap">
      <a href="index.html" class="brand">
        <span class="brand-mark">DR</span>
        <span class="brand-text">DR Technology</span>
      </a>

      <nav class="site-nav" aria-label="Primary navigation">
        <button class="menu-toggle" aria-expanded="false" aria-controls="mobile-menu">
          <span></span><span></span><span></span>
        </button>

        <ul class="nav-links" id="mobile-menu">
          <li><a href="index.html">Home</a></li>
          <li><a href="about.html">About</a></li>
          <li><a href="services.html">Services</a></li>
          <li><a href="shop.html">Shop</a></li>
          <li><a href="knowledge-centre.html">Knowledge Centre</a></li>
          <li><a href="contact.html">Contact</a></li>
          <li><a href="mailto:hello@drtechnology.co.uk" class="nav-cta">Email Us</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main id="main-content">
    <section class="hero">
      <div class="container">
        <div class="section-heading hero-copy">
          <p class="eyebrow">
            <?= $category['parent_id'] === null ? 'Product Category' : 'Product Type' ?>
          </p>

          <h1><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h1>

          <?php if (!empty($category['description'])): ?>
            <p class="hero-text"><?= htmlspecialchars($category['description'], ENT_QUOTES, 'UTF-8') ?></p>
          <?php else: ?>
            <p class="hero-text">
              Browse available products and request a tailored quote from DR Technology.
            </p>
          <?php endif; ?>

          <div class="card-actions">
            <a href="categories.php" class="btn btn-small">Back to Categories</a>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <?php if ($children !== []): ?>
          <div class="section-heading">
            <p class="eyebrow">Choose a product type</p>
            <h2>Browse <?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p>Select a product type to view equipment available for quotation.</p>
          </div>

          <div class="card-grid">
            <?php foreach ($children as $child): ?>
              <article class="service-card shop-card">
                <div>
                  <h3><?= htmlspecialchars($child['name'], ENT_QUOTES, 'UTF-8') ?></h3>

                  <?php if (!empty($child['description'])): ?>
                    <p><?= htmlspecialchars($child['description'], ENT_QUOTES, 'UTF-8') ?></p>
                  <?php else: ?>
                    <p>Browse products in this category and request a quote.</p>
                  <?php endif; ?>
                </div>

                <div class="card-actions">
                  <a href="category.php?id=<?= (int) $child['id'] ?>" class="btn btn-small btn-primary">
                    View Products
                  </a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php elseif ($products === []): ?>
          <div class="section-heading">
            <h2>Products coming soon</h2>
            <p>
              This category is ready, but products have not been added yet.
              Contact us if you need help sourcing equipment.
            </p>

            <div class="card-actions">
              <a href="contact.html" class="btn btn-primary">Request Help</a>
            </div>
          </div>
        <?php else: ?>
          <div class="section-heading">
            <p class="eyebrow">Available for quotation</p>
            <h2>Products in this category</h2>
            <p>Select a product to review it and add it to your quote request.</p>
          </div>

          <div class="card-grid category-products">
            <?php foreach ($products as $product): ?>
              <article class="service-card shop-card">
                <div>
                  <?php if (!empty($product['image_path'])): ?>
                    <div class="category-product-image-frame">
                      <img
                        src="/<?= htmlspecialchars($product['image_path'], ENT_QUOTES, 'UTF-8') ?>"
                        alt="<?= htmlspecialchars((string) ($product['alt_text'] ?? $product['name']), ENT_QUOTES, 'UTF-8') ?>"
                        class="category-product-image"
                      >
                    </div>
                  <?php endif; ?>

                  <p class="eyebrow">
                    SKU: <?= htmlspecialchars((string) $product['sku'], ENT_QUOTES, 'UTF-8') ?>
                  </p>

                  <h3><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h3>

                  <?php if (!empty($product['short_description'])): ?>
                    <p><?= htmlspecialchars($product['short_description'], ENT_QUOTES, 'UTF-8') ?></p>
                  <?php endif; ?>
                </div>

                <div class="card-actions">
                  <a href="product.php?id=<?= (int) $product['id'] ?>" class="btn btn-small btn-primary">
                    View Product
                  </a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-wrap">
      <p>© <?= date('Y') ?> DR Technology · Modern technology solutions · hello@drtechnology.co.uk</p>
      <p>DR Technology · Technology Infrastructure · Equipment Supply · Systems Integration</p>
    </div>
  </footer>

  <script src="script.js" defer></script>
</body>
</html>