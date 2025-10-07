# 🔍 Debug: Subsídios de Férias e Natal - Checkboxes

**Data:** 2025-01-07  
**Problema:** Checkboxes de subsídios não estão funcionando  
**Status:** 🔍 INVESTIGANDO COM LOGS

---

## 🔧 Análise do Código

### **1. Checkboxes na View:**

```blade
<!-- Christmas Subsidy -->
<input 
    type="checkbox" 
    wire:model.live="christmas_subsidy"
    id="christmas_subsidy"
    class="h-5 w-5 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
>

<!-- Vacation Subsidy -->
<input 
    type="checkbox" 
    wire:model.live="vacation_subsidy"
    id="vacation_subsidy"
    class="h-5 w-5 text-teal-600 focus:ring-teal-500 border-gray-300 rounded"
>
```

✅ **OK:** Usando `wire:model.live` para atualização em tempo real

---

### **2. Exibição dos Valores:**

```blade
<!-- Valor Christmas -->
<div class="text-2xl font-bold text-teal-700">
    {{ number_format($christmas_subsidy_amount, 2) }} AOA
</div>

<!-- Valor Vacation -->
<div class="text-2xl font-bold text-teal-700">
    {{ number_format($vacation_subsidy_amount, 2) }} AOA
</div>
```

✅ **OK:** Usando as propriedades corretas

---

### **3. Propriedades no Livewire:**

```php
// Checkboxes
public bool $christmas_subsidy = false;
public bool $vacation_subsidy = false;

// Valores calculados
public float $christmas_subsidy_amount = 0.0;
public float $vacation_subsidy_amount = 0.0;
```

✅ **OK:** Propriedades públicas declaradas

---

### **4. Listeners/Métodos Updated:**

```php
public function updatedChristmasSubsidy(): void
{
    Log::info('Christmas Subsidy Updated', [
        'value' => $this->christmas_subsidy,
        'employee_id' => $this->selectedEmployee->id ?? null,
    ]);
    $this->calculatePayrollComponents();
}

public function updatedVacationSubsidy(): void
{
    Log::info('Vacation Subsidy Updated', [
        'value' => $this->vacation_subsidy,
        'employee_id' => $this->selectedEmployee->id ?? null,
    ]);
    $this->calculatePayrollComponents();
}
```

✅ **OK:** Métodos `updated` corretos com **LOGS ADICIONADOS**

---

### **5. Helper Calcula os Subsídios:**

```php
// No calculatePayrollComponents():
$calculator->setChristmasSubsidy($this->christmas_subsidy);
$calculator->setVacationSubsidy($this->vacation_subsidy);

// Cálculo no Helper:
public function getChristmasSubsidyAmount(): float
{
    return $this->christmasSubsidy ? ($this->basicSalary * 0.5) : 0.0;
}

public function getVacationSubsidyAmount(): float
{
    return $this->vacationSubsidy ? ($this->basicSalary * 0.5) : 0.0;
}

// Atribuição dos resultados:
$this->christmas_subsidy_amount = $results['christmas_subsidy_amount'];
$this->vacation_subsidy_amount = $results['vacation_subsidy_amount'];

Log::info('Subsídios calculados pelo helper', [
    'christmas_subsidy' => $this->christmas_subsidy,
    'christmas_subsidy_amount' => $this->christmas_subsidy_amount,
    'vacation_subsidy' => $this->vacation_subsidy,
    'vacation_subsidy_amount' => $this->vacation_subsidy_amount,
]);
```

✅ **OK:** Helper configurado corretamente com **LOGS ADICIONADOS**

---

## 🔍 Logs Adicionados para Debug

### **Log 1: Quando Checkbox é Clicado**

```
'Christmas Subsidy Updated' ou 'Vacation Subsidy Updated'
- value: true/false
- employee_id: ID do funcionário
```

### **Log 2: Após Cálculo do Helper**

```
'Subsídios calculados pelo helper'
- christmas_subsidy: true/false
- christmas_subsidy_amount: valor calculado
- vacation_subsidy: true/false
- vacation_subsidy_amount: valor calculado
```

---

## 🧪 Testes a Realizar

### **Passo 1: Verificar se o Checkbox Está Funcionando**

1. Abrir a modal de payroll
2. Selecionar um funcionário
3. Clicar no checkbox "Christmas Subsidy"
4. **Verificar no log:** Aparece "Christmas Subsidy Updated"?
5. **Verificar no log:** Aparece "Subsídios calculados pelo helper"?
6. **Verificar na tela:** O valor abaixo do checkbox atualizou?

### **Passo 2: Verificar se o Valor Está Correto**

1. Com o checkbox marcado:
   - **Valor esperado:** 50% do salário base
   - **Exemplo:** Se salário base = 69,900 AOA
   - **Esperado:** 34,950.00 AOA

2. Com o checkbox desmarcado:
   - **Valor esperado:** 0.00 AOA

---

## 🐛 Possíveis Causas do Problema

### **1. Livewire Não Detecta Mudança no Checkbox** ❓

**Sintoma:** Log "Christmas Subsidy Updated" NÃO aparece

**Possível Causa:**
- Conflito com Alpine.js
- Problema no `wire:model.live`
- JavaScript não carregado

**Solução:**
- Verificar console do navegador
- Testar com `wire:model` ao invés de `wire:model.live`

---

### **2. Método Updated Não É Chamado** ❓

**Sintoma:** Log "Christmas Subsidy Updated" NÃO aparece

**Possível Causa:**
- Método com nome errado
- Propriedade não pública
- Conflito de nomes

**Solução:**
- Verificar se o nome está correto: `updatedChristmasSubsidy`
- Propriedade deve ser `public bool $christmas_subsidy`

---

### **3. Helper Não Calcula Corretamente** ❓

**Sintoma:** Log "Subsídios calculados pelo helper" mostra valor = 0

**Possível Causa:**
- `$this->christmas_subsidy` é false quando deveria ser true
- `$this->basicSalary` é 0
- Lógica no helper errada

**Solução:**
- Verificar valores no log
- Verificar se `setChristmasSubsidy()` está sendo chamado

---

### **4. View Não Atualiza** ❓

**Sintoma:** Log mostra valor correto, mas tela não atualiza

**Possível Causa:**
- Cache de view
- Problema de rendering do Livewire
- Alpine.js interferindo

**Solução:**
- `php artisan view:clear` ✅ JÁ FEITO
- `php artisan optimize:clear` ✅ JÁ FEITO
- Testar com `wire:key` no elemento

---

## 📋 Checklist de Verificação

### **Cache:**
- [x] ✅ `php artisan optimize:clear` executado
- [x] ✅ Views compiladas limpas

### **Código:**
- [x] ✅ Checkboxes com `wire:model.live`
- [x] ✅ Propriedades públicas declaradas
- [x] ✅ Métodos `updated` existem
- [x] ✅ Helper configurado corretamente
- [x] ✅ Logs adicionados

### **Testes:**
- [ ] ⏳ Testar checkbox no navegador
- [ ] ⏳ Verificar logs
- [ ] ⏳ Confirmar valor atualiza na tela

---

## 🔬 Como Ler os Logs

### **No Laravel:**

```bash
# Visualizar logs em tempo real
tail -f storage/logs/laravel.log

# Ou abrir o arquivo de log
storage/logs/laravel-YYYY-MM-DD.log
```

### **O Que Procurar:**

```
[timestamp] local.INFO: Christmas Subsidy Updated {"value":true,"employee_id":123}
[timestamp] local.INFO: Subsídios calculados pelo helper {"christmas_subsidy":true,"christmas_subsidy_amount":34950,...}
```

---

## 🎯 Próximos Passos

1. **Teste no navegador:**
   - Marcar/desmarcar checkbox
   - Observar se o valor muda

2. **Verificar logs:**
   - Confirmar que "Christmas Subsidy Updated" aparece
   - Verificar valores em "Subsídios calculados pelo helper"

3. **Reportar:**
   - Se o log NÃO aparece → Problema no Livewire/JavaScript
   - Se o log aparece mas valor está errado → Problema no Helper
   - Se o log está correto mas tela não atualiza → Problema de rendering

---

## 📝 Informações para Reportar

Se o problema persistir, fornecer:

1. **O checkbox está marcado/desmarcado?**
2. **O que aparece nos logs?**
3. **O valor na tela mudou?**
4. **Há erros no console do navegador?**
5. **Screenshot da tela**

---

**Status:** 🔍 AGUARDANDO TESTES  
**Logs:** ✅ ADICIONADOS  
**Cache:** ✅ LIMPO
