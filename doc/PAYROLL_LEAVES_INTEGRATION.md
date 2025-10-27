# Integração entre Férias (Leaves) e Folha de Pagamento (Payroll)

## 📋 Visão Geral

Este documento explica como o sistema trata a relação entre **férias/licenças** (leaves) e **cálculos de folha de pagamento** (payroll).

---

## 🎯 Regra de Negócio: Férias são PAGAS

### **Princípio Fundamental:**
✅ **Funcionários de férias devem receber o salário COMPLETO**  
❌ **Férias NÃO devem gerar deduções no salário**

Esta é a legislação trabalhista padrão em Angola e maioria dos países.

---

## 🔧 Como o Sistema Funciona

### **1. Status de Attendance (Presença)**

| Status | Código | Tratamento Payroll |
|--------|--------|-------------------|
| Presente | `present` | ✅ Dia pago completo |
| Atrasado | `late` | ✅ Dia pago - dedução de 1h |
| Meio Dia | `half_day` | ⚠️ Meio dia pago |
| **Férias** | `leave` | ✅ **Dia pago completo** |
| Ausente | `absent` | ❌ Dia NÃO pago |

---

## 💡 Correção Implementada

### **ANTES (❌ INCORRETO):**

```php
// PayrollCalculatorHelper.php - LINHA 169 (ANTIGA)
$this->presentDays = $attendances->whereIn('status', ['present', 'late', 'half_day'])->count();
```

**Problema:** Férias (`leave`) NÃO eram contadas como dias presentes, causando:
- ❌ Dedução indevida no salário
- ❌ Subsídios proporcionais reduzidos
- ❌ Salário líquido menor durante férias

---

### **DEPOIS (✅ CORRETO):**

```php
// PayrollCalculatorHelper.php - LINHA 170 (NOVA)
// Férias (leave) são consideradas como dias presentes e pagos
$this->presentDays = $attendances->whereIn('status', ['present', 'late', 'half_day', 'leave'])->count();
```

**Benefícios:**
- ✅ Férias contam como dias presentes
- ✅ Sem dedução por ausência
- ✅ Subsídios pagos proporcionalmente corretos
- ✅ Salário completo durante férias

---

## 📊 Impacto nos Cálculos

### **Exemplo Prático Completo:**

#### **Cenário:**
- Salário Base: 100.000 AOA
- Subsídio Transporte: 10.000 AOA/mês
- Dias úteis no mês: 22 dias
- Funcionário: 15 dias trabalhados + 7 dias de férias

---

#### **ANTES DA CORREÇÃO (❌):**

```
Present Days = 15 (apenas 'present', 'late', 'half_day')
Absent Days = 7 (férias contadas como ausência!)

Dedução por Ausência:
= (100.000 / 22) × 7
= 4.545 × 7
= 31.818 AOA deduzidos ❌

Salário Líquido = 100.000 - 31.818 = 68.182 AOA ❌ ERRADO!
```

---

#### **DEPOIS DA CORREÇÃO (✅):**

```
Present Days = 22 (inclui os 7 dias de férias)
Days Worked Effectively = 15 (EXCLUI os 7 dias de férias)
Absent Days = 0

Cálculo Salário:
= 100.000 AOA (sem dedução, baseado em Present Days) ✅

Cálculo Subsídio Transporte:
= (10.000 / 22) × 15 (usa Days Worked Effectively)
= 454,55 × 15
= 6.818,18 AOA ✅

Total Earnings = 100.000 + 6.818,18 = 106.818,18 AOA ✅ CORRETO!
```

**Lógica:**
- ✅ Salário base = pago completo (férias são pagas)
- ❌ Subsídio transporte = proporcional apenas aos dias trabalhados (férias não deslocam)

---

## 🔍 Código Detalhado

### **1. Dois Contadores Separados**

```php
// Férias (leave) são consideradas como dias presentes para SALÁRIO
$this->presentDays = $attendances->whereIn('status', [
    'present',   // Presente
    'late',      // Atrasado
    'half_day',  // Meio dia
    'leave'      // ✨ FÉRIAS (incluído para salário)
])->count();

// Dias efetivamente trabalhados (EXCLUI férias) para SUBSÍDIOS
$this->daysWorkedEffectively = $attendances->whereIn('status', [
    'present',   // Presente
    'late',      // Atrasado
    'half_day',  // Meio dia
    // ❌ 'leave' NÃO incluído (sem deslocamento = sem subsídio transporte)
])->count();

$this->absentDays = $this->totalWorkingDays - $this->presentDays;
```

**Por quê dois contadores?**
- `presentDays` → Usado para cálculo de **SALÁRIO** (férias = pagas)
- `daysWorkedEffectively` → Usado para **SUBSÍDIO TRANSPORTE** (férias = sem deslocamento)

---

### **2. Cálculo de Horas Trabalhadas**

```php
foreach ($attendances as $attendance) {
    // Férias (leave) contam como dia trabalhado completo
    if (in_array($attendance->status, ['present', 'late', 'half_day', 'leave'])) {
        
        switch ($attendance->status) {
            case 'present':
                $hours = 8; // Dia completo
                break;
            case 'late':
                $hours = 8; // Dia completo
                break;
            case 'half_day':
                $hours = 4; // Meio dia
                break;
            case 'leave':
                $hours = 8; // ✨ Férias = dia completo pago
                break;
        }
        
        $this->totalAttendanceHours += $hours;
    }
}
```

---

### **3. Subsídio de Transporte (NOVA LÓGICA)**

```php
public function calculateProportionalTransportAllowance(): float
{
    $fullTransportAllowance = $this->employee->transport_benefit;
    
    // ✅ USA daysWorkedEffectively (EXCLUI férias)
    $proportionalAllowance = ($fullTransportAllowance / $this->totalWorkingDays) * $this->daysWorkedEffectively;
    
    return $proportionalAllowance;
}
```

**Antes:** Usava `$this->presentDays` (incluía férias) ❌  
**Agora:** Usa `$this->daysWorkedEffectively` (exclui férias) ✅

---

### **4. Cálculo de Deduções**

```php
protected function calculateAttendanceDeductions($attendances): void
{
    foreach ($attendances as $attendance) {
        switch ($attendance->status) {
            case 'late':
                $this->lateDeduction += $this->hourlyRate; // Deduz 1 hora
                break;
            case 'absent':
                $this->absenceDeduction += $dailyRate; // Deduz dia completo
                break;
            case 'half_day':
                $this->absenceDeduction += ($dailyRate / 2); // Deduz meio dia
                break;
            // ✅ 'leave' NÃO tem dedução!
        }
    }
}
```

---

## 🎯 Subsídios Proporcionais

### **Subsídio de Transporte:**

⚠️ **IMPORTANTE:** Funcionários de férias **NÃO recebem** subsídio de transporte, pois não há deslocamento para o trabalho.

```php
public function calculateProportionalTransportAllowance(): float
{
    $fullTransportAllowance = $employee->transport_benefit;
    
    // ✅ Usa daysWorkedEffectively (EXCLUI férias)
    $proportionalAllowance = ($fullTransportAllowance / $totalWorkingDays) * $this->daysWorkedEffectively;
    
    return $proportionalAllowance;
}
```

**Resultado:** 
- ✅ Funcionário trabalhando = recebe subsídio proporcional
- ❌ Funcionário de férias = NÃO recebe subsídio (sem deslocamento)

---

## 📝 Registrar Férias no Sistema

### **1. Via Attendance Management:**

```
1. Acesse HR > Attendance
2. Clique em "Register Attendance"
3. Selecione o funcionário
4. Escolha a data
5. Status = "Leave" (Férias)
6. Salvar
```

### **2. Em Lote (Batch):**

```
1. Acesse HR > Attendance
2. Clique em "Batch Attendance"
3. Selecione o turno
4. Selecione funcionários
5. Status = "Leave"
6. Salvar para múltiplos dias
```

---

## 🧪 Como Testar

### **Teste 1: Funcionário em Férias**

1. Registre 1 mês de attendance para um funcionário:
   - 15 dias: status = `present`
   - 7 dias: status = `leave` (férias)

2. Processe payroll para o período

3. **Verificar:**
   - ✅ Present Days = 22 (15 + 7)
   - ✅ Absent Days = 0
   - ✅ Absence Deduction = 0.00 AOA
   - ✅ Gross Salary = Salário Base completo

---

### **Teste 2: Comparação Férias vs Ausência**

**Funcionário A - Férias:**
```
- 15 dias present
- 7 dias leave
Resultado: Salário completo ✅
```

**Funcionário B - Faltou:**
```
- 15 dias present
- 7 dias absent
Resultado: Salário com dedução de 7 dias ❌
```

---

## 🚨 Casos Especiais

### **1. Férias Não Pagas**
Se a empresa tiver férias não pagas (raro), use status = `absent` em vez de `leave`.

### **2. Meio Dia de Férias**
Use status = `leave` para dia completo. Para meio dia, registre:
- Manhã: `leave`
- Tarde: `half_day` ou vice-versa

### **3. Férias + Feriado**
Feriados durante férias são considerados parte das férias. Não precisa registro separado.

---

## 📊 Relatórios

### **Relatório de Attendance:**
- Mostra dias de férias separadamente
- Conta como "dias trabalhados" para payroll
- Diferencia de ausências

### **Relatório de Payroll:**
- Present Days inclui férias
- Absent Days exclui férias
- Deduções corretas

---

## 🔐 Segurança e Validação

### **Validações Implementadas:**

1. ✅ Status `leave` é aceito no modelo Attendance
2. ✅ Férias contam para dias presentes
3. ✅ Sem dedução por férias
4. ✅ Subsídios pagos durante férias

---

## 📚 Referências Legais

### **Lei de Trabalho de Angola:**
- Férias anuais são PAGAS
- Mínimo 22 dias úteis por ano
- Salário durante férias = salário normal

---

## ✅ Checklist de Implementação

- [x] Adicionar `leave` a dias presentes
- [x] Adicionar `leave` ao cálculo de horas
- [x] Garantir sem dedução para `leave`
- [x] Subsídios proporcionais corretos
- [x] Documentação completa
- [x] Testes implementados

---

## 🔄 Histórico de Mudanças

| Data | Versão | Mudança |
|------|--------|---------|
| 2025-10-12 | 1.0 | Correção implementada: férias agora são pagas |

---

**✅ Sistema agora trata férias corretamente: PAGAS e sem deduções!**

---

## 💡 Dúvidas Frequentes

**Q: E se o funcionário faltar durante as férias?**  
A: Isso não é possível. Durante férias programadas, o funcionário está "de férias", não "ausente".

**Q: Como registrar férias antecipadas?**  
A: Use status = `leave` para as datas futuras.

**Q: Férias afetam bónus ou horas extras?**  
A: Não. Férias são apenas para salário base. Bónus e horas extras são calculados separadamente.

**Q: E subsídio de alimentação?**  
A: Depende da política da empresa. O sistema paga proporcional aos dias presentes (incluindo férias).

---

**Última atualização:** 2025-10-12  
**Responsável:** Sistema de Payroll - ERP DEMBENA
