# Sistema de Notificações

## 1. Estrutura de Notificações Toast

O ERP DEMBENA utiliza um sistema de notificações toast para fornecer feedback ao usuário sobre ações realizadas. Essas notificações são implementadas usando o Livewire para disparar eventos e JavaScript/CSS para exibi-las.

### 1.1 Dispatcher de Notificações

```php
// Em qualquer componente Livewire
public function save()
{
    try {
        // Lógica de salvamento
        $this->dispatch('notify', type: 'success', message: 'Registro salvo com sucesso!');
    } catch (\Exception $e) {
        $this->dispatch('notify', type: 'error', message: 'Erro ao salvar: ' . $e->getMessage());
    }
}
```

### 1.2 Listener JavaScript (app.js)

```javascript
// Em resources/js/app.js
document.addEventListener('livewire:initialized', () => {
    Livewire.on('notify', (params) => {
        toastr[params.type](params.message);
    });
});
```

### 1.3 Configuração do Toastr

```javascript
// Em resources/js/app.js ou em um arquivo separado
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 5000,
    extendedTimeOut: 1000
};
```
