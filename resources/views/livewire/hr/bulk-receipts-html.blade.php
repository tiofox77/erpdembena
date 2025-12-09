<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Recibo de Salário</title>
  <style>
    :root {
      --gap: 3px;
      --border: 1px solid #222;
      --muted: #555;
      --heading: #000;
      --cutline: #888;
      --font: "Inter", system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial, "Apple Color Emoji", "Segoe UI Emoji";
    }

    * { box-sizing: border-box; }
    html, body { height: 100%; margin: 0; padding: 0; }
    body { background: #f5f5f5; color: #111; font-family: var(--font); font-size: 8px; }

    @page { size: A4; margin: 5mm 5mm 5mm 5mm; }
    
    .header {
      background: #fff;
      padding: 10px 15px;
      border-bottom: 1px solid #ddd;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .header h1 {
      margin: 0;
      font-size: 16px;
      color: #333;
    }
    
    .nav-buttons {
      margin-top: 8px;
      display: flex;
      gap: 8px;
    }
    
    .btn {
      padding: 6px 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      font-size: 12px;
      display: inline-block;
    }
    
    .btn-primary { background: #007bff; color: white; }
    .btn-secondary { background: #6c757d; color: white; }
    .btn-outline { background: transparent; color: #007bff; border: 1px solid #007bff; }
    
    .content-wrapper {
      margin-top: 90px;
      padding: 10px;
    }

    .receipt-page {
      width: 210mm; height: 297mm; margin: auto; background: #fff; padding: 3mm;
      box-shadow: 0 2px 12px rgba(0,0,0,.08);
      display: flex; flex-direction: column; justify-content: space-between;
      margin-bottom: 15mm;
      page-break-after: always;
    }
    
    .receipt-page:last-child {
      page-break-after: avoid;
    }

    .receipt {
      border: var(--border); border-radius: 3px; padding: 3px; 
      page-break-inside: avoid; font-size: 8px; flex: 1;
      display: flex; flex-direction: column;
    }

    .title { text-align: center; font-weight: 800; letter-spacing: .04em; color: var(--heading); font-size: 12px; margin: 0; }
    .company { text-align: center; margin-top: 1px; font-weight: 600; font-size: 10px; }

    .meta { display: grid; grid-template-columns: 1fr 1fr; gap: var(--gap); margin-top: 3px; font-size: 9px; }
    .meta .cell { border: 1px dashed #bbb; padding: 2px 3px; border-radius: 3px; }
    .label { color: var(--muted); font-size: 8px; text-transform: uppercase; letter-spacing: .03em; }
    .value { margin-top: 1px; font-weight: 600; font-size: 9px; }

    .row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--gap); margin-top: 3px; font-size: 9px; }
    .row .block { border: 1px dashed #bbb; padding: 2px 3px; border-radius: 3px; }
    .row .block .kv { display: flex; justify-content: space-between; gap: 4px; padding: 0; line-height: 1.3; }

    .tables { display: grid; grid-template-columns: 1fr 1fr; gap: var(--gap); margin-top: 3px; flex: 1; }
    table { width: 100%; border-collapse: collapse; font-size: 9px; }
    th, td { padding: 1px 2px; border-bottom: 1px solid #e5e5e5; line-height: 1.2; }
    th { text-align: left; font-size: 8px; color: var(--muted); text-transform: uppercase; letter-spacing: .03em; }
    tfoot td { font-weight: 700; border-top: 1px solid #333; }
    .amount { text-align: right; white-space: nowrap; }

    .signature { height: 28px; display: flex; flex-direction: column; justify-content: flex-end; }
    .signature .line { height: 1px; background: #333; margin-top: auto; }
    .signature .who { text-align: center; font-size: 8px; color: var(--muted); margin-top: 2px; }

    .srno { font-size: 8px; color: var(--muted); display: flex; justify-content: space-between; }

    .cut { position: relative; text-align: center; color: var(--cutline); margin: 1mm 0; font-size: 8px; user-select: none; }
    .cut:before, .cut:after { content: ""; position: absolute; top: 50%; width: 40%; border-top: 1px dashed var(--cutline); }
    .cut:before { left: 0; }
    .cut:after { right: 0; }

    .footer-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--gap); margin-top: 3px; font-size: 9px; }
    .footer-row .block { border: 1px dashed #bbb; padding: 2px 3px; border-radius: 3px; }

    @media print {
      .header {
        display: none;
      }
      
      .content-wrapper {
        margin-top: 0;
        padding: 0;
      }
      
      body { background: #fff; }
      .receipt-page { box-shadow: none; padding: 3mm; height: 297mm; }
      .receipt-page:last-child { page-break-after: avoid; }
      .receipt { break-inside: avoid; }
      .cut { margin: 1mm 0; }
    }
  </style>
</head>
<body>
  <div class="header">
    <h1>Recibos de Salário - {{ $payrolls->count() > 0 ? $payrolls->first()->payrollPeriod->start_date->format('F Y') : $month . '/' . $year }}</h1>
    <div class="nav-buttons">
      @if($payrolls->count() > 0)
        <button onclick="window.print()" class="btn btn-primary">📄 Imprimir</button>
        <a href="{{ route('payroll.bulk-receipts-pdf', request()->query()) }}" class="btn btn-secondary">📄 Gerar PDF</a>
      @endif
      <a href="{{ route('hr.payroll') }}" class="btn btn-outline">← Voltar</a>
    </div>
  </div>
  
  <div class="content-wrapper">
  @if(isset($error))
    <div style="text-align: center; padding: 40px; color: #666;">
      <h3>{{ $error }}</h3>
      <p>Tente ajustar os filtros ou verificar se existem dados para o período selecionado.</p>
    </div>
  @elseif($payrolls->count() === 0)
    <div style="text-align: center; padding: 40px; color: #666;">
      <h3>Nenhum recibo encontrado</h3>
      <p>Não foram encontrados recibos de salário para os critérios selecionados.</p>
    </div>
  @else
    @foreach($payrolls as $index => $payroll)
  
  {{-- DEBUG: Verificar dados do payroll e receiptData --}}
  @php
    \Log::info("=== DEBUG Template - Recibo {$index} ===", [
      'payroll_id' => $payroll->id,
      'employee_name' => $payroll->employee->full_name ?? 'N/A',
      'receiptData_exists' => isset($receiptData[$index]),
      'receiptData_keys' => isset($receiptData[$index]) ? array_keys($receiptData[$index]) : [],
      'receiptData_sample' => isset($receiptData[$index]) ? [
        'baseSalary' => $receiptData[$index]['baseSalary'] ?? 'not_set',
        'netSalary' => $receiptData[$index]['netSalary'] ?? 'not_set',
        'employeeName' => $receiptData[$index]['employeeName'] ?? 'not_set'
      ] : 'no_data',
      'payroll_direct_data' => [
        'basic_salary' => $payroll->basic_salary,
        'net_salary' => $payroll->net_salary,
        'employee_full_name' => $payroll->employee->full_name ?? null
      ]
    ]);
  @endphp

  <div class="receipt-page">
    <!-- Recibo 1 (Original) -->
    <section class="receipt">
      <h2 class="title">RECIBO DE SALÁRIO</h2>
      <div class="company">Dembena Indústria e Comércio Lda</div>

      <div class="meta">
        <div class="cell">
          <div class="label">NOME</div>
          <div class="value">{{ $payroll->employee->full_name }} (Id: {{ $payroll->employee->employee_id }})</div>
        </div>
        <div class="cell">
          <div class="label">MÊS</div>
          <div class="value">{{ $payroll->payrollPeriod->start_date->format('F Y') }} • Data de referência: {{ $payroll->payrollPeriod->end_date->format('d/m/Y') }}</div>
        </div>
        <div class="cell">
          <div class="label">CATEGORIA</div>
          <div class="value">{{ $payroll->employee->position->title ?? 'N/A' }}</div>
        </div>
        <div class="cell">
          <div class="label">PERÍODO DE REFERÊNCIA</div>
          <div class="value">{{ $payroll->payrollPeriod->start_date->format('d/m/Y') }} - {{ $payroll->payrollPeriod->end_date->format('d/m/Y') }}</div>
        </div>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">ID Emp #</div>
          <div class="value">{{ $payroll->employee->employee_id ?? $payroll->employee->id }}</div>
        </div>
        <div class="block">
          <div class="kv"><span>Dias úteis do mês</span><span>{{ $receiptData[$index]['monthlyWorkingDays'] ?? 26 }}</span></div>
          <div class="kv"><span>Dias trabalhados</span><span>{{ $receiptData[$index]['workedDays'] ?? 0 }}</span></div>
        </div>
        <div class="block">
          <div class="kv"><span>Total de ausências</span><span>{{ $receiptData[$index]['absences'] ?? 0 }}</span></div>
          <div class="kv"><span>Horas extras</span><span>{{ number_format($receiptData[$index]['extraHours'] ?? 0, 2) }}</span></div>
        </div>
      </div>

      <div class="tables">
        <table>
          <thead>
            <tr><th>REMUNERAÇÃO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>Salário Base</td><td class="amount">{{ number_format($receiptData[$index]['baseSalary'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Transporte</td><td class="amount">{{ number_format($receiptData[$index]['transportSubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Alimentação</td><td class="amount">{{ number_format($receiptData[$index]['foodSubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio Noturno ({{ $receiptData[$index]['nightShiftDays'] ?? 0 }} dias)</td><td class="amount">{{ number_format($receiptData[$index]['nightShiftAllowance'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Natal</td><td class="amount">{{ number_format($receiptData[$index]['christmasSubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Férias</td><td class="amount">{{ number_format($receiptData[$index]['holidaySubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Abono de Família</td><td class="amount">{{ number_format($receiptData[$index]['familyAllowance'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Cargo</td><td class="amount">{{ number_format($receiptData[$index]['positionSubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Desempenho</td><td class="amount">{{ number_format($receiptData[$index]['performanceSubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Bónus Adicional</td><td class="amount">{{ number_format($receiptData[$index]['payrollBonus'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Horas Extras</td><td class="amount">{{ number_format($receiptData[$index]['overtimeAmount'] ?? 0, 2, ',', '.') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Remunerações</td><td class="amount">{{ number_format($receiptData[$index]['totalEarnings'] ?? 0, 2, ',', '.') }}</td></tr>
          </tfoot>
        </table>

        <table>
          <thead>
            <tr><th>DESCONTO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>IRT</td><td class="amount">{{ number_format($receiptData[$index]['incomeTax'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>INSS (3%)</td><td class="amount">{{ number_format($receiptData[$index]['socialSecurity'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Desconto Subsídio Alimentação</td><td class="amount">{{ number_format($receiptData[$index]['foodSubsidyDeduction'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Adiantamentos Salariais</td><td class="amount">{{ number_format($receiptData[$index]['salaryAdvances'] ?? 0, 2, ',', '.') }}</td></tr>
            @if(isset($receiptData[$index]['salaryDiscountsDetailed']) && count($receiptData[$index]['salaryDiscountsDetailed']) > 0)
              @foreach($receiptData[$index]['salaryDiscountsDetailed'] as $discount)
                <tr><td>{{ $discount['type_name'] }}</td><td class="amount">{{ number_format($discount['total_amount'], 2, ',', '.') }}</td></tr>
              @endforeach
            @else
              <tr><td>Descontos Salariais</td><td class="amount">{{ number_format($receiptData[$index]['salaryDiscounts'] ?? 0, 2, ',', '.') }}</td></tr>
            @endif
            <tr><td>Faltas ({{ $receiptData[$index]['absenceDays'] ?? 0 }} dias)</td><td class="amount">{{ number_format($receiptData[$index]['absenceDeduction'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Atrasos ({{ $receiptData[$index]['lateDays'] ?? 0 }} dias)</td><td class="amount">{{ number_format($receiptData[$index]['lateDeduction'] ?? 0, 2, ',', '.') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Descontos</td><td class="amount">{{ number_format($receiptData[$index]['totalDeductions'] ?? 0, 2, ',', '.') }}</td></tr>
          </tfoot>
        </table>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">VENCIMENTO LÍQUIDO</div>
          <div class="value">{{ number_format($receiptData[$index]['netSalary'] ?? 0, 2, ',', '.') }}</div>
        </div>
        <div class="block">
          <div class="label">DADOS BANCÁRIOS</div>
          <div class="kv"><span>Nome do banco</span><span>{{ $receiptData[$index]['bankName'] ?? 'N/A' }}</span></div>
          <div class="kv"><span>Número de conta</span><span>{{ $receiptData[$index]['accountNumber'] ?? 'N/A' }}</span></div>
        </div>
        <div class="block">
          <div class="signature">
            <div class="line"></div>
            <div class="who">Assinatura do trabalhador</div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">MODO DE PAGAMENTO</div>
          <div class="value">{{ $receiptData[$index]['paymentMethod'] ?? 'CASH' }}</div>
        </div>
        <div class="block" style="grid-column: span 2;">
          <div class="srno">
            <span>Sr. No. {{ $receiptData[$index]['receiptNumber'] ?? 'N/A' }}</span>
          </div>
        </div>
      </div>
    </section>

    <div class="cut">————— CORTE AQUI —————</div>

    <!-- Recibo 2 (Duplicata) -->
    <section class="receipt">
      <h2 class="title">RECIBO DE SALÁRIO</h2>
      <div class="company">Dembena Indústria e Comércio Lda</div>

      <div class="meta">
        <div class="cell">
          <div class="label">NOME</div>
          <div class="value">{{ $payroll->employee->full_name }} (Id: {{ $payroll->employee->employee_id }})</div>
        </div>
        <div class="cell">
          <div class="label">MÊS</div>
          <div class="value">{{ $payroll->payrollPeriod->start_date->format('F Y') }} • Data de referência: {{ $payroll->payrollPeriod->end_date->format('d/m/Y') }}</div>
        </div>
        <div class="cell">
          <div class="label">CATEGORIA</div>
          <div class="value">{{ $payroll->employee->position->title ?? 'N/A' }}</div>
        </div>
        <div class="cell">
          <div class="label">PERÍODO DE REFERÊNCIA</div>
          <div class="value">{{ $payroll->payrollPeriod->start_date->format('d/m/Y') }} - {{ $payroll->payrollPeriod->end_date->format('d/m/Y') }}</div>
        </div>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">ID Emp #</div>
          <div class="value">{{ $payroll->employee->employee_id ?? $payroll->employee->id }}</div>
        </div>
        <div class="block">
          <div class="kv"><span>Dias úteis do mês</span><span>{{ $receiptData[$index]['monthlyWorkingDays'] ?? 26 }}</span></div>
          <div class="kv"><span>Dias trabalhados</span><span>{{ $receiptData[$index]['workedDays'] ?? 0 }}</span></div>
        </div>
        <div class="block">
          <div class="kv"><span>Total de ausências</span><span>{{ $receiptData[$index]['absences'] ?? 0 }}</span></div>
          <div class="kv"><span>Horas extras</span><span>{{ number_format($receiptData[$index]['extraHours'] ?? 0, 2) }}</span></div>
        </div>
      </div>

      <div class="tables">
        <table>
          <thead>
            <tr><th>REMUNERAÇÃO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>Salário Base</td><td class="amount">{{ number_format($receiptData[$index]['baseSalary'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Transporte</td><td class="amount">{{ number_format($receiptData[$index]['transportSubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Alimentação</td><td class="amount">{{ number_format($receiptData[$index]['foodSubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio Noturno ({{ $receiptData[$index]['nightShiftDays'] ?? 0 }} dias)</td><td class="amount">{{ number_format($receiptData[$index]['nightShiftAllowance'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Natal</td><td class="amount">{{ number_format($receiptData[$index]['christmasSubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Férias</td><td class="amount">{{ number_format($receiptData[$index]['holidaySubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Abono de Família</td><td class="amount">{{ number_format($receiptData[$index]['familyAllowance'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Cargo</td><td class="amount">{{ number_format($receiptData[$index]['positionSubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Subsídio de Desempenho</td><td class="amount">{{ number_format($receiptData[$index]['performanceSubsidy'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Bónus Adicional</td><td class="amount">{{ number_format($receiptData[$index]['payrollBonus'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Horas Extras</td><td class="amount">{{ number_format($receiptData[$index]['overtimeAmount'] ?? 0, 2, ',', '.') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Remunerações</td><td class="amount">{{ number_format($receiptData[$index]['totalEarnings'] ?? 0, 2, ',', '.') }}</td></tr>
          </tfoot>
        </table>

        <table>
          <thead>
            <tr><th>DESCONTO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>IRT</td><td class="amount">{{ number_format($receiptData[$index]['incomeTax'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>INSS (3%)</td><td class="amount">{{ number_format($receiptData[$index]['socialSecurity'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Desconto Subsídio Alimentação</td><td class="amount">{{ number_format($receiptData[$index]['foodSubsidyDeduction'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Adiantamentos Salariais</td><td class="amount">{{ number_format($receiptData[$index]['salaryAdvances'] ?? 0, 2, ',', '.') }}</td></tr>
            @if(isset($receiptData[$index]['salaryDiscountsDetailed']) && count($receiptData[$index]['salaryDiscountsDetailed']) > 0)
              @foreach($receiptData[$index]['salaryDiscountsDetailed'] as $discount)
                <tr><td>{{ $discount['type_name'] }}</td><td class="amount">{{ number_format($discount['total_amount'], 2, ',', '.') }}</td></tr>
              @endforeach
            @else
              <tr><td>Descontos Salariais</td><td class="amount">{{ number_format($receiptData[$index]['salaryDiscounts'] ?? 0, 2, ',', '.') }}</td></tr>
            @endif
            <tr><td>Faltas ({{ $receiptData[$index]['absenceDays'] ?? 0 }} dias)</td><td class="amount">{{ number_format($receiptData[$index]['absenceDeduction'] ?? 0, 2, ',', '.') }}</td></tr>
            <tr><td>Atrasos ({{ $receiptData[$index]['lateDays'] ?? 0 }} dias)</td><td class="amount">{{ number_format($receiptData[$index]['lateDeduction'] ?? 0, 2, ',', '.') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Descontos</td><td class="amount">{{ number_format($receiptData[$index]['totalDeductions'] ?? 0, 2, ',', '.') }}</td></tr>
          </tfoot>
        </table>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">VENCIMENTO LÍQUIDO</div>
          <div class="value">{{ number_format($receiptData[$index]['netSalary'] ?? 0, 2, ',', '.') }}</div>
        </div>
        <div class="block">
          <div class="label">DADOS BANCÁRIOS</div>
          <div class="kv"><span>Nome do banco</span><span>{{ $receiptData[$index]['bankName'] ?? 'N/A' }}</span></div>
          <div class="kv"><span>Número de conta</span><span>{{ $receiptData[$index]['accountNumber'] ?? 'N/A' }}</span></div>
        </div>
        <div class="block">
          <div class="signature">
            <div class="line"></div>
            <div class="who">Assinatura do trabalhador</div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">MODO DE PAGAMENTO</div>
          <div class="value">{{ $receiptData[$index]['paymentMethod'] ?? 'CASH' }}</div>
        </div>
        <div class="block" style="grid-column: span 2;">
          <div class="srno">
            <span>Sr. No. {{ $receiptData[$index]['receiptNumber'] ?? 'N/A' }}</span>
          </div>
        </div>
      </div>
    </section>

  </div>
    @endforeach
  @endif
  </div>
</body>
</html>
