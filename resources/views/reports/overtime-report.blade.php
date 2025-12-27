<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{__('messages.overtime')}} - {{ $overtime->employee->full_name }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
        
        .action-btn {
            padding: 12px 24px;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-btn {
            background: linear-gradient(135deg, #9E9E9E 0%, #757575 100%);
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(158, 158, 158, 0.4);
        }
        
        .lang-btn {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            min-width: 50px;
            justify-content: center;
        }
        
        .lang-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4);
        }
        
        .lang-btn.active {
            background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
        }
        
        .print-btn {
            background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
        }
        
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(156, 39, 176, 0.4);
        }
        
        .pdf-btn {
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        }
        
        .pdf-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(244, 67, 54, 0.4);
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            .container {
                box-shadow: none;
                padding: 20px;
                max-width: 100%;
                margin: 0;
            }
            
            .action-buttons {
                display: none;
            }
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
            background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
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
            background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
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
    <div class="action-buttons">
        <button class="action-btn back-btn" onclick="window.history.back()">
            <i class="fas fa-arrow-left"></i>
            <span data-i18n="back">{{ __('messages.back') }}</span>
        </button>
        <button class="action-btn lang-btn" id="langPT" onclick="setLanguage('pt')">
            🇦🇴 PT
        </button>
        <button class="action-btn lang-btn" id="langEN" onclick="setLanguage('en')">
            🇬🇧 EN
        </button>
        <button class="action-btn print-btn" onclick="window.print()">
            <i class="fas fa-print"></i>
            <span data-i18n="print">{{ __('messages.print') }}</span>
        </button>
        <a href="{{ route('hr.overtime-report.pdf', $overtime->id) }}" class="action-btn pdf-btn">
            <i class="fas fa-file-pdf"></i>
            <span data-i18n="download_pdf">{{ __('messages.download_pdf') }}</span>
        </a>
    </div>
    
    <div class="container">
        {{-- Header com Logo --}}
        <div class="header">
            @if($companyLogo)
                <img src="{{ asset('storage/' . $companyLogo) }}" alt="Logo">
            @else
                <img src="{{ asset('img/logo.png') }}" alt="Logo">
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
        <div class="document-title" data-i18n="overtime_record">
            {{ __('messages.overtime_record') }}
        </div>

        {{-- Informações do Funcionário --}}
        <div class="info-section">
            <h3><i class="fas fa-user"></i> <span data-i18n="employee_information">{{ __('messages.employee_information') }}</span></h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">{{ __('messages.name') }}:</span>
                    <span class="info-value">{{ $overtime->employee->full_name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('messages.id_card') }}:</span>
                    <span class="info-value">{{ $overtime->employee->id_card }}</span>
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

        {{-- Resumo Financeiro --}}
        <div class="financial-summary">
            <h3 data-i18n="overtime_summary">
                @if($overtime->is_night_shift)
                    {{ __('messages.night_allowance') }}
                @else
                    {{ __('messages.overtime_summary') }}
                @endif
            </h3>
            <div class="financial-grid">
                <div class="financial-item">
                    <p data-i18n="date">{{ __('messages.date') }}</p>
                    <p>{{ $overtime->date->format('d/m/Y') }}</p>
                </div>
                @if($overtime->is_night_shift)
                <div class="financial-item">
                    <p data-i18n="days_worked">{{ __('messages.days_worked') }}</p>
                    <p>{{ number_format($overtime->direct_hours, 1) }} {{ __('messages.days') }}</p>
                </div>
                @else
                <div class="financial-item">
                    <p data-i18n="hours">{{ __('messages.hours') }}</p>
                    <p>{{ number_format($overtime->hours, 2) }}h</p>
                </div>
                @endif
                <div class="financial-item">
                    <p data-i18n="amount">{{ __('messages.amount') }}</p>
                    <p>{{ number_format($overtime->amount, 2, ',', '.') }} Kz</p>
                </div>
            </div>
        </div>

        {{-- Detalhes do Overtime --}}
        <div class="info-section">
            <h3><i class="fas fa-info-circle"></i> 
                <span data-i18n="overtime_details">
                    @if($overtime->is_night_shift)
                        {{ __('messages.night_allowance') }} {{ __('messages.details') }}
                    @else
                        {{ __('messages.overtime_details') }}
                    @endif
                </span>
            </h3>
            <div class="info-grid">
                @if($overtime->is_night_shift)
                    {{-- Night Allowance Details --}}
                    <div class="info-item">
                        <span class="info-label">{{ __('messages.days_worked') }}:</span>
                        <span class="info-value">{{ number_format($overtime->direct_hours, 1) }} {{ __('messages.days') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{ __('messages.daily_rate') }}:</span>
                        <span class="info-value">{{ number_format($overtime->rate, 2, ',', '.') }} Kz/dia</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{ __('messages.subtotal') }}:</span>
                        <span class="info-value">{{ number_format($overtime->direct_hours * $overtime->rate, 2, ',', '.') }} Kz</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">{{ __('messages.night_shift_bonus') }} (20%):</span>
                        <span class="info-value">{{ number_format($overtime->amount, 2, ',', '.') }} Kz</span>
                    </div>
                @else
                    {{-- Regular Overtime Details --}}
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
                @endif
                <div class="info-item">
                    <span class="info-label">{{ __('messages.status') }}:</span>
                    <span class="info-value">
                        <span class="status-badge status-{{ $overtime->status }}">
                            {{ __('messages.' . $overtime->status) }}
                        </span>
                    </span>
                </div>
                @if($overtime->status === 'approved' && $overtime->approver)
                <div class="info-item">
                    <span class="info-label">{{ __('messages.approved_by') }}:</span>
                    <span class="info-value">{{ $overtime->approver->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('messages.approved_at') }}:</span>
                    <span class="info-value">{{ $overtime->approved_at ? $overtime->approved_at->format('d/m/Y H:i') : 'N/A' }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Motivo --}}
        @if($overtime->notes)
        <div class="notes-section">
            <h4><i class="fas fa-sticky-note"></i> {{ __('messages.notes') }}</h4>
            <p>{{ $overtime->notes }}</p>
        </div>
        @endif

        {{-- Assinaturas --}}
        <div class="signatures">
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-name">{{ __('messages.supervisor') }}</div>
                    <div class="signature-role">{{ __('messages.signature') }}</div>
                    <div class="signature-role">{{ __('messages.date') }}: _____/_____/_________</div>
                </div>
            </div>
            
            <div class="signature-box">
                <div class="signature-line">
                    <div class="signature-name">{{ $overtime->employee->full_name }}</div>
                    <div class="signature-role">{{ __('messages.employee_signature') }}</div>
                    <div class="signature-role">{{ __('messages.date') }}: _____/_____/_________</div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p><strong>{{ $companyName }}</strong> &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
            <p>{{ __('messages.document_generated_at') }}: {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>
    
    <script>
        // Sistema de tradução
        const translations = {
            pt: {
                'back': 'Voltar',
                'print': 'Imprimir',
                'download_pdf': 'Baixar PDF',
                'overtime_record': 'REGISTRO DE HORAS EXTRAS',
                'employee_information': 'Informações do Funcionário',
                'overtime_summary': 'Resumo de Horas Extras',
                'overtime_details': 'Detalhes das Horas Extras',
                'date': 'Data',
                'hours': 'Horas',
                'amount': 'Valor'
            },
            en: {
                'back': 'Back',
                'print': 'Print',
                'download_pdf': 'Download PDF',
                'overtime_record': 'OVERTIME RECORD',
                'employee_information': 'Employee Information',
                'overtime_summary': 'Overtime Summary',
                'overtime_details': 'Overtime Details',
                'date': 'Date',
                'hours': 'Hours',
                'amount': 'Amount'
            }
        };
        
        let currentLang = localStorage.getItem('reportLang') || 'pt';
        
        function setLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('reportLang', lang);
            
            document.querySelectorAll('[data-i18n]').forEach(element => {
                const key = element.getAttribute('data-i18n');
                if (translations[lang][key]) {
                    element.textContent = translations[lang][key];
                }
            });
            
            document.getElementById('langPT').classList.toggle('active', lang === 'pt');
            document.getElementById('langEN').classList.toggle('active', lang === 'en');
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            setLanguage(currentLang);
        });
        
        // Atalho de impressão
        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.metaKey) && event.key === 'p') {
                event.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
