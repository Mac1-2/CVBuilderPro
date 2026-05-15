<?php
require_once __DIR__ . '/../includes/BlockRenderer.php';

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $input['action'] ?? $route[1] ?? '';

$cvModel = new Cv();
$phraseModel = new Phrase();
$templateModel = new Template();
$blockModel = new TemplateBlock();

switch ($action) {
    case 'save-section':
        $cvId = (int)($input['cv_id'] ?? 0);
        $sectionType = $input['section_type'] ?? '';
        $content = $input['content'] ?? [];
        $title = $input['title'] ?? null;

        $cv = $cvModel->getById($cvId, $_SESSION['user_id']);
        if (!$cv) { json_response(['error' => 'CV not found'], 404); }

        $result = $cvModel->saveSection($cvId, $sectionType, $content, $title);
        json_response(['success' => $result]);
        break;

    case 'load-section':
        $cvId = (int)($input['cv_id'] ?? 0);
        $sectionType = $input['section_type'] ?? '';

        $cv = $cvModel->getById($cvId, $_SESSION['user_id']);
        if (!$cv) { json_response(['error' => 'CV not found'], 404); }

        $section = $cvModel->getSection($cvId, $sectionType);
        json_response(['success' => true, 'section' => $section]);
        break;

    case 'load-cv':
        $cvId = (int)($input['cv_id'] ?? 0);
        $data = $cvModel->getFullData($cvId, $_SESSION['user_id']);
        if (!$data) { json_response(['error' => 'CV not found'], 404); }
        json_response(['success' => true, 'cv' => $data]);
        break;

    case 'apply-template':
        $cvId = (int)($input['cv_id'] ?? 0);
        $templateId = (int)($input['template_id'] ?? 0);

        $cv = $cvModel->getById($cvId, $_SESSION['user_id']);
        $template = $templateModel->getById($templateId);
        if (!$cv || !$template) { json_response(['error' => 'Not found'], 404); }

        $cvModel->update($cvId, $_SESSION['user_id'], ['template_id' => $templateId]);
        json_response(['success' => true, 'template' => $template]);
        break;

    case 'get-phrases':
        $search = $input['q'] ?? '';
        $industryId = isset($input['industry_id']) ? (int)$input['industry_id'] : null;
        $category = $input['category'] ?? null;
        $limit = (int)($input['limit'] ?? 50);

        $phrases = $phraseModel->search($search, $industryId, $category, $limit);
        json_response(['success' => true, 'phrases' => $phrases]);
        break;

    case 'favorite-phrase':
        $phraseId = (int)($input['phrase_id'] ?? 0);
        $isFav = $phraseModel->isFavorite($_SESSION['user_id'], $phraseId);

        if ($isFav) {
            $phraseModel->removeFavorite($_SESSION['user_id'], $phraseId);
            json_response(['success' => true, 'favorited' => false]);
        } else {
            $phraseModel->saveFavorite($_SESSION['user_id'], $phraseId);
            json_response(['success' => true, 'favorited' => true]);
        }
        break;

    case 'increment-phrase-usage':
        $phraseId = (int)($input['phrase_id'] ?? 0);
        $phraseModel->incrementUsage($phraseId);
        json_response(['success' => true]);
        break;

    case 'duplicate-cv':
        $cvId = (int)($input['cv_id'] ?? 0);
        $newId = $cvModel->duplicate($cvId, $_SESSION['user_id']);
        json_response(['success' => $newId > 0, 'new_id' => $newId]);
        break;

    case 'delete-cv':
        $cvId = (int)($input['cv_id'] ?? 0);
        $result = $cvModel->delete($cvId, $_SESSION['user_id']);
        json_response(['success' => $result]);
        break;

    case 'preview-template':
        $slug = $_GET['slug'] ?? '';
        $template = $templateModel->getBySlug($slug);
        if (!$template) { json_response(['error' => 'Template not found'], 404); }

        $sampleData = ['first_name' => 'Alexandra', 'last_name' => 'Mitchell', 'first_initial' => 'A', 'last_initial' => 'M', 'job_title' => 'Senior Product Manager', 'email' => 'alex@email.com', 'phone' => '+1 (555) 123-4567', 'address' => 'San Francisco, CA'];
        $html = str_replace(['{{first_name}}', '{{last_name}}', '{{first_initial}}', '{{last_initial}}', '{{job_title}}', '{{email}}', '{{phone}}', '{{address}}', '{{linkedin}}', '{{website}}', '{{photo}}'], array_values($sampleData), $template['html_structure']);
        $html = str_replace('{{contact_entries}}', '<div style="font-size:12px;color:#666;">alex@email.com &bull; +1 (555) 123-4567 &bull; San Francisco, CA</div>', $html);
        $html = str_replace('{{summary}}', '<p style="font-size:13px;line-height:1.7;">Results-driven product manager with 8+ years of experience leading cross-functional teams to deliver innovative digital products.</p>', $html);
        $html = str_replace('{{experience_entries}}', '<div style="margin-bottom:15px;"><div style="font-weight:bold;font-size:14px;">Senior Product Manager</div><div style="color:#4f46e5;font-size:13px;">TechCorp Inc.</div><div style="color:#888;font-size:12px;">Jan 2021 - Present</div><div style="font-size:13px;line-height:1.6;margin-top:4px;">Led product strategy for SaaS platform serving 500K+ users.</div></div><div style="margin-bottom:15px;"><div style="font-weight:bold;font-size:14px;">Product Manager</div><div style="color:#4f46e5;font-size:13px;">StartupXYZ</div><div style="color:#888;font-size:12px;">Mar 2018 - Dec 2020</div><div style="font-size:13px;line-height:1.6;margin-top:4px;">Managed product lifecycle from ideation to launch.</div></div>', $html);
        $html = str_replace('{{education_entries}}', '<div style="margin-bottom:10px;"><div style="font-weight:bold;font-size:14px;">MBA, Business Administration</div><div style="color:#4f46e5;font-size:13px;">Stanford University</div><div style="color:#888;font-size:12px;">2016 - 2018</div></div><div style="margin-bottom:10px;"><div style="font-weight:bold;font-size:14px;">BS, Computer Science</div><div style="color:#4f46e5;font-size:13px;">UC Berkeley</div><div style="color:#888;font-size:12px;">2012 - 2016</div></div>', $html);
        $html = str_replace('{{skills_entries}}', '<div style="display:flex;flex-wrap:wrap;gap:8px;"><span style="background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;">Product Strategy</span><span style="background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;">Agile/Scrum</span><span style="background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;">Data Analysis</span><span style="background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;">SQL</span></div>', $html);
        $html = str_replace(['{{language_entries}}', '{{certification_entries}}', '{{project_entries}}', '{{publication_entries}}', '{{skill_group_entries}}', '{{highlight_entries}}', '{{interest_entries}}'], '', $html);

        json_response(['success' => true, 'template' => ['name' => $template['name'], 'slug' => $template['slug']], 'preview_html' => "<style>{$template['css_styles']}</style>" . $html]);
        break;

    case 'save-cv-title':
        $cvId = (int)($input['cv_id'] ?? 0);
        $title = trim($input['title'] ?? '');
        if ($title) {
            $cvModel->update($cvId, $_SESSION['user_id'], ['title' => $title]);
        }
        json_response(['success' => true]);
        break;

    case 'create-template':
        $name = trim($input['name'] ?? 'My Template');
        $baseId = (int)($input['base_template_id'] ?? 0);
        $newId = $blockModel->createCustomTemplate($_SESSION['user_id'], $name, $baseId ?: null);
        json_response(['success' => true, 'template_id' => $newId]);
        break;

    case 'load-template-blocks':
        $templateId = (int)($input['template_id'] ?? 0);
        $blocks = $blockModel->getByTemplate($templateId);
        $globalStyles = $blockModel->getGlobalStyles($templateId) ?? [];
        json_response(['success' => true, 'blocks' => $blocks, 'global_styles' => $globalStyles]);
        break;

    case 'save-block':
        $blockId = (int)($input['block_id'] ?? 0);
        $templateId = (int)($input['template_id'] ?? 0);
        $type = $input['block_type'] ?? '';
        $config = $input['config'] ?? [];
        $cssOverrides = $input['css_overrides'] ?? [];
        $order = (int)($input['block_order'] ?? 0);
        $parentId = isset($input['parent_id']) ? (int)$input['parent_id'] : null;

        if ($blockId > 0) {
            $blockModel->update($blockId, $config, $cssOverrides);
            json_response(['success' => true, 'block_id' => $blockId]);
        } else {
            $newId = $blockModel->create($templateId, $type, $config, $order, $parentId);
            json_response(['success' => true, 'block_id' => $newId]);
        }
        break;

    case 'delete-block':
        $blockId = (int)($input['block_id'] ?? 0);
        json_response(['success' => $blockModel->delete($blockId)]);
        break;

    case 'reorder-blocks':
        $templateId = (int)($input['template_id'] ?? 0);
        $orderedIds = $input['ordered_ids'] ?? [];
        json_response(['success' => $blockModel->reorder($templateId, $orderedIds)]);
        break;

    case 'duplicate-block':
        $blockId = (int)($input['block_id'] ?? 0);
        $newId = $blockModel->duplicate($blockId);
        json_response(['success' => $newId > 0, 'block_id' => $newId]);
        break;

    case 'regenerate-template':
        $templateId = (int)($input['template_id'] ?? 0);
        $result = $blockModel->regenerateTemplate($templateId);
        $template = $templateModel->getById($templateId);
        json_response(['success' => $result, 'template' => $template]);
        break;

    case 'upload-graphic':
        if (isset($_FILES['graphic'])) {
            $templateId = (int)($input['template_id'] ?? 0);
            $result = $blockModel->uploadGraphic($templateId, $_FILES['graphic']);
            json_response($result, $result['error'] ? 400 : 200);
        }
        json_response(['error' => 'No file uploaded'], 400);
        break;

    case 'list-graphics':
        $templateId = (int)($input['template_id'] ?? 0);
        json_response(['success' => true, 'graphics' => $blockModel->getGraphics($templateId)]);
        break;

    case 'delete-graphic':
        $graphicId = (int)($input['graphic_id'] ?? 0);
        json_response(['success' => $blockModel->deleteGraphic($graphicId)]);
        break;

    case 'save-global-styles':
        $templateId = (int)($input['template_id'] ?? 0);
        $styles = $input['styles'] ?? [];
        json_response(['success' => $blockModel->saveGlobalStyles($templateId, $styles)]);
        break;

    case 'clone-template':
        $templateId = (int)($input['template_id'] ?? 0);
        $newName = trim($input['name'] ?? 'Copy');
        $newId = $blockModel->cloneTemplateWithBlocks($templateId, $_SESSION['user_id'], $newName);
        json_response(['success' => $newId > 0, 'template_id' => $newId]);
        break;

    default:
        json_response(['error' => 'Unknown action'], 400);
        break;
}
