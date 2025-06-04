<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contracheque - {{ $payroll->employee->full_name }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 150px;
            max-height: 70px;
            margin-bottom: 10px;
        }
        h1 {
            color: #2563eb;
            font-size: 24px;
            margin: 0;
            margin-bottom: 5px;
        }
        h2 {
            color: #4b5563;
            font-size: 18px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .date {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 12px;
            font-weight: bold;
            color: #374151;
            border-bottom: 2px solid #ddd;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .employee-info {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
            padding: 15px;
            border-radius: 5px;
            background-color: #f9fafb;
        }
        .info-group {
            width: 50%;
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            display: block;
            font-size: 12px;
            color: #6b7280;
        }
        .info-value {
            font-size: 14px;
        }
        .amount {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f3f4f6;
        }
        .net-salary {
            font-size: 16px;
            font-weight: bold;
            color: #2563eb;
            text-align: right;
            margin-top: 20px;
            padding: 10px;
            background-color: #f3f4f6;
            border-radius: 5px;
        }
        .signature-area {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-line {
            width: 45%;
            text-align: center;
        }
        .signature-box {
            border-top: 1px solid #000;
            margin-top: 30px;
            padding-top: 5px;
            font-size: 12px;
        }
        .footer {
            margin-top: 50px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoFullPath = $companyLogo ? public_path('storage/' . $companyLogo) : public_path('img/logo.png');
            $hasLogo = file_exists($logoFullPath);
            $logoPath = $hasLogo ? $logoFullPath : null;
        @endphp
        
        @if($hasLogo && $logoPath)
            <img src="{{ $logoPath }}" alt="{{ $companyName }} Logo" class="logo">
        @endif
        <h1>{{ $companyName }}</h1>
        <div class="date">
            {{ $companyAddress }}
            @if($companyPhone) | Tel: {{ $companyPhone }} @endif
            @if($companyEmail) | Email: {{ $companyEmail }} @endif
        </div>
        <h2>CONTRACHEQUE</h2>
        <p class="date">Gerado em: {{ $date }}</p>
    </div>
    
    <h2>Informações do Funcionário</h2>
    <div class="employee-info">
        <div class="info-group">
            <span class="info-label">Nome</span>
            <span class="info-value">{{ $payroll->employee->full_name }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">ID</span>
            <span class="info-value">{{ $payroll->employee->employee_id }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Departamento</span>
            <span class="info-value">{{ $payroll->employee->department->name ?? 'N/A' }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Cargo</span>
            <span class="info-value">{{ $payroll->employee->position->name ?? 'N/A' }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Período</span>
            <span class="info-value">
                @if($payroll->payrollPeriod && $payroll->payrollPeriod->start_date && $payroll->payrollPeriod->end_date)
                    {{ $payroll->payrollPeriod->name }} ({{ $payroll->payrollPeriod->start_date->format('d/m/Y') }} - {{ $payroll->payrollPeriod->end_date->format('d/m/Y') }})
                @else
                    N/A
                @endif
            </span>
        </div>
        <div class="info-group">
            <span class="info-label">Status</span>
            <span class="info-value">{{ ucfirst($payroll->status) }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Data de Pagamento</span>
            <span class="info-value">{{ $payroll->payment_date ? date('d/m/Y', strtotime($payroll->payment_date)) : 'N/A' }}</span>
        </div>
        <div class="info-group">
            <span class="info-label">Método de Pagamento</span>
            <span class="info-value">{{ ucfirst(str_replace('_', ' ', $payroll->payment_method)) }}</span>
        </div>
    </div>
    
    <h2>Rendimentos e Deduções</h2>
    <table>
        <tr>
            <th style="width: 35%;">Rendimentos</th>
            <th style="width: 15%; text-align: right;">Valor</th>
            <th style="width: 35%;">Deduções</th>
            <th style="width: 15%; text-align: right;">Valor</th>
        </tr>
        <tr>
            <td>Salário Base</td>
            <td class="amount">{{ number_format($payroll->basic_salary, 2, ',', '.') }}</td>
            <td>Imposto de Renda (IRT)</td>
            <td class="amount">{{ number_format($payroll->tax, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Subsídios</td>
            <td class="amount">{{ number_format($payroll->allowances, 2, ',', '.') }}</td>
            <td>Segurança Social (INSS)</td>
            <td class="amount">{{ number_format($payroll->social_security, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Horas Extras</td>
            <td class="amount">{{ number_format($payroll->overtime, 2, ',', '.') }}</td>
            <td>Outras Deduções</td>
            <td class="amount">{{ number_format($payroll->deductions, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bônus</td>
            <td class="amount">{{ number_format($payroll->bonuses, 2, ',', '.') }}</td>
            <td></td>
            <td class="amount"></td>
        </tr>
        <tr class="total-row">
            <td>Total Rendimentos</td>
            <td class="amount">{{ number_format($payroll->basic_salary + $payroll->allowances + $payroll->overtime + $payroll->bonuses, 2, ',', '.') }}</td>
            <td>Total Deduções</td>
            <td class="amount">{{ number_format($payroll->tax + $payroll->social_security + $payroll->deductions, 2, ',', '.') }}</td>
        </tr>
    </table>
    
    <div class="net-salary">
        Salário Líquido: {{ number_format($payroll->net_salary, 2, ',', '.') }}
    </div>

    <div style="margin-top: 20px; padding: 10px; background-color: #f5f5f5; border-radius: 5px; font-size: 11px;">
        <p><strong>Informações sobre os cálculos:</strong></p>
        <ul style="margin-left: 15px; margin-top: 5px;">
            <li>INSS: 3% sobre a soma do Salário Base e Subsídios ({{ number_format(($payroll->basic_salary + $payroll->allowances) * 0.03, 2, ',', '.') }})</li>
            <li>IRT: Calculado com base na tabela atualizada de Angola após dedução do INSS</li>
            <li>Base de cálculo do IRT: {{ number_format(($payroll->basic_salary + $payroll->allowances) - $payroll->social_security, 2, ',', '.') }}</li>
        </ul>
    </div>
    
    @if($payroll->remarks)
    <h2>Observações</h2>
    <p>{{ $payroll->remarks }}</p>
    @endif
    
    <div class="signature-area">
        <div class="signature-line">
            <div class="signature-box">
                Assinatura do Empregador
            </div>
        </div>
        <div class="signature-line">
            <div class="signature-box">
                Assinatura do Empregado
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>Este documento é um registro oficial de pagamento. Por favor, guarde-o para suas declarações de imposto de renda.</p>
        <p>{{ $companyName }} &copy; {{ date('Y') }} - Todos os direitos reservados</p>
        <p>Gerado em: {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
