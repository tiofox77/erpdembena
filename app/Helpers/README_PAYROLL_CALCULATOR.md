# PayrollCalculatorHelper - Documentação Completa

## 📋 Visão Geral

O **PayrollCalculatorHelper** é um serviço centralizado que contém toda a lógica de cálculo de folha de pagamento (payroll) do sistema ERP DEMBENA.

### Objetivo

Garantir que os cálculos de salário sejam **consistentes e reutilizáveis** em todo o sistema, eliminando duplicação de código e garantindo que:
- Pagamento individual de salário
- Pagamento em lote (batch)
- Processamento de payroll
- Relatórios e exportações

...todos usem a **mesma lógica de cálculo**.

## 📁 Arquivos Criados

```
app/Helpers/
├── PayrollCalculatorHelper.php                    # Helper principal
├── PayrollCalculatorHelper_USAGE.md              # Guia de uso detalhado
├── PayrollCalculatorHelper_INTEGRATION_EXAMPLE.php # Exemplos de integração
└── README_PAYROLL_CALCULATOR.md                   # Este arquivo
```

## 🎯 Funcionalidades

### ✅ Cálculos Implementados

1. **Salário Base e Taxa Horária**
   - Cálculo de taxa horária baseado em dias/horas úteis
   - Salário base proporcional

2. **Presença e Ausências**
   - Carregamento de dados de presença
   - Cálculo de dias presentes, ausentes e atrasos
   - Deduções por faltas e atrasos
   - Total de horas trabalhadas

3. **Horas Extras**
   - Carregamento de registros aprovados
   - Cálculo de valor total de horas extras

4. **Subsídios e Benefícios**
   - Subsídio de transporte proporcional (baseado em dias trabalhados)
   - Subsídio de alimentação
   - Subsídio de Natal (50% do salário base)
   - Subsídio de Férias (50% do salário base)
   - Bônus do perfil do empregado
   - Bônus adicional configurável

5. **Isenções Fiscais**
   - Transporte: até 30.000 AOA isento
   - Alimentação: até 30.000 AOA isento
   - Cálculo de valores tributáveis e isentos

6. **Impostos e Contribuições**
   - **INSS 3%**: Calculado sobre salário principal
   - **INSS 8%**: Ilustrativo (pago pelo empregador)
   - **IRT**: Calculado usando escalões progressivos
   - Detalhamento completo do cálculo de IRT

7. **Deduções**
   - Adiantamentos salariais (parcelas mensais)
   - Descontos salariais (parcelas mensais)
   - Deduções por atrasos
   - Deduções por faltas
   - Licenças não remuneradas

8. **Salário Líquido**
   - Cálculo final considerando todas as componentes

## 🔧 Instalação e Uso

### Uso Básico

```php
use App\Helpers\PayrollCalculatorHelper;
use App\Models\HR\Employee;
use Carbon\Carbon;

// 1. Obter empregado
$employee = Employee::find($employeeId);

// 2. Definir período
$startDate = Carbon::create(2025, 10, 1)->startOfMonth();
$endDate = Carbon::create(2025, 10, 1)->endOfMonth();

// 3. Criar calculator
$calculator = new PayrollCalculatorHelper($employee, $startDate, $endDate);

// 4. Carregar dados
$calculator->loadAllEmployeeData();

// 5. Configurar subsídios (opcional)
$calculator->setChristmasSubsidy(true);
$calculator->setVacationSubsidy(false);
$calculator->setAdditionalBonus(5000);

// 6. Calcular
$results = $calculator->calculate();

// 7. Usar resultados
echo "Salário Líquido: " . number_format($results['net_salary'], 2) . " AOA";
```

### Integração no Livewire Component

```php
public function calculatePayrollComponents(): void
{
    $calculator = new PayrollCalculatorHelper(
        $this->selectedEmployee,
        Carbon::parse($this->selectedPayrollPeriod->start_date),
        Carbon::parse($this->selectedPayrollPeriod->end_date)
    );
    
    $calculator->loadAllEmployeeData();
    $calculator->setChristmasSubsidy($this->christmas_subsidy);
    $calculator->setVacationSubsidy($this->vacation_subsidy);
    $calculator->setAdditionalBonus($this->additional_bonus_amount);
    
    $results = $calculator->calculate();
    
    // Atribuir resultados
    $this->gross_salary = $results['gross_salary'];
    $this->calculated_net_salary = $results['net_salary'];
    $this->calculated_inss = $results['inss_3_percent'];
    $this->calculated_irt = $results['irt'];
    // ... etc
}
```

### Integração no Batch Processing

```php
foreach ($batch->batchItems as $item) {
    $calculator = new PayrollCalculatorHelper(
        $item->employee,
        $startDate,
        $endDate
    );
    
    $calculator->loadAllEmployeeData();
    $calculator->setChristmasSubsidy($item->christmas_subsidy ?? false);
    $calculator->setVacationSubsidy($item->vacation_subsidy ?? false);
    $calculator->setAdditionalBonus($item->additional_bonus ?? 0);
    
    $results = $calculator->calculate();
    
    $item->update([
        'gross_salary' => $results['gross_salary'],
        'net_salary' => $results['net_salary'],
        'inss_deduction' => $results['inss_3_percent'],
        'irt_deduction' => $results['irt'],
        // ... etc
    ]);
}
```

## 📊 Estrutura de Dados Retornados

O método `calculate()` retorna um array completo com:

```php
[
    // Dados básicos
    'employee_id' => 1,
    'employee_name' => 'João Silva',
    'period_start' => '2025-10-01',
    'period_end' => '2025-10-31',
    
    // Salário base
    'basic_salary' => 150000.00,
    'hourly_rate' => 852.27,
    
    // Presença
    'total_working_days' => 22,
    'present_days' => 20,
    'absent_days' => 2,
    'late_arrivals' => 1,
    'total_attendance_hours' => 160.0,
    'attendance_data' => [...],
    
    // Horas extras
    'total_overtime_hours' => 10.0,
    'total_overtime_amount' => 15000.00,
    'overtime_records' => [...],
    
    // Benefícios
    'food_benefit' => 25000.00,
    'transport_benefit_full' => 60000.00,
    'transport_allowance' => 54545.45, // Proporcional
    'transport_discount' => 5454.55,
    'taxable_transport' => 24545.45,
    'exempt_transport' => 30000.00,
    'taxable_food' => 0.00,
    'exempt_food' => 25000.00,
    
    // Bônus e subsídios
    'bonus_amount' => 10000.00,
    'additional_bonus_amount' => 5000.00,
    'christmas_subsidy' => true,
    'christmas_subsidy_amount' => 75000.00,
    'vacation_subsidy' => false,
    'vacation_subsidy_amount' => 0.00,
    
    // Adiantamentos e descontos
    'total_salary_advances' => 50000.00,
    'advance_deduction' => 10000.00,
    'salary_advances' => [...],
    'total_salary_discounts' => 5000.00,
    'salary_discounts' => [...],
    
    // Licenças
    'total_leave_days' => 0,
    'unpaid_leave_days' => 0,
    'leave_deduction' => 0.00,
    'leave_records' => [],
    
    // Deduções por presença
    'late_deduction' => 852.27,
    'absence_deduction' => 13636.36,
    
    // Cálculos de salário
    'gross_salary' => 329545.45,
    'main_salary' => 329545.45,
    'irt_base' => 264681.82,
    
    // Impostos e contribuições
    'inss_3_percent' => 9886.36,
    'inss_8_percent' => 26363.64,
    'irt' => 32420.27,
    'irt_details' => [
        'mc' => 264681.82,
        'bracket' => {...},
        'bracket_number' => 4,
        'excess' => 114681.82,
        'fixed_amount' => 10500.00,
        'tax_on_excess' => 20642.73,
        'total_irt' => 31142.73,
        'description' => 'Escalões 1-4 | Fixo: 10.500 + Atual: 20.643 = 31.143 AOA'
    ],
    
    // Totais
    'total_deductions' => 67795.26,
    'net_salary' => 261750.19,
    
    // Configurações
    'is_food_in_kind' => false,
    'hr_settings' => [...]
]
```

## 🧮 Fórmulas de Cálculo

### 1. Taxa Horária
```
Hourly Rate = Basic Salary / (Working Days × Working Hours per Day)
Exemplo: 150.000 / (22 × 8) = 852,27 AOA/hora
```

### 2. Subsídio de Transporte Proporcional
```
Transport Allowance = (Full Transport / Total Working Days) × Present Days
Exemplo: (60.000 / 22) × 20 = 54.545,45 AOA
```

### 3. Salário Bruto (Gross Salary)
```
Gross = Basic + Food + Transport + Overtime + Bonus + Additional Bonus + 
        Christmas Subsidy + Vacation Subsidy
```

### 4. INSS (3%)
```
INSS = Gross Salary × 3%
```

### 5. Base Tributável IRT (MC - Matéria Coletável)
```
MC = Gross - INSS - Exempt Transport (até 30k) - Exempt Food (até 30k)
```

### 6. IRT (Escalões Progressivos)
```
IRT = Fixed Amount + (Excess × Tax Rate)
Onde:
- Fixed Amount = Valor fixo do escalão
- Excess = MC - Mínimo do escalão
- Tax Rate = Taxa do escalão
```

### 7. Total de Deduções
```
Total Deductions = INSS + IRT + Advances + Discounts + Late + Absence
```

### 8. Salário Líquido
```
Net Salary = Gross Salary - Total Deductions
```

## 📈 Escalões de IRT (Angola)

| Escalão | Mínimo (AOA) | Máximo (AOA) | Taxa | Valor Fixo (AOA) |
|---------|--------------|--------------|------|------------------|
| 1 | 0 | 70.000 | 0% | 0 |
| 2 | 70.001 | 100.000 | 10% | 0 |
| 3 | 100.001 | 150.000 | 15% | 3.000 |
| 4 | 150.001 | 200.000 | 18% | 10.500 |
| 5 | 200.001 | 250.000 | 19% | 19.500 |
| 6 | 250.001 | 350.000 | 20,5% | 29.000 |
| 7 | 350.001 | 500.000 | 22% | 49.500 |
| 8 | 500.001 | + | 25% | 82.500 |

## 🎓 Métodos Disponíveis

### Carregar Dados
- `loadAllEmployeeData()` - Carrega todos os dados de uma vez
- `loadAttendanceData()` - Carrega apenas presença
- `loadOvertimeData()` - Carrega apenas horas extras
- `loadSalaryAdvances()` - Carrega apenas adiantamentos
- `loadSalaryDiscounts()` - Carrega apenas descontos
- `loadLeaveData()` - Carrega apenas licenças

### Configurar Subsídios
- `setChristmasSubsidy(bool)` - Define subsídio de Natal
- `setVacationSubsidy(bool)` - Define subsídio de férias
- `setAdditionalBonus(float)` - Define bônus adicional
- `setFoodInKind(bool)` - Define se alimentação é em espécie

### Calcular Valores
- `calculateHourlyRate()` - Taxa horária
- `calculateProportionalTransportAllowance()` - Transporte proporcional
- `calculateGrossSalary()` - Salário bruto
- `calculateMainSalary()` - Salário principal
- `calculateIRTBase()` - Base tributável IRT
- `calculateINSS()` - INSS 3%
- `calculateINSS8Percent()` - INSS 8%
- `calculateIRT()` - IRT
- `calculateTotalDeductions()` - Total de deduções
- `calculateNetSalary()` - Salário líquido
- `calculate()` - Calcula tudo de uma vez

### Obter Valores
- `getFullTransportBenefit()` - Subsídio de transporte completo
- `getTransportDiscountAmount()` - Desconto de transporte
- `getTaxableTransportAllowance()` - Transporte tributável
- `getExemptTransportAllowance()` - Transporte isento
- `getTaxableFoodAllowance()` - Alimentação tributável
- `getExemptFoodAllowance()` - Alimentação isenta
- `getChristmasSubsidyAmount()` - Valor do subsídio de Natal
- `getVacationSubsidyAmount()` - Valor do subsídio de férias
- `getIRTCalculationDetails()` - Detalhes do cálculo de IRT
- `getResults()` - Todos os resultados calculados

### Obter Dados Específicos
- `getAttendanceData()` - Dados de presença
- `getOvertimeData()` - Dados de horas extras
- `getAdvancesData()` - Dados de adiantamentos
- `getDiscountsData()` - Dados de descontos

## 🔍 Exemplo Completo de Uso

Ver arquivo: `PayrollCalculatorHelper_USAGE.md`

## 🔗 Exemplos de Integração

Ver arquivo: `PayrollCalculatorHelper_INTEGRATION_EXAMPLE.php`

## ⚠️ Notas Importantes

1. **Sempre carregue os dados**: Chame `loadAllEmployeeData()` antes de calcular
2. **Período correto**: Use sempre início e fim do mês para cálculos corretos
3. **Subsídios antes de calcular**: Configure os subsídios antes de chamar `calculate()`
4. **Reutilização**: Crie nova instância para cada empregado
5. **Consistência**: Use sempre este helper para garantir cálculos iguais em todo o sistema

## 📝 Próximos Passos

### Para integrar no sistema existente:

1. **No componente Payroll.php**:
   - Substituir método `calculatePayrollComponents()` para usar o helper
   - Manter propriedades do componente mas popular com dados do helper

2. **No componente PayrollBatch.php**:
   - Usar helper no método de cálculo de itens
   - Usar helper no método de edição de itens
   - Garantir recálculo automático quando subsídios mudam

3. **No Job ProcessPayrollBatch**:
   - Usar helper para processar cada item do batch
   - Garantir tratamento de erros adequado

4. **Testes**:
   - Criar testes unitários para o helper
   - Validar cálculos com casos reais
   - Comparar resultados com cálculos manuais

## 🐛 Debug e Logs

O helper inclui método de logging:

```php
$calculator->logCalculations();
```

Isso registrará todos os cálculos no log do Laravel para debug.

## 📞 Suporte

Para dúvidas ou problemas:
1. Consulte a documentação completa em `PayrollCalculatorHelper_USAGE.md`
2. Veja exemplos em `PayrollCalculatorHelper_INTEGRATION_EXAMPLE.php`
3. Verifique os logs do Laravel para debug
4. Entre em contato com a equipe de desenvolvimento

---

**Criado em**: 06/10/2025  
**Versão**: 1.0.0  
**Autor**: Sistema ERP DEMBENA
