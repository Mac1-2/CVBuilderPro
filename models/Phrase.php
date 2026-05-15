<?php
class Phrase {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function search(string $query = '', ?int $industryId = null, ?string $category = null, int $limit = 50, int $offset = 0): array {
        $sql = "SELECT p.*, i.name as industry_name FROM phrase_library p
                LEFT JOIN industries i ON p.industry_id = i.id WHERE 1=1";
        $params = [];

        if ($industryId) {
            $sql .= " AND p.industry_id = ?";
            $params[] = $industryId;
        }
        if ($category) {
            $sql .= " AND p.category = ?";
            $params[] = $category;
        }
        if ($query) {
            $sql .= " AND (p.phrase_text LIKE ? OR p.tags LIKE ?)";
            $params[] = "%$query%";
            $params[] = "%$query%";
        }

        $sql .= " ORDER BY p.is_featured DESC, p.usage_count DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function count(string $query = '', ?int $industryId = null, ?string $category = null): int {
        $sql = "SELECT COUNT(*) FROM phrase_library WHERE 1=1";
        $params = [];
        if ($industryId) { $sql .= " AND industry_id = ?"; $params[] = $industryId; }
        if ($category) { $sql .= " AND category = ?"; $params[] = $category; }
        if ($query) { $sql .= " AND (phrase_text LIKE ? OR tags LIKE ?)"; $params[] = "%$query%"; $params[] = "%$query%"; }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getByIndustry(int $industryId): array {
        $stmt = $this->db->prepare("SELECT p.*, i.name as industry_name FROM phrase_library p LEFT JOIN industries i ON p.industry_id = i.id WHERE p.industry_id = ? ORDER BY p.is_featured DESC, p.category, p.usage_count DESC");
        $stmt->execute([$industryId]);
        return $stmt->fetchAll();
    }

    public function getFeatured(?int $industryId = null, int $limit = 20): array {
        $sql = "SELECT p.*, i.name as industry_name FROM phrase_library p LEFT JOIN industries i ON p.industry_id = i.id WHERE p.is_featured = 1";
        $params = [];
        if ($industryId) { $sql .= " AND p.industry_id = ?"; $params[] = $industryId; }
        $sql .= " ORDER BY p.usage_count DESC LIMIT ?";
        $params[] = $limit;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function incrementUsage(int $phraseId): void {
        $this->db->prepare("UPDATE phrase_library SET usage_count = usage_count + 1 WHERE id = ?")->execute([$phraseId]);
    }

    public function saveFavorite(int $userId, int $phraseId): bool {
        try {
            $stmt = $this->db->prepare("INSERT INTO user_saved_phrases (user_id, phrase_id) VALUES (?, ?)");
            $stmt->execute([$userId, $phraseId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function removeFavorite(int $userId, int $phraseId): bool {
        return $this->db->prepare("DELETE FROM user_saved_phrases WHERE user_id = ? AND phrase_id = ?")->execute([$userId, $phraseId]);
    }

    public function getFavorites(int $userId): array {
        $stmt = $this->db->prepare("SELECT p.*, i.name as industry_name FROM user_saved_phrases usp JOIN phrase_library p ON usp.phrase_id = p.id LEFT JOIN industries i ON p.industry_id = i.id WHERE usp.user_id = ? ORDER BY usp.created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function isFavorite(int $userId, int $phraseId): bool {
        $stmt = $this->db->prepare("SELECT id FROM user_saved_phrases WHERE user_id = ? AND phrase_id = ?");
        $stmt->execute([$userId, $phraseId]);
        return (bool) $stmt->fetch();
    }

    public function getAllIndustries(): array {
        return $this->db->query("SELECT * FROM industries ORDER BY sort_order ASC")->fetchAll();
    }
}
