# 📋 PAYROLL BATCH - Funcionalidades Faltantes

## ⚠️ Comparação: Payroll Individual vs Payroll Batch

A modal de processamento individual (`_ProcessPayrollModal.blade.php`) possui **MUITO MAIS funcionalidades** que o Payroll Batch atual.

---

## 🔴 FUNCIONALIDADES FALTANTES NO BATCH

### 1. **ATTENDANCE DATA (Presenças)**
**Status:** ❌ **NÃO IMPLEMENTADO**

O que falta:
- ✅ Total de horas trabalhadas
- ✅ Dias presentes
- ✅ Dias ausentes (faltas)
- ✅ Atrasos (late arrivals)
- ✅ Taxa horária (hourly rate)
- ✅ Taxa diária (daily rate)
- ✅ Registros individuais de presença
- ✅ Pagamento por horas regulares

**Impacto:** Sem isso, não há desconto por faltas automático!

---

### 2. **OVERTIME (Horas Extras)**
**Status:** ❌ **NÃO IMPLEMENTADO**

O que falta:
- ✅ Total de horas extras
- ✅ Valor das horas extras
- ✅ Registros detalhados de overtime por data

**Impacto:** Horas extras não são calculadas automaticamente!

---

### 3. **SALARY ADVANCES (Adiantamentos)**
**Status:** ❌ **NÃO IMPLEMENTADO**

O que falta:
- ✅ Total de adiantamentos
- ✅ Valor da dedução mensal
- ✅ Parcelas restantes/totais
- ✅ Data de solicitação
- ✅ Motivo do adiantamento
- ✅ **REGRA:** Até 30k não tributável

**Impacto:** Adiantamentos não são descontados automaticamente!

---

### 4. **SALARY DISCOUNTS (Descontos)**
**Status:** ❌ **NÃO IMPLEMENTADO**

O que falta:
- ✅ Total de descontos
- ✅ Número de descontos ativos
- ✅ Tipo de desconto
- ✅ Motivo do desconto
- ✅ Parcelas restantes/totais
- ✅ Valor da parcela mensal

**Impacto:** Descontos não são aplicados automaticamente!

---

### 5. **SUBSÍDIOS (Subsidies)**
**Status:** ❌ **NÃO IMPLEMENTADO**

O que falta:
- ✅ Christmas Subsidy (50% do salário base)
- ✅ Vacation Subsidy (50% do salário base)
- ✅ Controle de quando aplicar (boolean toggles)

**Impacto:** Subsídios não são calculados!

---

### 6. **BENEFITS & ALLOWANCES (Benefícios)**
**Status:** ⚠️ **PARCIALMENTE IMPLEMENTADO**

O que existe:
- ✅ Gross Salary (básico)
- ✅ Net Salary

O que falta:
- ❌ **Food Benefit** (até 30k não tributável)
- ❌ **Transport Benefit** (proporcional aos dias trabalhados)
  - Cálculo proporcional: `(Benefício Total / 22 dias) × Dias Presentes`
  - Desconto por faltas
  - Parte isenta vs parte tributável (até 30k)
- ❌ **Employee Profile Bonus**
- ❌ **Additional Payroll Bonus**

---

### 7. **IRT CALCULATION (Cálculo de IRT)**
**Status:** ❌ **NÃO IMPLEMENTADO CORRETAMENTE**

O que falta:
- ✅ Cálculo por escalões progressivos
- ✅ MC (Matéria Coletável) = Gross Salary - INSS 3%
- ✅ Fixed Amount por escalão
- ✅ Tax on Excess (taxa sobre o excedente)
- ✅ Detalhes do escalão aplicado
- ✅ Descrição do escalão (ex: "Escalão 3: 15%")

**Exemplo de cálculo:**
```
MC = 150,000 AOA
Escalão 3: 100,001 - 150,000 (15%)
Fixed Amount: 7,000 AOA
Excess: 150,000 - 100,000 = 50,000 AOA
Tax on Excess: 50,000 × 15% = 7,500 AOA
Total IRT: 7,000 + 7,500 = 14,500 AOA
```

---

### 8. **INSS CALCULATION**
**Status:** ⚠️ **PARCIALMENTE IMPLEMENTADO**

O que existe:
- ✅ Total Deductions (genérico)

O que falta:
- ❌ **INSS 3%** (deduzido do funcionário)
  - Base: Salário Bruto + Food + Transport + Bônus + Subsídios
- ❌ **INSS 8%** (ilustrativo, pago pela empresa)
  - Mostrado mas não deduzido

---

### 9. **ABSENCE DEDUCTIONS (Deduções por Faltas)**
**Status:** ❌ **NÃO IMPLEMENTADO**

O que falta:
- ✅ Cálculo automático: `(Salário Base / 22 dias) × Dias Ausentes`
- ✅ Mostrar número de dias ausentes
- ✅ Valor deduzido

---

### 10. **LATE ARRIVAL DEDUCTIONS (Deduções por Atrasos)**
**Status:** ❌ **NÃO IMPLEMENTADO**

O que falta:
- ✅ Cálculo por número de atrasos
- ✅ Valor por atraso (configurável)
- ✅ Total de atrasos
- ✅ Total deduzido

---

## 📊 CAMPOS NECESSÁRIOS NA TABELA `payroll_batch_items`

### Campos Atuais:
```sql
- gross_salary
- net_salary
- total_deductions
- notes
```

### Campos Faltantes:
```sql
-- Attendance
- attendance_hours DECIMAL(8,2)
- present_days INT
- absent_days INT
- late_arrivals INT
- hourly_rate DECIMAL(10,2)
- regular_hours_pay DECIMAL(10,2)

-- Overtime
- overtime_hours DECIMAL(8,2)
- overtime_amount DECIMAL(10,2)

-- Subsídios
- christmas_subsidy BOOLEAN
- vacation_subsidy BOOLEAN
- christmas_subsidy_amount DECIMAL(10,2)
- vacation_subsidy_amount DECIMAL(10,2)

-- Benefits
- food_benefit DECIMAL(10,2)
- transport_allowance DECIMAL(10,2)
- employee_profile_bonus DECIMAL(10,2)
- additional_bonus DECIMAL(10,2)

-- Deductions Detalhadas
- irt_amount DECIMAL(10,2)
- irt_bracket_number INT
- inss_3_percent DECIMAL(10,2)
- inss_8_percent DECIMAL(10,2)
- salary_advances_deduction DECIMAL(10,2)
- salary_discounts_deduction DECIMAL(10,2)
- absence_deduction DECIMAL(10,2)
- late_arrival_deduction DECIMAL(10,2)

-- Totals
- total_earnings DECIMAL(10,2)
- total_taxable DECIMAL(10,2)
- total_non_taxable DECIMAL(10,2)
```

---

## 🎯 SOLUÇÃO PROPOSTA

### **Opção 1: Migration Completa** (RECOMENDADO)
1. Criar migration para adicionar todos os campos faltantes
2. Atualizar `PayrollBatch` service para calcular tudo automaticamente
3. Modificar modal de edição para permitir ajustes manuais
4. Integrar com tabelas:
   - `attendances` (presenças)
   - `overtime_records` (horas extras)
   - `salary_advances` (adiantamentos)
   - `salary_discounts` (descontos)
   - `irt_brackets` (escalões IRT)

### **Opção 2: JSON Fields** (RÁPIDO)
1. Adicionar campos JSON para armazenar dados detalhados:
   ```sql
   - attendance_data JSON
   - overtime_data JSON
   - advances_data JSON
   - discounts_data JSON
   - deductions_breakdown JSON
   ```
2. Mais flexível mas menos performático

---

## 🚀 PRÓXIMOS PASSOS

### **Fase 1: Estrutura de Dados**
- [ ] Criar migration com novos campos
- [ ] Atualizar model `PayrollBatchItem`
- [ ] Criar DTOs para organizar dados

### **Fase 2: Lógica de Cálculo**
- [ ] Criar `PayrollCalculationService`
- [ ] Implementar cálculo de IRT por escalões
- [ ] Implementar cálculo de INSS
- [ ] Implementar cálculo de faltas
- [ ] Implementar cálculo de horas extras
- [ ] Implementar cálculo de adiantamentos/descontos

### **Fase 3: Interface**
- [ ] Atualizar modal de edição com todos os campos
- [ ] Criar seções organizadas (Earnings, Deductions, etc.)
- [ ] Adicionar tooltips explicativos
- [ ] Mostrar cálculos intermediários (como na modal individual)

### **Fase 4: Integração**
- [ ] Buscar dados de `attendances`
- [ ] Buscar dados de `overtime_records`
- [ ] Buscar dados de `salary_advances`
- [ ] Buscar dados de `salary_discounts`
- [ ] Aplicar regras de negócio

---

## 💡 EXEMPLO DE FLUXO COMPLETO

```php
// 1. Criar Batch
$batch = PayrollBatch::create([...]);

// 2. Adicionar funcionários
foreach ($employees as $employee) {
    // 3. Buscar dados do período
    $attendance = Attendance::forEmployee($employee)->inPeriod($period)->get();
    $overtime = OvertimeRecord::forEmployee($employee)->inPeriod($period)->get();
    $advances = SalaryAdvance::activeFor($employee)->get();
    $discounts = SalaryDiscount::activeFor($employee)->get();
    
    // 4. Calcular componentes
    $calculator = new PayrollCalculationService($employee, $period);
    $calculator->setAttendance($attendance);
    $calculator->setOvertime($overtime);
    $calculator->setAdvances($advances);
    $calculator->setDiscounts($discounts);
    
    // 5. Calcular tudo
    $result = $calculator->calculate();
    
    // 6. Criar item do batch
    PayrollBatchItem::create([
        'payroll_batch_id' => $batch->id,
        'employee_id' => $employee->id,
        'gross_salary' => $result->grossSalary,
        'net_salary' => $result->netSalary,
        'irt_amount' => $result->irt,
        'inss_3_percent' => $result->inss3,
        'attendance_hours' => $result->attendanceHours,
        'absent_days' => $result->absentDays,
        'overtime_amount' => $result->overtimeAmount,
        // ... todos os outros campos
    ]);
}
```

---

## ⚠️ AVISO IMPORTANTE

**O Payroll Batch atual está MUITO simplificado!**

Está salvando apenas:
- Gross Salary
- Net Salary  
- Total Deductions

**Falta implementar:**
- 90% dos cálculos
- 95% das integrações
- 100% da lógica de negócio

**Tempo estimado para implementar tudo:** 3-5 dias de desenvolvimento

---

## 📞 DECISÃO NECESSÁRIA

**Você quer que eu implemente TODA essa funcionalidade agora?**

Ou prefere uma abordagem incremental:
1. ✅ Implementar IRT e INSS primeiro
2. ✅ Depois Attendance e Faltas
3. ✅ Depois Overtime
4. ✅ Depois Advances e Discounts
5. ✅ Por último, Subsídios

**Me diga como quer proceder!** 🚀
