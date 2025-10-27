# Laravel Excel - Resolução de Problemas para cPanel

## ✅ Status Local Verificado
- **Extensões PHP**: Todas necessárias estão activas
- **Laravel Excel**: v3.1.66 instalado correctamente
- **Permissões**: Directórios storage com 777
- **Configuração**: excel.php presente

## ⚠️ Problemas Comuns em cPanel

### 1. **Extensões PHP Desactivadas**
```bash
# Verificar no cPanel → PHP Selector → Extensions
zip ✅ (obrigatória)
xml ✅ (obrigatória) 
gd ✅ (obrigatória)
simplexml ✅ (obrigatória)
xmlreader ✅ (obrigatória)
zlib ✅ (obrigatória)
```

### 2. **Limites PHP Insuficientes**
```ini
# Ajustar em cPanel → PHP Options ou .htaccess
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 10M
post_max_size = 10M
```

### 3. **Permissões Restrictivas**
```bash
# Definir permissões corretas via File Manager
chmod 755 storage/
chmod 755 storage/framework/
chmod 755 storage/framework/cache/
chmod 755 storage/framework/cache/laravel-excel/
chmod 644 storage/framework/cache/laravel-excel/*
```

### 4. **Directório Temporário Missing**
```bash
# Criar manualmente se não existir
mkdir -p storage/framework/cache/laravel-excel
```

### 5. **Autoload/Vendor Issues**
```bash
# Re-gerar autoload em produção
composer install --no-dev --optimize-autoloader
composer dump-autoload
```

### 6. **Cache Configuration**
```bash
# Limpar caches Laravel
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

## 🔧 Configuração Excel Optimizada para cPanel

```php
// config/excel.php - ajustes para cPanel
'exports' => [
    'chunk_size' => 500, // Reduzir para servidores limitados
    'pre_calculate_formulas' => false,
],

'cache' => [
    'driver' => 'memory', // Manter em memory para cPanel
    'batch' => [
        'memory_limit' => 30000, // Reduzir limite
    ],
],

'temporary_files' => [
    'local_path' => storage_path('framework/cache/laravel-excel'),
    'local_permissions' => [
        'dir'  => 0755,
        'file' => 0644,
    ],
],
```

## 🚨 Erros Frequentes

### Error: "Class Excel not found"
**Solução**: 
```bash
composer dump-autoload
php artisan config:cache
```

### Error: "Permission denied on temporary file"
**Solução**:
```bash
chmod 755 storage/framework/cache/laravel-excel/
```

### Error: "Memory exhausted"
**Solução**:
- Aumentar `memory_limit` para 256M
- Reduzir `chunk_size` para 500
- Processar exports em background jobs

### Error: "ZIP extension required"
**Solução**:
- Activar extensão ZIP no cPanel → PHP Extensions
- Contactar suporte do hosting se necessário
