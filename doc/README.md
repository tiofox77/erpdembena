# Documentação do Sistema ERPDEMBENA

Bem-vindo à documentação do sistema ERP DEMBENA!

## 📚 Índice de Documentação

### 🔔 [Sistema de Notificações](./NOTIFICATIONS_SYSTEM.md)
Guia completo sobre como usar o sistema de notificações Toastr no projeto, incluindo:
- Como disparar notificações
- Configuração multi-língua (PT/EN)
- Boas práticas e exemplos
- Troubleshooting

## 🌍 Multi-Língua

O sistema suporta múltiplos idiomas através do sistema de localização do Laravel:

### Idiomas Disponíveis:
- 🇵🇹 Português (pt)
- 🇬🇧 English (en)

### Arquivos de Tradução:

```
resources/lang/
├── pt/
│   ├── messages.php      # Mensagens gerais do sistema
│   └── attendance.php    # Traduções específicas de attendance
└── en/
    ├── messages.php      # General system messages
    └── attendance.php    # Attendance specific translations
```

### Como Usar Traduções:

```php
// Mensagem simples
__('attendance.messages.saved_successfully')

// Mensagem com parâmetros
__('attendance.messages.batch_saved', ['count' => 5])

// Mensagem com múltiplos parâmetros
__('attendance.messages.employees_not_found', [
    'count' => 3,
    'ids' => '28, 45, 67'
])
```

### Alterar Idioma do Sistema:

**Arquivo:** `config/app.php`
```php
'locale' => 'pt', // ou 'en'
'fallback_locale' => 'en',
```

## 📖 Convenções de Código

### Notificações:
- Sempre use o evento `notify` (não `toast`)
- Use parâmetros nomeados: `type:`, `message:`
- Sempre use traduções multi-língua
- Use try-catch para operações de BD

### Traduções:
- Adicione novas traduções em **PT e EN**
- Use placeholders para valores dinâmicos: `:count`, `:error`, etc.
- Agrupe traduções por módulo

## 🛠️ Comandos Úteis

### Limpar Cache:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Verificar Traduções:
```bash
# Ver traduções de attendance em português
php artisan tinker
>>> __('attendance.messages.saved_successfully', [], 'pt')

# Ver traduções de attendance em inglês  
>>> __('attendance.messages.saved_successfully', [], 'en')
```

## 📝 Contribuindo

Ao adicionar novas funcionalidades:

1. ✅ Adicione traduções em PT e EN
2. ✅ Use o sistema de notificações padrão
3. ✅ Documente mudanças significativas
4. ✅ Teste em ambos os idiomas

## 🔗 Links Úteis

- [Laravel Localization](https://laravel.com/docs/localization)
- [Livewire Events](https://livewire.laravel.com/docs/events)
- [Toastr Documentation](https://github.com/CodeSeven/toastr)

---

**Última atualização:** 2025-10-08  
**Versão do Sistema:** 1.0
