<div class="page-header">
    <div>
        <h1>Create Custom Template</h1>
        <p>Start from scratch or clone an existing template as your base.</p>
    </div>
    <a href="<?= BASE_URL ?>/templates" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form method="POST" action="<?= BASE_URL ?>/creator/new">
    <?= csrf_field() ?>
    <div class="card" style="max-width:600px;margin-bottom:24px;">
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Template Name</label>
                <input type="text" name="name" class="form-input" placeholder="e.g., My Custom Template" value="My Custom Template" required>
            </div>
            <div class="form-group">
                <label class="form-label">Start From (optional)</label>
                <select name="base_template_id" class="form-select">
                    <option value="0">Blank Canvas</option>
                    <?php foreach ($templates as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> — <?= e($t['category']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-magic"></i> Create Template</button>
        </div>
    </div>
</form>

<div style="max-width:600px;">
    <h3 style="margin-bottom:12px;font-size:16px;">Or clone an existing template:</h3>
    <div class="grid grid-3">
        <?php foreach ($templates as $t): ?>
        <a href="<?= BASE_URL ?>/creator/clone/<?= $t['id'] ?>" class="card" style="padding:16px;display:block;text-align:center;transition:var(--transition);">
            <i class="fas fa-copy" style="font-size:24px;color:var(--primary);margin-bottom:8px;"></i>
            <div style="font-weight:600;font-size:14px;"><?= e($t['name']) ?></div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
