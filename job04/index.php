<?php
require_once __DIR__ . '/../job01/index.php'; // classe Product

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
    echo "Produit avec id={$id} introuvable. Vérifie que la table product contient des données.<br>";
    $stmt->close();
    $mysqli->close();
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

// dates
$createdAt = new DateTime();
$updatedAt = new DateTime();
try {
    if (!empty($row['created_at'])) {
        $createdAt = new DateTime($row['created_at']);
    }
} catch (Exception $e) {
    $createdAt = new DateTime();
}
try {
    if (!empty($row['updated_at'])) {
        $updatedAt = new DateTime($row['updated_at']);
    }
} catch (Exception $e) {
    $updatedAt = new DateTime();
}

// Instancier sans appeler le constructeur pour montrer l'hydratation via les setters
$ref = new ReflectionClass('Product');
$product = $ref->newInstanceWithoutConstructor();

// Appeler les setters manuellement
if ($product instanceof Product) {
    $product->setId(isset($row['id']) ? (int)$row['id'] : null);
    $product->setName($row['name'] ?? '');
    $product->setPhotos($photos);
    $product->setPrice(isset($row['price']) ? (int)$row['price'] : 0);
    $product->setDescription($row['description'] ?? '');
    $product->setQuantity(isset($row['quantity']) ? (int)$row['quantity'] : 0);
    $product->setCreatedAt($createdAt);
    $product->setUpdatedAt($updatedAt);
    $product->setCategoryId(array_key_exists('category_id', $row) && $row['category_id'] !== null ? (int)$row['category_id'] : null);
} else {
    die('Impossible d\'instancier Product via Reflection.');
}

// Affichage pour vérifier l'hydratation
echo "<h1>Job 04 - Produit hydraté à partir d'un tableau associatif (id=$id)</h1>";
echo "<ul>";
echo "<li>ID: " . h($product->getId()) . "</li>";
echo "<li>Nom: " . h($product->getName()) . "</li>";
echo "<li>Prix (centimes): " . h($product->getPrice()) . "</li>";
echo "<li>Prix (euros): " . h(number_format($product->getPrice() / 100, 2, ',', ' ')) . " €</li>";
echo "<li>Description: " . h($product->getDescription()) . "</li>";
echo "<li>Quantité: " . h($product->getQuantity()) . "</li>";
echo "<li>Category ID: " . h($product->getCategoryId()) . "</li>";
echo "<li>Photos: " . h(implode(', ', $product->getPhotos())) . "</li>";
echo "<li>Créé le: " . h($product->getCreatedAt()->format('d/m/Y H:i')) . "</li>";
echo "<li>Mis à jour le: " . h($product->getUpdatedAt()->format('d/m/Y H:i')) . "</li>";
echo "</ul>";

$stmt->close();
$mysqli->close();
