# FIX PRODUÇÃO - Laravel Excel no cPanel

## 🚨 PROBLEMA
Erro: `Class "Maatwebsite\Excel\Facades\Excel" not found` no servidor de produção após GitHub update.

## ✅ SOLUÇÕES (Execute na ordem)

### OPÇÃO 1: Via cPanel Terminal
```bash
# 1. Navegar para o diretório do projeto
cd public_html

# 2. Instalar dependências 
composer install --no-dev --optimize-autoloader

# 3. Limpar caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 4. Publicar configuração
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config

# 5. Recriar cache
php artisan config:cache

# 6. Testar
php artisan tinker --execute="use Maatwebsite\Excel\Facades\Excel; echo 'OK';"
```

### OPÇÃO 2: Via cPanel File Manager
Se não tem terminal:

1. **Verificar se existe:** `/vendor/maatwebsite/excel/`
2. **Se não existir:** Fazer upload do directório `vendor/` completo do ambiente local
3. **Apagar ficheiros cache:** `/bootstrap/cache/config.php` e `/bootstrap/cache/services.php`
4. **Copiar ficheiro:** `config/excel.php` do local para produção

### OPÇÃO 3: Fix Manual no Código
Adicionar ao início do método `exportToExcel()`:

```php
public function exportToExcel()
{
    // Verificar se classe existe
    if (!class_exists('Maatwebsite\Excel\Facades\Excel')) {
        session()->flash('error', 'Laravel Excel não instalado no servidor');
        return;
    }
    
    try {
        $fileName = 'funcionarios_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new EmployeesExport, $fileName);
    } catch (\Exception $e) {
        session()->flash('error', __('messages.export_failed') . ': ' . $e->getMessage());
    }
}
```

## 🔍 DIAGNÓSTICO
Execute para verificar:
```bash
# Ver se vendor existe
ls -la vendor/maatwebsite/

# Ver dependências instaladas
composer show | grep excel

# Ver caches
ls -la bootstrap/cache/
```

## ⚠️ NOTAS IMPORTANTES
- Composer deve ser executado no servidor de produção após cada GitHub update
- Nunca commitar o directório `vendor/` no GitHub
- Sempre limpar caches após mudanças de configuração
