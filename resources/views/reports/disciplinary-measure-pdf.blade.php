<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{__('messages.disciplinary_measure')}} - {{ $measure->employee->full_name }}</title>
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
            border-bottom: 3px solid #DC2626;
        }
        
        .header img {
            max-height: 80px;
            margin-bottom: 10px;
        }
        
        .header h1 {
            color: #DC2626;
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
            background: #DC2626;
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
            border-left: 4px solid #DC2626;
            border-radius: 4px;
        }
        
        .info-section h3 {
            color: #DC2626;
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
        
        .measure-details {
            background: #DC2626;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .measure-details h3 {
            font-size: 16px;
            margin-bottom: 15px;
            text-align: center;
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
        
        .description-section {
            margin: 20px 0;
            padding: 15px;
            background: #fff9e6;
            border-left: 4px solid #ffc107;
            border-radius: 4px;
        }
        
        .description-section h4 {
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
        {{ __('messages.disciplinary_measure_form') }}
    </div>

    <div class="info-section">
        <h3>{{ __('messages.employee_information') }}</h3>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">{{ __('messages.name') }}:</span>
                <span class="info-value">{{ $measure->employee->full_name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('messages.id_card') }}:</span>
                <span class="info-value">{{ $measure->employee->id_card }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">{{ __('messages.employee_id') }}:</span>
                <span class="info-value">{{ $measure->employee->employee_id }}</span>
            </div>
            @if($measure->employee->department)
            <div class="info-item">
                <span class="info-label">{{ __('messages.department') }}:</span>
                <span class="info-value">{{ $measure->employee->department->name }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="measure-details">
        <h3>{{ __('messages.measure_details') }}</h3>
        <div class="info-grid" style="color: white;">
            <div class="info-item" style="color: white;">
                <span class="info-label" style="color: rgba(255,255,255,0.9);">{{ __('messages.measure_type') }}:</span>
                <span class="info-value" style="color: white;">{{ $measure->measure_type_name }}</span>
            </div>
            <div class="info-item" style="color: white;">
                <span class="info-label" style="color: rgba(255,255,255,0.9);">{{ __('messages.applied_date') }}:</span>
                <span class="info-value" style="color: white;">{{ $measure->applied_date->format('d/m/Y') }}</span>
            </div>
            @if($measure->effective_date)
            <div class="info-item" style="color: white;">
                <span class="info-label" style="color: rgba(255,255,255,0.9);">{{ __('messages.effective_date') }}:</span>
                <span class="info-value" style="color: white;">{{ $measure->effective_date->format('d/m/Y') }}</span>
            </div>
            @endif
            <div class="info-item" style="color: white;">
                <span class="info-label" style="color: rgba(255,255,255,0.9);">{{ __('messages.status') }}:</span>
                <span class="info-value" style="color: white;">{{ $measure->status_name }}</span>
            </div>
        </div>
    </div>

    <div class="info-section">
        <h3>{{ __('messages.reason') }}</h3>
        <p>{{ $measure->reason }}</p>
    </div>

    <div class="description-section">
        <h4>{{ __('messages.description') }}</h4>
        <p style="white-space: pre-wrap;">{{ $measure->description }}</p>
    </div>

    @if($measure->notes)
    <div class="description-section">
        <h4>{{ __('messages.notes') }}</h4>
        <p style="white-space: pre-wrap;">{{ $measure->notes }}</p>
    </div>
    @endif

    <div class="signatures">
        <div class="signature-box">
            <div class="signature-line">
                <div class="signature-name">{{ $measure->appliedByUser->name ?? __('messages.management') }}</div>
                <div class="signature-role">{{ __('messages.applied_by') }}</div>
            </div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line">
                <div class="signature-name">{{ $measure->employee->full_name }}</div>
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
