<?php
$pageTitle = 'Preview: ' . $template['name'];
$sections = [];
$sampleData = [
    'personal' => ['first_name' => 'Alexandra', 'last_name' => 'Mitchell', 'job_title' => 'Senior Product Manager', 'email' => 'alex@email.com', 'phone' => '+1 (555) 123-4567', 'address' => 'San Francisco, CA', 'linkedin' => 'linkedin.com/in/alexmitchell'],
    'summary' => ['text' => 'Results-driven product manager with 8+ years of experience leading cross-functional teams to deliver innovative digital products.'],
    'experience' => ['entries' => [
        ['title' => 'Senior Product Manager', 'company' => 'TechCorp Inc.', 'start_date' => 'Jan 2021', 'end_date' => 'Present', 'location' => 'San Francisco', 'description' => 'Led product strategy for SaaS platform serving 500K+ users. Increased retention by 25%.'],
        ['title' => 'Product Manager', 'company' => 'StartupXYZ', 'start_date' => 'Mar 2018', 'end_date' => 'Dec 2020', 'location' => '', 'description' => 'Managed product lifecycle from ideation to launch. Grew user base from 10K to 100K.'],
    ]],
    'education' => ['entries' => [
        ['degree' => 'MBA, Business Administration', 'school' => 'Stanford University', 'year' => '2016 - 2018', 'description' => ''],
        ['degree' => 'BS, Computer Science', 'school' => 'UC Berkeley', 'year' => '2012 - 2016', 'description' => ''],
    ]],
    'skills' => ['entries' => [
        ['name' => 'Product Strategy'], ['name' => 'Agile/Scrum'], ['name' => 'Data Analysis'], ['name' => 'SQL'], ['name' => 'User Research'],
    ]],
    'languages' => ['entries' => [['language' => 'English', 'level' => 'Native'], ['language' => 'Spanish', 'level' => 'Fluent']]],
    'certifications' => ['entries' => [['name' => 'PMP', 'issuer' => 'PMI', 'date' => '2020']]],
    'references' => ['entries' => []],
];
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
        .cv-page{background:#fff;width:210mm;min-height:297mm;margin:60px auto 20px;box-shadow:0 4px 20px rgba(0,0,0,0.15);overflow:hidden;}
    </style>
    <style><?= $template['css_styles'] ?></style>
</head>
<body>
    <div class="preview-bar">
        <a href="<?= BASE_URL ?>/creator/edit/<?= $template['id'] ?>"><i class="fas fa-arrow-left"></i> Back to Editor</a>
        <span><?= e($template['name']) ?> — Template Preview</span>
        <a href="<?= BASE_URL ?>/export/pdf/1" style="opacity:0.5;cursor:not-allowed;" title="Save template first">Export PDF</a>
    </div>
    <div class="cv-page">
        <?= renderTemplatePreview($template, $sampleData) ?>
    </div>
</body>
</html>

<?php
function renderTemplatePreview(array $template, array $data): string {
    $personal = $data['personal'];
    $html = $template['html_structure'] ?? '';

    $html = str_replace(
        ['{{first_name}}', '{{last_name}}', '{{first_initial}}', '{{last_initial}}', '{{job_title}}', '{{email}}', '{{phone}}', '{{address}}', '{{linkedin}}', '{{website}}', '{{photo}}'],
        [$personal['first_name'], $personal['last_name'], 'A', 'M', $personal['job_title'], $personal['email'], $personal['phone'], $personal['address'], $personal['linkedin'], '', ''],
        $html
    );

    $contactBlock = "<p>{$personal['email']}</p><p>{$personal['phone']}</p><p>{$personal['address']}</p>";
    $html = str_replace('{{contact_entries}}', $contactBlock, $html);
    $html = str_replace('{{summary}}', $data['summary']['text'], $html);

    $expHtml = '';
    foreach ($data['experience']['entries'] as $entry) {
        $expHtml .= "<h3>{$entry['title']}</h3><p class='light'>{$entry['company']} | {$entry['start_date']} - {$entry['end_date']}</p><p class='justified'>{$entry['description']}</p>";
    }
    $html = str_replace('{{experience_entries}}', $expHtml, $html);

    $eduHtml = '';
    foreach ($data['education']['entries'] as $entry) {
        $eduHtml .= "<p class='rela-block list-thing'>{$entry['degree']}</p><p class='rela-block list-thing'>{$entry['school']}</p><p class='rela-block list-thing'>{$entry['year']}</p>";
    }
    $html = str_replace('{{education_entries}}', $eduHtml, $html);

    $skillsHtml = '';
    foreach ($data['skills']['entries'] as $entry) {
        $skillsHtml .= "<p class='rela-block list-thing'>{$entry['name']}</p>";
    }
    $html = str_replace('{{skills_entries}}', $skillsHtml, $html);

    $html = str_replace(['{{language_entries}}', '{{certification_entries}}', '{{reference_entries}}', '{{project_entries}}', '{{publication_entries}}', '{{skill_group_entries}}', '{{highlight_entries}}', '{{interest_entries}}'], '', $html);

    return $html;
}
?>
