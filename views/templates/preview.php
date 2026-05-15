<div class="page-header">
    <div>
        <h1><?= e($template['name']) ?></h1>
        <p><?= e($template['description']) ?></p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="<?= BASE_URL ?>/templates" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        <a href="<?= BASE_URL ?>/cv/create" class="btn btn-primary"><i class="fas fa-plus"></i> Use This Template</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:24px;">
    <div style="background:#e5e7eb;padding:20px;border-radius:var(--radius-lg);">
        <div style="background:#fff;width:210mm;min-height:297mm;margin:0 auto;box-shadow:var(--shadow-lg);overflow:hidden;">
            <?= renderTemplatePreview($template) ?>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-body">
                <h3 style="font-size:16px;margin-bottom:12px;">Template Details</h3>
                <p style="font-size:13px;color:var(--gray-600);margin-bottom:12px;"><?= e($template['description']) ?></p>
                <div style="margin-bottom:8px;"><span class="badge badge-primary"><?= e($template['category']) ?></span></div>
                <a href="<?= BASE_URL ?>/cv/create" class="btn btn-primary btn-block"><i class="fas fa-plus"></i> Use Template</a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3 style="font-size:16px;margin-bottom:12px;">Features</h3>
                <ul style="font-size:13px;color:var(--gray-600);padding-left:16px;line-height:2;">
                    <li>ATS-friendly layout</li>
                    <li>Print-ready formatting</li>
                    <li>Customizable colors</li>
                    <li>PDF & Word export</li>
                    <li>Mobile responsive preview</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
function renderTemplatePreview(array $template): string {
    $sampleData = [
        'first_name' => 'Kyle', 'last_name' => 'Shanks',
        'first_initial' => 'K', 'last_initial' => 'S',
        'job_title' => 'Jr Front-End Developer',
        'email' => 'emailsareforsquares@gmail.com', 'phone' => '1-800-CALLPLZ',
        'address' => '123 My Place Drive, Astoria, New York 11105', 'linkedin' => 'linkedin.com/in/kyleshanks',
    ];
    $html = str_replace(
        ['{{first_name}}', '{{last_name}}', '{{first_initial}}', '{{last_initial}}', '{{job_title}}', '{{email}}', '{{phone}}', '{{address}}', '{{linkedin}}', '{{website}}', '{{photo}}'],
        [$sampleData['first_name'], $sampleData['last_name'], $sampleData['first_initial'], $sampleData['last_initial'], $sampleData['job_title'], $sampleData['email'], $sampleData['phone'], $sampleData['address'], $sampleData['linkedin'], '', ''],
        $template['html_structure']
    );

    $html = str_replace('{{contact_entries}}',
        '<div style="font-size:12px;color:#666;line-height:1.8;">' .
        '<span><i class="fas fa-envelope"></i> ' . $sampleData['email'] . '</span> &bull; ' .
        '<span><i class="fas fa-phone"></i> ' . $sampleData['phone'] . '</span> &bull; ' .
        '<span><i class="fas fa-map-marker-alt"></i> ' . $sampleData['address'] . '</span>' .
        '</div>', $html);

    $html = str_replace('{{summary}}', '<p style="font-size:13px;line-height:1.7;">Results-driven product manager with 8+ years of experience leading cross-functional teams to deliver innovative digital products. Proven track record of increasing user engagement by 40% and revenue by $12M through data-driven strategy and agile methodology.</p>', $html);

    $html = str_replace('{{experience_entries}}',
        '<div style="margin-bottom:15px;"><div style="font-weight:bold;font-size:14px;">Senior Product Manager</div><div style="color:#4f46e5;font-size:13px;">TechCorp Inc.</div><div style="color:#888;font-size:12px;">Jan 2021 - Present</div><div style="font-size:13px;line-height:1.6;margin-top:4px;">Led product strategy for SaaS platform serving 500K+ users. Increased retention by 25% through feature optimization and user research.</div></div>' .
        '<div style="margin-bottom:15px;"><div style="font-weight:bold;font-size:14px;">Product Manager</div><div style="color:#4f46e5;font-size:13px;">StartupXYZ</div><div style="color:#888;font-size:12px;">Mar 2018 - Dec 2020</div><div style="font-size:13px;line-height:1.6;margin-top:4px;">Managed product lifecycle from ideation to launch. Grew user base from 10K to 100K in 18 months.</div></div>', $html);

    $html = str_replace('{{education_entries}}',
        '<div style="margin-bottom:10px;"><div style="font-weight:bold;font-size:14px;">MBA, Business Administration</div><div style="color:#4f46e5;font-size:13px;">Stanford University</div><div style="color:#888;font-size:12px;">2016 - 2018</div></div>' .
        '<div style="margin-bottom:10px;"><div style="font-weight:bold;font-size:14px;">BS, Computer Science</div><div style="color:#4f46e5;font-size:13px;">UC Berkeley</div><div style="color:#888;font-size:12px;">2012 - 2016</div></div>', $html);

    $html = str_replace('{{skills_entries}}',
        '<div style="display:flex;flex-wrap:wrap;gap:8px;">' .
        '<span style="background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;">Product Strategy</span>' .
        '<span style="background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;">Agile/Scrum</span>' .
        '<span style="background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;">User Research</span>' .
        '<span style="background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;">Data Analysis</span>' .
        '<span style="background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;">A/B Testing</span>' .
        '<span style="background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;">SQL</span>' .
        '</div>', $html);

    $html = str_replace(['{{language_entries}}', '{{certification_entries}}', '{{project_entries}}', '{{publication_entries}}', '{{skill_group_entries}}', '{{highlight_entries}}', '{{interest_entries}}'], '', $html);

    return "<style>{$template['css_styles']}</style>" . $html;
}
?>
