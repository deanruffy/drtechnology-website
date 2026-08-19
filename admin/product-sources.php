<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

admin_require_login();

$pdo = admin_pdo();

$search = trim((string) ($_GET['q'] ?? ''));
$products = [];
$productSources = [];

if ($search !== '') {
    $searchTerm = '%' . $search . '%';

    $productStatement = $pdo->prepare(
        'SELECT DISTINCT
            p.id,
            p.sku,
            p.name,
            p.category_id,
            c.name AS category_name
         FROM products p
         LEFT JOIN categories c ON c.id = p.category_id
         LEFT JOIN product_distributors pd ON pd.product_id = p.id
         WHERE
            p.sku LIKE ?
            OR p.name LIKE ?
            OR pd.distributor_sku LIKE ?
         ORDER BY p.name
         LIMIT 50'
    );

    $productStatement->execute([$searchTerm, $searchTerm, $searchTerm]);
    $products = $productStatement->fetchAll();

    if ($products !== []) {
        $productIds = array_column($products, 'id');
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));

        $sourceStatement = $pdo->prepare(
            "SELECT
                pd.product_id,
                d.name AS distributor_name,
                pd.distributor_sku,
                pd.distributor_product_url,
                pd.cost_price,
                pd.currency,
                pd.stock_quantity,
                pd.stock_status,
                pd.lead_time_days,
                pd.preferred_supplier,
                pd.active,
                pd.last_checked_at,
                pd.notes
             FROM product_distributors pd
             JOIN distributors d ON d.id = pd.distributor_id
             WHERE pd.product_id IN ($placeholders)
             ORDER BY
                pd.product_id,
                pd.active DESC,
                pd.preferred_supplier DESC,
                d.name"
        );

        $sourceStatement->execute($productIds);

        foreach ($sourceStatement->fetchAll() as $source) {
            $productSources[(int) $source['product_id']][] = $source;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Sources | DR Technology Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../style.css">
  <link rel="icon" type="image/png" href="/Media/Favicon.png">
</head>
<body>
  <a class="skip-link" href="#main-content">Skip to content</a>

  <header class="site-header">
    <div class="container nav-wrap">
      <a href="../index.html" class="brand">
        <span class="brand-mark">DR</span>
        <span class="brand-text">DR Technology Admin</span>
      </a>

      <nav class="site-nav" aria-label="Admin navigation">
        <ul class="nav-links">
          <li><span>Signed in as <?= admin_escape($_SESSION['admin_username'] ?? '') ?></span></li>
          <li><a href="logout.php" class="nav-cta">Sign out</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main id="main-content">
    <section class="hero">
      <div class="container">
        <div class="section-heading hero-copy">
          <p class="eyebrow">Internal only</p>
          <h1>Product sourcing</h1>
          <p class="hero-text">
            Search by DR Technology SKU, product name, or distributor SKU.
            Distributor costs, stock, and purchase details are not shown on the public website.
          </p>
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <article class="service-card shop-card">
          <div>
            <form method="get" action="product-sources.php" id="quote-request-form">
              <div class="form-group">
                <label for="q">Search products or distributor SKUs</label>
                <input
                  type="search"
                  id="q"
                  name="q"
                  value="<?= admin_escape($search) ?>"
                  placeholder="Example: GSM4248P-100EUS or WC-EXAMPLE-001"
                  autofocus
                >
              </div>

              <button type="submit" class="btn btn-primary">Search</button>
            </form>
          </div>
        </article>

        <?php if ($search !== ''): ?>
          <div style="margin-top: 2rem;">
            <?php if ($products === []): ?>
              <div class="section-heading">
                <h2>No matching products</h2>
                <p>No products or distributor source records matched “<?= admin_escape($search) ?>”.</p>
              </div>
            <?php else: ?>
              <div class="section-heading">
                <p class="eyebrow">Search results</p>
                <h2><?= count($products) ?> matching product<?= count($products) === 1 ? '' : 's' ?></h2>
              </div>

              <div class="card-grid">
                <?php foreach ($products as $product): ?>
                  <article class="service-card shop-card">
                    <div>
                      <p class="eyebrow">DRT SKU: <?= admin_escape($product['sku']) ?></p>
                      <h3><?= admin_escape($product['name']) ?></h3>

                      <?php if (!empty($product['category_name'])): ?>
                        <p>Category: <?= admin_escape($product['category_name']) ?></p>
                      <?php endif; ?>

                      <div class="product-specifications">
                        <?php if (empty($productSources[(int) $product['id']])): ?>
                          <p>No distributor source records have been added yet.</p>
                        <?php else: ?>
                          <?php foreach ($productSources[(int) $product['id']] as $source): ?>
                            <dl class="product-specification">
                              <dt>Distributor</dt>
                              <dd><?= admin_escape($source['distributor_name']) ?></dd>

                              <dt>Distributor SKU</dt>
                              <dd><?= admin_escape($source['distributor_sku']) ?></dd>

                              <dt>Cost price</dt>
                              <dd>
                                <?= $source['cost_price'] !== null
                                    ? admin_escape($source['currency']) . ' ' . number_format((float) $source['cost_price'], 2)
                                    : 'Not recorded' ?>
                              </dd>

                              <dt>Availability</dt>
                              <dd>
                                <?= admin_escape($source['stock_status'] ?: 'Not recorded') ?>
                                <?php if ($source['stock_quantity'] !== null): ?>
                                  · <?= (int) $source['stock_quantity'] ?> in stock
                                <?php endif; ?>
                              </dd>

                              <dt>Lead time</dt>
                              <dd>
                                <?= $source['lead_time_days'] !== null
                                    ? (int) $source['lead_time_days'] . ' day(s)'
                                    : 'Not recorded' ?>
                              </dd>

                              <dt>Preferred</dt>
                              <dd><?= (int) $source['preferred_supplier'] === 1 ? 'Yes' : 'No' ?></dd>

                              <dt>Record status</dt>
                              <dd><?= (int) $source['active'] === 1 ? 'Active' : 'Inactive' ?></dd>

                              <dt>Last checked</dt>
                              <dd><?= admin_escape($source['last_checked_at'] ?: 'Not recorded') ?></dd>

                              <?php if (!empty($source['distributor_product_url'])): ?>
                                <dt>Distributor page</dt>
                                <dd>
                                  <a
                                    href="<?= admin_escape($source['distributor_product_url']) ?>"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                  >
                                    Open supplier listing
                                  </a>
                                </dd>
                              <?php endif; ?>

                              <?php if (!empty($source['notes'])): ?>
                                <dt>Notes</dt>
                                <dd><?= admin_escape($source['notes']) ?></dd>
                              <?php endif; ?>
                            </dl>
                          <?php endforeach; ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container footer-wrap">
      <p>Internal DR Technology admin area</p>
      <p>Distributor and cost data must not be shared externally.</p>
    </div>
  </footer>
</body>
</html>