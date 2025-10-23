#!/bin/bash

# Script para corrigir erro após atualização no cPanel
# Execute este script via SSH no servidor

echo "======================================"
echo "Corrigindo erro de atualização..."
echo "======================================"

# Navegar para o diretório do projeto
cd /home/softec/dembenaerp.softec.vip

# Limpar todos os caches
echo "1. Limpando caches..."
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
php artisan optimize:clear

# Limpar cache de sessões
echo "2. Limpando cache de sessões..."
php artisan session:clear 2>/dev/null || echo "Comando session:clear não disponível, continuando..."

# Recompilar configurações
echo "3. Recompilando configurações..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Limpar cache do Livewire
echo "4. Limpando cache do Livewire..."
rm -rf storage/framework/cache/livewire-tmp/* 2>/dev/null
rm -rf storage/framework/sessions/* 2>/dev/null
rm -rf storage/framework/views/* 2>/dev/null

# Ajustar permissões
echo "5. Ajustando permissões..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Otimizar autoloader
echo "6. Otimizando autoloader..."
composer dump-autoload --optimize 2>/dev/null || echo "Composer não disponível via linha de comando"

echo "======================================"
echo "Correção concluída!"
echo "======================================"
echo ""
echo "Se o erro persistir, execute também:"
echo "php artisan optimize"
echo ""
echo "Depois acesse: https://dembenaerp.softec.vip/settings/system"
