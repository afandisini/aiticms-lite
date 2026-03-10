<?php

declare(strict_types=1);

namespace App\Services\Cms;

class DeveloperProfileService
{
    public function __construct()
    {
        $this->ensureTable();
    }

    public function findByUsername(string $username): ?array
    {
        $username = trim($username);
        if ($username === '') {
            return null;
        }

        $stmt = db()->prepare(
            'SELECT
                u.id AS user_id,
                u.name,
                u.username,
                u.email,
                u.avatar,
                u.phone,
                u.address,
                u.description AS user_description,
                u.web,
                u.facebook,
                u.twitter,
                u.linkedin,
                u.youtube,
                u.instagram,
                u.github,
                dp.headline,
                dp.location,
                dp.summary,
                dp.about_html,
                dp.skills,
                dp.experience_html,
                dp.education_html,
                dp.projects_html,
                dp.contact_html,
                dp.seo_title,
                dp.seo_description,
                dp.is_public,
                dp.updated_at AS profile_updated_at
             FROM users u
             LEFT JOIN developer_profiles dp ON dp.user_id = u.id
             WHERE u.username = :username
               AND u.active = 1
             LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!is_array($row)) {
            return null;
        }

        if ((int) ($row['is_public'] ?? 0) !== 1) {
            return null;
        }

        return $row;
    }

    public function users(): array
    {
        $stmt = db()->query(
            'SELECT
                u.id,
                u.name,
                u.username,
                u.email,
                u.roles,
                ur.name_role,
                u.active,
                COALESCE(dp.is_public, 0) AS is_public,
                dp.updated_at
             FROM users u
             LEFT JOIN users_role ur ON ur.id = u.roles
             LEFT JOIN developer_profiles dp ON dp.user_id = u.id
             WHERE u.roles = 3
             ORDER BY u.name ASC, u.username ASC'
        );

        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return is_array($rows) ? $rows : [];
    }

    public function findByUserId(int $userId): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $stmt = db()->prepare(
            'SELECT
                u.id AS user_id,
                u.name,
                u.username,
                u.email,
                u.roles,
                u.avatar,
                u.phone,
                u.address,
                u.description AS user_description,
                u.web,
                u.facebook,
                u.twitter,
                u.linkedin,
                u.youtube,
                u.instagram,
                u.github,
                dp.headline,
                dp.location,
                dp.summary,
                dp.about_html,
                dp.skills,
                dp.experience_html,
                dp.education_html,
                dp.projects_html,
                dp.contact_html,
                dp.seo_title,
                dp.seo_description,
                dp.is_public,
                dp.updated_at AS profile_updated_at
             FROM users u
             LEFT JOIN developer_profiles dp ON dp.user_id = u.id
             WHERE u.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    public function save(int $userId, array $payload): void
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('User tidak valid.');
        }

        $user = $this->findByUserId($userId);
        if ($user === null) {
            throw new \RuntimeException('User tidak ditemukan.');
        }

        $username = trim((string) ($user['username'] ?? ''));
        if ($username === '') {
            throw new \RuntimeException('User harus memiliki username agar profil publik dapat diakses.');
        }
        if ((int) ($user['roles'] ?? 0) !== 3) {
            throw new \RuntimeException('Profile CV hanya untuk user dengan role Operator.');
        }

        $data = [
            'headline' => trim((string) ($payload['headline'] ?? '')),
            'location' => trim((string) ($payload['location'] ?? '')),
            'summary' => trim((string) ($payload['summary'] ?? '')),
            'about_html' => trim((string) ($payload['about_html'] ?? '')),
            'skills' => trim((string) ($payload['skills'] ?? '')),
            'experience_html' => trim((string) ($payload['experience_html'] ?? '')),
            'education_html' => trim((string) ($payload['education_html'] ?? '')),
            'projects_html' => trim((string) ($payload['projects_html'] ?? '')),
            'contact_html' => trim((string) ($payload['contact_html'] ?? '')),
            'seo_title' => trim((string) ($payload['seo_title'] ?? '')),
            'seo_description' => trim((string) ($payload['seo_description'] ?? '')),
            'is_public' => ((int) ($payload['is_public'] ?? 0)) === 1 ? 1 : 0,
        ];

        $stmt = db()->prepare(
            'INSERT INTO developer_profiles
                (user_id, headline, location, summary, about_html, skills, experience_html, education_html, projects_html, contact_html, seo_title, seo_description, is_public, created_at, updated_at)
             VALUES
                (:user_id, :headline, :location, :summary, :about_html, :skills, :experience_html, :education_html, :projects_html, :contact_html, :seo_title, :seo_description, :is_public, NOW(), NOW())
             ON DUPLICATE KEY UPDATE
                headline = VALUES(headline),
                location = VALUES(location),
                summary = VALUES(summary),
                about_html = VALUES(about_html),
                skills = VALUES(skills),
                experience_html = VALUES(experience_html),
                education_html = VALUES(education_html),
                projects_html = VALUES(projects_html),
                contact_html = VALUES(contact_html),
                seo_title = VALUES(seo_title),
                seo_description = VALUES(seo_description),
                is_public = VALUES(is_public),
                updated_at = NOW()'
        );

        $stmt->execute([
            'user_id' => $userId,
            ...$data,
        ]);
    }

    private function ensureTable(): void
    {
        db()->exec(
            'CREATE TABLE IF NOT EXISTS developer_profiles (
                id INT(11) NOT NULL AUTO_INCREMENT,
                user_id BIGINT(20) UNSIGNED NOT NULL,
                headline VARCHAR(255) DEFAULT NULL,
                location VARCHAR(255) DEFAULT NULL,
                summary TEXT DEFAULT NULL,
                about_html MEDIUMTEXT DEFAULT NULL,
                skills TEXT DEFAULT NULL,
                experience_html MEDIUMTEXT DEFAULT NULL,
                education_html MEDIUMTEXT DEFAULT NULL,
                projects_html MEDIUMTEXT DEFAULT NULL,
                contact_html MEDIUMTEXT DEFAULT NULL,
                seo_title VARCHAR(255) DEFAULT NULL,
                seo_description TEXT DEFAULT NULL,
                is_public TINYINT(1) NOT NULL DEFAULT 0,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_developer_profiles_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
}
