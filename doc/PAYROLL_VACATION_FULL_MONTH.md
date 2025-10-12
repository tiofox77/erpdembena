# Férias: Mês Completo Pago

## 🎯 Regra de Negócio

**Se um funcionário sai de férias, deve receber o MÊS COMPLETO de salário.**

Esta é a implementação correta e está funcionando no sistema.

---

## ✅ Como o Sistema Funciona

### **Exemplo: Funcionário Sai de Férias Todo o Mês**

#### **Cenário:**
- Funcionário: João Silva
- Salário Base: 100.000 AOA
- Subsídio Transporte: 10.000 AOA/mês
- Mês: Janeiro (22 dias úteis)
- **Situação: 22 dias de férias (mês completo)**

---

### **Registros de Attendance:**

```
01/01 - leave (férias)
02/01 - leave (férias)
03/01 - leave (férias)
...
22/01 - leave (férias)
```

---

### **Cálculo do Payroll:**

#### **1. Contagem de Dias:**

```php
// PayrollCalculatorHelper.php

// Dias presentes (INCLUI férias)
$this->presentDays = $attendances->whereIn('status', [
    'present', 'late', 'half_day', 'leave'
])->count();
// Resultado: 22 dias ✅

// Dias efetivamente trabalhados (EXCLUI férias)
$this->daysWorkedEffectively = $attendances->whereIn('status', [
    'present', 'late', 'half_day'
])->count();
// Resultado: 0 dias ✅

// Dias ausentes
$this->absentDays = $this->totalWorkingDays - $this->presentDays;
// Resultado: 22 - 22 = 0 dias ✅
```

---

#### **2. Cálculo de Deduções:**

```php
foreach ($attendances as $attendance) {
    switch ($attendance->status) {
        case 'late':
            $this->lateDeduction += $this->hourlyRate;
            break;
        case 'absent':
            $this->absenceDeduction += $dailyRate;
            break;
        case 'half_day':
            $this->absenceDeduction += ($dailyRate / 2);
            break;
        // ✅ 'leave' NÃO está aqui = SEM dedução!
    }
}

// Resultado:
// - lateDeduction = 0.00 AOA ✅
// - absenceDeduction = 0.00 AOA ✅
```

**Total Deductions por Attendance = 0.00 AOA** ✅

---

#### **3. Salário Base:**

```php
// Salário base é SEMPRE o valor completo
$basicSalary = 100.000 AOA;

// NÃO há cálculo proporcional por férias
// Férias = dias presentes = salário completo
```

**Basic Salary = 100.000 AOA** ✅ (SEM DEDUÇÃO)

---

#### **4. Subsídio de Transporte:**

```php
// Usa daysWorkedEffectively (EXCLUI férias)
$proportionalTransport = (10.000 / 22) × 0;
$proportionalTransport = 0.00 AOA;
```

**Transport Allowance = 0.00 AOA** ✅ (Correto - sem deslocamento)

---

#### **5. Resultado Final:**

```
EARNINGS:
├─ Basic Salary: 100.000,00 AOA ✅ MÊS COMPLETO
├─ Transport Allowance: 0,00 AOA
├─ Food Allowance: 0,00 AOA (ou proporcional se política diferente)
└─ Total Earnings: 100.000,00 AOA

DEDUCTIONS:
├─ INSS (3%): 3.000,00 AOA
├─ IRT: (calculado sobre 100.000)
├─ Absence Deduction: 0,00 AOA ✅
├─ Late Deduction: 0,00 AOA ✅
└─ Total Deductions: (INSS + IRT)

NET SALARY: 100.000 - (INSS + IRT) = ~95.000 AOA ✅ COMPLETO
```

---

## 📊 Comparação: Férias vs Faltas

### **Funcionário A - 22 Dias de Férias:**

```
Status: 22 × leave
Present Days: 22 ✅
Absent Days: 0
Absence Deduction: 0,00 AOA
Salary: 100.000 AOA ✅ COMPLETO
```

### **Funcionário B - 22 Dias Ausente (Faltou):**

```
Status: 22 × absent
Present Days: 0 ❌
Absent Days: 22
Absence Deduction: 100.000 AOA (salário todo deduzido)
Salary: 0,00 AOA ❌ NADA PAGO
```

### **Funcionário C - 15 Trabalhados + 7 Férias:**

```
Status: 15 × present + 7 × leave
Present Days: 22 (15 + 7) ✅
Absent Days: 0
Absence Deduction: 0,00 AOA
Salary: 100.000 AOA ✅ COMPLETO
Transport: (10.000 / 22) × 15 = 6.818,18 AOA (proporcional aos 15 dias trabalhados)
```

---

## 🔍 Código Responsável

### **Arquivo:** `app/Helpers/PayrollCalculatorHelper.php`

### **1. Dias Presentes (Linha 170-174):**

```php
// Férias (leave) são consideradas como dias presentes para SALÁRIO
$this->presentDays = $attendances->whereIn('status', [
    'present', 'late', 'half_day', 'leave' // ✅ INCLUI férias
])->count();

// Dias efetivamente trabalhados (EXCLUINDO férias) para SUBSÍDIOS
$this->daysWorkedEffectively = $attendances->whereIn('status', [
    'present', 'late', 'half_day' // ❌ EXCLUI férias
])->count();
```

### **2. Dias Ausentes (Linha 176):**

```php
$this->absentDays = $this->totalWorkingDays - $this->presentDays;

// Se presentDays = 22 (todos de férias)
// absentDays = 22 - 22 = 0 ✅
```

### **3. Cálculo de Deduções (Linha 237-250):**

```php
foreach ($attendances as $attendance) {
    switch ($attendance->status) {
        case 'late':
            $this->lateDeduction += $this->hourlyRate;
            break;
        case 'absent':
            $this->absenceDeduction += $dailyRate;
            break;
        case 'half_day':
            $this->absenceDeduction += ($dailyRate / 2);
            break;
        // ✅ 'leave' NÃO está aqui
        // = ZERO dedução por férias
    }
}
```

### **4. Deduções Implícitas (Linha 255-258):**

```php
$implicitAbsences = $this->absentDays - $explicitAbsences - ($explicitHalfDays * 0.5);

if ($implicitAbsences > 0) {
    $this->absenceDeduction += ($implicitAbsences * $dailyRate);
}

// Se absentDays = 0 (porque presentDays inclui férias)
// implicitAbsences = 0
// absenceDeduction NÃO aumenta ✅
```

---

## ✅ Garantias do Sistema

1. ✅ **Férias contam como dias presentes**
2. ✅ **ZERO dedução por dias de férias**
3. ✅ **Salário base SEMPRE completo**
4. ✅ **Não há cálculo proporcional de salário por férias**
5. ✅ **Subsídio transporte proporcional aos dias TRABALHADOS** (correto)

---

## 🧪 Como Testar

### **Teste: Mês Completo de Férias**

1. **Registrar Férias:**
   ```
   HR > Attendance > Register Attendance
   Employee: Selecione funcionário
   Date: 01/01/2025 até 31/01/2025
   Status: "Leave" (Férias)
   ```

2. **Processar Payroll:**
   ```
   HR > Payroll > Process Payroll
   Period: Janeiro 2025
   Processar
   ```

3. **Verificar Resultado:**
   ```
   ✅ Present Days = 22 (ou total de dias úteis)
   ✅ Absent Days = 0
   ✅ Absence Deduction = 0.00 AOA
   ✅ Basic Salary = Salário Base Completo
   ✅ Gross Salary = Salário Completo (sem dedução)
   ✅ Net Salary = Gross - (INSS + IRT) apenas
   ```

---

## 📝 Casos Especiais

### **Caso 1: Férias Parciais**

**15 dias trabalhados + 7 dias de férias:**
```
Present Days: 22 (15 + 7)
Absence Deduction: 0.00 AOA
Salary: COMPLETO ✅
Transport: Proporcional aos 15 dias trabalhados
```

### **Caso 2: Férias + Feriados**

**Feriados durante férias:**
```
20 dias úteis + 2 feriados = 22 dias no mês
Todos em férias: status = leave
Result: Salário COMPLETO ✅
```

### **Caso 3: Início/Fim de Férias no Mês**

**Trabalhou 10 dias + 12 dias férias:**
```
Present Days: 22 (10 + 12)
Salary: COMPLETO ✅
Transport: Proporcional aos 10 dias trabalhados
```

---

## 🚨 Importante

### **Salário vs Subsídios:**

| Item | Cálculo | Inclui Férias? |
|------|---------|----------------|
| **Salário Base** | Completo | ✅ SIM (Férias = PAGAS) |
| **Transport Subsidy** | Proporcional | ❌ NÃO (Sem deslocamento) |
| **Food Subsidy** | Variável* | Depende política empresa |

*Food Subsidy: Depende da política da empresa. O sistema pode ser configurado para:
- Pagar durante férias (como salário)
- NÃO pagar durante férias (como transporte)

---

## 📖 Referência Legal

### **Código do Trabalho de Angola:**

**Artigo sobre Férias:**
- Férias anuais são um direito
- Mínimo 22 dias úteis por ano
- **Férias são PAGAS** - funcionário recebe salário normal
- Férias não podem ser convertidas em dinheiro (exceto rescisão)
- Durante férias, mantém-se todos os direitos

---

## ✅ Conclusão

O sistema **ESTÁ CORRETO** e **JÁ IMPLEMENTADO**:

1. ✅ Funcionário de férias recebe **MÊS COMPLETO**
2. ✅ **ZERO dedução** por dias de férias
3. ✅ Férias contam como **dias presentes**
4. ✅ Subsídio transporte **proporcional** aos dias trabalhados (correto)

**Não há necessidade de alteração no código.**

---

**Última atualização:** 2025-10-12  
**Status:** ✅ Implementado e Funcional
