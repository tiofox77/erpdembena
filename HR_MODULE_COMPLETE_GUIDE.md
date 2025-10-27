# 🏢 GUIA COMPLETO DO MÓDULO DE RECURSOS HUMANOS (HR)
**Sistema ERP - Laravel 11 + Livewire 3 + PHP 8.3**

---

## 📋 **ÍNDICE**
1. [Visão Geral da Arquitetura](#visão-geral)
2. [Estrutura de Pastas](#estrutura)
3. [Modelos de Dados](#modelos)
4. [Componentes Livewire](#componentes)
5. [Views e Interface](#views)
6. [Fluxo de Dados](#fluxo)
7. [Padrões e Convenções](#padrões)
8. [Internacionalização](#i18n)
9. [Validações](#validações)
10. [Guia para Nova IA](#guia-ai)

---

## 🏗️ **VISÃO GERAL DA ARQUITETURA** {#visão-geral}

### **Paradigma Técnico:**
- **Framework:** Laravel 11.x
- **Frontend:** Livewire 3.x (componentes reativos)
- **PHP:** 8.3 com `declare(strict_types=1);`
- **Estilo:** PSR-12 + Laravel Pint
- **Base de Dados:** MySQL/MariaDB
- **UI:** TailwindCSS + Alpine.js
- **Padrão:** MVC com componentes Livewire

### **Princípios de Design:**
1. **Separation of Concerns** - Cada classe tem responsabilidade específica
2. **DRY (Don't Repeat Yourself)** - Reutilização de código e componentes
3. **SOLID Principles** - Classes extensíveis e manuteníveis
4. **Component-Based Architecture** - Modularização com Livewire
5. **Multilingual Support** - Internacionalização completa (PT/EN)

---

## 📁 **ESTRUTURA DE PASTAS** {#estrutura}

```
app/
├── Models/HR/                          # Modelos de dados
│   ├── Employee.php                    # Modelo central de funcionários
│   ├── Department.php                  # Departamentos
│   ├── Attendance.php                  # Presenças
│   ├── Payroll.php                     # Folha de pagamento
│   ├── SalaryAdvance.php               # Adiantamentos salariais
│   └── [25+ outros modelos...]
│
├── Livewire/HR/                        # Componentes Livewire
│   ├── Employees.php                   # Gestão de funcionários
│   ├── Attendance.php                  # Gestão de presenças
│   ├── Payroll.php                     # Processamento de folha
│   └── [15+ outros componentes...]
│
resources/
├── views/livewire/hr/                  # Views Blade (40+ arquivos)
├── lang/en/livewire/hr/               # Traduções inglês
└── lang/pt-BR/livewire/hr/            # Traduções português
```

---

## 🎯 **MODELOS DE DADOS PRINCIPAIS** {#modelos}

### **1. Employee.php - Modelo Central**
```php
<?php

declare(strict_types=1);

namespace App\Models\HR;

class Employee extends Model
{
    // Relacionamentos principais
    public function department(): BelongsTo;
    public function position(): BelongsTo;
    public function attendances(): HasMany;
    public function payrolls(): HasMany;
    public function salaryAdvances(): HasMany;
    public function shiftAssignments(): HasMany;
    
    // Campos principais
    protected $fillable = [
        'full_name', 'email', 'phone', 'id_card',
        'department_id', 'position_id', 'hire_date',
        'basic_salary', 'status'
    ];
    
    // Status possíveis
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_TERMINATED = 'terminated';
}
```

### **2. Payroll.php - Folha de Pagamento**
```php
class Payroll extends Model
{
    protected $fillable = [
        'employee_id', 'payroll_period_id', 'basic_salary',
        'gross_salary', 'net_salary', 'total_deductions',
        'payment_method', 'status'
    ];
    
    // Status possíveis
    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';
    
    // Relacionamentos
    public function employee(): BelongsTo;
    public function payrollItems(): HasMany;
}
```

### **3. Relacionamentos Principais**
```
Employee (1) ←→ (N) Payroll
Employee (1) ←→ (N) Attendance
Employee (1) ←→ (N) SalaryAdvance
Employee (N) ←→ (1) Department
Payroll (1) ←→ (N) PayrollItem
```

---

## ⚛️ **COMPONENTES LIVEWIRE** {#componentes}

### **1. Estrutura Base dos Componentes**
```php
<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

class ExampleComponent extends Component
{
    use WithPagination;
    
    // Propriedades de controlo da UI
    public bool $showModal = false;
    public bool $isEditing = false;
    
    // Propriedades de filtros
    public string $search = '';
    public string $sortBy = 'created_at';
    public int $perPage = 15;
    
    // Propriedades do modelo
    #[Validate('required')]
    public ?string $name = null;
    
    public function render(): View
    {
        return view('livewire.hr.example', [
            'items' => $this->getItems()
        ]);
    }
    
    public function save(): void
    {
        $this->validate();
        // Lógica de salvamento
        $this->closeModal();
    }
    
    protected function getItems()
    {
        return Model::query()
            ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy($this->sortBy, 'desc')
            ->paginate($this->perPage);
    }
}
```

---

## 🎨 **VIEWS E INTERFACE** {#views}

### **1. Estrutura Padrão das Views**
```blade
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <h1>{{ __('hr.module.title') }}</h1>
        <button wire:click="openModal" class="btn-primary">
            {{ __('messages.add_new') }}
        </button>
    </div>
    
    {{-- Filtros --}}
    <div class="bg-white rounded-lg shadow p-4">
        <input wire:model.live="search" 
               placeholder="{{ __('messages.search') }}"
               class="form-input">
    </div>
    
    {{-- Tabela --}}
    <div class="bg-white rounded-lg shadow">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th wire:click="sortBy('field')">
                        {{ __('hr.field') }}
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td>{{ $item->field }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="text-center">{{ __('messages.no_data') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $items->links() }}
    </div>
    
    {{-- Modais --}}
    @include('livewire.hr.modals.create-edit')
</div>
```

---

## 🔄 **FLUXO DE DADOS** {#fluxo}

### **1. Fluxo de Processamento de Folha**
```
1. Utilizador → Pesquisa Funcionário
2. selectEmployee() → Carrega dados completos
3. calculateComponents() → Calcula salário, presenças, deduções
4. Modal Processamento → Exibe dados calculados
5. save() → Cria Payroll + PayrollItems
6. Notificação → Confirma sucesso
```

### **2. Fluxo de Presenças em Lote**
```
1. Calendário → Seleciona data
2. Seleciona turno → Lista funcionários
3. Marca presenças → batchSave()
4. Cria registos → Calcula horas extra
5. Atualiza interface → Confirma salvamento
```

---

## 📏 **PADRÕES E CONVENÇÕES** {#padrões}

### **1. Nomenclatura**
```php
// Classes - PascalCase
class EmployeeManagement extends Component

// Métodos - camelCase
public function calculateSalary(): float

// Propriedades - camelCase
public string $employeeName = '';

// Constantes - SCREAMING_SNAKE_CASE
const STATUS_ACTIVE = 'active';

// Views - kebab-case
employee-management.blade.php

// Translation Keys - snake_case
'employee_name' => 'Nome do Funcionário'
```

### **2. Ordem de Propriedades**
```php
// 1. Propriedades UI
public bool $showModal = false;

// 2. Filtros e paginação
public string $search = '';
public int $perPage = 15;

// 3. Modelo principal
public ?int $selectedId = null;

// 4. Campos do formulário
#[Validate('required')]
public string $name = '';
```

---

## 🌐 **INTERNACIONALIZAÇÃO** {#i18n}

### **1. Estrutura de Traduções**
```
resources/lang/
├── en/livewire/hr/
│   ├── payroll.php
│   └── employees.php
└── pt-BR/livewire/hr/
    ├── payroll.php
    └── employees.php
```

### **2. Uso nas Views**
```blade
{{-- Básico --}}
<h1>{{ __('hr.payroll.title') }}</h1>

{{-- Com parâmetros --}}
{{ __('hr.payroll.employee_salary', ['name' => $name]) }}

{{-- Fallback --}}
{{ __('hr.field') ?: 'Campo' }}
```

---

## ✅ **VALIDAÇÕES E REGRAS** {#validações}

### **1. Validação Livewire**
```php
#[Validate('required|exists:employees,id')]
public ?int $employee_id = null;

#[Validate('required|numeric|min:0')]
public float $basic_salary = 0.0;

// Regras personalizadas
protected function rules(): array
{
    return [
        'employee_id' => [
            'required',
            function ($attribute, $value, $fail) {
                if ($this->hasExistingPayroll($value)) {
                    $fail(__('hr.payroll.duplicate_error'));
                }
            }
        ]
    ];
}
```

---

## 🤖 **GUIA PARA NOVA IA** {#guia-ai}

### **1. Passos Essenciais**
1. **Analise estrutura existente** - Veja pastas Models/HR e Livewire/HR
2. **Entenda relacionamentos** - Employee é modelo central
3. **Siga padrões** - Use convenções estabelecidas
4. **Mantenha traduções** - Sempre PT/EN sincronizados
5. **Teste funcionalidades** - Valide fluxos principais

### **2. Template Novo Módulo**
```php
<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use Livewire\Component;
use Livewire\WithPagination;

class NewModule extends Component
{
    use WithPagination;
    
    public bool $showModal = false;
    public string $search = '';
    
    public function render()
    {
        return view('livewire.hr.new-module');
    }
    
    public function save(): void
    {
        // Implementar lógica
    }
}
```

### **3. Comandos Importantes**
```bash
# Novo modelo
php artisan make:model HR/ModelName -m

# Novo componente
php artisan make:livewire HR/ComponentName

# Executar migrations
php artisan migrate

# Limpar cache
php artisan cache:clear
```

---

## 📊 **RESUMO ESTATÍSTICO**

- **25+ Modelos HR** - Employee, Payroll, Attendance, etc.
- **15+ Componentes Livewire** - Gestão completa de RH
- **40+ Views Blade** - Interface moderna e responsiva
- **2 Idiomas** - Português e Inglês totalmente sincronizados
- **100% Tipado** - PHP 8.3 strict types
- **PSR-12 Compliant** - Código limpo e padronizado

**Este guia fornece uma base sólida para qualquer IA continuar o desenvolvimento do módulo de RH seguindo os mesmos padrões e convenções estabelecidos.**
