#!/bin/bash

# Script de Deploy para Produção - Laravel Excel Fix
# Execute este script no servidor de produção via cPanel Terminal

echo "=== DEPLOY PRODUÇÃO - LARAVEL EXCEL FIX ==="

# 1. Instalar dependências de produção
echo "📦 Instalando dependências..."
composer install --no-dev --optimize-autoloader --no-interaction

# 2. Limpar todos os caches
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 3. Publicar configuração Excel (se necessário)
echo "📋 Publicando configuração Excel..."
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config --force

# 4. Recriar cache de configuração
echo "⚡ Recriando cache..."
php artisan config:cache

# 5. Verificar se Excel facade está disponível
echo "🔍 Verificando Excel facade..."
php artisan tinker --execute="use Maatwebsite\Excel\Facades\Excel; echo 'Excel facade OK';"

echo "✅ Deploy concluído!"
