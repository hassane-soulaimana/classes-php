<?php
//findOneById
require_once __DIR__ . '/../job01/index.php';

function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$id = 7;
$product = Product::findOneById($id);

if ($product === false) {
    echo "Produit id={$id} introuvable ou erreur de connexion.";
    exit;
}

echo "<h1>Job07 - Product::findOneById({$id})</h1>\n";
echo "<ul>\n";
echo "<li>ID: " . h($product->getId()) . "</li>\n";
echo "<li>Nom: " . h($product->getName()) . "</li>\n";
echo "<li>Prix (centimes): " . h($product->getPrice()) . "</li>\n";
echo "<li>Prix (euros): " . h(number_format($product->getPrice() / 100, 2, ',', ' ')) . " €</li>\n";
echo "<li>Description: " . h($product->getDescription()) . "</li>\n";
echo "<li>Quantité: " . h($product->getQuantity()) . "</li>\n";
echo "<li>Category ID: " . h($product->getCategoryId()) . "</li>\n";
echo "</ul>\n";
