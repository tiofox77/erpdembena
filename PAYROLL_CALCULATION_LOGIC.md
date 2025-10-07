# Lógica de Cálculo do Payroll - Documentação Completa

## 📊 Visão Geral

Ambas as modais (Individual e Batch) usam o **mesmo helper** (`PayrollCalculatorHelper`) para calcular todos os campos. A única diferença é como acessam os dados:

- **Modal Individual**: Propriedades diretas → `$basic_salary`, `$gross_salary`
- **Modal Batch**: Array → `$calculatedData['basic_salary']`, `$calculatedData['gross_salary']`

## 🔧 Fluxo de Cálculo

### 1. Inicialização
```php
$calculator = new PayrollCalculatorHelper($employee, $startDate, $endDate);
$calculator->loadAllEmployeeData();
$calculator->setChristmasSubsidy($christmas_subsidy);
$calculator->setVacationSubsidy($vacation_subsidy);
$calculator->setAdditionalBonus($additional_bonus);
$results = $calculator->calculate();
```

---

## 📋 Campos Calculados (Em Ordem de Exibição)

### 1️⃣ **Salário Base** (`basic_salary`)
```php
$basic_salary = $employee->base_salary
```
- Vem direto do perfil do empregado
- Não sofre cálculos

---

### 2️⃣ **Subsídio de Alimentação** (`food_benefit`)
```php
$food_benefit = $employee->food_benefit
```
- Vem direto do perfil do empregado
- **Isento de imposto até 30.000 AOA**
- Se `is_food_in_kind = true`, é 100% tributável

---

### 3️⃣ **Subsídio de Natal** (`christmas_subsidy_amount`)
```php
$christmas_subsidy_amount = $christmas_subsidy ? ($basic_salary * 0.5) : 0
```
- **50% do salário base**
- Só é pago se checkbox marcado
- **Tributável 100%**

---

### 4️⃣ **Subsídio de Férias** (`vacation_subsidy_amount`)
```php
$vacation_subsidy_amount = $vacation_subsidy ? ($basic_salary * 0.5) : 0
```
- **50% do salário base**
- Só é pago se checkbox marcado
- **Tributável 100%**

---

### 5️⃣ **Subsídio de Transporte** (`transport_allowance`) - **PROPORCIONAL**
```php
$transport_benefit_full = $employee->transport_benefit;
$monthly_working_days = HRSetting::get('monthly_working_days', 22);
$transport_allowance = ($transport_benefit_full / $monthly_working_days) * $present_days;
```

**Detalhes:**
- `transport_benefit_full`: Benefício total mensal
- `present_days`: Dias que o funcionário esteve presente
- `transport_discount`: Desconto por faltas
- `exempt_transport`: Isento até 30.000 AOA
- `taxable_transport`: Excesso acima de 30k é tributável

**Exemplo:**
- Benefício total: 30.000 AOA
- Dias úteis: 22
- Dias presentes: 20
- Cálculo: `(30.000 / 22) * 20 = 27.272,73 AOA`

---

### 6️⃣ **Bónus do Perfil** (`bonus_amount`)
```php
$bonus_amount = $employee->bonus_amount ?? 0
```
- Vem do perfil do empregado
- **Tributável 100%**

---

### 7️⃣ **Bónus Adicional** (`additional_bonus_amount`)
```php
$additional_bonus_amount = $additional_bonus ?? 0
```
- Definido manualmente na modal
- **Tributável 100%**

---

### 8️⃣ **Horas Extras** (`total_overtime_amount`)
```php
$overtime_records = OvertimeRecord::where('employee_id', $employee_id)
    ->whereBetween('date', [$startDate, $endDate])
    ->where('status', 'approved')
    ->get();

$total_overtime_amount = $overtime_records->sum('amount');
```
- Soma de todas as horas extras aprovadas no período
- **Tributável 100%**

---

## 💰 Salários Calculados

### 9️⃣ **Salário Bruto / Main Salary** (`gross_salary` / `main_salary`)
```php
$main_salary = $basic_salary 
    + $food_benefit 
    + $transport_allowance 
    + $bonus_amount 
    + $additional_bonus_amount 
    + $total_overtime_amount 
    + $christmas_subsidy_amount 
    + $vacation_subsidy_amount;

$gross_salary = $main_salary;
```
- **Soma de TODOS os rendimentos**
- **Antes de qualquer dedução**

---

### 🔟 **Base Tributável IRT** (`irt_base` / `base_irt_taxable_amount`)
```php
// Cálculo do INSS 3%
$inss_base = $basic_salary 
    + $food_benefit 
    + $transport_allowance 
    + $bonus_amount 
    + $additional_bonus_amount 
    + $christmas_subsidy_amount 
    + $vacation_subsidy_amount;

$inss_3_percent = $inss_base * 0.03;

// Base IRT = Rendimentos tributáveis - INSS
$taxable_food = max(0, $food_benefit - 30000);
$taxable_transport = max(0, $transport_allowance - 30000);

$irt_base = $basic_salary 
    + $taxable_food 
    + $taxable_transport 
    + $bonus_amount 
    + $additional_bonus_amount 
    + $total_overtime_amount 
    + $christmas_subsidy_amount 
    + $vacation_subsidy_amount 
    - $inss_3_percent;
```

**Regra importante:**
- Alimentação e Transporte: Isentos até 30k cada
- Só o excesso é tributável

---

## 💸 Deduções

### 1️⃣1️⃣ **IRT** (`irt`)
```php
// Aplicar escalões progressivos de IRT
$irt = calculateIRT($irt_base);
```

**Escalões (Tabela de IRT)**:
- Até 70k: Isento
- 70k - 100k: Taxa X%
- 100k - 150k: Taxa Y%
- Etc...

---

### 1️⃣2️⃣ **INSS 3%** (`inss_3_percent`)
```php
$inss_base = $basic_salary 
    + $food_benefit 
    + $transport_allowance 
    + $bonus_amount 
    + $additional_bonus_amount 
    + $christmas_subsidy_amount 
    + $vacation_subsidy_amount;

$inss_3_percent = $inss_base * 0.03;
```
- **Pago pelo funcionário**
- Incide sobre: Salário + Todos os benefícios e subsídios

---

### 1️⃣3️⃣ **INSS 8%** (`inss_8_percent`) - **ILUSTRATIVO**
```php
$inss_8_percent = $inss_base * 0.08;
```
- **Pago pela EMPRESA**
- Apenas ilustrativo na folha
- **NÃO deduzido do salário do funcionário**

---

### 1️⃣4️⃣ **Dedução por Faltas** (`absence_deduction`)
```php
$daily_rate = $basic_salary / $monthly_working_days;
$absence_deduction = $absent_days * $daily_rate;
```

**Cálculo:**
- Se funcionário faltou 26 dias
- E salário base é 69.900 AOA
- Dias úteis: 22
- Taxa diária: 69.900 / 22 = 3.177,27 AOA
- Dedução: 26 * 3.177,27 = **82.609,02 AOA** ❌ (maior que salário!)

**CORREÇÃO APLICADA:**
```php
// Se não há registros explícitos de faltas, calcular automaticamente
$implicit_absences = $absent_days - $explicit_absences;
$absence_deduction = $implicit_absences * $daily_rate;
```

---

### 1️⃣5️⃣ **Dedução por Atrasos** (`late_deduction`)
```php
$late_deduction = $late_arrivals * $hourly_rate;
```
- Cada atraso = 1 hora de salário
- Se houver 5 atrasos e taxa horária é 397,16 AOA
- Dedução: 5 * 397,16 = **1.985,80 AOA**

---

### 1️⃣6️⃣ **Adiantamentos** (`advance_deduction`)
```php
$salary_advances = SalaryAdvance::where('employee_id', $employee_id)
    ->where('status', 'approved')
    ->whereBetween('payment_date', [$startDate, $endDate])
    ->get();

$advance_deduction = $salary_advances->sum('monthly_deduction_amount');
```
- Soma dos adiantamentos aprovados no período

---

### 1️⃣7️⃣ **Descontos Salariais** (`total_salary_discounts`)
```php
$salary_discounts = SalaryDiscount::where('employee_id', $employee_id)
    ->where('is_active', true)
    ->whereBetween('effective_date', [$startDate, $endDate])
    ->get();

$total_salary_discounts = $salary_discounts->sum('amount');
```
- Descontos diversos (multas, empréstimos, etc.)

---

### 1️⃣8️⃣ **Total de Deduções** (`total_deductions`)
```php
$total_deductions = $inss_3_percent 
    + $irt 
    + $advance_deduction 
    + $total_salary_discounts 
    + $late_deduction 
    + $absence_deduction;
```

---

## 💵 Salário Líquido Final

### 1️⃣9️⃣ **Net Salary** (`net_salary`)
```php
$net_salary = $gross_salary - $total_deductions;
```
- **Valor que o funcionário recebe efetivamente**

---

## 🔄 Dados de Presença

### **Present Days** (`present_days`)
```php
$present_days = Attendance::whereIn('status', ['present', 'late', 'half_day'])
    ->whereBetween('date', [$startDate, $endDate])
    ->count();
```

### **Absent Days** (`absent_days`)
```php
$absent_days = $total_working_days - $present_days;
```

### **Late Arrivals** (`late_arrivals`)
```php
$late_arrivals = Attendance::where('status', 'late')
    ->whereBetween('date', [$startDate, $endDate])
    ->count();
```

### **Total Hours** (`total_attendance_hours`)
```php
$total_attendance_hours = $attendances->sum(function($att) {
    if ($att->time_in && $att->time_out) {
        return Carbon::parse($att->time_in)->diffInHours($att->time_out);
    }
    return 8; // Standard work day
});
```

---

## 📌 Resumo de Mapeamento

| Campo na Modal Individual | Campo no Helper | Campo na Modal Batch |
|--------------------------|-----------------|---------------------|
| `$basic_salary` | `basic_salary` | `$calculatedData['basic_salary']` |
| `$gross_salary` | `gross_salary` | `$calculatedData['gross_salary']` |
| `$main_salary` | `main_salary` | `$calculatedData['main_salary']` |
| `$transport_allowance` | `transport_allowance` | `$calculatedData['transport_allowance']` |
| `$meal_allowance` | `food_benefit` | `$calculatedData['food_benefit']` |
| `$total_deductions` | `total_deductions` | `$calculatedData['total_deductions']` |
| `$net_salary` | `net_salary` | `$calculatedData['net_salary']` |
| `$this->irtCalculationDetails` | `irt_details` | `$calculatedData['irt_details']` |
| `$this->getFullTransportBenefit()` | `transport_benefit_full` | `$calculatedData['transport_benefit_full']` |
| `$this->getTransportDiscountAmount()` | `transport_discount` | `$calculatedData['transport_discount']` |

---

## ✅ Conclusão

**AMBAS AS MODAIS USAM O MESMO CÁLCULO!**

A única diferença é:
- **Individual**: Armazena em propriedades do componente
- **Batch**: Armazena em array `$calculatedData`

Todos os cálculos são feitos pelo **`PayrollCalculatorHelper`**, garantindo consistência total entre as duas interfaces.
