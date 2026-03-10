$root = 'C:/xampp/htdocs/aiticore-cms';
$sqlPath = $root . '/aitisolutions.sql';
$outPath = $root . '/aitisolutions_webp_migrated.sql';
$uploadsDir = $root . '/public/storage';

if (-not (Test-Path $sqlPath)) { throw "Missing SQL file: $sqlPath" }
if (-not (Test-Path $uploadsDir)) { throw "Missing uploads dir: $uploadsDir" }

$sql = Get-Content -Path $sqlPath -Raw -Encoding Unicode
$webpBases = @{}
Get-ChildItem -Path $uploadsDir -File -Filter *.webp | ForEach-Object {
    $base = [System.IO.Path]::GetFileNameWithoutExtension($_.Name).ToLowerInvariant()
    $webpBases[$base] = $true
}

$replaceCount = 0
$imgRegex = [regex]'(?<![A-Za-z0-9_-])([A-Za-z0-9._-]+)\.(png|jpe?g|gif|bmp|tiff?)\b'
$sql = $imgRegex.Replace($sql, {
    param($m)
    $base = $m.Groups[1].Value.ToLowerInvariant()
    if ($webpBases.ContainsKey($base)) {
        $script:replaceCount++
        return $m.Groups[1].Value + '.webp'
    }
    return $m.Value
})

$serializedFixCount = 0
$serRegex = [regex]'s:(\d+):\\"((?:[^\\"\\]|\\.)*)\\";'
$sql = $serRegex.Replace($sql, {
    param($m)
    $raw = $m.Groups[2].Value
    $decoded = [System.Text.RegularExpressions.Regex]::Unescape($raw)
    $len = [System.Text.Encoding]::UTF8.GetByteCount($decoded)
    if ([int]$m.Groups[1].Value -ne $len) {
        $script:serializedFixCount++
        return ('s:{0}:\"{1}\";' -f $len, $raw)
    }
    return $m.Value
})

[System.IO.File]::WriteAllText($outPath, $sql, [System.Text.Encoding]::Unicode)

Write-Output "Output: $outPath"
Write-Output "WebP basenames: $($webpBases.Count)"
Write-Output "Image reference replacements: $replaceCount"
Write-Output "Serialized length fixes: $serializedFixCount"
