# PayrollCalculatorHelper - Guia de Uso

## Descrição

O `PayrollCalculatorHelper` é um helper centralizado que contém toda a lógica de cálculo de folha de pagamento (payroll) extraída da modal `ProcessPayrollModal` e do componente Livewire `Payroll`.

Este helper garante que os cálculos sejam **consistentes e reutilizáveis** entre:
- Pagamento individual de salário
- Pagamento em lote (batch)
- Processamento de payroll
- Relatórios e exportações

## Instalação

O helper já está criado em `app/Helpers/PayrollCalculatorHelper.php`.

## Uso Básico

```php
use App\Helpers\PayrollCalculatorHelper;
use App\Models\HR\Employee;
use Carbon\Carbon;

// 1. Obter o empregado
$employee = Employee::find($employeeId);

// 2. Definir período (início e fim do mês)
$startDate = Carbon::create(2025, 10, 1)->startOfMonth();
$endDate = Carbon::create(2025, 10, 1)->endOfMonth();

// 3. Criar instância do helper
$calculator = new PayrollCalculatorHelper($employee, $startDate, $endDate);

// 4. Carregar todos os dados do empregado
$calculator->loadAllEmployeeData();

// 5. Configurar subsídios (opcional)
$calculator->setChristmasSubsidy(true);  // Subsídio de Natal
$calculator->setVacationSubsidy(false);  // Subsídio de Férias
$calculator->setAdditionalBonus(5000);   // Bônus adicional

// 6. Calcular tudo
$results = $calculator->calculate();

// 7. Usar os resultados
echo "Salário Líquido: " . number_format($results['net_salary'], 2) . " AOA";
echo "IRT: " . number_format($results['irt'], 2) . " AOA";
echo "INSS: " . number_format($results['inss_3_percent'], 2) . " AOA";
```

## Uso no Componente Livewire (Payroll Individual)

```php
use App\Helpers\PayrollCalculatorHelper;

class Payroll extends Component
{
    public function calculatePayrollComponents(): void
    {
        if (!$this->selectedEmployee) {
            return;
        }
        
        // Criar calculator
        $calculator = new PayrollCalculatorHelper(
            $this->selectedEmployee,
            Carbon::parse($this->selectedPayrollPeriod->start_date),
            Carbon::parse($this->selectedPayrollPeriod->end_date)
        );
        
        // Carregar dados
        $calculator->loadAllEmployeeData();
        
        // Configurar subsídios
        $calculator->setChristmasSubsidy($this->christmas_subsidy);
        $calculator->setVacationSubsidy($this->vacation_subsidy);
        $calculator->setAdditionalBonus($this->additional_bonus_amount);
        $calculator->setFoodInKind($this->is_food_in_kind);
        
        // Calcular
        $results = $calculator->calculate();
        
        // Atribuir resultados às propriedades do componente
        $this->basic_salary = $results['basic_salary'];
        $this->hourly_rate = $results['hourly_rate'];
        $this->transport_allowance = $results['transport_allowance'];
        $this->total_overtime_amount = $results['total_overtime_amount'];
        $this->advance_deduction = $results['advance_deduction'];
        $this->total_salary_discounts = $results['total_salary_discounts'];
        $this->gross_salary = $results['gross_salary'];
        $this->calculated_inss = $results['inss_3_percent'];
        $this->calculated_irt = $results['irt'];
        $this->total_deductions_calculated = $results['total_deductions'];
        $this->calculated_net_salary = $results['net_salary'];
        
        // Dados de presença
        $this->total_working_days = $results['total_working_days'];
        $this->present_days = $results['present_days'];
        $this->absent_days = $results['absent_days'];
        $this->late_arrivals = $results['late_arrivals'];
        $this->attendanceData = $results['attendance_data'];
        
        // Detalhes de IRT
        $this->irtCalculationDetails = $results['irt_details'];
    }
}
```

## Uso no Processamento em Lote (Batch)

```php
use App\Helpers\PayrollCalculatorHelper;
use App\Models\HR\PayrollBatch;
use App\Models\HR\PayrollBatchItem;

class ProcessPayrollBatch
{
    public function processBatch(PayrollBatch $batch)
    {
        $employees = $batch->items()->with('employee')->get();
        
        foreach ($employees as $item) {
            $employee = $item->employee;
            
            // Criar calculator
            $calculator = new PayrollCalculatorHelper(
                $employee,
                Carbon::parse($batch->period_start),
                Carbon::parse($batch->period_end)
            );
            
            // Carregar dados
            $calculator->loadAllEmployeeData();
            
            // Configurar subsídios do item
            $calculator->setChristmasSubsidy($item->christmas_subsidy ?? false);
            $calculator->setVacationSubsidy($item->vacation_subsidy ?? false);
            $calculator->setAdditionalBonus($item->additional_bonus ?? 0);
            
            // Calcular
            $results = $calculator->calculate();
            
            // Atualizar item do batch
            $item->update([
                'basic_salary' => $results['basic_salary'],
                'transport_allowance' => $results['transport_allowance'],
                'food_allowance' => $results['food_benefit'],
                'overtime_amount' => $results['total_overtime_amount'],
                'bonus_amount' => $results['bonus_amount'],
                'christmas_subsidy_amount' => $results['christmas_subsidy_amount'],
                'vacation_subsidy_amount' => $results['vacation_subsidy_amount'],
                'gross_salary' => $results['gross_salary'],
                'inss_deduction' => $results['inss_3_percent'],
                'irt_deduction' => $results['irt'],
                'advance_deduction' => $results['advance_deduction'],
                'discount_deduction' => $results['total_salary_discounts'],
                'total_deductions' => $results['total_deductions'],
                'net_salary' => $results['net_salary'],
                'present_days' => $results['present_days'],
                'absent_days' => $results['absent_days'],
                'late_days' => $results['late_arrivals'],
            ]);
        }
    }
}
```

## Métodos Disponíveis

### Carregar Dados

```php
// Carregar todos os dados de uma vez
$calculator->loadAllEmployeeData();

// Ou carregar individualmente
$calculator->loadAttendanceData();
$calculator->loadOvertimeData();
$calculator->loadSalaryAdvances();
$calculator->loadSalaryDiscounts();
$calculator->loadLeaveData();
```

### Configurar Subsídios e Bônus

```php
$calculator->setChristmasSubsidy(true);      // Subsídio de Natal (50% do salário base)
$calculator->setVacationSubsidy(true);       // Subsídio de Férias (50% do salário base)
$calculator->setAdditionalBonus(10000);      // Bônus adicional em AOA
$calculator->setFoodInKind(true);            // Alimentação em espécie (não em dinheiro)
```

### Obter Valores Calculados

```php
// Cálculos principais
$grossSalary = $calculator->calculateGrossSalary();
$mainSalary = $calculator->calculateMainSalary();
$irtBase = $calculator->calculateIRTBase();
$inss = $calculator->calculateINSS();
$irt = $calculator->calculateIRT();
$netSalary = $calculator->calculateNetSalary();

// Subsídios
$christmasAmount = $calculator->getChristmasSubsidyAmount();
$vacationAmount = $calculator->getVacationSubsidyAmount();

// Transporte
$fullTransport = $calculator->getFullTransportBenefit();
$proportionalTransport = $calculator->calculateProportionalTransportAllowance();
$transportDiscount = $calculator->getTransportDiscountAmount();
$taxableTransport = $calculator->getTaxableTransportAllowance();
$exemptTransport = $calculator->getExemptTransportAllowance();

// Detalhes de IRT
$irtDetails = $calculator->getIRTCalculationDetails();
// Retorna: ['mc', 'bracket', 'excess', 'fixed_amount', 'tax_on_excess', 'total_irt', 'description']
```

### Obter Dados Específicos

```php
// Dados de presença
$attendanceData = $calculator->getAttendanceData();

// Dados de horas extras
$overtimeData = $calculator->getOvertimeData();

// Dados de adiantamentos
$advancesData = $calculator->getAdvancesData();

// Dados de descontos
$discountsData = $calculator->getDiscountsData();
```

### Calcular Tudo de Uma Vez

```php
// Retorna array completo com todos os valores
$results = $calculator->calculate();

// Estrutura do array retornado:
[
    'employee_id' => 1,
    'employee_name' => 'João Silva',
    'basic_salary' => 150000.00,
    'gross_salary' => 180000.00,
    'main_salary' => 175000.00,
    'irt_base' => 140000.00,
    'inss_3_percent' => 5250.00,
    'irt' => 8500.00,
    'total_deductions' => 15000.00,
    'net_salary' => 165000.00,
    // ... e muitos outros campos
]
```

## Lógica de Cálculo

### 1. Salário Bruto (Gross Salary)
```
Gross Salary = Basic Salary + Food Benefit + Transport Allowance + Overtime + 
               Bonus + Additional Bonus + Christmas Subsidy + Vacation Subsidy
```

### 2. Salário Principal (Main Salary)
```
Main Salary = Gross Salary (usado para calcular INSS)
```

### 3. INSS (3%)
```
INSS = Main Salary × 3%
```

### 4. Base Tributável IRT (Matéria Coletável - MC)
```
MC = Gross Salary - INSS - Isenção Transporte (até 30k) - Isenção Alimentação (até 30k)
```

### 5. IRT (Imposto sobre Rendimento do Trabalho)
```
IRT = Calculado usando escalões progressivos da tabela IRTTaxBracket
```

### 6. Total de Deduções
```
Total Deductions = INSS + IRT + Adiantamentos + Descontos + Atrasos + Faltas
```

### 7. Salário Líquido (Net Salary)
```
Net Salary = Gross Salary - Total Deductions
```

## Subsídio de Transporte Proporcional

O subsídio de transporte é calculado proporcionalmente aos dias trabalhados:

```php
Transport Allowance = (Full Transport Benefit / Total Working Days) × Present Days
```

**Exemplo:**
- Subsídio completo: 60.000 AOA
- Dias úteis no mês: 22
- Dias presentes: 20
- Subsídio proporcional: (60.000 / 22) × 20 = 54.545,45 AOA

## Isenções Fiscais

### Transporte
- **Até 30.000 AOA**: Isento de IRT
- **Acima de 30.000 AOA**: Tributável

### Alimentação
- **Até 30.000 AOA**: Isento de IRT
- **Acima de 30.000 AOA**: Tributável

## Escalões de IRT

O IRT é calculado usando escalões progressivos. Exemplo:

| Escalão | Mínimo | Máximo | Taxa | Valor Fixo |
|---------|--------|--------|------|------------|
| 1 | 0 | 70.000 | 0% | 0 |
| 2 | 70.001 | 100.000 | 10% | 0 |
| 3 | 100.001 | 150.000 | 15% | 3.000 |
| 4 | 150.001 | 200.000 | 18% | 10.500 |
| 5 | 200.001 | + | 25% | 19.500 |

## Exemplo Completo

```php
use App\Helpers\PayrollCalculatorHelper;
use App\Models\HR\Employee;
use Carbon\Carbon;

// Empregado
$employee = Employee::find(1);

// Período (Outubro 2025)
$startDate = Carbon::create(2025, 10, 1)->startOfMonth();
$endDate = Carbon::create(2025, 10, 1)->endOfMonth();

// Criar calculator
$calculator = new PayrollCalculatorHelper($employee, $startDate, $endDate);

// Carregar dados
$calculator->loadAllEmployeeData();

// Configurar subsídios
$calculator->setChristmasSubsidy(true);
$calculator->setAdditionalBonus(10000);

// Calcular
$results = $calculator->calculate();

// Exibir resultados
echo "=== FOLHA DE PAGAMENTO ===\n";
echo "Empregado: {$results['employee_name']}\n";
echo "Período: {$results['period_start']} a {$results['period_end']}\n\n";

echo "--- SALÁRIOS ---\n";
echo "Salário Base: " . number_format($results['basic_salary'], 2) . " AOA\n";
echo "Subsídio Transporte: " . number_format($results['transport_allowance'], 2) . " AOA\n";
echo "Subsídio Alimentação: " . number_format($results['food_benefit'], 2) . " AOA\n";
echo "Horas Extras: " . number_format($results['total_overtime_amount'], 2) . " AOA\n";
echo "Bônus: " . number_format($results['bonus_amount'], 2) . " AOA\n";
echo "Subsídio Natal: " . number_format($results['christmas_subsidy_amount'], 2) . " AOA\n";
echo "Salário Bruto: " . number_format($results['gross_salary'], 2) . " AOA\n\n";

echo "--- DEDUÇÕES ---\n";
echo "INSS (3%): " . number_format($results['inss_3_percent'], 2) . " AOA\n";
echo "IRT: " . number_format($results['irt'], 2) . " AOA\n";
echo "Adiantamentos: " . number_format($results['advance_deduction'], 2) . " AOA\n";
echo "Descontos: " . number_format($results['total_salary_discounts'], 2) . " AOA\n";
echo "Total Deduções: " . number_format($results['total_deductions'], 2) . " AOA\n\n";

echo "--- PRESENÇA ---\n";
echo "Dias Úteis: {$results['total_working_days']}\n";
echo "Dias Presentes: {$results['present_days']}\n";
echo "Dias Ausentes: {$results['absent_days']}\n";
echo "Atrasos: {$results['late_arrivals']}\n\n";

echo "=== SALÁRIO LÍQUIDO: " . number_format($results['net_salary'], 2) . " AOA ===\n";
```

## Notas Importantes

1. **Consistência**: Use sempre este helper para garantir que os cálculos sejam iguais em todo o sistema
2. **Período**: O período deve ser sempre início e fim do mês para cálculos corretos
3. **Dados**: Sempre chame `loadAllEmployeeData()` antes de calcular
4. **Subsídios**: Configure os subsídios antes de chamar `calculate()`
5. **Reutilização**: O helper pode ser reutilizado para múltiplos empregados criando novas instâncias

## Suporte

Para dúvidas ou problemas, consulte a documentação do código ou entre em contato com a equipe de desenvolvimento.
