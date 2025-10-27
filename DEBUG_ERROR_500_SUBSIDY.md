# 🔴 DEBUG: Erro 500 ao Marcar Checkboxes - SEM LOGS

**Data:** 2025-01-07  
**Erro:** HTTP 500 Internal Server Error ao atualizar checkboxes  
**Problema:** Nenhum log é gerado no Laravel  
**Status:** 🔍 INVESTIGANDO

---

## 🐛 Sintomas

1. **Usuário marca checkbox**
2. **Tela preta aparece** (loading do Livewire)
3. **Erro 500 no console:**
   ```
   POST http://erpdembena.test/livewire/update 500 (Internal Server Error)
   ```
4. **NENHUM LOG aparece em `storage/logs/laravel.log`**

---

## 🔍 Análise do Problema

### **Por Que Não Há Logs?**

Um erro 500 **SEM LOGS** significa:

1. ❌ **Erro Fatal PHP** - Crash antes de chegar ao handler de exceções
2. ❌ **Timeout** - PHP excede tempo máximo de execução
3. ❌ **Memória Esgotada** - PHP fica sem memória
4. ❌ **Erro no Bootstrap** - Erro antes do Laravel iniciar
5. ❌ **Loop Infinito** - Código entra em recursão infinita

---

## 🔧 Correções Aplicadas

### **1. Try-Catch Expandido nos Métodos Updated**

**Adicionado catch detalhado:**

```php
public function updatedChristmasSubsidy(): void
{
    try {
        Log::info('Christmas Subsidy Updated - INÍCIO', [
            'value' => $this->christmas_subsidy,
            'employee_id' => $this->selectedEmployee->id ?? null,
        ]);
        
        $this->calculatePayrollComponents();
        
        Log::info('Christmas Subsidy Updated - SUCESSO');
    } catch (\Exception $e) {
        Log::error('ERRO ao atualizar Christmas Subsidy', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        
        session()->flash('error', 'Erro ao calcular subsídio: ' . $e->getMessage());
    }
}
```

### **2. Re-Throw Exception para Debug**

**No calculatePayrollComponents:**

```php
} catch (\Exception $e) {
    Log::error('ERRO FATAL ao calcular payroll com helper', [
        'employee_id' => $this->selectedEmployee->id ?? null,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ]);
    
    session()->flash('error', 'Erro ao calcular payroll: ' . $e->getMessage());
    throw $e; // ✅ Re-throw para ver erro completo
}
```

---

## 🧪 Próximos Passos de Debug

### **1. Verificar Logs PHP do Servidor**

Verificar logs do PHP (não Laravel):

```powershell
# Laragon geralmente loga em:
C:\laragon2\bin\php\php-8.3.16\logs\php_error.log

# OU
C:\laragon2\www\ERPDEMBENA\storage\logs\laravel.log
```

**Procure por:**
- Fatal error
- Maximum execution time exceeded
- Allowed memory size exhausted
- Call to undefined method

---

### **2. Teste Simples no Tinker**

```bash
php artisan tinker
```

```php
// Teste o helper isolado
$employee = App\Models\HR\Employee::find(1);
$start = Carbon\Carbon::now()->startOfMonth();
$end = Carbon\Carbon::now()->endOfMonth();

$calculator = new App\Helpers\PayrollCalculatorHelper($employee, $start, $end);
$calculator->loadAllEmployeeData();
$calculator->setChristmasSubsidy(true);
$results = $calculator->calculate();

dd($results['christmas_subsidy_amount']);
```

**Se falhar aqui:** Erro é no Helper  
**Se funcionar:** Erro é no Livewire

---

### **3. Teste com Timeout Aumentado**

Adicionar no início de `calculatePayrollComponents()`:

```php
set_time_limit(60); // 60 segundos
ini_set('memory_limit', '512M'); // 512MB
```

---

### **4. Verificar Queries Lentas**

Adicionar no Helper (método `calculate()`):

```php
\DB::enableQueryLog();
$results = $this->calculate();
\Log::info('Queries executadas', \DB::getQueryLog());
```

---

## 🔬 Possíveis Causas Específicas

### **Causa 1: Loop Infinito em Computed Properties**

**ANTES (removido):**
```php
public function getMainSalaryProperty() {
    return $this->mainSalary; // ❌ Recursão infinita!
}
```

**STATUS:** ✅ JÁ CORRIGIDO - Removemos todas computed properties

---

### **Causa 2: Helper Carregando TODOS os Attendance Records**

**Verificar em Helper:**
```php
public function loadAllEmployeeData() {
    $this->loadAttendance(); // ⚠️ Pode carregar milhares de registros
}
```

**Solução:** Adicionar limit:

```php
$attendance = Attendance::where('employee_id', $this->employee->id)
    ->whereBetween('date', [$this->startDate, $this->endDate])
    ->limit(1000) // ✅ Limitar registros
    ->get();
```

---

### **Causa 3: Relações Não Otimizadas**

**Verificar N+1 queries:**

```php
// ❌ ERRADO
foreach ($attendances as $att) {
    $employee = $att->employee; // Query para cada
}

// ✅ CORRETO
$attendances = Attendance::with('employee')->get();
```

---

### **Causa 4: Timeout em Produção**

**Verificar php.ini:**

```ini
max_execution_time = 30  ; ⚠️ Muito curto para cálculos pesados
memory_limit = 128M      ; ⚠️ Pode ser insuficiente
```

**Recomendado:**
```ini
max_execution_time = 60
memory_limit = 512M
```

---

## 📋 Checklist de Debug

### **Logs:**
- [ ] ⏳ Verificar `storage/logs/laravel.log`
- [ ] ⏳ Verificar logs do PHP
- [ ] ⏳ Verificar logs do Nginx/Apache

### **Teste Isolado:**
- [ ] ⏳ Testar Helper no Tinker
- [ ] ⏳ Testar com um funcionário simples
- [ ] ⏳ Verificar queries executadas

### **Performance:**
- [ ] ⏳ Verificar tempo de execução
- [ ] ⏳ Verificar uso de memória
- [ ] ⏳ Verificar número de queries

### **Código:**
- [x] ✅ Try-catch adicionados
- [x] ✅ Re-throw exception ativado
- [x] ✅ Logs detalhados adicionados
- [ ] ⏳ Timeout aumentado (se necessário)

---

## 🔍 Como Investigar Agora

### **Opção 1: Verificar Logs PHP**

```powershell
# Abrir arquivo de log do PHP
notepad C:\laragon2\bin\php\php-8.3.16\logs\php_error.log
```

### **Opção 2: Testar no Tinker**

```bash
php artisan tinker

# Copiar e colar:
$employee = App\Models\HR\Employee::first();
$start = now()->startOfMonth();
$end = now()->endOfMonth();
$calc = new App\Helpers\PayrollCalculatorHelper($employee, $start, $end);
$calc->loadAllEmployeeData();
$calc->setChristmasSubsidy(true);
$results = $calc->calculate();
```

### **Opção 3: Marcar Checkbox Novamente**

1. Abrir modal de payroll
2. Marcar checkbox
3. **IMEDIATAMENTE verificar logs:**

```powershell
# Em tempo real:
Get-Content C:\laragon2\www\ERPDEMBENA\storage\logs\laravel.log -Wait -Tail 50
```

---

## 🎯 O Que Esperar nos Logs

### **Se o erro for capturado agora:**

```
[timestamp] local.INFO: Christmas Subsidy Updated - INÍCIO {"value":true,"employee_id":123}
[timestamp] local.ERROR: ERRO FATAL ao calcular payroll com helper {"employee_id":123,"error":"...","file":"...","line":...}
```

### **Se ainda não aparecer log:**

Significa que o erro é **ANTES** do catch:
- ❌ Erro fatal no PHP
- ❌ Timeout severo
- ❌ Memória esgotada

**Solução:** Verificar logs do PHP diretamente.

---

## 💡 Dicas Adicionais

### **Aumentar Timeout Temporariamente:**

No início do método `updatedChristmasSubsidy()`:

```php
public function updatedChristmasSubsidy(): void
{
    set_time_limit(120); // 2 minutos
    ini_set('memory_limit', '1G'); // 1 GB
    
    try {
        // ... resto do código
    }
}
```

### **Desabilitar Debounce Temporariamente:**

```blade
{{-- Testar sem debounce para ver se é problema de timing --}}
<input wire:model.live="christmas_subsidy" ...>
```

### **Teste com Funcionário Simples:**

Selecionar um funcionário que:
- ✅ Tem poucos attendance records
- ✅ Não tem overtime
- ✅ Não tem advances/deductions
- ✅ Período curto (1 mês apenas)

---

## 🏆 Resultado Esperado

Após estas correções, **DEVE** aparecer um log com o erro detalhado:

```
[2025-01-07 13:XX:XX] local.ERROR: ERRO FATAL ao calcular payroll com helper
{
    "employee_id": 123,
    "error": "Call to undefined method...",
    "file": "/path/to/file.php",
    "line": 123,
    "trace": "..."
}
```

Com esse log, poderemos **identificar exatamente** o problema!

---

**Status:** 🔍 AGUARDANDO NOVO TESTE  
**Logs:** ✅ HABILITADOS  
**Try-Catch:** ✅ EXPANDIDO  
**Próximo Passo:** Marcar checkbox novamente e verificar logs
