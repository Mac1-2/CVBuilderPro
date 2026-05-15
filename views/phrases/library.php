<div class="page-header">
    <div>
        <h1>Phrase Library</h1>
        <p>1,000+ industry-specific expert phrases to supercharge your CV.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:250px 1fr;gap:24px;">
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-body">
                <h3 style="font-size:14px;margin-bottom:12px;"><i class="fas fa-industry"></i> Industry</h3>
                <a href="<?= BASE_URL ?>/phrases" class="phrase-filter-item <?= !$industryId ? 'active' : '' ?>" style="display:block;padding:6px 10px;border-radius:4px;font-size:13px;color:var(--gray-600);margin-bottom:2px;<?= !$industryId ? 'background:#eef2ff;color:var(--primary);font-weight:600;' : '' ?>">All Industries</a>
                <?php foreach ($industries as $ind): ?>
                <a href="<?= BASE_URL ?>/phrases?industry=<?= $ind['id'] ?>" class="phrase-filter-item" style="display:block;padding:6px 10px;border-radius:4px;font-size:13px;color:var(--gray-600);margin-bottom:2px;<?= $industryId == $ind['id'] ? 'background:#eef2ff;color:var(--primary);font-weight:600;' : '' ?>"><?= e($ind['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h3 style="font-size:14px;margin-bottom:12px;"><i class="fas fa-tag"></i> Category</h3>
                <a href="<?= BASE_URL ?>/phrases<?= $industryId ? "?industry=$industryId" : '' ?>" style="display:block;padding:6px 10px;border-radius:4px;font-size:13px;color:var(--gray-600);margin-bottom:2px;<?= !$category ? 'background:#eef2ff;color:var(--primary);font-weight:600;' : '' ?>">All</a>
                <?php foreach (['summary' => 'Professional Summary', 'experience' => 'Experience Bullets', 'skills' => 'Skills & Keywords', 'achievement' => 'Achievements', 'objective' => 'Career Objective'] as $key => $label): ?>
                <a href="<?= BASE_URL ?>/phrases?category=<?= $key ?><?= $industryId ? "&industry=$industryId" : '' ?>" style="display:block;padding:6px 10px;border-radius:4px;font-size:13px;color:var(--gray-600);margin-bottom:2px;<?= $category === $key ? 'background:#eef2ff;color:var(--primary);font-weight:600;' : '' ?>"><?= $label ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div>
        <div style="margin-bottom:16px;display:flex;gap:8px;">
            <input type="text" id="phrase-search" class="form-input" placeholder="Search phrases..." value="<?= e($search) ?>" style="flex:1;" onkeyup="if(event.key==='Enter')searchPhrases()">
            <button class="btn btn-primary" onclick="searchPhrases()"><i class="fas fa-search"></i></button>
        </div>

        <div style="margin-bottom:12px;font-size:13px;color:var(--gray-500);"><?= $total ?> phrases found</div>

        <?php if (empty($phrases)): ?>
        <div class="card" style="text-align:center;padding:40px;">
            <i class="fas fa-search" style="font-size:32px;color:var(--gray-300);margin-bottom:12px;"></i>
            <p style="color:var(--gray-500);">No phrases found. Try a different search or filter.</p>
        </div>
        <?php else: ?>
        <div style="display:grid;gap:8px;">
            <?php foreach ($phrases as $phrase): ?>
            <div class="card phrase-card" style="padding:14px 16px;cursor:pointer;transition:var(--transition);" onclick="copyPhrase(this)" data-text="<?= e($phrase['phrase_text']) ?>">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div style="flex:1;">
                        <p style="font-size:14px;line-height:1.6;color:var(--gray-700);"><?= e($phrase['phrase_text']) ?></p>
                        <div style="margin-top:6px;display:flex;gap:6px;flex-wrap:wrap;">
                            <span class="badge badge-primary"><?= e($phrase['industry_name'] ?? 'General') ?></span>
                            <span class="badge" style="background:#f0fdf4;color:#166534;"><?= e($phrase['category']) ?></span>
                            <?php if ($phrase['is_featured']): ?><span class="badge" style="background:#fef3c7;color:#92400e;"><i class="fas fa-star"></i> Featured</span><?php endif; ?>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-secondary" onclick="event.stopPropagation();copyPhrase(this.closest('.phrase-card'))" title="Copy to clipboard"><i class="fas fa-copy"></i></button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
        <div style="margin-top:20px;display:flex;justify-content:center;gap:4px;">
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <a href="<?= BASE_URL ?>/phrases?p=<?= $p ?><?= $search ? '&q=' . urlencode($search) : '' ?><?= $industryId ? "&industry=$industryId" : '' ?><?= $category ? "&category=$category" : '' ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $p ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function searchPhrases() {
    const q = document.getElementById('phrase-search').value;
    window.location.href = `<?= BASE_URL ?>/phrases?q=${encodeURIComponent(q)}<?= $industryId ? "&industry=$industryId" : '' ?><?= $category ? "&category=$category" : '' ?>`;
}

function copyPhrase(card) {
    const text = card.dataset.text;
    navigator.clipboard.writeText(text).then(() => {
        App.showToast('Phrase copied to clipboard!');
    });
}
</script>
