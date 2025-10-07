# 📊 Referência Completa - Modal Individual de Payroll

## 🎯 Objetivo
Este documento serve como **REFERÊNCIA COMPLETA** da lógica de cálculo da modal individual de payroll.
Use este documento para **replicar EXATAMENTE** a mesma lógica na modal batch.

---

## 📍 Estrutura da Tela (Ordem de Exibição)

### **LADO ESQUERDO - Payroll Summary**

#### 1️⃣ **Basic Salary** (Salário Base)
- **Fonte:** `$basic_salary`
- **Origem:** `$employee->base_salary`
- **Sempre exibido:** ✅ SIM

#### 2️⃣ **Christmas Subsidy** (Subsídio de Natal)
- **Fonte:** `$christmas_subsidy_amount`
- **Cálculo:** `$christmas_subsidy ? ($basic_salary * 0.5) : 0`
- **Condição:** Exibir se `$christmas_subsidy_amount > 0`

#### 3️⃣ **Vacation Subsidy** (Subsídio de Férias)
- **Fonte:** `$vacation_subsidy_amount`
- **Cálculo:** `$vacation_subsidy ? ($basic_salary * 0.5) : 0`
- **Condição:** Exibir se `$vacation_subsidy_amount > 0`

#### 4️⃣ **Transport Allowance** (Subsídio de Transporte)
- **Fonte:** `$transport_allowance`
- **Sempre exibido:** ✅ SIM
- **Tag:** "Proporcional"
- **Breakdown expandível:**
  - Total do Benefício: `$transport_benefit_full`
  - Dias Presentes: `$present_days / $total_working_days`
  - Desconto por Faltas: `$transport_discount`
  - Valor a Pagar: `$transport_allowance`
  - Isento (até 30k): `$exempt_transport`
  - Tributável: `$taxable_transport`

#### 5️⃣ **Gross Salary** (com breakdown expandível ?)
- **Fonte:** `$gross_salary`
- **Cálculo (Helper):** `calculateGrossSalary()`
```php
$basic + $food + $transport + $overtime + $bonus + $additionalBonus + $christmas + $vacation
```
- **Breakdown:**
  - Basic Salary
  - Food Benefit
  - Transport Benefit
  - Overtime (se > 0)
  - Bonus (se > 0)
  - Additional Bonus (se > 0)
  - Christmas Subsidy (se > 0)
  - Vacation Subsidy (se > 0)
  - **Total = Gross Salary**

#### 6️⃣ **Base IRT Taxable Amount** (com breakdown expandível ?)
- **Fonte:** `$base_irt_taxable_amount` (ou `$irt_base`)
- **Cálculo (Modal Individual):**
```php
getIrtBaseProperty(): float
{
    $grossForTax = $this->getGrossForTaxProperty();
    $inssDeduction = $this->getCalculatedInssProperty();
    
    $transportCash = $this->transport_allowance;
    $foodBenefit = $this->selectedEmployee->food_benefit;
    $isFoodInKind = $this->is_food_in_kind;
    $foodCash = $isFoodInKind ? 0.0 : $foodBenefit;
    
    $exemptTransport = min(30000.0, $transportCash);
    $exemptFood = min(30000.0, $foodCash);
    
    return max(0.0, $grossForTax - $inssDeduction - $exemptTransport - $exemptFood);
}
```
- **Breakdown:**
  - Basic Salary
  - Transport Benefit (excesso tributável) - se `$taxable_transport > 0`
  - Bonus (tributável) - se > 0
  - Additional Bonus (tributável) - se > 0
  - Christmas Subsidy (tributável) - se > 0
  - Vacation Subsidy (tributável) - se > 0
  - Overtime (tributável) - se > 0
  - Food Benefit (excesso tributável) - se `$taxable_food > 0`
  - **Linha de isenções:**
    - Food Benefit (até 30k não tributável): `$exempt_food`
    - Transport Benefit (até 30k não tributável): `$exempt_transport`
  - **INSS 3% deduzido**
  - **Total = Base IRT**

---

### **DEDUCTIONS SECTION** (Seção de Deduções)

#### 7️⃣ **IRT (Imposto sobre Rendimento do Trabalho)**
- **Fonte:** `$income_tax` ou `$calculated_irt`
- **Cálculo:** `IRTTaxBracket::calculateIRT($irt_base)`
- **Exibição:** 
  - Base: `$irt_base`
  - Bracket (se aplicável)
- **Sempre exibido:** ✅ SIM

#### 8️⃣ **INSS 3%**
- **Fonte:** `$inss_3_percent`
- **Cálculo (Modal Individual):**
```php
getCalculatedInssProperty(): float
{
    $basic = $this->basic_salary;
    $transport = $this->transport_allowance;
    $meal = $this->meal_allowance;
    $overtime = $this->total_overtime_amount;
    
    return round(($basic + $transport + $meal + $overtime) * 0.03, 2);
}
```
- **Sempre exibido:** ✅ SIM

#### 9️⃣ **INSS 8% (Illustrative)**
- **Fonte:** `$inss_8_percent`
- **Tag:** "Apenas ilustrativo"
- **Sempre exibido:** ✅ SIM

#### 🔟 **Salary Advances** (Adiantamentos Salariais)
- **Fonte:** `$advance_deduction`
- **Condição:** Exibir se > 0

#### 1️⃣1️⃣ **Salary Discounts** (Descontos Salariais)
- **Fonte:** `$total_salary_discounts`
- **Condição:** Exibir se > 0

#### 1️⃣2️⃣ **Late Arrival Deductions** (Dedução por Atrasos)
- **Fonte:** `$late_deduction`
- **Exibição:** Com número de dias `$late_arrivals`
- **Condição:** Exibir se > 0

#### 1️⃣3️⃣ **Absence Deductions** (Dedução por Faltas)
- **Fonte:** `$absence_deduction`
- **Exibição:** Com número de dias `$absent_days`
- **Sempre exibido:** ✅ SIM (mesmo se = 0)

---

#### 1️⃣4️⃣ **Main Salary** (Salário Principal)
- **Fonte:** `$main_salary` ou `$this->mainSalary`
- **Cálculo (Modal Individual):**
```php
getMainSalaryProperty(): float
{
    $basic = $this->basic_salary;
    $transportCash = $this->transport_allowance;
    
    // Food benefit handling
    $isFoodInKind = $this->is_food_in_kind;
    $foodBenefit = $this->selectedEmployee->food_benefit;
    $foodCash = $isFoodInKind ? 0.0 : $foodBenefit;
    
    $overtime = $this->total_overtime_amount;
    $nightShift = $this->night_shift_allowance;
    $otherAllow = $this->other_allowances;
    
    // Absence deduction
    $absence = max($this->absence_deduction, $this->absenceDeductionAmount);
    
    return max(0.0, $basic + $transportCash + $foodCash + $overtime + $nightShift + $otherAllow - $absence);
}
```
- **Sempre exibido:** ✅ SIM

#### 1️⃣5️⃣ **Total Deductions** (Total de Deduções)
- **Fonte:** `$total_deductions` ou `$this->totalDeductionsCalculated`
- **Cálculo (Modal Individual):**
```php
getTotalDeductionsCalculatedProperty(): float
{
    $inss = $this->getCalculatedInssProperty();
    $irt = $this->getCalculatedIrtProperty();
    $advance = $this->advance_deduction;
    $otherDiscounts = $this->total_salary_discounts;
    $union = $this->union_deduction;
    $uFund = $this->u_fund_ded;
    $loans = $this->loan_installments;
    
    // Food handling
    $isFoodInKind = $this->is_food_in_kind;
    $foodBenefit = $this->selectedEmployee->food_benefit;
    $foodInKind = $isFoodInKind ? $foodBenefit : 0.0;
    $foodCash = $isFoodInKind ? 0.0 : $foodBenefit;
    
    return $inss + $irt + $advance + $otherDiscounts + $union + $uFund + $loans + $foodInKind + $foodCash;
}
```
- **Sempre exibido:** ✅ SIM

#### 1️⃣6️⃣ **Net Salary** (Salário Líquido) - com breakdown expandível ?
- **Fonte:** `$net_salary` ou `$this->calculatedNetSalary`
- **Cálculo (Modal Individual):**
```php
getCalculatedNetSalaryProperty(): float
{
    $grossForTax = $this->getGrossForTaxProperty();
    $deductions = $this->getTotalDeductionsCalculatedProperty();
    
    return round(max(0.0, $grossForTax - $deductions), 2);
}
```
- **Breakdown:**
  - Gross Salary (Gross For Tax)
  - IRT (dedução)
  - INSS 3% (dedução)
  - INSS 8% (ilustrativo)
  - Advances (se > 0)
  - Discounts (se > 0)
  - Union (se > 0)
  - U Fund (se > 0)
  - Loans (se > 0)
  - Food In Kind (se aplicável)
  - **Main Salary Total**
  - Total Deductions
  - **Net Salary**
- **Sempre exibido:** ✅ SIM

---

## 🔢 Computed Properties (Modal Individual)

### 1. `mainSalary` (getMainSalaryProperty)
```php
Basic + Transport + Food(cash) + Overtime + NightShift + Other - Absences
```

### 2. `grossForTax` (getGrossForTaxProperty)
```php
mainSalary + VacationSubsidy + ChristmasSubsidy + Bonuses
```

### 3. `calculatedInss` (getCalculatedInssProperty)
```php
(Basic + Transport + Meal + Overtime) * 0.03
```

### 4. `irtBase` (getIrtBaseProperty)
```php
grossForTax - INSS - ExemptTransport(30k) - ExemptFood(30k)
```

### 5. `calculatedIrt` (getCalculatedIrtProperty)
```php
IRTTaxBracket::calculateIRT(irtBase)
```

### 6. `totalDeductionsCalculated` (getTotalDeductionsCalculatedProperty)
```php
INSS + IRT + Advances + Discounts + Union + UFund + Loans + FoodInKind + FoodCash
```

### 7. `calculatedNetSalary` (getCalculatedNetSalaryProperty)
```php
grossForTax - totalDeductionsCalculated
```

---

## ⚠️ DIFERENÇAS CRÍTICAS: Modal Individual vs Helper

### 🔴 **Main Salary**

**Modal Individual:**
```php
Basic + Transport + Food(cash) + Overtime + NightShift + Other - ABSENCE
```

**Helper:**
```php
Basic + Food(SEMPRE) + Transport + Overtime + Bonus + AdditionalBonus + Christmas + Vacation
// ❌ NÃO deduz absence
// ❌ Inclui food mesmo se in kind
```

### 🔴 **Total Deductions**

**Modal Individual:**
```php
INSS + IRT + Advances + Discounts + Union + UFund + Loans + FoodInKind + FoodCash
// ⚠️ SOMA food DUAS VEZES (in kind E cash)
```

**Helper:**
```php
INSS + IRT + Advances + Discounts + Late + Absence
// ✅ Não soma food duas vezes
```

### 🔴 **Net Salary**

**Modal Individual:**
```php
grossForTax - totalDeductionsCalculated
```

**Helper (AGORA CORRIGIDO):**
```php
grossSalary - totalDeductions - foodDeduction(se in kind)
```

---

## 📋 Exemplo de Cálculo (Do Screenshot)

**Dados:**
- Basic Salary: 69,900.00
- Food Benefit: 29,000.00 (NÃO in kind)
- Transport: 30,000.00 (mas 0 dias presentes = 0.00 pago)
- Absent Days: 26 dias
- Present Days: 0 dias

**Gross Salary:**
```
69,900 (basic) + 29,000 (food) + 0 (transport) = 98,900.00
```

**INSS 3%:**
```
(69,900 + 0 + 29,000 + 0) * 0.03 = 2,967.00
```

**Base IRT:**
```
98,900 - 2,967 - 0 (exempt transport) - 29,000 (exempt food) = 66,933.00
```

**IRT:**
```
Bracket 1 (isento): 0.00
```

**Absence Deduction:**
```
69,900 / 22 * 26 = 69,900.00 (todo o salário)
```

**Main Salary:**
```
69,900 + 0 (transport) + 29,000 (food cash) + 0 - 69,900 (absence) = 29,000.00
```

**Total Deductions:**
```
2,967 (INSS) + 0 (IRT) + 0 + 69,900 (absence via total_deductions?) = 31,967.00
// ⚠️ Parece que absence está sendo contada DUAS VEZES
```

**Net Salary:**
```
98,900 - 31,967 = 66,933.00
// ❌ MAS exibe 0.00 no screenshot
```

---

## 🎯 PARA REPLICAR NA MODAL BATCH

### ✅ O Que Fazer:

1. **Usar EXATAMENTE o mesmo helper** - ✅ JÁ FEITO
2. **Exibir campos NA MESMA ORDEM** - ✅ VERIFICAR
3. **Mesmas condições de exibição** - ✅ VERIFICAR
4. **Mesmos breakdowns expandíveis** - ✅ VERIFICAR
5. **Resolver duplicação de absence** - ⚠️ CRÍTICO

### ⚠️ Problemas a Resolver:

1. **Absence está sendo deduzida DUAS VEZES?**
   - Uma vez no Main Salary
   - Outra vez no Total Deductions?

2. **Food está sendo somada DUAS VEZES nas deduções?**
   - Food in kind
   - Food cash

3. **Net Salary mostra 0.00 mas deveria ser 66,933.00?**

---

## 🔍 Próximos Passos

1. ✅ Analisar por que Net Salary = 0.00
2. ✅ Verificar se absence está duplicada
3. ✅ Corrigir helper para match EXATO com modal individual
4. ✅ Replicar TODOS os campos e breakdowns na modal batch

---

**Data:** 2025-10-07  
**Versão:** 1.0
