<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create CV - CV Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/darkmode.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?= BASE_URL ?>/assets/js/darkmode.js"></script>
    <style>
        .loader-overlay { position: absolute; top:0; left:0; right:0; bottom:0; background:rgba(255,255,255,0.7); display:none; align-items:center; justify-content:center; z-index:10; }
        [data-theme="dark"] .loader-overlay { background: rgba(30,30,30,0.7); }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">CV Builder</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-7">
                <div class="card p-4 shadow mb-5">
                    <h2 class="mb-4">Build Your CV</h2>
                    
                    <div class="wizard-progress">
                        <div class="progress-bar-fill" id="progressFill" style="width: 0%;"></div>
                        <div class="progress-step active" data-step="1">1</div>
                        <div class="progress-step" data-step="2">2</div>
                        <div class="progress-step" data-step="3">3</div>
                        <div class="progress-step" data-step="4">4</div>
                        <div class="progress-step" data-step="5">5</div>
                    </div>

                    <form id="cvForm" action="save_form.php" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        
                        <!-- STEP 1 -->
                        <div class="step active" id="step1">
                            <h4>Personal Information</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label>Full Name *</label>
                                    <input type="text" name="full_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Email *</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Phone *</label>
                                    <input type="text" name="phone" id="phone" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label>Address</label>
                                    <input type="text" name="address" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>LinkedIn URL</label>
                                    <input type="url" name="linkedin_url" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label>Portfolio URL</label>
                                    <input type="url" name="portfolio_url" class="form-control">
                                </div>
                                <div class="col-12">
                                    <label>Professional Summary</label>
                                    <textarea name="professional_summary" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="col-12">
                                    <label>Profile Picture</label>
                                    <input type="file" name="profile_picture" id="profile_picture" class="form-control" accept=".jpg,.jpeg,.png,.gif">
                                    <div id="fileError" class="invalid-feedback">File must be JPG/PNG/GIF and under 2MB.</div>
                                    <img id="imagePreview" src="#" alt="Preview">
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2 -->
                        <div class="step" id="step2">
                            <h4>Education</h4>
                            <div id="educationContainer"></div>
                            <button type="button" class="btn btn-outline-primary mt-2" id="addEducation">
                                <i class="fas fa-plus"></i> Add Education
                            </button>
                        </div>

                        <!-- STEP 3 -->
                        <div class="step" id="step3">
                            <h4>Work Experience</h4>
                            <div id="experienceContainer"></div>
                            <button type="button" class="btn btn-outline-primary mt-2" id="addExperience">
                                <i class="fas fa-plus"></i> Add Experience
                            </button>
                        </div>

                        <!-- STEP 4 -->
                        <div class="step" id="step4">
                            <h4>Skills, Certifications & Projects</h4>
                            
                            <h5 class="mt-4">Skills</h5>
                            <div class="input-group mb-2">
                                <input type="text" id="skillInput" class="form-control" placeholder="Skill name">
                                <select id="skillProficiency" class="form-select" style="max-width: 150px;">
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate" selected>Intermediate</option>
                                    <option value="Expert">Expert</option>
                                </select>
                                <button class="btn btn-secondary" type="button" id="addSkill">Add Skill</button>
                            </div>
                            <div id="skillsList" class="mb-3"></div>
                            <input type="hidden" name="skills_json" id="skillsJson">

                            <h5 class="mt-4">Certifications</h5>
                            <div id="certContainer"></div>
                            <button type="button" class="btn btn-outline-primary mt-2" id="addCert">
                                <i class="fas fa-plus"></i> Add Certification
                            </button>

                            <h5 class="mt-4">Projects</h5>
                            <div id="projectContainer"></div>
                            <button type="button" class="btn btn-outline-primary mt-2" id="addProject">
                                <i class="fas fa-plus"></i> Add Project
                            </button>
                        </div>

                        <!-- STEP 5 -->
                        <div class="step" id="step5">
                            <h4>Choose Template</h4>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="w-100">
                                        <input type="radio" name="template_id" value="1" class="d-none" checked>
                                        <div class="card template-card selected" data-id="1">
                                            <div class="card-body text-center">
                                                <i class="fas fa-file-alt fa-3x mb-2 text-primary"></i>
                                                <h5>Classic Professional</h5>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="w-100">
                                        <input type="radio" name="template_id" value="2" class="d-none">
                                        <div class="card template-card" data-id="2">
                                            <div class="card-body text-center">
                                                <i class="fas fa-columns fa-3x mb-2 text-success"></i>
                                                <h5>Modern Minimal</h5>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-4">
                                    <label class="w-100">
                                        <input type="radio" name="template_id" value="3" class="d-none">
                                        <div class="card template-card" data-id="3">
                                            <div class="card-body text-center">
                                                <i class="fas fa-paint-brush fa-3x mb-2 text-danger"></i>
                                                <h5>Creative Dark</h5>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" id="prevBtn" style="display:none;">Previous</button>
                            <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                            <button type="submit" class="btn btn-success" id="submitBtn" style="display:none;">Finish & Save</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-5">
                <!-- Live Preview Panel -->
                <div class="card shadow sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-eye"></i> Live Preview</span>
                        <div class="spinner-border spinner-border-sm text-light" id="previewLoader" role="status" style="display:none;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div class="card-body p-0 position-relative">
                        <div class="loader-overlay" id="loaderOverlay"></div>
                        <iframe id="livePreviewFrame" class="iframe-container border-0 w-100"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        let currentStep = 1;
        const totalSteps = 5;
        
        // Image preview & validation
        const profileInput = document.getElementById('profile_picture');
        const imgPreview = document.getElementById('imagePreview');
        profileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024 || !['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
                    this.classList.add('is-invalid');
                    this.value = '';
                    imgPreview.style.display = 'none';
                } else {
                    this.classList.remove('is-invalid');
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        imgPreview.src = e.target.result;
                        imgPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            }
        });

        // Navigation
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const submitBtn = document.getElementById('submitBtn');

        nextBtn.addEventListener('click', () => {
            if (validateStep(currentStep)) {
                document.getElementById(`step${currentStep}`).classList.remove('active');
                currentStep++;
                document.getElementById(`step${currentStep}`).classList.add('active');
                updateUI();
                if(currentStep === 5) updateLivePreview();
            }
        });

        prevBtn.addEventListener('click', () => {
            document.getElementById(`step${currentStep}`).classList.remove('active');
            currentStep--;
            document.getElementById(`step${currentStep}`).classList.add('active');
            updateUI();
        });

        function updateUI() {
            prevBtn.style.display = currentStep > 1 ? 'block' : 'none';
            if (currentStep === totalSteps) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'block';
            } else {
                nextBtn.style.display = 'block';
                submitBtn.style.display = 'none';
            }

            // Progress bar
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.getElementById('progressFill').style.width = `${progress}%`;

            document.querySelectorAll('.progress-step').forEach((el, index) => {
                if (index + 1 < currentStep) {
                    el.classList.add('completed');
                    el.classList.remove('active');
                } else if (index + 1 === currentStep) {
                    el.classList.add('active');
                    el.classList.remove('completed');
                } else {
                    el.classList.remove('active', 'completed');
                }
            });
        }

        function validateStep(step) {
            let valid = true;
            const currentPanel = document.getElementById(`step${step}`);
            const requiredFields = currentPanel.querySelectorAll('input[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    valid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (step === 1) {
                const email = currentPanel.querySelector('input[type="email"]');
                const phone = document.getElementById('phone');
                if (email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                    email.classList.add('is-invalid');
                    valid = false;
                }
                if (phone.value && !/^[+]?[\d\s\-()]{7,20}$/.test(phone.value)) {
                    phone.classList.add('is-invalid');
                    valid = false;
                }
            }
            return valid;
        }

        // Dynamic Rows logic
        function createRow(type, html) {
            const div = document.createElement('div');
            div.className = 'dynamic-row';
            div.innerHTML = `<i class="fas fa-times remove-row"></i>${html}`;
            div.querySelector('.remove-row').onclick = function() { div.remove(); updateLivePreview(); };
            return div;
        }

        let eduCount = 0;
        document.getElementById('addEducation').onclick = () => {
            const i = eduCount++;
            const html = `
                <div class="row g-2">
                    <div class="col-md-6"><input type="text" name="education[${i}][institution]" class="form-control" placeholder="Institution" required></div>
                    <div class="col-md-6"><input type="text" name="education[${i}][degree]" class="form-control" placeholder="Degree" required></div>
                    <div class="col-md-6"><input type="text" name="education[${i}][field_of_study]" class="form-control" placeholder="Field of Study"></div>
                    <div class="col-md-6"><input type="text" name="education[${i}][grade]" class="form-control" placeholder="Grade"></div>
                    <div class="col-md-6"><input type="date" name="education[${i}][start_date]" class="form-control" required></div>
                    <div class="col-md-6"><input type="date" name="education[${i}][end_date]" class="form-control"></div>
                </div>`;
            document.getElementById('educationContainer').appendChild(createRow('edu', html));
        };

        let expCount = 0;
        document.getElementById('addExperience').onclick = () => {
            const i = expCount++;
            const html = `
                <div class="row g-2">
                    <div class="col-md-6"><input type="text" name="experience[${i}][company]" class="form-control" placeholder="Company" required></div>
                    <div class="col-md-6"><input type="text" name="experience[${i}][job_title]" class="form-control" placeholder="Job Title" required></div>
                    <div class="col-md-12"><input type="text" name="experience[${i}][location]" class="form-control" placeholder="Location"></div>
                    <div class="col-md-6"><input type="date" name="experience[${i}][start_date]" class="form-control" required></div>
                    <div class="col-md-6"><input type="date" name="experience[${i}][end_date]" class="form-control"></div>
                    <div class="col-12"><textarea name="experience[${i}][responsibilities]" class="form-control" placeholder="Responsibilities"></textarea></div>
                </div>`;
            document.getElementById('experienceContainer').appendChild(createRow('exp', html));
        };

        // Skills logic
        let skills = [];
        document.getElementById('addSkill').onclick = () => {
            const input = document.getElementById('skillInput');
            const prof = document.getElementById('skillProficiency');
            if (input.value.trim()) {
                skills.push({ name: input.value.trim(), prof: prof.value });
                input.value = '';
                renderSkills();
                updateLivePreview();
            }
        };

        function renderSkills() {
            const list = document.getElementById('skillsList');
            list.innerHTML = '';
            skills.forEach((s, i) => {
                const tag = document.createElement('span');
                tag.className = 'skill-tag';
                tag.innerHTML = `${s.name} (${s.prof}) <i class="fas fa-times remove-skill" onclick="removeSkill(${i})"></i>`;
                list.appendChild(tag);
            });
            document.getElementById('skillsJson').value = JSON.stringify(skills);
        }
        window.removeSkill = (i) => { skills.splice(i, 1); renderSkills(); updateLivePreview(); };

        // Certs
        let certCount = 0;
        document.getElementById('addCert').onclick = () => {
            const i = certCount++;
            const html = `
                <div class="row g-2">
                    <div class="col-md-6"><input type="text" name="certifications[${i}][title]" class="form-control" placeholder="Title" required></div>
                    <div class="col-md-6"><input type="text" name="certifications[${i}][issuer]" class="form-control" placeholder="Issuer"></div>
                    <div class="col-md-6"><input type="date" name="certifications[${i}][issue_date]" class="form-control"></div>
                    <div class="col-md-6"><input type="url" name="certifications[${i}][credential_url]" class="form-control" placeholder="URL"></div>
                </div>`;
            document.getElementById('certContainer').appendChild(createRow('cert', html));
        };

        // Projects
        let projCount = 0;
        document.getElementById('addProject').onclick = () => {
            const i = projCount++;
            const html = `
                <div class="row g-2">
                    <div class="col-md-6"><input type="text" name="projects[${i}][title]" class="form-control" placeholder="Title" required></div>
                    <div class="col-md-6"><input type="text" name="projects[${i}][tech_stack]" class="form-control" placeholder="Tech Stack"></div>
                    <div class="col-md-12"><textarea name="projects[${i}][description]" class="form-control" placeholder="Description"></textarea></div>
                    <div class="col-md-12"><input type="url" name="projects[${i}][project_url]" class="form-control" placeholder="URL"></div>
                </div>`;
            document.getElementById('projectContainer').appendChild(createRow('proj', html));
        };

        // Template Selection
        document.querySelectorAll('.template-card').forEach(card => {
            card.onclick = function() {
                document.querySelectorAll('.template-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                this.previousElementSibling.checked = true;
                updateLivePreview();
            };
        });

        // Initialize empty rows
        document.getElementById('addEducation').click();
        document.getElementById('addExperience').click();

        // Live Preview fetch
        function updateLivePreview() {
            const form = document.getElementById('cvForm');
            const fd = new FormData(form);
            
            document.getElementById('previewLoader').style.display = 'block';
            document.getElementById('loaderOverlay').style.display = 'flex';
            
            fetch('preview_ajax.php', {
                method: 'POST',
                body: fd
            })
            .then(res => res.text())
            .then(html => {
                const iframe = document.getElementById('livePreviewFrame');
                const doc = iframe.contentWindow.document;
                doc.open();
                doc.write(html);
                doc.close();
            })
            .catch(err => console.error(err))
            .finally(() => {
                document.getElementById('previewLoader').style.display = 'none';
                document.getElementById('loaderOverlay').style.display = 'none';
            });
        }

        // Live update on input change
        document.getElementById('cvForm').addEventListener('input', debounce(updateLivePreview, 500));

        function debounce(func, wait) {
            let timeout;
            return function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, arguments), wait);
            };
        }
    });
    </script>
</body>
</html>
