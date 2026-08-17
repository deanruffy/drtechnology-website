<?php
declare(strict_types=1);

session_start();
$formError = $_SESSION['quote_form_error'] ?? null;
unset($_SESSION['quote_form_error']);

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

if ($products === []) {
    header('Location: quote-basket.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Details | DR Technology</title>
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
          <p class="eyebrow">Quote request</p>
          <h1>Your contact details</h1>
          <p class="hero-text">
            Tell us how to contact you and any project requirements. We will confirm availability, lead times, and pricing.
          </p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="card-grid">
          <article class="service-card shop-card">
            <div>
              <p class="eyebrow">Selected products</p>
              <h2>Your quote request</h2>

              <?php foreach ($products as $product): ?>
                <p>
                  <strong><?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                  Product code: <?= htmlspecialchars((string) $product['sku'], ENT_QUOTES, 'UTF-8') ?><br>
                  Quantity: <?= (int) ($basket[$product['id']] ?? 0) ?>
                </p>
              <?php endforeach; ?>

              <div class="card-actions">
                <a href="quote-basket.php" class="btn btn-small">Back to Quote Request</a>
              </div>
            </div>
          </article>

          <article class="service-card shop-card">
            <div>
              <p class="eyebrow">Your details</p>
              <h2>Send your request</h2>
              <?php if ($formError !== null): ?>
                <p class="form-error" role="alert">
                  <?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?>
                </p>
              <?php endif; ?>
              <form id="quote-request-form" method="post" action="submit-quote-request.php">
                <label for="customer_name">Full name</label>
                <input
                  id="customer_name"
                  name="customer_name"
                  type="text"
                  maxlength="150"
                  autocomplete="name"
                  required
                >

                <label for="company_name">Company name</label>
                <input
                  id="company_name"
                  name="company_name"
                  type="text"
                  maxlength="150"
                  autocomplete="organization"
                >

                <label for="email">Email address</label>
                <input
                  id="email"
                  name="email"
                  type="email"
                  maxlength="255"
                  autocomplete="email"
                  required
                >

                <label for="telephone">Telephone number</label>
                <input
                  id="telephone"
                  name="telephone"
                  type="tel"
                  maxlength="50"
                  autocomplete="tel"
                >

                <label for="message">Project requirements or message</label>
                <textarea
                  id="message"
                  name="message"
                  rows="6"
                ></textarea>

                <div class="card-actions">
                  <button type="submit" class="btn btn-primary">Send Quote Request</button>
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
      <p>© <?= date('Y') ?> DR Technology · Modern technology solutions · hello@drtechnology.co.uk</p>
      <p>DR Technology · Technology Infrastructure · Equipment Supply · Systems Integration</p>
    </div>
  </footer>

  <script src="script.js" defer></script>
</body>
</html>