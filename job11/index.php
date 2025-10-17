<?php
require_once __DIR__ . '/..//job01/index.php';
require_once __DIR__ . '/Clothing.php';
require_once __DIR__ . '/Electronic.php';

session_start();

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'utf-8');
}

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_product') {
    $p = new Product();
    $p->setName($_POST['name'] ?? 'Untitled')
        ->setPrice(isset($_POST['price']) ? (int)$_POST['price'] : 0)
        ->setDescription($_POST['description'] ?? '')
        ->setQuantity(isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0)
        ->setCategoryId(isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null);

    if ($p->create()) {
        $message = 'Produit créé avec id ' . $p->getId();
        $_SESSION['last_product_id'] = $p->getId();
    } else {
        $message = 'Erreur création produit';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_clothing') {
    $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $c = new Clothing($pid);
    $c->setSize($_POST['size'] ?? '')
        ->setColor($_POST['color'] ?? '')
        ->setType($_POST['type'] ?? '')
        ->setMaterialFee(isset($_POST['material_fee']) ? (int)$_POST['material_fee'] : 0);

    if ($c->saveExtra()) {
        $message = 'Clothing saved for product ' . $pid;
    } else {
        $message = 'Error saving clothing';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_electronic') {
    $pid = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $e = new Electronic($pid);
    $e->setBrand($_POST['brand'] ?? '')
        ->setWarantyFee(isset($_POST['waranty_fee']) ? (int)$_POST['waranty_fee'] : 0);

    if ($e->saveExtra()) {
        $message = 'Electronic saved for product ' . $pid;
    } else {
        $message = 'Error saving electronic';
    }
}

function listProductsBrief(): array
{
    $rows = [];
    require_once __DIR__ . '/../config/db.php';
    $mysqli = db_connect();
    if ($mysqli === null) {
        return [];
    }
    $res = $mysqli->query('SELECT id, name FROM product ORDER BY id DESC LIMIT 50');
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $rows[] = $r;
        }
        $res->close();
    }
    $mysqli->close();
    return $rows;
}

$products = listProductsBrief();

?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title> Vetements & électroniques</title>
</head>

<body>
    <h1>Vetements & Electronique</h1>

    <?php if ($message): ?>
        <p><strong><?php echo h($message); ?></strong></p>
    <?php endif; ?>

    <h2>Crée une marque de produit </h2>
    <form method="post">
        <input type="hidden" name="action" value="create_product">
        <label>Nom: <input name="name"></label><br>
        <label>Prix (int): <input name="price" type="number"></label><br>
        <label>Description: <input name="description"></label><br>
        <label>Quantitée: <input name="quantity" type="number"></label><br>
        <label>Categorie (optionnel): <input name="category_id" type="number"></label><br>
        <button type="submit">Envoyer </button>
    </form>

    <h2>Produits récents </h2>
    <?php if (count($products) === 0): ?>
        <p>Aucun produit trouvé</p>
    <?php else: ?>
        <ul>
            <?php foreach ($products as $pr): ?>
                <li><?php echo h($pr['id']) . ' — ' . h($pr['name']); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2>Données sur les vetements</h2>
    <form method="post">
        <input type="hidden" name="action" value="save_clothing">
        <label>Produit id: <input name="product_id" value="<?php echo isset($_SESSION['last_product_id']) ? h($_SESSION['last_product_id']) : ''; ?>"></label><br>
        <label>Taille: <input name="size"></label><br>
        <label>Couleurs: <input name="color"></label><br>
        <label>Type: <input name="type"></label><br>
        <label>Materiel fee (int): <input name="material_fee" type="number"></label><br>
        <button type="submit">Sauvegarder</button>
    </form>

    <h2>Joindre des donées electroniques </h2>
    <form method="post">
        <input type="hidden" name="action" value="save_electronic">
        <label>Product id: <input name="product_id" value="<?php echo isset($_SESSION['last_product_id']) ? h($_SESSION['last_product_id']) : ''; ?>"></label><br>
        <label>Marque : <input name="brand"></label><br>
        <label>Frais de garantie: <input name="waranty_fee" type="number"></label><br>
        <button type="submit">Sauvegarder</button>
    </form>

</body>

</html>