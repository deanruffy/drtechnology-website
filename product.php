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
    'SELECT
        p.id,
        p.sku,
        p.name,
        p.short_description,
        p.description,
        c.id AS category_id,
        c.name AS category_name
     FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ?
       AND p.active = 1'
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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['name']) ?> | DR Technology</title>
  <meta name="description" content="<?= htmlspecialchars((string) ($product['short_description'] ?? '')) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
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
            <?= htmlspecialchars((string) ($product['category_name'] ?? 'Product')) ?>
          </p>

          <h1><?= htmlspecialchars($product['name']) ?></h1>

          <p class="hero-text">
            <?= htmlspecialchars((string) ($product['short_description'] ?? '')) ?>
          </p>

          <p><strong>Product code:</strong> <?= htmlspecialchars((string) $product['sku']) ?></p>

          <div class="card-actions">
            <?php if (!empty($product['category_id'])): ?>
              <a href="category.php?id=<?= (int) $product['category_id'] ?>" class="btn btn-small">
                Back to Category
              </a>
            <?php endif; ?>

            <a href="categories.php" class="btn btn-small">All Categories</a>
          </div>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="card-grid">
          <article class="service-card shop-card">
            <div>
              <p class="eyebrow">Product details</p>
              <h2>Overview</h2>

              <?php if (!empty($product['description'])): ?>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
              <?php else: ?>
                <p>Contact DR Technology for full technical specifications and compatibility guidance.</p>
              <?php endif; ?>
            </div>
          </article>

          <article class="service-card shop-card">
            <div>
              <p class="eyebrow">Request a quote</p>
              <h2>Confirm supply and pricing</h2>
              <p>
                Add this item to your quote request. We will confirm current availability,
                lead time, pricing, and suitability before supplying.
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

                <div class="card-actions">
                  <button type="submit" class="btn btn-primary">Add to Quote Request</button>
                </div>
              </form>
            </div>
          </article>
        </div>
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