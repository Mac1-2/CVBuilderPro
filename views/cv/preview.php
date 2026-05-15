<?php
$pageTitle = 'Preview: ' . $cv['title'];
$sections = $cv['sections'];
$data = [];
foreach ($sections as $s) {
    $data[$s['section_type']] = json_decode($s['content_json'], true);
}
$personal = $data['personal'] ?? [];
$fullName = trim(($personal['first_name'] ?? '') . ' ' . ($personal['last_name'] ?? ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <style>
        body{background:#e5e7eb;margin:0;padding:20px;display:flex;flex-direction:column;align-items:center;}
        .preview-bar{position:fixed;top:0;left:0;right:0;background:#1f2937;color:#fff;padding:10px 24px;display:flex;align-items:center;justify-content:space-between;z-index:100;}
        .preview-bar a{color:#fff;text-decoration:none;padding:6px 14px;border-radius:6px;font-size:14px;}
        .preview-bar a:hover{background:rgba(255,255,255,0.1);}
        .preview-bar .btn-pdf{background:#ef4444;}
        .preview-bar .btn-word{background:#22c55e;}
        .cv-page{background:#fff;width:210mm;min-height:297mm;margin:60px auto 20px;box-shadow:0 4px 20px rgba(0,0,0,0.15);padding:0;overflow:hidden;}
        <?= $cv['css_styles'] ?>
    </style>
</head>
<body>
    <div class="preview-bar">
        <div>
            <a href="<?= BASE_URL ?>/cv/edit/<?= $cv['id'] ?>?mode=wizard"><i class="fas fa-arrow-left"></i> Back to Editor</a>
            <a href="<?= BASE_URL ?>/cv/edit/<?= $cv['id'] ?>?mode=wysiwyg">WYSIWYG Mode</a>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="<?= BASE_URL ?>/export/pdf/<?= $cv['id'] ?>" class="btn-pdf"><i class="fas fa-file-pdf"></i> Export PDF</a>
            <a href="<?= BASE_URL ?>/export/word/<?= $cv['id'] ?>" class="btn-word"><i class="fas fa-file-word"></i> Export Word</a>
        </div>
    </div>

    <div class="cv-page">
        <?= renderCvContent($cv, $data) ?>
    </div>
</body>
</html>

<?php
function renderCvContent(array $cv, array $data): string {
    $personal = $data['personal'] ?? [];
    $fullName = trim(($personal['first_name'] ?? '') . ' ' . ($personal['last_name'] ?? ''));
    $firstInitial = strtoupper(substr($personal['first_name'] ?? '', 0, 1));
    $lastInitial = strtoupper(substr($personal['last_name'] ?? '', 0, 1));
    $contactParts = array_filter([$personal['email'] ?? '', $personal['phone'] ?? '', $personal['address'] ?? '', $personal['linkedin'] ?? '', $personal['website'] ?? '']);
    $contactHtml = implode(' &bull; ', $contactParts);

    $html = $cv['html_structure'] ?? '';

    $html = str_replace(
        ['{{first_name}}', '{{last_name}}', '{{first_initial}}', '{{last_initial}}', '{{job_title}}', '{{email}}', '{{phone}}', '{{address}}', '{{linkedin}}', '{{website}}', '{{photo}}'],
        [$personal['first_name'] ?? '', $personal['last_name'] ?? '', $firstInitial, $lastInitial, $personal['job_title'] ?? '', $personal['email'] ?? '', $personal['phone'] ?? '', $personal['address'] ?? '', $personal['linkedin'] ?? '', $personal['website'] ?? '', ''],
        $html
    );

    $contactBlock = '';
    if (!empty($personal['email'])) $contactBlock .= "<p>{$personal['email']}</p>";
    if (!empty($personal['phone'])) $contactBlock .= "<p>{$personal['phone']}</p>";
    if (!empty($personal['address'])) $contactBlock .= "<p>{$personal['address']}</p>";
    if (!empty($personal['linkedin'])) $contactBlock .= "<p class='rela-block social linked-in'>{$personal['linkedin']}</p>";
    $html = str_replace('{{contact_entries}}', $contactBlock, $html);

    $summary = $data['summary']['text'] ?? '';
    $html = str_replace('{{summary}}', $summary, $html);

    $expHtml = '';
    if (!empty($data['experience']['entries'])) {
        foreach ($data['experience']['entries'] as $entry) {
            $dateStr = ($entry['start_date'] ?? '') . ($entry['end_date'] ? ' - ' . $entry['end_date'] : ' - Present');
            $locStr = !empty($entry['location']) ? ', ' . $entry['location'] : '';
            $expHtml .= "<h3>{$entry['title']}</h3>";
            $expHtml .= "<p class='light'>{$entry['company']}{$locStr} | {$dateStr}</p>";
            $expHtml .= "<p class='justified'>{$entry['description']}</p>";
        }
    }
    $html = str_replace('{{experience_entries}}', $expHtml, $html);

    $eduHtml = '';
    if (!empty($data['education']['entries'])) {
        foreach ($data['education']['entries'] as $entry) {
            $eduHtml .= "<p class='rela-block list-thing'>{$entry['degree']}</p>";
            if (!empty($entry['school'])) $eduHtml .= "<p class='rela-block list-thing'>{$entry['school']}</p>";
            if (!empty($entry['year'])) $eduHtml .= "<p class='rela-block list-thing'>{$entry['year']}</p>";
        }
    }
    $html = str_replace('{{education_entries}}', $eduHtml, $html);

    $skillsHtml = '';
    if (!empty($data['skills']['entries'])) {
        foreach ($data['skills']['entries'] as $entry) {
            $skillsHtml .= "<p class='rela-block list-thing'>{$entry['name']}</p>";
        }
    }
    $html = str_replace('{{skills_entries}}', $skillsHtml, $html);

    $langHtml = '';
    if (!empty($data['languages']['entries'])) {
        foreach ($data['languages']['entries'] as $entry) {
            $langHtml .= "<p class='rela-block list-thing'>{$entry['language']} - {$entry['level']}</p>";
        }
    }
    $html = str_replace('{{language_entries}}', $langHtml, $html);

    $certHtml = '';
    if (!empty($data['certifications']['entries'])) {
        foreach ($data['certifications']['entries'] as $entry) {
            $text = $entry['name'] ?? '';
            if (!empty($entry['issuer'])) $text .= ' - ' . $entry['issuer'];
            if (!empty($entry['date'])) $text .= ' (' . $entry['date'] . ')';
            $certHtml .= "<p class='rela-block list-thing'>{$text}</p>";
        }
    }
    $html = str_replace('{{certification_entries}}', $certHtml, $html);

    $refHtml = '';
    if (!empty($data['references']['entries'])) {
        foreach ($data['references']['entries'] as $entry) {
            $text = $entry['name'] ?? '';
            if (!empty($entry['title'])) $text .= ' - ' . $entry['title'];
            if (!empty($entry['contact'])) $text .= ' | ' . $entry['contact'];
            $refHtml .= "<p class='rela-block list-thing'>{$text}</p>";
        }
    }
    $html = str_replace('{{reference_entries}}', $refHtml, $html);

    $html = str_replace(['{{project_entries}}', '{{publication_entries}}', '{{skill_group_entries}}', '{{highlight_entries}}', '{{interest_entries}}'], '', $html);

    return $html;
}
?>
