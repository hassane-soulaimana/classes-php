<?php
require_once __DIR__ . '/..//job01/index.php';
require_once __DIR__ . '/../job14/SockableInterface.php';

class Electronic extends Product implements SockableInterface
{
    private string $brand;
    private int $waranty_fee;

    public function __construct(?int $id = null)
    {
        parent::__construct($id);
        $this->brand = '';
        $this->waranty_fee = 0;
    }

    // Getters
    public function getBrand(): string
    {
        return $this->brand;
    }

    public function getWarantyFee(): int
    {
        return $this->waranty_fee;
    }

    // Setters
    public function setBrand(string $v): self
    {
        $this->brand = $v;
        return $this;
    }

    public function setWarantyFee(int $v): self
    {
        $this->waranty_fee = $v;
        return $this;
    }

    public static function findByProductId(int $productId): ?Electronic
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

        $stmt = $mysqli->prepare('SELECT brand, waranty_fee FROM electronic WHERE product_id = ?');
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

        $e = new Electronic($product->getId());
        $e->setBrand($row['brand'] ?? '')
            ->setWarantyFee(isset($row['waranty_fee']) ? (int)$row['waranty_fee'] : 0)
            ->setName($product->getName())
            ->setPhotos($product->getPhotos())
            ->setPrice($product->getPrice())
            ->setDescription($product->getDescription())
            ->setQuantity($product->getQuantity())
            ->setCreatedAt($product->getCreatedAt())
            ->setUpdatedAt($product->getUpdatedAt())
            ->setCategoryId($product->getCategoryId());

        return $e;
    }

    public static function findOneById(int $id)
    {
        $product = Product::findOneById($id);
        if ($product === false) return false;

        require_once __DIR__ . '/../config/db.php';
        $mysqli = db_connect();
        if ($mysqli === null) return false;

        $stmt = $mysqli->prepare('SELECT brand, waranty_fee FROM electronic WHERE product_id = ?');
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

        $e = new Electronic($product->getId());
        $e->setBrand($row['brand'] ?? '')
            ->setWarantyFee(isset($row['waranty_fee']) ? (int)$row['waranty_fee'] : 0)
            ->setName($product->getName())
            ->setPhotos($product->getPhotos())
            ->setPrice($product->getPrice())
            ->setDescription($product->getDescription())
            ->setQuantity($product->getQuantity())
            ->setCreatedAt($product->getCreatedAt())
            ->setUpdatedAt($product->getUpdatedAt())
            ->setCategoryId($product->getCategoryId());
        return $e;
    }

    public static function findAll(): array
    {
        require_once __DIR__ . '/../config/db.php';
        $mysqli = db_connect();
        if ($mysqli === null) return [];
        $sql = 'SELECT p.id FROM product p JOIN electronic e ON p.id = e.product_id ORDER BY p.id';
        $res = $mysqli->query($sql);
        if ($res === false) {
            $mysqli->close();
            return [];
        }
        $out = [];
        while ($r = $res->fetch_assoc()) {
            $id = isset($r['id']) ? (int)$r['id'] : null;
            if ($id !== null) {
                $e = self::findOneById($id);
                if ($e !== false && $e !== null) $out[] = $e;
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

        $sql = "INSERT INTO electronic (product_id, brand, waranty_fee) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE brand = VALUES(brand), waranty_fee = VALUES(waranty_fee)";
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            $mysqli->close();
            return false;
        }

        $b = $this->brand;
        $w = $this->waranty_fee;
        $stmt->bind_param('isi', $pid, $b, $w);
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
