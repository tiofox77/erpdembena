# 🔍 Auditoria: HR Settings no Payroll

## ⚙️ Settings Disponíveis no Banco

### **Labor Rules (Regras Laborais):**
- `working_hours_per_week` = 44
- `working_days_per_week` = 5
- `working_hours_per_day` = 8 (calculado ou default)
- `working_days_per_month` = 22
- `monthly_working_days` = 22
- `overtime_multiplier_weekday` = 1.5
- `overtime_multiplier_weekend` = 2.0
- `overtime_multiplier_holiday` = 2.5
- `overtime_first_hour_weekday` = 1.25
- `overtime_additional_hours_weekday` = 1.375
- `overtime_daily_limit` = 2
- `overtime_monthly_limit` = 48
- `overtime_yearly_limit` = 200

### **Tax (Impostos):**
- `inss_employee_rate` = 3%
- `inss_employer_rate` = 8%
- `min_salary_tax_exempt` = 70,000

### **Benefits (Benefícios):**
- `subsidy_transport` = 15,000
- `subsidy_meal` = 30,000
- `vacation_subsidy_percentage` = 50%
- `christmas_subsidy_percentage` = 50%

---

## ✅ Settings USADAS no Helper

```php
protected function loadHRSettings(): void
{
    $this->hrSettings = [
        'working_hours_per_day' => HRSetting::get('working_hours_per_day', 8),
        'working_days_per_month' => HRSetting::get('working_days_per_month', 22),
        'monthly_working_days' => HRSetting::get('monthly_working_days', 22),
        'vacation_subsidy_percentage' => HRSetting::get('vacation_subsidy_percentage', 50),
        'christmas_subsidy_percentage' => HRSetting::get('christmas_subsidy_percentage', 50),
    ];
}
```

---

## ❌ Settings FALTANDO no Helper

### **1. INSS Rates:**
- `inss_employee_rate` (3%) - ⚠️ HARDCODED no código
- `inss_employer_rate` (8%) - ⚠️ HARDCODED no código

### **2. Overtime Multipliers:**
- `overtime_multiplier_weekday` (1.5)
- `overtime_multiplier_weekend` (2.0)
- `overtime_multiplier_holiday` (2.5)
- `overtime_first_hour_weekday` (1.25)
- `overtime_additional_hours_weekday` (1.375)

### **3. Isenções Fiscais:**
- `min_salary_tax_exempt` (70,000 para IRT)
- Isenções transport/food (30k) - ⚠️ HARDCODED

---

## 🔧 Correção Necessária

### **Atualizar `loadHRSettings()`:**

```php
protected function loadHRSettings(): void
{
    $this->hrSettings = [
        // Horas e dias
        'working_hours_per_day' => (float) HRSetting::get('working_hours_per_day', 8),
        'working_days_per_month' => (int) HRSetting::get('working_days_per_month', 22),
        'monthly_working_days' => (int) HRSetting::get('monthly_working_days', 22),
        
        // Subsídios
        'vacation_subsidy_percentage' => (float) HRSetting::get('vacation_subsidy_percentage', 50),
        'christmas_subsidy_percentage' => (float) HRSetting::get('christmas_subsidy_percentage', 50),
        
        // INSS - ADICIONAR
        'inss_employee_rate' => (float) HRSetting::get('inss_employee_rate', 3),
        'inss_employer_rate' => (float) HRSetting::get('inss_employer_rate', 8),
        
        // IRT - ADICIONAR
        'min_salary_tax_exempt' => (float) HRSetting::get('min_salary_tax_exempt', 70000),
        'transport_tax_exempt' => (float) HRSetting::get('transport_tax_exempt', 30000),
        'food_tax_exempt' => (float) HRSetting::get('food_tax_exempt', 30000),
        
        // Overtime - ADICIONAR
        'overtime_multiplier_weekday' => (float) HRSetting::get('overtime_multiplier_weekday', 1.5),
        'overtime_multiplier_weekend' => (float) HRSetting::get('overtime_multiplier_weekend', 2.0),
        'overtime_multiplier_holiday' => (float) HRSetting::get('overtime_multiplier_holiday', 2.5),
        'overtime_first_hour_weekday' => (float) HRSetting::get('overtime_first_hour_weekday', 1.25),
        'overtime_additional_hours_weekday' => (float) HRSetting::get('overtime_additional_hours_weekday', 1.375),
    ];
}
```

### **Usar nos Cálculos:**

```php
// INSS
public function calculateINSS(): float
{
    $rate = $this->hrSettings['inss_employee_rate'] / 100;
    return round($this->calculateMainSalary() * $rate, 2);
}

// IRT com isenções dinâmicas
public function calculateIRTBase(): float
{
    $exemptTransport = min($this->hrSettings['transport_tax_exempt'], $transportCash);
    $exemptFood = min($this->hrSettings['food_tax_exempt'], $foodCash);
    // ...
}

// Overtime com multipliers corretos
protected function calculateOvertimeAmount(array $overtimeRecords): float
{
    foreach ($overtimeRecords as $record) {
        $multiplier = match($record->overtime_type) {
            'weekday' => $this->hrSettings['overtime_multiplier_weekday'],
            'weekend' => $this->hrSettings['overtime_multiplier_weekend'],
            'holiday' => $this->hrSettings['overtime_multiplier_holiday'],
            default => 1.5
        };
        // ...
    }
}
```

---

## ✅ Checklist

- [ ] Adicionar todos os settings faltantes no `loadHRSettings()`
- [ ] Remover hardcoded 0.03 e 0.08 (INSS)
- [ ] Remover hardcoded 30000 (isenções)
- [ ] Usar multipliers dinâmicos para overtime
- [ ] Testar com diferentes valores de settings
- [ ] Atualizar documentação

---

**Benefício:** Sistema totalmente configurável via HR Settings sem precisar alterar código!
