<style>
    .tpl2-wrapper { font-family: 'Segoe UI', Roboto, sans-serif; max-width: 800px; margin: 0 auto; background: #fff; padding: 50px; color: #2c3e50; line-height: 1.6; }
    .tpl2-header { text-align: center; margin-bottom: 40px; }
    .tpl2-header h1 { font-size: 3rem; margin: 0; color: #2c3e50; font-weight: 800; display: inline-block; border-bottom: 4px solid #2ecc71; padding-bottom: 10px; }
    .tpl2-contact { display: flex; justify-content: center; flex-wrap: wrap; gap: 15px; margin-top: 20px; font-size: 0.9rem; color: #7f8c8d; }
    .tpl2-contact span { display: flex; align-items: center; gap: 5px; }
    .tpl2-section { margin-bottom: 30px; }
    .tpl2-section h2 { font-size: 1.4rem; color: #2c3e50; margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 10px; }
    .tpl2-section h2 i { color: #2ecc71; }
    .tpl2-timeline { border-left: 3px solid #2ecc71; padding-left: 20px; margin-left: 10px; }
    .tpl2-item { position: relative; margin-bottom: 25px; }
    .tpl2-item::before { content: ''; position: absolute; left: -29px; top: 5px; width: 15px; height: 15px; background: #2ecc71; border-radius: 50%; border: 3px solid #fff; }
    .tpl2-title { font-size: 1.1rem; font-weight: bold; color: #2c3e50; margin-bottom: 3px; }
    .tpl2-meta { font-size: 0.85rem; color: #7f8c8d; margin-bottom: 8px; font-weight: 500; }
    .tpl2-desc { font-size: 0.95rem; color: #34495e; }
    .tpl2-skills { display: flex; flex-wrap: wrap; gap: 10px; }
    .tpl2-skill-badge { background: #e8f8f5; color: #117a65; padding: 5px 15px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid #d1f2eb; }
    @media print { .tpl2-wrapper { padding: 0; } }
</style>

<div class="tpl2-wrapper">
    <div class="tpl2-header">
        <?php 
            $pic = $cv['profile']['profile_picture'] ?? '';
            $fullPath = $pic ? __DIR__ . '/../' . $pic : '';
            if ($pic && file_exists($fullPath)): 
        ?>
            <img src="<?= htmlspecialchars(BASE_URL . '/' . $pic, ENT_QUOTES, 'UTF-8') ?>" class="tpl1-profile-img" style="border-radius:50%; width:100px; height:100px; object-fit:cover; margin-bottom:15px;" alt="Profile">
        <?php else: ?>
            <img src="<?= htmlspecialchars(BASE_URL . '/assets/images/placeholders/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>" class="tpl1-profile-img" style="border-radius:50%; width:100px; height:100px; object-fit:cover; margin-bottom:15px;" alt="Profile">
        <?php endif; ?>
        <br>
        <h1><?= $cv['profile']['full_name'] ?></h1>
        <div class="tpl2-contact">
            <span><i class="fas fa-envelope"></i> <?= $cv['profile']['email'] ?></span>
            <?php if (!empty($cv['profile']['phone'])): ?><span><i class="fas fa-phone"></i> <?= $cv['profile']['phone'] ?></span><?php endif; ?>
            <?php if (!empty($cv['profile']['address'])): ?><span><i class="fas fa-map-marker-alt"></i> <?= $cv['profile']['address'] ?></span><?php endif; ?>
        </div>
    </div>

    <?php if (!empty($cv['profile']['professional_summary'])): ?>
    <div class="tpl2-section">
        <h2><i class="fas fa-user"></i> Profile</h2>
        <p class="tpl2-desc"><?= nl2br($cv['profile']['professional_summary']) ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($cv['experience'])): ?>
    <div class="tpl2-section">
        <h2><i class="fas fa-briefcase"></i> Experience</h2>
        <div class="tpl2-timeline">
            <?php foreach ($cv['experience'] as $exp): ?>
            <div class="tpl2-item">
                <div class="tpl2-title"><?= $exp['job_title'] ?> at <?= $exp['company'] ?></div>
                <div class="tpl2-meta"><?= $exp['start_date'] ?> – <?= $exp['end_date'] ?: 'Present' ?> | <?= $exp['location'] ?></div>
                <div class="tpl2-desc"><?= nl2br($exp['responsibilities']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($cv['education'])): ?>
    <div class="tpl2-section">
        <h2><i class="fas fa-graduation-cap"></i> Education</h2>
        <div class="tpl2-timeline">
            <?php foreach ($cv['education'] as $edu): ?>
            <div class="tpl2-item">
                <div class="tpl2-title"><?= $edu['degree'] ?> in <?= $edu['field_of_study'] ?></div>
                <div class="tpl2-meta"><?= $edu['institution'] ?> | <?= $edu['start_date'] ?> – <?= $edu['end_date'] ?: 'Present' ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($cv['skills'])): ?>
    <div class="tpl2-section">
        <h2><i class="fas fa-code"></i> Skills</h2>
        <div class="tpl2-skills">
            <?php foreach ($cv['skills'] as $skill): ?>
                <span class="tpl2-skill-badge"><?= $skill['skill_name'] ?> (<?= $skill['proficiency'] ?>)</span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
