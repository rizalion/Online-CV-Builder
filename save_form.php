<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/cv_repository.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request.');
}

if (!csrf_verify()) {
    die('Invalid CSRF token.');
}

$pdo = db();
$userId = (int)$_SESSION['user_id'];

// Server-side validation mirrored from client
$fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_SPECIAL_CHARS);
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_SPECIAL_CHARS);
if ($phone && !preg_match('/^\+?[\d\s\-\(\)]{7,20}$/', $phone)) {
    die('Invalid phone format.');
}
if (!$fullName || !$email) die('Missing required fields.');

$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_SPECIAL_CHARS);
$linkedin = filter_input(INPUT_POST, 'linkedin_url', FILTER_SANITIZE_URL);
if ($linkedin && strpos($linkedin, 'linkedin.com') === false) {
    die('Invalid LinkedIn URL.');
}
$portfolio = filter_input(INPUT_POST, 'portfolio_url', FILTER_SANITIZE_URL);
$summary = filter_input(INPUT_POST, 'professional_summary', FILTER_SANITIZE_SPECIAL_CHARS);
if (strlen($summary) > 500) {
    $summary = substr($summary, 0, 500); // Enforce max 500 chars
}

// Image handling & resizing
$profilePicPath = null;
if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['profile_picture']['tmp_name'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmp);
    finfo_close($finfo);

    if (in_array($mime, ALLOWED_TYPES) && $_FILES['profile_picture']['size'] <= MAX_FILE_SIZE) {
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('profile_', true) . '.' . strtolower($ext);
        $dest = UPLOAD_DIR . $filename;
        
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }
        
        // GD Resizing to max 400x400
        list($width, $height) = getimagesize($tmp);
        $maxSize = 400;
        if (($width > $maxSize || $height > $maxSize) && function_exists('imagecreatetruecolor')) {
            $ratio = min($maxSize/$width, $maxSize/$height);
            $newW = (int)($width * $ratio);
            $newH = (int)($height * $ratio);
            
            $dstImg = imagecreatetruecolor($newW, $newH);
            if ($mime === 'image/png') {
                imagealphablending($dstImg, false);
                imagesavealpha($dstImg, true);
                $srcImg = imagecreatefrompng($tmp);
            } elseif ($mime === 'image/gif') {
                $srcImg = imagecreatefromgif($tmp);
            } else {
                $srcImg = imagecreatefromjpeg($tmp);
            }
            
            imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $newW, $newH, $width, $height);
            
            if ($mime === 'image/png') imagepng($dstImg, $dest);
            elseif ($mime === 'image/gif') imagegif($dstImg, $dest);
            else imagejpeg($dstImg, $dest, 90);
            
            imagedestroy($srcImg);
            imagedestroy($dstImg);
        } else {
            move_uploaded_file($tmp, $dest);
        }
        $profilePicPath = 'assets/uploads/' . $filename;
    }
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO cv_profiles (user_id, full_name, email, phone, address, linkedin_url, portfolio_url, professional_summary, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $fullName, $email, $phone, $address, $linkedin, $portfolio, $summary, $profilePicPath]);
    $cvId = $pdo->lastInsertId();

    // Education
    if (!empty($_POST['education']) && is_array($_POST['education'])) {
        $stmt = $pdo->prepare('INSERT INTO education (cv_id, institution, degree, field_of_study, start_date, end_date, grade, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($_POST['education'] as $i => $edu) {
            if (empty(trim($edu['institution'] ?? ''))) continue;
            $start = $edu['start_date'] ?: null;
            $end = $edu['end_date'] ?: null;
            if ($start && $end && strtotime($end) < strtotime($start)) $end = null; // Logical check
            
            $stmt->execute([
                $cvId,
                trim($edu['institution']),
                trim($edu['degree'] ?? ''),
                trim($edu['field_of_study'] ?? ''),
                $start,
                $end,
                trim($edu['grade'] ?? ''),
                $i
            ]);
        }
    }

    // Experience
    if (!empty($_POST['experience']) && is_array($_POST['experience'])) {
        $stmt = $pdo->prepare('INSERT INTO experience (cv_id, company, job_title, location, start_date, end_date, responsibilities, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($_POST['experience'] as $i => $exp) {
            if (empty(trim($exp['company'] ?? ''))) continue;
            $start = $exp['start_date'] ?: null;
            $end = $exp['end_date'] ?: null;
            if ($start && $end && strtotime($end) < strtotime($start)) $end = null; // Logical check
            
            $stmt->execute([
                $cvId,
                trim($exp['company']),
                trim($exp['job_title'] ?? ''),
                trim($exp['location'] ?? ''),
                $start,
                $end,
                trim($exp['responsibilities'] ?? ''),
                $i
            ]);
        }
    }

    // Skills
    $skills = json_decode($_POST['skills_json'] ?? '[]', true);
    if (!empty($skills) && is_array($skills)) {
        $stmt = $pdo->prepare('INSERT INTO skills (cv_id, skill_name, proficiency, sort_order) VALUES (?, ?, ?, ?)');
        foreach ($skills as $i => $sk) {
            if (isset($sk['name'], $sk['prof'])) {
                $stmt->execute([$cvId, substr(trim($sk['name']), 0, 100), trim($sk['prof']), $i]);
            }
        }
    }

    // Certifications
    if (!empty($_POST['certifications']) && is_array($_POST['certifications'])) {
        $stmt = $pdo->prepare('INSERT INTO certifications (cv_id, title, issuer, issue_date, credential_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($_POST['certifications'] as $i => $cert) {
            if (empty(trim($cert['title'] ?? ''))) continue;
            $stmt->execute([
                $cvId,
                trim($cert['title']),
                trim($cert['issuer'] ?? ''),
                $cert['issue_date'] ?: null,
                trim($cert['credential_url'] ?? ''),
                $i
            ]);
        }
    }

    // Projects
    if (!empty($_POST['projects']) && is_array($_POST['projects'])) {
        $stmt = $pdo->prepare('INSERT INTO projects (cv_id, title, description, tech_stack, project_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($_POST['projects'] as $i => $proj) {
            if (empty(trim($proj['title'] ?? ''))) continue;
            $stmt->execute([
                $cvId,
                trim($proj['title']),
                trim($proj['description'] ?? ''),
                trim($proj['tech_stack'] ?? ''),
                trim($proj['project_url'] ?? ''),
                $i
            ]);
        }
    }

    $pdo->commit();
    $_SESSION['current_cv_id'] = $cvId;
    header('Location: ' . BASE_URL . '/select_template.php?cv_id=' . $cvId);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("DB Error in save_form: " . $e->getMessage());
    die('An error occurred saving your CV. Please try again later.');
}
