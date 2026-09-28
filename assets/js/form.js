/**
 * CV Builder — Multi-Step Form Logic
 * 
 * Handles step navigation, dynamic rows, validation, and data submission.
 */

let currentStep = 1;
const totalSteps = 5;
let educationCount = 0;
let experienceCount = 0;
let skillCount = 0;
let certCount = 0;
let projectCount = 0;

document.addEventListener('DOMContentLoaded', () => {
    initForm();
    initCharCounter();
});

// ── Initialization ───────────────────────────────

function initForm() {
    // If editing an existing CV, populate fields
    if (window.existingCvData) {
        populateExistingData(window.existingCvData);
    } else {
        // Add one empty entry for each section
        addEducation();
        addExperience();
        addSkill();
    }
    updateProgressBar();
}

function populateExistingData(data) {
    // Education
    if (data.education && data.education.length > 0) {
        data.education.forEach(edu => addEducation(edu));
    } else {
        addEducation();
    }

    // Experience
    if (data.experience && data.experience.length > 0) {
        data.experience.forEach(exp => addExperience(exp));
    } else {
        addExperience();
    }

    // Skills
    if (data.skills && data.skills.length > 0) {
        data.skills.forEach(skill => addSkill(skill));
    } else {
        addSkill();
    }

    // Certifications
    if (data.certifications && data.certifications.length > 0) {
        data.certifications.forEach(cert => addCertification(cert));
    }

    // Projects
    if (data.projects && data.projects.length > 0) {
        data.projects.forEach(proj => addProject(proj));
    }
}

// ── Step Navigation ──────────────────────────────

function nextStep() {
    if (!validateCurrentStep()) return;
    saveCurrentStep();

    if (currentStep < totalSteps) {
        currentStep++;
        updateStepUI();
    }
}

function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        updateStepUI();
    }
}

function goToStep(step) {
    // Only allow going to completed steps or current step
    if (step > currentStep) return;
    currentStep = step;
    updateStepUI();
}

function updateStepUI() {
    // Hide all panels, show current
    document.querySelectorAll('.step-panel').forEach(panel => panel.classList.remove('active'));
    document.getElementById(`step${currentStep}`).classList.add('active');

    // Update progress indicators
    document.querySelectorAll('.wizard-step').forEach(step => {
        const stepNum = parseInt(step.dataset.step);
        step.classList.remove('active', 'completed');
        if (stepNum === currentStep) {
            step.classList.add('active');
        } else if (stepNum < currentStep) {
            step.classList.add('completed');
            step.querySelector('.step-circle').innerHTML = '<i class="fas fa-check"></i>';
        } else {
            step.querySelector('.step-circle').innerHTML = `<span>${stepNum}</span>`;
        }
    });

    updateProgressBar();

    // Toggle nav buttons
    document.getElementById('prevBtn').style.display = currentStep > 1 ? '' : 'none';
    document.getElementById('nextBtn').style.display = currentStep < totalSteps ? '' : 'none';
    document.getElementById('submitBtn').style.display = currentStep === totalSteps ? '' : 'none';

    // Scroll to top of form
    document.querySelector('.form-wizard').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function updateProgressBar() {
    const fill = document.getElementById('progressFill');
    const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
    // Calculate width relative to the track (between first and last step circles)
    const trackWidth = document.querySelector('.wizard-progress').offsetWidth - 80;
    fill.style.width = `${(progress / 100) * trackWidth}px`;
}

// ── Validation ───────────────────────────────────

function validateCurrentStep() {
    let errors = [];

    switch (currentStep) {
        case 1:
            errors = validateStep1();
            break;
        case 2:
            errors = validateStep2();
            break;
        case 3:
            errors = validateStep3();
            break;
        case 4:
            // Skills are optional — no strict validation
            break;
        case 5:
            if (!document.getElementById('templateSlug').value) {
                errors.push('Please select a template.');
            }
            break;
    }

    if (errors.length > 0) {
        showToast(errors[0], 'danger');
        return false;
    }
    return true;
}

function validateStep1() {
    const errors = [];
    const name = document.getElementById('fullName').value.trim();
    const email = document.getElementById('cvEmail').value.trim();

    if (!name) errors.push('Full name is required.');
    if (!email) errors.push('Email is required.');
    else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.push('Please enter a valid email.');

    const phone = document.getElementById('phone').value.trim();
    if (phone && !/^[+]?[\d\s\-()]{7,20}$/.test(phone)) errors.push('Please enter a valid phone number.');

    // Highlight invalid fields
    highlightField('fullName', !name);
    highlightField('cvEmail', !email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email));

    return errors;
}

function validateStep2() {
    const errors = [];
    const entries = document.querySelectorAll('#educationSection .dynamic-entry');

    entries.forEach((entry, i) => {
        const inst = entry.querySelector('[name^="edu_institution"]').value.trim();
        const degree = entry.querySelector('[name^="edu_degree"]').value.trim();
        const start = entry.querySelector('[name^="edu_start"]').value;

        if (!inst) errors.push(`Education ${i + 1}: Institution is required.`);
        if (!degree) errors.push(`Education ${i + 1}: Degree is required.`);
        if (!start) errors.push(`Education ${i + 1}: Start date is required.`);
    });

    return errors;
}

function validateStep3() {
    const errors = [];
    const entries = document.querySelectorAll('#experienceSection .dynamic-entry');

    entries.forEach((entry, i) => {
        const company = entry.querySelector('[name^="exp_company"]').value.trim();
        const title = entry.querySelector('[name^="exp_title"]').value.trim();
        const start = entry.querySelector('[name^="exp_start"]').value;

        if (!company) errors.push(`Experience ${i + 1}: Company is required.`);
        if (!title) errors.push(`Experience ${i + 1}: Job title is required.`);
        if (!start) errors.push(`Experience ${i + 1}: Start date is required.`);
    });

    return errors;
}

function highlightField(id, isInvalid) {
    const el = document.getElementById(id);
    if (!el) return;
    if (isInvalid) {
        el.style.borderColor = '#ef4444';
        el.addEventListener('input', () => { el.style.borderColor = ''; }, { once: true });
    } else {
        el.style.borderColor = '';
    }
}

// ── Save Step (AJAX) ─────────────────────────────

async function saveCurrentStep() {
    const formData = collectFormData();

    try {
        if (currentStep === 1) {
            // Save profile + upload image
            const fd = new FormData(document.getElementById('cvForm'));
            fd.append('action', 'save_profile');

            const res = await fetch(`${window.baseUrl}/api/save_profile.php`, {
                method: 'POST',
                body: fd,
            });
            const result = await res.json();
            if (result.success && result.cv_id) {
                document.getElementById('cvIdField').value = result.cv_id;
            }
        } else if (currentStep >= 2 && currentStep <= 4) {
            const cvId = document.getElementById('cvIdField').value;
            if (!cvId) return;

            const sectionData = {
                csrf_token: getCsrfToken(),
                cv_id: cvId,
            };

            if (currentStep === 2) {
                sectionData.section = 'education';
                sectionData.entries = JSON.stringify(collectEducation());
            } else if (currentStep === 3) {
                sectionData.section = 'experience';
                sectionData.entries = JSON.stringify(collectExperience());
            } else if (currentStep === 4) {
                sectionData.section = 'all_step4';
                sectionData.skills = JSON.stringify(collectSkills());
                sectionData.certifications = JSON.stringify(collectCertifications());
                sectionData.projects = JSON.stringify(collectProjects());
            }

            await apiPost(`${window.baseUrl}/api/save_section.php`, sectionData);
        }
    } catch (err) {
        console.error('Auto-save error:', err);
    }
}

// ── Data Collection ──────────────────────────────

function collectFormData() {
    return {
        personal: {
            full_name: document.getElementById('fullName')?.value || '',
            email: document.getElementById('cvEmail')?.value || '',
            phone: document.getElementById('phone')?.value || '',
            address: document.getElementById('address')?.value || '',
            linkedin: document.getElementById('linkedin')?.value || '',
            website: document.getElementById('website')?.value || '',
            summary: document.getElementById('summary')?.value || '',
        },
        education: collectEducation(),
        experience: collectExperience(),
        skills: collectSkills(),
        certifications: collectCertifications(),
        projects: collectProjects(),
    };
}

function collectEducation() {
    const entries = [];
    document.querySelectorAll('#educationSection .dynamic-entry').forEach((entry, i) => {
        entries.push({
            institution: entry.querySelector('[name^="edu_institution"]')?.value || '',
            degree: entry.querySelector('[name^="edu_degree"]')?.value || '',
            field_of_study: entry.querySelector('[name^="edu_field"]')?.value || '',
            start_date: entry.querySelector('[name^="edu_start"]')?.value || '',
            end_date: entry.querySelector('[name^="edu_end"]')?.value || '',
            grade: entry.querySelector('[name^="edu_grade"]')?.value || '',
            sort_order: i,
        });
    });
    return entries;
}

function collectExperience() {
    const entries = [];
    document.querySelectorAll('#experienceSection .dynamic-entry').forEach((entry, i) => {
        entries.push({
            company: entry.querySelector('[name^="exp_company"]')?.value || '',
            job_title: entry.querySelector('[name^="exp_title"]')?.value || '',
            location: entry.querySelector('[name^="exp_location"]')?.value || '',
            start_date: entry.querySelector('[name^="exp_start"]')?.value || '',
            end_date: entry.querySelector('[name^="exp_end"]')?.value || '',
            description: entry.querySelector('[name^="exp_desc"]')?.value || '',
            sort_order: i,
        });
    });
    return entries;
}

function collectSkills() {
    const entries = [];
    document.querySelectorAll('#skillsContainer .dynamic-entry').forEach((entry, i) => {
        entries.push({
            skill_name: entry.querySelector('[name^="skill_name"]')?.value || '',
            proficiency_level: parseInt(entry.querySelector('[name^="skill_level"]')?.value || '3'),
            sort_order: i,
        });
    });
    return entries.filter(s => s.skill_name.trim());
}

function collectCertifications() {
    const entries = [];
    document.querySelectorAll('#certsContainer .dynamic-entry').forEach((entry, i) => {
        entries.push({
            title: entry.querySelector('[name^="cert_title"]')?.value || '',
            issuer: entry.querySelector('[name^="cert_issuer"]')?.value || '',
            issue_date: entry.querySelector('[name^="cert_date"]')?.value || '',
            credential_url: entry.querySelector('[name^="cert_url"]')?.value || '',
            sort_order: i,
        });
    });
    return entries.filter(c => c.title.trim());
}

function collectProjects() {
    const entries = [];
    document.querySelectorAll('#projectsContainer .dynamic-entry').forEach((entry, i) => {
        entries.push({
            title: entry.querySelector('[name^="proj_title"]')?.value || '',
            description: entry.querySelector('[name^="proj_desc"]')?.value || '',
            tech_stack: entry.querySelector('[name^="proj_tech"]')?.value || '',
            project_url: entry.querySelector('[name^="proj_url"]')?.value || '',
            sort_order: i,
        });
    });
    return entries.filter(p => p.title.trim());
}

// ── Dynamic Rows: Education ──────────────────────

function addEducation(data = null) {
    educationCount++;
    const n = educationCount;
    const section = document.getElementById('educationSection');

    const html = `
        <div class="dynamic-entry" id="edu_${n}">
            <div class="entry-header">
                <span class="entry-number">Education #${n}</span>
                <button type="button" class="btn-remove-entry" onclick="removeEntry('edu_${n}', 'education')" title="Remove">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Institution *</label>
                    <input type="text" class="form-control" name="edu_institution_${n}" 
                           value="${escapeHtml(data?.institution || '')}" placeholder="e.g. MIT" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Degree *</label>
                    <input type="text" class="form-control" name="edu_degree_${n}" 
                           value="${escapeHtml(data?.degree || '')}" placeholder="e.g. Bachelor of Science" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Field of Study</label>
                    <input type="text" class="form-control" name="edu_field_${n}" 
                           value="${escapeHtml(data?.field_of_study || '')}" placeholder="e.g. Computer Science">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Grade / GPA</label>
                    <input type="text" class="form-control" name="edu_grade_${n}" 
                           value="${escapeHtml(data?.grade || '')}" placeholder="e.g. 3.8/4.0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Date *</label>
                    <input type="date" class="form-control" name="edu_start_${n}" 
                           value="${data?.start_date || ''}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="edu_end_${n}" 
                           value="${data?.end_date || ''}" id="edu_end_${n}">
                    <div class="present-checkbox">
                        <input type="checkbox" id="edu_present_${n}" 
                               onchange="togglePresent('edu_end_${n}', this.checked)"
                               ${!data?.end_date && data?.institution ? 'checked' : ''}>
                        <label for="edu_present_${n}">Currently studying here</label>
                    </div>
                </div>
            </div>
        </div>
    `;
    section.insertAdjacentHTML('beforeend', html);

    // If editing with no end date, disable the field
    if (data && !data.end_date && data.institution) {
        const endField = document.getElementById(`edu_end_${n}`);
        if (endField) endField.disabled = true;
    }
}

// ── Dynamic Rows: Experience ─────────────────────

function addExperience(data = null) {
    experienceCount++;
    const n = experienceCount;
    const section = document.getElementById('experienceSection');

    const html = `
        <div class="dynamic-entry" id="exp_${n}">
            <div class="entry-header">
                <span class="entry-number">Experience #${n}</span>
                <button type="button" class="btn-remove-entry" onclick="removeEntry('exp_${n}', 'experience')" title="Remove">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Company *</label>
                    <input type="text" class="form-control" name="exp_company_${n}" 
                           value="${escapeHtml(data?.company || '')}" placeholder="e.g. Google" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Job Title *</label>
                    <input type="text" class="form-control" name="exp_title_${n}" 
                           value="${escapeHtml(data?.job_title || '')}" placeholder="e.g. Software Engineer" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Location</label>
                    <input type="text" class="form-control" name="exp_location_${n}" 
                           value="${escapeHtml(data?.location || '')}" placeholder="e.g. San Francisco, CA">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Start Date *</label>
                    <input type="date" class="form-control" name="exp_start_${n}" 
                           value="${data?.start_date || ''}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="exp_end_${n}" 
                           value="${data?.end_date || ''}" id="exp_end_${n}">
                    <div class="present-checkbox">
                        <input type="checkbox" id="exp_present_${n}" 
                               onchange="togglePresent('exp_end_${n}', this.checked)"
                               ${!data?.end_date && data?.company ? 'checked' : ''}>
                        <label for="exp_present_${n}">I currently work here</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="exp_desc_${n}" rows="3" 
                              placeholder="Describe your responsibilities and achievements...">${escapeHtml(data?.description || '')}</textarea>
                </div>
            </div>
        </div>
    `;
    section.insertAdjacentHTML('beforeend', html);

    if (data && !data.end_date && data.company) {
        const endField = document.getElementById(`exp_end_${n}`);
        if (endField) endField.disabled = true;
    }
}

// ── Dynamic Rows: Skills ─────────────────────────

function addSkill(data = null) {
    skillCount++;
    const n = skillCount;
    const container = document.getElementById('skillsContainer');
    const level = data?.proficiency_level || 3;

    const html = `
        <div class="dynamic-entry" id="skill_${n}" style="padding: 16px;">
            <div class="row align-items-center g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="skill_name_${n}" 
                           value="${escapeHtml(data?.skill_name || '')}" placeholder="e.g. JavaScript">
                </div>
                <div class="col-md-5">
                    <div class="proficiency-group">
                        <input type="range" class="proficiency-slider" name="skill_level_${n}" 
                               min="1" max="5" value="${level}" 
                               oninput="updateProfLabel(this, 'prof_label_${n}')">
                        <span class="proficiency-label" id="prof_label_${n}">${getProfLabel(level)}</span>
                    </div>
                </div>
                <div class="col-md-2 text-end">
                    <button type="button" class="btn-remove-entry" onclick="removeEntry('skill_${n}', 'skills')" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

function updateProfLabel(slider, labelId) {
    document.getElementById(labelId).textContent = getProfLabel(parseInt(slider.value));
}

function getProfLabel(level) {
    return ['', 'Beginner', 'Elementary', 'Intermediate', 'Advanced', 'Expert'][level] || 'Intermediate';
}

// ── Dynamic Rows: Certifications ─────────────────

function addCertification(data = null) {
    certCount++;
    const n = certCount;
    const container = document.getElementById('certsContainer');

    const html = `
        <div class="dynamic-entry" id="cert_${n}">
            <div class="entry-header">
                <span class="entry-number">Certification #${n}</span>
                <button type="button" class="btn-remove-entry" onclick="removeEntry('cert_${n}', 'certifications')" title="Remove">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title *</label>
                    <input type="text" class="form-control" name="cert_title_${n}" 
                           value="${escapeHtml(data?.title || '')}" placeholder="e.g. AWS Solutions Architect">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Issuer</label>
                    <input type="text" class="form-control" name="cert_issuer_${n}" 
                           value="${escapeHtml(data?.issuer || '')}" placeholder="e.g. Amazon Web Services">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Issue Date</label>
                    <input type="date" class="form-control" name="cert_date_${n}" 
                           value="${data?.issue_date || ''}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Credential URL</label>
                    <input type="url" class="form-control" name="cert_url_${n}" 
                           value="${escapeHtml(data?.credential_url || '')}" placeholder="https://...">
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

// ── Dynamic Rows: Projects ───────────────────────

function addProject(data = null) {
    projectCount++;
    const n = projectCount;
    const container = document.getElementById('projectsContainer');

    const html = `
        <div class="dynamic-entry" id="proj_${n}">
            <div class="entry-header">
                <span class="entry-number">Project #${n}</span>
                <button type="button" class="btn-remove-entry" onclick="removeEntry('proj_${n}', 'projects')" title="Remove">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Title *</label>
                    <input type="text" class="form-control" name="proj_title_${n}" 
                           value="${escapeHtml(data?.title || '')}" placeholder="e.g. E-commerce Platform">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tech Stack</label>
                    <input type="text" class="form-control" name="proj_tech_${n}" 
                           value="${escapeHtml(data?.tech_stack || '')}" placeholder="e.g. React, Node.js, MongoDB">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="proj_desc_${n}" rows="2" 
                              placeholder="Describe the project...">${escapeHtml(data?.description || '')}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Project URL</label>
                    <input type="url" class="form-control" name="proj_url_${n}" 
                           value="${escapeHtml(data?.project_url || '')}" placeholder="https://...">
                </div>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
}

// ── Entry Removal ────────────────────────────────

function removeEntry(id, type) {
    const section = document.getElementById(id);
    if (!section) return;

    // Don't remove if it's the last entry for required sections
    const parent = section.parentElement;
    if (['education', 'experience'].includes(type) && parent.querySelectorAll('.dynamic-entry').length <= 1) {
        showToast('You need at least one entry.', 'warning');
        return;
    }

    section.style.animation = 'fadeIn 0.2s ease-out reverse';
    setTimeout(() => section.remove(), 200);
}

// ── Helpers ──────────────────────────────────────

function togglePresent(fieldId, isPresent) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.disabled = isPresent;
        if (isPresent) field.value = '';
    }
}

function toggleAccordion(trigger) {
    const content = trigger.nextElementSibling;
    trigger.classList.toggle('open');
    content.classList.toggle('open');
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate size
        if (file.size > 2 * 1024 * 1024) {
            showToast('Image must be under 2 MB.', 'danger');
            input.value = '';
            return;
        }

        // Validate type
        if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
            showToast('Only JPG, PNG, and GIF files are allowed.', 'danger');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.getElementById('uploadPreview');
            preview.innerHTML = `<img src="${e.target.result}" alt="Profile Preview" id="previewImg">`;
        };
        reader.readAsDataURL(file);
    }
}

function selectTemplate(card) {
    // Deselect all
    document.querySelectorAll('.template-card').forEach(c => c.classList.remove('selected'));
    // Select this one
    card.classList.add('selected');
    document.getElementById('templateSlug').value = card.dataset.template;
}

function toggleTemplateDark(slug, isDark) {
    document.getElementById('templateDark').value = isDark ? '1' : '0';
}

function initCharCounter() {
    const summary = document.getElementById('summary');
    const counter = document.getElementById('summaryCount');
    if (summary && counter) {
        const update = () => { counter.textContent = summary.value.length; };
        summary.addEventListener('input', update);
        update();
    }
}

// ── Form Submission ──────────────────────────────

async function submitForm() {
    if (!validateCurrentStep()) return;

    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';

    try {
        await saveCurrentStep();

        const cvId = document.getElementById('cvIdField').value;
        const templateSlug = document.getElementById('templateSlug').value;
        const templateDark = document.getElementById('templateDark').value;

        // Redirect to preview
        window.location.href = `${window.baseUrl}/preview.php?cv_id=${cvId}&template=${templateSlug}&dark=${templateDark}`;
    } catch (err) {
        console.error('Submit error:', err);
        showToast('Failed to save. Please try again.', 'danger');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-eye me-2"></i> Preview & Finish';
    }
}
