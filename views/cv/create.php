<div class="page-header">
    <div>
        <h1>Create New CV</h1>
        <p>Choose a template and start building your professional CV.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="<?= BASE_URL ?>/import" class="btn btn-success"><i class="fas fa-file-import"></i> Import CV</a>
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
</div>

<div class="card" style="margin-bottom:24px;background:linear-gradient(135deg,#dcfce7,#d1fae5);border:1px solid #86efac;">
    <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <i class="fas fa-file-import" style="font-size:32px;color:#16a34a;"></i>
            <div>
                <h3 style="font-size:16px;color:#166534;margin-bottom:2px;">Have an existing CV?</h3>
                <p style="font-size:13px;color:#15803d;margin:0;">Upload your PDF or DOCX and we'll auto-fill everything for you.</p>
            </div>
        </div>
        <a href="<?= BASE_URL ?>/import" class="btn btn-success"><i class="fas fa-upload"></i> Import Now</a>
    </div>
</div>

<form method="POST" action="<?= BASE_URL ?>/cv/create">
    <?= csrf_field() ?>
    <div class="card" style="margin-bottom:24px;">
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">CV Title</label>
                <input type="text" name="title" class="form-input" placeholder="e.g., My Professional CV" value="My Professional CV" required>
            </div>
        </div>
    </div>

    <h2 style="margin-bottom:16px;font-size:18px;">Choose a Template</h2>
    <div class="grid grid-4">
        <?php foreach ($templates as $template): ?>
        <label class="template-option" style="cursor:pointer;">
            <input type="radio" name="template_id" value="<?= $template['id'] ?>" <?= $template['id'] === 1 ? 'checked' : '' ?> style="display:none;">
            <div class="card template-card" style="transition:var(--transition);">
                <div class="template-preview" style="height:200px;background:var(--gray-100);display:flex;align-items:center;justify-content:center;overflow:hidden;">
                    <div style="width:80%;height:85%;background:#fff;border-radius:4px;box-shadow:var(--shadow);padding:12px;transform:scale(0.9);">
                        <?= renderMiniPreview($template['slug']) ?>
                    </div>
                </div>
                <div style="padding:12px;">
                    <div style="font-weight:600;font-size:14px;"><?= e($template['name']) ?></div>
                    <div style="font-size:12px;color:var(--gray-500);"><?= e($template['category']) ?></div>
                </div>
            </div>
        </label>
        <?php endforeach; ?>
    </div>

    <div style="margin-top:24px;display:flex;gap:12px;">
        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-magic"></i> Create CV with Wizard</button>
        <button type="button" onclick="this.form.action='<?= BASE_URL ?>/cv/create';this.form.submit();" class="btn btn-secondary btn-lg"><i class="fas fa-edit"></i> Create with WYSIWYG</button>
    </div>
</form>

<style>
.template-option input:checked + .template-card { border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,0.2); }
.template-option:hover .template-card { transform:translateY(-2px);box-shadow:var(--shadow-lg); }
.template-card { border:2px solid transparent; }
</style>

<?php
function renderMiniPreview(string $slug): string {
    $presets = [
        'modern' => '<div style="display:flex;height:100%;gap:6px;"><div style="width:35%;background:#2c3e50;border-radius:2px;"></div><div style="flex:1;display:flex;flex-direction:column;gap:4px;"><div style="height:8px;background:#eee;border-radius:2px;width:60%;"></div><div style="height:6px;background:#f5f5f5;border-radius:2px;"></div><div style="flex:1;display:flex;flex-direction:column;gap:3px;margin-top:6px;"><div style="height:4px;background:#f5f5f5;border-radius:2px;"></div><div style="height:4px;background:#f5f5f5;border-radius:2px;width:80%;"></div><div style="height:4px;background:#f5f5f5;border-radius:2px;width:90%;"></div></div></div></div>',
        'classic' => '<div style="text-align:center;height:100%;padding:8px;"><div style="height:10px;background:#333;border-radius:2px;width:50%;margin:0 auto 6px;"></div><div style="height:2px;background:#333;width:40%;margin:0 auto 8px;"></div><div style="display:flex;flex-direction:column;gap:4px;"><div style="height:4px;background:#f5f5f5;border-radius:2px;"></div><div style="height:4px;background:#f5f5f5;border-radius:2px;width:85%;"></div><div style="height:4px;background:#f5f5f5;border-radius:2px;width:95%;"></div><div style="height:4px;background:#f5f5f5;border-radius:2px;width:70%;"></div></div></div>',
        'minimal' => '<div style="padding:12px;height:100%;"><div style="height:12px;background:#1a1a1a;border-radius:2px;width:40%;margin-bottom:4px;"></div><div style="height:6px;background:#888;border-radius:2px;width:25%;margin-bottom:12px;"></div><div style="display:flex;flex-direction:column;gap:3px;"><div style="height:3px;background:#f0f0f0;border-radius:2px;"></div><div style="height:3px;background:#f0f0f0;border-radius:2px;width:90%;"></div><div style="height:3px;background:#f0f0f0;border-radius:2px;width:75%;"></div></div></div>',
    ];
    $default = '<div style="display:flex;flex-direction:column;gap:4px;padding:8px;height:100%;"><div style="height:8px;background:#4f46e5;border-radius:2px;width:50%;"></div><div style="height:4px;background:#f0f0f0;border-radius:2px;"></div><div style="height:4px;background:#f0f0f0;border-radius:2px;width:80%;"></div><div style="height:4px;background:#f0f0f0;border-radius:2px;width:60%;"></div><div style="flex:1;"></div><div style="height:4px;background:#f0f0f0;border-radius:2px;"></div><div style="height:4px;background:#f0f0f0;border-radius:2px;width:90%;"></div></div>';
    return $presets[$slug] ?? $default;
}
?>
