<?php\n\necho 'Creating backup...';\n\n// Create backup\ncopy(\
c:\\laragon\\www\\ERPDEMBENA\\app\\Livewire\\Mrp\\ProductionScheduling.php\, \c:\\laragon\\www\\ERPDEMBENA\\app\\Livewire\\Mrp\\ProductionScheduling.php.bak2\);\n\necho \\\nReplacing
problematic
hyphens
in
file...\\n\;\n\n// Read the file\n = file_get_contents(\c:\\laragon\\www\\ERPDEMBENA\\app\\Livewire\\Mrp\\ProductionScheduling.php\);\n\n// Replace problematic characters\n = str_replace([\\\xe2\\x80\\x93\, \\\xe2\\x80\\x94\], \-\, );\n\n// Write back to file\nfile_put_contents(\c:\\laragon\\www\\ERPDEMBENA\\app\\Livewire\\Mrp\\ProductionScheduling.php\, );\n\necho \Done!
File
updated.\;\n
