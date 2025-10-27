<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Adiantamento Salarial</title>
    <style>
        @page {
            margin: 2cm 1.5cm;
            size: A4;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 16pt;
            font-weight: bold;
            color: #374151;
            margin-top: 10px;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .info-table td {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            vertical-align: top;
        }
        
        .info-table .label {
            background-color: #f3f4f6;
            font-weight: bold;
            width: 30%;
        }
        
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1f2937;
            margin: 25px 0 15px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .payment-schedule {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .payment-schedule th,
        .payment-schedule td {
            padding: 10px;
            border: 1px solid #d1d5db;
            text-align: center;
        }
        
        .payment-schedule th {
            background-color: #f8fafc;
            font-weight: bold;
            color: #374151;
        }
        
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        
        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .signature-table td {
            padding: 40px 15px 15px 15px;
            border-top: 1px solid #374151;
            text-align: center;
            vertical-align: bottom;
            width: 50%;
        }
        
        .signature-label {
            font-weight: bold;
            font-size: 10pt;
        }
        
        .terms-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
        }
        
        .terms-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #374151;
        }
        
        .terms-list {
            font-size: 10pt;
            line-height: 1.5;
            margin: 0;
            padding-left: 20px;
        }
        
        .footer {
            position: fixed;
            bottom: 1cm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9pt;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        
        .amount-highlight {
            background-color: #fef3c7;
            padding: 3px 6px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9pt;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company_name ?? 'ERPDEMBENA' }}</div>
        <div style="font-size: 10pt; color: #6b7280;">{{ $company_address ?? 'Luanda, Angola' }}</div>
        <div class="document-title">FORMULÁRIO DE ADIANTAMENTO SALARIAL</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Nº do Adiantamento:</td>
                <td><strong>#{{ str_pad($advance->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                <td class="label">Data da Solicitação:</td>
                <td>{{ Carbon\Carbon::parse($advance->request_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Status:</td>
                <td>
                    <span class="status-badge status-{{ $advance->status }}">
                        {{ ucfirst($advance->status) }}
                    </span>
                </td>
                <td class="label">Data de Aprovação:</td>
                <td>{{ $advance->approved_at ? Carbon\Carbon::parse($advance->approved_at)->format('d/m/Y H:i') : 'Pendente' }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">1. DADOS DO FUNCIONÁRIO</div>
    <table class="info-table">
        <tr>
            <td class="label">Nome Completo:</td>
            <td colspan="3"><strong>{{ $advance->employee->full_name }}</strong></td>
        </tr>
        <tr>
            <td class="label">Nº Funcionário:</td>
            <td>{{ $advance->employee->employee_number ?? 'N/A' }}</td>
            <td class="label">Departamento:</td>
            <td>{{ $advance->employee->department->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Cargo:</td>
            <td>{{ $advance->employee->position ?? 'N/A' }}</td>
            <td class="label">Salário Base:</td>
            <td><span class="amount-highlight">{{ number_format($advance->employee->base_salary ?? 0, 2, ',', '.') }} AOA</span></td>
        </tr>
    </table>

    <div class="section-title">2. DETALHES DO ADIANTAMENTO</div>
    <table class="info-table">
        <tr>
            <td class="label">Valor Solicitado:</td>
            <td><strong style="font-size: 14pt; color: #dc2626;">{{ number_format($advance->amount, 2, ',', '.') }} AOA</strong></td>
            <td class="label">Nº de Prestações:</td>
            <td><strong>{{ $advance->installments }} x</strong></td>
        </tr>
        <tr>
            <td class="label">Valor por Prestação:</td>
            <td><span class="amount-highlight">{{ number_format($advance->installment_amount, 2, ',', '.') }} AOA</span></td>
            <td class="label">Início dos Descontos:</td>
            <td>{{ Carbon\Carbon::parse($advance->first_deduction_date)->format('d/m/Y') }}</td>
        </tr>
    </table>

    <div class="section-title">3. JUSTIFICATIVA</div>
    <div style="border: 1px solid #d1d5db; padding: 15px; min-height: 80px; background-color: #fafafa;">
        <strong>Motivo:</strong> {{ $advance->reason ?? 'Não especificado' }}
        @if($advance->notes)
            <br><br><strong>Observações:</strong> {{ $advance->notes }}
        @endif
    </div>

    <div class="section-title">4. CRONOGRAMA DE DESCONTOS</div>
    <table class="payment-schedule">
        <thead>
            <tr>
                <th style="width: 15%;">Prestação</th>
                <th style="width: 25%;">Data Prevista</th>
                <th style="width: 20%;">Valor</th>
                <th style="width: 20%;">Status</th>
                <th style="width: 20%;">Data Efetiva</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentDate = Carbon\Carbon::parse($advance->first_deduction_date);
            @endphp
            @for($i = 1; $i <= $advance->installments; $i++)
                @php
                    $payment = $advance->payments()->where('installment_number', $i)->first();
                @endphp
                <tr>
                    <td><strong>{{ $i }}ª</strong></td>
                    <td>{{ $currentDate->format('d/m/Y') }}</td>
                    <td>{{ number_format($advance->installment_amount, 2, ',', '.') }} AOA</td>
                    <td>
                        @if($payment)
                            <span style="color: #059669; font-weight: bold;">✓ Pago</span>
                        @else
                            <span style="color: #dc2626;">Pendente</span>
                        @endif
                    </td>
                    <td>{{ $payment ? Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') : '-' }}</td>
                </tr>
                @php $currentDate->addMonth(); @endphp
            @endfor
        </tbody>
    </table>

    <div class="terms-section">
        <div class="terms-title">TERMOS E CONDIÇÕES</div>
        <ol class="terms-list">
            <li>O adiantamento será descontado do salário conforme cronograma estabelecido;</li>
            <li>Em caso de rescisão do contrato, o saldo devedor será descontado na liquidação;</li>
            <li>O funcionário declara estar ciente das condições de desconto;</li>
            <li>Este documento serve como comprovante e autorização para desconto;</li>
            <li>Qualquer alteração deve ser comunicada formalmente ao RH.</li>
        </ol>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-label">FUNCIONÁRIO</div>
                    <div style="margin-top: 5px; font-size: 10pt;">{{ $advance->employee->full_name }}</div>
                </td>
                <td>
                    <div class="signature-label">RECURSOS HUMANOS / TESOURARIA</div>
                    <div style="margin-top: 5px; font-size: 10pt;">{{ $approved_by ?? '_________________________' }}</div>
                </td>
            </tr>
        </table>
        
        <div style="text-align: center; margin-top: 30px; font-size: 10pt;">
            <strong>Data:</strong> {{ now()->format('d/m/Y') }}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <strong>Local:</strong> {{ $company_city ?? 'Luanda' }}
        </div>
    </div>

    <div class="footer">
        Documento gerado automaticamente pelo sistema ERPDEMBENA em {{ now()->format('d/m/Y H:i') }}
        <br>Este documento possui valor legal para fins de comprovação de adiantamento salarial
    </div>
</body>
</html>
