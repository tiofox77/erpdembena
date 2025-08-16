<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulário de Desconto Salarial</title>
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
            border-bottom: 2px solid #dc2626;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #991b1b;
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
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
        }
        
        .terms-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #991b1b;
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
            background-color: #fee2e2;
            padding: 3px 6px;
            border-radius: 4px;
            font-weight: bold;
            color: #991b1b;
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
        
        .status-active {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .discount-type {
            background-color: #fef3c7;
            padding: 8px 12px;
            border-radius: 6px;
            border-left: 4px solid #f59e0b;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $company_name ?? 'ERPDEMBENA' }}</div>
        <div style="font-size: 10pt; color: #6b7280;">{{ $company_address ?? 'Luanda, Angola' }}</div>
        <div class="document-title">FORMULÁRIO DE DESCONTO SALARIAL</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Nº do Desconto:</td>
                <td><strong>#{{ str_pad($discount->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                <td class="label">Data de Aplicação:</td>
                <td>{{ Carbon\Carbon::parse($discount->start_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="label">Status:</td>
                <td>
                    <span class="status-badge status-{{ $discount->status }}">
                        {{ ucfirst($discount->status) }}
                    </span>
                </td>
                <td class="label">Data Final:</td>
                <td>{{ $discount->end_date ? Carbon\Carbon::parse($discount->end_date)->format('d/m/Y') : 'Indefinido' }}</td>
            </tr>
        </table>
    </div>

    <div class="section-title">1. DADOS DO FUNCIONÁRIO</div>
    <table class="info-table">
        <tr>
            <td class="label">Nome Completo:</td>
            <td colspan="3"><strong>{{ $discount->employee->full_name }}</strong></td>
        </tr>
        <tr>
            <td class="label">Nº Funcionário:</td>
            <td>{{ $discount->employee->employee_number ?? 'N/A' }}</td>
            <td class="label">Departamento:</td>
            <td>{{ $discount->employee->department->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Cargo:</td>
            <td>{{ $discount->employee->position ?? 'N/A' }}</td>
            <td class="label">Salário Base:</td>
            <td><span class="amount-highlight">{{ number_format($discount->employee->base_salary ?? 0, 2, ',', '.') }} AOA</span></td>
        </tr>
    </table>

    <div class="section-title">2. DETALHES DO DESCONTO</div>
    
    <div class="discount-type">
        <strong>Tipo de Desconto:</strong> {{ ucfirst(str_replace('_', ' ', $discount->type)) }}
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Valor do Desconto:</td>
            <td><strong style="font-size: 14pt; color: #dc2626;">{{ number_format($discount->amount, 2, ',', '.') }} AOA</strong></td>
            <td class="label">Frequência:</td>
            <td><strong>{{ ucfirst($discount->frequency) }}</strong></td>
        </tr>
        @if($discount->frequency === 'installments')
        <tr>
            <td class="label">Nº de Prestações:</td>
            <td><strong>{{ $discount->installments ?? 1 }} x</strong></td>
            <td class="label">Valor por Prestação:</td>
            <td><span class="amount-highlight">{{ number_format(($discount->amount / ($discount->installments ?? 1)), 2, ',', '.') }} AOA</span></td>
        </tr>
        @endif
        <tr>
            <td class="label">Total Descontado:</td>
            <td><span class="amount-highlight">{{ number_format($discount->total_deducted ?? 0, 2, ',', '.') }} AOA</span></td>
            <td class="label">Saldo Restante:</td>
            <td><strong style="color: #dc2626;">{{ number_format(($discount->amount - ($discount->total_deducted ?? 0)), 2, ',', '.') }} AOA</strong></td>
        </tr>
    </table>

    <div class="section-title">3. MOTIVO E JUSTIFICATIVA</div>
    <div style="border: 1px solid #d1d5db; padding: 15px; min-height: 80px; background-color: #fafafa;">
        <strong>Motivo:</strong> {{ $discount->reason ?? 'Não especificado' }}
        @if($discount->description)
            <br><br><strong>Descrição Detalhada:</strong> {{ $discount->description }}
        @endif
        @if($discount->notes)
            <br><br><strong>Observações Administrativas:</strong> {{ $discount->notes }}
        @endif
    </div>

    @if($discount->frequency === 'installments' && $discount->installments > 1)
    <div class="section-title">4. CRONOGRAMA DE DESCONTOS</div>
    <table class="payment-schedule" style="width: 100%; border-collapse: collapse; margin-top: 15px;">
        <thead>
            <tr style="background-color: #f8fafc;">
                <th style="padding: 10px; border: 1px solid #d1d5db; text-align: center; font-weight: bold;">Prestação</th>
                <th style="padding: 10px; border: 1px solid #d1d5db; text-align: center; font-weight: bold;">Valor</th>
                <th style="padding: 10px; border: 1px solid #d1d5db; text-align: center; font-weight: bold;">Status</th>
                <th style="padding: 10px; border: 1px solid #d1d5db; text-align: center; font-weight: bold;">Data Efetiva</th>
            </tr>
        </thead>
        <tbody>
            @php
                $installmentAmount = $discount->amount / $discount->installments;
                $totalDeducted = $discount->total_deducted ?? 0;
                $paidInstallments = floor($totalDeducted / $installmentAmount);
            @endphp
            @for($i = 1; $i <= $discount->installments; $i++)
                <tr>
                    <td style="padding: 10px; border: 1px solid #d1d5db; text-align: center;"><strong>{{ $i }}ª</strong></td>
                    <td style="padding: 10px; border: 1px solid #d1d5db; text-align: center;">{{ number_format($installmentAmount, 2, ',', '.') }} AOA</td>
                    <td style="padding: 10px; border: 1px solid #d1d5db; text-align: center;">
                        @if($i <= $paidInstallments)
                            <span style="color: #059669; font-weight: bold;">✓ Descontado</span>
                        @else
                            <span style="color: #dc2626;">Pendente</span>
                        @endif
                    </td>
                    <td style="padding: 10px; border: 1px solid #d1d5db; text-align: center;">
                        {{ $i <= $paidInstallments ? '(Processado)' : 'A processar' }}
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
    @endif

    <div class="terms-section">
        <div class="terms-title">TERMOS E CONDIÇÕES</div>
        <ol class="terms-list">
            <li>O desconto será aplicado conforme especificações estabelecidas neste documento;</li>
            <li>Em caso de rescisão do contrato, o saldo devedor poderá ser descontado na liquidação;</li>
            <li>O funcionário declara estar ciente das condições e motivos do desconto;</li>
            <li>Este documento serve como comprovante e autorização para desconto salarial;</li>
            <li>Qualquer contestação deve ser comunicada formalmente ao RH no prazo de 30 dias;</li>
            <li>O desconto está em conformidade com a legislação trabalhista vigente.</li>
        </ol>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-label">FUNCIONÁRIO</div>
                    <div style="margin-top: 5px; font-size: 10pt;">{{ $discount->employee->full_name }}</div>
                    <div style="margin-top: 10px; font-size: 9pt; color: #6b7280;">
                        (Ciente e de acordo)
                    </div>
                </td>
                <td>
                    <div class="signature-label">RECURSOS HUMANOS / TESOURARIA</div>
                    <div style="margin-top: 5px; font-size: 10pt;">{{ $approved_by ?? '_________________________' }}</div>
                    <div style="margin-top: 10px; font-size: 9pt; color: #6b7280;">
                        (Autorização)
                    </div>
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
        <br>Este documento possui valor legal para fins de comprovação de desconto salarial
    </div>
</body>
</html>
