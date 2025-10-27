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
            font-size: 9px;
            line-height: 1.2;
            color: #000;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        
        .page {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 8mm;
        }
        
        .payslip {
            height: 135mm;
            border: 2px solid #000;
            margin-bottom: 8mm;
            padding: 6mm;
            position: relative;
            page-break-inside: avoid;
        }
        
        /* Header do Recibo */
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }
        
        .company-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .company-info {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        .document-title {
            font-size: 20px;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
        }
        
        /* Cards de Informação */
        .info-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            flex: 1;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
        }
        
        .card-title {
            font-size: 16px;
            font-weight: bold;
            color: #4a5568;
            margin-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 8px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: 600;
            color: #718096;
        }
        
        .info-value {
            font-weight: bold;
            color: #2d3748;
        }
        
        /* Tabelas Modernas */
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2d3748;
            margin: 30px 0 15px 0;
            padding: 12px 0;
            border-bottom: 3px solid #667eea;
        }
        
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            font-size: 13px;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        tr:hover {
            background-color: #edf2f7;
        }
        
        .amount {
            text-align: right;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-earning { background: #c6f6d5; color: #22543d; }
        .badge-allowance { background: #bee3f8; color: #2a4365; }
        .badge-bonus { background: #fbb6ce; color: #702459; }
        .badge-tax { background: #fed7d7; color: #742a2a; }
        .badge-deduction { background: #feebc8; color: #7b341e; }
        .badge-taxable { background: #fed7d7; color: #742a2a; }
        .badge-exempt { background: #e6fffa; color: #234e52; }
        
        /* Totais */
        .totals-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 30px 0;
        }
        
        .totals-grid {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .total-item {
            text-align: center;
            flex: 1;
        }
        
        .total-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .total-value {
            font-size: 20px;
            font-weight: bold;
        }
        
        .net-salary {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 8px;
        }
        
        .net-salary .total-value {
            font-size: 28px;
        }
        
        /* Observações */
        .observations {
            background: #fffbeb;
            border: 1px solid #f59e0b;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }
        
        .observations-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
        }
        
        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        
        .signatures {
            display: flex;
            justify-content: space-between;
            margin: 40px 0;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
            border-top: 2px solid #2d3748;
            padding-top: 10px;
            font-weight: bold;
        }
        
        /* Responsividade para PDF */
        @media print {
            .container { padding: 10px; }
            .header { margin-bottom: 20px; }
            .info-cards { gap: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Moderno -->
        <div class="header">
            <div class="company-name">{{ $company['name'] }}</div>
            <div class="company-info">
                {{ $company['address'] }}
                @if($company['phone']) | Tel: {{ $company['phone'] }} @endif
                @if($company['email']) | Email: {{ $company['email'] }} @endif
                @if($company['nif']) | NIF: {{ $company['nif'] }} @endif
            </div>
            <div class="document-title">CONTRACHEQUE</div>
        </div>
        
        <!-- Cards de Informação -->
        <div class="info-cards">
            <!-- Funcionário -->
            <div class="info-card">
                <div class="card-title">👤 Funcionário</div>
                <div class="info-item">
                    <span class="info-label">Nome:</span>
                    <span class="info-value">{{ $employee['name'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">ID:</span>
                    <span class="info-value">{{ $employee['id'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Departamento:</span>
                    <span class="info-value">{{ $employee['department'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Cargo:</span>
                    <span class="info-value">{{ $employee['position'] }}</span>
                </div>
            </div>
            
            <!-- Período -->
            <div class="info-card">
                <div class="card-title">📅 Período</div>
                <div class="info-item">
                    <span class="info-label">Período:</span>
                    <span class="info-value">{{ $period['name'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Início:</span>
                    <span class="info-value">{{ $period['start_date'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fim:</span>
                    <span class="info-value">{{ $period['end_date'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="info-value">{{ ucfirst($payroll->status) }}</span>
                </div>
            </div>
            
            <!-- Pagamento -->
            <div class="info-card">
                <div class="card-title">💳 Pagamento</div>
                <div class="info-item">
                    <span class="info-label">Método:</span>
                    <span class="info-value">{{ ucfirst(str_replace('_', ' ', $payroll->payment_method)) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Conta:</span>
                    <span class="info-value">{{ $employee['bank_account'] }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Horas:</span>
                    <span class="info-value">{{ number_format((float)$payroll->attendance_hours, 1) }}h</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Gerado:</span>
                    <span class="info-value">{{ $generated_at }}</span>
                </div>
            </div>
        </div>
        
        <!-- Rendimentos -->
        @if(count($earnings) > 0)
        <div class="section-title">💰 Rendimentos</div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40%;">Descrição</th>
                        <th style="width: 15%;">Tipo</th>
                        <th style="width: 15%;">Tributação</th>
                        <th style="width: 30%; text-align: right;">Valor (AOA)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($earnings as $earning)
                    <tr>
                        <td>
                            <strong>{{ $earning['name'] }}</strong>
                            @if(isset($earning['description']) && $earning['description'])
                                <br><small style="color: #718096;">{{ $earning['description'] }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $earning['type'] }}">
                                {{ $earning['type'] === 'earning' ? 'Ganho' : ($earning['type'] === 'allowance' ? 'Subsídio' : 'Bónus') }}
                            </span>
                        </td>
                        <td>
                            @if(isset($earning['is_taxable']))
                                <span class="badge {{ $earning['is_taxable'] ? 'badge-taxable' : 'badge-exempt' }}">
                                    {{ $earning['is_taxable'] ? 'Tributável' : 'Isento' }}
                                </span>
                            @else
                                <span class="badge badge-taxable">Tributável</span>
                            @endif
                        </td>
                        <td class="amount">{{ number_format($earning['amount'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr style="background: #edf2f7; font-weight: bold;">
                        <td colspan="3"><strong>TOTAL RENDIMENTOS</strong></td>
                        <td class="amount"><strong>{{ number_format($totals['earnings'], 2, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
        
        <!-- Deduções -->
        @if(count($deductions) > 0)
        <div class="section-title">📉 Deduções</div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50%;">Descrição</th>
                        <th style="width: 20%;">Tipo</th>
                        <th style="width: 30%; text-align: right;">Valor (AOA)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deductions as $deduction)
                    <tr>
                        <td>
                            <strong>{{ $deduction['name'] }}</strong>
                            @if(isset($deduction['description']) && $deduction['description'])
                                <br><small style="color: #718096;">{{ $deduction['description'] }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $deduction['type'] }}">
                                {{ $deduction['type'] === 'tax' ? 'Imposto' : 'Dedução' }}
                            </span>
                        </td>
                        <td class="amount">{{ number_format($deduction['amount'], 2, ',', '.') }}</td>
                    </tr>
                    @endforeach
                    <tr style="background: #edf2f7; font-weight: bold;">
                        <td colspan="2"><strong>TOTAL DEDUÇÕES</strong></td>
                        <td class="amount"><strong>{{ number_format($totals['deductions'], 2, ',', '.') }}</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
        
        <!-- Resumo Final -->
        <div class="totals-section">
            <div class="totals-grid">
                <div class="total-item">
                    <div class="total-label">Total Bruto</div>
                    <div class="total-value">{{ number_format($totals['earnings'], 2, ',', '.') }} AOA</div>
                </div>
                <div class="total-item">
                    <div class="total-label">Total Deduções</div>
                    <div class="total-value">{{ number_format($totals['deductions'], 2, ',', '.') }} AOA</div>
                </div>
                <div class="total-item net-salary">
                    <div class="total-label">SALÁRIO LÍQUIDO</div>
                    <div class="total-value">{{ number_format($totals['net_salary'], 2, ',', '.') }} AOA</div>
                </div>
            </div>
        </div>
        
        <!-- Observações -->
        @if($payroll->remarks)
        <div class="observations">
            <div class="observations-title">📝 Observações</div>
            <div>{{ $payroll->remarks }}</div>
        </div>
        @endif
        
        <!-- Assinaturas -->
        <div class="signatures">
            <div class="signature-box">
                Assinatura do Empregador
            </div>
            <div class="signature-box">
                Assinatura do Empregado
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $company['name'] }}</strong> © {{ date('Y') }} - Todos os direitos reservados</p>
            <p>Este documento é um registro oficial de pagamento. Guarde-o para suas declarações fiscais.</p>
            <p>Gerado por: {{ $generated_by }} em {{ $generated_at }}</p>
        </div>
    </div>
</body>
</html>
