<?php

declare(strict_types=1);

namespace App\Services\Cms;

class FileManagerService
{
    private const MAX_UPLOAD_SIZE = 10485760; // 10 MB
    /** @var array<string, array<int, string>> */
    private const ALLOWED_UPLOADS = [
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
        'image/webp' => ['webp'],
        'image/gif' => ['gif'],
        'application/pdf' => ['pdf'],
        'text/plain' => ['txt'],
    ];
    /** @var array<int, string> */
    private const FORBIDDEN_EXTENSIONS = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'exe', 'bat', 'cmd', 'com', 'sh', 'ps1',
    ];

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listUserFoldersWithRoles(array $preferredUserIds = []): array
    {
        $preferredUserIds = array_values(array_filter(array_map('intval', $preferredUserIds), static fn (int $id): bool => $id > 0));

        $sourceIds = [];
        $rows = db()->query('SELECT DISTINCT users_id FROM file_album WHERE users_id IS NOT NULL')->fetchAll();
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $id = (int) ($row['users_id'] ?? 0);
                if ($id > 0) {
                    $sourceIds[] = $id;
                }
            }
        }

        $userIds = array_values(array_unique(array_merge($sourceIds, $preferredUserIds)));
        if ($userIds === []) {
            return [];
        }

        sort($userIds);
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = db()->prepare(
            "SELECT u.id AS user_id, u.roles AS role_id, ur.name_role
             FROM users u
             LEFT JOIN users_role ur ON ur.id = u.roles
             WHERE u.id IN ({$placeholders})
             ORDER BY u.id DESC"
        );
        $stmt->execute($userIds);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = (int) ($row['user_id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $roleName = trim((string) ($row['name_role'] ?? ''));
            $result[] = [
                'user_id' => $id,
                'role_id' => (int) ($row['role_id'] ?? 0),
                'role_name' => $roleName,
                'label' => $roleName !== '' ? ('user_id #' . $id . ' - ' . $roleName) : ('user_id #' . $id),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAlbums(int $userId): array
    {
        $stmt = db()->prepare(
            'SELECT id, name_album, info_album
             FROM file_album
             WHERE users_id = :users_id
             ORDER BY id DESC'
        );
        $stmt->execute(['users_id' => $userId]);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $albums = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $id = (int) ($row['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }
            $albums[] = [
                'id' => $id,
                'name' => (string) ($row['name_album'] ?? ('Album #' . $id)),
                'info' => (string) ($row['info_album'] ?? ''),
            ];
        }

        return $albums;
    }

    public function createAlbum(int $userId, string $albumName, string $albumInfo = '-'): int
    {
        $albumName = trim($albumName);
        if ($albumName === '') {
            throw new \RuntimeException('Nama album tidak valid.');
        }

        $exists = db()->prepare(
            'SELECT id FROM file_album WHERE users_id = :users_id AND LOWER(name_album) = LOWER(:name) LIMIT 1'
        );
        $exists->execute([
            'users_id' => $userId,
            'name' => $albumName,
        ]);
        $row = $exists->fetch();
        if (is_array($row) && (int) ($row['id'] ?? 0) > 0) {
            throw new \RuntimeException('Album sudah ada.');
        }

        $stmt = db()->prepare(
            'INSERT INTO file_album (name_album, info_album, created_at, updated_at, users_id)
             VALUES (:name_album, :info_album, :created_at, NULL, :users_id)'
        );
        $stmt->execute([
            'name_album' => $albumName,
            'info_album' => trim($albumInfo) !== '' ? trim($albumInfo) : '-',
            'created_at' => date('Y-m-d H:i:s'),
            'users_id' => $userId,
        ]);

        return (int) db()->lastInsertId();
    }

    public function ensureAlbumForUser(int $userId, int $albumId): int
    {
        $stmt = db()->prepare('SELECT id FROM file_album WHERE id = :id AND users_id = :users_id LIMIT 1');
        $stmt->execute([
            'id' => $albumId,
            'users_id' => $userId,
        ]);
        $row = $stmt->fetch();
        if (!is_array($row) || (int) ($row['id'] ?? 0) <= 0) {
            throw new \RuntimeException('Album tidak ditemukan untuk user ini.');
        }
        return (int) $row['id'];
    }

    public function defaultAlbumId(int $userId): int
    {
        $stmt = db()->prepare(
            'SELECT id FROM file_album WHERE users_id = :users_id ORDER BY id ASC LIMIT 1'
        );
        $stmt->execute(['users_id' => $userId]);
        $row = $stmt->fetch();
        if (is_array($row) && (int) ($row['id'] ?? 0) > 0) {
            return (int) $row['id'];
        }

        return $this->createAlbum($userId, 'Uncategory', 'berisi tentang file tidak berkategori');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listFiles(int $userId, ?int $albumId = null, string $search = '', ?int $limit = null, int $offset = 0): array
    {
        $params = ['users_id' => $userId];
        $whereAlbum = '';
        $search = trim($search);
        if ($albumId !== null && $albumId > 0) {
            $whereAlbum = ' AND fm.album_id = :album_id';
            $params['album_id'] = $albumId;
        }

        $whereSearch = '';
        if ($search !== '') {
            $whereSearch = ' AND (fm.file_name LIKE :search_file_name OR fm.dir_file LIKE :search_dir_file)';
            $params['search_file_name'] = '%' . $search . '%';
            $params['search_dir_file'] = '%' . $search . '%';
        }

        $limitSql = '';
        if ($limit !== null && $limit > 0) {
            $limitSql = ' LIMIT ' . max(1, $limit) . ' OFFSET ' . max(0, $offset);
        }

        $stmt = db()->prepare(
            "SELECT fm.id, fm.file_name, fm.file_size, fm.dir_file, fm.type, fm.extension, fm.created_at, fm.updated_at, fm.album_id,
                    COALESCE(fa.name_album, CONCAT('Album #', fm.album_id)) AS album_name
             FROM file_manager fm
             LEFT JOIN file_album fa ON fa.id = fm.album_id
             WHERE fm.users_id = :users_id{$whereAlbum}{$whereSearch}
             ORDER BY fm.id DESC{$limitSql}"
        );
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $files = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $album = (int) ($row['album_id'] ?? 0);
            $storedName = (string) ($row['dir_file'] ?? '');
            if ($album <= 0 || $storedName === '') {
                continue;
            }

            $resolved = $this->resolveStorage($userId, $album, $storedName);
            $path = $resolved['path'];
            $sizeValue = (int) ($row['file_size'] ?? 0);
            if ($sizeValue <= 0 && is_file($path)) {
                $sizeValue = (int) (filesize($path) ?: 0);
            }

            $modifiedRaw = (string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? ''));
            $modifiedAt = strtotime($modifiedRaw ?: '') ?: 0;

            $ext = strtolower((string) ($row['extension'] ?? pathinfo($storedName, PATHINFO_EXTENSION)));
            $files[] = [
                'id' => (int) ($row['id'] ?? 0),
                'name' => (string) ($row['file_name'] ?? $storedName),
                'stored_name' => $storedName,
                'album_id' => $album,
                'album' => (string) ($row['album_name'] ?? ('Album #' . $album)),
                'size' => $sizeValue,
                'modified_at' => $modifiedAt,
                'url' => $resolved['url'],
                'is_image' => $this->isImageFile($ext),
                'mime' => $this->mimeType($path),
            ];
        }

        return $files;
    }

    public function countFiles(int $userId, ?int $albumId = null, string $search = ''): int
    {
        $params = ['users_id' => $userId];
        $whereAlbum = '';
        $search = trim($search);
        if ($albumId !== null && $albumId > 0) {
            $whereAlbum = ' AND album_id = :album_id';
            $params['album_id'] = $albumId;
        }

        $whereSearch = '';
        if ($search !== '') {
            $whereSearch = ' AND (file_name LIKE :search_file_name OR dir_file LIKE :search_dir_file)';
            $params['search_file_name'] = '%' . $search . '%';
            $params['search_dir_file'] = '%' . $search . '%';
        }

        $stmt = db()->prepare(
            "SELECT COUNT(*) AS total
             FROM file_manager
             WHERE users_id = :users_id{$whereAlbum}{$whereSearch}"
        );
        $stmt->execute($params);
        $row = $stmt->fetch();

        return (int) ($row['total'] ?? 0);
    }

    public function upload(int $userId, array $file, int $albumId): int
    {
        $albumId = $this->ensureAlbumForUser($userId, $albumId);

        $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($errorCode !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Upload file gagal.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        if ($tmpName === '' || !is_uploaded_file($tmpName)) {
            throw new \RuntimeException('File upload tidak valid.');
        }

        $size = (int) ($file['size'] ?? 0);
        if ($size <= 0 || $size > self::MAX_UPLOAD_SIZE) {
            throw new \RuntimeException('Ukuran file tidak valid. Maksimal 10 MB.');
        }

        $original = trim((string) ($file['name'] ?? ''));
        if ($original === '') {
            $original = 'file';
        }

        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?? '';
        if ($ext !== '' && in_array($ext, self::FORBIDDEN_EXTENSIONS, true)) {
            throw new \RuntimeException('Tipe file tidak diizinkan.');
        }

        $mimeType = $this->detectUploadedMimeType($tmpName);
        if ($mimeType === '' || !array_key_exists($mimeType, self::ALLOWED_UPLOADS)) {
            throw new \RuntimeException('Tipe MIME file tidak diizinkan.');
        }
        $allowedExtensions = self::ALLOWED_UPLOADS[$mimeType];
        if ($ext === '' || !in_array($ext, $allowedExtensions, true)) {
            $ext = $allowedExtensions[0] ?? '';
        }
        if ($ext === '') {
            throw new \RuntimeException('Ekstensi file tidak valid.');
        }

        $targetDir = $this->albumDirectory($userId, $albumId, true);
        $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
        $destination = $targetDir . DIRECTORY_SEPARATOR . $storedName;

        if (!move_uploaded_file($tmpName, $destination)) {
            throw new \RuntimeException('Gagal memindahkan file upload.');
        }

        $type = $this->detectType($ext);
        $stmt = db()->prepare(
            'INSERT INTO file_manager
            (info_file, file_name, file_size, dir_file, type, extension, created_at, updated_at, album_id, users_id)
            VALUES (:info_file, :file_name, :file_size, :dir_file, :type, :extension, :created_at, NULL, :album_id, :users_id)'
        );
        $stmt->execute([
            'info_file' => '-',
            'file_name' => $original,
            'file_size' => (string) $size,
            'dir_file' => $storedName,
            'type' => $type,
            'extension' => $ext,
            'created_at' => date('Y-m-d H:i:s'),
            'album_id' => $albumId,
            'users_id' => $userId,
        ]);

        return (int) db()->lastInsertId();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findFileById(int $userId, int $fileId): ?array
    {
        $stmt = db()->prepare(
            'SELECT fm.id, fm.file_name, fm.file_size, fm.dir_file, fm.type, fm.extension, fm.created_at, fm.updated_at, fm.album_id,
                    COALESCE(fa.name_album, CONCAT(\'Album #\', fm.album_id)) AS album_name
             FROM file_manager fm
             LEFT JOIN file_album fa ON fa.id = fm.album_id
             WHERE fm.id = :id AND fm.users_id = :users_id
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $fileId,
            'users_id' => $userId,
        ]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            return null;
        }

        $album = (int) ($row['album_id'] ?? 0);
        $storedName = (string) ($row['dir_file'] ?? '');
        if ($album <= 0 || $storedName === '') {
            return null;
        }

        $resolved = $this->resolveStorage($userId, $album, $storedName);
        $path = $resolved['path'];
        $ext = strtolower((string) ($row['extension'] ?? pathinfo($storedName, PATHINFO_EXTENSION)));

        return [
            'id' => (int) ($row['id'] ?? 0),
            'name' => (string) ($row['file_name'] ?? $storedName),
            'stored_name' => $storedName,
            'album_id' => $album,
            'album' => (string) ($row['album_name'] ?? ('Album #' . $album)),
            'size' => (int) ($row['file_size'] ?? 0),
            'modified_at' => strtotime((string) (($row['updated_at'] ?? '') !== '' ? $row['updated_at'] : ($row['created_at'] ?? ''))) ?: 0,
            'url' => $resolved['url'],
            'is_image' => $this->isImageFile($ext),
            'mime' => $this->mimeType($path),
        ];
    }

    public function delete(int $userId, int $fileId): void
    {
        $stmt = db()->prepare(
            'SELECT id, dir_file, album_id FROM file_manager
             WHERE id = :id AND users_id = :users_id
             LIMIT 1'
        );
        $stmt->execute([
            'id' => $fileId,
            'users_id' => $userId,
        ]);
        $row = $stmt->fetch();
        if (!is_array($row)) {
            throw new \RuntimeException('File tidak ditemukan.');
        }

        $albumId = (int) ($row['album_id'] ?? 0);
        $dirFile = basename((string) ($row['dir_file'] ?? ''));
        if ($albumId <= 0 || $dirFile === '') {
            throw new \RuntimeException('Data file tidak valid.');
        }

        $path = $this->filePath($userId, $albumId, $dirFile);
        if (is_file($path)) {
            @unlink($path);
        }

        $deleteStmt = db()->prepare('DELETE FROM file_manager WHERE id = :id AND users_id = :users_id');
        $deleteStmt->execute([
            'id' => $fileId,
            'users_id' => $userId,
        ]);
    }

    public function publicUrl(int $userId, int $albumId, string $filename): string
    {
        return '/storage/filemanager/'
            . rawurlencode((string) $userId)
            . '/'
            . rawurlencode((string) $albumId)
            . '/'
            . rawurlencode($filename);
    }

    private function baseDirectory(): string
    {
        $preferred = app()->basePath('storage/filemanager');
        if (is_dir($preferred)) {
            return $preferred;
        }

        return app()->basePath('public/storage/filemanager');
    }

    private function albumDirectory(int $userId, int $albumId, bool $create): string
    {
        $base = $this->baseDirectory();
        $userDir = $base . DIRECTORY_SEPARATOR . $userId;
        $albumDir = $userDir . DIRECTORY_SEPARATOR . $albumId;

        if ($create) {
            if (!is_dir($base)) {
                @mkdir($base, 0775, true);
            }
            if (!is_dir($userDir)) {
                @mkdir($userDir, 0775, true);
            }
            if (!is_dir($albumDir)) {
                @mkdir($albumDir, 0775, true);
            }
        }

        if (!is_dir($albumDir)) {
            throw new \RuntimeException('Folder album tidak tersedia.');
        }

        return $albumDir;
    }

    private function filePath(int $userId, int $albumId, string $filename): string
    {
        return $this->baseDirectory() . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . $albumId . DIRECTORY_SEPARATOR . $filename;
    }

    /**
     * @return array{path:string,url:string}
     */
    private function resolveStorage(int $userId, int $albumId, string $filename): array
    {
        $filename = basename($filename);
        $baseDir = $this->baseDirectory();

        $candidates = [
            [
                'path' => $this->filePath($userId, $albumId, $filename),
                'url' => $this->publicUrl($userId, $albumId, $filename),
            ],
            [
                'path' => $baseDir . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . $filename,
                'url' => '/storage/filemanager/' . rawurlencode((string) $userId) . '/' . rawurlencode($filename),
            ],
            [
                'path' => $baseDir . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . 'thumbnail' . DIRECTORY_SEPARATOR . $filename,
                'url' => '/storage/filemanager/' . rawurlencode((string) $userId) . '/thumbnail/' . rawurlencode($filename),
            ],
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate['path'])) {
                return $candidate;
            }
        }

        $patterns = [
            $baseDir . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $filename,
            $baseDir . DIRECTORY_SEPARATOR . $userId . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . $filename,
        ];

        foreach ($patterns as $pattern) {
            $matches = glob($pattern);
            if (!is_array($matches) || $matches === []) {
                continue;
            }

            foreach ($matches as $matchPath) {
                if (!is_file($matchPath)) {
                    continue;
                }

                $relative = str_replace('\\', '/', substr($matchPath, strlen($baseDir)));
                $relative = ltrim($relative, '/');
                if ($relative === '') {
                    continue;
                }

                return [
                    'path' => $matchPath,
                    'url' => '/storage/filemanager/' . str_replace('%2F', '/', rawurlencode($relative)),
                ];
            }
        }

        return $candidates[0];
    }

    private function isImageFile(string $ext): bool
    {
        return in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    }

    private function detectType(string $ext): string
    {
        $ext = strtolower($ext);
        if ($this->isImageFile($ext)) {
            return 'Image';
        }
        if (in_array($ext, ['pdf', 'txt'], true)) {
            return 'Documents';
        }
        return 'File';
    }

    private function detectUploadedMimeType(string $tmpName): string
    {
        if (!class_exists(\finfo::class)) {
            return '';
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detected = $finfo->file($tmpName);
        if (!is_string($detected) || trim($detected) === '') {
            return '';
        }

        return strtolower(trim($detected));
    }

    private function mimeType(string $path): string
    {
        if (!is_file($path)) {
            return 'application/octet-stream';
        }
        $mime = mime_content_type($path);
        if ($mime === false) {
            return 'application/octet-stream';
        }
        return (string) $mime;
    }
}
