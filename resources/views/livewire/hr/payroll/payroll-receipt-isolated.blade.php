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
    body { background: #f5f5f5; color: #111; font-family: var(--font); font-size: 10px; }

    @page { size: A4; margin: 5mm 5mm 5mm 5mm; }

    .page {
      width: 210mm; height: 297mm; margin: auto; background: #fff; padding: 3mm;
      box-shadow: 0 2px 12px rgba(0,0,0,.08);
      display: flex; flex-direction: column; justify-content: space-between;
    }

    .receipt {
      border: var(--border); border-radius: 3px; padding: 3px; 
      page-break-inside: avoid; font-size: 10px; flex: 1;
      display: flex; flex-direction: column;
    }

    .title { text-align: center; font-weight: 800; letter-spacing: .04em; color: var(--heading); font-size: 13px; margin: 0; }
    .company { text-align: center; margin-top: 1px; font-weight: 600; font-size: 11px; }

    .meta { display: grid; grid-template-columns: 1fr 1fr; gap: var(--gap); margin-top: 3px; font-size: 10px; }
    .meta .cell { border: 1px dashed #bbb; padding: 2px 3px; border-radius: 3px; }
    .label { color: var(--muted); font-size: 8px; text-transform: uppercase; letter-spacing: .03em; }
    .value { margin-top: 1px; font-weight: 600; font-size: 10px; }

    .row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--gap); margin-top: 3px; font-size: 10px; }
    .row .block { border: 1px dashed #bbb; padding: 2px 3px; border-radius: 3px; }
    .row .block .kv { display: flex; justify-content: space-between; gap: 4px; padding: 0; line-height: 1.3; }

    .tables { display: grid; grid-template-columns: 1fr 1fr; gap: var(--gap); margin-top: 3px; flex: 1; }
    table { width: 100%; border-collapse: collapse; font-size: 10px; }
    th, td { padding: 1px 2px; border-bottom: 1px solid #e5e5e5; line-height: 1.2; }
    th { text-align: left; font-size: 8px; color: var(--muted); text-transform: uppercase; letter-spacing: .03em; }
    tfoot td { font-weight: 700; border-top: 1px solid #333; }
    .amount { text-align: right; white-space: nowrap; }

    .signature { height: 28px; display: flex; flex-direction: column; justify-content: flex-end; }
    .signature .line { height: 1px; background: #333; margin-top: auto; }
    .signature .who { text-align: center; font-size: 7px; color: var(--muted); margin-top: 2px; }

    .srno { font-size: 7px; color: var(--muted); display: flex; justify-content: space-between; }

    .cut { position: relative; text-align: center; color: var(--cutline); margin: 2mm 0; font-size: 7px; user-select: none; }
    .cut:before, .cut:after { content: ""; position: absolute; top: 50%; width: 40%; border-top: 1px dashed var(--cutline); }
    .cut:before { left: 0; }
    .cut:after { right: 0; }

    .footer-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--gap); margin-top: 3px; font-size: 10px; }
    .footer-row .block { border: 1px dashed #bbb; padding: 2px 3px; border-radius: 3px; }

    @media print {
      body { background: #fff; }
      .page { box-shadow: none; padding: 3mm; height: 297mm; }
      .receipt { break-inside: avoid; }
      .cut { margin: 1mm 0; }
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
          <div class="kv"><span>Dias úteis do mês</span><span>{{ $monthlyWorkingDays ?? 26 }}</span></div>
          <div class="kv"><span>Dias trabalhados</span><span>{{ $workedDays ?? '0' }}</span></div>
        </div>
        <div class="block">
          <div class="kv"><span>Total de ausências</span><span>{{ $absences ?? '0' }}</span></div>
          <div class="kv"><span>Horas extras</span><span>{{ number_format($extraHours ?? 0, 2) }}</span></div>
        </div>
      </div>

      <div class="tables">
        <table>
          <thead>
            <tr><th>REMUNERAÇÃO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>Salário Base</td><td class="amount">{{ number_format($baseSalary ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Transporte</td><td class="amount">{{ number_format($transportSubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Alimentação</td><td class="amount">{{ number_format($foodSubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio Noturno ({{ $nightShiftDays ?? 0 }} dias)</td><td class="amount">{{ number_format($nightShiftAllowance ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Natal</td><td class="amount">{{ number_format($christmasSubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Férias</td><td class="amount">{{ number_format($holidaySubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Abono de Família</td><td class="amount">{{ number_format($familyAllowance ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Cargo</td><td class="amount">{{ number_format($positionSubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Desempenho</td><td class="amount">{{ number_format($performanceSubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Bónus Adicional</td><td class="amount">{{ number_format($payrollBonus ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Horas Extras</td><td class="amount">{{ number_format($overtimeAmount ?? 0, 2, ',', ' ') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Remunerações</td><td class="amount">{{ number_format($totalEarnings ?? 0, 2, ',', ' ') }}</td></tr>
          </tfoot>
        </table>

        <table>
          <thead>
            <tr><th>DESCONTO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>IRT</td><td class="amount">{{ number_format($incomeTax ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>INSS (3%)</td><td class="amount">{{ number_format($socialSecurity ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Desconto Subsídio Alimentação</td><td class="amount">{{ number_format($foodSubsidyDeduction ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Adiantamentos Salariais</td><td class="amount">{{ number_format($salaryAdvances ?? 0, 2, ',', ' ') }}</td></tr>
            @if(isset($salaryDiscountsDetailed) && (is_array($salaryDiscountsDetailed) ? count($salaryDiscountsDetailed) > 0 : $salaryDiscountsDetailed->count() > 0))
              @foreach($salaryDiscountsDetailed as $discount)
                <tr><td>{{ $discount['type_name'] }}</td><td class="amount">{{ number_format($discount['total_amount'], 2, ',', ' ') }}</td></tr>
              @endforeach
            @else
              <tr><td>Descontos Salariais</td><td class="amount">{{ number_format($salaryDiscounts ?? 0, 2, ',', ' ') }}</td></tr>
            @endif
            <tr><td>Faltas ({{ $absenceDays ?? 0 }} dias)</td><td class="amount">{{ number_format($absenceDeduction ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Atrasos ({{ $lateDays ?? 0 }} dias)</td><td class="amount">{{ number_format($lateDeduction ?? 0, 2, ',', ' ') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Descontos</td><td class="amount">{{ number_format($totalDeductions ?? 0, 2, ',', ' ') }}</td></tr>
          </tfoot>
        </table>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">VENCIMENTO LÍQUIDO</div>
          <div class="value">{{ number_format($netSalary ?? 0, 2, ',', ' ') }}</div>
        </div>
        <div class="block">
          <div class="label">DADOS BANCÁRIOS</div>
          <div class="kv"><span>Nome do banco</span><span>{{ $bankName ?? 'N/A' }}</span></div>
          <div class="kv"><span>Número de conta</span><span>{{ $accountNumber ?? 'N/A' }}</span></div>
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
            <span>Sr. No. {{ $receiptNumber ?? 'N/A' }}</span>
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
          <div class="value">{{ $employeeName ?? 'N/A' }}</div>
        </div>
        <div class="cell">
          <div class="label">MÊS</div>
          <div class="value">{{ $month ?? 'N/A' }}</div>
        </div>
        <div class="cell">
          <div class="label">CATEGORIA</div>
          <div class="value">{{ $category ?? 'N/A' }}</div>
        </div>
        <div class="cell">
          <div class="label">PERÍODO DE REFERÊNCIA</div>
          <div class="value">{{ $referencePeriod ?? 'N/A' }}</div>
        </div>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">ID Emp #</div>
          <div class="value">{{ $employeeId ?? '—' }}</div>
        </div>
        <div class="block">
          <div class="kv"><span>Dias úteis do mês</span><span>{{ $monthlyWorkingDays ?? 26 }}</span></div>
          <div class="kv"><span>Dias trabalhados</span><span>{{ $workedDays ?? '0' }}</span></div>
        </div>
        <div class="block">
          <div class="kv"><span>Total de ausências</span><span>{{ $absences ?? '0' }}</span></div>
          <div class="kv"><span>Horas extras</span><span>{{ number_format($extraHours ?? 0, 2) }}</span></div>
        </div>
      </div>

      <div class="tables">
        <table>
          <thead>
            <tr><th>REMUNERAÇÃO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>Salário Base</td><td class="amount">{{ number_format($baseSalary ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Transporte</td><td class="amount">{{ number_format($transportSubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Alimentação</td><td class="amount">{{ number_format($foodSubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio Noturno ({{ $nightShiftDays ?? 0 }} dias)</td><td class="amount">{{ number_format($nightShiftAllowance ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Natal</td><td class="amount">{{ number_format($christmasSubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Férias</td><td class="amount">{{ number_format($holidaySubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Abono de Família</td><td class="amount">{{ number_format($familyAllowance ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Cargo</td><td class="amount">{{ number_format($positionSubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Subsídio de Desempenho</td><td class="amount">{{ number_format($performanceSubsidy ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Bónus Adicional</td><td class="amount">{{ number_format($payrollBonus ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Horas Extras</td><td class="amount">{{ number_format($overtimeAmount ?? 0, 2, ',', ' ') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Remunerações</td><td class="amount">{{ number_format($totalEarnings ?? 0, 2, ',', ' ') }}</td></tr>
          </tfoot>
        </table>

        <table>
          <thead>
            <tr><th>DESCONTO</th><th class="amount">KZ</th></tr>
          </thead>
          <tbody>
            <tr><td>IRT</td><td class="amount">{{ number_format($incomeTax ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>INSS (3%)</td><td class="amount">{{ number_format($socialSecurity ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Desconto Subsídio Alimentação</td><td class="amount">{{ number_format($foodSubsidyDeduction ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Adiantamentos Salariais</td><td class="amount">{{ number_format($salaryAdvances ?? 0, 2, ',', ' ') }}</td></tr>
            @if(isset($salaryDiscountsDetailed) && (is_array($salaryDiscountsDetailed) ? count($salaryDiscountsDetailed) > 0 : $salaryDiscountsDetailed->count() > 0))
              @foreach($salaryDiscountsDetailed as $discount)
                <tr><td>{{ $discount['type_name'] }}</td><td class="amount">{{ number_format($discount['total_amount'], 2, ',', ' ') }}</td></tr>
              @endforeach
            @else
              <tr><td>Descontos Salariais</td><td class="amount">{{ number_format($salaryDiscounts ?? 0, 2, ',', ' ') }}</td></tr>
            @endif
            <tr><td>Faltas ({{ $absenceDays ?? 0 }} dias)</td><td class="amount">{{ number_format($absenceDeduction ?? 0, 2, ',', ' ') }}</td></tr>
            <tr><td>Atrasos ({{ $lateDays ?? 0 }} dias)</td><td class="amount">{{ number_format($lateDeduction ?? 0, 2, ',', ' ') }}</td></tr>
          </tbody>
          <tfoot>
            <tr><td>Total Descontos</td><td class="amount">{{ number_format($totalDeductions ?? 0, 2, ',', ' ') }}</td></tr>
          </tfoot>
        </table>
      </div>

      <div class="row">
        <div class="block">
          <div class="label">VENCIMENTO LÍQUIDO</div>
          <div class="value">{{ number_format($netSalary ?? 0, 2, ',', ' ') }}</div>
        </div>
        <div class="block">
          <div class="label">DADOS BANCÁRIOS</div>
          <div class="kv"><span>Nome do banco</span><span>{{ $bankName ?? 'N/A' }}</span></div>
          <div class="kv"><span>Número de conta</span><span>{{ $accountNumber ?? 'N/A' }}</span></div>
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
            <span>Sr. No. {{ $receiptNumber ?? 'N/A' }}</span>
          </div>
        </div>
      </div>
    </section>

  </div>
</body>
</html>
