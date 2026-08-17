<?php
declare(strict_types=1);

session_start();

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

$basket = $_SESSION['quote_basket'] ?? [];
$productIds = array_keys($basket);
$products = [];

if ($productIds !== []) {
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));

    $statement = $pdo->prepare(
        "SELECT id, sku, name, short_description
         FROM products
         WHERE id IN ($placeholders)
           AND active = 1
         ORDER BY name"
    );

    $statement->execute($productIds);
    $products = $statement->fetchAll();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Quote Request | DR Technology</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <link rel="icon" type="image/png" href="/media/favicon.png">
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
          <p class="eyebrow">Quote request</p>
          <h1>Your selected products</h1>
          <p class="hero-text">
            Review the equipment you would like DR Technology to quote for.
          </p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <?php if ($products === []): ?>
          <article class="service-card shop-card">
            <div>
              <p class="eyebrow">Your quote request</p>
              <h2>Your quote request is empty</h2>
              <p>Browse the catalogue and add one or more products to request pricing and availability.</p>

              <div class="card-actions">
                <a href="categories.php" class="btn btn-primary">Browse Product Categories</a>
              </div>
            </div>
          </article>
        <?php else: ?>
          <div class="card-grid">
            <?php foreach ($products as $product): ?>
              <article class="service-card shop-card">
                <div>
                  <p class="eyebrow">
                    Quantity: <?= (int) ($basket[$product['id']] ?? 0) ?>
                  </p>

                  <h2><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></h2>

                  <p>
                    <strong>Product code:</strong>
                    <?= htmlspecialchars((string) $product['sku'], ENT_QUOTES, 'UTF-8') ?>
                  </p>

                  <?php if (!empty($product['short_description'])): ?>
                    <p><?= htmlspecialchars($product['short_description'], ENT_QUOTES, 'UTF-8') ?></p>
                  <?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

          <div class="card-actions" style="margin-top: 2rem;">
            <a href="categories.php" class="btn btn-small">Continue Browsing</a>
            <a href="quote-contact.php" class="btn btn-primary">Continue to Contact Details</a>
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