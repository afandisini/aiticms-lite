Param(
    [switch]$UpdateRemote,
    [switch]$Prune
)

$ErrorActionPreference = "Stop"

$repoRoot = Resolve-Path (Join-Path $PSScriptRoot "..")
$submodulePath = Join-Path $repoRoot "core\AitiCore"
$sourceSystem = Join-Path $submodulePath "system"
$targetSystem = Join-Path $repoRoot "system"

if (!(Test-Path $submodulePath)) {
    Write-Error "Submodule core/AitiCore belum ada. Jalankan: git submodule update --init --recursive"
}

Write-Host "==> Init/update submodule"
git -C $repoRoot submodule update --init --recursive core/AitiCore | Out-Null

if ($UpdateRemote) {
    Write-Host "==> Pull latest core (remote)"
    git -C $repoRoot submodule update --remote --merge core/AitiCore
}

if (!(Test-Path $sourceSystem)) {
    Write-Error "Source core tidak ditemukan: $sourceSystem"
}
if (!(Test-Path $targetSystem)) {
    Write-Error "Target system tidak ditemukan: $targetSystem"
}

Write-Host "==> Diff ringkas sebelum sync"
git -C $repoRoot diff --no-index --name-status -- system core/AitiCore/system

Write-Host "==> Sync core/AitiCore/system -> system"
$robocopyArgs = @(
    $sourceSystem,
    $targetSystem,
    "/E",
    "/R:1",
    "/W:1",
    "/NFL",
    "/NDL",
    "/NP",
    "/NJH",
    "/NJS"
)
if ($Prune) {
    # WARNING: /PURGE akan menghapus file di target yang tidak ada di source.
    $robocopyArgs += "/PURGE"
}

& robocopy @robocopyArgs | Out-Null
$exitCode = $LASTEXITCODE
if ($exitCode -gt 7) {
    Write-Error "Robocopy gagal dengan exit code $exitCode"
}

Write-Host "==> Diff ringkas setelah sync"
git -C $repoRoot diff --name-status -- system

Write-Host "Selesai."

