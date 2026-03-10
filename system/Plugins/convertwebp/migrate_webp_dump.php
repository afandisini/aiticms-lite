<?php
$root = 'C:/xampp/htdocs/aiticore-cms';
$sqlPath = $root . '/aitisolutions.sql';
$outPath = $root . '/aitisolutions_webp_migrated.sql';
$uploadsDir = $root . '/public/storage';

if (!is_file($sqlPath)) {
    fwrite(STDERR, "sql file not found: $sqlPath\n");
    exit(1);
}
if (!is_dir($uploadsDir)) {
    fwrite(STDERR, "uploads dir not found: $uploadsDir\n");
    exit(1);
}

$sql = file_get_contents($sqlPath);
if ($sql === false) {
    fwrite(STDERR, "failed to read $sqlPath\n");
    exit(1);
}

$webpBases = [];
foreach (glob($uploadsDir . '/*.webp') as $webpFile) {
    $base = strtolower(pathinfo($webpFile, PATHINFO_FILENAME));
    $webpBases[$base] = true;
}

$exts = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'tif', 'tiff'];
$map = [];
foreach (array_keys($webpBases) as $base) {
    foreach ($exts as $ext) {
        $map[$base . '.' . $ext] = $base . '.webp';
    }
}

$replaceCount = 0;
$sql = preg_replace_callback(
    '~(?<![A-Za-z0-9_-])([A-Za-z0-9._-]+)\.(png|jpe?g|gif|bmp|tiff?)\b~i',
    static function ($m) use ($webpBases, &$replaceCount) {
        $base = strtolower($m[1]);
        if (!isset($webpBases[$base])) {
            return $m[0];
        }
        $replaceCount++;
        return $m[1] . '.webp';
    },
    $sql
);

if ($sql === null) {
    fwrite(STDERR, "regex replacement failed\n");
    exit(1);
}

$serializedFixCount = 0;
$sql = preg_replace_callback(
    '~s:(\d+):\\"((?:[^\\"\\\\]|\\\\.)*)\\";~s',
    static function ($m) use (&$serializedFixCount) {
        $decoded = stripcslashes($m[2]);
        $len = strlen($decoded);
        if ((int) $m[1] !== $len) {
            $serializedFixCount++;
            return 's:' . $len . ':\\"' . $m[2] . '\\";';
        }
        return $m[0];
    },
    $sql
);

if ($sql === null) {
    fwrite(STDERR, "serialized length fix failed\n");
    exit(1);
}

if (file_put_contents($outPath, $sql) === false) {
    fwrite(STDERR, "failed to write $outPath\n");
    exit(1);
}

echo "Output: $outPath\n";
echo "WebP basenames: " . count($webpBases) . "\n";
echo "Image reference replacements: $replaceCount\n";
echo "Serialized length fixes: $serializedFixCount\n";
