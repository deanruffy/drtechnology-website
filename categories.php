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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Categories | DR Technology</title>
  <meta name="description" content="Browse DR Technology product categories and request a tailored quote.">
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
          <p class="eyebrow">Product Catalogue</p>
          <h1>Browse products by technical workflow.</h1>
          <p class="hero-text">
            Select a category to view equipment available for quotation.
            DR Technology confirms current price, availability, lead time,
            and suitability before supplying.
          </p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <?php if (!$categories): ?>
          <p>No product categories have been added yet.</p>
        <?php else: ?>
          <div class="card-grid">
            <?php foreach ($categories as $category): ?>
              <article class="service-card shop-card">
                <div>
                  <h3><?= htmlspecialchars($category['name']) ?></h3>

                  <?php if (!empty($category['description'])): ?>
                    <p><?= htmlspecialchars($category['description']) ?></p>
                  <?php else: ?>
                    <p>Browse products in this category and request a quote.</p>
                  <?php endif; ?>
                </div>

                <div class="card-actions">
                  <a href="category.php?id=<?= (int) $category['id'] ?>" class="btn btn-small btn-primary">
                    View Products
                  </a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <section class="section section-alt">
      <div class="container">
        <div class="section-heading">
          <p class="eyebrow">Need help choosing?</p>
          <h2>Tell us what you are trying to achieve.</h2>
          <p>
            If you cannot find the exact product you need, send us your requirements.
            We can recommend suitable equipment and prepare a tailored quotation.
          </p>
        </div>

        <div class="card-actions">
          <a href="contact.html" class="btn btn-primary">Contact DR Technology</a>
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