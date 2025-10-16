<?php
/**
 * Clear OPcache via Web
 * Access: http://erpdembena.test/clear-opcache.php
 */

// Security: Only allow in development
if (getenv('APP_ENV') === 'production') {
    die('Not allowed in production');
}

$results = [];

// 1. Clear OPcache
if (function_exists('opcache_reset')) {
    $opcacheCleared = opcache_reset();
    $results[] = $opcacheCleared ? '✅ OPcache cleared' : '❌ OPcache reset failed';
} else {
    $results[] = '⚠️ OPcache not available';
}

// 2. Clear realpath cache
clearstatcache(true);
$results[] = '✅ Realpath cache cleared';

// 3. Check if file exists
$targetFile = dirname(__DIR__) . '/app/Livewire/Settings/SystemSettings.php';
if (file_exists($targetFile)) {
    $content = file_get_contents($targetFile);
    $hasWarning = preg_match("/'warning'[,;]/", $content) && !preg_match("/type:\s*'warning'/", $content);
    $results[] = $hasWarning ? '❌ File still has "warning" status' : '✅ File is correct';
} else {
    $results[] = '❌ File not found';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>OPcache Cleared</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .result {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            background: #ecf0f1;
        }
        .success {
            background: #d4edda;
            color: #155724;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🧹 OPcache & Cache Cleared</h1>
        
        <?php foreach ($results as $result): ?>
            <?php
                $class = 'result';
                if (strpos($result, '✅') !== false) $class .= ' success';
                elseif (strpos($result, '⚠️') !== false) $class .= ' warning';
                elseif (strpos($result, '❌') !== false) $class .= ' error';
            ?>
            <div class="<?= $class ?>"><?= htmlspecialchars($result) ?></div>
        <?php endforeach; ?>
        
        <p style="margin-top: 30px;">
            <strong>Next Steps:</strong><br>
            1. Restart Apache/Nginx in Laragon<br>
            2. Clear browser cache (Ctrl+Shift+Delete)<br>
            3. Reload the Settings page
        </p>
        
        <a href="/maintenance/settings" class="button">Go to Settings</a>
        <a href="javascript:location.reload()" class="button">Reload This Page</a>
    </div>
</body>
</html>
