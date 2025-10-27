# Fix: Session Storage Directory Error

## Problem
```
file_put_contents(/home/softec/dembenaerp.softec.vip/storage/framework/sessions/...): 
Failed to open stream: No such file or directory
```

## Root Cause
The `storage/framework/sessions` directory (and potentially other storage subdirectories) don't exist on your production server. This happens when:
- Storage directories aren't tracked in Git (they're in .gitignore)
- The directory structure wasn't created during deployment
- Permissions issues prevent Laravel from creating directories

## Solution - Option 1: Quick Fix (Run on Production Server)

### Step 1: Upload and Run the Fix Script
1. Upload `fix_storage_directories.php` to your production server root
2. Run it via command line:
   ```bash
   php fix_storage_directories.php
   ```
   Or access it via browser:
   ```
   https://dembenaerp.softec.vip/fix_storage_directories.php
   ```

### Step 2: Set Proper Permissions
After running the script, set correct permissions:
```bash
cd /home/softec/dembenaerp.softec.vip
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage
chown -R www-data:www-data bootstrap/cache
```

**Note:** Replace `www-data` with your actual web server user (might be `apache`, `nginx`, `nobody`, etc.)

### Step 3: Clear Laravel Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Solution - Option 2: Manual Directory Creation

If you have SSH access:
```bash
cd /home/softec/dembenaerp.softec.vip/storage/framework
mkdir -p sessions views cache/data testing
chmod -R 775 sessions views cache testing
```

## Solution - Option 3: Using cPanel File Manager

1. Log into cPanel
2. Navigate to File Manager
3. Go to `/home/softec/dembenaerp.softec.vip/storage/framework/`
4. Create these folders if missing:
   - `sessions`
   - `views`
   - `cache/data`
   - `testing`
5. Set permissions to 755 or 775 for each folder

## Prevention: Track Directory Structure in Git

The `.gitignore` files have been added to preserve directory structure:
- `storage/framework/sessions/.gitignore`
- `storage/framework/views/.gitignore`
- `storage/framework/cache/.gitignore`
- `storage/framework/cache/data/.gitignore`

### Commit these changes:
```bash
git add storage/framework/*/.gitignore
git commit -m "Add .gitignore files to preserve storage directory structure"
git push origin main
```

Now when you deploy, the directory structure will be preserved.

## Verification

After fixing, test by:
1. Clearing browser cookies
2. Accessing your application
3. Checking that sessions work without errors

## Common Permission Issues

If you still see errors:
```bash
# Find your web server user
ps aux | grep -E '(apache|nginx|httpd)'

# Set ownership (replace USER with your web server user)
chown -R USER:USER storage bootstrap/cache

# Set permissions
find storage -type f -exec chmod 644 {} \;
find storage -type d -exec chmod 755 {} \;
find bootstrap/cache -type f -exec chmod 644 {} \;
find bootstrap/cache -type d -exec chmod 755 {} \;
```

## Why This Happened

Laravel's default `.gitignore` excludes session files but the directory structure wasn't preserved:
```
storage/framework/sessions/*
```

This means Git doesn't track the `sessions` directory itself, causing it to be missing after deployment.

The fix adds a `.gitignore` inside each directory that says "ignore everything except this .gitignore file", which ensures the directory structure is tracked in Git.
