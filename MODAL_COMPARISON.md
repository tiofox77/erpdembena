# Comparação das Modals - Payroll Summary

## Modal Individual (_ProcessPayrollModal.blade.php)

### Campos Usados no Summary:
1. **Basic Salary**: `$basic_salary`
2. **Christmas Subsidy**: `($basic_salary ?? 0) * 0.5` (calculado inline)
3. **Vacation Subsidy**: `($basic_salary ?? 0) * 0.5` (calculado inline)
4. **Transport Allowance**: `$transport_allowance` (proporcional)
   - Detalhes: `$this->getFullTransportBenefit()`, `$this->present_days`, `$this->getTransportDiscountAmount()`, `$this->getProportionalTransportOnly()`, `$this->getExemptTransportAllowance()`, `$this->getTaxableTransportAllowance()`
5. **Employee Profile Bonus**: `$bonus_amount`
6. **Additional Payroll Bonus**: `$additional_bonus_amount`
7. **Overtime**: `$total_overtime_amount`
8. **Main Salary (Gross)**: Calculado inline com todos os componentes
9. **Base IRT Taxable Amount**: Calculado inline com `@php`
10. **Deductions**:
    - IRT: `$this->irtCalculationDetails['total_irt']`
    - INSS 3%: Calculado inline
    - INSS 8%: Calculado inline
    - Absence deductions: `$this->absenceDeductionAmount`
    - Absence discount: `$absence_deduction`
11. **Main Salary**: `$main_salary`
12. **Total Deductions**: Soma de todas as deduções
13. **Net Salary**: `$this->calculatedNetSalary`

## Modal Batch (_edit-item-modal-complete.blade.php)

### Campos Usados no Summary:
1. **Basic Salary**: `$calculatedData['basic_salary']`
2. **Christmas Subsidy**: `$calculatedData['christmas_subsidy_amount']`
3. **Vacation Subsidy**: `$calculatedData['vacation_subsidy_amount']`
4. **Transport Allowance**: `$calculatedData['transport_allowance']`
5. **Profile Bonus**: `$calculatedData['profile_bonus']`
6. **Overtime**: `$calculatedData['overtime_amount']`
7. **Gross Salary**: `$calculatedData['gross_salary']`
8. **Base IRT Taxable Amount**: `$calculatedData['base_irt_taxable_amount']`
9. **Deductions**:
    - IRT: `$calculatedData['deductions_irt']`
    - INSS 3%: `$calculatedData['inss_3_percent']`
    - INSS 8%: `$calculatedData['inss_8_percent']`
    - Advances: `$calculatedData['advance_deduction']`
    - Discounts: `$calculatedData['total_salary_discounts']`
    - Absence: `$calculatedData['absence_deduction_amount']`
    - Late: `$calculatedData['late_deduction']`
10. **Total Deductions**: `$calculatedData['total_deductions']`
11. **Net Salary**: `$calculatedData['net_salary']`

## ✅ Solução: Usar Helper em Ambas

A modal individual deve usar os mesmos campos do helper que a modal batch usa.

### Campos do Helper a Usar:
```php
$calculatedData = [
    'basic_salary',
    'christmas_subsidy_amount',
    'vacation_subsidy_amount',
    'transport_allowance',
    'transport_benefit_full',
    'transport_discount',
    'exempt_transport',
    'taxable_transport',
    'bonus_amount',
    'additional_bonus_amount',
    'total_overtime_amount',
    'gross_salary',
    'main_salary',
    'base_irt_taxable_amount',
    'inss_3_percent',
    'inss_8_percent',
    'irt',
    'advance_deduction',
    'total_salary_discounts',
    'late_deduction',
    'absence_deduction',
    'total_deductions',
    'net_salary',
    'present_days',
    'absent_days',
    'late_arrivals',
]
```

## 🎯 Ação Necessária

A modal do batch (`_edit-item-modal-complete.blade.php`) já está usando os campos corretos do helper.

**Conclusão**: A modal do batch está correta! Ela usa `$calculatedData` do helper, que é a abordagem certa.
