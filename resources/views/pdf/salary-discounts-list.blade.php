<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Descontos Salariais</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e53e3e;
        }
        .logo {
            max-height: 60px;
            max-width: 180px;
            margin-bottom: 8px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #e53e3e;
            margin-bottom: 4px;
        }
        .document-title {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
            color: #2d3748;
        }
        .filters-info {
            background-color: #f7fafc;
            padding: 8px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 10px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #e53e3e;
            color: white;
            padding: 8px 4px;
            text-align: left;
            font-size: 10px;
            border: 1px solid #c53030;
        }
        .data-table td {
            padding: 6px 4px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f7fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-completed {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #f7fafc;
            border-left: 3px solid #e53e3e;
        }
        .summary-item {
            display: inline-block;
            margin-right: 20px;
            font-size: 10px;
        }
        .summary-label {
            font-weight: bold;
            color: #2d3748;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 9px;
            color: #718096;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho -->
    <div class="header">
        @if(!empty($companyInfo['logo']))
            <img src="{{ public_path($companyInfo['logo']) }}" alt="Logo" class="logo">
        @endif
        <div class="company-name">{{ $companyInfo['name'] ?? 'Empresa' }}</div>
        @if(!empty($companyInfo['address']))
            <div style="font-size: 9px; color: #718096;">{{ $companyInfo['address'] }}</div>
        @endif
    </div>

    <div class="document-title">RELATÓRIO DE DESCONTOS SALARIAIS</div>

    <!-- Filtros Aplicados -->
    @if($filters['status'] || $filters['type'] || $filters['dateFrom'] || $filters['dateTo'])
    <div class="filters-info">
        <strong>Filtros Aplicados:</strong>
        @if($filters['status'])
            Status: 
            @if($filters['status'] === 'pending') Pendente
            @elseif($filters['status'] === 'approved') Aprovado
            @elseif($filters['status'] === 'rejected') Rejeitado
            @else Concluído
            @endif
            &nbsp;&nbsp;
        @endif
        @if($filters['type'])
            Tipo: 
            @if($filters['type'] === 'union') Sindical
            @elseif($filters['type'] === 'quixiquila') Quixiquila
            @else Outros
            @endif
            &nbsp;&nbsp;
        @endif
        @if($filters['dateFrom'])
            De: {{ \Carbon\Carbon::parse($filters['dateFrom'])->format('d/m/Y') }}&nbsp;&nbsp;
        @endif
        @if($filters['dateTo'])
            Até: {{ \Carbon\Carbon::parse($filters['dateTo'])->format('d/m/Y') }}
        @endif
    </div>
    @endif

    <!-- Tabela de Dados -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20%;">Funcionário</th>
                <th style="width: 8%;">Nº Func.</th>
                <th style="width: 10%;">Data</th>
                <th style="width: 12%;">Valor</th>
                <th style="width: 12%;">Tipo</th>
                <th style="width: 8%;">Parcelas</th>
                <th style="width: 12%;">Vlr. Parcela</th>
                <th style="width: 10%;">Restantes</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalAmount = 0;
                $totalRemaining = 0;
            @endphp
            @foreach($discounts as $discount)
                @php
                    $totalAmount += $discount->amount;
                    $totalRemaining += ($discount->installment_amount * $discount->remaining_installments);
                @endphp
                <tr>
                    <td>{{ $discount->employee->full_name }}</td>
                    <td>{{ $discount->employee->employee_id ?? 'N/A' }}</td>
                    <td>{{ $discount->request_date->format('d/m/Y') }}</td>
                    <td>{{ number_format($discount->amount, 2, ',', '.') }} Kz</td>
                    <td>
                        @if($discount->discount_type === 'union')
                            Sindical
                        @elseif($discount->discount_type === 'quixiquila')
                            Quixiquila
                        @else
                            Outros
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $discount->installments }}</td>
                    <td>{{ number_format($discount->installment_amount, 2, ',', '.') }} Kz</td>
                    <td style="text-align: center;">{{ $discount->remaining_installments }}</td>
                    <td>
                        @if($discount->status === 'pending')
                            <span class="status-badge status-pending">Pendente</span>
                        @elseif($discount->status === 'approved')
                            <span class="status-badge status-approved">Aprovado</span>
                        @elseif($discount->status === 'rejected')
                            <span class="status-badge status-rejected">Rejeitado</span>
                        @else
                            <span class="status-badge status-completed">Concluído</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Resumo -->
    <div class="summary">
        <div class="summary-item">
            <span class="summary-label">Total de Registros:</span> {{ $discounts->count() }}
        </div>
        <div class="summary-item">
            <span class="summary-label">Valor Total:</span> {{ number_format($totalAmount, 2, ',', '.') }} Kz
        </div>
        <div class="summary-item">
            <span class="summary-label">Valor Restante:</span> {{ number_format($totalRemaining, 2, ',', '.') }} Kz
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        Documento gerado em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
