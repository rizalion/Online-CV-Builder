<?php
require_once __DIR__ . '/db.php';

class CVRepository {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = db();
    }

    public function get_cv_by_id(int $cvId, int $userId): ?array {
        if ($cvId <= 0 || $userId <= 0) return null;

        try {
            $stmt = $this->pdo->prepare('SELECT * FROM cv_profiles WHERE id = ? AND user_id = ?');
            $stmt->execute([$cvId, $userId]);
            $profile = $stmt->fetch();

            if (!$profile) return null;

            $cv = ['profile' => $profile];
            $tables = ['education', 'experience', 'skills', 'certifications', 'projects'];
            
            foreach ($tables as $tbl) {
                // Table names are hardcoded safely above
                $stmt = $this->pdo->prepare("SELECT * FROM {$tbl} WHERE cv_id = ? ORDER BY sort_order");
                $stmt->execute([$cvId]);
                $cv[$tbl] = $stmt->fetchAll();
            }

            return $cv;
        } catch (PDOException $e) {
            error_log("DB Error in get_cv_by_id: " . $e->getMessage());
            return null;
        }
    }

    public function get_admin_stats(): array {
        try {
            return [
                'users' => $this->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
                'cvs' => $this->pdo->query('SELECT COUNT(*) FROM cv_profiles')->fetchColumn(),
                'downloads' => $this->pdo->query('SELECT COUNT(*) FROM cv_downloads')->fetchColumn(),
                'recent' => $this->pdo->query('SELECT name, email, created_at FROM users ORDER BY id DESC LIMIT 5')->fetchAll()
            ];
        } catch (PDOException $e) {
            error_log("DB Error in get_admin_stats: " . $e->getMessage());
            return ['users' => 0, 'cvs' => 0, 'downloads' => 0, 'recent' => []];
        }
    }

    public function log_download(int $cvId, int $templateId): void {
        try {
            $stmt = $this->pdo->prepare('INSERT INTO cv_downloads (cv_id, template_id) VALUES (?, ?)');
            $stmt->execute([$cvId, $templateId]);
        } catch (PDOException $e) {
            error_log("DB Error in log_download: " . $e->getMessage());
        }
    }
}
