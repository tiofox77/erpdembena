#!/bin/bash

# Script para encontrar Composer no servidor
echo "=== PROCURANDO COMPOSER NO SERVIDOR ==="

# Verificar caminhos comuns do composer
echo "🔍 Verificando caminhos comuns..."
which composer 2>/dev/null || echo "composer não encontrado no PATH"
which composer.phar 2>/dev/null || echo "composer.phar não encontrado no PATH"

# Verificar em diretórios comuns
echo "📂 Verificando diretórios..."
ls -la /usr/local/bin/composer* 2>/dev/null || echo "Não encontrado em /usr/local/bin/"
ls -la /usr/bin/composer* 2>/dev/null || echo "Não encontrado em /usr/bin/"
ls -la ~/composer* 2>/dev/null || echo "Não encontrado no home"
ls -la ./composer* 2>/dev/null || echo "Não encontrado no diretório atual"

# Verificar versão PHP
echo "🐘 Versão PHP:"
php -v

# Tentar usar php com composer.phar se existir
if [ -f "composer.phar" ]; then
    echo "✅ composer.phar encontrado! Use: php composer.phar"
elif [ -f "/usr/local/bin/composer.phar" ]; then
    echo "✅ composer.phar encontrado em /usr/local/bin/! Use: php /usr/local/bin/composer.phar"
else
    echo "❌ Composer não disponível. Usar solução manual."
fi
