# Como Adicionar Menu "Relatórios de Payroll"

## 📋 **O Que Foi Criado:**

✅ **Componente Livewire:** `app/Livewire/HR/PayrollReports.php`  
✅ **View:** `resources/views/livewire/hr/payroll-reports.blade.php`  
✅ **Service:** `app/Services/PayrollBatchReportService.php` (já existente)  

---

## 🔧 **Passo 1: Adicionar Rota**

### **Arquivo:** `routes/web.php`

Adicione dentro do grupo de rotas de HR (se houver) ou após as rotas de Payroll:

```php
// Relatórios de Payroll
Route::middleware(['auth'])->group(function () {
    Route::get('/hr/payroll-reports', \App\Livewire\HR\PayrollReports::class)
        ->name('hr.payroll-reports');
});
```

---

## 🎯 **Passo 2: Adicionar ao Menu/Sidebar**

### **Opção A: Sidebar com Bootstrap (sb-admin style)**

Se usar `resources/views/components/sidebar.blade.php`:

```blade
<!-- Seção HR/Payroll -->
<a class="nav-link collapsed" href="#" data-bs-toggle="collapse" 
   data-bs-target="#collapsePayroll" aria-expanded="false">
    <div class="sb-nav-link-icon"><i class="fas fa-money-bill-wave"></i></div>
    Folha de Pagamento
    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
</a>
<div class="collapse" id="collapsePayroll" data-bs-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        <a class="nav-link" href="{{ route('hr.payroll') }}">
            <div class="sb-nav-link-icon"><i class="fas fa-calculator"></i></div>
            Processar Payroll
        </a>
        <a class="nav-link" href="{{ route('hr.payroll-batch') }}">
            <div class="sb-nav-link-icon"><i class="fas fa-layer-group"></i></div>
            Payroll Batch
        </a>
        <a class="nav-link" href="{{ route('hr.payroll-reports') }}">
            <div class="sb-nav-link-icon"><i class="fas fa-file-pdf"></i></div>
            Relatórios de Payroll
        </a>
    </nav>
</div>
```

---

### **Opção B: Menu Tailwind (se usar Tailwind)**

```blade
<!-- Menu HR -->
<div x-data="{ open: false }">
    <button @click="open = !open" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100">
        <i class="fas fa-money-bill-wave mr-3"></i>
        <span>Folha de Pagamento</span>
        <i class="fas fa-chevron-down ml-auto"></i>
    </button>
    
    <div x-show="open" class="ml-6 mt-2 space-y-1">
        <a href="{{ route('hr.payroll') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
            <i class="fas fa-calculator mr-2"></i>
            Processar Payroll
        </a>
        <a href="{{ route('hr.payroll-batch') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
            <i class="fas fa-layer-group mr-2"></i>
            Payroll Batch
        </a>
        <a href="{{ route('hr.payroll-reports') }}" class="block px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
            <i class="fas fa-file-pdf mr-2"></i>
            Relatórios de Payroll
        </a>
    </div>
</div>
```

---

### **Opção C: Menu Dropdown Laravel Breeze/Jetstream**

```blade
<x-nav-dropdown align="left">
    <x-slot name="trigger">
        <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
            <div>Folha de Pagamento</div>
            <div class="ml-1">
                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
        </button>
    </x-slot>

    <x-slot name="content">
        <x-dropdown-link :href="route('hr.payroll')">
            Processar Payroll
        </x-dropdown-link>
        <x-dropdown-link :href="route('hr.payroll-batch')">
            Payroll Batch
        </x-dropdown-link>
        <x-dropdown-link :href="route('hr.payroll-reports')">
            Relatórios de Payroll
        </x-dropdown-link>
    </x-slot>
</x-nav-dropdown>
```

---

## 🎨 **Ícones Recomendados:**

| Item | Ícone FontAwesome |
|------|-------------------|
| **Folha de Pagamento** | `fas fa-money-bill-wave` |
| **Processar Payroll** | `fas fa-calculator` |
| **Payroll Batch** | `fas fa-layer-group` |
| **Relatórios** | `fas fa-file-pdf` |

---

## 🔒 **Passo 3: Adicionar Permissões (Opcional)**

Se usar Spatie Permission:

```php
// Em database/seeders/RolePermissionSeeder.php ou similar

$permissions = [
    'hr.payroll-reports.view',
    'hr.payroll-reports.generate',
];

foreach ($permissions as $permission) {
    Permission::create(['name' => $permission]);
}

// Atribuir ao role HR Manager
$hrManager = Role::findByName('HR Manager');
$hrManager->givePermissionTo($permissions);
```

**Middleware na rota:**
```php
Route::get('/hr/payroll-reports', \App\Livewire\HR\PayrollReports::class)
    ->name('hr.payroll-reports')
    ->middleware(['auth', 'permission:hr.payroll-reports.view']);
```

---

## 📊 **Funcionalidades da Página:**

### **Filtros Disponíveis:**
- ✅ **Pesquisar** por nome do batch
- ✅ **Filtrar** por período de pagamento
- ✅ **Filtrar** por departamento
- ✅ **Status** (apenas completed por padrão)

### **Tabela de Resultados:**

| Coluna | Descrição |
|--------|-----------|
| **Batch** | Nome e data do batch |
| **Período** | Período de pagamento |
| **Departamento** | Departamento ou "Todos" |
| **Status** | Badge com status (Completed) |
| **Funcionários** | Total de funcionários |
| **Total Líquido** | Valor líquido total |
| **Ação** | Botão "Gerar PDF" |

### **Ação Principal:**
- 🔴 **Botão "Gerar PDF"** - Gera e faz download do relatório

---

## 🧪 **Como Testar:**

1. **Adicionar Rota:**
   ```bash
   php artisan route:list | grep payroll-reports
   ```

2. **Acessar URL:**
   ```
   http://seu-dominio.com/hr/payroll-reports
   ```

3. **Verificar:**
   - ✅ Página carrega
   - ✅ Filtros funcionam
   - ✅ Batches são listados
   - ✅ Botão "Gerar PDF" funciona

---

## 📝 **Traduções (Opcional):**

### **Arquivo:** `resources/lang/pt/payroll.php`

```php
'reports_page_title' => 'Relatórios de Folha de Pagamento',
'reports_page_description' => 'Gere relatórios consolidados de batches processados',
'generate_pdf' => 'Gerar PDF',
'no_batches_found' => 'Nenhum Batch Encontrado',
'process_batches_first' => 'Processe batches primeiro na seção de Payroll Batch',
```

### **Arquivo:** `resources/lang/en/payroll.php`

```php
'reports_page_title' => 'Payroll Reports',
'reports_page_description' => 'Generate consolidated reports from processed batches',
'generate_pdf' => 'Generate PDF',
'no_batches_found' => 'No Batches Found',
'process_batches_first' => 'Process batches first in the Payroll Batch section',
```

---

## 🎯 **Estrutura do Menu Recomendada:**

```
📁 Dashboard
📁 Recursos Humanos
   ├─ 👥 Funcionários
   ├─ 📅 Attendance
   ├─ 💰 Folha de Pagamento
   │  ├─ 🧮 Processar Payroll
   │  ├─ 📦 Payroll Batch
   │  └─ 📄 Relatórios de Payroll ✨ NOVO
   ├─ 🏖️ Férias e Licenças
   └─ ⚙️ Configurações RH
```

---

## ✅ **Checklist de Implementação:**

- [ ] Criar rota em `routes/web.php`
- [ ] Adicionar item no menu/sidebar
- [ ] Testar acesso à página
- [ ] Verificar listagem de batches
- [ ] Testar geração de PDF
- [ ] Adicionar permissões (se aplicável)
- [ ] Adicionar traduções
- [ ] Documentar para equipe

---

## 🔗 **Links Relacionados:**

- **Componente:** `app/Livewire/HR/PayrollReports.php`
- **View:** `resources/views/livewire/hr/payroll-reports.blade.php`
- **Service:** `app/Services/PayrollBatchReportService.php`
- **PDF Template:** `resources/views/reports/payroll-batch-summary.blade.php`

---

**Última atualização:** 2025-10-12  
**Status:** ✅ Pronto para Implementação

