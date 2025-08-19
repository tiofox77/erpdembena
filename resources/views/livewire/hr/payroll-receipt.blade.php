<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Recibo de Salário</title>
  <style>
    /* CSS Isolado - Reset Total para evitar herança */
    .payroll-receipt {
      --gap: 6px;
      --border: 1px solid #222;
      --muted: #555;
      --heading: #000;
      --cutline: #888;
      --font: "Inter", system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial, "Apple Color Emoji", "Segoe UI Emoji";
      
      /* Reset total */
      all: initial !important;
      display: block !important;
      position: static !important;
      height: 100vh !important;
      margin: 0 !important;
      padding: 0 !important;
      background: #f5f5f5 !important;
      color: #111 !important;
      font-family: var(--font) !important;
    }

    .payroll-receipt *, 
    .payroll-receipt *::before, 
    .payroll-receipt *::after { 
      all: unset !important;
      display: revert !important;
      box-sizing: border-box !important;
    }

    @page { size: A4; margin: 10mm 8mm 12mm 8mm; }

    .payroll-receipt .page {
      width: 210mm !important; min-height: 297mm !important; margin: auto !important; background: #fff !important; padding: 6mm !important; 
      box-shadow: 0 2px 12px rgba(0,0,0,.08) !important;
      display: flex !important; flex-direction: column !important; justify-content: flex-start !important;
    }

    .payroll-receipt .receipt {
      border: var(--border) !important; border-radius: 4px !important; padding: 6px !important; 
      page-break-inside: avoid !important; font-size: 11px !important; margin-bottom: 8mm !important;
    }

    .payroll-receipt .title { 
      text-align: center !important; font-weight: 800 !important; letter-spacing: .04em !important; 
      color: var(--heading) !important; font-size: 13px !important; 
    }
    .payroll-receipt .company { 
      text-align: center !important; margin-top: 2px !important; font-weight: 600 !important; font-size: 11px !important; 
    }

    .payroll-receipt .meta { 
      display: grid !important; grid-template-columns: 1fr 1fr !important; gap: var(--gap) !important; 
      margin-top: 6px !important; font-size: 10px !important; 
    }
    .payroll-receipt .meta .cell { 
      border: 1px dashed #bbb !important; padding: 4px !important; border-radius: 4px !important; 
    }
    .payroll-receipt .label { 
      color: var(--muted) !important; font-size: 9px !important; text-transform: uppercase !important; letter-spacing: .04em !important; 
    }
    .payroll-receipt .value { 
      margin-top: 2px !important; font-weight: 600 !important; font-size: 10px !important; 
    }

    .payroll-receipt .row { 
      display: grid !important; grid-template-columns: 1fr 1fr 1fr !important; gap: var(--gap) !important; 
      margin-top: 6px !important; font-size: 10px !important; 
    }
    .payroll-receipt .row .block { 
      border: 1px dashed #bbb !important; padding: 5px !important; border-radius: 4px !important; 
    }
    .payroll-receipt .row .block .kv { 
      display: flex !important; justify-content: space-between !important; gap: 6px !important; padding: 1px 0 !important; 
    }

    .payroll-receipt .tables { 
      display: grid !important; grid-template-columns: 1fr 1fr !important; gap: var(--gap) !important; margin-top: 6px !important; 
    }
    .payroll-receipt table { 
      width: 100% !important; border-collapse: collapse !important; font-size: 10px !important; 
    }
    .payroll-receipt th, .payroll-receipt td { 
      padding: 3px !important; border-bottom: 1px solid #e5e5e5 !important; 
    }
    .payroll-receipt th { 
      text-align: left !important; font-size: 9px !important; color: var(--muted) !important; 
      text-transform: uppercase !important; letter-spacing: .04em !important; 
    }
    .payroll-receipt tfoot td { 
      font-weight: 700 !important; 
    }
    .payroll-receipt .amount { 
      text-align: right !important; white-space: nowrap !important; 
    }

    .payroll-receipt .signature { 
      height: 36px !important; display: flex !important; flex-direction: column !important; justify-content: flex-end !important; 
    }
    .payroll-receipt .signature .line { 
      height: 1px !important; background: #333 !important; margin-top: auto !important; 
    }
    .payroll-receipt .signature .who { 
      text-align: center !important; font-size: 9px !important; color: var(--muted) !important; margin-top: 3px !important; 
    }

    .payroll-receipt .srno { 
      margin-top: 5px !important; font-size: 9px !important; color: var(--muted) !important; 
      display: flex !important; justify-content: space-between !important; 
    }

    .payroll-receipt .cut { 
      position: relative !important; text-align: center !important; color: var(--cutline) !important; 
      margin: 5mm 0 !important; font-size: 9px !important; user-select: none !important; 
    }
    .payroll-receipt .cut:before, .payroll-receipt .cut:after { 
      content: "" !important; position: absolute !important; top: 50% !important; width: 40% !important; 
      border-top: 1px dashed var(--cutline) !important; 
    }
    .payroll-receipt .cut:before { left: 0 !important; }
    .payroll-receipt .cut:after { right: 0 !important; }

    @media print {
      .payroll-receipt { background: #fff !important; }
      .payroll-receipt .page { box-shadow: none !important; padding: 6mm !important; }
      .payroll-receipt .receipt { break-inside: avoid !important; margin-bottom: 8mm !important; }
      .payroll-receipt .cut { margin: 5mm 0 !important; }
    }
  </style>
</head>
<body>
  <div class="payroll-receipt">
    <div class="page">

      <!-- Recibo 1 -->
      <section class="receipt">
        <h2 class="title">RECIBO DE SALÁRIO</h2>
        <div class="company">{{ $company_name ?? 'Dembena Industria e Comercio Lda' }}</div>
        <div class="meta">
          <div class="cell">
            <div class="label">Nome</div>
            <div class="value">{{ $employee->name ?? 'N/A' }} (Id: {{ $employee->id ?? '—' }} )</div>
          </div>
          <div class="cell">
            <div class="label">Mês</div>
            <div class="value">{{ $period_name ?? 'N/A' }} • Data de referência: {{ $reference_date ?? 'N/A' }}</div>
          </div>
          <div class="cell">
            <div class="label">Categoria</div>
            <div class="value">{{ $employee->job_title ?? 'N/A' }}</div>
          </div>
          <div class="cell">
            <div class="label">Período de referência</div>
            <div class="value">{{ $period_start ?? 'N/A' }} – {{ $period_end ?? 'N/A' }}</div>
          </div>
        </div>
        <div class="row">
          <div class="block">
            <div class="label">ID Emp #</div>
            <div class="value">{{ $employee->employee_number ?? '—' }}</div>
          </div>
          <div class="block">
            <div class="kv"><span>Dias trabalhados</span><span>{{ $worked_days ?? 0 }}</span></div>
            <div class="kv"><span>Total de ausências</span><span>{{ $total_absences ?? 0 }}</span></div>
          </div>
          <div class="block">
            <div class="kv"><span>Horas extras</span><span>{{ $overtime_hours ?? 0 }}</span></div>
          </div>
        </div>
        <div class="tables">
          <table>
            <thead>
              <tr><th>Remuneração</th><th class="amount">{{ $currency ?? 'KZ' }}</th></tr>
            </thead>
            <tbody>
              <tr><td>Vencimento Base</td><td class="amount">{{ number_format($base_salary ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Subsídio Transporte</td><td class="amount">{{ number_format($transport_allowance ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Subsídio de Férias</td><td class="amount">{{ number_format($vacation_allowance ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Subsídio Alimentação</td><td class="amount">{{ number_format($meal_allowance ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Outros (Telephone / Mobile)</td><td class="amount">{{ number_format($phone_allowance ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Prémio</td><td class="amount">{{ number_format($bonus ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Gratuital</td><td class="amount">{{ number_format($gratuity ?? 0, 0, ',', '.') }}</td></tr>
            </tbody>
            <tfoot>
              <tr><td>Total Remunerações</td><td class="amount">{{ number_format($total_earnings ?? 0, 0, ',', '.') }}</td></tr>
            </tfoot>
          </table>
          <table>
            <thead>
              <tr><th>Desconto</th><th class="amount">{{ $currency ?? 'KZ' }}</th></tr>
            </thead>
            <tbody>
              <tr><td>IRT</td><td class="amount">{{ number_format($irt ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Segurança social Tax 3%</td><td class="amount">{{ number_format($social_security ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Faltas</td><td class="amount">{{ number_format($absences_deduction ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Adiantamento</td><td class="amount">{{ number_format($advance ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Subsídio Alimentação</td><td class="amount">{{ number_format($meal_deduction ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Outras Deduções</td><td class="amount">{{ number_format($other_deductions ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Sindicato de Trabalhadores</td><td class="amount">{{ number_format($union_fee ?? 0, 0, ',', '.') }}</td></tr>
            </tbody>
            <tfoot>
              <tr><td>Total Descontos</td><td class="amount">{{ number_format($total_deductions ?? 0, 0, ',', '.') }}</td></tr>
            </tfoot>
          </table>
        </div>
        <div class="row">
          <div class="block">
            <div class="label">Vencimento líquido</div>
            <div class="value">{{ number_format($net_salary ?? 0, 0, ',', '.') }}</div>
            <div class="label">Modo de pagamento</div>
            <div class="value">{{ $payment_method ?? 'CASH' }}</div>
          </div>
          <div class="block">
            <div class="label">Dados Bancários</div>
            <div class="kv"><span>Nome do banco</span><span>{{ $bank_name ?? 'N/A' }}</span></div>
            <div class="kv"><span>Número de conta</span><span>{{ $account_number ?? 'N/A' }}</span></div>
          </div>
          <div class="block signature">
            <div class="line"></div>
            <div class="who">Assinatura do trabalhador</div>
          </div>
        </div>
        <div class="srno">Sr. No: {{ $receipt_number ?? $period_name ?? 'N/A' }}</div>
      </section>

      <div class="cut">— CORTE AQUI —</div>

      <!-- Recibo 2 (duplicado) -->
      <section class="receipt">
        <h2 class="title">RECIBO DE SALÁRIO</h2>
        <div class="company">{{ $company_name ?? 'Dembena Industria e Comercio Lda' }}</div>
        <div class="meta">
          <div class="cell">
            <div class="label">Nome</div>
            <div class="value">{{ $employee->name ?? 'N/A' }} (Id: {{ $employee->id ?? '—' }} )</div>
          </div>
          <div class="cell">
            <div class="label">Mês</div>
            <div class="value">{{ $period_name ?? 'N/A' }} • Data de referência: {{ $reference_date ?? 'N/A' }}</div>
          </div>
          <div class="cell">
            <div class="label">Categoria</div>
            <div class="value">{{ $employee->job_title ?? 'N/A' }}</div>
          </div>
          <div class="cell">
            <div class="label">Período de referência</div>
            <div class="value">{{ $period_start ?? 'N/A' }} – {{ $period_end ?? 'N/A' }}</div>
          </div>
        </div>
        <div class="row">
          <div class="block">
            <div class="label">ID Emp #</div>
            <div class="value">{{ $employee->employee_number ?? '—' }}</div>
          </div>
          <div class="block">
            <div class="kv"><span>Dias trabalhados</span><span>{{ $worked_days ?? 0 }}</span></div>
            <div class="kv"><span>Total de ausências</span><span>{{ $total_absences ?? 0 }}</span></div>
          </div>
          <div class="block">
            <div class="kv"><span>Horas extras</span><span>{{ $overtime_hours ?? 0 }}</span></div>
          </div>
        </div>
        <div class="tables">
          <table>
            <thead>
              <tr><th>Remuneração</th><th class="amount">{{ $currency ?? 'KZ' }}</th></tr>
            </thead>
            <tbody>
              <tr><td>Vencimento Base</td><td class="amount">{{ number_format($base_salary ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Subsídio Transporte</td><td class="amount">{{ number_format($transport_allowance ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Subsídio de Férias</td><td class="amount">{{ number_format($vacation_allowance ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Subsídio Alimentação</td><td class="amount">{{ number_format($meal_allowance ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Outros (Telephone / Mobile)</td><td class="amount">{{ number_format($phone_allowance ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Prémio</td><td class="amount">{{ number_format($bonus ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Gratuital</td><td class="amount">{{ number_format($gratuity ?? 0, 0, ',', '.') }}</td></tr>
            </tbody>
            <tfoot>
              <tr><td>Total Remunerações</td><td class="amount">{{ number_format($total_earnings ?? 0, 0, ',', '.') }}</td></tr>
            </tfoot>
          </table>
          <table>
            <thead>
              <tr><th>Desconto</th><th class="amount">{{ $currency ?? 'KZ' }}</th></tr>
            </thead>
            <tbody>
              <tr><td>IRT</td><td class="amount">{{ number_format($irt ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Segurança social Tax 3%</td><td class="amount">{{ number_format($social_security ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Faltas</td><td class="amount">{{ number_format($absences_deduction ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Adiantamento</td><td class="amount">{{ number_format($advance ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Subsídio Alimentação</td><td class="amount">{{ number_format($meal_deduction ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Outras Deduções</td><td class="amount">{{ number_format($other_deductions ?? 0, 0, ',', '.') }}</td></tr>
              <tr><td>Sindicato de Trabalhadores</td><td class="amount">{{ number_format($union_fee ?? 0, 0, ',', '.') }}</td></tr>
            </tbody>
            <tfoot>
              <tr><td>Total Descontos</td><td class="amount">{{ number_format($total_deductions ?? 0, 0, ',', '.') }}</td></tr>
            </tfoot>
          </table>
        </div>
        <div class="row">
          <div class="block">
            <div class="label">Vencimento líquido</div>
            <div class="value">{{ number_format($net_salary ?? 0, 0, ',', '.') }}</div>
            <div class="label">Modo de pagamento</div>
            <div class="value">{{ $payment_method ?? 'CASH' }}</div>
          </div>
          <div class="block">
            <div class="label">Dados Bancários</div>
            <div class="kv"><span>Nome do banco</span><span>{{ $bank_name ?? 'N/A' }}</span></div>
            <div class="kv"><span>Número de conta</span><span>{{ $account_number ?? 'N/A' }}</span></div>
          </div>
          <div class="block signature">
            <div class="line"></div>
            <div class="who">Assinatura do trabalhador</div>
          </div>
        </div>
        <div class="srno">Sr. No: {{ $receipt_number ?? $period_name ?? 'N/A' }}</div>
      </section>
    </div>
  </div>
</body>
</html>
