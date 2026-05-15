const TemplateCreator = {
    state: {
        templateId: TEMPLATE_ID,
        blocks: INITIAL_BLOCKS.map(b => ({...b, config: JSON.parse(b.config_json || '{}'), cssOverrides: JSON.parse(b.css_overrides || '{}')})),
        selectedBlockId: null,
        history: [],
        historyIndex: -1,
        maxHistory: 50,
        globalStyles: GLOBAL_STYLES,
        sortable: null
    },

    init() {
        this.initSortable();
        this.initPaletteDrag();
        this.initKeyboard();
        if (this.state.blocks.length) this.selectBlock(this.state.blocks[0].id);
    },

    initSortable() {
        const container = document.getElementById('canvas-blocks');
        if (!container) return;
        this.state.sortable = Sortable.create(container, {
            handle: '.block-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            onEnd: (evt) => {
                const orderedIds = [...container.querySelectorAll('.canvas-block')].map(el => parseInt(el.dataset.blockId));
                this.pushHistory('reorder', this.state.blocks.map(b => b.id), orderedIds);
                this.reorderBlocks(orderedIds);
            }
        });
    },

    initPaletteDrag() {
        document.querySelectorAll('.palette-item').forEach(item => {
            item.addEventListener('click', () => {
                this.addBlock(item.dataset.blockType);
            });
        });
    },

    initKeyboard() {
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'z' && !e.shiftKey) { e.preventDefault(); this.undo(); }
            if (e.ctrlKey && e.shiftKey && e.key === 'Z') { e.preventDefault(); this.redo(); }
            if (e.key === 'Delete' && this.state.selectedBlockId) {
                const el = document.activeElement;
                if (el.tagName !== 'INPUT' && el.tagName !== 'TEXTAREA' && el.tagName !== 'SELECT') {
                    this.deleteBlock(this.state.selectedBlockId);
                }
            }
        });
    },

    pushHistory(action, before, after) {
        this.state.history = this.state.history.slice(0, this.state.historyIndex + 1);
        this.state.history.push({ action, before, after });
        if (this.state.history.length > this.state.maxHistory) this.state.history.shift();
        this.state.historyIndex = this.state.history.length - 1;
        this.updateUndoRedoButtons();
    },

    undo() {
        if (this.state.historyIndex < 0) return;
        const entry = this.state.history[this.state.historyIndex];
        this.state.historyIndex--;
        this.applyHistoryState(entry, 'before');
        this.updateUndoRedoButtons();
    },

    redo() {
        if (this.state.historyIndex >= this.state.history.length - 1) return;
        this.state.historyIndex++;
        const entry = this.state.history[this.state.historyIndex];
        this.applyHistoryState(entry, 'after');
        this.updateUndoRedoButtons();
    },

    applyHistoryState(entry, key) {
        const data = entry[key];
        if (entry.action === 'reorder' && Array.isArray(data)) {
            this.reorderBlocks(data);
        } else if (entry.action === 'add' && data) {
            this.state.blocks = this.state.blocks.filter(b => b.id !== data.id);
            this.renderCanvas();
        } else if (entry.action === 'delete' && data) {
            this.state.blocks.push(data);
            this.renderCanvas();
        } else if (entry.action === 'update' && data) {
            const block = this.state.blocks.find(b => b.id === data.id);
            if (block) {
                block.config = entry.before.config;
                block.cssOverrides = entry.before.cssOverrides;
                this.renderCanvas();
                this.renderProperties();
            }
        }
    },

    updateUndoRedoButtons() {
        const undoBtn = document.getElementById('btn-undo');
        const redoBtn = document.getElementById('btn-redo');
        if (undoBtn) undoBtn.disabled = this.state.historyIndex < 0;
        if (redoBtn) redoBtn.disabled = this.state.historyIndex >= this.state.history.length - 1;
    },

    selectBlock(blockId) {
        this.state.selectedBlockId = blockId;
        document.querySelectorAll('.canvas-block').forEach(el => {
            el.classList.toggle('selected', parseInt(el.dataset.blockId) === blockId);
        });
        document.getElementById('no-selection-msg').style.display = 'none';
        document.getElementById('block-properties').style.display = 'block';
        this.renderProperties();
    },

    renderProperties() {
        const block = this.state.blocks.find(b => b.id === this.state.selectedBlockId);
        if (!block) return;
        const panel = document.getElementById('block-properties');
        const config = block.config;
        const css = block.cssOverrides || {};

        let html = `<div class="prop-section">
            <h4><i class="fas fa-cog"></i> ${this.getBlockLabel(block.block_type)} Settings</h4>`;

        switch (block.block_type) {
            case 'header':
                html += this.propInput('Name Field', 'nameField', config.nameField || '{{first_name}} {{last_name}}');
                html += this.propCheckbox('Show Title', 'showTitle', config.showTitle);
                html += this.propInput('Title Field', 'titleField', config.titleField || '{{job_title}}');
                html += this.propColor('Background', 'background', config.background || '#848484');
                html += this.propColor('Text Color', 'textColor', config.textColor || '#ffffff');
                html += this.propInput('Height', 'height', config.height || '220px');
                html += this.propInput('Font Size', 'fontSize', config.fontSize || '36px');
                html += this.propSelect('Font Weight', 'fontWeight', config.fontWeight || '700', ['100','300','400','500','600','700','900']);
                html += this.propInput('Letter Spacing', 'letterSpacing', config.letterSpacing || '2px');
                html += this.propSelect('Align', 'textAlign', config.textAlign || 'center', ['left','center','right']);
                break;
            case 'sidebar':
                html += this.propColor('Background', 'background', config.background || '#f5f5f5');
                html += this.propInput('Width', 'width', config.width || '300px');
                html += this.propSelect('Position', 'position', config.position || 'left', ['left','right']);
                html += this.propInput('Padding', 'padding', config.padding || '20px');
                break;
            case 'section':
                html += this.propInput('Title', 'title', config.title || 'Section');
                html += this.propSelect('Section Type', 'sectionType', config.sectionType || 'experience', ['experience','education','certifications','languages','references','skills']);
                html += this.propColor('Title Color', 'titleColor', config.titleColor || '#4f46e5');
                html += this.propInput('Title Size', 'titleSize', config.titleSize || '18px');
                html += this.propSelect('Title Weight', 'titleWeight', config.titleWeight || '600', ['400','500','600','700']);
                html += this.propInput('Border Bottom', 'titleBorder', config.titleBorder || '2px solid #4f46e5');
                break;
            case 'text':
                html += this.propTextarea('Content', 'content', config.content || 'Text content...');
                html += this.propSelect('Align', 'textAlign', config.textAlign || 'left', ['left','center','right','justify']);
                html += this.propInput('Font Size', 'fontSize', config.fontSize || '14px');
                html += this.propColor('Color', 'color', config.color || '#333333');
                break;
            case 'image':
                html += this.propInput('Image URL', 'src', config.src || '');
                html += this.propInput('Width', 'width', config.width || '120px');
                html += this.propInput('Height', 'height', config.height || '120px');
                html += this.propInput('Border Radius', 'borderRadius', config.borderRadius || '0');
                html += this.propSelect('Position', 'position', config.position || 'relative', ['relative','absolute']);
                if (config.position === 'absolute') {
                    html += this.propInput('Top', 'top', config.top || '0');
                    html += this.propInput('Left', 'left', config.left || '0');
                }
                break;
            case 'divider':
                html += this.propColor('Color', 'color', config.color || '#dddddd');
                html += this.propInput('Width', 'width', config.width || '100%');
                html += this.propInput('Height', 'height', config.height || '2px');
                html += this.propInput('Margin', 'margin', config.margin || '20px 0');
                break;
            case 'spacer':
                html += this.propInput('Height', 'height', config.height || '20px');
                break;
            case 'columns':
                html += this.propInput('Columns', 'columns', config.columns || '2');
                html += this.propInput('Gap', 'gap', config.gap || '20px');
                break;
            case 'grid':
                html += this.propInput('Grid Columns', 'columns', config.columns || 'repeat(3, 1fr)');
                html += this.propInput('Gap', 'gap', config.gap || '10px');
                break;
            case 'personal-field':
                html += this.propSelect('Field', 'field', config.field || 'first_name', ['first_name','last_name','job_title','email','phone','address','linkedin','website']);
                html += this.propInput('Label', 'label', config.label || '');
                break;
            case 'contact-line':
                html += this.propInput('Separator', 'separator', config.separator || ' &bull; ');
                html += this.propInput('Fields (comma-sep)', 'fields', (config.fields || ['email','phone','address']).join(','));
                break;
            case 'skill-bar':
                html += this.propSelect('Display Style', 'displayStyle', config.displayStyle || 'tags', ['tags','list','bars']);
                html += this.propColor('Tag Background', 'tagBg', config.tagBg || '#eef2ff');
                html += this.propColor('Tag Color', 'tagColor', config.tagColor || '#4f46e5');
                html += this.propInput('Tag Radius', 'tagRadius', config.tagRadius || '4px');
                html += this.propInput('Tag Padding', 'tagPadding', config.tagPadding || '4px 12px');
                break;
            case 'entry-loop':
                html += this.propSelect('Entry Type', 'entryType', config.entryType || 'experience', ['experience','education','certifications','languages','references']);
                html += this.propSelect('Layout', 'layout', config.layout || 'stacked', ['stacked','timeline','grid','compact']);
                break;
        }

        html += `</div><div class="prop-section"><h4><i class="fas fa-ruler-combined"></i> Spacing</h4>`;
        html += `<div style="margin-bottom:6px;font-size:11px;color:var(--gray-500);">Margin (px)</div>`;
        html += `<div class="spacing-grid">`;
        html += `<input type="number" placeholder="Top" value="${css['margin-top'] || ''}" onchange="TemplateCreator.updateCssOverride('margin-top',this.value)">`;
        html += `<input type="number" placeholder="Right" value="${css['margin-right'] || ''}" onchange="TemplateCreator.updateCssOverride('margin-right',this.value)">`;
        html += `<input type="number" placeholder="Bottom" value="${css['margin-bottom'] || ''}" onchange="TemplateCreator.updateCssOverride('margin-bottom',this.value)">`;
        html += `<input type="number" placeholder="Left" value="${css['margin-left'] || ''}" onchange="TemplateCreator.updateCssOverride('margin-left',this.value)">`;
        html += `</div>`;
        html += `<div style="margin:8px 0 6px;font-size:11px;color:var(--gray-500);">Padding (px)</div>`;
        html += `<div class="spacing-grid">`;
        html += `<input type="number" placeholder="Top" value="${css['padding-top'] || ''}" onchange="TemplateCreator.updateCssOverride('padding-top',this.value)">`;
        html += `<input type="number" placeholder="Right" value="${css['padding-right'] || ''}" onchange="TemplateCreator.updateCssOverride('padding-right',this.value)">`;
        html += `<input type="number" placeholder="Bottom" value="${css['padding-bottom'] || ''}" onchange="TemplateCreator.updateCssOverride('padding-bottom',this.value)">`;
        html += `<input type="number" placeholder="Left" value="${css['padding-left'] || ''}" onchange="TemplateCreator.updateCssOverride('padding-left',this.value)">`;
        html += `</div></div>`;

        panel.innerHTML = html;
    },

    propInput(label, key, value) {
        return `<div class="prop-row"><label>${label}</label><input type="text" value="${this.esc(value)}" onchange="TemplateCreator.updateConfig('${key}',this.value)"></div>`;
    },
    propColor(label, key, value) {
        return `<div class="prop-row"><label>${label}</label><input type="color" value="${value}" onchange="TemplateCreator.updateConfig('${key}',this.value)"></div>`;
    },
    propSelect(label, key, value, options) {
        const opts = options.map(o => `<option value="${o}" ${o === value ? 'selected' : ''}>${o}</option>`).join('');
        return `<div class="prop-row"><label>${label}</label><select onchange="TemplateCreator.updateConfig('${key}',this.value)">${opts}</select></div>`;
    },
    propTextarea(label, key, value) {
        return `<div class="prop-row" style="flex-direction:column;align-items:stretch;"><label>${label}</label><textarea rows="3" style="width:100%;padding:6px 8px;border:1px solid var(--gray-200);border-radius:4px;font-size:12px;resize:vertical;" onchange="TemplateCreator.updateConfig('${key}',this.value)">${this.esc(value)}</textarea></div>`;
    },
    propCheckbox(label, key, checked) {
        return `<div class="prop-row"><label>${label}</label><input type="checkbox" ${checked ? 'checked' : ''} onchange="TemplateCreator.updateConfig('${key}',this.checked)"></div>`;
    },

    esc(str) {
        return String(str || '').replace(/"/g, '&quot;').replace(/</g, '&lt;');
    },

    getBlockLabel(type) {
        return type.split('-').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
    },

    updateConfig(key, value) {
        const block = this.state.blocks.find(b => b.id === this.state.selectedBlockId);
        if (!block) return;
        const before = { id: block.id, config: {...block.config}, cssOverrides: {...block.cssOverrides} };
        block.config[key] = value;
        this.pushHistory('update', before, { id: block.id });
        this.saveBlock(block.id);
        this.renderCanvasBlock(block.id);
    },

    updateCssOverride(prop, value) {
        const block = this.state.blocks.find(b => b.id === this.state.selectedBlockId);
        if (!block) return;
        if (value === '') {
            delete block.cssOverrides[prop];
        } else {
            block.cssOverrides[prop] = value + 'px';
        }
        this.saveBlock(block.id);
        this.renderCanvasBlock(block.id);
    },

    addBlock(type, order) {
        const maxOrder = this.state.blocks.length;
        const newOrder = order !== undefined ? order : maxOrder;
        const block = {
            id: Date.now(),
            template_id: this.state.templateId,
            block_type: type,
            block_order: newOrder,
            parent_id: null,
            config_json: '{}',
            css_overrides: '{}',
            config: this.getDefaultConfig(type),
            cssOverrides: {}
        };
        this.state.blocks.splice(newOrder, 0, block);
        this.pushHistory('add', null, { id: block.id });
        this.renderCanvas();
        this.saveBlock(block.id);
        this.selectBlock(block.id);
        document.getElementById('empty-canvas-msg')?.remove();
    },

    addBlockAtEnd() {
        this.addBlock('text');
    },

    getDefaultConfig(type) {
        const defaults = {
            'header': { nameField: '{{first_name}} {{last_name}}', titleField: '{{job_title}}', background: '#848484', textColor: '#ffffff', height: '220px', fontSize: '36px', fontWeight: '700', letterSpacing: '2px', textAlign: 'center', showTitle: true },
            'sidebar': { background: '#f5f5f5', width: '300px', position: 'left', padding: '20px' },
            'section': { title: 'Section', sectionType: 'experience', titleColor: '#4f46e5', titleSize: '18px', titleWeight: '600', titleBorder: '2px solid #4f46e5' },
            'text': { content: 'Text content...', textAlign: 'left', fontSize: '14px', color: '#333333' },
            'image': { src: '', width: '120px', height: '120px', borderRadius: '0', position: 'relative' },
            'divider': { color: '#dddddd', width: '100%', height: '2px', margin: '20px 0' },
            'spacer': { height: '20px' },
            'columns': { columns: '2', gap: '20px' },
            'grid': { columns: 'repeat(3, 1fr)', gap: '10px' },
            'personal-field': { field: 'first_name', label: '' },
            'contact-line': { separator: ' &bull; ', fields: ['email', 'phone', 'address'] },
            'skill-bar': { displayStyle: 'tags', tagBg: '#eef2ff', tagColor: '#4f46e5', tagRadius: '4px', tagPadding: '4px 12px' },
            'entry-loop': { entryType: 'experience', layout: 'stacked' }
        };
        return defaults[type] || {};
    },

    deleteBlock(blockId) {
        const idx = this.state.blocks.findIndex(b => b.id === blockId);
        if (idx === -1) return;
        const block = this.state.blocks.splice(idx, 1)[0];
        this.pushHistory('delete', block, null);
        this.state.selectedBlockId = null;
        document.getElementById('no-selection-msg').style.display = '';
        document.getElementById('block-properties').style.display = 'none';
        this.renderCanvas();
        fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'delete-block', block_id: blockId })
        });
    },

    duplicateBlock(blockId) {
        const block = this.state.blocks.find(b => b.id === blockId);
        if (!block) return;
        const idx = this.state.blocks.indexOf(block);
        const newBlock = {
            ...block,
            id: Date.now(),
            config: {...block.config},
            cssOverrides: {...block.cssOverrides}
        };
        this.state.blocks.splice(idx + 1, 0, newBlock);
        this.renderCanvas();
        fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'duplicate-block', block_id: blockId })
        }).then(r => r.json()).then(data => {
            if (data.block_id) {
                newBlock.id = data.block_id;
                this.renderCanvas();
            }
        });
        this.selectBlock(newBlock.id);
    },

    reorderBlocks(orderedIds) {
        const reordered = [];
        for (const id of orderedIds) {
            const block = this.state.blocks.find(b => b.id === id);
            if (block) reordered.push(block);
        }
        this.state.blocks = reordered;
        this.state.blocks.forEach((b, i) => b.block_order = i);
        this.renderCanvas();
        fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'reorder-blocks', template_id: this.state.templateId, ordered_ids: orderedIds })
        });
    },

    saveBlock(blockId) {
        const block = this.state.blocks.find(b => b.id === blockId);
        if (!block) return;
        fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({
                action: 'save-block',
                block_id: blockId,
                template_id: this.state.templateId,
                block_type: block.block_type,
                config: block.config,
                css_overrides: block.cssOverrides,
                block_order: block.block_order
            })
        });
    },

    renderCanvas() {
        const container = document.getElementById('canvas-blocks');
        if (!container) return;
        container.innerHTML = this.state.blocks.map(b => this.renderCanvasBlockHtml(b)).join('');
        this.initSortable();
    },

    renderCanvasBlock(blockId) {
        const block = this.state.blocks.find(b => b.id === blockId);
        if (!block) return;
        const el = document.querySelector(`.canvas-block[data-block-id="${blockId}"]`);
        if (el) el.outerHTML = this.renderCanvasBlockHtml(block);
    },

    renderCanvasBlockHtml(block) {
        const config = block.config || {};
        const type = block.block_type;
        const id = block.id;
        const isSelected = this.state.selectedBlockId === id;

        const preview = this.getBlockPreview(block);

        return `<div class='canvas-block ${isSelected ? 'selected' : ''}' data-block-id='${id}' data-block-type='${type}'>
            <div class='block-handle'><i class='fas fa-grip-vertical'></i></div>
            <div class='block-actions'>
                <button class='btn-dup' onclick='TemplateCreator.duplicateBlock(${id})' title='Duplicate'><i class='fas fa-copy'></i></button>
                <button class='btn-del' onclick='TemplateCreator.deleteBlock(${id})' title='Delete'><i class='fas fa-trash'></i></button>
            </div>
            <div class='block-content' onclick='TemplateCreator.selectBlock(${id})'>${preview}</div>
        </div>`;
    },

    getBlockPreview(block) {
        const c = block.config || {};
        switch (block.block_type) {
            case 'header':
                return `<div style="background:${c.background || '#848484'};padding:20px;text-align:center;color:${c.textColor || '#fff'};">${c.nameField || '{{first_name}} {{last_name}}'}</div>`;
            case 'sidebar':
                return `<div style="background:${c.background || '#f5f5f5'};padding:15px;min-height:60px;">Sidebar</div>`;
            case 'section':
                return `<div style="padding:10px;"><strong style="color:${c.titleColor || '#4f46e5'};">${c.title || 'Section'}</strong><div style="color:#9ca3af;font-size:12px;margin-top:4px;">{{${c.sectionType || 'experience'}_entries}}</div></div>`;
            case 'text':
                return `<div style="padding:10px;color:#6b7280;font-size:13px;">${c.content || 'Text...'}</div>`;
            case 'image':
                return `<div style="padding:10px;text-align:center;"><div style="width:${c.width || '120px'};height:${c.height || '120px'};background:#f3f4f6;margin:0 auto;border-radius:${c.borderRadius || '0'};display:flex;align-items:center;justify-content:center;"><i class="fas fa-image" style="color:#9ca3af;"></i></div></div>`;
            case 'divider':
                return `<div style="padding:10px;"><div style="width:${c.width || '100%'};height:${c.height || '2px'};background:${c.color || '#ddd'};margin:${c.margin || '10px 0'};"></div></div>`;
            case 'spacer':
                return `<div style="padding:5px;text-align:center;color:#d1d5db;font-size:11px;height:${c.height || '20px'};">Spacer</div>`;
            case 'columns':
                return `<div style="display:flex;gap:8px;padding:10px;"><div style="flex:1;background:#f9fafb;padding:15px;border-radius:4px;">Col 1</div><div style="flex:1;background:#f9fafb;padding:15px;border-radius:4px;">Col 2</div></div>`;
            case 'grid':
                return `<div style="display:grid;grid-template-columns:${c.columns || 'repeat(3, 1fr)'};gap:8px;padding:10px;"><div style="background:#f9fafb;padding:10px;border-radius:4px;">1</div><div style="background:#f9fafb;padding:10px;border-radius:4px;">2</div><div style="background:#f9fafb;padding:10px;border-radius:4px;">3</div></div>`;
            case 'personal-field':
                return `<div style="padding:10px;font-size:13px;"><span style="color:#6b7280;">${c.label || ''}</span> {{${c.field || 'first_name'}}}</div>`;
            case 'contact-line':
                return `<div style="padding:10px;font-size:12px;color:#6b7280;">{{email}} ${c.separator || '•'} {{phone}} ${c.separator || '•'} {{address}}</div>`;
            case 'skill-bar':
                return `<div style="padding:10px;"><span style="background:#eef2ff;color:#4f46e5;padding:3px 10px;border-radius:${c.tagRadius || '4px'};font-size:11px;">{{skills_entries}}</span></div>`;
            case 'entry-loop':
                return `<div style="padding:10px;font-size:12px;color:#6b7280;">{{${c.entryType || 'experience'}_entries}}</div>`;
            default:
                return `<div style="padding:10px;color:#9ca3af;">Block</div>`;
        }
    },

    updateGlobalColor(name, value) {
        if (!this.state.globalStyles.colors) this.state.globalStyles.colors = {};
        this.state.globalStyles.colors[name] = value;
        fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'save-global-styles', template_id: this.state.templateId, styles: this.state.globalStyles })
        });
    },

    updateGlobalFont(type, value) {
        if (!this.state.globalStyles.fonts) this.state.globalStyles.fonts = {};
        this.state.globalStyles.fonts[type] = value;
        fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'save-global-styles', template_id: this.state.templateId, styles: this.state.globalStyles })
        });
    },

    setPunctuation(type, value, btn) {
        if (!this.state.globalStyles.punctuation) this.state.globalStyles.punctuation = {};
        this.state.globalStyles.punctuation[type] = value;
        if (btn) {
            btn.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
        fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'save-global-styles', template_id: this.state.templateId, styles: this.state.globalStyles })
        });
    },

    uploadGraphic(file) {
        if (!file) return;
        const formData = new FormData();
        formData.append('graphic', file);
        formData.append('template_id', this.state.templateId);
        formData.append('action', 'upload-graphic');
        fetch('/ajax', { method: 'POST', body: formData })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const grid = document.getElementById('graphics-grid');
                    grid.innerHTML += `<div class="graphic-item" onclick="TemplateCreator.insertGraphic('${data.path}')">
                        ${data.type === 'svg' ? '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f9fafb;"><i class="fas fa-file-image" style="color:#9ca3af;"></i></div>' : `<img src="${data.path}" alt="">`}
                        <button class="graphic-del" onclick="event.stopPropagation();TemplateCreator.deleteGraphic(${data.id})"><i class="fas fa-times"></i></button>
                    </div>`;
                    App.showToast('Graphic uploaded');
                }
            });
    },

    insertGraphic(path) {
        if (this.state.selectedBlockId) {
            const block = this.state.blocks.find(b => b.id === this.state.selectedBlockId);
            if (block && block.block_type === 'image') {
                block.config.src = path;
                this.saveBlock(block.id);
                this.renderCanvasBlock(block.id);
                this.renderProperties();
            }
        }
    },

    deleteGraphic(graphicId) {
        fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'delete-graphic', graphic_id: graphicId })
        }).then(() => {
            event.target.closest('.graphic-item').remove();
        });
    },

    saveTemplateName(name) {
        fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'save-cv-title', cv_id: this.state.templateId, title: name })
        });
    },

    regenerate() {
        fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'regenerate-template', template_id: this.state.templateId })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                App.showToast('Template saved and regenerated!');
            } else {
                App.showToast('Save failed', 'error');
            }
        });
    },

    cloneFromBase() {
        const baseId = prompt('Enter base template ID (1-16):');
        if (!baseId) return;
        fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'clone-template', template_id: parseInt(baseId), name: this.state.templateId + ' Clone' })
        }).then(r => r.json()).then(data => {
            if (data.success) {
                window.location.href = `/creator/edit/${data.template_id}`;
            }
        });
    },

    resetBlocks() {
        if (!confirm('Clear all blocks? This cannot be undone.')) return;
        this.state.blocks.forEach(b => {
            fetch('/ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ action: 'delete-block', block_id: b.id })
            });
        });
        this.state.blocks = [];
        this.renderCanvas();
        document.getElementById('canvas-blocks').innerHTML = `<div style="text-align:center;padding:80px 20px;color:var(--gray-400);" id="empty-canvas-msg"><i class="fas fa-plus-circle" style="font-size:48px;margin-bottom:16px;"></i><h3>Start Building Your Template</h3><p style="margin-top:8px;">Drag blocks from the palette or click "Clone Base Template"</p></div>`;
    }
};

document.addEventListener('DOMContentLoaded', () => TemplateCreator.init());
