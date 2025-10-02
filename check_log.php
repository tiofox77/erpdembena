<?php

$logFile = __DIR__ . '/storage/logs/laravel.log';
$lines = file($logFile);
$output = [];

// Find last occurrence of "Excel columns detected"
for ($i = count($lines) - 1; $i >= 0; $i--) {
    if (strpos($lines[$i], 'Excel columns detected') !== false) {
        $output[] = "═══ COLUMNS DETECTED ═══\n" . $lines[$i];
        break;
    }
}

// Find last occurrence of "First row data"
for ($i = count($lines) - 1; $i >= 0; $i--) {
    if (strpos($lines[$i], 'First row data') !== false) {
        $output[] = "═══ FIRST ROW DATA ═══\n" . $lines[$i];
        break;
    }
}

// Find last occurrence of "Debug raw values"
for ($i = count($lines) - 1; $i >= 0; $i--) {
    if (strpos($lines[$i], 'Debug raw values') !== false) {
        $output[] = "═══ DEBUG RAW VALUES ═══\n" . $lines[$i];
        break;
    }
}

// Find last occurrence of "Parsing attendance"
for ($i = count($lines) - 1; $i >= 0; $i--) {
    if (strpos($lines[$i], 'Parsing attendance') !== false) {
        $output[] = "═══ PARSING RESULT ═══\n" . $lines[$i];
        break;
    }
}

file_put_contents(__DIR__ . '/import_debug.txt', implode("\n\n", array_reverse($output)));
echo "✓ Debug saved to import_debug.txt\n";
echo "\n📌 IMPORTANT: Import the Excel file NOW, then run this script again!\n";
