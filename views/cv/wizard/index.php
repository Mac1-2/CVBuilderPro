<style>
.editor-layout{display:flex;height:calc(100vh - 60px);gap:0;}
.editor-sidebar{width:220px;background:#fff;border-right:1px solid var(--gray-200);display:flex;flex-direction:column;}
.editor-steps{flex:1;overflow-y:auto;}
.step-item{padding:12px 16px;cursor:pointer;display:flex;align-items:center;gap:10px;font-size:13px;color:var(--gray-600);border-left:3px solid transparent;transition:var(--transition);}
.step-item:hover{background:var(--gray-50);}
.step-item.active{background:#eef2ff;color:var(--primary);border-left-color:var(--primary);font-weight:600;}
.step-item.completed{color:var(--success);}
.step-number{width:24px;height:24px;border-radius:50%;background:var(--gray-200);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;}
.step-item.active .step-number{background:var(--primary);color:#fff;}
.step-item.completed .step-number{background:var(--success);color:#fff;}
.editor-main{flex:1;overflow-y:auto;padding:24px;background:var(--gray-50);}
.editor-preview{width:400px;background:#fff;border-left:1px solid var(--gray-200);overflow-y:auto;padding:20px;}
.step-content{display:none;}
.step-content.active{display:block;}
.entry-card{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);padding:16px;margin-bottom:12px;position:relative;}
.entry-card .remove-entry{position:absolute;top:8px;right:8px;background:none;border:none;color:var(--danger);cursor:pointer;font-size:16px;}
.entry-card .remove-entry:hover{color:#b91c1c;}
.phrase-panel{background:#fff;border:1px solid var(--gray-200);border-radius:var(--radius);margin-top:16px;}
.phrase-panel-header{padding:12px 16px;border-bottom:1px solid var(--gray-200);font-weight:600;font-size:14px;display:flex;align-items:center;justify-content:space-between;}
.phrase-list{max-height:300px;overflow-y:auto;}
.phrase-item{padding:10px 16px;border-bottom:1px solid var(--gray-100);font-size:13px;cursor:pointer;transition:var(--transition);}
.phrase-item:hover{background:var(--gray-50);}
.phrase-item .phrase-text{color:var(--gray-700);line-height:1.5;}
.phrase-item .phrase-meta{font-size:11px;color:var(--gray-400);margin-top:4px;}
.editor-toolbar{display:flex;align-items:center;justify-content:space-between;padding:12px 24px;background:#fff;border-bottom:1px solid var(--gray-200);}
.mode-toggle{display:flex;gap:4px;background:var(--gray-100);border-radius:var(--radius);padding:3px;}
.mode-toggle a{padding:6px 14px;border-radius:6px;font-size:13px;color:var(--gray-600);}
.mode-toggle a.active{background:#fff;color:var(--primary);box-shadow:var(--shadow);}
.preview-frame{width:100%;min-height:600px;border:1px solid var(--gray-200);border-radius:var(--radius);}
</style>

<div class="editor-toolbar">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i></a>
        <input type="text" id="cv-title" value="<?= e($cv['title']) ?>" class="form-input" style="width:250px;font-weight:600;" onchange="saveCvTitle(this.value)">
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        <div class="mode-toggle">
            <a href="<?= BASE_URL ?>/cv/edit/<?= $cv['id'] ?>?mode=wizard" class="active"><i class="fas fa-list-ol"></i> Wizard</a>
            <a href="<?= BASE_URL ?>/cv/edit/<?= $cv['id'] ?>?mode=wysiwyg"><i class="fas fa-edit"></i> WYSIWYG</a>
        </div>
        <select id="template-select" class="form-select" style="width:160px;" onchange="applyTemplate(this.value)">
            <?php foreach ($templates as $t): ?>
            <option value="<?= $t['id'] ?>" <?= $t['id'] == $cv['template_id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <a href="<?= BASE_URL ?>/cv/preview/<?= $cv['id'] ?>" class="btn btn-sm btn-secondary"><i class="fas fa-eye"></i> Preview</a>
        <a href="<?= BASE_URL ?>/export/pdf/<?= $cv['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-file-pdf"></i> PDF</a>
        <a href="<?= BASE_URL ?>/export/word/<?= $cv['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-file-word"></i> Word</a>
        <button onclick="autoSave()" class="btn btn-sm btn-secondary" id="save-btn"><i class="fas fa-save"></i> Save</button>
    </div>
</div>

<div class="editor-layout">
    <div class="editor-sidebar">
        <div class="editor-steps">
            <?php
            $steps = [
                ['personal', 'Personal Info', 'fa-user'],
                ['summary', 'Summary', 'fa-align-left'],
                ['experience', 'Experience', 'fa-briefcase'],
                ['education', 'Education', 'fa-graduation-cap'],
                ['skills', 'Skills', 'fa-star'],
                ['languages', 'Languages', 'fa-language'],
                ['certifications', 'Certifications', 'fa-certificate'],
                ['references', 'References', 'fa-address-card'],
            ];
            foreach ($steps as $i => $step):
            ?>
            <div class="step-item <?= $i === 0 ? 'active' : '' ?>" data-step="<?= $step[0] ?>" onclick="goToStep('<?= $step[0] ?>')">
                <div class="step-number"><?= $i + 1 ?></div>
                <span><?= $step[1] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="padding:12px;border-top:1px solid var(--gray-200);">
            <a href="<?= BASE_URL ?>/phrases" target="_blank" class="btn btn-sm btn-block btn-secondary"><i class="fas fa-lightbulb"></i> Phrase Library</a>
        </div>
    </div>

    <div class="editor-main">
        <?php
        $sectionData = [];
        foreach ($cv['sections'] as $s) {
            $sectionData[$s['section_type']] = json_decode($s['content_json'], true);
        }
        $personal = $sectionData['personal'] ?? [];
        $summary = $sectionData['summary'] ?? [];
        $experience = $sectionData['experience'] ?? [];
        $education = $sectionData['education'] ?? [];
        $skills = $sectionData['skills'] ?? [];
        $languages = $sectionData['languages'] ?? [];
        $certifications = $sectionData['certifications'] ?? [];
        $references = $sectionData['references'] ?? [];
        ?>

        <div class="step-content active" data-step-content="personal">
            <h2 style="margin-bottom:20px;">Personal Information</h2>
            <div class="card"><div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group"><label class="form-label">First Name</label><input type="text" class="form-input" id="p_first_name" value="<?= e($personal['first_name'] ?? '') ?>"></div>
                    <div class="form-group"><label class="form-label">Last Name</label><input type="text" class="form-input" id="p_last_name" value="<?= e($personal['last_name'] ?? '') ?>"></div>
                </div>
                <div class="form-group"><label class="form-label">Job Title</label><input type="text" class="form-input" id="p_job_title" value="<?= e($personal['job_title'] ?? '') ?>"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-input" id="p_email" value="<?= e($personal['email'] ?? '') ?>"></div>
                    <div class="form-group"><label class="form-label">Phone</label><input type="text" class="form-input" id="p_phone" value="<?= e($personal['phone'] ?? '') ?>"></div>
                </div>
                <div class="form-group"><label class="form-label">Address</label><input type="text" class="form-input" id="p_address" value="<?= e($personal['address'] ?? '') ?>"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group"><label class="form-label">LinkedIn</label><input type="text" class="form-input" id="p_linkedin" value="<?= e($personal['linkedin'] ?? '') ?>"></div>
                    <div class="form-group"><label class="form-label">Website</label><input type="text" class="form-input" id="p_website" value="<?= e($personal['website'] ?? '') ?>"></div>
                </div>
            </div></div>
        </div>

        <div class="step-content" data-step-content="summary">
            <h2 style="margin-bottom:20px;">Professional Summary</h2>
            <div class="card"><div class="card-body">
                <div class="form-group"><label class="form-label">Write a compelling summary of your professional background</label>
                <textarea class="form-textarea" id="summary_text" rows="6"><?= e($summary['text'] ?? '') ?></textarea></div>
                <div class="phrase-panel">
                    <div class="phrase-panel-header"><span><i class="fas fa-lightbulb" style="color:var(--warning);"></i> Suggested Phrases</span></div>
                    <div class="phrase-list" id="summary-phrases"></div>
                </div>
            </div></div>
        </div>

        <div class="step-content" data-step-content="experience">
            <h2 style="margin-bottom:20px;">Work Experience</h2>
            <div id="experience-entries">
                <?php
                $expEntries = $experience['entries'] ?? [[]];
                if (empty($expEntries)) $expEntries = [[]];
                foreach ($expEntries as $i => $entry):
                ?>
                <div class="entry-card" data-index="<?= $i ?>">
                    <button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group"><label class="form-label">Job Title</label><input type="text" class="form-input exp-title" value="<?= e($entry['title'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Company</label><input type="text" class="form-input exp-company" value="<?= e($entry['company'] ?? '') ?>"></div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group"><label class="form-label">Start Date</label><input type="month" class="form-input exp-start" value="<?= e($entry['start_date'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">End Date</label><input type="month" class="form-input exp-end" value="<?= e($entry['end_date'] ?? '') ?>"> <small style="color:var(--gray-400);">Leave empty for current</small></div>
                    </div>
                    <div class="form-group"><label class="form-label">Location</label><input type="text" class="form-input exp-location" value="<?= e($entry['location'] ?? '') ?>"></div>
                    <div class="form-group"><label class="form-label">Description</label><textarea class="form-textarea exp-description" rows="4"><?= e($entry['description'] ?? '') ?></textarea></div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-secondary" onclick="addExperienceEntry()"><i class="fas fa-plus"></i> Add Experience</button>
            <div class="phrase-panel" style="margin-top:16px;">
                <div class="phrase-panel-header"><span><i class="fas fa-lightbulb" style="color:var(--warning);"></i> Expert Bullet Points</span></div>
                <div class="phrase-list" id="experience-phrases"></div>
            </div>
        </div>

        <div class="step-content" data-step-content="education">
            <h2 style="margin-bottom:20px;">Education</h2>
            <div id="education-entries">
                <?php
                $eduEntries = $education['entries'] ?? [[]];
                if (empty($eduEntries)) $eduEntries = [[]];
                foreach ($eduEntries as $i => $entry):
                ?>
                <div class="entry-card">
                    <button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    <div class="form-group"><label class="form-label">Degree / Certificate</label><input type="text" class="form-input edu-degree" value="<?= e($entry['degree'] ?? '') ?>"></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group"><label class="form-label">School / University</label><input type="text" class="form-input edu-school" value="<?= e($entry['school'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Year</label><input type="text" class="form-input edu-year" value="<?= e($entry['year'] ?? '') ?>"></div>
                    </div>
                    <div class="form-group"><label class="form-label">Description (optional)</label><textarea class="form-textarea edu-description" rows="3"><?= e($entry['description'] ?? '') ?></textarea></div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-secondary" onclick="addEducationEntry()"><i class="fas fa-plus"></i> Add Education</button>
        </div>

        <div class="step-content" data-step-content="skills">
            <h2 style="margin-bottom:20px;">Skills</h2>
            <div class="card"><div class="card-body">
                <div id="skills-entries">
                    <?php
                    $skillEntries = $skills['entries'] ?? [];
                    if (empty($skillEntries)) $skillEntries = [['name' => '']];
                    foreach ($skillEntries as $entry):
                    ?>
                    <div style="display:flex;gap:8px;margin-bottom:8px;">
                        <input type="text" class="form-input skill-name" value="<?= e($entry['name'] ?? '') ?>" placeholder="Skill name">
                        <button class="btn btn-sm btn-danger" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="btn btn-sm btn-secondary" onclick="addSkillEntry()"><i class="fas fa-plus"></i> Add Skill</button>
                <div class="phrase-panel" style="margin-top:16px;">
                    <div class="phrase-panel-header"><span><i class="fas fa-lightbulb" style="color:var(--warning);"></i> Suggested Skills</span></div>
                    <div class="phrase-list" id="skills-phrases"></div>
                </div>
            </div></div>
        </div>

        <div class="step-content" data-step-content="languages">
            <h2 style="margin-bottom:20px;">Languages</h2>
            <div id="language-entries">
                <?php
                $langEntries = $languages['entries'] ?? [];
                if (empty($langEntries)) $langEntries = [['language' => '', 'level' => '']];
                foreach ($langEntries as $entry):
                ?>
                <div style="display:flex;gap:8px;margin-bottom:8px;">
                    <input type="text" class="form-input lang-name" value="<?= e($entry['language'] ?? '') ?>" placeholder="Language" style="flex:1;">
                    <select class="form-select lang-level" style="width:160px;">
                        <option value="">Proficiency</option>
                        <option value="Native" <?= ($entry['level'] ?? '') === 'Native' ? 'selected' : '' ?>>Native</option>
                        <option value="Fluent" <?= ($entry['level'] ?? '') === 'Fluent' ? 'selected' : '' ?>>Fluent</option>
                        <option value="Advanced" <?= ($entry['level'] ?? '') === 'Advanced' ? 'selected' : '' ?>>Advanced</option>
                        <option value="Intermediate" <?= ($entry['level'] ?? '') === 'Intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="Basic" <?= ($entry['level'] ?? '') === 'Basic' ? 'selected' : '' ?>>Basic</option>
                    </select>
                    <button class="btn btn-sm btn-danger" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-sm btn-secondary" onclick="addLanguageEntry()"><i class="fas fa-plus"></i> Add Language</button>
        </div>

        <div class="step-content" data-step-content="certifications">
            <h2 style="margin-bottom:20px;">Certifications</h2>
            <div id="certification-entries">
                <?php
                $certEntries = $certifications['entries'] ?? [];
                if (empty($certEntries)) $certEntries = [[]];
                foreach ($certEntries as $entry):
                ?>
                <div class="entry-card">
                    <button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    <div class="form-group"><label class="form-label">Certification Name</label><input type="text" class="form-input cert-name" value="<?= e($entry['name'] ?? '') ?>"></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group"><label class="form-label">Issuing Organization</label><input type="text" class="form-input cert-issuer" value="<?= e($entry['issuer'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Date</label><input type="text" class="form-input cert-date" value="<?= e($entry['date'] ?? '') ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-sm btn-secondary" onclick="addCertificationEntry()"><i class="fas fa-plus"></i> Add Certification</button>
        </div>

        <div class="step-content" data-step-content="references">
            <h2 style="margin-bottom:20px;">References</h2>
            <div id="reference-entries">
                <?php
                $refEntries = $references['entries'] ?? [];
                if (empty($refEntries)) $refEntries = [[]];
                foreach ($refEntries as $entry):
                ?>
                <div class="entry-card">
                    <button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
                    <div class="form-group"><label class="form-label">Name</label><input type="text" class="form-input ref-name" value="<?= e($entry['name'] ?? '') ?>"></div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="form-group"><label class="form-label">Title / Company</label><input type="text" class="form-input ref-title" value="<?= e($entry['title'] ?? '') ?>"></div>
                        <div class="form-group"><label class="form-label">Contact</label><input type="text" class="form-input ref-contact" value="<?= e($entry['contact'] ?? '') ?>"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button class="btn btn-sm btn-secondary" onclick="addReferenceEntry()"><i class="fas fa-plus"></i> Add Reference</button>
        </div>
    </div>

    <div class="editor-preview">
        <h3 style="margin-bottom:12px;font-size:14px;color:var(--gray-500);">Live Preview</h3>
        <div id="cv-preview" class="preview-frame"></div>
    </div>
</div>

<script>
const CV_ID = <?= $cv['id'] ?>;
const CV_DATA = <?= json_encode($sectionData) ?>;
const TEMPLATE_HTML = <?= json_encode($cv['html_structure'] ?? '') ?>;
const TEMPLATE_CSS = <?= json_encode($cv['css_styles'] ?? '') ?>;
</script>
