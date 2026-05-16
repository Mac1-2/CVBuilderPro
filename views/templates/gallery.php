<div class="page-header">
    <div>
        <h1>CV Templates</h1>
        <p>Choose from 15 professionally designed templates for any industry.</p>
    </div>
</div>

<div style="margin-bottom:20px;display:flex;gap:8px;flex-wrap:wrap;">
    <a href="<?= BASE_URL ?>/templates" class="btn btn-sm <?= !$selectedCategory ? 'btn-primary' : 'btn-secondary' ?>">All</a>
    <?php foreach ($categories as $cat): ?>
    <a href="<?= BASE_URL ?>/templates?category=<?= e($cat) ?>" class="btn btn-sm <?= $selectedCategory === $cat ? 'btn-primary' : 'btn-secondary' ?>"><?= ucfirst(e($cat)) ?></a>
    <?php endforeach; ?>
</div>

<div class="grid grid-3">
    <?php foreach ($templates as $template): ?>
    <div class="card template-card" style="overflow:hidden;">
        <div style="height:280px;background:var(--gray-100);position:relative;overflow:hidden;cursor:pointer;" onclick="openTemplatePreview('<?= e($template['slug']) ?>')">
            <div style="width:70%;height:90%;background:#fff;margin:15px auto 0;border-radius:4px;box-shadow:var(--shadow);padding:15px;transform:scale(0.95);">
                <?= renderMiniPreview($template['slug']) ?>
            </div>
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0);display:flex;align-items:center;justify-content:center;transition:var(--transition);" onmouseover="this.style.background='rgba(0,0,0,0.5)'" onmouseout="this.style.background='rgba(0,0,0,0)'">
                <span style="color:#fff;font-size:14px;opacity:0;transition:var(--transition);font-weight:600;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'"><i class="fas fa-eye"></i> Preview</span>
            </div>
        </div>
        <div style="padding:16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-weight:600;font-size:16px;"><?= e($template['name']) ?></div>
                    <div style="font-size:12px;color:var(--gray-500);"><?= e($template['description'] ?? '') ?></div>
                </div>
                <span class="badge badge-primary"><?= e($template['category']) ?></span>
            </div>
            <div style="margin-top:12px;display:flex;gap:8px;">
                <a href="<?= BASE_URL ?>/templates/preview/<?= e($template['slug']) ?>" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i> Preview</a>
                <a href="<?= BASE_URL ?>/cv/create" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Use</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div id="template-modal" class="modal-overlay" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="modal" style="max-width:900px;">
        <div class="modal-header">
            <h3 id="modal-template-name">Template Preview</h3>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('show')">&times;</button>
        </div>
        <div class="modal-body" id="modal-template-content" style="padding:0;"></div>
        <div class="modal-footer">
            <a href="<?= BASE_URL ?>/cv/create" class="btn btn-primary"><i class="fas fa-plus"></i> Use This Template</a>
        </div>
    </div>
</div>

<script>
function openTemplatePreview(slug) {
    fetch(`<?= BASE_URL ?>/ajax/preview-template?slug=${slug}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modal-template-name').textContent = data.template.name;
                document.getElementById('modal-template-content').innerHTML = `<div style="padding:20px;background:#e5e7eb;overflow:auto;max-height:70vh;"><div style="background:#fff;width:210mm;min-height:297mm;margin:0 auto;box-shadow:0 4px 20px rgba(0,0,0,0.15);">${data.preview_html}</div></div>`;
                document.getElementById('template-modal').classList.add('show');
            }
        });
}
</script>

<?php
function renderMiniPreview(string $slug): string {
    $presets = [
        'modern' => '<div style="display:flex;height:100%;gap:6px;"><div style="width:35%;background:#2c3e50;border-radius:2px;"></div><div style="flex:1;display:flex;flex-direction:column;gap:4px;"><div style="height:8px;background:#eee;border-radius:2px;width:60%;"></div><div style="height:6px;background:#f5f5f5;border-radius:2px;"></div><div style="flex:1;display:flex;flex-direction:column;gap:3px;margin-top:6px;"><div style="height:4px;background:#f5f5f5;border-radius:2px;"></div><div style="height:4px;background:#f5f5f5;border-radius:2px;width:80%;"></div><div style="height:4px;background:#f5f5f5;border-radius:2px;width:90%;"></div></div></div></div>',
        'classic' => '<div style="text-align:center;height:100%;padding:8px;"><div style="height:10px;background:#333;border-radius:2px;width:50%;margin:0 auto 6px;"></div><div style="height:2px;background:#333;width:40%;margin:0 auto 8px;"></div><div style="display:flex;flex-direction:column;gap:4px;"><div style="height:4px;background:#f5f5f5;border-radius:2px;"></div><div style="height:4px;background:#f5f5f5;border-radius:2px;width:85%;"></div><div style="height:4px;background:#f5f5f5;border-radius:2px;width:95%;"></div><div style="height:4px;background:#f5f5f5;border-radius:2px;width:70%;"></div></div></div>',
        'minimal' => '<div style="padding:12px;height:100%;"><div style="height:12px;background:#1a1a1a;border-radius:2px;width:40%;margin-bottom:4px;"></div><div style="height:6px;background:#888;border-radius:2px;width:25%;margin-bottom:12px;"></div><div style="display:flex;flex-direction:column;gap:3px;"><div style="height:3px;background:#f0f0f0;border-radius:2px;"></div><div style="height:3px;background:#f0f0f0;border-radius:2px;width:90%;"></div><div style="height:3px;background:#f0f0f0;border-radius:2px;width:75%;"></div></div></div>',
        'shanks' => '<div style="overflow:hidden;height:100%;"><div style="background:#848484;height:25%;position:relative;"><div style="position:absolute;bottom:50%;left:50%;transform:translate(-50%,50%);color:#fff;font-size:8px;font-weight:100;letter-spacing:2px;">NAME</div></div><div style="display:flex;height:75%;"><div style="width:35%;background:#F7E0C1;"></div><div style="flex:1;padding:4px;"><div style="height:4px;background:#f5f5f5;border-radius:2px;margin-bottom:3px;"></div><div style="height:3px;background:#f5f5f5;border-radius:2px;width:80%;"></div></div></div></div>',
    ];
    $colors = [
        'academic' => '#333', 'technical' => '#0066cc', 'infographic' => '#f5576c',
        'functional' => '#3498db', 'combination' => '#1565C0', 'chronological' => '#222',
        'elegant' => '#b8860b', 'bold' => '#e74c3c', 'professional' => '#2c3e50', 'fresh' => '#43e97b'
    ];
    $c = $colors[$slug] ?? '#4f46e5';
    $default = "<div style='display:flex;flex-direction:column;gap:4px;padding:8px;height:100%;'><div style='height:8px;background:{$c};border-radius:2px;width:50%;'></div><div style='height:4px;background:#f0f0f0;border-radius:2px;'></div><div style='height:4px;background:#f0f0f0;border-radius:2px;width:80%;'></div><div style='height:4px;background:#f0f0f0;border-radius:2px;width:60%;'></div><div style='flex:1;'></div><div style='height:4px;background:#f0f0f0;border-radius:2px;'></div><div style='height:4px;background:#f0f0f0;border-radius:2px;width:90%;'></div></div>";
    return $presets[$slug] ?? $default;
}
?>
