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
    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setPhotos(array $photos): void
    {
        $this->photos = $photos;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function setCategoryId(?int $category_id): void
    {
        $this->category_id = $category_id;
    }

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
}
