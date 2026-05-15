let currentStep = 'personal';
let autoSaveTimer = null;

function goToStep(step) {
    currentStep = step;
    document.querySelectorAll('.step-item').forEach(el => {
        el.classList.remove('active');
        if (el.dataset.step === step) el.classList.add('active');
    });
    document.querySelectorAll('.step-content').forEach(el => {
        el.classList.remove('active');
        if (el.dataset.stepContent === step) el.classList.add('active');
    });
    updatePreview();
}

function collectPersonalData() {
    return {
        first_name: document.getElementById('p_first_name')?.value || '',
        last_name: document.getElementById('p_last_name')?.value || '',
        job_title: document.getElementById('p_job_title')?.value || '',
        email: document.getElementById('p_email')?.value || '',
        phone: document.getElementById('p_phone')?.value || '',
        address: document.getElementById('p_address')?.value || '',
        linkedin: document.getElementById('p_linkedin')?.value || '',
        website: document.getElementById('p_website')?.value || '',
        photo: ''
    };
}

function collectSummaryData() {
    return { text: document.getElementById('summary_text')?.value || '' };
}

function collectExperienceData() {
    const entries = [];
    document.querySelectorAll('#experience-entries .entry-card').forEach(card => {
        entries.push({
            title: card.querySelector('.exp-title')?.value || '',
            company: card.querySelector('.exp-company')?.value || '',
            start_date: card.querySelector('.exp-start')?.value || '',
            end_date: card.querySelector('.exp-end')?.value || '',
            location: card.querySelector('.exp-location')?.value || '',
            description: card.querySelector('.exp-description')?.value || ''
        });
    });
    return { entries };
}

function collectEducationData() {
    const entries = [];
    document.querySelectorAll('#education-entries .entry-card').forEach(card => {
        entries.push({
            degree: card.querySelector('.edu-degree')?.value || '',
            school: card.querySelector('.edu-school')?.value || '',
            year: card.querySelector('.edu-year')?.value || '',
            description: card.querySelector('.edu-description')?.value || ''
        });
    });
    return { entries };
}

function collectSkillsData() {
    const entries = [];
    document.querySelectorAll('.skill-name').forEach(input => {
        if (input.value.trim()) entries.push({ name: input.value.trim() });
    });
    return { entries };
}

function collectLanguagesData() {
    const entries = [];
    document.querySelectorAll('#language-entries > div').forEach(div => {
        const lang = div.querySelector('.lang-name')?.value || '';
        const level = div.querySelector('.lang-level')?.value || '';
        if (lang) entries.push({ language: lang, level });
    });
    return { entries };
}

function collectCertificationsData() {
    const entries = [];
    document.querySelectorAll('#certification-entries .entry-card').forEach(card => {
        entries.push({
            name: card.querySelector('.cert-name')?.value || '',
            issuer: card.querySelector('.cert-issuer')?.value || '',
            date: card.querySelector('.cert-date')?.value || ''
        });
    });
    return { entries };
}

function collectReferencesData() {
    const entries = [];
    document.querySelectorAll('#reference-entries .entry-card').forEach(card => {
        entries.push({
            name: card.querySelector('.ref-name')?.value || '',
            title: card.querySelector('.ref-title')?.value || '',
            contact: card.querySelector('.ref-contact')?.value || ''
        });
    });
    return { entries };
}

function collectAllData() {
    return {
        personal: collectPersonalData(),
        summary: collectSummaryData(),
        experience: collectExperienceData(),
        education: collectEducationData(),
        skills: collectSkillsData(),
        languages: collectLanguagesData(),
        certifications: collectCertificationsData(),
        references: collectReferencesData()
    };
}

async function autoSave() {
    const data = collectAllData();
    const sections = ['personal', 'summary', 'experience', 'education', 'skills', 'languages', 'certifications', 'references'];

    for (const section of sections) {
        try {
            await fetch('/ajax', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ action: 'save-section', cv_id: CV_ID, section_type: section, content: data[section] })
            });
        } catch (e) { console.error('Save error:', section, e); }
    }

    const btn = document.getElementById('save-btn');
    if (btn) {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Saved';
        setTimeout(() => btn.innerHTML = orig, 2000);
    }
    App.showToast('CV saved successfully!');
}

function scheduleAutoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(autoSave, 2000);
}

document.querySelectorAll('.form-input, .form-textarea, .form-select').forEach(el => {
    el.addEventListener('input', () => { scheduleAutoSave(); updatePreview(); });
    el.addEventListener('change', () => { scheduleAutoSave(); updatePreview(); });
});

function updatePreview() {
    const preview = document.getElementById('cv-preview');
    if (!preview) return;
    const data = collectAllData();
    const personal = data.personal;
    const fullName = `${personal.first_name} ${personal.last_name}`.trim();

    let html = `<style>${TEMPLATE_CSS}</style><div class="cv-${window.currentTemplateSlug || 'modern'}">`;

    html += `<div class="personal-section"><h1 class="cv-name">${fullName || 'Your Name'}</h1>`;
    if (personal.job_title) html += `<p class="cv-title">${personal.job_title}</p>`;
    const contacts = [personal.email, personal.phone, personal.address].filter(Boolean);
    if (contacts.length) html += `<div class="cv-contact">${contacts.join(' &bull; ')}</div>`;
    html += '</div>';

    if (data.summary.text) {
        html += `<div class="summary-section"><h2>Professional Summary</h2><p>${data.summary.text}</p></div>`;
    }

    if (data.experience.entries.length) {
        html += '<div class="experience-section"><h2>Experience</h2>';
        data.experience.entries.forEach(e => {
            const date = e.start_date + (e.end_date ? ' - ' + e.end_date : ' - Present');
            html += `<div class="experience-entry"><div class="entry-title">${e.title}</div><div class="entry-company">${e.company}</div><div class="entry-date">${date}</div><div class="entry-description">${e.description}</div></div>`;
        });
        html += '</div>';
    }

    if (data.education.entries.length) {
        html += '<div class="education-section"><h2>Education</h2>';
        data.education.entries.forEach(e => {
            html += `<div class="education-entry"><div class="entry-title">${e.degree}</div><div class="entry-company">${e.school}</div><div class="entry-date">${e.year}</div></div>`;
        });
        html += '</div>';
    }

    if (data.skills.entries.length) {
        html += '<div class="skills-section"><h2>Skills</h2>';
        data.skills.entries.forEach(s => { html += `<div class="skill-item">${s.name}</div>`; });
        html += '</div>';
    }

    html += '</div>';
    preview.innerHTML = html;
}

function addExperienceEntry() {
    const container = document.getElementById('experience-entries');
    const div = document.createElement('div');
    div.className = 'entry-card';
    div.innerHTML = `<button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group"><label class="form-label">Job Title</label><input type="text" class="form-input exp-title"></div>
            <div class="form-group"><label class="form-label">Company</label><input type="text" class="form-input exp-company"></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group"><label class="form-label">Start Date</label><input type="month" class="form-input exp-start"></div>
            <div class="form-group"><label class="form-label">End Date</label><input type="month" class="form-input exp-end"></div>
        </div>
        <div class="form-group"><label class="form-label">Location</label><input type="text" class="form-input exp-location"></div>
        <div class="form-group"><label class="form-label">Description</label><textarea class="form-textarea exp-description" rows="4"></textarea></div>`;
    container.appendChild(div);
    scheduleAutoSave();
}

function addEducationEntry() {
    const container = document.getElementById('education-entries');
    const div = document.createElement('div');
    div.className = 'entry-card';
    div.innerHTML = `<button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        <div class="form-group"><label class="form-label">Degree / Certificate</label><input type="text" class="form-input edu-degree"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group"><label class="form-label">School / University</label><input type="text" class="form-input edu-school"></div>
            <div class="form-group"><label class="form-label">Year</label><input type="text" class="form-input edu-year"></div>
        </div>
        <div class="form-group"><label class="form-label">Description (optional)</label><textarea class="form-textarea edu-description" rows="3"></textarea></div>`;
    container.appendChild(div);
    scheduleAutoSave();
}

function addSkillEntry() {
    const container = document.getElementById('skills-entries');
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;';
    div.innerHTML = `<input type="text" class="form-input skill-name" placeholder="Skill name"><button class="btn btn-sm btn-danger" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>`;
    container.appendChild(div);
    scheduleAutoSave();
}

function addLanguageEntry() {
    const container = document.getElementById('language-entries');
    const div = document.createElement('div');
    div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;';
    div.innerHTML = `<input type="text" class="form-input lang-name" placeholder="Language" style="flex:1;"><select class="form-select lang-level" style="width:160px;"><option value="">Proficiency</option><option value="Native">Native</option><option value="Fluent">Fluent</option><option value="Advanced">Advanced</option><option value="Intermediate">Intermediate</option><option value="Basic">Basic</option></select><button class="btn btn-sm btn-danger" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>`;
    container.appendChild(div);
    scheduleAutoSave();
}

function addCertificationEntry() {
    const container = document.getElementById('certification-entries');
    const div = document.createElement('div');
    div.className = 'entry-card';
    div.innerHTML = `<button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        <div class="form-group"><label class="form-label">Certification Name</label><input type="text" class="form-input cert-name"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group"><label class="form-label">Issuing Organization</label><input type="text" class="form-input cert-issuer"></div>
            <div class="form-group"><label class="form-label">Date</label><input type="text" class="form-input cert-date"></div>
        </div>`;
    container.appendChild(div);
    scheduleAutoSave();
}

function addReferenceEntry() {
    const container = document.getElementById('reference-entries');
    const div = document.createElement('div');
    div.className = 'entry-card';
    div.innerHTML = `<button class="remove-entry" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>
        <div class="form-group"><label class="form-label">Name</label><input type="text" class="form-input ref-name"></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group"><label class="form-label">Title / Company</label><input type="text" class="form-input ref-title"></div>
            <div class="form-group"><label class="form-label">Contact</label><input type="text" class="form-input ref-contact"></div>
        </div>`;
    container.appendChild(div);
    scheduleAutoSave();
}

async function saveCvTitle(title) {
    try {
        await fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'save-cv-title', cv_id: CV_ID, title })
        });
    } catch (e) { console.error(e); }
}

async function applyTemplate(templateId) {
    try {
        const res = await fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'apply-template', cv_id: CV_ID, template_id: templateId })
        });
        const data = await res.json();
        if (data.success) {
            window.TEMPLATE_CSS = data.template.css_styles;
            window.currentTemplateSlug = data.template.slug;
            updatePreview();
            App.showToast('Template applied!');
        }
    } catch (e) { console.error(e); }
}

async function loadPhrases(sectionType) {
    const container = document.getElementById(`${sectionType}-phrases`);
    if (!container) return;

    try {
        const res = await fetch('/ajax', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'get-phrases', category: sectionType === 'summary' ? 'summary' : sectionType === 'skills' ? 'skills' : 'experience', limit: 15 })
        });
        const data = await res.json();
        if (data.success && data.phrases.length) {
            container.innerHTML = data.phrases.map(p =>
                `<div class="phrase-item" onclick="insertPhrase('${sectionType}', this)" data-text="${p.phrase_text.replace(/'/g, "\\'")}">
                    <div class="phrase-text">${p.phrase_text}</div>
                    <div class="phrase-meta">${p.industry_name || ''} &middot; ${p.category}</div>
                </div>`
            ).join('');
        }
    } catch (e) { console.error(e); }
}

function insertPhrase(sectionType, el) {
    const text = el.dataset.text;
    if (sectionType === 'summary') {
        const textarea = document.getElementById('summary_text');
        if (textarea) {
            textarea.value = textarea.value ? textarea.value + '\n' + text : text;
        }
    } else if (sectionType === 'skills') {
        const skills = text.split(',').map(s => s.trim());
        const container = document.getElementById('skills-entries');
        skills.forEach(skill => {
            if (skill) {
                const div = document.createElement('div');
                div.style.cssText = 'display:flex;gap:8px;margin-bottom:8px;';
                div.innerHTML = `<input type="text" class="form-input skill-name" value="${skill}"><button class="btn btn-sm btn-danger" onclick="this.parentElement.remove()"><i class="fas fa-times"></i></button>`;
                container.appendChild(div);
            }
        });
    } else {
        const textarea = document.querySelector('#experience-entries .entry-card:last-child .exp-description');
        if (textarea) {
            textarea.value = textarea.value ? textarea.value + '\n' + text : text;
        }
    }
    scheduleAutoSave();
    updatePreview();
}

document.addEventListener('DOMContentLoaded', () => {
    updatePreview();
    loadPhrases('summary');
    loadPhrases('experience');
    loadPhrases('skills');
});
