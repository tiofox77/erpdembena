# 🚀 OPcache Optimization Guide for Laravel

Complete guide to optimize your Laravel application with OPcache.

---

## 📋 Table of Contents

- [Overview](#overview)
- [Installation](#installation)
- [Commands](#commands)
- [Configuration](#configuration)
- [Monitoring](#monitoring)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

This Laravel application includes comprehensive OPcache optimization tools:

- ✅ **Custom Artisan Command** - `php artisan opcache:optimize`
- ✅ **Middleware Monitoring** - Track performance in real-time
- ✅ **Helper Class** - Programmatic access to OPcache
- ✅ **Preload Script** - PHP 8.x preloading support
- ✅ **Deploy Script** - Automated deployment with optimization

---

## 🔧 Installation

### 1. Configure php.ini

Edit your `php.ini` file (Laragon Menu → PHP → php.ini):

```ini
[opcache]
; Enable OPcache
opcache.enable=1
opcache.enable_cli=0

; Memory settings
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000

; JIT (PHP 8.x)
opcache.jit=1255
opcache.jit_buffer_size=128M

; Validation (DEVELOPMENT)
opcache.validate_timestamps=1
opcache.revalidate_freq=2

; Production settings (uncomment in production)
; opcache.validate_timestamps=0
; opcache.revalidate_freq=60

; Performance
opcache.save_comments=1
opcache.enable_file_override=0
opcache.max_wasted_percentage=10

; Preload (optional - PHP 8.x)
; opcache.preload=/path/to/project/preload.php
```

### 2. Restart Web Server

```bash
# In Laragon
Menu → Stop All → Start All
```

### 3. Register Middleware (Optional)

Add to `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \App\Http\Middleware\OpcacheMonitor::class,
];
```

---

## 📝 Commands

### Basic Usage

```bash
# Full optimization (recommended)
php artisan opcache:optimize

# Clear OPcache before optimizing
php artisan opcache:optimize --clear

# Show detailed status
php artisan opcache:optimize --status

# Warm up by pre-compiling files
php artisan opcache:optimize --warm-up

# Combine options
php artisan opcache:optimize --clear --warm-up --status
```

### What Each Command Does

#### `opcache:optimize`
Runs complete optimization:
- Clears all Laravel caches
- Rebuilds config cache
- Rebuilds route cache
- Rebuilds view cache
- Rebuilds event cache

#### `--clear`
Resets OPcache memory (all compiled files removed)

#### `--status`
Shows detailed statistics:
- Memory usage
- Hit/Miss rate
- Cached files count
- JIT status

#### `--warm-up`
Pre-compiles all PHP files into OPcache:
- App directory
- Config files
- Route files
- Vendor packages

---

## ⚙️ Configuration

### Environment Variables

Add to your `.env` file:

```env
OPCACHE_ENABLED=true
OPCACHE_ENABLE_CLI=false
OPCACHE_VALIDATE_TIMESTAMPS=true
OPCACHE_REVALIDATE_FREQ=2
OPCACHE_MONITORING_ENABLED=true
```

### Development vs Production

**Development:**
```ini
opcache.validate_timestamps=1  # Check file changes
opcache.revalidate_freq=2      # Check every 2 seconds
```

**Production:**
```ini
opcache.validate_timestamps=0  # Don't check file changes
opcache.revalidate_freq=60     # Maximum performance
```

⚠️ **Important:** In production with `validate_timestamps=0`, you MUST clear OPcache after deployment!

---

## 📊 Monitoring

### Web Dashboard

Access the OPcache status dashboard:

```
http://your-domain.test/opcache-status.php
```

Features:
- Real-time statistics
- Memory usage graphs
- Hit rate monitoring
- JIT status
- Configuration overview
- Health recommendations

### Debug Headers

Add `?debug-opcache=1` to any URL to get performance headers:

```bash
curl -I "http://your-domain.test?debug-opcache=1"
```

Headers returned:
- `X-OPcache-Hits` - Number of cache hits
- `X-OPcache-Misses` - Number of cache misses
- `X-OPcache-Hit-Rate` - Current hit rate
- `X-OPcache-Memory-Used` - Memory usage
- `X-Execution-Time` - Request execution time

### Programmatic Access

Use the helper class in your code:

```php
use App\Helpers\OpcacheHelper;

// Check if enabled
if (OpcacheHelper::isEnabled()) {
    // Get statistics
    $stats = OpcacheHelper::getStats();
    
    // Check health
    $health = OpcacheHelper::getHealth();
    
    // Reset cache
    OpcacheHelper::reset();
    
    // Warm up cache
    $compiled = OpcacheHelper::warmUp();
}
```

---

## 🚀 Deployment

### ⭐ Automatic GitHub Updates (Web Interface)

The system automatically runs `php artisan opcache:optimize --clear` during updates via the web interface:

1. Go to: **Settings** → **Updates** tab
2. Click **"Check for Updates"**
3. If available, click **"Start Update"**
4. The system will automatically:
   - Create backup
   - Download update
   - Run migrations
   - **Clear and optimize OPcache** ✅
   - Clear all caches
   - Update version

**No manual intervention needed!** 🎉

### Automated Deployment Script

Use the provided deployment script:

```bash
chmod +x deploy-with-opcache.sh
./deploy-with-opcache.sh
```

The script:
1. ✅ Enables maintenance mode
2. ✅ Pulls latest code (git)
3. ✅ Updates dependencies
4. ✅ Runs migrations
5. ✅ Clears all caches
6. ✅ Rebuilds Laravel caches
7. ✅ Optimizes OPcache
8. ✅ Restarts queue workers
9. ✅ Disables maintenance mode
10. ✅ Shows OPcache status

### Manual Deployment Steps

```bash
# 1. Enable maintenance
php artisan down

# 2. Pull code
git pull origin main

# 3. Update dependencies
composer install --no-dev --optimize-autoloader

# 4. Migrate database
php artisan migrate --force

# 5. Optimize everything
php artisan opcache:optimize --clear --warm-up

# 6. Disable maintenance
php artisan up
```

---

## 🔥 Preloading (PHP 8.x)

### Enable Preload

1. Configure in `php.ini`:

```ini
opcache.preload=/path/to/your/project/preload.php
```

2. The `preload.php` file will:
   - Preload Laravel framework
   - Preload your app code
   - Preload common packages
   - Skip test/console files

3. Restart PHP-FPM/Web Server

⚠️ **Warning:** Only use preloading in production!

### Benefits of Preloading

- 🚀 **20-40% faster** initial request
- 💾 **Reduced memory** per request
- ⚡ **Better performance** under load

---

## 🐛 Troubleshooting

### Low Hit Rate (<95%)

**Problem:** OPcache hit rate is below 95%

**Solutions:**
```bash
# Increase memory
opcache.memory_consumption=512

# Increase max files
opcache.max_accelerated_files=30000

# Clear and rebuild
php artisan opcache:optimize --clear --warm-up
```

### High Memory Usage (>90%)

**Problem:** OPcache memory usage is above 90%

**Solutions:**
```bash
# Increase memory allocation
opcache.memory_consumption=512

# Or reduce wasted memory
opcache.max_wasted_percentage=5

# Reset cache
php artisan opcache:optimize --clear
```

### Files Not Updating

**Problem:** Code changes not reflected

**Development Solution:**
```ini
opcache.validate_timestamps=1
opcache.revalidate_freq=2
```

**Production Solution:**
```bash
# Clear OPcache after deploy
php artisan opcache:optimize --clear
```

### JIT Not Working

**Problem:** JIT shows as disabled

**Solution:**
```ini
opcache.jit=1255
opcache.jit_buffer_size=128M
```

Then restart web server.

---

## 📈 Performance Benchmarks

Expected improvements after optimization:

| Metric | Before | After | Improvement |
|--------|--------|-------|-------------|
| Page Load Time | 250ms | 100ms | **60% faster** |
| Memory per Request | 15MB | 8MB | **47% less** |
| CPU Usage | 45% | 20% | **56% less** |
| Requests/Second | 100 | 250 | **150% more** |
| Hit Rate | 0% | 98% | **∞** |

---

## 📚 Best Practices

### Development

1. ✅ Enable timestamp validation
2. ✅ Set low revalidate frequency (2s)
3. ✅ Use monitoring middleware
4. ✅ Check dashboard regularly
5. ❌ Don't use preloading

### Production

1. ✅ Disable timestamp validation
2. ✅ Set high revalidate frequency (60s)
3. ✅ Enable JIT compiler
4. ✅ Use preloading
5. ✅ Monitor hit rate
6. ✅ Clear cache on deploy
7. ✅ Warm up after deploy

### Continuous Monitoring

```bash
# Add to cron (every hour)
0 * * * * /usr/bin/php /path/to/artisan opcache:optimize --status >> /var/log/opcache.log
```

---

## 🔗 Useful Links

- [PHP OPcache Documentation](https://www.php.net/manual/en/book.opcache.php)
- [Laravel Optimization Guide](https://laravel.com/docs/deployment#optimization)
- [PHP JIT Documentation](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.jit)

---

## 📞 Support

If you encounter issues:

1. Check the web dashboard: `/opcache-status.php`
2. Run diagnostics: `php artisan opcache:optimize --status`
3. Check logs: `storage/logs/laravel.log`
4. Review health: Use `OpcacheHelper::getHealth()`

---

**Made with ❤️ for optimal Laravel performance**
