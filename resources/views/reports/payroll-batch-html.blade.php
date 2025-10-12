<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Folha de Pagamento - {{ $batchName }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }
        
        .header h1 {
            font-size: 22px;
            margin-bottom: 8px;
            color: #000;
            font-weight: bold;
        }
        
        .header p {
            font-size: 12px;
            color: #666;
            margin: 3px 0;
        }
        
        .batch-info {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid #4CAF50;
        }
        
        .batch-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .batch-info td {
            padding: 5px 8px;
            font-size: 11px;
        }
        
        .batch-info strong {
            color: #000;
            font-weight: 600;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .summary-table th,
        .summary-table td {
            padding: 10px 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .summary-table th {
            background-color: #D4AF37;
            color: #000;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        .summary-table td.code {
            width: 100px;
            text-align: center;
            font-weight: bold;
            color: #555;
            font-size: 10px;
        }
        
        .summary-table td.amount {
            text-align: right;
            font-weight: bold;
            font-size: 12px;
        }
        
        .grand-total {
            background-color: #FFA500 !important;
            font-weight: bold;
            font-size: 13px;
        }
        
        .grand-total td {
            color: #000;
        }
        
        .section-header {
            background-color: #f0f0f0;
            font-weight: 600;
            color: #000;
        }
        
        .bonus-row {
            background-color: #FFFF99 !important;
        }
        
        .net-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        
        .net-row td {
            color: #dc2626 !important;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #FFE5B4;
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #000 !important;
        }
        
        .total-row td {
            color: #dc2626 !important;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #45a049;
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        <i class="fas fa-print"></i> Imprimir Relatório
    </button>
    
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>RELATÓRIO DE FOLHA DE PAGAMENTO</h1>
            <p><strong>{{ $batchName }}</strong></p>
            <p>Período: {{ $periodName }} | {{ $batchDate }}</p>
        </div>
        
        {{-- Batch Information --}}
        <div class="batch-info">
            <table>
                <tr>
                    <td width="50%"><strong>Departamento:</strong> {{ $departmentName }}</td>
                    <td width="50%"><strong>Total Funcionários:</strong> {{ $totalEmployees }}</td>
                </tr>
                <tr>
                    <td><strong>Criado por:</strong> {{ $creatorName }}</td>
                    <td><strong>Data de Geração:</strong> {{ $generatedAt }}</td>
                </tr>
            </table>
        </div>
        
        {{-- Summary Table --}}
        <table class="summary-table">
            <thead>
                <tr>
                    <th colspan="2">DESCRIÇÃO</th>
                    <th style="width: 100px; text-align: center;">CÓDIGO</th>
                    <th style="width: 150px; text-align: right;">VALOR (AOA)</th>
                </tr>
            </thead>
            <tbody>
                {{-- Grand Total --}}
                <tr class="grand-total">
                    <td colspan="3"><strong>GRAND TOTAL</strong></td>
                    <td class="amount">{{ number_format($totals['grand_total'], 2, ',', '.') }}</td>
                </tr>
                
                <tr><td colspan="4" style="height: 8px; border: none; background: white;"></td></tr>
                
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
                    <td colspan="2" class="section-header">BONUS</td>
                    <td class="code">BNS</td>
                    <td class="amount">{{ number_format($totals['earnings']['bonus'], 2, ',', '.') }}</td>
                </tr>
                
                <tr class="net-row">
                    <td colspan="3"><strong>NET</strong></td>
                    <td class="amount">{{ number_format($totals['net_before_deductions'], 2, ',', '.') }}</td>
                </tr>
                
                <tr><td colspan="4" style="height: 12px; border: none; background: white;"></td></tr>
                
                {{-- Deductions Breakdown --}}
                <tr>
                    <td colspan="2" class="section-header">SOCIAL SECURITY 3%</td>
                    <td class="code">INSS 3%</td>
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
                
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;"><strong>TOTAL DEDUCTIONS:</strong></td>
                    <td class="amount">{{ number_format($totals['total_deductions'], 2, ',', '.') }}</td>
                </tr>
                
                <tr><td colspan="4" style="height: 8px; border: none; background: white;"></td></tr>
                
                {{-- Final Total --}}
                <tr class="grand-total" style="background-color: #FFE5B4 !important;">
                    <td colspan="3"><strong>TOTAL</strong></td>
                    <td class="amount">{{ number_format($totals['net_total'], 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        
        {{-- Footer --}}
        <div class="footer">
            <p><strong>Relatório gerado automaticamente pelo sistema ERP DEMBENA em {{ $generatedAt }}</strong></p>
            <p>Este documento é confidencial e destinado exclusivamente ao departamento de Recursos Humanos</p>
        </div>
    </div>
    
    <script>
        // Atalho para imprimir (Ctrl+P)
        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.metaKey) && event.key === 'p') {
                event.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
