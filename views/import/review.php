<?php
$pd = $parsedData;
$personal = $pd['personal'] ?? [];
$summary = $pd['summary']['text'] ?? '';
$experience = $pd['experience']['entries'] ?? [];
$education = $pd['education']['entries'] ?? [];
$skills = $pd['skills']['entries'] ?? [];
$languages = $pd['languages']['entries'] ?? [];
$certifications = $pd['certifications']['entries'] ?? [];
$references = $pd['references']['entries'] ?? [];
$fullName = trim(($personal['first_name'] ?? '') . ' ' . ($personal['last_name'] ?? ''));
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-file-import"></i> Review Imported CV</h1>
        <p>Review and edit the parsed data before creating your CV.</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="<?= BASE_URL ?>/import" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Upload Another</a>
        <button onclick="createCvFromImport()" class="btn btn-primary btn-lg"><i class="fas fa-check"></i> Create CV</button>
    </div>
</div>

<div id="import-stats" style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
    <div class="stat-card" style="flex:1;min-width:120px;">
        <div class="stat-icon primary"><i class="fas fa-briefcase"></i></div>
        <div><div class="stat-value"><?= count($experience) ?></div><div class="stat-label">Experience</div></div>
    </div>
    <div class="stat-card" style="flex:1;min-width:120px;">
        <div class="stat-icon success"><i class="fas fa-graduation-cap"></i></div>
        <div><div class="stat-value"><?= count($education) ?></div><div class="stat-label">Education</div></div>
    </div>
    <div class="stat-card" style="flex:1;min-width:120px;">
        <div class="stat-icon warning"><i class="fas fa-star"></i></div>
        <div><div class="stat-value"><?= count($skills) ?></div><div class="stat-label">Skills</div></div>
    </div>
    <div class="stat-card" style="flex:1;min-width:120px;">
        <div class="stat-icon secondary"><i class="fas fa-language"></i></div>
        <div><div class="stat-value"><?= count($languages) ?></div><div class="stat-label">Languages</div></div>
    </div>
</div>

<div class="grid grid-2" style="gap:20px;">
    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header"><span class="card-title"><i class="fas fa-user"></i> Personal Information</span></div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group"><label class="form-label">First Name</label><input type="text" class="form-input import-field" data-section="personal" data-field="first_name" value="<?= e($personal['first_name'] ?? '') ?>"></div>
                    <div class="form-group"><label class="form-label">Last Name</label><input type="text" class="form-input import-field" data-section="personal" data-field="last_name" value="<?= e($personal['last_name'] ?? '') ?>"></div>
                </div>
                <div class="form-group"><label class="form-label">Job Title</label><input type="text" class="form-input import-field" data-section="personal" data-field="job_title" value="<?= e($personal['job_title'] ?? '') ?>"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-input import-field" data-section="personal" data-field="email" value="<?= e($personal['email'] ?? '') ?>"></div>
                    <div class="form-group"><label class="form-label">Phone</label><input type="text" class="form-input import-field" data-section="personal" data-field="phone" value="<?= e($personal['phone'] ?? '') ?>"></div>
                </div>
                <div class="form-group"><label class="form-label">Address</label><input type="text" class="form-input import-field" data-section="personal" data-field="address" value="<?= e($personal['address'] ?? '') ?>"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div class="form-group"><label class="form-label">LinkedIn</label><input type="text" class="form-input import-field" data-section="personal" data-field="linkedin" value="<?= e($personal['linkedin'] ?? '') ?>"></div>
                    <div class="form-group"><label class="form-label">Website</label><input type="text" class="form-input import-field" data-section="personal" data-field="website" value="<?= e($personal['website'] ?? '') ?>"></div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom:16px;">
            <div class="card-header"><span class="card-title"><i class="fas fa-align-left"></i> Professional Summary</span></div>
            <div class="card-body">
                <div class="form-group"><textarea class="form-textarea import-field" data-section="summary" data-field="text" rows="5"><?= e($summary) ?></textarea></div>
            </div>
        </div>

        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-briefcase"></i> Experience (<?= count($experience) ?>)</span>
                <button class="btn btn-sm btn-secondary" onclick="addImportEntry('experience')"><i class="fas fa-plus"></i> Add</button>
            </div>
            <div class="card-body" id="import-experience">
                <?php foreach ($experience as $i => $entry): ?>
                <div class="import-entry" data-index="<?= $i ?>">
                    <button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <input type="text" class="form-input import-field" data-section="experience" data-field="title" data-index="<?= $i ?>" value="<?= e($entry['title'] ?? '') ?>" placeholder="Job Title">
                        <input type="text" class="form-input import-field" data-section="experience" data-field="company" data-index="<?= $i ?>" value="<?= e($entry['company'] ?? '') ?>" placeholder="Company">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:6px;">
                        <input type="text" class="form-input import-field" data-section="experience" data-field="start_date" data-index="<?= $i ?>" value="<?= e($entry['start_date'] ?? '') ?>" placeholder="Start Date">
                        <input type="text" class="form-input import-field" data-section="experience" data-field="end_date" data-index="<?= $i ?>" value="<?= e($entry['end_date'] ?? '') ?>" placeholder="End Date">
                    </div>
                    <textarea class="form-textarea import-field" data-section="experience" data-field="description" data-index="<?= $i ?>" rows="3" style="margin-top:6px;" placeholder="Description"><?= e($entry['description'] ?? '') ?></textarea>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-graduation-cap"></i> Education (<?= count($education) ?>)</span>
                <button class="btn btn-sm btn-secondary" onclick="addImportEntry('education')"><i class="fas fa-plus"></i> Add</button>
            </div>
            <div class="card-body" id="import-education">
                <?php foreach ($education as $i => $entry): ?>
                <div class="import-entry" data-index="<?= $i ?>">
                    <button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    <input type="text" class="form-input import-field" data-section="education" data-field="degree" data-index="<?= $i ?>" value="<?= e($entry['degree'] ?? '') ?>" placeholder="Degree" style="margin-bottom:6px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <input type="text" class="form-input import-field" data-section="education" data-field="school" data-index="<?= $i ?>" value="<?= e($entry['school'] ?? '') ?>" placeholder="School">
                        <input type="text" class="form-input import-field" data-section="education" data-field="year" data-index="<?= $i ?>" value="<?= e($entry['year'] ?? '') ?>" placeholder="Year">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
                <span class="card-title"><i class="fas fa-star"></i> Skills (<?= count($skills) ?>)</span>
                <button class="btn btn-sm btn-secondary" onclick="addImportEntry('skills')"><i class="fas fa-plus"></i> Add</button>
            </div>
            <div class="card-body" id="import-skills">
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    <?php foreach ($skills as $i => $entry): ?>
                    <span class="import-skill-tag" data-index="<?= $i ?>">
                        <input type="text" class="import-field" data-section="skills" data-field="name" data-index="<?= $i ?>" value="<?= e($entry['name'] ?? '') ?>" style="border:none;background:transparent;min-width:60px;font-size:13px;">
                        <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:14px;">&times;</button>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if (!empty($languages)): ?>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header"><span class="card-title"><i class="fas fa-language"></i> Languages (<?= count($languages) ?>)</span></div>
            <div class="card-body">
                <?php foreach ($languages as $i => $entry): ?>
                <div style="display:flex;gap:8px;margin-bottom:6px;">
                    <input type="text" class="form-input import-field" data-section="languages" data-field="language" data-index="<?= $i ?>" value="<?= e($entry['language'] ?? '') ?>" placeholder="Language" style="flex:1;">
                    <select class="form-select import-field" data-section="languages" data-field="level" data-index="<?= $i ?>" style="width:140px;">
                        <?php foreach (['','Native','Fluent','Advanced','Intermediate','Basic'] as $lvl): ?>
                        <option value="<?= $lvl ?>" <?= ($entry['level'] ?? '') === $lvl ? 'selected' : '' ?>><?= $lvl ?: 'Select' ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($certifications)): ?>
        <div class="card" style="margin-bottom:16px;">
            <div class="card-header"><span class="card-title"><i class="fas fa-certificate"></i> Certifications (<?= count($certifications) ?>)</span></div>
            <div class="card-body">
                <?php foreach ($certifications as $i => $entry): ?>
                <div class="import-entry" data-index="<?= $i ?>">
                    <button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    <input type="text" class="form-input import-field" data-section="certifications" data-field="name" data-index="<?= $i ?>" value="<?= e($entry['name'] ?? '') ?>" placeholder="Certification Name" style="margin-bottom:6px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <input type="text" class="form-input import-field" data-section="certifications" data-field="issuer" data-index="<?= $i ?>" value="<?= e($entry['issuer'] ?? '') ?>" placeholder="Issuer">
                        <input type="text" class="form-input import-field" data-section="certifications" data-field="date" data-index="<?= $i ?>" value="<?= e($entry['date'] ?? '') ?>" placeholder="Date">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">CV Title</label>
                    <input type="text" id="cv-title" class="form-input" value="<?= e($fullName ? $fullName . ' - CV' : 'Imported CV') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Template</label>
                    <select id="cv-template" class="form-select">
                        <?php
                        $templateModel = new Template();
                        foreach ($templateModel->getAll() as $t):
                        ?>
                        <option value="<?= $t['id'] ?>"><?= e($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button onclick="createCvFromImport()" class="btn btn-primary btn-block btn-lg"><i class="fas fa-check"></i> Create CV with Imported Data</button>
            </div>
        </div>
    </div>
</div>

<script>
const IMPORT_ID = '<?= e($importId) ?>';

function collectImportData() {
    const data = {
        personal: {}, summary: { text: '' },
        experience: { entries: [] }, education: { entries: [] },
        skills: { entries: [] }, languages: { entries: [] },
        certifications: { entries: [] }, references: { entries: [] }
    };

    document.querySelectorAll('.import-field').forEach(el => {
        const section = el.dataset.section;
        const field = el.dataset.field;
        const index = el.dataset.index;
        const value = el.value;

        if (section === 'personal') {
            data.personal[field] = value;
        } else if (section === 'summary') {
            data.summary.text = value;
        } else if (index !== undefined) {
            if (!data[section].entries[parseInt(index)]) {
                data[section].entries[parseInt(index)] = {};
            }
            data[section].entries[parseInt(index)][field] = value;
        }
    });

    data.skills.entries = data.skills.entries.filter(e => e.name && e.name.trim());
    data.experience.entries = data.experience.entries.filter(e => e.title || e.company);
    data.education.entries = data.education.entries.filter(e => e.degree || e.school);

    return data;
}

async function createCvFromImport() {
    const btn = event.target.closest('button');
    btn.innerHTML = '<span class="loading"></span> Creating...';
    btn.disabled = true;

    try {
        const data = collectImportData();
        const res = await fetch('<?= BASE_URL ?>/import/create-cv', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({
                import_id: IMPORT_ID,
                title: document.getElementById('cv-title').value,
                template_id: parseInt(document.getElementById('cv-template').value),
                overrides: data
            })
        });
        const result = await res.json();
        if (result.success) {
            window.location.href = '<?= BASE_URL ?>/cv/edit/' + result.cv_id;
        } else {
            App.showToast(result.error || 'Failed to create CV', 'error');
            btn.innerHTML = '<i class="fas fa-check"></i> Create CV';
            btn.disabled = false;
        }
    } catch(e) {
        App.showToast('Error creating CV', 'error');
        btn.innerHTML = '<i class="fas fa-check"></i> Create CV';
        btn.disabled = false;
    }
}

function addImportEntry(type) {
    const container = document.getElementById('import-' + type);
    const index = container.querySelectorAll('.import-entry, .import-skill-tag').length;

    if (type === 'skills') {
        const span = document.createElement('span');
        span.className = 'import-skill-tag';
        span.dataset.index = index;
        span.innerHTML = `<input type="text" class="import-field" data-section="skills" data-field="name" data-index="${index}" placeholder="Skill" style="border:none;background:transparent;min-width:60px;font-size:13px;"><button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:var(--gray-400);font-size:14px;">&times;</button>`;
        container.querySelector('div').appendChild(span);
    } else {
        const div = document.createElement('div');
        div.className = 'import-entry';
        div.dataset.index = index;
        if (type === 'experience') {
            div.innerHTML = `<button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <input type="text" class="form-input import-field" data-section="experience" data-field="title" data-index="${index}" placeholder="Job Title">
                    <input type="text" class="form-input import-field" data-section="experience" data-field="company" data-index="${index}" placeholder="Company">
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:6px;">
                    <input type="text" class="form-input import-field" data-section="experience" data-field="start_date" data-index="${index}" placeholder="Start Date">
                    <input type="text" class="form-input import-field" data-section="experience" data-field="end_date" data-index="${index}" placeholder="End Date">
                </div>
                <textarea class="form-textarea import-field" data-section="experience" data-field="description" data-index="${index}" rows="3" style="margin-top:6px;" placeholder="Description"></textarea>`;
        } else if (type === 'education') {
            div.innerHTML = `<button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                <input type="text" class="form-input import-field" data-section="education" data-field="degree" data-index="${index}" placeholder="Degree" style="margin-bottom:6px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    <input type="text" class="form-input import-field" data-section="education" data-field="school" data-index="${index}" placeholder="School">
                    <input type="text" class="form-input import-field" data-section="education" data-field="year" data-index="${index}" placeholder="Year">
                </div>`;
        }
        container.appendChild(div);
    }
}
</script>
