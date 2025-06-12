<?php
// Script to fix Max Receivable and Total fields in goods receipts modal
$filePath = __DIR__ . '/resources/views/livewire/supply-chain/goods-receipts-modals.blade.php';
$content = file_get_contents($filePath);

// First replace: Fix Max Receivable to show Previously Received Quantity
$pattern1 = '/{{ __\(\'messages\.max_receivable\'\) }}: {{ number_format\(\$item\[\'max_receivable\'\], 2\) }}/';
$replacement1 = '{{ __(\'messages.max_receivable\') }}: {{ number_format($previouslyReceived, 2) }}';
$content = preg_replace($pattern1, $replacement1, $content);

// Second replace: Fix Total field to show Previously Received Quantity (first pattern)
$pattern2 = '/{{ __\(\'messages\.total\'\) }}: {{ number_format\(\$item\[\'original_accepted\'\] \+ \$acceptedQty, 2\) }}/';
$replacement2 = '{{ __(\'messages.total\') }}: {{ number_format($previouslyReceived, 2) }}';
$content = preg_replace($pattern2, $replacement2, $content);

// Third replace: Fix any remaining Total fields (second pattern if needed)
$pattern3 = '/{{ __\(\'messages\.total\'\) }}: {{ number_format\(\$item\[\'original_accepted\'\], 2\) }}/';
$content = preg_replace($pattern3, $replacement2, $content);

// Write the updated content back to the file
file_put_contents($filePath, $content);

echo "Fixed Max Receivable and Total fields to show Previously Received Quantity.\n";
?>
