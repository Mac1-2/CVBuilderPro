<?php
$pageTitle = 'Dashboard - ' . APP_NAME;
$cvModel = new Cv();
$templateModel = new Template();

$cvs = $cvModel->getByUser($_SESSION['user_id']);
$totalCvs = $cvModel->countByUser($_SESSION['user_id']);
$templates = $templateModel->getAll();
$featuredPhrases = (new Phrase())->getFeatured(null, 5);

require __DIR__ . '/../views/layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome back, <?= e(explode(' ', $_SESSION['user_name'] ?? 'User')[0]) ?>! Manage your CVs and templates.</p>
    </div>
    <a href="<?= BASE_URL ?>/cv/create" class="btn btn-primary"><i class="fas fa-plus"></i> Create New CV</a>
    <a href="<?= BASE_URL ?>/import" class="btn btn-success"><i class="fas fa-file-import"></i> Import CV</a>
</div>

<div class="grid grid-4" style="margin-bottom:24px;">
    <div class="stat-card">
        <div class="stat-icon primary"><i class="fas fa-file-lines"></i></div>
        <div><div class="stat-value"><?= $totalCvs ?></div><div class="stat-label">Total CVs</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon success"><i class="fas fa-palette"></i></div>
        <div><div class="stat-value"><?= count($templates) ?></div><div class="stat-label">Templates</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon warning"><i class="fas fa-lightbulb"></i></div>
        <div><div class="stat-value">1,000</div><div class="stat-label">Expert Phrases</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon secondary"><i class="fas fa-file-export"></i></div>
        <div><div class="stat-value">PDF/DOCX</div><div class="stat-label">Export Formats</div></div>
    </div>
</div>

<?php if (empty($cvs)): ?>
<div class="card" style="text-align:center;padding:60px 20px;">
    <i class="fas fa-file-circle-plus" style="font-size:64px;color:var(--gray-300);margin-bottom:16px;"></i>
    <h2 style="margin-bottom:8px;">No CVs yet</h2>
    <p style="color:var(--gray-500);margin-bottom:24px;">Create your first professional CV or import an existing one.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="<?= BASE_URL ?>/cv/create" class="btn btn-primary btn-lg"><i class="fas fa-plus"></i> Create from Scratch</a>
        <a href="<?= BASE_URL ?>/import" class="btn btn-success btn-lg"><i class="fas fa-file-import"></i> Import Existing CV</a>
    </div>
</div>
<?php else: ?>
<h2 style="margin-bottom:16px;font-size:18px;">Your CVs</h2>
<div class="cv-list">
    <?php foreach ($cvs as $cv): ?>
    <div class="cv-card">
        <div class="cv-card-preview">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="cv-card-body">
            <div class="cv-card-title"><?= e($cv['title']) ?></div>
            <div class="cv-card-meta">
                <span class="badge badge-primary"><?= e($cv['template_name'] ?? 'Default') ?></span>
                &middot; Updated <?= date('M j, Y', strtotime($cv['updated_at'])) ?>
            </div>
            <div class="cv-card-actions">
                <a href="<?= BASE_URL ?>/cv/edit/<?= $cv['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>
                <a href="<?= BASE_URL ?>/cv/preview/<?= $cv['id'] ?>" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i> Preview</a>
                <button onclick="duplicateCv(<?= $cv['id'] ?>)" class="btn btn-sm btn-secondary"><i class="fas fa-copy"></i></button>
                <button onclick="deleteCv(<?= $cv['id'] ?>)" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div style="margin-top:32px;">
    <h2 style="margin-bottom:16px;font-size:18px;">Quick Actions</h2>
    <div class="grid grid-4">
        <a href="<?= BASE_URL ?>/import" class="card" style="padding:24px;display:block;border:2px solid var(--success);">
            <i class="fas fa-file-import" style="font-size:24px;color:var(--success);margin-bottom:12px;"></i>
            <h3 style="font-size:16px;margin-bottom:4px;">Import Existing CV</h3>
            <p style="font-size:13px;color:var(--gray-500);">Upload PDF/DOCX and auto-fill your CV</p>
        </a>
        <a href="<?= BASE_URL ?>/templates" class="card" style="padding:24px;display:block;">
            <i class="fas fa-palette" style="font-size:24px;color:var(--primary);margin-bottom:12px;"></i>
            <h3 style="font-size:16px;margin-bottom:4px;">Browse Templates</h3>
            <p style="font-size:13px;color:var(--gray-500);">Choose from 19 professional CV templates</p>
        </a>
        <a href="<?= BASE_URL ?>/phrases" class="card" style="padding:24px;display:block;">
            <i class="fas fa-lightbulb" style="font-size:24px;color:var(--warning);margin-bottom:12px;"></i>
            <h3 style="font-size:16px;margin-bottom:4px;">Phrase Library</h3>
            <p style="font-size:13px;color:var(--gray-500);">1,000+ industry-specific expert phrases</p>
        </a>
        <a href="<?= BASE_URL ?>/cv/create" class="card" style="padding:24px;display:block;">
            <i class="fas fa-wand-magic-sparkles" style="font-size:24px;color:var(--success);margin-bottom:12px;"></i>
            <h3 style="font-size:16px;margin-bottom:4px;">Start from Scratch</h3>
            <p style="font-size:13px;color:var(--gray-500);">Build a new CV with our step-by-step wizard</p>
        </a>
    </div>
</div>

<script>
async function duplicateCv(id) {
    if (!confirm('Duplicate this CV?')) return;
    try {
        const res = await App.fetch('<?= BASE_URL ?>/ajax/duplicate-cv', { method: 'POST', body: JSON.stringify({ cv_id: id }) });
        if (res.success) { window.location.reload(); }
    } catch(e) { alert('Error duplicating CV'); }
}

async function deleteCv(id) {
    if (!confirm('Delete this CV permanently?')) return;
    try {
        const res = await App.fetch('<?= BASE_URL ?>/ajax/delete-cv', { method: 'POST', body: JSON.stringify({ cv_id: id }) });
        if (res.success) { window.location.reload(); }
    } catch(e) { alert('Error deleting CV'); }
}
</script>

<?php require __DIR__ . '/../views/layouts/footer.php'; ?>
