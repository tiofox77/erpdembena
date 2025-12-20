<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{__('messages.overtime')}} - {{ $overtime->employee->full_name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #9C27B0;
        }
        
        .header img {
            max-height: 80px;
            margin-bottom: 10px;
        }
        
        .header h1 {
            color: #9C27B0;
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
            background: #9C27B0;
            color: white;
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
        }
        
        .info-section {
            margin: 20px 0;
            padding: 15px;
            background: #f9f9f9;
            border-left: 4px solid #9C27B0;
            border-radius: 4px;
        }
        
        .info-section h3 {
            color: #9C27B0;
            font-size: 14px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 10px;
        }
        
        .info-item {
            display: flex;
            gap: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
            min-width: 140px;
        }
        
        .info-value {
            color: #333;
            flex: 1;
        }
        
        .financial-summary {
            background: #9C27B0;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .financial-summary h3 {
            font-size: 16px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .financial-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            text-align: center;
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
        
        .signatures {
            margin-top: 60px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 40px;
        }
        
        .signature-box {
            text-align: center;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
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
            border-radius: 4px;
        }
        
        .notes-section h4 {
            color: #f57c00;
            font-size: 12px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
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

    <div class="document-title">
        {{ __('messages.overtime_record') }}
    </div>

    <div class="info-section">
        <h3>{{ __('messages.employee_information') }}</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">{{ __('messages.name') }}:</span>
                <span class="info-value">{{ $overtime->employee->full_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('messages.employee_id') }}:</span>
                <span class="info-value">{{ $overtime->employee->employee_id }}</span>
            </div>
            @if($overtime->employee->department)
            <div class="info-item">
                <span class="info-label">{{ __('messages.department') }}:</span>
                <span class="info-value">{{ $overtime->employee->department->name }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="financial-summary">
        <h3>{{ __('messages.overtime_summary') }}</h3>
        <div class="financial-grid">
            <div class="financial-item">
                <p>{{ __('messages.date') }}</p>
                <p>{{ $overtime->date->format('d/m/Y') }}</p>
            </div>
            <div class="financial-item">
                <p>{{ __('messages.hours') }}</p>
                <p>{{ number_format($overtime->hours, 2) }}h</p>
            </div>
            <div class="financial-item">
                <p>{{ __('messages.amount') }}</p>
                <p>{{ number_format($overtime->amount, 2, ',', '.') }} Kz</p>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h3>{{ __('messages.overtime_details') }}</h3>
        <div class="info-grid">
            @if($overtime->input_type === 'time_range' && $overtime->start_time && $overtime->end_time)
            <div class="info-item">
                <span class="info-label">{{ __('messages.start_time') }}:</span>
                <span class="info-value">{{ $overtime->start_time }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('messages.end_time') }}:</span>
                <span class="info-value">{{ $overtime->end_time }}</span>
            </div>
            @endif
            <div class="info-item">
                <span class="info-label">{{ __('messages.hourly_rate') }}:</span>
                <span class="info-value">{{ number_format($overtime->hourly_rate, 2, ',', '.') }} Kz/h</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('messages.status') }}:</span>
                <span class="info-value">{{ __('messages.' . $overtime->status) }}</span>
            </div>
        </div>
    </div>

    @if($overtime->notes)
    <div class="notes-section">
        <h4>{{ __('messages.notes') }}</h4>
        <p>{{ $overtime->notes }}</p>
    </div>
    @endif

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                <div class="signature-name">{{ __('messages.supervisor') }}</div>
                <div class="signature-role">{{ __('messages.signature') }}</div>
            </div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line">
                <div class="signature-name">{{ $overtime->employee->full_name }}</div>
                <div class="signature-role">{{ __('messages.employee_signature') }}</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>{{ $companyName }}</strong> &copy; {{ date('Y') }}</p>
        <p>{{ __('messages.document_generated_at') }}: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
