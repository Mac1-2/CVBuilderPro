<?php
class Template {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(?string $category = null): array {
        $sql = "SELECT * FROM cv_templates WHERE is_active = 1";
        $params = [];
        if ($category) {
            $sql .= " AND category = ?";
            $params[] = $category;
        }
        $sql .= " ORDER BY sort_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("SELECT * FROM cv_templates WHERE slug = ? AND is_active = 1");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    public function getById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM cv_templates WHERE id = ? AND is_active = 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getCategories(): array {
        return $this->db->query("SELECT DISTINCT category FROM cv_templates WHERE is_active = 1 ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
    }
}
