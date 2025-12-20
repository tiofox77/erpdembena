<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Desconto Salarial - {{ $discount->employee->full_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 15px 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e53e3e;
        }
        .logo {
            max-height: 50px;
            max-width: 180px;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #e53e3e;
            margin-bottom: 3px;
        }
        .document-title {
            font-size: 13px;
            font-weight: bold;
            margin: 8px 0 8px 0;
            color: #2d3748;
        }
        .section {
            margin-bottom: 10px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #e53e3e;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .info-table th {
            text-align: left;
            padding: 4px 6px;
            width: 35%;
            background-color: #f7fafc;
            font-weight: bold;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        .info-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 8px;
            font-size: 8px;
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
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 25px;
            padding-top: 3px;
            font-size: 9px;
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
            <div style="font-size: 8px; color: #718096;">{{ $companyInfo['address'] }}</div>
        @endif
        @if(!empty($companyInfo['phone']) || !empty($companyInfo['email']))
            <div style="font-size: 8px; color: #718096;">
                @if(!empty($companyInfo['phone']))Tel: {{ $companyInfo['phone'] }}@endif
                @if(!empty($companyInfo['phone']) && !empty($companyInfo['email'])) | @endif
                @if(!empty($companyInfo['email']))Email: {{ $companyInfo['email'] }}@endif
            </div>
        @endif
    </div>

    <div class="document-title">FORMULÁRIO DE DESCONTO SALARIAL</div>

    <!-- Informações do Funcionário -->
    <div class="section">
        <div class="section-title">Dados do Funcionário</div>
        <table class="info-table">
            <tr>
                <th>Nome Completo:</th>
                <td>{{ $discount->employee->full_name }}</td>
            </tr>
            <tr>
                <th>Nº Funcionário:</th>
                <td>{{ $discount->employee->employee_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Departamento:</th>
                <td>{{ $discount->employee->department->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Informações do Desconto -->
    <div class="section">
        <div class="section-title">Detalhes do Desconto</div>
        <table class="info-table">
            <tr>
                <th>Data da Solicitação:</th>
                <td>{{ $discount->request_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Valor Total:</th>
                <td>{{ number_format($discount->amount, 2, ',', '.') }} Kz</td>
            </tr>
            <tr>
                <th>Tipo de Desconto:</th>
                <td>
                    @if($discount->discount_type === 'union')
                        Sindical
                    @elseif($discount->discount_type === 'quixiquila')
                        Quixiquila
                    @else
                        Outros
                    @endif
                </td>
            </tr>
            <tr>
                <th>Nº de Parcelas:</th>
                <td>{{ $discount->installments }}</td>
            </tr>
            <tr>
                <th>Valor por Parcela:</th>
                <td>{{ number_format($discount->installment_amount, 2, ',', '.') }} Kz</td>
            </tr>
            <tr>
                <th>Data 1ª Dedução:</th>
                <td>{{ $discount->first_deduction_date->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <th>Parcelas Restantes:</th>
                <td>{{ $discount->remaining_installments }}</td>
            </tr>
            <tr>
                <th>Motivo:</th>
                <td>{{ $discount->reason ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Status:</th>
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
            @if($discount->notes)
            <tr>
                <th>Observações:</th>
                <td>{{ $discount->notes }}</td>
            </tr>
            @endif
        </table>
    </div>

    <!-- Informações de Aprovação -->
    @if($discount->status !== 'pending' && $discount->approver)
    <div class="section">
        <div class="section-title">Aprovação</div>
        <table class="info-table">
            <tr>
                <th>Aprovado por:</th>
                <td>{{ $discount->approver->name }}</td>
            </tr>
            <tr>
                <th>Data de Aprovação:</th>
                <td>{{ $discount->approved_at ? $discount->approved_at->format('d/m/Y H:i') : 'N/A' }}</td>
            </tr>
        </table>
    </div>
    @endif

    <!-- Assinaturas -->
    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    Funcionário<br>
                    {{ $discount->employee->full_name }}
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    Recursos Humanos<br>
                    Data: ___/___/______
                </div>
            </div>
        </div>
    </div>

    <!-- Rodapé -->
    <div style="text-align: center; margin-top: 10px; font-size: 8px; color: #718096;">
        Documento gerado em {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
