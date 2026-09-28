<style>
    .tpl1-wrapper { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; display: flex; max-width: 900px; margin: 0 auto; background: #fff; color: #333; line-height: 1.5; }
    .tpl1-sidebar { width: 30%; background: #1a3c5e; color: #fff; padding: 30px 20px; }
    .tpl1-main { width: 70%; padding: 40px 30px; }
    .tpl1-profile-img { width: 120px; height: 120px; border-radius: 50%; border: 3px solid #fff; object-fit: cover; margin: 0 auto 20px; display: block; }
    .tpl1-sidebar h3 { font-size: 1.1rem; border-bottom: 1px solid rgba(255,255,255,0.2); padding-bottom: 5px; margin-bottom: 15px; margin-top: 30px; text-transform: uppercase; }
    .tpl1-contact p { margin: 5px 0; font-size: 0.85rem; }
    .tpl1-skill { margin-bottom: 10px; }
    .tpl1-skill-name { font-size: 0.85rem; margin-bottom: 3px; }
    .tpl1-skill-bar { background: rgba(255,255,255,0.2); height: 6px; border-radius: 3px; }
    .tpl1-skill-fill { background: #fff; height: 100%; border-radius: 3px; }
    .tpl1-main h1 { font-size: 2.5rem; color: #1a3c5e; margin: 0 0 5px; text-transform: uppercase; }
    .tpl1-main h2 { font-size: 1.3rem; color: #1a3c5e; border-bottom: 2px solid #1a3c5e; padding-bottom: 5px; margin: 30px 0 15px; text-transform: uppercase; }
    .tpl1-entry { margin-bottom: 20px; }
    .tpl1-entry-title { font-weight: bold; font-size: 1.1rem; color: #333; }
    .tpl1-entry-sub { font-size: 0.9rem; color: #666; font-style: italic; margin-bottom: 5px; }
    .tpl1-entry-desc { font-size: 0.9rem; color: #444; }
    @media print {
        .tpl1-wrapper { width: 100%; max-width: none; }
        .tpl1-sidebar { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>

<div class="tpl1-wrapper">
    <div class="tpl1-sidebar">
        <?php 
            $pic = $cv['profile']['profile_picture'] ?? '';
            $fullPath = $pic ? __DIR__ . '/../' . $pic : '';
            if ($pic && file_exists($fullPath)): 
        ?>
            <img src="<?= htmlspecialchars(BASE_URL . '/' . $pic, ENT_QUOTES, 'UTF-8') ?>" class="tpl1-profile-img" alt="Profile">
        <?php else: ?>
            <img src="<?= htmlspecialchars(BASE_URL . '/assets/images/placeholders/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>" class="tpl1-profile-img" alt="Profile">
        <?php endif; ?>
        
        <h3>Contact</h3>
        <div class="tpl1-contact">
            <p><i class="fas fa-envelope"></i> <?= $cv['profile']['email'] ?></p>
            <?php if (!empty($cv['profile']['phone'])): ?><p><i class="fas fa-phone"></i> <?= $cv['profile']['phone'] ?></p><?php endif; ?>
            <?php if (!empty($cv['profile']['address'])): ?><p><i class="fas fa-map-marker-alt"></i> <?= $cv['profile']['address'] ?></p><?php endif; ?>
            <?php if (!empty($cv['profile']['linkedin_url'])): ?><p><i class="fab fa-linkedin"></i> <?= $cv['profile']['linkedin_url'] ?></p><?php endif; ?>
            <?php if (!empty($cv['profile']['portfolio_url'])): ?><p><i class="fas fa-globe"></i> <?= $cv['profile']['portfolio_url'] ?></p><?php endif; ?>
        </div>

        <?php if (!empty($cv['skills'])): ?>
        <h3>Skills</h3>
        <?php foreach ($cv['skills'] as $skill): 
            $pct = $skill['proficiency'] === 'Expert' ? 100 : ($skill['proficiency'] === 'Intermediate' ? 66 : 33);
        ?>
            <div class="tpl1-skill">
                <div class="tpl1-skill-name"><?= $skill['skill_name'] ?></div>
                <div class="tpl1-skill-bar"><div class="tpl1-skill-fill" style="width: <?= $pct ?>%"></div></div>
            </div>
        <?php endforeach; endif; ?>
    </div>
    
    <div class="tpl1-main">
        <h1><?= $cv['profile']['full_name'] ?></h1>
        <?php if (!empty($cv['profile']['professional_summary'])): ?>
            <p class="tpl1-entry-desc"><?= nl2br($cv['profile']['professional_summary']) ?></p>
        <?php endif; ?>

        <?php if (!empty($cv['experience'])): ?>
        <h2>Experience</h2>
        <?php foreach ($cv['experience'] as $exp): ?>
            <div class="tpl1-entry">
                <div class="tpl1-entry-title"><?= $exp['job_title'] ?> - <?= $exp['company'] ?></div>
                <div class="tpl1-entry-sub"><?= $exp['start_date'] ?> to <?= $exp['end_date'] ?: 'Present' ?> | <?= $exp['location'] ?></div>
                <div class="tpl1-entry-desc"><?= nl2br($exp['responsibilities']) ?></div>
            </div>
        <?php endforeach; endif; ?>

        <?php if (!empty($cv['education'])): ?>
        <h2>Education</h2>
        <?php foreach ($cv['education'] as $edu): ?>
            <div class="tpl1-entry">
                <div class="tpl1-entry-title"><?= $edu['degree'] ?> in <?= $edu['field_of_study'] ?></div>
                <div class="tpl1-entry-sub"><?= $edu['institution'] ?> | <?= $edu['start_date'] ?> to <?= $edu['end_date'] ?: 'Present' ?></div>
                <?php if (!empty($edu['grade'])): ?><div class="tpl1-entry-desc">Grade: <?= $edu['grade'] ?></div><?php endif; ?>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
