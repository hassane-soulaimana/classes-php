<?php
require_once __DIR__ . '/../job01/index.php'; // Product

function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$products = Product::findAll();

echo "<h1>Job08 - Tous les produits (" . count($products) . ")</h1>\n";
if (empty($products)) {
    echo "<p>Aucun produit trouvé.</p>\n";
} else {
    echo "<ul>\n";
    foreach ($products as $p) {
        echo "<li>" . h($p->getId()) . " - " . h($p->getName()) . " - " . h(number_format($p->getPrice() / 100, 2, ',', ' ')) . " €" . "</li>\n";
    }
    echo "</ul>\n";
}
