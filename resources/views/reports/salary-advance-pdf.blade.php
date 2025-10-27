<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{__('messages.salary_advance')}} - {{ $advance->employee->full_name }}</title>
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
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #4CAF50;
        }
        
        .header img {
            max-height: 80px;
            margin-bottom: 10px;
        }
        
        .header h1 {
            color: #4CAF50;
            font-size: 24px;
            margin: 10px 0;
            text-transform: uppercase;
        }
        
        .header .company-info {
            font-size: 10px;
            color: #666;
            margin-top: 8px;
        }
        
        .document-title {
            text-align: center;
            background: #4CAF50;
            color: white;
            padding: 15px;
            margin: 20px 0;
            font-size: 18px;
            font-weight: bold;
        }
        
        .info-section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #4CAF50;
        }
        
        .info-section h3 {
            color: #4CAF50;
            font-size: 14px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .info-grid {
            margin-top: 10px;
        }
        
        .info-item {
            margin-bottom: 8px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            min-width: 180px;
        }
        
        .info-value {
            color: #333;
        }
        
        .financial-summary {
            background: #667eea;
            color: white;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        
        .financial-summary h3 {
            font-size: 16px;
            margin-bottom: 15px;
        }
        
        .financial-grid {
            width: 100%;
            overflow: hidden;
        }
        
        .financial-item {
            float: left;
            width: 33.33%;
            box-sizing: border-box;
            padding: 0 5px;
        }
        
        .financial-item p:first-child {
            font-size: 10px;
            opacity: 0.9;
            margin-bottom: 5px;
        }
        
        .financial-item p:last-child {
            font-size: 20px;
            font-weight: bold;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        
        .status-pending {
            background: #FFF3CD;
            color: #856404;
        }
        
        .status-approved {
            background: #D4EDDA;
            color: #155724;
        }
        
        .status-rejected {
            background: #F8D7DA;
            color: #721C24;
        }
        
        .status-completed {
            background: #D1ECF1;
            color: #0C5460;
        }
        
        .signatures {
            margin-top: 60px;
            width: 100%;
            overflow: hidden;
        }
        
        .signature-box {
            float: left;
            width: 48%;
            text-align: center;
            padding: 20px;
            border: 2px solid #ddd;
            margin: 0 1%;
            box-sizing: border-box;
        }
        
        .clear {
            clear: both;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            margin: 60px 20px 10px 20px;
            padding-top: 10px;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 12px;
            color: #333;
        }
        
        .signature-role {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .notes-section {
            margin: 20px 0;
            padding: 15px;
            background: #fff9e6;
            border-left: 4px solid #ffc107;
        }
        
        .notes-section h4 {
            color: #f57c00;
            font-size: 12px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    {{-- Header com Logo --}}
    <div class="header">
        @if($companyLogo)
            <img src="{{ public_path('storage/' . $companyLogo) }}" alt="Logo">
        @endif
        <h1>{{ $companyName }}</h1>
        <div class="company-info">
            @if($companyAddress) {{ $companyAddress }} @endif
            @if($companyPhone) | Tel: {{ $companyPhone }} @endif
            @if($companyEmail) | Email: {{ $companyEmail }} @endif
            @if($companyTaxId) | NIF: {{ $companyTaxId }} @endif
        </div>
    </div>

    {{-- Título do Documento --}}
    <div class="document-title">
        {{ __('messages.salary_advance_agreement') }}
    </div>

    {{-- Informações do Funcionário --}}
    <div class="info-section">
        <h3>{{ __('messages.employee_information') }}</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">{{ __('messages.name') }}:</span>
                <span class="info-value">{{ $advance->employee->full_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('messages.id_card') }}:</span>
                <span class="info-value">{{ $advance->employee->id_card }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('messages.employee_id') }}:</span>
                <span class="info-value">{{ $advance->employee->employee_id }}</span>
            </div>
            @if($advance->employee->department)
            <div class="info-item">
                <span class="info-label">{{ __('messages.department') }}:</span>
                <span class="info-value">{{ $advance->employee->department->name }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Resumo Financeiro --}}
    <div class="financial-summary">
        <h3>{{ __('messages.financial_summary') }}</h3>
        <div class="financial-grid">
            <div class="financial-item">
                <p>{{ __('messages.total_amount') }}</p>
                <p>{{ number_format($advance->amount, 2, ',', '.') }} Kz</p>
            </div>
            <div class="financial-item">
                <p>{{ __('messages.installments') }}</p>
                <p>{{ $advance->installments }}x</p>
            </div>
            <div class="financial-item">
                <p>{{ __('messages.installment_amount') }}</p>
                <p>{{ number_format($advance->installment_amount, 2, ',', '.') }} Kz</p>
            </div>
            <div class="clear"></div>
        </div>
    </div>

    {{-- Detalhes do Adiantamento --}}
    <div class="info-section">
        <h3>{{ __('messages.advance_details') }}</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">{{ __('messages.request_date') }}:</span>
                <span class="info-value">{{ $advance->request_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('messages.first_deduction_date') }}:</span>
                <span class="info-value">{{ $advance->first_deduction_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('messages.status') }}:</span>
                <span class="info-value">
                    <span class="status-badge status-{{ $advance->status }}">
                        {{ __('messages.' . $advance->status) }}
                    </span>
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('messages.remaining_installments') }}:</span>
                <span class="info-value">{{ $advance->remaining_installments }} de {{ $advance->installments }}</span>
            </div>
        </div>
    </div>

    {{-- Motivo --}}
    @if($advance->reason)
    <div class="notes-section">
        <h4>{{ __('messages.reason') }}</h4>
        <p>{{ $advance->reason }}</p>
    </div>
    @endif

    {{-- Notas --}}
    @if($advance->notes)
    <div class="notes-section">
        <h4>{{ __('messages.notes') }}</h4>
        <p>{{ $advance->notes }}</p>
    </div>
    @endif

    {{-- Assinaturas --}}
    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                <div class="signature-name">{{ $advance->createdBy->name ?? __('messages.system') }}</div>
                <div class="signature-role">{{ __('messages.created_by') }}</div>
                <div class="signature-role">{{ __('messages.date') }}: {{ $advance->created_at->format('d/m/Y') }}</div>
            </div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line">
                <div class="signature-name">{{ $advance->employee->full_name }}</div>
                <div class="signature-role">{{ __('messages.employee_signature') }}</div>
                <div class="signature-role">{{ __('messages.date') }}: _____/_____/_________</div>
            </div>
        </div>
        
        <div class="clear"></div>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <p><strong>{{ $companyName }}</strong> &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
        <p>{{ __('messages.document_generated_at') }}: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
