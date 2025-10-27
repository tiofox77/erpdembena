═══════════════════════════════════════════════════════════════
  SISTEMA DE LIMPEZA - ERP DEMBENA
═══════════════════════════════════════════════════════════════

📋 O QUE FAZ:
  Remove arquivos temporários, scripts de debug e documentação
  de desenvolvimento que estão poluindo o diretório raiz.

🎯 COMO USAR NO CPANEL:

  1. Acesse via navegador:
     https://seudominio.com/cleanup_system.php

  2. O sistema mostrará:
     • Quantos arquivos foram encontrados
     • Lista completa dos arquivos
     • Estatísticas

  3. Clique em "Confirmar Remoção"
  
  4. Confirme a ação
  
  5. Arquivos serão removidos permanentemente

🔒 SEGURANÇA:

  • O script NÃO remove arquivos importantes (.env, composer.json, etc)
  • Lista de 50+ arquivos protegidos
  • Modo de pré-visualização disponível
  • Confirmação obrigatória antes de deletar

📁 TIPOS DE ARQUIVOS REMOVIDOS:

  ✓ Scripts de debug (debug_*.php)
  ✓ Scripts de teste (test_*.php)
  ✓ Documentação temporária (*.md)
  ✓ Arquivos de análise (analyze_*.php)
  ✓ Scripts de correção antigos (fix_*.php)
  ✓ Backups de permissões (backup_permissions_*.json)
  ✓ Logs temporários (*.txt)

⚠️ IMPORTANTE:

  • Esta ação é IRREVERSÍVEL
  • Sempre faça backup antes
  • Verifique a lista antes de confirmar
  • Após limpeza, pode deletar o próprio cleanup_system.php

🗑️ REMOVER O SCRIPT APÓS USO:

  Depois de limpar, você pode remover:
  • cleanup_system.php
  • .htaccess_cleanup
  • CLEANUP_README.txt

💡 DICA:

  Execute periodicamente para manter o sistema organizado.
  Especialmente útil após sessões de debug/desenvolvimento.

═══════════════════════════════════════════════════════════════
