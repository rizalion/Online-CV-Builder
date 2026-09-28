/**
 * CV Builder — Live Preview Engine
 * 
 * Updates a preview panel in real-time as the user types in the form.
 */

document.addEventListener('DOMContentLoaded', () => {
    initLivePreview();
});

function initLivePreview() {
    // Only activate on preview.php or if preview panel exists
    const previewFrame = document.getElementById('livePreviewFrame');
    if (!previewFrame) return;

    // Debounce all form inputs
    const form = document.getElementById('cvForm');
    if (!form) return;

    const debouncedUpdate = debounce(() => {
        updateLivePreview(previewFrame);
    }, 300);

    form.addEventListener('input', debouncedUpdate);
    form.addEventListener('change', debouncedUpdate);

    // Initial render
    updateLivePreview(previewFrame);
}

function updateLivePreview(frame) {
    const data = collectFormData();
    const template = document.getElementById('templateSlug')?.value || 'classic';

    // Build a simplified preview HTML
    const html = renderPreviewHtml(data, template);
    frame.innerHTML = html;
}

function renderPreviewHtml(data, template) {
    const p = data.personal;
    const skills = data.skills.filter(s => s.skill_name);
    const certs = data.certifications.filter(c => c.title);
    const projects = data.projects.filter(pr => pr.title);

    // Simplified inline preview that matches the selected template's style
    let accentColor, bgColor, textColor;

    switch (template) {
        case 'modern':
            accentColor = '#0d9488'; bgColor = '#fff'; textColor = '#111827';
            break;
        case 'creative':
            accentColor = '#f97316'; bgColor = '#0f0f1a'; textColor = '#e2e8f0';
            break;
        default: // classic
            accentColor = '#1a365d'; bgColor = '#fff'; textColor = '#1a202c';
    }

    let html = `<div style="font-family:Inter,sans-serif;color:${textColor};background:${bgColor};padding:24px;border-radius:8px;font-size:13px;line-height:1.6;">`;

    // Name & Contact
    html += `<div style="text-align:center;margin-bottom:20px;">`;
    html += `<h2 style="margin:0;color:${accentColor};font-size:1.4rem;">${escapeHtml(p.full_name || 'Your Name')}</h2>`;
    
    const contactParts = [p.email, p.phone, p.address].filter(Boolean);
    if (contactParts.length) {
        html += `<p style="color:#718096;font-size:0.8rem;margin:4px 0;">${contactParts.map(escapeHtml).join(' · ')}</p>`;
    }
    html += `</div>`;

    // Summary
    if (p.summary) {
        html += `<div style="margin-bottom:16px;">`;
        html += `<h4 style="color:${accentColor};font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;border-bottom:2px solid ${accentColor};padding-bottom:4px;margin-bottom:8px;">Summary</h4>`;
        html += `<p style="font-size:0.82rem;color:#64748b;">${escapeHtml(p.summary)}</p>`;
        html += `</div>`;
    }

    // Experience
    if (data.experience.length > 0 && data.experience[0].company) {
        html += `<div style="margin-bottom:16px;">`;
        html += `<h4 style="color:${accentColor};font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;border-bottom:2px solid ${accentColor};padding-bottom:4px;margin-bottom:8px;">Experience</h4>`;
        data.experience.forEach(exp => {
            if (!exp.company) return;
            html += `<div style="margin-bottom:10px;">`;
            html += `<strong>${escapeHtml(exp.job_title || '')}</strong>`;
            html += `<br><span style="color:${accentColor};font-size:0.82rem;">${escapeHtml(exp.company)}</span>`;
            if (exp.start_date) {
                html += `<br><span style="color:#94a3b8;font-size:0.75rem;">${formatDate(exp.start_date)} – ${formatDate(exp.end_date)}</span>`;
            }
            if (exp.description) {
                html += `<br><span style="font-size:0.78rem;color:#64748b;">${escapeHtml(exp.description)}</span>`;
            }
            html += `</div>`;
        });
        html += `</div>`;
    }

    // Education
    if (data.education.length > 0 && data.education[0].institution) {
        html += `<div style="margin-bottom:16px;">`;
        html += `<h4 style="color:${accentColor};font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;border-bottom:2px solid ${accentColor};padding-bottom:4px;margin-bottom:8px;">Education</h4>`;
        data.education.forEach(edu => {
            if (!edu.institution) return;
            html += `<div style="margin-bottom:10px;">`;
            html += `<strong>${escapeHtml(edu.degree || '')}</strong>`;
            if (edu.field_of_study) html += ` in ${escapeHtml(edu.field_of_study)}`;
            html += `<br><span style="color:${accentColor};font-size:0.82rem;">${escapeHtml(edu.institution)}</span>`;
            if (edu.start_date) {
                html += `<br><span style="color:#94a3b8;font-size:0.75rem;">${formatDate(edu.start_date)} – ${formatDate(edu.end_date)}</span>`;
            }
            html += `</div>`;
        });
        html += `</div>`;
    }

    // Skills
    if (skills.length > 0) {
        html += `<div style="margin-bottom:16px;">`;
        html += `<h4 style="color:${accentColor};font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;border-bottom:2px solid ${accentColor};padding-bottom:4px;margin-bottom:8px;">Skills</h4>`;
        html += `<div style="display:flex;flex-wrap:wrap;gap:6px;">`;
        skills.forEach(skill => {
            const pct = skill.proficiency_level * 20;
            html += `<span style="display:inline-block;padding:3px 10px;background:rgba(99,102,241,0.1);color:${accentColor};border-radius:12px;font-size:0.75rem;font-weight:600;">${escapeHtml(skill.skill_name)} (${pct}%)</span>`;
        });
        html += `</div></div>`;
    }

    // Certifications
    if (certs.length > 0) {
        html += `<div style="margin-bottom:16px;">`;
        html += `<h4 style="color:${accentColor};font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;border-bottom:2px solid ${accentColor};padding-bottom:4px;margin-bottom:8px;">Certifications</h4>`;
        certs.forEach(cert => {
            html += `<div style="margin-bottom:6px;font-size:0.82rem;"><strong>${escapeHtml(cert.title)}</strong>`;
            if (cert.issuer) html += ` — ${escapeHtml(cert.issuer)}`;
            html += `</div>`;
        });
        html += `</div>`;
    }

    // Projects
    if (projects.length > 0) {
        html += `<div style="margin-bottom:16px;">`;
        html += `<h4 style="color:${accentColor};font-size:0.8rem;text-transform:uppercase;letter-spacing:1px;border-bottom:2px solid ${accentColor};padding-bottom:4px;margin-bottom:8px;">Projects</h4>`;
        projects.forEach(proj => {
            html += `<div style="margin-bottom:6px;font-size:0.82rem;"><strong>${escapeHtml(proj.title)}</strong>`;
            if (proj.description) html += `<br><span style="color:#64748b;">${escapeHtml(proj.description)}</span>`;
            html += `</div>`;
        });
        html += `</div>`;
    }

    html += `</div>`;
    return html;
}
