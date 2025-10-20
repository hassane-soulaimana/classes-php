<?php
require_once __DIR__ . '/..//job01/index.php';
require_once __DIR__ . '/../job14/SockableInterface.php';

class Clothing extends Product implements SockableInterface
{
    private string $size;
    private string $color;
    private string $type;
    private int $material_fee;


    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->size = '';
        $this->color = '';
        $this->type = '';
        $this->material_fee = 0;
    }

    // Getters
    public function getSize(): string
    {
        return $this->size;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMaterialFee(): int
    {
        return $this->material_fee;
    }

    // Setters
    public function setSize(string $v): self
    {
        $this->size = $v;
        return $this;
    }

    public function setColor(string $v): self
    {
        $this->color = $v;
        return $this;
    }

    public function setType(string $v): self
    {
        $this->type = $v;
        return $this;
    }

    public function setMaterialFee(int $v): self
    {
        $this->material_fee = $v;
        return $this;
    }

    public static function findByProductId(int $productId): ?Clothing
    {
        $product = Product::findOneById($productId);
        if (! $product) {
            return null;
        }

        require_once __DIR__ . '/../config/db.php';
        $mysqli = db_connect();
        if ($mysqli === null) {
            return null;
        }

        $stmt = $mysqli->prepare('SELECT size, color, type, material_fee FROM clothing WHERE product_id = ?');
        if ($stmt === false) {
            $mysqli->close();
            return null;
        }
        $stmt->bind_param('i', $productId);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $stmt->close();
            $mysqli->close();
            return null;
        }
        $row = $res->fetch_assoc();
        $stmt->close();
        $mysqli->close();

        $c = new Clothing($product->getId());
        $c->setSize($row['size'] ?? '')
            ->setColor($row['color'] ?? '')
            ->setType($row['type'] ?? '')
            ->setMaterialFee(isset($row['material_fee']) ? (int)$row['material_fee'] : 0)
            ->setName($product->getName())
            ->setPhotos($product->getPhotos())
            ->setPrice($product->getPrice())
            ->setDescription($product->getDescription())
            ->setQuantity($product->getQuantity())
            ->setCreatedAt($product->getCreatedAt())
            ->setUpdatedAt($product->getUpdatedAt())
            ->setCategoryId($product->getCategoryId());
        return $c;
    }

    public static function findOneById(int $id)
    {
        $product = Product::findOneById($id);
        if ($product === false) {
            return false;
        }

        require_once __DIR__ . '/../config/db.php';
        $mysqli = db_connect();
        if ($mysqli === null) {
            return false;
        }
        $stmt = $mysqli->prepare('SELECT size, color, type, material_fee FROM clothing WHERE product_id = ?');
        if ($stmt === false) {
            $mysqli->close();
            return false;
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $stmt->close();
            $mysqli->close();
            return false;
        }
        $row = $res->fetch_assoc();
        $stmt->close();
        $mysqli->close();

        $c = new Clothing($product->getId());
        $c->setSize($row['size'] ?? '')
            ->setColor($row['color'] ?? '')
            ->setType($row['type'] ?? '')
            ->setMaterialFee(isset($row['material_fee']) ? (int)$row['material_fee'] : 0)
            ->setName($product->getName())
            ->setPhotos($product->getPhotos())
            ->setPrice($product->getPrice())
            ->setDescription($product->getDescription())
            ->setQuantity($product->getQuantity())
            ->setCreatedAt($product->getCreatedAt())
            ->setUpdatedAt($product->getUpdatedAt())
            ->setCategoryId($product->getCategoryId());
        return $c;
    }

    public static function findAll(): array
    {
        require_once __DIR__ . '/../config/db.php';
        $mysqli = db_connect();
        if ($mysqli === null) return [];
        $sql = 'SELECT p.id FROM product p JOIN clothing c ON p.id = c.product_id ORDER BY p.id';
        $res = $mysqli->query($sql);
        if ($res === false) {
            $mysqli->close();
            return [];
        }
        $out = [];
        while ($r = $res->fetch_assoc()) {
            $id = isset($r['id']) ? (int)$r['id'] : null;
            if ($id !== null) {
                $c = self::findOneById($id);
                if ($c !== false && $c !== null) $out[] = $c;
            }
        }
        $res->close();
        $mysqli->close();
        return $out;
    }

    public function create()
    {
        $res = parent::create();
        if ($res === false) return false;
        return $this->saveExtra();
    }


    public function update()
    {
        $ok = parent::update();
        if ($ok === false) return false;
        return $this->saveExtra();
    }

    public function saveExtra(): bool
    {
        $pid = $this->getId();
        if ($pid === null) {
            return false;
        }

        require_once __DIR__ . '/../config/db.php';
        $mysqli = db_connect();
        if ($mysqli === null) {
            return false;
        }

        $sql = "INSERT INTO clothing (product_id, size, color, type, material_fee) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE size = VALUES(size), color = VALUES(color), type = VALUES(type), material_fee = VALUES(material_fee)";
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            $mysqli->close();
            return false;
        }

        $s = $this->size;
        $c = $this->color;
        $t = $this->type;
        $m = $this->material_fee;
        $stmt->bind_param('isssi', $pid, $s, $c, $t, $m);
        $ok = $stmt->execute();
        $stmt->close();
        $mysqli->close();
        return $ok !== false;
    }

    // Stock management
    public function addStocks(int $stock): self
    {
        if ($stock <= 0) return $this;
        $this->setQuantity($this->getQuantity() + $stock);
        return $this;
    }

    public function removeStocks(int $stock): self
    {
        if ($stock <= 0) return $this;
        $new = $this->getQuantity() - $stock;
        $this->setQuantity($new < 0 ? 0 : $new);
        return $this;
    }
}
