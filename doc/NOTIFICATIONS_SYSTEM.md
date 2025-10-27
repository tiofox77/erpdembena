# Sistema de Notificações Toastr

## 📋 Visão Geral

O sistema utiliza **Toastr** como biblioteca de notificações, com um sistema centralizado de eventos Livewire para disparar toasts em toda a aplicação.

---

## 🏗️ Arquitetura

### **1. Layout Global**
📁 `resources/views/layouts/livewire.blade.php`

O layout principal contém os listeners globais para eventos de notificação:

```javascript
document.addEventListener('livewire:initialized', () => {
    // Listener principal para notificações
    Livewire.on('notify', (params) => {
        console.log('Notification event received:', params);

        // Verificar se toastr está definido
        if (typeof toastr === 'undefined') {
            console.error('Toastr is not defined!');
            alert(params.message || 'An error occurred');
            return;
        }

        // Configurar opções do toastr
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: params.type === 'error' ? 8000 : 5000,
            preventDuplicates: true,
            newestOnTop: true,
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };

        // Exibir notificação baseado no tipo
        if (params.type === 'success') {
            toastr.success(params.message, params.title || 'Success');
        } else if (params.type === 'error') {
            toastr.error(params.message, params.title || 'Error');
        } else if (params.type === 'warning') {
            toastr.warning(params.message, params.title || 'Warning');
        } else if (params.type === 'info') {
            toastr.info(params.message, params.title || 'Information');
        }
    });
});
```

---

## 🚀 Como Usar

### **Padrão Recomendado (Named Parameters)**

```php
$this->dispatch('notify', 
    type: 'success', 
    message: __('attendance.messages.saved_successfully')
);
```

### **Tipos de Notificação**

| Tipo | Uso | Duração | Cor |
|------|-----|---------|-----|
| `success` | Operações bem-sucedidas | 5000ms | Verde |
| `error` | Erros e falhas | 8000ms | Vermelho |
| `warning` | Avisos importantes | 5000ms | Amarelo |
| `info` | Informações gerais | 5000ms | Azul |

---

## 📝 Exemplos de Uso

### **1. Sucesso Simples**
```php
$this->dispatch('notify', 
    type: 'success', 
    message: __('messages.operation_successful')
);
```

### **2. Erro com Detalhes**
```php
try {
    // operação
} catch (\Exception $e) {
    $this->dispatch('notify', 
        type: 'error', 
        message: __('messages.operation_failed') . ': ' . $e->getMessage()
    );
}
```

### **3. Aviso (Warning)**
```php
$this->dispatch('notify', 
    type: 'warning', 
    message: __('attendance.messages.duplicate_found', ['count' => 5])
);
```

### **4. Informação**
```php
$this->dispatch('notify', 
    type: 'info', 
    message: __('messages.processing_please_wait')
);
```

---

## 🌍 Multi-Língua

### **Estrutura de Arquivos de Tradução**

```
resources/lang/
├── en/
│   ├── messages.php
│   └── attendance.php
└── pt/
    ├── messages.php
    └── attendance.php
```

### **Arquivo de Traduções Gerais**

**📁 `resources/lang/pt/messages.php`**
```php
return [
    'success' => 'Sucesso',
    'error' => 'Erro',
    'warning' => 'Aviso',
    'information' => 'Informação',
    
    'operation_successful' => 'Operação realizada com sucesso!',
    'operation_failed' => 'Erro ao realizar operação',
    'record_saved' => 'Registo guardado com sucesso.',
    'record_updated' => 'Registo atualizado com sucesso.',
    'record_deleted' => 'Registo eliminado com sucesso.',
    'validation_error' => 'Erro de validação',
    'processing_please_wait' => 'A processar, por favor aguarde...',
];
```

**📁 `resources/lang/en/messages.php`**
```php
return [
    'success' => 'Success',
    'error' => 'Error',
    'warning' => 'Warning',
    'information' => 'Information',
    
    'operation_successful' => 'Operation completed successfully!',
    'operation_failed' => 'Failed to perform operation',
    'record_saved' => 'Record saved successfully.',
    'record_updated' => 'Record updated successfully.',
    'record_deleted' => 'Record deleted successfully.',
    'validation_error' => 'Validation error',
    'processing_please_wait' => 'Processing, please wait...',
];
```

### **Arquivo de Traduções do Attendance**

**📁 `resources/lang/pt/attendance.php`**
```php
return [
    'messages' => [
        'saved_successfully' => 'Registo de presença criado com sucesso.',
        'updated_successfully' => 'Registo de presença atualizado com sucesso.',
        'deleted_successfully' => 'Registo de presença eliminado com sucesso.',
        'duplicate_found' => 'Já existe um registo de presença para este funcionário nesta data.',
        'import_completed' => 'Importação concluída: :created criado(s), :updated atualizado(s)',
        'import_failed' => 'Erro ao importar: :error',
        'employees_not_found' => 'Não foi possível importar :count funcionário(s): IDs não encontrados (:ids)',
        'conflicts_found' => 'Foram encontrados :count registos com horas duplicadas. Por favor, selecione as horas corretas.',
        'conflicts_resolved' => 'Conflitos resolvidos com sucesso! :message',
        'select_shift_first' => 'Por favor, selecione um turno primeiro.',
        'no_employees_selected' => 'Nenhum funcionário selecionado.',
        'batch_saved' => 'Registadas :count presenças com sucesso.',
    ],
];
```

**📁 `resources/lang/en/attendance.php`**
```php
return [
    'messages' => [
        'saved_successfully' => 'Attendance record created successfully.',
        'updated_successfully' => 'Attendance record updated successfully.',
        'deleted_successfully' => 'Attendance record deleted successfully.',
        'duplicate_found' => 'An attendance record already exists for this employee on this date.',
        'import_completed' => 'Import completed: :created created, :updated updated',
        'import_failed' => 'Import failed: :error',
        'employees_not_found' => 'Could not import :count employee(s): IDs not found (:ids)',
        'conflicts_found' => 'Found :count records with duplicate times. Please select the correct times.',
        'conflicts_resolved' => 'Conflicts resolved successfully! :message',
        'select_shift_first' => 'Please select a shift first.',
        'no_employees_selected' => 'No employees selected.',
        'batch_saved' => 'Successfully registered :count attendance records.',
    ],
];
```

---

## 💡 Boas Práticas

### ✅ **FAZER:**

1. **Sempre usar traduções**
   ```php
   // ✅ BOM
   $this->dispatch('notify', 
       type: 'success', 
       message: __('attendance.messages.saved_successfully')
   );
   
   // ❌ EVITAR
   $this->dispatch('notify', 
       type: 'success', 
       message: 'Registo salvo com sucesso.'
   );
   ```

2. **Usar try-catch para operações críticas**
   ```php
   try {
       $attendance->save();
       $this->dispatch('notify', 
           type: 'success', 
           message: __('attendance.messages.saved_successfully')
       );
   } catch (\Exception $e) {
       $this->dispatch('notify', 
           type: 'error', 
           message: __('messages.operation_failed') . ': ' . $e->getMessage()
       );
   }
   ```

3. **Disparar notificação ANTES de fechar modal**
   ```php
   // ✅ CORRETO
   $this->dispatch('notify', type: 'success', message: '...');
   $this->closeModal();
   
   // ❌ ERRADO
   $this->closeModal();
   $this->dispatch('notify', type: 'success', message: '...'); // Pode não aparecer
   ```

4. **Usar parâmetros nomeados para clareza**
   ```php
   // ✅ BOM (Legível)
   $this->dispatch('notify', 
       type: 'success', 
       message: __('attendance.messages.import_completed', [
           'created' => $created,
           'updated' => $updated
       ])
   );
   ```

### ❌ **EVITAR:**

1. **Strings hardcoded**
2. **Dispatch sem try-catch em operações de BD**
3. **Fechar modal antes de disparar notificação**
4. **Misturar `dispatch('toast')` e `dispatch('notify')`**

---

## 🔍 Troubleshooting

### **Problema: Notificações não aparecem**

**Soluções:**
1. Verificar se Toastr está carregado:
   ```javascript
   if (typeof toastr === 'undefined') {
       console.error('Toastr not loaded!');
   }
   ```

2. Verificar console do browser para erros

3. Confirmar que o layout `livewire.blade.php` está sendo usado

4. Verificar ordem: `dispatch()` ANTES de `closeModal()`

### **Problema: Mensagem em língua errada**

**Soluções:**
1. Verificar configuração em `config/app.php`:
   ```php
   'locale' => 'pt', // ou 'en'
   ```

2. Limpar cache de traduções:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   ```

3. Verificar se arquivo de tradução existe

---

## 📊 Estatísticas de Uso

### **Métodos que usam notificações no Attendance:**

| Método | Tipos Usados | Multi-Língua |
|--------|--------------|--------------|
| `save()` | success, error | ✅ |
| `delete()` | success, error | ✅ |
| `saveBatchAttendance()` | success, error | ✅ |
| `processConflictResolution()` | success, error | ✅ |
| `importFromExcel()` | success, error, warning | ✅ |

---

## 🎨 Personalização

### **Alterar tempo de exibição**

No listener em `livewire.blade.php`:
```javascript
toastr.options = {
    timeOut: params.type === 'error' ? 8000 : 5000, // Alterar aqui
    // ...
};
```

### **Alterar posição**

```javascript
toastr.options = {
    positionClass: 'toast-top-right', // Opções:
    // toast-top-left
    // toast-top-center
    // toast-bottom-left
    // toast-bottom-right
    // toast-bottom-center
};
```

### **Adicionar som**

```javascript
toastr.options = {
    // ... outras opções
    onShown: function() {
        // Tocar som
        new Audio('/sounds/notification.mp3').play();
    }
};
```

---

## 📚 Referências

- [Toastr Documentation](https://github.com/CodeSeven/toastr)
- [Livewire Events](https://livewire.laravel.com/docs/events)
- [Laravel Localization](https://laravel.com/docs/localization)

---

**Última atualização:** 2025-10-08  
**Versão:** 1.0
