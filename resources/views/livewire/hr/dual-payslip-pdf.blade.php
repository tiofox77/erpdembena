<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Salário - {{ $employee['name'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
            line-height: 1.1;
            color: #000;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        
        .page {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 5mm;
        }
        
        .payslip {
            height: 140mm;
            border: 2px solid #000;
            margin-bottom: 3mm;
            padding: 3mm;
            position: relative;
            page-break-inside: avoid;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }
        
        .document-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 1mm;
        }
        
        .company-name {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .sr-number {
            position: absolute;
            top: 2mm;
            right: 5mm;
            font-size: 8px;
            font-weight: bold;
        }
        
        .employee-info {
            border: 1px solid #000;
            margin-bottom: 2mm;
        }
        
        .employee-row {
            display: flex;
            border-bottom: 1px solid #000;
            font-size: 8px;
        }
        
        .employee-row:last-child {
            border-bottom: none;
        }
        
        .employee-label {
            font-weight: bold;
            padding: 1mm;
            border-right: 1px solid #000;
            width: 15%;
            background-color: #f0f0f0;
        }
        
        .employee-value {
            padding: 1mm;
            width: 35%;
            border-right: 1px solid #000;
        }
        
        .attendance-section {
            border: 1px solid #000;
            margin-bottom: 2mm;
            padding: 1mm;
        }
        
        .attendance-row {
            display: flex;
            font-size: 8px;
            margin-bottom: 1mm;
        }
        
        .attendance-label {
            font-weight: bold;
            width: 40%;
        }
        
        .attendance-input {
            border: 1px solid #000;
            width: 15%;
            text-align: center;
            padding: 0.5mm;
            margin-right: 5mm;
        }
        
        .main-content {
            border: 1px solid #000;
            margin-bottom: 2mm;
        }
        
        .content-header {
            display: flex;
            border-bottom: 1px solid #000;
            font-size: 8px;
            font-weight: bold;
            background-color: #f0f0f0;
        }
        
        .remuneration-header {
            width: 50%;
            text-align: center;
            padding: 1mm;
            border-right: 1px solid #000;
        }
        
        .deductions-header {
            width: 50%;
            text-align: center;
            padding: 1mm;
        }
        
        .content-body {
            display: flex;
            min-height: 50mm;
        }
        
        .remuneration-section {
            width: 50%;
            border-right: 1px solid #000;
            padding: 2mm;
        }
        
        .deductions-section {
            width: 50%;
            padding: 2mm;
        }
        
        .line-item {
            display: flex;
            justify-content: space-between;
            font-size: 7px;
            margin-bottom: 1mm;
            padding: 0.5mm 0;
        }
        
        .item-description {
            width: 70%;
        }
        
        .item-amount {
            width: 30%;
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .total-line {
            border-top: 1px solid #000;
            font-weight: bold;
            padding-top: 1mm;
            margin-top: 2mm;
        }
        
        .net-salary {
            border: 2px solid #000;
            text-align: center;
            padding: 2mm;
            margin: 2mm 0;
            font-size: 10px;
            font-weight: bold;
        }
        
        .payment-method {
            border: 1px solid #000;
            padding: 1mm;
            margin-bottom: 2mm;
            font-size: 8px;
        }
        
        .bank-details {
            border: 1px solid #000;
            padding: 1mm;
            margin-bottom: 2mm;
            font-size: 8px;
        }
        
        .signature-section {
            text-align: center;
            margin-top: 5mm;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 60%;
            margin: 0 auto;
            padding-top: 1mm;
            font-size: 8px;
        }
        
        .cut-line {
            text-align: center;
            font-size: 8px;
            margin: 2mm 0;
            border-top: 1px dashed #000;
            padding-top: 1mm;
            font-weight: bold;
        }
        
        @media print {
            .page { margin: 0; }
            .payslip { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- PRIMEIRA VIA - FUNCIONÁRIO -->
        <div class="payslip">
            <div class="via-label">1ª VIA - FUNCIONÁRIO</div>
            
            <!-- Header -->
            <div class="header">
                <div class="company-name">{{ $company['name'] }}</div>
                <div class="company-info">
                    @if($company['phone'])Tel: {{ $company['phone'] }} | @endif
                    @if($company['email']){{ $company['email'] }} | @endif
                    @if($company['nif'])NIF: {{ $company['nif'] }}@endif
                </div>
                <div class="document-title">RECIBO DE PAGAMENTO</div>
            </div>
            
            <!-- Informações Principais -->
            <div class="info-section">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Funcionário:</td>
                        <td class="info-value">{{ $employee['name'] }}</td>
                        <td class="info-label">ID:</td>
                        <td class="info-value">{{ $employee['id'] }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Departamento:</td>
                        <td class="info-value">{{ $employee['department'] }}</td>
                        <td class="info-label">Cargo:</td>
                        <td class="info-value">{{ $employee['position'] }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Período:</td>
                        <td class="info-value">{{ $period['name'] }}</td>
                        <td class="info-label">Gerado em:</td>
                        <td class="info-value">{{ $generated_at }}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Rendimentos -->
            @if(count($earnings) > 0)
            <div class="earnings-section">
                <div class="section-title">RENDIMENTOS</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Descrição</th>
                            <th style="width: 15%;">Tipo</th>
                            <th style="width: 12%;">Horas</th>
                            <th style="width: 15%;">Taxa/Hr</th>
                            <th style="width: 10%;">%</th>
                            <th style="width: 23%;">Valor (AOA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($earnings as $earning)
                        <tr>
                            <td>{{ $earning['name'] }}</td>
                            <td>{{ $earning['type'] === 'earning' ? 'Salário' : ($earning['type'] === 'allowance' ? 'Subsídio' : ($earning['type'] === 'overtime' ? 'H. Extra' : 'Bónus')) }}</td>
                            <td class="amount">{{ isset($earning['hours']) ? number_format($earning['hours'], 1, ',', '.') : '-' }}</td>
                            <td class="amount">{{ isset($earning['rate']) ? number_format($earning['rate'], 2, ',', '.') : '-' }}</td>
                            <td class="amount">{{ isset($earning['percentage']) ? number_format($earning['percentage'], 1, ',', '.') : '-' }}</td>
                            <td class="amount">{{ number_format($earning['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="5"><strong>TOTAL RENDIMENTOS</strong></td>
                            <td class="amount"><strong>{{ number_format($totals['earnings'], 2, ',', '.') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Deduções -->
            @if(count($deductions) > 0)
            <div class="deductions-section">
                <div class="section-title">DEDUÇÕES</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Descrição</th>
                            <th style="width: 15%;">Tipo</th>
                            <th style="width: 12%;">Taxa %</th>
                            <th style="width: 15%;">Base Cálc.</th>
                            <th style="width: 10%;">Ref.</th>
                            <th style="width: 23%;">Valor (AOA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deductions as $deduction)
                        <tr>
                            <td>{{ $deduction['name'] }}</td>
                            <td>{{ $deduction['type'] === 'tax' ? 'Imposto' : ($deduction['type'] === 'advance' ? 'Adiantam.' : ($deduction['type'] === 'loan' ? 'Empréstimo' : 'Dedução')) }}</td>
                            <td class="amount">{{ isset($deduction['rate']) ? number_format($deduction['rate'], 2, ',', '.') : '-' }}</td>
                            <td class="amount">{{ isset($deduction['base']) ? number_format($deduction['base'], 0, ',', '.') : '-' }}</td>
                            <td class="amount">{{ isset($deduction['reference']) ? $deduction['reference'] : '-' }}</td>
                            <td class="amount">{{ number_format($deduction['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="5"><strong>TOTAL DEDUÇÕES</strong></td>
                            <td class="amount"><strong>{{ number_format($totals['deductions'], 2, ',', '.') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Resumo Final -->
            <div class="summary-section">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label" style="width: 25%;">Salário Base:</td>
                        <td class="summary-value" style="width: 25%;">{{ number_format($totals['base_salary'] ?? 0, 2, ',', '.') }} AOA</td>
                        <td class="summary-label" style="width: 25%;">Horas Extras:</td>
                        <td class="summary-value" style="width: 25%;">{{ number_format($totals['overtime'] ?? 0, 2, ',', '.') }} AOA</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Subsídios:</td>
                        <td class="summary-value">{{ number_format($totals['allowances'] ?? 0, 2, ',', '.') }} AOA</td>
                        <td class="summary-label">Adiantamentos:</td>
                        <td class="summary-value">{{ number_format($totals['advances'] ?? 0, 2, ',', '.') }} AOA</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Total Bruto:</td>
                        <td class="summary-value">{{ number_format($totals['earnings'], 2, ',', '.') }} AOA</td>
                        <td class="summary-label">Total Deduções:</td>
                        <td class="summary-value">{{ number_format($totals['deductions'], 2, ',', '.') }} AOA</td>
                    </tr>
                    <tr class="net-salary-row">
                        <td class="summary-label" colspan="2">SALÁRIO LÍQUIDO:</td>
                        <td class="summary-value" colspan="2">{{ number_format($totals['net_salary'], 2, ',', '.') }} AOA</td>
                    </tr>
                </table>
            </div>
            
            <!-- Assinaturas -->
            <div class="signatures">
                <table>
                    <tr>
                        <td>Assinatura do Empregador</td>
                        <td>Assinatura do Funcionário</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Linha de Corte -->
        <div class="cut-line">✂ CORTE AQUI ✂</div>
        
        <!-- SEGUNDA VIA - EMPRESA -->
        <div class="payslip">
            <div class="via-label">2ª VIA - EMPRESA</div>
            
            <!-- Header -->
            <div class="header">
                <div class="company-name">{{ $company['name'] }}</div>
                <div class="company-info">
                    @if($company['phone'])Tel: {{ $company['phone'] }} | @endif
                    @if($company['email']){{ $company['email'] }} | @endif
                    @if($company['nif'])NIF: {{ $company['nif'] }}@endif
                </div>
                <div class="document-title">RECIBO DE PAGAMENTO</div>
            </div>
            
            <!-- Informações Principais -->
            <div class="info-section">
                <table class="info-table">
                    <tr>
                        <td class="info-label">Funcionário:</td>
                        <td class="info-value">{{ $employee['name'] }}</td>
                        <td class="info-label">ID:</td>
                        <td class="info-value">{{ $employee['id'] }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Departamento:</td>
                        <td class="info-value">{{ $employee['department'] }}</td>
                        <td class="info-label">Cargo:</td>
                        <td class="info-value">{{ $employee['position'] }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Período:</td>
                        <td class="info-value">{{ $period['name'] }}</td>
                        <td class="info-label">Gerado em:</td>
                        <td class="info-value">{{ $generated_at }}</td>
                    </tr>
                </table>
            </div>
            
            <!-- Rendimentos -->
            @if(count($earnings) > 0)
            <div class="earnings-section">
                <div class="section-title">RENDIMENTOS</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Descrição</th>
                            <th style="width: 15%;">Tipo</th>
                            <th style="width: 12%;">Horas</th>
                            <th style="width: 15%;">Taxa/Hr</th>
                            <th style="width: 10%;">%</th>
                            <th style="width: 23%;">Valor (AOA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($earnings as $earning)
                        <tr>
                            <td>{{ $earning['name'] }}</td>
                            <td>{{ $earning['type'] === 'earning' ? 'Salário' : ($earning['type'] === 'allowance' ? 'Subsídio' : ($earning['type'] === 'overtime' ? 'H. Extra' : 'Bónus')) }}</td>
                            <td class="amount">{{ isset($earning['hours']) ? number_format($earning['hours'], 1, ',', '.') : '-' }}</td>
                            <td class="amount">{{ isset($earning['rate']) ? number_format($earning['rate'], 2, ',', '.') : '-' }}</td>
                            <td class="amount">{{ isset($earning['percentage']) ? number_format($earning['percentage'], 1, ',', '.') : '-' }}</td>
                            <td class="amount">{{ number_format($earning['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="5"><strong>TOTAL RENDIMENTOS</strong></td>
                            <td class="amount"><strong>{{ number_format($totals['earnings'], 2, ',', '.') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Deduções -->
            @if(count($deductions) > 0)
            <div class="deductions-section">
                <div class="section-title">DEDUÇÕES</div>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Descrição</th>
                            <th style="width: 15%;">Tipo</th>
                            <th style="width: 12%;">Taxa %</th>
                            <th style="width: 15%;">Base Cálc.</th>
                            <th style="width: 10%;">Ref.</th>
                            <th style="width: 23%;">Valor (AOA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deductions as $deduction)
                        <tr>
                            <td>{{ $deduction['name'] }}</td>
                            <td>{{ $deduction['type'] === 'tax' ? 'Imposto' : ($deduction['type'] === 'advance' ? 'Adiantam.' : ($deduction['type'] === 'loan' ? 'Empréstimo' : 'Dedução')) }}</td>
                            <td class="amount">{{ isset($deduction['rate']) ? number_format($deduction['rate'], 2, ',', '.') : '-' }}</td>
                            <td class="amount">{{ isset($deduction['base']) ? number_format($deduction['base'], 0, ',', '.') : '-' }}</td>
                            <td class="amount">{{ isset($deduction['reference']) ? $deduction['reference'] : '-' }}</td>
                            <td class="amount">{{ number_format($deduction['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="5"><strong>TOTAL DEDUÇÕES</strong></td>
                            <td class="amount"><strong>{{ number_format($totals['deductions'], 2, ',', '.') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
            
            <!-- Resumo Final -->
            <div class="summary-section">
                <table class="summary-table">
                    <tr>
                        <td class="summary-label" style="width: 25%;">Salário Base:</td>
                        <td class="summary-value" style="width: 25%;">{{ number_format($totals['base_salary'] ?? 0, 2, ',', '.') }} AOA</td>
                        <td class="summary-label" style="width: 25%;">Horas Extras:</td>
                        <td class="summary-value" style="width: 25%;">{{ number_format($totals['overtime'] ?? 0, 2, ',', '.') }} AOA</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Subsídios:</td>
                        <td class="summary-value">{{ number_format($totals['allowances'] ?? 0, 2, ',', '.') }} AOA</td>
                        <td class="summary-label">Adiantamentos:</td>
                        <td class="summary-value">{{ number_format($totals['advances'] ?? 0, 2, ',', '.') }} AOA</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Total Bruto:</td>
                        <td class="summary-value">{{ number_format($totals['earnings'], 2, ',', '.') }} AOA</td>
                        <td class="summary-label">Total Deduções:</td>
                        <td class="summary-value">{{ number_format($totals['deductions'], 2, ',', '.') }} AOA</td>
                    </tr>
                    <tr class="net-salary-row">
                        <td class="summary-label" colspan="2">SALÁRIO LÍQUIDO:</td>
                        <td class="summary-value" colspan="2">{{ number_format($totals['net_salary'], 2, ',', '.') }} AOA</td>
                    </tr>
                </table>
            </div>
            
            <!-- Assinaturas -->
            <div class="signatures">
                <table>
                    <tr>
                        <td>Assinatura do Empregador</td>
                        <td>Assinatura do Funcionário</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
