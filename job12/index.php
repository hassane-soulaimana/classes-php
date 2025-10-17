<?php
require_once __DIR__ . '/../job11/Clothing.php';
require_once __DIR__ . '/../job11/Electronic.php';

function h($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_clothing') {
    $p = new Clothing();
    $p->setName($_POST['name'] ?? 'Untitled')
        ->setPrice(isset($_POST['price']) ? (int)$_POST['price'] : 0)
        ->setDescription($_POST['description'] ?? '')
        ->setQuantity(isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0)
        ->setCreatedAt(new DateTime())
        ->setUpdatedAt(new DateTime());

    $p->setSize($_POST['size'] ?? '')
        ->setColor($_POST['color'] ?? '')
        ->setType($_POST['type'] ?? '')
        ->setMaterialFee(isset($_POST['material_fee']) ? (int)$_POST['material_fee'] : 0);

    if ($p->create() !== false) {
        $message = 'Clothing created id=' . h((string)$p->getId());
    } else {
        $message = 'Error creating clothing';
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_electronic') {
    $p = new Electronic();
    $p->setName($_POST['ename'] ?? 'Untitled')
        ->setPrice(isset($_POST['eprice']) ? (int)$_POST['eprice'] : 0)
        ->setDescription($_POST['edesc'] ?? '')
        ->setQuantity(isset($_POST['eqty']) ? (int)$_POST['eqty'] : 0)
        ->setCreatedAt(new DateTime())
        ->setUpdatedAt(new DateTime());

    $p->setBrand($_POST['brand'] ?? '')->setWarantyFee(isset($_POST['waranty_fee']) ? (int)$_POST['waranty_fee'] : 0);

    if ($p->create() !== false) {
        $message = 'Electronic created id=' . h((string)$p->getId());
    } else {
        $message = 'Error creating electronic';
    }
}


require_once __DIR__ . '/../config/db.php';
$clothes = Clothing::findAll();
$electronics = Electronic::findAll();

?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Job12 - Démonstration d'héritage</title>
</head>

<body>
    <h1>Vêtements & Appareils électroniques</h1>
    <?php if ($message): ?><p><?php echo h($message); ?></p><?php endif; ?>

    <h2>Créer un vêtement</h2>
    <form method="post">
        <input type="hidden" name="action" value="create_clothing">
        Nom : <input name="name"><br>
        Prix : <input name="price" type="number"><br>
        Description : <input name="description"><br>
        Quantité : <input name="quantity" type="number"><br>
        Taille : <input name="size"><br>
        Couleur : <input name="color"><br>
        Type : <input name="type"><br>
        Frais matériel : <input name="material_fee" type="number"><br>
        <button type="submit">Créer le vêtement</button>
    </form>

    <h2>Créer un appareil électronique</h2>
    <form method="post">
        <input type="hidden" name="action" value="create_electronic">
        Nom : <input name="ename"><br>
        Prix : <input name="eprice" type="number"><br>
        Description : <input name="edesc"><br>
        Quantité : <input name="eqty" type="number"><br>
        Marque : <input name="brand"><br>
        Frais de garantie : <input name="waranty_fee" type="number"><br>
        <button type="submit">Créer l'appareil</button>
    </form>

    <h2>Vêtements (<?php echo count($clothes); ?>)</h2>
    <?php if (empty($clothes)): ?><p>Aucun</p><?php else: ?><ul>
            <?php foreach ($clothes as $c): ?>
                <li><?php echo h((string)$c->getId()) . ' — ' . h($c->getName()) . ' — taille : ' . h($c->getSize()); ?></li>
            <?php endforeach; ?>
        </ul><?php endif; ?>

    <h2>Appareils électroniques (<?php echo count($electronics); ?>)</h2>
    <?php if (empty($electronics)): ?><p>Aucun</p><?php else: ?><ul>
            <?php foreach ($electronics as $e): ?>
                <li><?php echo h((string)$e->getId()) . ' — ' . h($e->getName()) . ' — marque : ' . h($e->getBrand()); ?></li>
            <?php endforeach; ?>
        </ul><?php endif; ?>

</body>

</html>