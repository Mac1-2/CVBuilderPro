<?php
class TemplateBlock {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getByTemplate(int $templateId): array {
        $stmt = $this->db->prepare("SELECT * FROM template_blocks WHERE template_id = ? ORDER BY block_order ASC");
        $stmt->execute([$templateId]);
        return $stmt->fetchAll();
    }

    public function getById(int $blockId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM template_blocks WHERE id = ?");
        $stmt->execute([$blockId]);
        return $stmt->fetch() ?: null;
    }

    public function create(int $templateId, string $type, array $config = [], int $order = 0, ?int $parentId = null): int {
        $stmt = $this->db->prepare("INSERT INTO template_blocks (template_id, block_type, block_order, parent_id, config_json, css_overrides) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$templateId, $type, $order, $parentId, json_encode($config), json_encode([])]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $blockId, array $config, array $cssOverrides = []): bool {
        $stmt = $this->db->prepare("UPDATE template_blocks SET config_json = ?, css_overrides = ? WHERE id = ?");
        return $stmt->execute([json_encode($config), json_encode($cssOverrides), $blockId]);
    }

    public function delete(int $blockId): bool {
        return $this->db->prepare("DELETE FROM template_blocks WHERE id = ?")->execute([$blockId]);
    }

    public function reorder(int $templateId, array $orderedIds): bool {
        $stmt = $this->db->prepare("UPDATE template_blocks SET block_order = ? WHERE id = ? AND template_id = ?");
        foreach ($orderedIds as $order => $id) {
            $stmt->execute([$order, $id, $templateId]);
        }
        return true;
    }

    public function duplicate(int $blockId): int {
        $block = $this->getById($blockId);
        if (!$block) return 0;
        $stmt = $this->db->prepare("INSERT INTO template_blocks (template_id, block_type, block_order, parent_id, config_json, css_overrides) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$block['template_id'], $block['block_type'], $block['block_order'] + 1, $block['parent_id'], $block['config_json'], $block['css_overrides']]);
        return (int) $this->db->lastInsertId();
    }

    public function getMaxOrder(int $templateId): int {
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(block_order), -1) FROM template_blocks WHERE template_id = ?");
        $stmt->execute([$templateId]);
        return (int) $stmt->fetchColumn();
    }

    public function createCustomTemplate(int $userId, string $name, ?int $baseTemplateId = null): int {
        $slug = generate_slug($name);
        $baseHtml = '';
        $baseCss = '';
        $baseGlobal = null;
        if ($baseTemplateId) {
            $stmt = $this->db->prepare("SELECT html_structure, css_styles, global_styles FROM cv_templates WHERE id = ?");
            $stmt->execute([$baseTemplateId]);
            $base = $stmt->fetch();
            $baseHtml = $base['html_structure'] ?? '';
            $baseCss = $base['css_styles'] ?? '';
            $baseGlobal = $base['global_styles'];
        }
        $stmt = $this->db->prepare("INSERT INTO cv_templates (name, slug, description, category, html_structure, css_styles, is_custom, user_id, base_template_id, global_styles) VALUES (?, ?, ?, 'professional', ?, ?, 1, ?, ?, ?)");
        $stmt->execute([$name, $slug, 'Custom template', $baseHtml, $baseCss, $userId, $baseTemplateId, $baseGlobal]);
        return (int) $this->db->lastInsertId();
    }

    public function cloneTemplateWithBlocks(int $templateId, int $userId, string $newName): int {
        $newTemplateId = $this->createCustomTemplate($userId, $newName, $templateId);
        $blocks = $this->getByTemplate($templateId);
        foreach ($blocks as $block) {
            $stmt = $this->db->prepare("INSERT INTO template_blocks (template_id, block_type, block_order, parent_id, config_json, css_overrides) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$newTemplateId, $block['block_type'], $block['block_order'], $block['parent_id'], $block['config_json'], $block['css_overrides']]);
        }
        return $newTemplateId;
    }

    public function uploadGraphic(int $templateId, array $file): array {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['svg', 'png', 'jpg', 'jpeg', 'webp'];
        if (!in_array($ext, $allowed)) {
            return ['error' => 'Invalid file type. Allowed: ' . implode(', ', $allowed)];
        }
        if ($ext === 'jpeg') $ext = 'jpg';
        $fileName = uniqid('graphic_') . '.' . $ext;
        $destPath = UPLOAD_PATH . '/graphics/uploads/' . $fileName;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return ['error' => 'Failed to upload file'];
        }
        $stmt = $this->db->prepare("INSERT INTO template_graphics (template_id, user_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$templateId, $_SESSION['user_id'] ?? null, $file['name'], '/uploads/graphics/uploads/' . $fileName, $ext, $file['size']]);
        return ['success' => true, 'id' => $this->db->lastInsertId(), 'path' => '/uploads/graphics/uploads/' . $fileName, 'type' => $ext];
    }

    public function getGraphics(?int $templateId = null): array {
        $sql = "SELECT * FROM template_graphics";
        $params = [];
        if ($templateId) {
            $sql .= " WHERE template_id = ? OR user_id = ?";
            $params = [$templateId, $_SESSION['user_id'] ?? 0];
        }
        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function deleteGraphic(int $graphicId): bool {
        $stmt = $this->db->prepare("SELECT file_path FROM template_graphics WHERE id = ?");
        $stmt->execute([$graphicId]);
        $graphic = $stmt->fetch();
        if ($graphic && file_exists(BASE_PATH . $graphic['file_path'])) {
            unlink(BASE_PATH . $graphic['file_path']);
        }
        return $this->db->prepare("DELETE FROM template_graphics WHERE id = ?")->execute([$graphicId]);
    }

    public function getGlobalStyles(int $templateId): ?array {
        $stmt = $this->db->prepare("SELECT global_styles FROM cv_templates WHERE id = ?");
        $stmt->execute([$templateId]);
        $row = $stmt->fetch();
        return $row && $row['global_styles'] ? json_decode($row['global_styles'], true) : null;
    }

    public function saveGlobalStyles(int $templateId, array $styles): bool {
        $stmt = $this->db->prepare("UPDATE cv_templates SET global_styles = ? WHERE id = ?");
        return $stmt->execute([json_encode($styles), $templateId]);
    }

    public function regenerateTemplate(int $templateId): bool {
        $blocks = $this->getByTemplate($templateId);
        if (empty($blocks)) return false;

        $globalStyles = $this->getGlobalStyles($templateId) ?? [];
        $renderer = new BlockRenderer();
        $result = $renderer->renderFromBlocks($blocks, $globalStyles);

        $stmt = $this->db->prepare("UPDATE cv_templates SET html_structure = ?, css_styles = ?, global_styles = ? WHERE id = ?");
        return $stmt->execute([$result['html'], $result['css'], json_encode($globalStyles), $templateId]);
    }
}
