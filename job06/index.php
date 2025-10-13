<?php
require_once __DIR__ . '/../job01/index.php'; // Product
require_once __DIR__ . '/../job02/index.php'; // Category

function h($v)
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'draft-shop';

$mysqli = new mysqli($host, $user, $pass, $dbname);
if ($mysqli->connect_errno) {
    die('Erreur connexion MySQL: ' . $mysqli->connect_error);
}

$id = 7;
$stmt = $mysqli->prepare('SELECT id, name, photos, price, description, quantity, created_at, updated_at, category_id FROM product WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Produit id={$id} introuvable.";
    exit;
}
$row = $result->fetch_assoc();


$photos = [];
if (!empty($row['photos'])) {
    $decoded = json_decode($row['photos'], true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $photos = $decoded;
    }
}

try {
    $createdAt = !empty($row['created_at']) ? new DateTime($row['created_at']) : new DateTime();
} catch (Exception $e) {
    $createdAt = new DateTime();
}
try {
    $updatedAt = !empty($row['updated_at']) ? new DateTime($row['updated_at']) : new DateTime();
} catch (Exception $e) {
    $updatedAt = new DateTime();
}





$product = new Product();
$product->setId(isset($row['id']) ? (int)$row['id'] : null);
$product->setName($row['name'] ?? '');
$product->setPhotos($photos);
$product->setPrice(isset($row['price']) ? (int)$row['price'] : 0);
$product->setDescription($row['description'] ?? '');
$product->setQuantity(isset($row['quantity']) ? (int)$row['quantity'] : 0);
$product->setCreatedAt($createdAt);
$product->setUpdatedAt($updatedAt);
$product->setCategoryId(array_key_exists('category_id', $row) && $row['category_id'] !== null ? (int)$row['category_id'] : null);

$category = $product->getCategory();


echo "<h1>Job05 - Category pour product id={$id}</h1>\n";
if ($category === null) {
    echo "<p>Aucune category trouvée pour ce produit.</p>\n";
} else {
    echo "<ul>\n";
    echo "<li>ID: " . h($category->getID()) . "</li>\n";
    echo "<li>Nom: " . h($category->getName()) . "</li>\n";
    echo "<li>Description: " . h($category->getDescription()) . "</li>\n";
    echo "<li>Créé le: " . h($category->getCreatedAt()->format('d/m/Y H:i')) . "</li>\n";
    echo "<li>Mis à jour le: " . h($category->getUpdateAt()->format('d/m/Y H:i')) . "</li>\n";
    echo "</ul>\n";
}

$stmt->close();
$mysqli->close();

// Job06: lister les produits de la catégorie
echo "<h2>Job06 - Produits de la catégorie</h2>\n";
if ($category === null) {
    echo "<p>Pas de catégorie associée, impossible de lister les produits.</p>\n";
} else {
    $products = $category->getProducts();
    if (empty($products)) {
        echo "<p>Aucun produit trouvé pour la catégorie " . h($category->getName()) . "</p>\n";
    } else {
        echo "<ul>\n";
        foreach ($products as $p) {
            echo "<li>" . h($p->getId()) . " - " . h($p->getName()) . " (" . h(number_format($p->getPrice() / 100, 2, ',', ' ')) . " €)" . "</li>\n";
        }
        echo "</ul>\n";
    }
}
