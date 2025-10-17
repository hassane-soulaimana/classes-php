<?php
require_once __DIR__ . '/../job01/index.php';

$product = new Product();
$product->setName('Souris Tronic');
$product->setPhotos(['']);
$product->setPrice(599);
$product->setDescription('Caractéristiques

    Sans fil avec nano-récepteur USB
    Bruit de clic réduit pour un fonctionnement silencieux
    Ergonomique
    Capteur optique avec résolution sélectionnable : 1000, 1500 ou 2000 dpi
    5 boutons et 1 bouton spécial pour la sensibilité du curseur
    Portée jusqu’à 5 m (technologie sans fil 2,4 GHz)
    Plug and Play – connexion simple via le port USB sans installation de pilote complexe
    Y compris 2 piles AA (LR6)
    Compatible avec : Microsoft® Windows 8 ou supérieur
');
$product->setQuantity(5);
$product->setCreatedAt(new DateTime());
$product->setUpdatedAt(new DateTime());
$product->setCategoryId(2);

$res = $product->create();

if ($res === false) {
    echo "Erreur lors de l'insertion du produit.";
} else {
    // Product::create() returns the Product instance on success.
    // Use the original $product (which now has its id set) to avoid analyzer warnings.
    echo "Produit inséré avec id=" . htmlspecialchars((string)$product->getId(), ENT_QUOTES, 'UTF-8');
}
