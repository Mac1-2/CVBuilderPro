<?php
$pageTitle = 'Import Existing CV';
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <h1><i class="fas fa-file-import"></i> Import Your Existing CV</h1>
        <p>Upload your current CV and we'll extract all the data to create a new CV automatically.</p>
    </div>
    <a href="<?= BASE_URL ?>/dashboard" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<div style="max-width:700px;margin:0 auto;">
    <div class="card" style="margin-bottom:24px;">
        <div class="card-body" style="padding:40px;">
            <div id="upload-area" style="border:2px dashed var(--gray-300);border-radius:var(--radius-lg);padding:50px 30px;text-align:center;cursor:pointer;transition:var(--transition);background:var(--gray-50);"
                 ondragover="event.preventDefault();this.style.borderColor='var(--primary)';this.style.background='#eef2ff';"
                 ondragleave="this.style.borderColor='var(--gray-300)';this.style.background='var(--gray-50)';"
                 ondrop="event.preventDefault();this.style.borderColor='var(--gray-300)';this.style.background='var(--gray-50)';handleFileUpload(event.dataTransfer.files[0]);"
                 onclick="document.getElementById('file-input').click()">
                <i class="fas fa-cloud-upload-alt" style="font-size:56px;color:var(--primary);margin-bottom:16px;"></i>
                <h3 style="margin-bottom:8px;font-size:18px;">Drop your CV file here</h3>
                <p style="color:var(--gray-500);font-size:14px;margin-bottom:16px;">or click to browse your files</p>
                <div style="display:flex;justify-content:center;gap:8px;flex-wrap:wrap;">
                    <span class="badge badge-primary">PDF</span>
                    <span class="badge badge-primary">DOCX</span>
                    <span class="badge badge-primary">DOC</span>
                    <span class="badge badge-primary">TXT</span>
                    <span class="badge badge-primary">RTF</span>
                </div>
                <p style="color:var(--gray-400);font-size:12px;margin-top:12px;">Maximum file size: 10MB</p>
                <input type="file" id="file-input" accept=".pdf,.docx,.doc,.txt,.rtf" style="display:none;" onchange="handleFileUpload(this.files[0])">
            </div>
            <div id="upload-result" style="display:none;margin-top:20px;"></div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h3 style="font-size:16px;margin-bottom:12px;"><i class="fas fa-info-circle" style="color:var(--primary);"></i> How it works</h3>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;">
                <div style="text-align:center;">
                    <div style="width:48px;height:48px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 12px;">1</div>
                    <h4 style="font-size:14px;margin-bottom:4px;">Upload</h4>
                    <p style="font-size:12px;color:var(--gray-500);">Upload your existing CV in PDF, DOCX, TXT, or RTF format</p>
                </div>
                <div style="text-align:center;">
                    <div style="width:48px;height:48px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 12px;">2</div>
                    <h4 style="font-size:14px;margin-bottom:4px;">Review</h4>
                    <p style="font-size:12px;color:var(--gray-500);">We extract your data — review and edit anything before creating</p>
                </div>
                <div style="text-align:center;">
                    <div style="width:48px;height:48px;background:var(--primary);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:700;margin:0 auto 12px;">3</div>
                    <h4 style="font-size:14px;margin-bottom:4px;">Create</h4>
                    <p style="font-size:12px;color:var(--gray-500);">One click creates your CV with all imported data ready to edit</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function handleFileUpload(file) {
    if (!file) return;

    const area = document.getElementById('upload-area');
    const result = document.getElementById('upload-result');

    area.innerHTML = '<div class="loading" style="margin:0 auto 16px;"></div><h3 style="color:var(--primary);">Processing your CV...</h3><p style="color:var(--gray-500);font-size:14px;">Extracting text and parsing sections</p>';
    result.style.display = 'none';

    const formData = new FormData();
    formData.append('file', file);

    try {
        const res = await fetch('<?= BASE_URL ?>/import/upload', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            const pd = data.parsed_data;
            const expCount = (pd.experience?.entries || []).length;
            const eduCount = (pd.education?.entries || []).length;
            const skillCount = (pd.skills?.entries || []).length;
            const langCount = (pd.languages?.entries || []).length;

            result.innerHTML = `
                <div class="flash flash-success" style="margin-bottom:16px;">
                    <i class="fas fa-check-circle"></i> CV parsed successfully! Found ${expCount} experience entries, ${eduCount} education entries, ${skillCount} skills${langCount > 0 ? ', ' + langCount + ' languages' : ''}.
                </div>
                <div style="background:var(--gray-50);border-radius:var(--radius);padding:16px;margin-bottom:16px;">
                    <h4 style="font-size:14px;margin-bottom:8px;">Extracted Data Preview</h4>
                    ${pd.personal?.first_name ? `<div style="font-size:13px;color:var(--gray-600);margin-bottom:4px;"><strong>Name:</strong> ${pd.personal.first_name} ${pd.personal.last_name}</div>` : ''}
                    ${pd.personal?.job_title ? `<div style="font-size:13px;color:var(--gray-600);margin-bottom:4px;"><strong>Title:</strong> ${pd.personal.job_title}</div>` : ''}
                    ${pd.personal?.email ? `<div style="font-size:13px;color:var(--gray-600);margin-bottom:4px;"><strong>Email:</strong> ${pd.personal.email}</div>` : ''}
                    ${pd.summary?.text ? `<div style="font-size:13px;color:var(--gray-600);margin-bottom:4px;"><strong>Summary:</strong> ${pd.summary.text.substring(0, 100)}...</div>` : ''}
                </div>
                <a href="<?= BASE_URL ?>/import/review/${data.import_id}" class="btn btn-primary btn-lg btn-block"><i class="fas fa-edit"></i> Review & Create CV</a>
            `;
            result.style.display = 'block';
        } else {
            area.innerHTML = `<i class="fas fa-cloud-upload-alt" style="font-size:56px;color:var(--gray-300);margin-bottom:16px;"></i>
                <h3 style="margin-bottom:8px;font-size:18px;">Drop your CV file here</h3>
                <p style="color:var(--gray-500);font-size:14px;margin-bottom:16px;">or click to browse your files</p>
                <input type="file" id="file-input" accept=".pdf,.docx,.doc,.txt,.rtf" style="display:none;" onchange="handleFileUpload(this.files[0])">`;
            result.innerHTML = `<div class="flash flash-error">${data.error || 'Failed to parse file'}</div>`;
            result.style.display = 'block';
        }
    } catch(e) {
        area.innerHTML = `<i class="fas fa-cloud-upload-alt" style="font-size:56px;color:var(--gray-300);margin-bottom:16px;"></i>
            <h3 style="margin-bottom:8px;font-size:18px;">Drop your CV file here</h3>
            <p style="color:var(--gray-500);font-size:14px;margin-bottom:16px;">or click to browse your files</p>
            <input type="file" id="file-input" accept=".pdf,.docx,.doc,.txt,.rtf" style="display:none;" onchange="handleFileUpload(this.files[0])">`;
        result.innerHTML = '<div class="flash flash-error">Upload failed. Please try again.</div>';
        result.style.display = 'block';
    }
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
