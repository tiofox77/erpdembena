# 🔄 Mapeamento: Helper vs Modal Individual

## 📊 Como Cada Campo é Calculado

| Campo na Tela | Modal Individual | Helper (PayrollCalculatorHelper) | Status |
|---------------|------------------|----------------------------------|--------|
| **Basic Salary** | `$basic_salary` | `$calculatedData['basic_salary']` | ✅ OK |
| **Christmas Subsidy** | `$christmas_subsidy_amount` | `$calculatedData['christmas_subsidy_amount']` | ✅ OK |
| **Vacation Subsidy** | `$vacation_subsidy_amount` | `$calculatedData['vacation_subsidy_amount']` | ✅ OK |
| **Transport Allowance** | `$transport_allowance` | `$calculatedData['transport_allowance']` | ✅ OK |
| **Gross Salary** | `$gross_salary` | `$calculatedData['gross_salary']` | ✅ OK |
| **Base IRT** | `$this->irtBase` | `$calculatedData['irt_base']` | ⚠️ DIFERENTE |
| **IRT** | `$this->calculatedIrt` | `$calculatedData['irt']` | ⚠️ VERIFICAR |
| **INSS 3%** | `$inss_3_percent` | `$calculatedData['inss_3_percent']` | ⚠️ DIFERENTE |
| **INSS 8%** | `$inss_8_percent` | `$calculatedData['inss_8_percent']` | ✅ OK |
| **Absence Deduction** | `$absence_deduction` | `$calculatedData['absence_deduction']` | ✅ OK |
| **Late Deduction** | `$late_deduction` | `$calculatedData['late_deduction']` | ✅ OK |
| **Main Salary** | `$this->mainSalary` | `$calculatedData['main_salary']` | ❌ DIFERENTE |
| **Total Deductions** | `$this->totalDeductionsCalculated` | `$calculatedData['total_deductions']` | ❌ DIFERENTE |
| **Net Salary** | `$this->calculatedNetSalary` | `$calculatedData['net_salary']` | ❌ DIFERENTE |

---

## 🔴 DIFERENÇAS CRÍTICAS

### 1. **INSS 3% - Base de Cálculo**

**Modal Individual:**
```php
($basic + $transport + $meal + $overtime) * 0.03
```

**Helper:**
```php
($basic + $food + $transport + $overtime + $bonus + $additionalBonus + $christmas + $vacation) * 0.03
```

**Diferença:** Helper inclui MAIS componentes na base do INSS.

---

### 2. **Main Salary**

**Modal Individual:**
```php
$basic + $transportCash + $foodCash + $overtime + $nightShift + $otherAllow - $absence
```

**Helper:**
```php
$basic + $food + $transport + $overtime + $bonus + $additionalBonus + $christmas + $vacation
// ❌ NÃO deduz $absence
```

**Diferença:** 
- Modal individual DEDUZ faltas do Main Salary
- Helper NÃO deduz faltas

---

### 3. **Base IRT (IRT Taxable)**

**Modal Individual:**
```php
$grossForTax - $inss - $exemptTransport - $exemptFood

onde:
$grossForTax = $mainSalary + $vacation + $christmas + $bonuses
$mainSalary = $basic + ... - $absence (JÁ COM FALTAS DEDUZIDAS)
```

**Helper:**
```php
$grossSalary - $inss - $exemptTransport - $exemptFood

onde:
$grossSalary = $basic + ... (SEM FALTAS DEDUZIDAS)
```

**Diferença:** Modal individual usa `grossForTax` que já tem faltas deduzidas.

---

### 4. **Total Deductions**

**Modal Individual:**
```php
$inss + $irt + $advance + $discounts + $union + $uFund + $loans + $foodInKind + $foodCash
```

**Helper:**
```php
$inss + $irt + $advance + $discounts + $late + $absence
```

**Diferenças:**
- Modal individual inclui: union, uFund, loans, food (2x?)
- Helper inclui: late, absence

---

### 5. **Net Salary**

**Modal Individual:**
```php
$grossForTax - $totalDeductions

onde:
$grossForTax = incluiu faltas já deduzidas no mainSalary
$totalDeductions = inclui union, loans, food, etc
```

**Helper:**
```php
$grossSalary - $totalDeductions - $foodDeduction

onde:
$grossSalary = NÃO incluiu faltas
$totalDeductions = inclui late, absence
$foodDeduction = food if in kind
```

---

## 🎯 SOLUÇÃO: O Que Fazer?

### Opção 1: Adaptar Helper para Match Modal Individual ✅ RECOMENDADO

Modificar o helper para calcular EXATAMENTE como a modal individual:

1. **Main Salary:** Deduzir absence
2. **Gross For Tax:** Usar Main Salary (com absence deduzida)
3. **Total Deductions:** Incluir union, uFund, loans
4. **INSS Base:** Usar mesma base que modal individual

### Opção 2: Adaptar Modal Individual para Usar Helper ❌ NÃO FAZER

Mudar toda a lógica da modal individual (arriscado, já funciona).

---

## 📝 Campos Faltando no Helper

Estes campos são usados na modal individual mas NÃO estão no helper:

| Campo | Usado Em | Precisa Adicionar? |
|-------|----------|-------------------|
| `night_shift_allowance` | Main Salary | ✅ SIM |
| `other_allowances` | Main Salary | ✅ SIM |
| `union_deduction` | Total Deductions | ✅ SIM |
| `u_fund_ded` | Total Deductions | ✅ SIM |
| `loan_installments` | Total Deductions | ✅ SIM |
| `total_salary_discounts` | Total Deductions | ✅ JÁ TEM |
| `advance_deduction` | Total Deductions | ✅ JÁ TEM |

---

## 🔧 Correções Necessárias no Helper

### 1. Adicionar Night Shift e Other Allowances

```php
protected float $nightShiftAllowance = 0.0;
protected float $otherAllowances = 0.0;
```

### 2. Adicionar Union, U Fund, Loans

```php
protected float $unionDeduction = 0.0;
protected float $uFundDeduction = 0.0;
protected float $loanInstallments = 0.0;
```

### 3. Modificar Main Salary para Deduzir Absence

```php
public function calculateMainSalary(): float
{
    $basic = $this->basicSalary;
    $transportCash = $this->transportAllowance;
    
    $isFoodInKind = $this->isFoodInKind;
    $foodCash = $isFoodInKind ? 0.0 : $this->mealAllowance;
    
    $overtime = $this->totalOvertimeAmount;
    $nightShift = $this->nightShiftAllowance;
    $otherAllow = $this->otherAllowances;
    
    $absence = $this->absenceDeduction;
    
    return max(0.0, $basic + $transportCash + $foodCash + $overtime + $nightShift + $otherAllow - $absence);
}
```

### 4. Modificar Total Deductions

```php
public function calculateTotalDeductions(): float
{
    $inss = $this->calculateINSS();
    $irt = $this->calculateIRT();
    $advances = $this->advanceDeduction;
    $discounts = $this->totalSalaryDiscounts;
    $union = $this->unionDeduction;
    $uFund = $this->uFundDeduction;
    $loans = $this->loanInstallments;
    
    // Food handling
    $foodInKind = $this->isFoodInKind ? $this->mealAllowance : 0;
    $foodCash = $this->isFoodInKind ? 0 : $this->mealAllowance;
    
    return $inss + $irt + $advances + $discounts + $union + $uFund + $loans + $foodInKind + $foodCash;
}
```

---

## ✅ Checklist de Implementação

- [ ] 1. Adicionar campos faltantes no helper
- [ ] 2. Modificar `calculateMainSalary()` para deduzir absence
- [ ] 3. Modificar `calculateTotalDeductions()` para incluir union, uFund, loans, food
- [ ] 4. Modificar `calculateINSS()` para usar mesma base que modal individual
- [ ] 5. Testar com caso do screenshot (26 dias ausentes)
- [ ] 6. Verificar que Net Salary = 0.00 no caso de 26 ausências
- [ ] 7. Replicar na modal batch

---

**CONCLUSÃO:** O helper precisa ser REFATORADO para calcular EXATAMENTE como a modal individual. Só assim teremos 100% de fidelidade.
