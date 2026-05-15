<style>
.wysiwyg-layout{display:flex;height:calc(100vh - 60px);gap:0;}
.wysiwyg-editor{flex:1;display:flex;flex-direction:column;}
.wysiwyg-toolbar{background:#fff;border-bottom:1px solid var(--gray-200);padding:8px 16px;display:flex;align-items:center;gap:4px;flex-wrap:wrap;}
.wysiwyg-toolbar button{padding:6px 10px;border:1px solid var(--gray-200);background:#fff;border-radius:4px;cursor:pointer;font-size:14px;color:var(--gray-600);transition:var(--transition);}
.wysiwyg-toolbar button:hover{background:var(--gray-100);color:var(--primary);}
.wysiwyg-toolbar .separator{width:1px;height:24px;background:var(--gray-200);margin:0 4px;}
.wysiwyg-toolbar select{padding:4px 8px;border:1px solid var(--gray-200);border-radius:4px;font-size:13px;}
.wysiwyg-content{flex:1;overflow-y:auto;padding:24px;background:var(--gray-100);}
.cv-page{background:#fff;width:210mm;min-height:297mm;margin:0 auto;box-shadow:var(--shadow-lg);padding:20mm;position:relative;}
.wysiwyg-sidebar{width:280px;background:#fff;border-left:1px solid var(--gray-200);display:flex;flex-direction:column;}
.sidebar-section{border-bottom:1px solid var(--gray-200);padding:16px;}
.sidebar-section h4{font-size:13px;font-weight:600;color:var(--gray-700);margin-bottom:12px;}
.color-picker-row{display:flex;align-items:center;gap:8px;margin-bottom:8px;}
.color-picker-row label{font-size:12px;color:var(--gray-600);flex:1;}
.color-picker-row input[type="color"]{width:36px;height:28px;border:1px solid var(--gray-200);border-radius:4px;cursor:pointer;padding:0;}
.phrase-search{width:100%;padding:8px 12px;border:1px solid var(--gray-200);border-radius:var(--radius);font-size:13px;margin-bottom:8px;}
</style>

<div class="editor-toolbar">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="<?= BASE_URL ?>/dashboard" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i></a>
        <input type="text" id="cv-title" value="<?= e($cv['title']) ?>" class="form-input" style="width:250px;font-weight:600;" onchange="saveCvTitle(this.value)">
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        <div class="mode-toggle">
            <a href="<?= BASE_URL ?>/cv/edit/<?= $cv['id'] ?>?mode=wizard"><i class="fas fa-list-ol"></i> Wizard</a>
            <a href="<?= BASE_URL ?>/cv/edit/<?= $cv['id'] ?>?mode=wysiwyg" class="active"><i class="fas fa-edit"></i> WYSIWYG</a>
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

<div class="wysiwyg-layout">
    <div class="wysiwyg-editor">
        <div class="wysiwyg-toolbar">
            <select onchange="document.execCommand('formatBlock',false,this.value);this.value='';">
                <option value="">Heading</option>
                <option value="h1">H1</option>
                <option value="h2">H2</option>
                <option value="h3">H3</option>
                <option value="p">Paragraph</option>
            </select>
            <select onchange="document.execCommand('fontName',false,this.value);">
                <option value="Arial">Arial</option>
                <option value="Georgia">Georgia</option>
                <option value="Times New Roman">Times New Roman</option>
                <option value="Verdana">Verdana</option>
                <option value="Helvetica">Helvetica</option>
                <option value="Calibri">Calibri</option>
            </select>
            <select onchange="document.execCommand('fontSize',false,this.value);">
                <option value="">Size</option>
                <option value="1">Small</option>
                <option value="3">Normal</option>
                <option value="5">Large</option>
                <option value="7">Huge</option>
            </select>
            <div class="separator"></div>
            <button onclick="document.execCommand('bold')" title="Bold"><i class="fas fa-bold"></i></button>
            <button onclick="document.execCommand('italic')" title="Italic"><i class="fas fa-italic"></i></button>
            <button onclick="document.execCommand('underline')" title="Underline"><i class="fas fa-underline"></i></button>
            <button onclick="document.execCommand('strikeThrough')" title="Strikethrough"><i class="fas fa-strikethrough"></i></button>
            <div class="separator"></div>
            <button onclick="document.execCommand('foreColor',false,prompt('Color hex:','#333333'))" title="Text Color"><i class="fas fa-font"></i></button>
            <button onclick="document.execCommand('hiliteColor',false,'#ffff00')" title="Highlight"><i class="fas fa-highlighter"></i></button>
            <div class="separator"></div>
            <button onclick="document.execCommand('justifyLeft')" title="Align Left"><i class="fas fa-align-left"></i></button>
            <button onclick="document.execCommand('justifyCenter')" title="Align Center"><i class="fas fa-align-center"></i></button>
            <button onclick="document.execCommand('justifyRight')" title="Align Right"><i class="fas fa-align-right"></i></button>
            <div class="separator"></div>
            <button onclick="document.execCommand('insertUnorderedList')" title="Bullet List"><i class="fas fa-list-ul"></i></button>
            <button onclick="document.execCommand('insertOrderedList')" title="Numbered List"><i class="fas fa-list-ol"></i></button>
            <div class="separator"></div>
            <button onclick="document.execCommand('removeFormat')" title="Clear Formatting"><i class="fas fa-eraser"></i></button>
        </div>
        <div class="wysiwyg-content">
            <div class="cv-page" id="cv-page" contenteditable="true">
                <?= renderCvPage($cv) ?>
            </div>
        </div>
    </div>

    <div class="wysiwyg-sidebar">
        <div class="sidebar-section">
            <h4><i class="fas fa-palette"></i> Template Colors</h4>
            <div class="color-picker-row"><label>Primary</label><input type="color" id="color-primary" value="#4f46e5" onchange="updateColor('--primary',this.value)"></div>
            <div class="color-picker-row"><label>Secondary</label><input type="color" id="color-secondary" value="#0ea5e9" onchange="updateColor('--secondary',this.value)"></div>
            <div class="color-picker-row"><label>Text</label><input type="color" id="color-text" value="#333333" onchange="updateColor('--text',this.value)"></div>
            <div class="color-picker-row"><label>Background</label><input type="color" id="color-bg" value="#ffffff" onchange="updateColor('--bg',this.value)"></div>
        </div>
        <div class="sidebar-section">
            <h4><i class="fas fa-lightbulb"></i> Quick Phrases</h4>
            <input type="text" class="phrase-search" placeholder="Search phrases..." oninput="searchPhrases(this.value)">
            <div id="quick-phrases" style="max-height:300px;overflow-y:auto;"></div>
        </div>
        <div class="sidebar-section">
            <h4><i class="fas fa-section"></i> Sections</h4>
            <?php foreach ($cv['sections'] as $s): ?>
            <label style="display:flex;align-items:center;gap:8px;padding:4px 0;font-size:13px;">
                <input type="checkbox" <?= $s['is_visible'] ? 'checked' : '' ?> onchange="toggleSection('<?= $s['section_type'] ?>', this.checked)">
                <?= e($s['title']) ?>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
function renderCvPage(array $cv): string {
    $sections = $cv['sections'];
    $data = [];
    foreach ($sections as $s) {
        $data[$s['section_type']] = json_decode($s['content_json'], true);
    }
    $personal = $data['personal'] ?? [];
    $fullName = trim(($personal['first_name'] ?? '') . ' ' . ($personal['last_name'] ?? ''));
    $contactParts = array_filter([$personal['email'] ?? '', $personal['phone'] ?? '', $personal['address'] ?? '', $personal['linkedin'] ?? '']);
    $contactHtml = implode(' &bull; ', $contactParts);
    $summary = $data['summary']['text'] ?? '';

    $html = "<style>{$cv['css_styles']}</style>";
    $html .= "<div style='font-family:Arial,sans-serif;'>";
    $html .= "<h1 style='font-size:24px;margin-bottom:4px;'>{$fullName}</h1>";
    if (!empty($personal['job_title'])) $html .= "<p style='font-size:14px;color:#4f46e5;margin-bottom:4px;'>{$personal['job_title']}</p>";
    if ($contactHtml) $html .= "<p style='font-size:12px;color:#666;margin-bottom:20px;'>{$contactHtml}</p>";
    if ($summary) $html .= "<h2 style='font-size:16px;color:#4f46e5;border-bottom:2px solid #4f46e5;padding-bottom:4px;margin:16px 0 8px;'>Professional Summary</h2><p style='font-size:13px;line-height:1.7;'>{$summary}</p>";

    if (!empty($data['experience']['entries'])) {
        $html .= "<h2 style='font-size:16px;color:#4f46e5;border-bottom:2px solid #4f46e5;padding-bottom:4px;margin:16px 0 8px;'>Experience</h2>";
        foreach ($data['experience']['entries'] as $entry) {
            $dateStr = ($entry['start_date'] ?? '') . ($entry['end_date'] ? ' - ' . $entry['end_date'] : ' - Present');
            $locationStr = !empty($entry['location']) ? ', ' . $entry['location'] : '';
            $html .= "<div style='margin-bottom:12px;'><div style='font-weight:bold;font-size:14px;'>{$entry['title']}</div><div style='color:#4f46e5;font-size:13px;'>{$entry['company']}{$locationStr}</div><div style='color:#888;font-size:12px;font-style:italic;'>{$dateStr}</div><div style='font-size:13px;line-height:1.6;margin-top:4px;'>{$entry['description']}</div></div>";
        }
    }
    if (!empty($data['education']['entries'])) {
        $html .= "<h2 style='font-size:16px;color:#4f46e5;border-bottom:2px solid #4f46e5;padding-bottom:4px;margin:16px 0 8px;'>Education</h2>";
        foreach ($data['education']['entries'] as $entry) {
            $html .= "<div style='margin-bottom:8px;'><div style='font-weight:bold;font-size:14px;'>{$entry['degree']}</div><div style='color:#4f46e5;font-size:13px;'>{$entry['school']}</div><div style='color:#888;font-size:12px;'>{$entry['year']}</div></div>";
        }
    }
    if (!empty($data['skills']['entries'])) {
        $html .= "<h2 style='font-size:16px;color:#4f46e5;border-bottom:2px solid #4f46e5;padding-bottom:4px;margin:16px 0 8px;'>Skills</h2>";
        $skillNames = array_map(fn($e) => $e['name'] ?? '', $data['skills']['entries']);
        $html .= "<div style='display:flex;flex-wrap:wrap;gap:8px;'>" . implode('', array_map(fn($s) => "<span style='background:#eef2ff;color:#4f46e5;padding:4px 12px;border-radius:4px;font-size:12px;'>$s</span>", $skillNames)) . "</div>";
    }
    $html .= "</div>";
    return $html;
}
?>
