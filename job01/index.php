<?php
class Product
{
    private ?int $id;
    private string $name;
    private array $photos;
    private int $price;
    private string $description;
    private int $quantity;
    private DateTime $createdAt;
    private DateTime $updatedAt;
    private ?int $category_id;

    public function __construct(
        ?int $id = null,
        string $name = '',
        array $photos = [],
        int $price = 0,
        string $description = '',
        int $quantity = 0,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null,
        ?int $category_id = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->photos = $photos;
        $this->price = $price;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
        $this->category_id = $category_id;
    }

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhotos(): array
    {
        return $this->photos;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }
    public function getCategoryId(): ?int
    {
        return $this->category_id;
    }

    // Setters
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setPhotos(array $photos): self
    {
        $this->photos = $photos;
        return $this;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setCategoryId(?int $category_id): self
    {
        $this->category_id = $category_id;
        return $this;
    }

    // GetCategory
    public function getCategory(): ?Category
    {
        if ($this->category_id === null) {
            return null;
        }

        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $dbname = 'draft-shop';

        $mysqli = new mysqli($host, $user, $pass, $dbname);
        if ($mysqli->connect_errno) {
            return null;
        }

        $stmt = $mysqli->prepare('SELECT id, name, description, created_at, updated_at FROM category WHERE id = ?');
        $stmt->bind_param('i', $this->category_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $mysqli->close();
            return null;
        }

        $row = $result->fetch_assoc();

        if (!class_exists('Category')) {
            require_once __DIR__ . '/../job02/index.php';
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

        $category = new Category(
            isset($row['id']) ? (int)$row['id'] : null,
            $row['name'] ?? '',
            $row['description'] ?? '',
            $createdAt,
            $updatedAt
        );

        $stmt->close();
        $mysqli->close();

        return $category;
    }


    // FinderoneByid
    public static function findOneById(int $id)
    {
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $dbname = 'draft-shop';

        $mysqli = new mysqli($host, $user, $pass, $dbname);
        if ($mysqli->connect_errno) {
            return false;
        }

        $stmt = $mysqli->prepare('SELECT id, name, photos, price, description, quantity, created_at, updated_at, category_id FROM product WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $mysqli->close();
            return false;
        }

        $row = $result->fetch_assoc();

        // photos
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

        $stmt->close();
        $mysqli->close();

        return $product;
    }

    // TAbleau Array
    public static function findAll(): array
    {
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $dbname = 'draft-shop';

        $mysqli = new mysqli($host, $user, $pass, $dbname);
        if ($mysqli->connect_errno) {
            return [];
        }

        $sql = 'SELECT id, name, photos, price, description, quantity, created_at, updated_at, category_id FROM product';
        $result = $mysqli->query($sql);
        if ($result === false) {
            $mysqli->close();
            return [];
        }

        $products = [];
        while ($row = $result->fetch_assoc()) {
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

            $products[] = $product;
        }

        $result->free();
        $mysqli->close();
        return $products;
    }
    // Fonction create 
    public function create()
    {
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $dbname = 'draft-shop';

        $mysqli = new mysqli($host, $user, $pass, $dbname);
        if ($mysqli->connect_errno) {
            return false;
        }

        $sql = 'INSERT INTO product (name, photos, price, description, quantity, created_at, updated_at, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            $mysqli->close();
            return false;
        }

        $photosJson = json_encode($this->photos);
        // Ne pas modifier created_at lors d'un update ; forcer updated_at à maintenant
        $updatedAt = (new DateTime())->format('Y-m-d H:i:s');

        $catId = $this->category_id;

        $name = $this->name;
        $price = $this->price;
        $description = $this->description;
        $quantity = $this->quantity;




        $stmt->bind_param('ssisissi', $name, $photosJson, $price, $description, $quantity, $createdAt, $updatedAt, $catId);

        $ok = $stmt->execute();
        if (! $ok) {
            $stmt->close();
            $mysqli->close();
            return false;
        }

        $this->id = $mysqli->insert_id;

        $stmt->close();
        $mysqli->close();

        return $this;
    }

    public function update()
    {
        // Very simple update: update basic fields for the current product id.
        // Returns true on success, false on failure.
        if ($this->id === null) {
            return false;
        }

        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $dbname = 'draft-shop';

        $mysqli = new mysqli($host, $user, $pass, $dbname);
        if ($mysqli->connect_errno) {
            return false;
        }

        // Update only simple columns to keep the method easy to understand
        $sql = 'UPDATE product SET name = ?, price = ?, description = ?, quantity = ?, updated_at = ? WHERE id = ?';
        $stmt = $mysqli->prepare($sql);
        if ($stmt === false) {
            $mysqli->close();
            return false;
        }

        $name = $this->name;
        $price = $this->price;
        $description = $this->description;
        $quantity = $this->quantity;
        $updatedAt = (new DateTime())->format('Y-m-d H:i:s');
        $id = $this->id;

        // types: s (string), i (integer), s (string), i (integer), s (string), i (integer)
        $types = 'sisisi';
        $ok = $stmt->bind_param($types, $name, $price, $description, $quantity, $updatedAt, $id);
        if ($ok === false) {
            $stmt->close();
            $mysqli->close();
            return false;
        }

        if ($stmt->execute() === false) {
            $stmt->close();
            $mysqli->close();
            return false;
        }

        $stmt->close();
        $mysqli->close();

        return true;
    }
}
