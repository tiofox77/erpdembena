<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Folha de Pagamento - {{ $batch->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #000;
        }
        
        .header p {
            font-size: 10px;
            color: #666;
        }
        
        .batch-info {
            margin-bottom: 15px;
            background: #f9f9f9;
            padding: 10px;
            border-left: 4px solid #4CAF50;
        }
        
        .batch-info table {
            width: 100%;
        }
        
        .batch-info td {
            padding: 3px 5px;
        }
        
        .batch-info strong {
            color: #000;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .summary-table th,
        .summary-table td {
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .summary-table th {
            background-color: #D4AF37;
            color: #000;
            font-weight: bold;
            text-align: left;
        }
        
        .summary-table td.code {
            width: 80px;
            text-align: center;
            font-weight: bold;
            color: #555;
        }
        
        .summary-table td.amount {
            text-align: right;
            font-weight: bold;
        }
        
        .grand-total {
            background-color: #FFA500 !important;
            font-weight: bold;
            font-size: 12px;
        }
        
        .section-header {
            background-color: #f0f0f0;
            font-weight: bold;
            color: #000;
        }
        
        .bonus-row {
            background-color: #FFFF00 !important;
        }
        
        .net-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #FFE5B4;
            font-weight: bold;
            font-size: 11px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>RELATÓRIO DE FOLHA DE PAGAMENTO</h1>
        <p>{{ $batch->name }}</p>
        <p>Período: {{ $batch->payrollPeriod->name ?? 'N/A' }} | {{ $batch->formatted_batch_date }}</p>
    </div>
    
    {{-- Batch Information --}}
    <div class="batch-info">
        <table>
            <tr>
                <td width="50%"><strong>Departamento:</strong> {{ $batch->department->name ?? 'Todos' }}</td>
                <td width="50%"><strong>Total Funcionários:</strong> {{ $totals['employee_count'] }}</td>
            </tr>
            <tr>
                <td><strong>Criado por:</strong> {{ $batch->creator->name ?? 'N/A' }}</td>
                <td><strong>Data de Geração:</strong> {{ $generatedAt }}</td>
            </tr>
        </table>
    </div>
    
    {{-- Summary Table --}}
    <table class="summary-table">
        <thead>
            <tr>
                <th colspan="2">DESCRIÇÃO</th>
                <th style="width: 80px; text-align: center;">CÓDIGO</th>
                <th style="width: 120px; text-align: right;">VALOR (AOA)</th>
            </tr>
        </thead>
        <tbody>
            {{-- Grand Total --}}
            <tr class="grand-total">
                <td colspan="3"><strong>GRAND TOTAL</strong></td>
                <td class="amount">{{ number_format($totals['grand_total'], 2, ',', '.') }}</td>
            </tr>
            
            <tr><td colspan="4" style="height: 5px; border: none;"></td></tr>
            
            {{-- Earnings Breakdown --}}
            <tr>
                <td colspan="2" class="section-header">BASIC SALARY</td>
                <td class="code">BS</td>
                <td class="amount">{{ number_format($totals['earnings']['basic_salary'], 2, ',', '.') }}</td>
            </tr>
            
            <tr>
                <td colspan="2" class="section-header">TRANSPORT</td>
                <td class="code">TRNPT</td>
                <td class="amount">{{ number_format($totals['earnings']['transport'], 2, ',', '.') }}</td>
            </tr>
            
            <tr>
                <td colspan="2" class="section-header">OVER TIME</td>
                <td class="code">OT</td>
                <td class="amount">{{ number_format($totals['earnings']['overtime'], 2, ',', '.') }}</td>
            </tr>
            
            <tr>
                <td colspan="2" class="section-header">VACATION PAY</td>
                <td class="code">VP</td>
                <td class="amount">{{ number_format($totals['earnings']['vacation_pay'], 2, ',', '.') }}</td>
            </tr>
            
            <tr>
                <td colspan="2" class="section-header">FOOD ALLOW</td>
                <td class="code">FA</td>
                <td class="amount">{{ number_format($totals['earnings']['food_allow'], 2, ',', '.') }}</td>
            </tr>
            
            <tr>
                <td colspan="2" class="section-header">CHRISTMAS OFFER</td>
                <td class="code">CO</td>
                <td class="amount">{{ number_format($totals['earnings']['christmas_offer'], 2, ',', '.') }}</td>
            </tr>
            
            <tr class="bonus-row">
                <td colspan="2" class="section-header">BUNUS</td>
                <td class="code">BNS</td>
                <td class="amount">{{ number_format($totals['earnings']['bonus'], 2, ',', '.') }}</td>
            </tr>
            
            <tr class="net-row">
                <td colspan="3"><strong style="color: red;">NET</strong></td>
                <td class="amount" style="color: red;">{{ number_format($totals['net_before_deductions'], 2, ',', '.') }}</td>
            </tr>
            
            <tr><td colspan="4" style="height: 10px; border: none;"></td></tr>
            
            {{-- Deductions Breakdown --}}
            <tr>
                <td colspan="2" class="section-header">SOCIAL SECURITY 3%</td>
                <td class="code">ins 3%</td>
                <td class="amount">{{ number_format($totals['deductions']['inss_3_percent'], 2, ',', '.') }}</td>
            </tr>
            
            <tr>
                <td colspan="2" class="section-header">IRT</td>
                <td class="code">IRT</td>
                <td class="amount">{{ number_format($totals['deductions']['irt'], 2, ',', '.') }}</td>
            </tr>
            
            <tr>
                <td colspan="2" class="section-header">STAFF ADVANCE</td>
                <td class="code">Staff Adv</td>
                <td class="amount">{{ number_format($totals['deductions']['staff_advance'], 2, ',', '.') }}</td>
            </tr>
            
            <tr>
                <td colspan="2" class="section-header">ABSENT</td>
                <td class="code">Absent</td>
                <td class="amount">{{ number_format($totals['deductions']['absent'], 2, ',', '.') }}</td>
            </tr>
            
            @if($totals['deductions']['union_fund'] > 0)
            <tr>
                <td colspan="2" class="section-header">UNION FUND DEDUCTION</td>
                <td class="code">ded</td>
                <td class="amount">{{ number_format($totals['deductions']['union_fund'], 2, ',', '.') }}</td>
            </tr>
            @endif
            
            @if($totals['deductions']['union_deduction'] > 0)
            <tr>
                <td colspan="2" class="section-header">Union Deduction</td>
                <td class="code">union</td>
                <td class="amount">{{ number_format($totals['deductions']['union_deduction'], 2, ',', '.') }}</td>
            </tr>
            @endif
            
            <tr>
                <td colspan="2" class="section-header">OTHER DEDUCTION</td>
                <td class="code">DED</td>
                <td class="amount">{{ number_format($totals['deductions']['other_deduction'], 2, ',', '.') }}</td>
            </tr>
            
            @if($totals['deductions']['food_allow_deduction'] > 0)
            <tr>
                <td colspan="2" class="section-header">FOOD ALLOW</td>
                <td class="code">FA</td>
                <td class="amount">{{ number_format($totals['deductions']['food_allow_deduction'], 2, ',', '.') }}</td>
            </tr>
            @endif
            
            <tr style="border-top: 2px solid #000;">
                <td colspan="3" class="total-row" style="text-align: right; color: red;"><strong>TOTAL DEDUCTIONS:</strong></td>
                <td class="amount total-row" style="color: red;">{{ number_format($totals['total_deductions'], 2, ',', '.') }}</td>
            </tr>
            
            <tr><td colspan="4" style="height: 5px; border: none;"></td></tr>
            
            {{-- Final Total --}}
            <tr class="grand-total" style="background-color: #FFE5B4 !important;">
                <td colspan="3"><strong>TOTAL</strong></td>
                <td class="amount">{{ number_format($totals['net_total'], 2, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    
    {{-- Footer --}}
    <div class="footer">
        <p>Relatório gerado automaticamente pelo sistema ERP DEMBENA em {{ $generatedAt }}</p>
        <p>Este documento é confidencial e destinado exclusivamente ao departamento de Recursos Humanos</p>
    </div>
</body>
</html>
