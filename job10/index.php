<?php

require_once __DIR__ . '/../job01/index.php';

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

$errors = [];
$success = false;


$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$selectedId = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'select') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Jeton CSRF invalide.';
    } else {
        $selectedId = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SESSION['selectedId'])) {
    $selectedId = (int)$_SESSION['selectedId'];
    unset($_SESSION['selectedId']);
}

if ($selectedId === null) {
    $all = Product::findAll();
?>
    <!doctype html>
    <html lang="fr">

    <head>
        <meta charset="utf-8">
        <title> Choisir un produit</title>
    </head>

    <body>
        <h1>Choisir un produit à éditer</h1>
        <?php if (empty($all)): ?>
            <p>Aucun produit disponible.</p>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="action" value="select">
                <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">
                <label>Produit:
                    <select name="id">
                        <?php foreach ($all as $p): ?>
                            <option value="<?php echo h((string)$p->getId()); ?>"><?php echo h($p->getId() . ' - ' . $p->getName()); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <button type="submit">Éditer</button>
            </form>
        <?php endif; ?>
    </body>

    </html>
<?php
    exit;
}

$product = Product::findOneById($selectedId);
if ($product === false) {
    echo 'Produit introuvable (id=' . h((string)$selectedId) . ')';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : null;
    if ($action === 'save') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $errors[] = 'Jeton CSRF invalide.';
        }

        $selectedId = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
        $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
        $price = isset($_POST['price']) ? (int)$_POST['price'] : 0;
        $description = isset($_POST['description']) ? trim((string)$_POST['description']) : '';
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
        $category_id = isset($_POST['category_id']) && $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;

        // Validation
        if ($name === '') {
            $errors[] = 'Le nom est requis.';
        }
        if ($price < 0) {
            $errors[] = 'Le prix doit être positif ou zéro.';
        }
        if ($quantity < 0) {
            $errors[] = 'La quantité doit être positive ou zéro.';
        }

        if (empty($errors)) {
            $product = Product::findOneById($selectedId);
            if ($product === false) {
                $errors[] = 'Produit introuvable.';
            } else {
                $product->setName($name)
                    ->setPrice($price)
                    ->setDescription($description)
                    ->setQuantity($quantity)
                    ->setCategoryId($category_id)
                    ->setUpdatedAt(new DateTime());

                $res = $product->update();
                if ($res === false) {
                    $errors[] = 'Erreur lors de la mise à jour en base.';
                } else {
                    $_SESSION['selectedId'] = $product->getId();
                    $_SESSION['flash'] = 'Produit mis à jour avec succès.';
                    redirect($_SERVER['PHP_SELF']);
                }
            }
        }
    }
}

if ($flash !== null) {
    $success = true;
}

?>
<!doctype html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Job10 - Éditer le produit</title>
</head>

<body>
    <h1>Éditer le produit (ID <?php echo h((string)$product->getId()); ?>)</h1>

    <?php if ($success): ?>
        <p style="color:green;">Produit mis à jour avec succès.</p>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $e): ?>
                <li><?php echo h($e); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo h((string)$product->getId()); ?>">
        <input type="hidden" name="csrf_token" value="<?php echo h($_SESSION['csrf_token']); ?>">

        <div>
            <label>Nom:<br>
                <input type="text" name="name" value="<?php echo h($product->getName()); ?>">
            </label>
        </div>

        <div>
            <label>Prix (centimes):<br>
                <input type="number" name="price" value="<?php echo h((string)$product->getPrice()); ?>" min="0">
            </label>
        </div>

        <div>
            <label>Description:<br>
                <textarea name="description" rows="4" cols="50"><?php echo h($product->getDescription()); ?></textarea>
            </label>
        </div>

        <div>
            <label>Quantité:<br>
                <input type="number" name="quantity" value="<?php echo h((string)$product->getQuantity()); ?>" min="0">
            </label>
        </div>

        <div>
            <label>Category ID (laisser vide = null):<br>
                <input type="number" name="category_id" value="<?php echo $product->getCategoryId() !== null ? h((string)$product->getCategoryId()) : ''; ?>">
            </label>
        </div>

        <div style="margin-top:8px;">
            <button type="submit">Enregistrer</button>
        </div>
    </form>

    <p><a href="../job08/index.php">Retour à la liste des produits (job08)</a></p>
</body>

</html>