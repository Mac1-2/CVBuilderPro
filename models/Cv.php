<?php
class Cv {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(int $userId, string $title = 'Untitled CV', int $templateId = 1): int {
        $slug = generate_slug($title);
        $stmt = $this->db->prepare("INSERT INTO cvs (user_id, template_id, title, public_slug) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $templateId, $title, $slug]);
        $cvId = $this->db->lastInsertId();

        $this->initSections($cvId);
        return $cvId;
    }

    private function initSections(int $cvId): void {
        $sections = [
            ['personal', 1, 'Personal Information', json_encode(['first_name' => '', 'last_name' => '', 'job_title' => '', 'email' => '', 'phone' => '', 'address' => '', 'linkedin' => '', 'website' => '', 'photo' => ''])],
            ['summary', 2, 'Professional Summary', json_encode(['text' => ''])],
            ['experience', 3, 'Work Experience', json_encode(['entries' => []])],
            ['education', 4, 'Education', json_encode(['entries' => []])],
            ['skills', 5, 'Skills', json_encode(['entries' => []])],
            ['languages', 6, 'Languages', json_encode(['entries' => []])],
            ['certifications', 7, 'Certifications', json_encode(['entries' => []])],
            ['references', 8, 'References', json_encode(['entries' => []])],
        ];

        $stmt = $this->db->prepare("INSERT INTO cv_sections (cv_id, section_type, section_order, title, content_json) VALUES (?, ?, ?, ?, ?)");
        foreach ($sections as $section) {
            $stmt->execute([$cvId, $section[0], $section[1], $section[2], $section[3]]);
        }
    }

    public function getById(int $cvId, ?int $userId = null): ?array {
        $sql = "SELECT c.*, t.name as template_name, t.slug as template_slug, t.html_structure, t.css_styles
                FROM cvs c
                LEFT JOIN cv_templates t ON c.template_id = t.id
                WHERE c.id = ?";
        $params = [$cvId];
        if ($userId) {
            $sql .= " AND c.user_id = ?";
            $params[] = $userId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() ?: null;
    }

    public function getByUser(int $userId): array {
        $stmt = $this->db->prepare("SELECT c.*, t.name as template_name, t.slug as template_slug
                                     FROM cvs c
                                     LEFT JOIN cv_templates t ON c.template_id = t.id
                                     WHERE c.user_id = ?
                                     ORDER BY c.updated_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function update(int $cvId, int $userId, array $data): bool {
        $fields = [];
        $values = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['title', 'template_id', 'custom_css', 'is_public'])) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        if ($key === 'custom_colors' && isset($data['custom_colors'])) {
            $fields[] = "custom_colors = ?";
            $values[] = json_encode($data['custom_colors']);
        }
        if (empty($fields)) return false;
        $values[] = $cvId;
        $values[] = $userId;
        return $this->db->prepare("UPDATE cvs SET " . implode(', ', $fields) . " WHERE id = ? AND user_id = ?")->execute($values);
    }

    public function delete(int $cvId, int $userId): bool {
        return $this->db->prepare("DELETE FROM cvs WHERE id = ? AND user_id = ?")->execute([$cvId, $userId]);
    }

    public function getSection(int $cvId, string $sectionType): ?array {
        $stmt = $this->db->prepare("SELECT * FROM cv_sections WHERE cv_id = ? AND section_type = ?");
        $stmt->execute([$cvId, $sectionType]);
        return $stmt->fetch() ?: null;
    }

    public function getAllSections(int $cvId): array {
        $stmt = $this->db->prepare("SELECT * FROM cv_sections WHERE cv_id = ? ORDER BY section_order ASC");
        $stmt->execute([$cvId]);
        return $stmt->fetchAll();
    }

    public function saveSection(int $cvId, string $sectionType, array $content, ?string $title = null): bool {
        $existing = $this->getSection($cvId, $sectionType);
        if ($existing) {
            $sql = "UPDATE cv_sections SET content_json = ?, html_content = ?, updated_at = NOW()";
            $params = [json_encode($content), null];
            if ($title !== null) {
                $sql .= ", title = ?";
                $params[] = $title;
            }
            $sql .= " WHERE cv_id = ? AND section_type = ?";
            $params[] = $cvId;
            $params[] = $sectionType;
            return $this->db->prepare($sql)->execute($params);
        }
        return false;
    }

    public function getFullData(int $cvId, ?int $userId = null): ?array {
        $cv = $this->getById($cvId, $userId);
        if (!$cv) return null;
        $cv['sections'] = $this->getAllSections($cvId);
        return $cv;
    }

    public function duplicate(int $cvId, int $userId): int {
        $cv = $this->getById($cvId, $userId);
        if (!$cv) return 0;

        $newId = $this->create($userId, $cv['title'] . ' (Copy)', $cv['template_id']);
        $sections = $this->getAllSections($cvId);

        $stmt = $this->db->prepare("UPDATE cv_sections SET content_json = ? WHERE id = ?");
        foreach ($sections as $section) {
            $stmt->execute([$section['content_json'], $this->getSectionIdByType($newId, $section['section_type'])]);
        }

        return $newId;
    }

    private function getSectionIdByType(int $cvId, string $sectionType): ?int {
        $stmt = $this->db->prepare("SELECT id FROM cv_sections WHERE cv_id = ? AND section_type = ?");
        $stmt->execute([$cvId, $sectionType]);
        $row = $stmt->fetch();
        return $row ? $row['id'] : null;
    }

    public function countByUser(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM cvs WHERE user_id = ?");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }
}
