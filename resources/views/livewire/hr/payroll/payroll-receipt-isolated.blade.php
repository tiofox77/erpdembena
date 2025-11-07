<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Recibo de Salário</title>
  <style>
    :root {
      --gap: 6px;
      --border: 1px solid #222;
      --muted: #555;
      --heading: #000;
      --cutline: #888;
      --font: "Inter", system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial, "Apple Color Emoji", "Segoe UI Emoji";
    }

    * { box-sizing: border-box; }
    html, body { height: 100%; }
    body { margin: 0; padding: 0; background: #f5f5f5; color: #111; font-family: var(--font); }

    @page { size: A4; margin: 10mm 8mm 12mm 8mm; }

    .page {
      width: 210mm; min-height: 297mm; margin: auto; background: #fff; padding: 4mm; box-shadow: 0 2px 12px rgba(0,0,0,.08);
      display: flex; flex-direction: column; justify-content: flex-start;
    }

    .receipt {
      border: var(--border); border-radius: 4px; padding: 4px; page-break-inside: avoid; font-size: 10px; margin-bottom: 3mm;
    }

    .title { text-align: center; font-weight: 800; letter-spacing: .04em; color: var(--heading); font-size: 13px; }
    .company { text-align: center; margin-top: 2px; font-weight: 600; font-size: 11px; }

    .meta { display: grid; grid-template-columns: 1fr 1fr; gap: var(--gap); margin-top: 6px; font-size: 10px; }
    .meta .cell { border: 1px dashed #bbb; padding: 4px; border-radius: 4px; }
    .label { color: var(--muted); font-size: 9px; text-transform: uppercase; letter-spacing: .04em; }
    .value { margin-top: 2px; font-weight: 600; font-size: 10px; }

    .row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--gap); margin-top: 6px; font-size: 10px; }
    .row .block { border: 1px dashed #bbb; padding: 5px; border-radius: 4px; }
    .row .block .kv { display: flex; justify-content: space-between; gap: 6px; padding: 1px 0; }

    .tables { display: grid; grid-template-columns: 1fr 1fr; gap: var(--gap); margin-top: 6px; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    th, td { padding: 3px; border-bottom: 1px solid #e5e5e5; }
    th { text-align: left; font-size: 9px; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; }
    tfoot td { font-weight: 700; }
    .amount { text-align: right; white-space: nowrap; }

    .signature { height: 36px; display: flex; flex-direction: column; justify-content: flex-end; }
    .signature .line { height: 1px; background: #333; margin-top: auto; }
    .signature .who { text-align: center; font-size: 9px; color: var(--muted); margin-top: 3px; }

    .srno { margin-top: 5px; font-size: 9px; color: var(--muted); display: flex; justify-content: space-between; }

    .cut { position: relative; text-align: center; color: var(--cutline); margin: 2mm 0; font-size: 8px; user-select: none; }
    .cut:before, .cut:after { content: ""; position: absolute; top: 50%; width: 40%; border-top: 1px dashed var(--cutline); }
    .cut:before { left: 0; }
    .cut:after { right: 0; }

    @media print {
      body { background: #fff; }
      .page { box-shadow: none; padding: 4mm; }
      .receipt { break-inside: avoid; margin-bottom: 3mm; }
      .cut { margin: 2mm 0; }
    }
  </style>
</head>
<body>
  <div class="page">

    <!-- Recibo 1 -->
    <section class="receipt">
      <h2 class="title">RECIBO DE SALÁRIO</h2>
      <div class="company">{{ $companyName ?? 'Dembena Indústria e Comércio Lda' }}</div>

      <div class="meta">
        <div class="cell">
          <div class="label">NOME</div>
          <div class="value">{{ $employeeName ?? 'Ana Beatriz Lopes (Id: 14)' }}</div>
        </div>
        <div class="cell">
          <div class="label">MÊS</div>
          <div class="value">{{ $month ?? 'July 2025 • Data de referência: 31/07/2025' }}</div>
        </div>
        <div class="cell">
          <div class="label">CATEGORIA</div>
          <div class="value">{{ $category ?? 'N/A' }}</div>
        </div>
        <div class="cell">
          <div class="label">PERÍODO DE REFERÊNCIA</div>
          <div class="value">{{ $referencePeriod ?? '01/07/2025 - 31/07/2025' }}</div>
        </div>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">ID Emp #</div>
          <div class="value">{{ $employeeId ?? '—' }}</div>
        </div>
        <div class="block">
          <div class="kv"><span>Dias trabalhados</span><span>{{ $workedDays ?? '31' }}</span></div>
          <div class="kv"><span>Total de ausências</span><span>{{ $absences ?? '0' }}</span></div>
        </div>
        <div class="block">
          <div class="kv"><span>Horas extras</span><span>{{ $extraHours ?? '0' }}</span></div>
        </div>
      </div>

      <div class="tables">
        <table>
          <thead>
            <tr><th>REMUNERAÇÃO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>Salário Base</td><td class="amount">{{ number_format($baseSalary ?? 175000, 3, '.', ' ') }}</td></tr>
            <tr><td>Subsídio de Transporte</td><td class="amount">{{ number_format($transportSubsidy ?? 30000, 3, '.', ' ') }}</td></tr>
            <tr><td>Subsídio de Alimentação</td><td class="amount">{{ number_format($foodSubsidy ?? 12000, 3, '.', ' ') }}</td></tr>
            <tr><td>Subsídio de Natal</td><td class="amount">{{ number_format($christmasSubsidy ?? 87500, 3, '.', ' ') }}</td></tr>
            <tr><td>Subsídio de Férias</td><td class="amount">{{ number_format($holidaySubsidy ?? 87500, 3, '.', ' ') }}</td></tr>
            <tr><td>Bónus Perfil Funcionário</td><td class="amount">{{ number_format($profileBonus ?? 10000, 3, '.', ' ') }}</td></tr>
            @if(isset($positionSubsidy) && $positionSubsidy > 0)
            <tr><td>{{ __('messages.position_subsidy') }}</td><td class="amount">{{ number_format($positionSubsidy, 3, '.', ' ') }}</td></tr>
            @endif
            @if(isset($performanceSubsidy) && $performanceSubsidy > 0)
            <tr><td>{{ __('messages.performance_subsidy') }}</td><td class="amount">{{ number_format($performanceSubsidy, 3, '.', ' ') }}</td></tr>
            @endif
            <tr><td>Bónus Adicional Folha</td><td class="amount">{{ number_format($payrollBonus ?? 6000, 3, '.', ' ') }}</td></tr>
            <tr><td>Horas Extras</td><td class="amount">{{ number_format($overtimeHours ?? 2734.38, 3, '.', ' ') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Remunerações</td><td class="amount">{{ number_format($totalEarnings ?? 362734.38, 3, '.', ' ') }}</td></tr>
          </tfoot>
        </table>

        <table>
          <thead>
            <tr><th>DESCONTO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>IRT</td><td class="amount">{{ number_format($incomeTax ?? 42351.94, 3, '.', ' ') }}</td></tr>
            <tr><td>INSS (3%)</td><td class="amount">{{ number_format($socialSecurity ?? 5250, 3, '.', ' ') }}</td></tr>
            <tr><td>Desconto Subsídio Alimentação</td><td class="amount">{{ number_format($foodSubsidyDeduction ?? 1200, 3, '.', ' ') }}</td></tr>
            <tr><td>Adiantamentos Salariais</td><td class="amount">{{ number_format($salaryAdvances ?? 12857.14, 3, '.', ' ') }}</td></tr>
            @if(isset($salaryDiscountsDetailed) && $salaryDiscountsDetailed->count() > 1)
              @foreach($salaryDiscountsDetailed as $discount)
                <tr><td>{{ $discount['type_name'] }} ({{ $discount['count'] }})</td><td class="amount">{{ number_format($discount['total_amount'], 3, '.', ' ') }}</td></tr>
              @endforeach
            @else
              <tr><td>Descontos Salariais</td><td class="amount">{{ number_format($salaryDiscounts ?? 18000, 3, '.', ' ') }}</td></tr>
            @endif
            <tr><td>Deduções por Faltas (12 dias)</td><td class="amount">{{ number_format($absenceDeduction ?? 91304.35, 3, '.', ' ') }}</td></tr>
            <tr><td>Desconto por Atrasos (1 dia)</td><td class="amount">{{ number_format($lateDeduction ?? 994.32, 3, '.', ' ') }}</td></tr>
            <tr><td>Desconto por Faltas (12 dias)</td><td class="amount">{{ number_format($faultDeduction ?? 3804.35, 3, '.', ' ') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Descontos</td><td class="amount">{{ number_format($totalDeductions ?? 180194.13, 3, '.', ' ') }}</td></tr>
          </tfoot>
        </table>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">VENCIMENTO LÍQUIDO</div>
          <div class="value">{{ number_format($netSalary ?? 182540.25, 3, '.', ' ') }}</div>
        </div>
        <div class="block">
          <div class="label">DADOS BANCÁRIOS</div>
          <div class="kv"><span>Nome do banco</span><span>{{ $bankName ?? 'ACCESS BANK' }}</span></div>
          <div class="kv"><span>Número de conta</span><span>{{ $accountNumber ?? '123456' }}</span></div>
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
          <div class="value">{{ $paymentMethod ?? 'CASH' }}</div>
        </div>
        <div class="block" style="grid-column: span 2;">
          <div class="srno">
            <span>Sr. No. {{ $receiptNumber ?? 'July 2025' }}</span>
          </div>
        </div>
      </div>
    </section>

    <div class="cut">————— CORTE AQUI —————</div>

    <!-- Recibo 2 (Duplicata) -->
    <section class="receipt">
      <h2 class="title">RECIBO DE SALÁRIO</h2>
      <div class="company">{{ $companyName ?? 'Dembena Indústria e Comércio Lda' }}</div>

      <div class="meta">
        <div class="cell">
          <div class="label">NOME</div>
          <div class="value">{{ $employeeName ?? 'Ana Beatriz Lopes (Id: 14)' }}</div>
        </div>
        <div class="cell">
          <div class="label">MÊS</div>
          <div class="value">{{ $month ?? 'July 2025 • Data de referência: 31/07/2025' }}</div>
        </div>
        <div class="cell">
          <div class="label">CATEGORIA</div>
          <div class="value">{{ $category ?? 'N/A' }}</div>
        </div>
        <div class="cell">
          <div class="label">PERÍODO DE REFERÊNCIA</div>
          <div class="value">{{ $referencePeriod ?? '01/07/2025 - 31/07/2025' }}</div>
        </div>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">ID Emp #</div>
          <div class="value">{{ $employeeId ?? '—' }}</div>
        </div>
        <div class="block">
          <div class="kv"><span>Dias trabalhados</span><span>{{ $workedDays ?? '31' }}</span></div>
          <div class="kv"><span>Total de ausências</span><span>{{ $absences ?? '0' }}</span></div>
        </div>
        <div class="block">
          <div class="kv"><span>Horas extras</span><span>{{ $extraHours ?? '0' }}</span></div>
        </div>
      </div>

      <div class="tables">
        <table>
          <thead>
            <tr><th>REMUNERAÇÃO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>Salário Base</td><td class="amount">{{ number_format($baseSalary ?? 175000, 3, '.', ' ') }}</td></tr>
            <tr><td>Subsídio de Transporte</td><td class="amount">{{ number_format($transportSubsidy ?? 30000, 3, '.', ' ') }}</td></tr>
            <tr><td>Subsídio de Alimentação</td><td class="amount">{{ number_format($foodSubsidy ?? 12000, 3, '.', ' ') }}</td></tr>
            <tr><td>Subsídio de Natal</td><td class="amount">{{ number_format($christmasSubsidy ?? 87500, 3, '.', ' ') }}</td></tr>
            <tr><td>Subsídio de Férias</td><td class="amount">{{ number_format($holidaySubsidy ?? 87500, 3, '.', ' ') }}</td></tr>
            <tr><td>Bónus Perfil Funcionário</td><td class="amount">{{ number_format($profileBonus ?? 10000, 3, '.', ' ') }}</td></tr>
            @if(isset($positionSubsidy) && $positionSubsidy > 0)
            <tr><td>{{ __('messages.position_subsidy') }}</td><td class="amount">{{ number_format($positionSubsidy, 3, '.', ' ') }}</td></tr>
            @endif
            @if(isset($performanceSubsidy) && $performanceSubsidy > 0)
            <tr><td>{{ __('messages.performance_subsidy') }}</td><td class="amount">{{ number_format($performanceSubsidy, 3, '.', ' ') }}</td></tr>
            @endif
            <tr><td>Bónus Adicional Folha</td><td class="amount">{{ number_format($payrollBonus ?? 6000, 3, '.', ' ') }}</td></tr>
            <tr><td>Horas Extras</td><td class="amount">{{ number_format($overtimeHours ?? 2734.38, 3, '.', ' ') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Remunerações</td><td class="amount">{{ number_format($totalEarnings ?? 362734.38, 3, '.', ' ') }}</td></tr>
          </tfoot>
        </table>

        <table>
          <thead>
            <tr><th>DESCONTO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>IRT</td><td class="amount">{{ number_format($incomeTax ?? 42351.94, 3, '.', ' ') }}</td></tr>
            <tr><td>INSS (3%)</td><td class="amount">{{ number_format($socialSecurity ?? 5250, 3, '.', ' ') }}</td></tr>
            <tr><td>Desconto Subsídio Alimentação</td><td class="amount">{{ number_format($foodSubsidyDeduction ?? 1200, 3, '.', ' ') }}</td></tr>
            <tr><td>Adiantamentos Salariais</td><td class="amount">{{ number_format($salaryAdvances ?? 12857.14, 3, '.', ' ') }}</td></tr>
            @if(isset($salaryDiscountsDetailed) && $salaryDiscountsDetailed->count() > 1)
              @foreach($salaryDiscountsDetailed as $discount)
                <tr><td>{{ $discount['type_name'] }} ({{ $discount['count'] }})</td><td class="amount">{{ number_format($discount['total_amount'], 3, '.', ' ') }}</td></tr>
              @endforeach
            @else
              <tr><td>Descontos Salariais</td><td class="amount">{{ number_format($salaryDiscounts ?? 18000, 3, '.', ' ') }}</td></tr>
            @endif
            <tr><td>Deduções por Faltas (12 dias)</td><td class="amount">{{ number_format($absenceDeduction ?? 91304.35, 3, '.', ' ') }}</td></tr>
            <tr><td>Desconto por Atrasos (1 dia)</td><td class="amount">{{ number_format($lateDeduction ?? 994.32, 3, '.', ' ') }}</td></tr>
            <tr><td>Desconto por Faltas (12 dias)</td><td class="amount">{{ number_format($faultDeduction ?? 3804.35, 3, '.', ' ') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Descontos</td><td class="amount">{{ number_format($totalDeductions ?? 180194.13, 3, '.', ' ') }}</td></tr>
          </tfoot>
        </table>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">VENCIMENTO LÍQUIDO</div>
          <div class="value">{{ number_format($netSalary ?? 182540.25, 3, '.', ' ') }}</div>
        </div>
        <div class="block">
          <div class="label">DADOS BANCÁRIOS</div>
          <div class="kv"><span>Nome do banco</span><span>{{ $bankName ?? 'ACCESS BANK' }}</span></div>
          <div class="kv"><span>Número de conta</span><span>{{ $accountNumber ?? '123456' }}</span></div>
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
          <div class="value">{{ $paymentMethod ?? 'CASH' }}</div>
        </div>
        <div class="block" style="grid-column: span 2;">
          <div class="srno">
            <span>Sr. No. {{ $receiptNumber ?? 'July 2025' }}</span>
          </div>
        </div>
      </div>
    </section>

  </div>
</body>
</html>
