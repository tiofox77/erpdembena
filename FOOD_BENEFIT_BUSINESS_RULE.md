# 🍽️ Regra de Negócio: Food Benefit (Subsídio de Alimentação)

## ✅ **CONFIRMADO: Food NÃO é Pago ao Funcionário**

**Data da Confirmação:** 2025-10-07

---

## 📋 Regra de Negócio

### **Food Benefit é APENAS Ilustrativo:**

O subsídio de alimentação **NÃO é entregue em dinheiro ao funcionário**, independente de estar marcado como "in kind" ou "cash".

### **Por Que Aparece nos Cálculos?**

O food benefit aparece no Gross Salary **APENAS para fins de tributação:**

1. ✅ **Entra no Gross Salary** → Para calcular INSS e IRT
2. ✅ **Entra no INSS 3% base** → Tributa sobre food
3. ✅ **Tem isenção de 30k no IRT** → Parte isenta de IRT
4. ✅ **É DEDUZIDO do Net Salary** → Não é pago ao funcionário

---

## 🧮 Fluxo do Food no Cálculo

### **Exemplo: Food = 29,000 AOA**

```
1. Gross Salary (para tributação):
   = Basic + Food + Transport + ...
   = 69,900 + 29,000 + 0 + ...
   = 98,900 AOA ✅ (food incluído)

2. INSS 3%:
   Base = Basic + Food + Transport + Overtime
   = 98,900 * 0.03 = 2,967 AOA ✅ (tributa sobre food)

3. Base IRT:
   = Gross Salary - INSS - Isenção Food (30k) - Isenção Transport (30k)
   = 98,900 - 2,967 - 29,000 - 0
   = 66,933 AOA ✅ (food tem isenção)

4. Main Salary (intermediário):
   = Basic + Transport + Food + Overtime - Absence
   = 69,900 + 0 + 29,000 + 0 - 69,900
   = 29,000 AOA ✅ (ainda tem food)

5. Total Deductions:
   = INSS + IRT + ... + FOOD DEDUCTION
   = 2,967 + 0 + ... + 29,000
   = 31,967 AOA ✅ (food deduzido aqui)

6. Net Salary (valor a pagar):
   = Main Salary - Total Deductions
   = 29,000 - 31,967
   = -2,967 → 0.00 AOA ✅ (food NÃO é pago)
```

---

## 🔴 **NÃO Confundir Com:**

### **❌ ERRADO:** "Food in kind = não pago, Food cash = pago"
### ✅ **CERTO:** "Food NUNCA é pago, é apenas ilustrativo"

O campo `is_food_in_kind` pode existir, mas **AMBOS não são pagos**:
- **Food in Kind:** Funcionário recebe refeição física (não dinheiro)
- **Food Cash:** Funcionário NÃO recebe nada (apenas ilustrativo nos cálculos)

---

## 🎯 Implicações no Código

### **1. Helper deve SEMPRE deduzir food:**

```php
public function calculateNetSalary(): float
{
    $grossSalary = $this->calculateGrossSalary();
    $totalDeductions = $this->calculateTotalDeductions();
    
    // Food SEMPRE é deduzido (não é pago ao funcionário)
    $foodDeduction = $this->mealAllowance;
    
    return max(0.0, $grossSalary - $totalDeductions - $foodDeduction);
}
```

### **2. Total Deductions deve incluir food:**

```php
public function calculateTotalDeductions(): float
{
    $inss = $this->calculateINSS();
    $irt = $this->calculateIRT();
    $advances = $this->advanceDeduction;
    $discounts = $this->totalSalaryDiscounts;
    $late = $this->lateDeduction;
    $absence = $this->absenceDeduction;
    
    // Food SEMPRE é dedução
    $foodDeduction = $this->mealAllowance;
    
    return $inss + $irt + $advances + $discounts + $late + $absence + $foodDeduction;
}
```

### **3. Modal Individual está CORRETO:**

```php
public function getTotalDeductionsCalculatedProperty(): float
{
    // ... outras deduções ...
    
    // Food handling
    $isFoodInKind = $this->is_food_in_kind;
    $foodBenefit = $this->selectedEmployee->food_benefit;
    $foodInKind = $isFoodInKind ? $foodBenefit : 0.0;
    $foodCash = $isFoodInKind ? 0.0 : $foodBenefit;
    
    return $inss + $irt + ... + $foodInKind + $foodCash;
    // ✅ SOMA food (in kind OU cash) = deduz food sempre
}
```

---

## 📊 Casos de Uso

### **Caso 1: 0 Ausências**
```
Basic: 69,900
Food: 29,000
Absence: 0

Gross Salary: 98,900
INSS 3%: 2,967
Main Salary: 98,900
Total Deductions: 2,967 + 29,000 = 31,967
Net Salary: 98,900 - 31,967 = 66,933 AOA ✅
```

### **Caso 2: 26 Ausências (100%)**
```
Basic: 69,900
Food: 29,000
Absence: 69,900

Gross Salary: 98,900
INSS 3%: 2,967
Main Salary: 29,000 (food restante)
Total Deductions: 2,967 + 29,000 = 31,967
Net Salary: 29,000 - 31,967 = 0.00 AOA ✅
```

### **Caso 3: 13 Ausências (50%)**
```
Basic: 69,900
Food: 29,000
Absence: 34,950

Gross Salary: 98,900
INSS 3%: 2,967
Main Salary: 63,950 (69,900 + 29,000 - 34,950)
Total Deductions: 2,967 + 29,000 + ? (IRT) = ~32,000
Net Salary: 63,950 - 32,000 = ~31,950 AOA ✅
```

---

## ✅ Checklist de Implementação

- [x] 1. Confirmar regra de negócio ✅ CONFIRMADO
- [ ] 2. Corrigir helper para SEMPRE deduzir food
- [ ] 3. Atualizar `calculateNetSalary()` no helper
- [ ] 4. Atualizar `calculateTotalDeductions()` no helper
- [ ] 5. Remover lógica de `isFoodInKind` no Net Salary
- [ ] 6. Testar casos: 0, 13, 26 ausências
- [ ] 7. Replicar na modal batch
- [ ] 8. Atualizar documentação

---

## 🎯 Conclusão

**Food Benefit é um valor ILUSTRATIVO que:**
- ✅ Entra nos cálculos de impostos (INSS, IRT)
- ✅ Tem isenção fiscal de até 30k no IRT
- ❌ **NUNCA é pago ao funcionário**
- ✅ **SEMPRE é deduzido do Net Salary**

**Esta é uma REGRA DE NEGÓCIO confirmada, não um bug!**

---

**Atualizado:** 2025-10-07  
**Status:** Regra confirmada e documentada ✅
