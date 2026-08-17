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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($category['name']) ?> | DR Technology</title>
  <meta name="description" content="Browse <?= htmlspecialchars($category['name']) ?> products available from DR Technology.">
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
          <p class="eyebrow">Product Category</p>
          <h1><?= htmlspecialchars($category['name']) ?></h1>

          <?php if (!empty($category['description'])): ?>
            <p class="hero-text"><?= htmlspecialchars($category['description']) ?></p>
          <?php else: ?>
            <p class="hero-text">
              Browse the products available in this category and request a tailored quote.
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
        <?php if (!$products): ?>
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
                  <p class="eyebrow">SKU: <?= htmlspecialchars((string) $product['sku']) ?></p>
                  <h3><?= htmlspecialchars($product['name']) ?></h3>

                  <?php if (!empty($product['short_description'])): ?>
                    <p><?= htmlspecialchars($product['short_description']) ?></p>
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
      <p>© 2026 DR Technology · Modern technology solutions · hello@drtechnology.co.uk</p>
      <p>DR Technology · Technology Infrastructure · Equipment Supply · Systems Integration</p>
    </div>
  </footer>

  <script src="script.js" defer></script>
</body>
</html>