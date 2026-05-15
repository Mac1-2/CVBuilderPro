<style>
.creator-layout{display:flex;height:calc(100vh - 60px);overflow:hidden;}
.creator-palette{width:220px;background:#fff;border-right:1px solid var(--gray-200);overflow-y:auto;flex-shrink:0;}
.creator-canvas{flex:1;background:var(--gray-100);overflow-y:auto;padding:20px;display:flex;justify-content:center;}
.creator-properties{width:300px;background:#fff;border-left:1px solid var(--gray-200);overflow-y:auto;flex-shrink:0;}
.palette-section{padding:12px;border-bottom:1px solid var(--gray-100);}
.palette-section h4{font-size:11px;text-transform:uppercase;letter-spacing:1px;color:var(--gray-500);margin-bottom:8px;}
.palette-item{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:6px;cursor:grab;font-size:13px;color:var(--gray-700);transition:var(--transition);background:var(--gray-50);margin-bottom:4px;}
.palette-item:hover{background:var(--gray-100);color:var(--primary);}
.palette-item i{width:16px;text-align:center;color:var(--gray-400);}
.canvas-page{background:#fff;width:210mm;min-height:297mm;box-shadow:var(--shadow-lg);padding:0;position:relative;}
.canvas-block{position:relative;border:2px solid transparent;border-radius:4px;margin:4px;transition:border-color 0.15s;}
.canvas-block:hover{border-color:var(--primary-light);}
.canvas-block.selected{border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,0.15);}
.canvas-block .block-handle{position:absolute;top:4px;left:4px;width:24px;height:24px;background:var(--primary);color:#fff;border-radius:4px;display:none;align-items:center;justify-content:center;cursor:grab;font-size:12px;z-index:5;}
.canvas-block:hover .block-handle,.canvas-block.selected .block-handle{display:flex;}
.canvas-block .block-actions{position:absolute;top:4px;right:4px;display:none;gap:2px;z-index:5;}
.canvas-block:hover .block-actions,.canvas-block.selected .block-actions{display:flex;}
.canvas-block .block-actions button{width:24px;height:24px;border:none;border-radius:4px;cursor:pointer;font-size:11px;display:flex;align-items:center;justify-content:center;}
.block-actions .btn-dup{background:var(--gray-200);color:var(--gray-600);}
.block-actions .btn-del{background:#fee2e2;color:#dc2626;}
.block-actions .btn-dup:hover{background:var(--gray-300);}
.block-actions .btn-del:hover{background:#fecaca;}
.block-content{padding:8px;min-height:30px;}
.block-placeholder{color:var(--gray-400);font-size:12px;font-style:italic;}
.prop-section{padding:16px;border-bottom:1px solid var(--gray-100);}
.prop-section h4{font-size:13px;font-weight:600;color:var(--gray-700);margin-bottom:12px;display:flex;align-items:center;gap:6px;}
.prop-row{display:flex;align-items:center;margin-bottom:8px;gap:8px;}
.prop-row label{font-size:12px;color:var(--gray-600);min-width:70px;}
.prop-row input[type="text"],.prop-row input[type="number"],.prop-row select{flex:1;padding:6px 8px;border:1px solid var(--gray-200);border-radius:4px;font-size:12px;}
.prop-row input[type="color"]{width:32px;height:28px;border:1px solid var(--gray-200);border-radius:4px;cursor:pointer;padding:0;}
.prop-row .unit{font-size:11px;color:var(--gray-400);min-width:20px;}
.spacing-grid{display:grid;grid-template-columns:1fr 1fr;gap:4px;}
.spacing-grid input{width:100%;padding:4px 6px;border:1px solid var(--gray-200);border-radius:3px;font-size:11px;}
.punctuation-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:4px;}
.punctuation-grid button{padding:6px;border:1px solid var(--gray-200);border-radius:4px;background:#fff;cursor:pointer;font-size:14px;transition:var(--transition);}
.punctuation-grid button:hover,.punctuation-grid button.active{background:var(--primary);color:#fff;border-color:var(--primary);}
.graphics-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;}
.graphics-grid .graphic-item{aspect-ratio:1;border:1px solid var(--gray-200);border-radius:4px;overflow:hidden;cursor:pointer;position:relative;}
.graphics-grid .graphic-item img{width:100%;height:100%;object-fit:cover;}
.graphics-grid .graphic-item:hover{border-color:var(--primary);}
.graphics-grid .graphic-item .graphic-del{position:absolute;top:2px;right:2px;width:18px;height:18px;background:rgba(239,68,68,0.9);color:#fff;border:none;border-radius:50%;font-size:10px;cursor:pointer;display:none;align-items:center;justify-content:center;}
.graphics-grid .graphic-item:hover .graphic-del{display:flex;}
.upload-zone{border:2px dashed var(--gray-300);border-radius:8px;padding:20px;text-align:center;cursor:pointer;transition:var(--transition);margin-bottom:12px;}
.upload-zone:hover{border-color:var(--primary);background:var(--gray-50);}
.sortable-ghost{opacity:0.4;}
.sortable-chosen{box-shadow:var(--shadow-lg);}
.sortable-drop-indicator{height:2px;background:var(--primary);border-radius:1px;margin:2px 0;}
.creator-toolbar{display:flex;align-items:center;justify-content:space-between;padding:8px 16px;background:#fff;border-bottom:1px solid var(--gray-200);}
.undo-redo{display:flex;gap:4px;}
.undo-redo button{width:32px;height:32px;border:1px solid var(--gray-200);border-radius:6px;background:#fff;cursor:pointer;font-size:14px;color:var(--gray-500);transition:var(--transition);}
.undo-redo button:hover:not(:disabled){background:var(--gray-100);color:var(--gray-700);}
.undo-redo button:disabled{opacity:0.3;cursor:not-allowed;}
</style>

<div class="creator-toolbar">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="<?= BASE_URL ?>/templates" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i></a>
        <input type="text" id="template-name" value="<?= e($template['name']) ?>" class="form-input" style="width:200px;font-weight:600;" onchange="TemplateCreator.saveTemplateName(this.value)">
        <?php if ($template['is_custom']): ?><span class="badge badge-warning">Custom</span><?php endif; ?>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        <div class="undo-redo">
            <button id="btn-undo" disabled onclick="TemplateCreator.undo()" title="Undo (Ctrl+Z)"><i class="fas fa-undo"></i></button>
            <button id="btn-redo" disabled onclick="TemplateCreator.redo()" title="Redo (Ctrl+Shift+Z)"><i class="fas fa-redo"></i></button>
        </div>
        <a href="<?= BASE_URL ?>/creator/preview/<?= $template['id'] ?>" class="btn btn-sm btn-secondary" target="_blank"><i class="fas fa-eye"></i> Preview</a>
        <button onclick="TemplateCreator.regenerate()" class="btn btn-sm btn-primary"><i class="fas fa-save"></i> Save & Regenerate</button>
    </div>
</div>

<div class="creator-layout">
    <div class="creator-palette">
        <div class="palette-section">
            <h4>Layout Blocks</h4>
            <div class="palette-item" data-block-type="header"><i class="fas fa-window-maximize"></i> Header</div>
            <div class="palette-item" data-block-type="sidebar"><i class="fas fa-columns"></i> Sidebar</div>
            <div class="palette-item" data-block-type="columns"><i class="fas fa-table-columns"></i> Columns</div>
            <div class="palette-item" data-block-type="grid"><i class="fas fa-grid-2"></i> Grid</div>
            <div class="palette-item" data-block-type="spacer"><i class="fas fa-arrows-up-down"></i> Spacer</div>
            <div class="palette-item" data-block-type="divider"><i class="fas fa-minus"></i> Divider</div>
        </div>
        <div class="palette-section">
            <h4>Content Blocks</h4>
            <div class="palette-item" data-block-type="section"><i class="fas fa-file-lines"></i> Section</div>
            <div class="palette-item" data-block-type="text"><i class="fas fa-paragraph"></i> Text</div>
            <div class="palette-item" data-block-type="personal-field"><i class="fas fa-user"></i> Personal Field</div>
            <div class="palette-item" data-block-type="contact-line"><i class="fas fa-address-card"></i> Contact Line</div>
            <div class="palette-item" data-block-type="skill-bar"><i class="fas fa-star"></i> Skills</div>
            <div class="palette-item" data-block-type="entry-loop"><i class="fas fa-list"></i> Entry Loop</div>
        </div>
        <div class="palette-section">
            <h4>Media</h4>
            <div class="palette-item" data-block-type="image"><i class="fas fa-image"></i> Image</div>
        </div>
        <div class="palette-section">
            <h4>Quick Actions</h4>
            <button onclick="TemplateCreator.cloneFromBase()" class="btn btn-sm btn-block btn-secondary"><i class="fas fa-copy"></i> Clone Base Template</button>
            <button onclick="TemplateCreator.resetBlocks()" class="btn btn-sm btn-block btn-danger" style="margin-top:6px;"><i class="fas fa-trash"></i> Clear All Blocks</button>
        </div>
    </div>

    <div class="creator-canvas">
        <div class="canvas-page" id="canvas">
            <div id="canvas-blocks">
                <?php if (empty($blocks)): ?>
                <div style="text-align:center;padding:80px 20px;color:var(--gray-400);" id="empty-canvas-msg">
                    <i class="fas fa-plus-circle" style="font-size:48px;margin-bottom:16px;"></i>
                    <h3>Start Building Your Template</h3>
                    <p style="margin-top:8px;">Drag blocks from the palette or click "Clone Base Template"</p>
                </div>
                <?php else: ?>
                <?php foreach ($blocks as $block): ?>
                <?= renderCanvasBlock($block) ?>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div style="text-align:center;padding:12px;">
                <button onclick="TemplateCreator.addBlockAtEnd()" class="btn btn-sm btn-secondary"><i class="fas fa-plus"></i> Add Block</button>
            </div>
        </div>
    </div>

    <div class="creator-properties" id="properties-panel">
        <div style="text-align:center;padding:40px 20px;color:var(--gray-400);" id="no-selection-msg">
            <i class="fas fa-mouse-pointer" style="font-size:32px;margin-bottom:12px;"></i>
            <p>Select a block to edit its properties</p>
        </div>
        <div id="block-properties" style="display:none;"></div>

        <div class="prop-section">
            <h4><i class="fas fa-palette"></i> Global Colors</h4>
            <div class="prop-row"><label>Primary</label><input type="color" id="gc-primary" value="<?= e($globalStyles['colors']['primary'] ?? '#4f46e5') ?>" onchange="TemplateCreator.updateGlobalColor('primary',this.value)"></div>
            <div class="prop-row"><label>Secondary</label><input type="color" id="gc-secondary" value="<?= e($globalStyles['colors']['secondary'] ?? '#0ea5e9') ?>" onchange="TemplateCreator.updateGlobalColor('secondary',this.value)"></div>
            <div class="prop-row"><label>Text</label><input type="color" id="gc-text" value="<?= e($globalStyles['colors']['text'] ?? '#333333') ?>" onchange="TemplateCreator.updateGlobalColor('text',this.value)"></div>
            <div class="prop-row"><label>Background</label><input type="color" id="gc-bg" value="<?= e($globalStyles['colors']['background'] ?? '#ffffff') ?>" onchange="TemplateCreator.updateGlobalColor('background',this.value)"></div>
        </div>

        <div class="prop-section">
            <h4><i class="fas fa-font"></i> Global Fonts</h4>
            <div class="prop-row"><label>Heading</label>
                <select onchange="TemplateCreator.updateGlobalFont('heading',this.value)">
                    <?php foreach (['Inter','Open Sans','Roboto','Montserrat','Poppins','Raleway','Playfair Display','Merriweather','Oswald','Lato'] as $f): ?>
                    <option value="<?= $f ?>" <?= ($globalStyles['fonts']['heading'] ?? '') === $f ? 'selected' : '' ?>><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="prop-row"><label>Body</label>
                <select onchange="TemplateCreator.updateGlobalFont('body',this.value)">
                    <?php foreach (['Inter','Open Sans','Roboto','Montserrat','Poppins','Raleway','Merriweather','Lato','Nunito','Source Sans Pro'] as $f): ?>
                    <option value="<?= $f ?>" <?= ($globalStyles['fonts']['body'] ?? '') === $f ? 'selected' : '' ?>><?= $f ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="prop-section">
            <h4><i class="fas fa-text-width"></i> Punctuation</h4>
            <div style="margin-bottom:8px;font-size:12px;color:var(--gray-500);">Bullet Style</div>
            <div class="punctuation-grid" id="bullet-selector">
                <?php foreach (['●','○','■','▪','▸','→','–','*','+','◆','‣','⁃'] as $b): ?>
                <button onclick="TemplateCreator.setPunctuation('bullet','<?= $b ?>',this)"><?= $b ?></button>
                <?php endforeach; ?>
            </div>
            <div style="margin:12px 0 8px;font-size:12px;color:var(--gray-500);">Separator</div>
            <div class="punctuation-grid" id="separator-selector">
                <?php foreach (['•','|','—','/','·','~','||','◆','–','::','»','»'] as $s): ?>
                <button onclick="TemplateCreator.setPunctuation('separator','<?= $s ?>',this)"><?= $s ?></button>
                <?php endforeach; ?>
            </div>
            <div style="margin:12px 0 8px;font-size:12px;color:var(--gray-500);">Date Format</div>
            <select class="form-select" style="font-size:12px;" onchange="TemplateCreator.setPunctuation('dateFormat',this.value)">
                <option value="Jan 2020 - Present" <?= ($globalStyles['punctuation']['dateFormat'] ?? '') === 'Jan 2020 - Present' ? 'selected' : '' ?>>Jan 2020 - Present</option>
                <option value="01/2020 – Current" <?= ($globalStyles['punctuation']['dateFormat'] ?? '') === '01/2020 – Current' ? 'selected' : '' ?>>01/2020 – Current</option>
                <option value="January 2020 — Present" <?= ($globalStyles['punctuation']['dateFormat'] ?? '') === 'January 2020 — Present' ? 'selected' : '' ?>>January 2020 — Present</option>
                <option value="2020.01 - 2024.12" <?= ($globalStyles['punctuation']['dateFormat'] ?? '') === '2020.01 - 2024.12' ? 'selected' : '' ?>>2020.01 - 2024.12</option>
            </select>
        </div>

        <div class="prop-section">
            <h4><i class="fas fa-image"></i> Graphics</h4>
            <div class="upload-zone" onclick="document.getElementById('graphic-upload').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size:24px;color:var(--gray-400);"></i>
                <p style="font-size:12px;color:var(--gray-500);margin-top:4px;">Upload SVG, PNG, JPG</p>
                <input type="file" id="graphic-upload" accept=".svg,.png,.jpg,.jpeg,.webp" style="display:none;" onchange="TemplateCreator.uploadGraphic(this.files[0])">
            </div>
            <div class="graphics-grid" id="graphics-grid">
                <?php foreach ($graphics as $g): ?>
                <div class="graphic-item" onclick="TemplateCreator.insertGraphic('<?= e($g['file_path']) ?>')">
                    <?php if ($g['file_type'] === 'svg'): ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--gray-50);"><i class="fas fa-file-image" style="color:var(--gray-400);"></i></div>
                    <?php else: ?>
                    <img src="<?= e($g['file_path']) ?>" alt="">
                    <?php endif; ?>
                    <button class="graphic-del" onclick="event.stopPropagation();TemplateCreator.deleteGraphic(<?= $g['id'] ?>)"><i class="fas fa-times"></i></button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php
function renderCanvasBlock(array $block): string {
    $config = json_decode($block['config_json'] ?? '{}', true) ?: [];
    $type = $block['block_type'];
    $id = $block['id'];
    $label = ucfirst(str_replace('-', ' ', $type));
    $icon = match($type) {
        'header' => 'fa-window-maximize', 'sidebar' => 'fa-columns', 'section' => 'fa-file-lines',
        'text' => 'fa-paragraph', 'image' => 'fa-image', 'divider' => 'fa-minus',
        'spacer' => 'fa-arrows-up-down', 'columns' => 'fa-table-columns', 'grid' => 'fa-grid-2',
        'personal-field' => 'fa-user', 'contact-line' => 'fa-address-card',
        'skill-bar' => 'fa-star', 'entry-loop' => 'fa-list', default => 'fa-square',
    };

    $preview = match($type) {
        'header' => '<div style="background:' . ($config['background'] ?? '#848484') . ';padding:20px;text-align:center;color:' . ($config['textColor'] ?? '#fff') . ';">' . ($config['nameField'] ?? '{{first_name}} {{last_name}}') . '</div>',
        'sidebar' => '<div style="background:' . ($config['background'] ?? '#f5f5f5') . ';padding:15px;min-height:60px;">Sidebar content...</div>',
        'section' => '<div style="padding:10px;"><strong style="color:' . ($config['titleColor'] ?? '#4f46e5') . ';">' . ($config['title'] ?? 'Section') . '</strong><div style="color:var(--gray-400);font-size:12px;margin-top:4px;">{{' . ($config['sectionType'] ?? 'experience') . '_entries}}</div></div>',
        'text' => '<div style="padding:10px;color:var(--gray-500);font-size:13px;">' . ($config['content'] ?? 'Text content...') . '</div>',
        'image' => '<div style="padding:10px;text-align:center;"><div style="width:' . ($config['width'] ?? '120px') . ';height:' . ($config['height'] ?? '120px') . ';background:var(--gray-100);margin:0 auto;border-radius:' . ($config['borderRadius'] ?? '0') . ';display:flex;align-items:center;justify-content:center;"><i class="fas fa-image" style="color:var(--gray-400);"></i></div></div>',
        'divider' => '<div style="padding:10px;"><div style="width:' . ($config['width'] ?? '100%') . ';height:' . ($config['height'] ?? '2px') . ';background:' . ($config['color'] ?? '#ddd') . ';margin:' . ($config['margin'] ?? '10px 0') . ';"></div></div>',
        'spacer' => '<div style="padding:5px;text-align:center;color:var(--gray-300);font-size:11px;height:' . ($config['height'] ?? '20px') . ';">Spacer</div>',
        'columns' => '<div style="display:flex;gap:8px;padding:10px;"><div style="flex:1;background:var(--gray-50);padding:15px;border-radius:4px;">Col 1</div><div style="flex:1;background:var(--gray-50);padding:15px;border-radius:4px;">Col 2</div></div>',
        'grid' => '<div style="display:grid;grid-template-columns:' . ($config['columns'] ?? 'repeat(3, 1fr)') . ';gap:8px;padding:10px;"><div style="background:var(--gray-50);padding:10px;border-radius:4px;">1</div><div style="background:var(--gray-50);padding:10px;border-radius:4px;">2</div><div style="background:var(--gray-50);padding:10px;border-radius:4px;">3</div></div>',
        'personal-field' => '<div style="padding:10px;font-size:13px;"><span style="color:var(--gray-500);">' . ($config['label'] ?? '') . '</span> {{' . ($config['field'] ?? 'first_name') . '}}</div>',
        'contact-line' => '<div style="padding:10px;font-size:12px;color:var(--gray-500);">{{email}} ' . ($config['separator'] ?? '•') . ' {{phone}} ' . ($config['separator'] ?? '•') . ' {{address}}</div>',
        'skill-bar' => '<div style="padding:10px;"><div style="display:flex;gap:6px;flex-wrap:wrap;"><span style="background:#eef2ff;color:#4f46e5;padding:3px 10px;border-radius:4px;font-size:11px;">{{skills_entries}}</span></div></div>',
        'entry-loop' => '<div style="padding:10px;font-size:12px;color:var(--gray-500);">{{' . ($config['entryType'] ?? 'experience') . '_entries}}</div>',
        default => '<div style="padding:10px;color:var(--gray-400);">Block</div>',
    };

    return "<div class='canvas-block' data-block-id='{$id}' data-block-type='{$type}'>
        <div class='block-handle'><i class='fas fa-grip-vertical'></i></div>
        <div class='block-actions'>
            <button class='btn-dup' onclick='TemplateCreator.duplicateBlock({$id})' title='Duplicate'><i class='fas fa-copy'></i></button>
            <button class='btn-del' onclick='TemplateCreator.deleteBlock({$id})' title='Delete'><i class='fas fa-trash'></i></button>
        </div>
        <div class='block-content' onclick='TemplateCreator.selectBlock({$id})'>{$preview}</div>
    </div>";
}
?>

<script>
const TEMPLATE_ID = <?= $template['id'] ?>;
const INITIAL_BLOCKS = <?= json_encode($blocks) ?>;
const GLOBAL_STYLES = <?= json_encode($globalStyles) ?>;
</script>
