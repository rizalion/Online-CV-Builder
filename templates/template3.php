<style>
    .tpl3-wrapper { font-family: 'Poppins', sans-serif; max-width: 900px; margin: 0 auto; background: #1a1a2e; color: #e0e0e0; padding: 40px; display: grid; grid-template-columns: 35% 65%; gap: 40px; }
    .tpl3-wrapper.light-mode { background: #f4f4f9; color: #333; }
    .tpl3-sidebar { text-align: center; border-right: 2px solid rgba(233, 69, 96, 0.2); padding-right: 40px; }
    .tpl3-profile { width: 150px; height: 150px; border-radius: 50%; border: 4px solid #e94560; box-shadow: 0 0 20px rgba(233, 69, 96, 0.5); object-fit: cover; margin-bottom: 20px; }
    .tpl3-name { font-size: 2rem; color: #e94560; font-weight: 800; margin: 0 0 10px; line-height: 1.1; }
    .tpl3-contact { font-size: 0.85rem; margin-bottom: 30px; text-align: left; }
    .tpl3-contact div { margin-bottom: 8px; }
    .tpl3-contact i { color: #e94560; width: 20px; }
    .tpl3-section-title { font-size: 1.2rem; color: #e94560; border-bottom: 2px solid #e94560; padding-bottom: 5px; margin-bottom: 15px; text-transform: uppercase; font-weight: 700; text-align: left; }
    .tpl3-skill { margin-bottom: 12px; text-align: left; }
    .tpl3-skill-name { font-size: 0.85rem; font-weight: 600; margin-bottom: 4px; display: flex; justify-content: space-between; }
    .tpl3-skill-bar { height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden; }
    .tpl3-wrapper.light-mode .tpl3-skill-bar { background: rgba(0,0,0,0.1); }
    .tpl3-skill-fill { height: 100%; background: #e94560; }
    .tpl3-main-title { font-size: 1.5rem; color: #e94560; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 20px; font-weight: 800; }
    .tpl3-entry { margin-bottom: 25px; }
    .tpl3-entry h4 { font-size: 1.1rem; color: #fff; margin: 0 0 5px; }
    .tpl3-wrapper.light-mode .tpl3-entry h4 { color: #1a1a2e; }
    .tpl3-entry .meta { color: #e94560; font-size: 0.85rem; font-weight: 600; margin-bottom: 10px; }
    .tpl3-entry p { font-size: 0.9rem; line-height: 1.6; opacity: 0.9; }
    @media print { .tpl3-wrapper { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
</style>

<div class="tpl3-wrapper">
    <div class="tpl3-sidebar">
        <?php 
            $pic = $cv['profile']['profile_picture'] ?? '';
            $fullPath = $pic ? __DIR__ . '/../' . $pic : '';
            if ($pic && file_exists($fullPath)): 
        ?>
            <img src="<?= htmlspecialchars(BASE_URL . '/' . $pic, ENT_QUOTES, 'UTF-8') ?>" class="tpl3-profile" alt="Profile">
        <?php else: ?>
            <img src="<?= htmlspecialchars(BASE_URL . '/assets/images/placeholders/default-avatar.svg', ENT_QUOTES, 'UTF-8') ?>" class="tpl3-profile" alt="Profile">
        <?php endif; ?>
        <h1 class="tpl3-name"><?= $cv['profile']['full_name'] ?></h1>
        
        <div class="tpl3-contact">
            <div><i class="fas fa-envelope"></i> <?= $cv['profile']['email'] ?></div>
            <?php if (!empty($cv['profile']['phone'])): ?><div><i class="fas fa-phone"></i> <?= $cv['profile']['phone'] ?></div><?php endif; ?>
            <?php if (!empty($cv['profile']['address'])): ?><div><i class="fas fa-map-marker-alt"></i> <?= $cv['profile']['address'] ?></div><?php endif; ?>
        </div>

        <?php if (!empty($cv['skills'])): ?>
        <div class="tpl3-section-title">Skills</div>
        <?php foreach ($cv['skills'] as $skill): 
            $pct = $skill['proficiency'] === 'Expert' ? 95 : ($skill['proficiency'] === 'Intermediate' ? 70 : 40);
        ?>
            <div class="tpl3-skill">
                <div class="tpl3-skill-name"><span><?= $skill['skill_name'] ?></span> <span><?= $pct ?>%</span></div>
                <div class="tpl3-skill-bar"><div class="tpl3-skill-fill" style="width: <?= $pct ?>%"></div></div>
            </div>
        <?php endforeach; endif; ?>
    </div>
    
    <div class="tpl3-main">
        <?php if (!empty($cv['profile']['professional_summary'])): ?>
            <div class="tpl3-main-title">Profile</div>
            <p style="font-size:0.95rem; line-height:1.7; margin-bottom:30px;"><?= nl2br($cv['profile']['professional_summary']) ?></p>
        <?php endif; ?>

        <?php if (!empty($cv['experience'])): ?>
            <div class="tpl3-main-title">Experience</div>
            <?php foreach ($cv['experience'] as $exp): ?>
                <div class="tpl3-entry">
                    <h4><?= $exp['job_title'] ?></h4>
                    <div class="meta"><?= $exp['company'] ?> | <?= $exp['start_date'] ?> – <?= $exp['end_date'] ?: 'Present' ?></div>
                    <p><?= nl2br($exp['responsibilities']) ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($cv['education'])): ?>
            <div class="tpl3-main-title">Education</div>
            <?php foreach ($cv['education'] as $edu): ?>
                <div class="tpl3-entry">
                    <h4><?= $edu['degree'] ?> in <?= $edu['field_of_study'] ?></h4>
                    <div class="meta"><?= $edu['institution'] ?> | <?= $edu['start_date'] ?> – <?= $edu['end_date'] ?: 'Present' ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
