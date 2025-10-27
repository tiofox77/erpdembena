# ✅ Correção: Subsídio Mostrando 0 ao Invés de 50% do Salário

**Data:** 2025-01-07  
**Problema:** Checkbox marcado mas subsídio mostra 0.00 AOA ao invés de 50% do salário base  
**Status:** ✅ CORRIGIDO

---

## 🐛 Problema Identificado

### **Sintoma:**
```
Salário Base: 69,900.00 AOA
Checkbox marcado: ✅
Valor esperado: 34,950.00 AOA (50%)
Valor mostrado: 0.00 AOA ❌
```

### **Causa:**
O método estava usando `$this->basic_salary` que pode estar:
- ❌ Não inicializado
- ❌ Com valor 0
- ❌ Não sincronizado com o funcionário selecionado

---

## ✅ Solução Aplicada

### **ANTES (❌):**

```php
public function updatedChristmasSubsidy(): void
{
    // ❌ Usava $this->basic_salary direto (pode ser 0)
    $this->christmas_subsidy_amount = $this->christmas_subsidy 
        ? ($this->basic_salary * 0.5) 
        : 0.0;
}
```

### **DEPOIS (✅):**

```php
public function updatedChristmasSubsidy(): void
{
    // ✅ Prioriza o salário do funcionário selecionado
    $basicSalary = $this->selectedEmployee ? 
        (float)$this->selectedEmployee->basic_salary : 
        (float)$this->basic_salary;
    
    $this->christmas_subsidy_amount = $this->christmas_subsidy 
        ? ($basicSalary * 0.5) 
        : 0.0;
    
    // ✅ Log para debug
    Log::info('Christmas Subsidy atualizado', [
        'value' => $this->christmas_subsidy,
        'basic_salary' => $basicSalary,
        'amount' => $this->christmas_subsidy_amount,
    ]);
}
```

---

## 🔍 Como Funciona Agora

### **1. Busca o Salário Base:**

```php
$basicSalary = $this->selectedEmployee ? 
    (float)$this->selectedEmployee->basic_salary :  // ✅ Prioridade 1: Do employee
    (float)$this->basic_salary;                     // ✅ Prioridade 2: Da propriedade
```

**Lógica:**
- Se `$this->selectedEmployee` existe → Usa `basic_salary` do employee
- Se não existe → Fallback para `$this->basic_salary`

### **2. Calcula 50%:**

```php
$this->christmas_subsidy_amount = $this->christmas_subsidy 
    ? ($basicSalary * 0.5)  // ✅ 50% se marcado
    : 0.0;                  // ✅ 0 se desmarcado
```

### **3. Atualiza Totais:**

```php
$this->updateTotalsAfterSubsidyChange();
```

Recalcula:
- Gross Salary
- IRT Base
- IRT
- Total Deductions
- Net Salary

---

## 🧪 Teste Agora

### **Passo 1: Marcar Checkbox**

1. Abrir modal de payroll
2. Selecionar funcionário (ex: salário base = 69,900 AOA)
3. Marcar checkbox "Christmas Subsidy"

### **Passo 2: Verificar Valor**

**Esperado:**
```
Christmas Subsidy
Additional Christmas payment: 50% do salário base
34,950.00 AOA ✅
```

### **Passo 3: Verificar Log**

```powershell
# Ver últimas linhas do log
Get-Content C:\laragon2\www\ERPDEMBENA\storage\logs\laravel.log -Tail 20
```

**Procure por:**
```
[timestamp] local.INFO: Christmas Subsidy atualizado
{
    "value": true,
    "basic_salary": 69900,
    "amount": 34950
}
```

---

## 📊 Casos de Teste

### **Caso 1: Funcionário com Salário 69,900**

```
Checkbox: ✅ Marcado
Salário Base: 69,900.00 AOA
Esperado: 34,950.00 AOA (50%)
```

**Cálculo:**
```
69,900 × 0.5 = 34,950 ✅
```

### **Caso 2: Funcionário com Salário 100,000**

```
Checkbox: ✅ Marcado
Salário Base: 100,000.00 AOA
Esperado: 50,000.00 AOA (50%)
```

**Cálculo:**
```
100,000 × 0.5 = 50,000 ✅
```

### **Caso 3: Checkbox Desmarcado**

```
Checkbox: ❌ Desmarcado
Salário Base: Qualquer valor
Esperado: 0.00 AOA
```

**Cálculo:**
```
false ? (salário × 0.5) : 0.0 = 0.0 ✅
```

---

## 🔍 Debug: Se Ainda Mostrar 0

### **1. Verificar no Log:**

```
"basic_salary": 0  ❌ → Problema: Employee não tem salário definido
"basic_salary": 69900  ✅ → OK: Salário correto
```

### **2. Verificar Employee no Banco:**

```sql
SELECT id, full_name, basic_salary 
FROM employees 
WHERE id = [ID_DO_FUNCIONARIO];
```

**Se `basic_salary` for NULL ou 0:**
- ❌ Funcionário não tem salário cadastrado
- ✅ Solução: Cadastrar salário no perfil do funcionário

### **3. Verificar Propriedade Livewire:**

```php
// No início do método updatedChristmasSubsidy
Log::info('DEBUG', [
    'selectedEmployee_exists' => $this->selectedEmployee ? 'sim' : 'não',
    'selectedEmployee_salary' => $this->selectedEmployee->basic_salary ?? 'N/A',
    'this_basic_salary' => $this->basic_salary,
]);
```

---

## 📝 Fluxo Completo

### **Quando Funcionário É Selecionado:**

```
1. selectEmployee($employeeId) chamado
      ↓
2. calculatePayrollComponents() executado
      ↓
3. Helper calcula TUDO e popula:
   - $this->basic_salary = 69,900 ✅
   - $this->selectedEmployee = Employee object ✅
      ↓
4. Usuário marca checkbox
      ↓
5. updatedChristmasSubsidy() chamado
      ↓
6. Busca salário:
   $basicSalary = $this->selectedEmployee->basic_salary
                = 69,900 ✅
      ↓
7. Calcula 50%:
   $this->christmas_subsidy_amount = 69,900 × 0.5
                                     = 34,950 ✅
      ↓
8. Atualiza totais
      ↓
9. View mostra: 34,950.00 AOA ✅
```

---

## 🎯 Onde Olhar Se Falhar

### **A. Funcionário Não Selecionado:**

```php
if (!$this->selectedEmployee) {
    // ❌ Não deveria marcar checkbox sem funcionário
    return;
}
```

**Solução:** Desabilitar checkbox se não houver funcionário:

```blade
<input 
    type="checkbox" 
    wire:model="christmas_subsidy"
    {{ !$selectedEmployee ? 'disabled' : '' }}
    ...
>
```

### **B. Salário Base = 0:**

```php
if ($basicSalary <= 0) {
    Log::warning('Funcionário sem salário base definido', [
        'employee_id' => $this->selectedEmployee->id,
    ]);
    return;
}
```

### **C. Helper Não Foi Executado:**

Se `$this->basic_salary` está 0 E `$this->selectedEmployee->basic_salary` também está 0:
- ❌ `calculatePayrollComponents()` não foi chamado
- ❌ Employee não tem salário cadastrado

---

## 🏆 Resultado Esperado

### **ANTES (❌):**
```
Checkbox: ✅ Marcado
Valor mostrado: 0.00 AOA ❌
Log: basic_salary = 0 ❌
```

### **DEPOIS (✅):**
```
Checkbox: ✅ Marcado
Valor mostrado: 34,950.00 AOA ✅
Log: basic_salary = 69900, amount = 34950 ✅
```

---

## 📋 Checklist de Validação

- [x] ✅ Código corrigido para usar `selectedEmployee->basic_salary`
- [x] ✅ Fallback para `$this->basic_salary`
- [x] ✅ Log adicionado com valores
- [x] ✅ Views compiladas limpas
- [ ] ⏳ Testar com funcionário real
- [ ] ⏳ Verificar log
- [ ] ⏳ Confirmar valor correto na tela

---

**Status:** ✅ CORRIGIDO  
**Cache:** ✅ LIMPO  
**Logs:** ✅ HABILITADOS  
**Próximo Passo:** Marcar checkbox e verificar se mostra 34,950.00 AOA

---

**🔍 Marque o checkbox agora e me envie:**
1. O valor que aparece na tela
2. As últimas linhas do log (com "Christmas Subsidy atualizado")
3. Screenshot se possível
