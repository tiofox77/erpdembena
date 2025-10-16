# PowerShell Script to Enable JIT in Laragon
# Run as Administrator for best results

Write-Host "🚀 PHP JIT Enabler for Laragon" -ForegroundColor Cyan
Write-Host "================================" -ForegroundColor Cyan
Write-Host ""

# Find Laragon PHP directory
$laragonPath = "C:\laragon\bin\php"

if (-not (Test-Path $laragonPath)) {
    Write-Host "❌ Laragon PHP directory not found at: $laragonPath" -ForegroundColor Red
    Write-Host "Please check your Laragon installation path." -ForegroundColor Yellow
    pause
    exit
}

# Find PHP 8.x directories
$phpDirs = Get-ChildItem $laragonPath -Directory | Where-Object { $_.Name -like "php-8.*" } | Sort-Object Name -Descending

if ($phpDirs.Count -eq 0) {
    Write-Host "❌ No PHP 8.x installation found" -ForegroundColor Red
    pause
    exit
}

Write-Host "📦 Found PHP installations:" -ForegroundColor Green
for ($i = 0; $i -lt $phpDirs.Count; $i++) {
    Write-Host "  [$i] $($phpDirs[$i].Name)" -ForegroundColor White
}
Write-Host ""

# Select PHP version
if ($phpDirs.Count -eq 1) {
    $selectedPhp = $phpDirs[0]
    Write-Host "✅ Using: $($selectedPhp.Name)" -ForegroundColor Green
} else {
    $selection = Read-Host "Select PHP version (0-$($phpDirs.Count - 1))"
    $selectedPhp = $phpDirs[[int]$selection]
}

$phpIniPath = Join-Path $selectedPhp.FullName "php.ini"

if (-not (Test-Path $phpIniPath)) {
    Write-Host "❌ php.ini not found at: $phpIniPath" -ForegroundColor Red
    pause
    exit
}

Write-Host ""
Write-Host "📝 Reading php.ini..." -ForegroundColor Yellow
$phpIniContent = Get-Content $phpIniPath -Raw

# Check current JIT status
$hasJitBuffer = $phpIniContent -match "opcache\.jit_buffer_size\s*=\s*\d+"
$hasJit = $phpIniContent -match "opcache\.jit\s*=\s*\d+"

Write-Host ""
Write-Host "Current Status:" -ForegroundColor Cyan
if ($hasJitBuffer) {
    Write-Host "  ✅ JIT buffer size configured" -ForegroundColor Green
} else {
    Write-Host "  ❌ JIT buffer size not configured" -ForegroundColor Red
}

if ($hasJit) {
    Write-Host "  ✅ JIT mode configured" -ForegroundColor Green
} else {
    Write-Host "  ❌ JIT mode not configured" -ForegroundColor Red
}

Write-Host ""
$confirm = Read-Host "Do you want to enable/update JIT configuration? (Y/N)"

if ($confirm -ne "Y" -and $confirm -ne "y") {
    Write-Host "❌ Operation cancelled" -ForegroundColor Yellow
    pause
    exit
}

# Backup php.ini
$backupPath = "$phpIniPath.backup-$(Get-Date -Format 'yyyyMMdd-HHmmss')"
Copy-Item $phpIniPath $backupPath
Write-Host ""
Write-Host "💾 Backup created: $backupPath" -ForegroundColor Green

# JIT Configuration to add
$jitConfig = @"

; ========================================
; JIT Configuration (Added by enable-jit.ps1)
; ========================================
opcache.jit_buffer_size=128M
opcache.jit=1255

"@

# Remove old JIT configurations if exist
$phpIniContent = $phpIniContent -replace "opcache\.jit_buffer_size\s*=.*", ""
$phpIniContent = $phpIniContent -replace "opcache\.jit\s*=.*", ""

# Add new JIT configuration
$phpIniContent += $jitConfig

# Save php.ini
Set-Content -Path $phpIniPath -Value $phpIniContent -NoNewline

Write-Host ""
Write-Host "✅ JIT configuration added to php.ini" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Configuration applied:" -ForegroundColor Cyan
Write-Host "  opcache.jit_buffer_size = 128M" -ForegroundColor White
Write-Host "  opcache.jit = 1255" -ForegroundColor White
Write-Host ""
Write-Host "⚠️  IMPORTANT: You must restart Laragon for changes to take effect!" -ForegroundColor Yellow
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "  1. Open Laragon" -ForegroundColor White
Write-Host "  2. Click 'Stop All'" -ForegroundColor White
Write-Host "  3. Wait 3 seconds" -ForegroundColor White
Write-Host "  4. Click 'Start All'" -ForegroundColor White
Write-Host "  5. Visit: http://erpdembena.test/maintenance/settings?activeTab=opcache" -ForegroundColor White
Write-Host ""
Write-Host "✨ Done! JIT should now be enabled." -ForegroundColor Green
Write-Host ""

$openLaragon = Read-Host "Do you want to open php.ini to verify? (Y/N)"
if ($openLaragon -eq "Y" -or $openLaragon -eq "y") {
    notepad $phpIniPath
}

pause
