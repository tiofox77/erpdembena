# Estratégia de Deploy - Servidor sem Composer

## 🏗️ PROBLEMA
- Servidor de produção sem composer instalado
- GitHub update não instala dependências automaticamente
- Erro: `Class "Maatwebsite\Excel\Facades\Excel" not found`

## ✅ SOLUÇÕES

### OPÇÃO 1: Incluir vendor/ no Git (Para servidores sem composer)

**Vantagens:**
- GitHub update automaticamente envia todas as dependências
- Não precisa de composer no servidor
- Deploy mais simples e direto

**Desvantagens:**
- Repositório fica muito grande
- Commits ficam poluídos com arquivos vendor/
- Pode causar conflitos entre ambientes diferentes

**Implementação:**
```bash
# 1. Comentar linha vendor no .gitignore (já feito)
# /vendor  # Comentado para incluir dependências no Git

# 2. Adicionar vendor ao git
git add vendor/
git commit -m "Include vendor dependencies for production deploy"
git push origin main

# 3. No servidor após GitHub update:
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### OPÇÃO 2: Build Pipeline (Recomendado profissionalmente)

**Implementação:**
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Install dependencies
        run: composer install --no-dev --optimize-autoloader
      - name: Deploy to server
        # Upload com rsync ou FTP
```

### OPÇÃO 3: Hybrid - Só Dependências Críticas

```bash
# Incluir apenas dependências essenciais
git add vendor/maatwebsite/
git add vendor/phpoffice/
git add vendor/autoload.php
git add vendor/composer/
```

## 🎯 RECOMENDAÇÃO PARA SEU CASO

**Use OPÇÃO 1** temporariamente até configurar composer no servidor:

1. ✅ `.gitignore` já atualizado
2. Commit vendor/
3. Push para GitHub  
4. Deploy automático funcionará

**Depois migre para OPÇÃO 2** quando possível.
