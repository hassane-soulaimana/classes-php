<?php

class Category
{
    private ?int $id;
    private string $name;
    private string $description;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        ?int $id = null,
        string $name = "",
        string $description = "",
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    // Getters

    public function getID(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdateAt(): DateTime
    {
        return $this->updatedAt;
    }

    // Setters

    public function setID(?int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }
    public function setUpdateat(DateTime $updateat): void
    {
        $this->updatedAt = $updateat;
    }

// tableau d'instances Product
    public function getProducts(): array
    {
        if ($this->id === null) {
            return [];
        }

        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $dbname = 'draft-shop';

        $mysqli = new mysqli($host, $user, $pass, $dbname);
        if ($mysqli->connect_errno) {
            return [];
        }

        $stmt = $mysqli->prepare('SELECT id, name, photos, price, description, quantity, created_at, updated_at, category_id FROM product WHERE category_id = ?');
        $stmt->bind_param('i', $this->id);
        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];

        if (!class_exists('Product')) {
            require_once __DIR__ . '/../job01/index.php';
        }

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

        $stmt->close();
        $mysqli->close();

        return $products;
    }
}
