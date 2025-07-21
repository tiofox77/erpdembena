<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Pagamento - {{ $employee['name'] }}</title>
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
            padding: 3mm;
        }
        
        .payslip {
            height: 144mm;
            border: 2px solid #000;
            margin-bottom: 2mm;
            padding: 1.5mm;
            position: relative;
            page-break-inside: avoid;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
            margin-bottom: 2mm;
        }
        
        .company-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 0.5mm;
        }
        
        .company-info {
            font-size: 6px;
            margin-bottom: 0.5mm;
        }
        
        .document-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .via-label {
            position: absolute;
            top: 1mm;
            right: 3mm;
            font-size: 7px;
            font-weight: bold;
            border: 1px solid #000;
            padding: 0.5mm 1mm;
        }
        
        .info-section {
            margin-bottom: 1mm;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1mm;
        }
        
        .info-table td {
            padding: 0.5mm;
            border: 1px solid #000;
            vertical-align: middle;
            font-size: 6px;
        }
        
        .info-label {
            font-weight: bold;
            background-color: #f0f0f0;
            width: 18%;
        }
        
        .info-value {
            width: 32%;
        }
        
        .section-title {
            font-size: 8px;
            font-weight: bold;
            background-color: #e0e0e0;
            padding: 0.5mm;
            border: 1px solid #000;
            text-align: center;
            margin-bottom: 0.5mm;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1mm;
        }
        
        .items-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 0.5mm;
            text-align: left;
            font-size: 6px;
            font-weight: bold;
        }
        
        .items-table td {
            border: 1px solid #000;
            padding: 0.5mm;
            font-size: 6px;
            vertical-align: middle;
        }
        
        .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .total-row {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .summary-section {
            border: 2px solid #000;
            padding: 1mm;
            margin-top: 1mm;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 0.5mm;
            border-bottom: 1px solid #000;
            font-size: 7px;
        }
        
        .summary-label {
            font-weight: bold;
            width: 70%;
        }
        
        .summary-value {
            text-align: right;
            font-family: 'Courier New', monospace;
            width: 30%;
        }
        
        .net-salary-row {
            background-color: #e0e0e0;
            font-weight: bold;
            font-size: 8px;
        }
        
        .signatures {
            position: absolute;
            bottom: 2mm;
            left: 1.5mm;
            right: 1.5mm;
            width: calc(100% - 3mm);
        }
        
        .signatures table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .signatures td {
            width: 50%;
            text-align: center;
            font-size: 6px;
            border-top: 1px solid #000;
            padding-top: 1mm;
        }
        
        .cut-line {
            text-align: center;
            font-size: 7px;
            margin: 0.5mm 0;
            border-top: 1px dashed #000;
            padding-top: 0.5mm;
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
                            <th style="width: 45%;">Descrição</th>
                            <th style="width: 30%;">Tipo</th>
                            <th style="width: 25%;">Valor (AOA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($earnings as $earning)
                        <tr>
                            <td>{{ $earning['name'] }}</td>
                            <td>{{ $earning['type'] === 'earning' ? 'Salário' : ($earning['type'] === 'allowance' ? 'Subsídio' : 'Bónus') }}</td>
                            <td class="amount">{{ number_format($earning['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2"><strong>TOTAL RENDIMENTOS</strong></td>
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
                            <th style="width: 45%;">Descrição</th>
                            <th style="width: 30%;">Tipo</th>
                            <th style="width: 25%;">Valor (AOA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deductions as $deduction)
                        <tr>
                            <td>{{ $deduction['name'] }}</td>
                            <td>{{ $deduction['type'] === 'tax' ? 'Imposto' : 'Dedução' }}</td>
                            <td class="amount">{{ number_format($deduction['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2"><strong>TOTAL DEDUÇÕES</strong></td>
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
                        <td class="summary-label">Total Bruto:</td>
                        <td class="summary-value">{{ number_format($totals['earnings'], 2, ',', '.') }} AOA</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Total Deduções:</td>
                        <td class="summary-value">{{ number_format($totals['deductions'], 2, ',', '.') }} AOA</td>
                    </tr>
                    <tr class="net-salary-row">
                        <td class="summary-label">SALÁRIO LÍQUIDO:</td>
                        <td class="summary-value">{{ number_format($totals['net_salary'], 2, ',', '.') }} AOA</td>
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
                            <th style="width: 45%;">Descrição</th>
                            <th style="width: 30%;">Tipo</th>
                            <th style="width: 25%;">Valor (AOA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($earnings as $earning)
                        <tr>
                            <td>{{ $earning['name'] }}</td>
                            <td>{{ $earning['type'] === 'earning' ? 'Salário' : ($earning['type'] === 'allowance' ? 'Subsídio' : 'Bónus') }}</td>
                            <td class="amount">{{ number_format($earning['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2"><strong>TOTAL RENDIMENTOS</strong></td>
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
                            <th style="width: 45%;">Descrição</th>
                            <th style="width: 30%;">Tipo</th>
                            <th style="width: 25%;">Valor (AOA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deductions as $deduction)
                        <tr>
                            <td>{{ $deduction['name'] }}</td>
                            <td>{{ $deduction['type'] === 'tax' ? 'Imposto' : 'Dedução' }}</td>
                            <td class="amount">{{ number_format($deduction['amount'], 2, ',', '.') }}</td>
                        </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="2"><strong>TOTAL DEDUÇÕES</strong></td>
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
                        <td class="summary-label">Total Bruto:</td>
                        <td class="summary-value">{{ number_format($totals['earnings'], 2, ',', '.') }} AOA</td>
                    </tr>
                    <tr>
                        <td class="summary-label">Total Deduções:</td>
                        <td class="summary-value">{{ number_format($totals['deductions'], 2, ',', '.') }} AOA</td>
                    </tr>
                    <tr class="net-salary-row">
                        <td class="summary-label">SALÁRIO LÍQUIDO:</td>
                        <td class="summary-value">{{ number_format($totals['net_salary'], 2, ',', '.') }} AOA</td>
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
