# 🔍 Análise do Screenshot - Caso de 26 Dias Ausentes

## 📊 Dados do Screenshot

### **Valores Exibidos:**
- **Basic Salary:** 69,900.00 AOA
- **Christmas Subsidy:** 0.00 AOA
- **Vacation Subsidy:** 0.00 AOA
- **Transport Allowance:** 0.00 AOA (30,000 total - 30,000 desconto = 0)
- **Gross Salary:** 98,900.00 AOA
- **Base IRT Taxable:** 66,933.00 AOA
- **INSS 3%:** -2,967.00 AOA
- **INSS 8%:** 7,912.00 AOA (ilustrativo)
- **Absence Deductions (26 days):** -69,900.00 AOA
- **Absence Discount (26 days):** -0.00 AOA
- **Main Salary:** 29,000.00 AOA
- **Total Deductions:** 31,967.00 AOA
- **Net Salary:** 0.00 AOA

### **Dados de Presença:**
- **Total Working Days:** 26 dias
- **Present Days:** 0 dias
- **Absent Days:** 26 dias
- **Late Days:** 0 dias

---

## 🧮 Reconstrução dos Cálculos

### **1. Basic Salary**
```
69,900.00 AOA (do perfil do empregado)
```

### **2. Food Benefit**
```
29,000.00 AOA (não é in kind, então é CASH)
```

### **3. Transport Allowance**
```
Total Benefício: 30,000.00 AOA
Dias Presentes: 0 / 26
Proporcional: 30,000 * (0/26) = 0.00 AOA
Desconto: 30,000.00 AOA
Valor a Pagar: 0.00 AOA
```

### **4. Gross Salary**
```
Basic + Food + Transport + Overtime + Bonus + Christmas + Vacation
= 69,900 + 29,000 + 0 + 0 + 0 + 0 + 0
= 98,900.00 AOA ✅
```

### **5. INSS 3%**
```
Base: Basic + Transport + Food + Overtime
= 69,900 + 0 + 29,000 + 0 = 98,900

INSS 3% = 98,900 * 0.03 = 2,967.00 AOA ✅
```

### **6. Base IRT Taxable Amount**
```
Gross Salary: 98,900.00
- INSS 3%: -2,967.00
- Exempt Transport (até 30k): -0.00 (porque transport pago = 0)
- Exempt Food (até 30k): -29,000.00

Base IRT = 98,900 - 2,967 - 0 - 29,000 = 66,933.00 AOA ✅
```

### **7. IRT (Imposto)**
```
Base: 66,933.00 AOA
Bracket 1: 0 - 70,000 (Isento)
IRT = 0.00 AOA ✅
```

### **8. INSS 8% (Ilustrativo)**
```
Base: 98,900 (mesma do INSS 3%)
INSS 8% = 98,900 * 0.08 = 7,912.00 AOA ✅
```

### **9. Absence Deductions**
```
Daily Rate = 69,900 / 22 = 3,177.27 AOA/dia
Absent Days: 26 dias
Absence Deduction = 3,177.27 * 26 = 82,609.09 AOA

❌ MAS mostra -69,900.00 AOA

TEORIA: Dedução limitada ao salário base?
Absence Deduction = min(69,900, 82,609.09) = 69,900.00 AOA ✅
```

### **10. Main Salary**
```
FÓRMULA (Modal Individual):
Basic + Transport + Food(cash) + Overtime + NightShift + Other - Absence

= 69,900 + 0 + 29,000 + 0 + 0 + 0 - 69,900
= 29,000.00 AOA ✅

CONCLUSÃO: Main Salary = Food Benefit (porque tudo o resto foi deduzido por faltas)
```

### **11. Total Deductions**
```
EXIBIDO: 31,967.00 AOA

VERIFICAÇÃO:
INSS 3%: 2,967.00
IRT: 0.00
Advances: 0.00
Discounts: 0.00
Union: 0.00
U Fund: 0.00
Loans: 0.00
Food In Kind: 0.00
Food Cash: 29,000.00 ❓

TOTAL = 2,967 + 0 + 0 + 0 + 0 + 0 + 0 + 0 + 29,000 = 31,967.00 ✅

⚠️ ATENÇÃO: Food Cash está sendo DEDUZIDO no Total Deductions!
Por quê? Porque ele não entra no Net Salary para pagamento?
```

### **12. Net Salary**
```
FÓRMULA (Modal Individual):
grossForTax - totalDeductions

grossForTax = ?
totalDeductions = 31,967.00

OPÇÃO 1: Se grossForTax = Gross Salary
= 98,900 - 31,967 = 66,933.00 ❌ (mas mostra 0.00)

OPÇÃO 2: Se grossForTax = Main Salary
= 29,000 - 31,967 = -2,967.00 → max(0, -2,967) = 0.00 ✅

CONCLUSÃO: O cálculo usa Main Salary, não Gross Salary!
Net Salary = max(0, MainSalary - SomeDeductions)
```

---

## 🔴 DESCOBERTA CRÍTICA

### **A Lógica Real do Net Salary:**

```php
// PASSO 1: Calcular Main Salary (já deduz ausências)
$mainSalary = $basic + $transportCash + $foodCash + $overtime - $absence
            = 69,900 + 0 + 29,000 + 0 - 69,900
            = 29,000.00

// PASSO 2: Calcular outras deduções (SEM incluir absence de novo)
$otherDeductions = $inss + $irt + $advances + $discounts + $union + ...
                 = 2,967 + 0 + 0 + 0 + 0 + ...
                 = 2,967.00

// PASSO 3: Deduzir food cash (porque não é pago?)
$foodCashDeduction = 29,000.00

// PASSO 4: Net Salary
$netSalary = max(0, $mainSalary - $otherDeductions - $foodCashDeduction)
           = max(0, 29,000 - 2,967 - 29,000)
           = max(0, -2,967)
           = 0.00 ✅
```

### **Por Que Food Cash é Deduzido?**

Possibilidades:
1. **Food In Kind está TRUE:** Mesmo sendo cash, está marcado como in kind
2. **Lógica Duplicada:** Food está sendo deduzido duas vezes
3. **Condicional Especial:** Quando absent_days = total_working_days, food não é pago

---

## 🎯 Perguntas Para Resolver

### 1. **Por que Food Cash (29,000) está no Total Deductions?**
   - Está marcado como "in kind"?
   - É uma regra de negócio?
   - Erro de implementação?

### 2. **Por que Absence Discount mostra 0.00?**
   - Já está incluído em "Absence Deductions"?
   - São dois campos diferentes?

### 3. **O que é "Gross For Tax"?**
   - É igual a Gross Salary?
   - É igual a Main Salary?
   - É Gross Salary - algo?

---

## 📋 Checklist de Verificação

- [ ] 1. Verificar se `is_food_in_kind` está TRUE ou FALSE
- [ ] 2. Verificar código de `getTotalDeductionsCalculatedProperty()`
- [ ] 3. Verificar código de `getCalculatedNetSalaryProperty()`
- [ ] 4. Entender diferença entre "Absence Deductions" e "Absence Discount"
- [ ] 5. Confirmar fórmula de `grossForTax`
- [ ] 6. Verificar se food está sendo deduzido duas vezes
- [ ] 7. Entender regra de negócio: food não é pago se 100% ausente?

---

## 🔧 Correções Propostas

### **SE food NÃO deveria ser deduzido:**

```php
public function getTotalDeductionsCalculatedProperty(): float
{
    $inss = $this->getCalculatedInssProperty();
    $irt = $this->getCalculatedIrtProperty();
    $advance = $this->advance_deduction;
    $otherDiscounts = $this->total_salary_discounts;
    $union = $this->union_deduction;
    $uFund = $this->u_fund_ded;
    $loans = $this->loan_installments;
    
    // Food handling - APENAS se in kind
    $isFoodInKind = $this->is_food_in_kind;
    $foodBenefit = $this->selectedEmployee->food_benefit;
    $foodInKind = $isFoodInKind ? $foodBenefit : 0.0;
    
    return $inss + $irt + $advance + $otherDiscounts + $union + $uFund + $loans + $foodInKind;
    // ❌ REMOVIDO: + $foodCash
}
```

### **SE food DEVE ser deduzido quando 100% ausente:**

```php
public function getTotalDeductionsCalculatedProperty(): float
{
    // ... mesmos cálculos acima ...
    
    // Food handling
    $isFoodInKind = $this->is_food_in_kind;
    $foodBenefit = $this->selectedEmployee->food_benefit;
    
    // Se 100% ausente, não paga food mesmo se cash
    $totalWorkingDays = $this->total_working_days ?? 22;
    $absentDays = $this->absent_days ?? 0;
    $is100PercentAbsent = $absentDays >= $totalWorkingDays;
    
    $foodDeduction = 0;
    if ($isFoodInKind) {
        $foodDeduction = $foodBenefit; // sempre deduz se in kind
    } elseif ($is100PercentAbsent) {
        $foodDeduction = $foodBenefit; // deduz se 100% ausente
    }
    
    return $inss + $irt + $advance + $otherDiscounts + $union + $uFund + $loans + $foodDeduction;
}
```

---

## ✅ Próximos Passos

1. **Confirmar regra de negócio do Food Benefit**
2. **Verificar código atual de Total Deductions**
3. **Confirmar se is_food_in_kind está correto no banco**
4. **Replicar lógica EXATA no Helper**
5. **Testar com outros casos (0 ausências, 13 ausências, etc)**

---

**Data:** 2025-10-07  
**Caso:** 26 dias ausentes, Net Salary = 0.00  
**Status:** Em análise - Aguardando confirmação de regras de negócio
