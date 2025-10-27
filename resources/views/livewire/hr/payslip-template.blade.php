<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Recibo de Salário</title>
  <style>
    /* CSS Isolado para Payslip Template - Namespace: .payslip-template-container */
    .payslip-template-container {
      --gap: 6px;
      --border: 1px solid #222;
      --muted: #555;
      --heading: #000;
      --cutline: #888;
      --font: "Inter", system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial, "Apple Color Emoji", "Segoe UI Emoji";
    }

    .payslip-template-container * { 
      box-sizing: border-box; 
    }
    
    .payslip-template-container {
      height: 100vh;
      margin: 0; 
      padding: 0; 
      background: #f5f5f5; 
      color: #111; 
      font-family: var(--font);
    }

    @page { size: A4; margin: 10mm 8mm 12mm 8mm; }

    .payslip-template-container .page {
      width: 210mm; min-height: 297mm; margin: auto; background: #fff; padding: 6mm; box-shadow: 0 2px 12px rgba(0,0,0,.08);
      display: flex; flex-direction: column; justify-content: flex-start;
    }

    .payslip-template-container .receipt {
      border: var(--border); border-radius: 4px; padding: 6px; page-break-inside: avoid; font-size: 11px; margin-bottom: 8mm;
    }

    .payslip-template-container .title { 
      text-align: center; font-weight: 800; letter-spacing: .04em; color: var(--heading); font-size: 13px; 
    }
    
    .payslip-template-container .company { 
      text-align: center; margin-top: 2px; font-weight: 600; font-size: 11px; 
    }

    .payslip-template-container .meta { 
      display: grid; grid-template-columns: 1fr 1fr; gap: var(--gap); margin-top: 6px; font-size: 10px; 
    }
    
    .payslip-template-container .meta .cell { 
      border: 1px dashed #bbb; padding: 4px; border-radius: 4px; 
    }
    
    .payslip-template-container .label { 
      color: var(--muted); font-size: 9px; text-transform: uppercase; letter-spacing: .04em; 
    }
    
    .payslip-template-container .value { 
      margin-top: 2px; font-weight: 600; font-size: 10px; 
    }

    .payslip-template-container .row { 
      display: grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--gap); margin-top: 6px; font-size: 10px; 
    }
    
    .payslip-template-container .row .block { 
      border: 1px dashed #bbb; padding: 5px; border-radius: 4px; 
    }
    
    .payslip-template-container .row .block .kv { 
      display: flex; justify-content: space-between; gap: 6px; padding: 1px 0; 
    }

    .payslip-template-container .tables { 
      display: grid; grid-template-columns: 1fr 1fr; gap: var(--gap); margin-top: 6px; 
    }
    
    .payslip-template-container table { 
      width: 100%; border-collapse: collapse; font-size: 10px; 
    }
    
    .payslip-template-container th, 
    .payslip-template-container td { 
      padding: 3px; border-bottom: 1px solid #e5e5e5; 
    }
    
    .payslip-template-container th { 
      text-align: left; font-size: 9px; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; 
    }
    
    .payslip-template-container tfoot td { 
      font-weight: 700; 
    }
    
    .payslip-template-container .amount { 
      text-align: right; white-space: nowrap; 
    }

    .payslip-template-container .signature { 
      height: 36px; display: flex; flex-direction: column; justify-content: flex-end; 
    }
    
    .payslip-template-container .signature .line { 
      height: 1px; background: #333; margin-top: auto; 
    }
    
    .payslip-template-container .signature .who { 
      text-align: center; font-size: 9px; color: var(--muted); margin-top: 3px; 
    }

    .payslip-template-container .srno { 
      margin-top: 5px; font-size: 9px; color: var(--muted); display: flex; justify-content: space-between; 
    }

    .payslip-template-container .cut { 
      position: relative; text-align: center; color: var(--cutline); margin: 5mm 0; font-size: 9px; user-select: none; 
    }
    
    .payslip-template-container .cut:before, 
    .payslip-template-container .cut:after { 
      content: ""; position: absolute; top: 50%; width: 40%; border-top: 1px dashed var(--cutline); 
    }
    
    .payslip-template-container .cut:before { 
      left: 0; 
    }
    
    .payslip-template-container .cut:after { 
      right: 0; 
    }

    @media print {
      .payslip-template-container { 
        background: #fff; 
      }
      
      .payslip-template-container .page { 
        box-shadow: none; padding: 6mm; 
      }
      
      .payslip-template-container .receipt { 
        break-inside: avoid; margin-bottom: 8mm; 
      }
      
      .payslip-template-container .cut { 
        margin: 5mm 0; 
      }
    }
  </style>
</head>
<body>
  <div class="payslip-template-container">
    <div class="page">

      <!-- Recibo 1 -->
      <section class="receipt">
        <h2 class="title">RECIBO DE SALÁRIO</h2>
        <div class="company">Dembena Industria e Comercio Lda</div>
        <div class="meta">
          <div class="cell">
            <div class="label">Nome</div>
            <div class="value">SSSSSS (Id: — )</div>
          </div>
          <div class="cell">
            <div class="label">Mês</div>
            <div class="value">July 2025 • Data de referência: 31/07/2025</div>
          </div>
          <div class="cell">
            <div class="label">Categoria</div>
            <div class="value">N/A</div>
          </div>
          <div class="cell">
            <div class="label">Período de referência</div>
            <div class="value">01/07/2025 – 31/07/2025</div>
          </div>
        </div>
        <div class="row">
          <div class="block">
            <div class="label">ID Emp #</div>
            <div class="value">—</div>
          </div>
          <div class="block">
            <div class="kv"><span>Dias trabalhados</span><span>31</span></div>
            <div class="kv"><span>Total de ausências</span><span>0</span></div>
          </div>
          <div class="block">
            <div class="kv"><span>Horas extras</span><span>0</span></div>
          </div>
        </div>
        <div class="tables">
          <table>
            <thead>
              <tr><th>Remuneração</th><th class="amount">KZ</th></tr>
            </thead>
            <tbody>
              <tr><td>Vencimento Base</td><td class="amount">450.498</td></tr>
              <tr><td>Subsídio Transporte</td><td class="amount">0</td></tr>
              <tr><td>Subsídio de Férias</td><td class="amount">0</td></tr>
              <tr><td>Subsídio Alimentação</td><td class="amount">0</td></tr>
              <tr><td>Outros (Telephone / Mobile)</td><td class="amount">0</td></tr>
              <tr><td>Prémio</td><td class="amount">0</td></tr>
              <tr><td>Gratuital</td><td class="amount">0</td></tr>
            </tbody>
            <tfoot>
              <tr><td>Total Remunerações</td><td class="amount">450.498</td></tr>
            </tfoot>
          </table>
          <table>
            <thead>
              <tr><th>Desconto</th><th class="amount">KZ</th></tr>
            </thead>
            <tbody>
              <tr><td>IRT</td><td class="amount">0</td></tr>
              <tr><td>Segurança social Tax 3%</td><td class="amount">11.865</td></tr>
              <tr><td>Faltas</td><td class="amount">0</td></tr>
              <tr><td>Adiantamento</td><td class="amount">0</td></tr>
              <tr><td>Subsídio Alimentação</td><td class="amount">0</td></tr>
              <tr><td>Outras Deduções</td><td class="amount">0</td></tr>
              <tr><td>Sindicato de Trabalhadores</td><td class="amount">0</td></tr>
            </tbody>
            <tfoot>
              <tr><td>Total Descontos</td><td class="amount">103.748</td></tr>
            </tfoot>
          </table>
        </div>
        <div class="row">
          <div class="block">
            <div class="label">Vencimento líquido</div>
            <div class="value">291.750</div>
            <div class="label">Modo de pagamento</div>
            <div class="value">CASH</div>
          </div>
          <div class="block">
            <div class="label">Dados Bancários</div>
            <div class="kv"><span>Nome do banco</span><span>ACCESS BANK</span></div>
            <div class="kv"><span>Número de conta</span><span>123456</span></div>
          </div>
          <div class="block signature">
            <div class="line"></div>
            <div class="who">Assinatura do trabalhador</div>
          </div>
        </div>
        <div class="srno">Sr. No: July 2025</div>
      </section>

      <div class="cut">— CORTE AQUI —</div>

      <!-- Recibo 2 (duplicado) -->
      <section class="receipt">
        <h2 class="title">RECIBO DE SALÁRIO</h2>
        <div class="company">Dembena Industria e Comercio Lda</div>
        <div class="meta">
          <div class="cell">
            <div class="label">Nome</div>
            <div class="value">SSSSSS (Id: — )</div>
          </div>
          <div class="cell">
            <div class="label">Mês</div>
            <div class="value">July 2025 • Data de referência: 31/07/2025</div>
          </div>
          <div class="cell">
            <div class="label">Categoria</div>
            <div class="value">N/A</div>
          </div>
          <div class="cell">
            <div class="label">Período de referência</div>
            <div class="value">01/07/2025 – 31/07/2025</div>
          </div>
        </div>
        <div class="row">
          <div class="block">
            <div class="label">ID Emp #</div>
            <div class="value">—</div>
          </div>
          <div class="block">
            <div class="kv"><span>Dias trabalhados</span><span>31</span></div>
            <div class="kv"><span>Total de ausências</span><span>0</span></div>
          </div>
          <div class="block">
            <div class="kv"><span>Horas extras</span><span>0</span></div>
          </div>
        </div>
        <div class="tables">
          <table>
            <thead>
              <tr><th>Remuneração</th><th class="amount">KZ</th></tr>
            </thead>
            <tbody>
              <tr><td>Vencimento Base</td><td class="amount">450.498</td></tr>
              <tr><td>Subsídio Transporte</td><td class="amount">0</td></tr>
              <tr><td>Subsídio de Férias</td><td class="amount">0</td></tr>
              <tr><td>Subsídio Alimentação</td><td class="amount">0</td></tr>
              <tr><td>Outros (Telephone / Mobile)</td><td class="amount">0</td></tr>
              <tr><td>Prémio</td><td class="amount">0</td></tr>
              <tr><td>Gratuital</td><td class="amount">0</td></tr>
            </tbody>
            <tfoot>
              <tr><td>Total Remunerações</td><td class="amount">450.498</td></tr>
            </tfoot>
          </table>
          <table>
            <thead>
              <tr><th>Desconto</th><th class="amount">KZ</th></tr>
            </thead>
            <tbody>
              <tr><td>IRT</td><td class="amount">0</td></tr>
              <tr><td>Segurança social Tax 3%</td><td class="amount">11.865</td></tr>
              <tr><td>Faltas</td><td class="amount">0</td></tr>
              <tr><td>Adiantamento</td><td class="amount">0</td></tr>
              <tr><td>Subsídio Alimentação</td><td class="amount">0</td></tr>
              <tr><td>Outras Deduções</td><td class="amount">0</td></tr>
              <tr><td>Sindicato de Trabalhadores</td><td class="amount">0</td></tr>
            </tbody>
            <tfoot>
              <tr><td>Total Descontos</td><td class="amount">103.748</td></tr>
            </tfoot>
          </table>
        </div>
        <div class="row">
          <div class="block">
            <div class="label">Vencimento líquido</div>
            <div class="value">291.750</div>
            <div class="label">Modo de pagamento</div>
            <div class="value">CASH</div>
          </div>
          <div class="block">
            <div class="label">Dados Bancários</div>
            <div class="kv"><span>Nome do banco</span><span>ACCESS BANK</span></div>
            <div class="kv"><span>Número de conta</span><span>123456</span></div>
          </div>
          <div class="block signature">
            <div class="line"></div>
            <div class="who">Assinatura do trabalhador</div>
          </div>
        </div>
        <div class="srno">Sr. No: July 2025</div>
      </section>
    </div>
  </div>
</body>
</html>
