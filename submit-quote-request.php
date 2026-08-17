<?php
declare(strict_types=1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: quote-basket.php');
    exit;
}

function redirectWithError(string $message): never
{
    $_SESSION['quote_form_error'] = $message;
    header('Location: quote-contact.php');
    exit;
}

$customerName = trim((string) ($_POST['customer_name'] ?? ''));
$companyName = trim((string) ($_POST['company_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$telephone = trim((string) ($_POST['telephone'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));

if ($customerName === '') {
    redirectWithError('Please enter your full name.');
}

if (mb_strlen($customerName) > 150) {
    redirectWithError('Your name is too long.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirectWithError('Please enter a valid email address.');
}

if (mb_strlen($email) > 255) {
    redirectWithError('Your email address is too long.');
}

if (mb_strlen($companyName) > 150) {
    redirectWithError('Your company name is too long.');
}

if (mb_strlen($telephone) > 50) {
    redirectWithError('Your telephone number is too long.');
}

$basket = $_SESSION['quote_basket'] ?? [];

if ($basket === []) {
    redirectWithError('Your quote request is empty. Please add a product first.');
}

$productIds = array_keys($basket);

foreach ($productIds as $productId) {
    if ((int) $productId < 1 || (int) ($basket[$productId] ?? 0) < 1) {
        redirectWithError('Your quote request contains an invalid product or quantity.');
    }
}

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

$placeholders = implode(',', array_fill(0, count($productIds), '?'));

$productStatement = $pdo->prepare(
    "SELECT id, sku, name
     FROM products
     WHERE id IN ($placeholders)
       AND active = 1
     ORDER BY name"
);

$productStatement->execute($productIds);
$products = $productStatement->fetchAll();

if (count($products) !== count($productIds)) {
    redirectWithError('One or more selected products are no longer available. Please review your quote request.');
}

try {
    $pdo->beginTransaction();

    $requestStatement = $pdo->prepare(
        'INSERT INTO quote_requests (
            customer_name,
            company_name,
            email,
            telephone,
            message
        ) VALUES (?, ?, ?, ?, ?)'
    );

    $requestStatement->execute([
        $customerName,
        $companyName !== '' ? $companyName : null,
        $email,
        $telephone !== '' ? $telephone : null,
        $message !== '' ? $message : null,
    ]);

    $quoteRequestId = (int) $pdo->lastInsertId();

    $itemStatement = $pdo->prepare(
        'INSERT INTO quote_request_items (
            quote_request_id,
            product_id,
            quantity
        ) VALUES (?, ?, ?)'
    );

    foreach ($products as $product) {
        $productId = (int) $product['id'];
        $quantity = (int) $basket[$productId];

        $itemStatement->execute([
            $quoteRequestId,
            $productId,
            $quantity,
        ]);
    }

    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log('Quote request database error: ' . $exception->getMessage());
    http_response_code(500);
    exit('Sorry, we could not save your quote request. Please try again later.');
}

$emailLines = [
    'New DR Technology quote request',
    '',
    'Quote request number: ' . $quoteRequestId,
    'Name: ' . $customerName,
    'Company: ' . ($companyName !== '' ? $companyName : 'Not provided'),
    'Email: ' . $email,
    'Telephone: ' . ($telephone !== '' ? $telephone : 'Not provided'),
    '',
    'Requested products:',
];

foreach ($products as $product) {
    $productId = (int) $product['id'];
    $quantity = (int) $basket[$productId];

    $emailLines[] = sprintf(
        '- %s (%s) × %d',
        $product['name'],
        $product['sku'],
        $quantity
    );
}

$emailLines[] = '';
$emailLines[] = 'Project requirements / message:';
$emailLines[] = $message !== '' ? $message : 'Not provided';

$emailSubject = 'New quote request #' . $quoteRequestId . ' – ' . $customerName;
$emailBody = implode(PHP_EOL, $emailLines);

$headers = [
    'From: DR Technology Website <hello@drtechnology.co.uk>',
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8',
];

$mailSent = mail(
    'hello@drtechnology.co.uk',
    $emailSubject,
    $emailBody,
    implode("\r\n", $headers)
);

if (!$mailSent) {
    error_log('Quote request email could not be sent for request #' . $quoteRequestId);
}

unset($_SESSION['quote_basket']);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quote Request Received | DR Technology</title>
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
          <p class="eyebrow">Quote request received</p>
          <h1>Thank you, <?= htmlspecialchars($customerName, ENT_QUOTES, 'UTF-8') ?>.</h1>
          <p class="hero-text">
            Your request has been received. Your reference is
            <strong>#<?= $quoteRequestId ?></strong>.
          </p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <article class="service-card shop-card">
          <div>
            <h2>What happens next</h2>
            <p>
              A member of the DR Technology team will review your requested equipment
              and contact you with availability, lead time, and pricing.
            </p>

            <div class="card-actions">
              <a href="shop.html" class="btn btn-primary">Continue Browsing</a>
              <a href="contact.html" class="btn btn-small">Contact DR Technology</a>
            </div>
          </div>
        </article>
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