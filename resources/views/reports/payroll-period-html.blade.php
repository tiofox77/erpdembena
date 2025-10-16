<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Consolidado - {{ $periodName }}</title>
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
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
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
        
        .print-btn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
        }
        
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
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
        
        .detail-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .detail-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        
        .detail-btn.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }
            
            .container {
                box-shadow: none;
                padding: 8px;
                max-width: 100%;
                margin: 0;
                transform: scale(0.95);
                transform-origin: top center;
            }
            
            .action-buttons {
                display: none;
            }
            
            /* Otimizações para caber em 1 página */
            @page {
                size: A4;
                margin: 10mm;
            }
            
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .summary-card.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .summary-card.red {
            background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
        }
        
        .summary-card.blue {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .summary-card h3 {
            font-size: 12px;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .summary-card .amount {
            font-size: 24px;
            font-weight: bold;
        }
        
        .batch-section, .individual-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            background: #2563eb;
            color: white;
            padding: 12px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .detail-table th,
        .detail-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 11px;
        }
        
        .detail-table th {
            background-color: #f3f4f6;
            font-weight: 600;
            color: #374151;
        }
        
        .detail-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .text-green {
            color: #10b981;
        }
        
        .text-red {
            color: #ef4444;
        }
        
        .text-blue {
            color: #3b82f6;
        }
        
        .action-button {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .print-btn {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
        }
        
        .detail-btn {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .details-section {
            margin-top: 30px;
            border-top: 2px solid #e5e7eb;
            padding-top: 30px;
        }
        
        @media print {
            .action-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="action-buttons">
        <button class="action-btn back-btn" onclick="window.history.back()">
            <i class="fas fa-arrow-left"></i>
            <span data-i18n="back">Voltar</span>
        </button>
        <button class="action-btn lang-btn" id="langPT" onclick="setLanguage('pt')">
            🇦🇴 PT
        </button>
        <button class="action-btn lang-btn" id="langEN" onclick="setLanguage('en')">
            🇬🇧 EN
        </button>
        <button class="action-btn detail-btn" onclick="toggleDetails()">
            <i class="fas fa-eye"></i>
            <span data-i18n="toggle_details">Ver Detalhes</span>
        </button>
        <button class="action-btn print-btn" onclick="window.print()">
            <i class="fas fa-print"></i>
            <span data-i18n="print">Imprimir</span>
        </button>
    </div>
    
    <div class="container">
        {{-- Header com Logo e Informações da Empresa --}}
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="margin-bottom: 15px;">
                @php
                    $logoPath = \App\Models\Setting::get('company_logo');
                @endphp
                @if($logoPath)
                    <img src="{{ asset('storage/' . $logoPath) }}" alt="Logo" style="max-height: 100px; max-width: 250px;">
                @else
                    <img src="{{ asset('img/logo.png') }}" alt="Logo" style="max-height: 100px; max-width: 250px;">
                @endif
            </div>
            <div>
                @php
                    $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
                @endphp
                <h2 style="margin: 8px 0; padding: 0; font-size: 20px; font-weight: bold; color: #2563eb;">{{ $companyName }}</h2>
            </div>
            <div style="border-top: 2px solid #2563eb; padding-top: 15px; margin-top: 15px;">
                <h1 style="margin: 0; font-size: 22px; color: #2563eb; font-weight: bold;" data-i18n="consolidated_payroll_report">RELATÓRIO CONSOLIDADO DE FOLHA DE PAGAMENTO</h1>
                <p style="margin: 5px 0; font-size: 14px; color: #333;"><strong>{{ $periodName }}</strong></p>
                <p style="margin: 3px 0; font-size: 12px; color: #666;">{{ $batchDate }}</p>
            </div>
        </div>
        
        {{-- Resumo Financeiro --}}
        <div class="summary-cards">
            <div class="summary-card green">
                <h3 data-i18n="total_gross_salary">SALÁRIO BRUTO TOTAL</h3>
                <div class="amount">{{ number_format($totals['grand_total'], 2, ',', '.') }}</div>
                <div style="font-size: 10px; margin-top: 5px;">AOA</div>
            </div>
            <div class="summary-card red">
                <h3 data-i18n="total_deductions_label">DEDUÇÕES TOTAIS</h3>
                <div class="amount">{{ number_format($totals['total_deductions'], 2, ',', '.') }}</div>
                <div style="font-size: 10px; margin-top: 5px;">AOA</div>
            </div>
            <div class="summary-card blue">
                <h3 data-i18n="total_net_salary">SALÁRIO LÍQUIDO TOTAL</h3>
                <div class="amount">{{ number_format($totals['net_total'], 2, ',', '.') }}</div>
                <div style="font-size: 10px; margin-top: 5px;">AOA</div>
            </div>
        </div>
        
        {{-- Informações Gerais --}}
        <div style="background: #f3f4f6; padding: 15px; margin-bottom: 25px; border-left: 4px solid #2563eb; border-radius: 4px;">
            <table style="width: 100%;">
                <tr>
                    <td style="padding: 5px;"><strong style="color: #2563eb;" data-i18n="total_employees">Total de Funcionários</strong>: {{ $totals['total_employees'] }}</td>
                    <td style="padding: 5px;"><strong style="color: #2563eb;" data-i18n="batch_payments">Batches Processados</strong>: {{ $totals['batch_count'] }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px;"><strong style="color: #2563eb;" data-i18n="individual_payments">Pagamentos Individuais</strong>: {{ $totals['individual_count'] }}</td>
                    <td style="padding: 5px;"><strong style="color: #2563eb;">Data de Geração</strong>: {{ $generatedAt }}</td>
                </tr>
            </table>
        </div>
        
        {{-- Seção de Batches --}}
        @if($batches->count() > 0)
        <div class="batch-section details-section" id="batchDetails" style="display: none;">
            <div class="section-title">
                📦 <span data-i18n="batch_details">PAGAMENTOS EM BATCH</span> ({{ $batches->count() }} lote(s))
            </div>
            
            <table class="detail-table">
                <thead>
                    <tr>
                        <th data-i18n="batch_name">Nome do Batch</th>
                        <th data-i18n="department">Departamento</th>
                        <th class="text-center" data-i18n="employees">Funcionários</th>
                        <th class="text-right" data-i18n="gross_amount">Salário Bruto</th>
                        <th class="text-right" data-i18n="deductions">Deduções</th>
                        <th class="text-right" data-i18n="net_amount">Salário Líquido</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($batches as $batch)
                    <tr>
                        <td class="font-bold">{{ $batch->name }}</td>
                        <td>{{ $batch->department->name ?? 'Todos' }}</td>
                        <td class="text-center">{{ $batch->total_employees }}</td>
                        <td class="text-right text-green font-bold">{{ number_format($batch->total_gross_amount, 2, ',', '.') }} AOA</td>
                        <td class="text-right text-red font-bold">{{ number_format($batch->total_deductions, 2, ',', '.') }} AOA</td>
                        <td class="text-right text-blue font-bold">{{ number_format($batch->total_net_amount, 2, ',', '.') }} AOA</td>
                    </tr>
                    @endforeach
                    <tr style="background-color: #e5e7eb; font-weight: bold;">
                        <td colspan="2" data-i18n="total">SUBTOTAL BATCHES</td>
                        <td class="text-center">{{ $totals['batch_employees'] }}</td>
                        <td class="text-right text-green">{{ number_format($totals['batch_gross'], 2, ',', '.') }} AOA</td>
                        <td class="text-right text-red">{{ number_format($totals['batch_deductions'], 2, ',', '.') }} AOA</td>
                        <td class="text-right text-blue">{{ number_format($totals['batch_net'], 2, ',', '.') }} AOA</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
        
        {{-- Seção de Individuais --}}
        @if($individualPayrolls->count() > 0)
        <div class="individual-section details-section" id="individualDetails" style="display: none;">
            <div class="section-title">
                👤 <span data-i18n="individual_details">PAGAMENTOS INDIVIDUAIS</span> ({{ $individualPayrolls->count() }} funcionário(s))
            </div>
            
            <table class="detail-table">
                <thead>
                    <tr>
                        <th data-i18n="employee">Funcionário</th>
                        <th data-i18n="department">Departamento</th>
                        <th class="text-right" data-i18n="gross_amount">Salário Bruto</th>
                        <th class="text-right" data-i18n="deductions">Deduções</th>
                        <th class="text-right" data-i18n="net_amount">Salário Líquido</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($individualPayrolls as $payroll)
                    <tr>
                        <td>{{ $payroll->employee->full_name ?? 'N/A' }}</td>
                        <td>{{ $payroll->employee->department->name ?? 'N/A' }}</td>
                        <td class="text-right text-green">{{ number_format($payroll->gross_salary, 2, ',', '.') }} AOA</td>
                        <td class="text-right text-red">{{ number_format($payroll->deductions ?? 0, 2, ',', '.') }} AOA</td>
                        <td class="text-right text-blue">{{ number_format($payroll->net_salary, 2, ',', '.') }} AOA</td>
                    </tr>
                    @endforeach
                    <tr style="background-color: #e5e7eb; font-weight: bold;">
                        <td colspan="2" data-i18n="total">SUBTOTAL INDIVIDUAIS</td>
                        <td class="text-right text-green">{{ number_format($totals['individual_gross'], 2, ',', '.') }} AOA</td>
                        <td class="text-right text-red">{{ number_format($totals['individual_deductions'], 2, ',', '.') }} AOA</td>
                        <td class="text-right text-blue">{{ number_format($totals['individual_net'], 2, ',', '.') }} AOA</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
        
        {{-- Total Geral --}}
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-top: 30px;">
            <table style="width: 100%; color: white;">
                <tr>
                    <td style="padding: 10px; font-size: 14px; font-weight: bold;">TOTAL GERAL CONSOLIDADO</td>
                    <td style="padding: 10px; text-align: right; font-size: 18px; font-weight: bold;">
                        {{ number_format($totals['grand_total'], 2, ',', '.') }} AOA
                    </td>
                    <td style="padding: 10px; text-align: right; font-size: 18px; font-weight: bold;">
                        -{{ number_format($totals['total_deductions'], 2, ',', '.') }} AOA
                    </td>
                    <td style="padding: 10px; text-align: right; font-size: 20px; font-weight: bold;">
                        = {{ number_format($totals['net_total'], 2, ',', '.') }} AOA
                    </td>
                </tr>
            </table>
        </div>
        
        {{-- Footer --}}
        <div style="margin-top: 40px; border-top: 2px solid #e5e7eb; padding-top: 15px; text-align: center;">
            <p style="margin: 5px 0; font-size: 10px; color: #666;">
                Relatório gerado em: {{ $generatedAt }} | Por: {{ $creatorName }}
            </p>
            <p style="margin: 5px 0; font-size: 10px; color: #999;">
                Sistema ERP DEMBENA - Gestão de Recursos Humanos
            </p>
        </div>
    </div>
    
    <script>
        // Sistema de tradução
        const translations = {
            pt: {
                'back': 'Voltar',
                'print': 'Imprimir',
                'toggle_details': 'Ver Detalhes',
                'hide_details': 'Ocultar Detalhes',
                'consolidated_payroll_report': 'RELATÓRIO CONSOLIDADO DE FOLHA DE PAGAMENTO',
                'total_gross_salary': 'SALÁRIO BRUTO TOTAL',
                'total_deductions_label': 'DEDUÇÕES TOTAIS',
                'total_net_salary': 'SALÁRIO LÍQUIDO TOTAL',
                'total_employees': 'Total de Funcionários',
                'batch_payments': 'Pagamentos em Batch',
                'individual_payments': 'Pagamentos Individuais',
                'payment_summary': 'RESUMO DE PAGAMENTOS',
                'batch_details': 'PAGAMENTOS EM BATCH',
                'individual_details': 'PAGAMENTOS INDIVIDUAIS',
                'batch_name': 'Batch',
                'department': 'Departamento',
                'employees': 'Funcionários',
                'gross_amount': 'Valor Bruto',
                'deductions': 'Deduções',
                'net_amount': 'Valor Líquido',
                'employee': 'Funcionário',
                'total': 'Total'
            },
            en: {
                'back': 'Back',
                'print': 'Print',
                'toggle_details': 'View Details',
                'hide_details': 'Hide Details',
                'consolidated_payroll_report': 'CONSOLIDATED PAYROLL REPORT',
                'total_gross_salary': 'TOTAL GROSS SALARY',
                'total_deductions_label': 'TOTAL DEDUCTIONS',
                'total_net_salary': 'TOTAL NET SALARY',
                'total_employees': 'Total Employees',
                'batch_payments': 'Batch Payments',
                'individual_payments': 'Individual Payments',
                'payment_summary': 'PAYMENT SUMMARY',
                'batch_details': 'BATCH PAYMENTS',
                'individual_details': 'INDIVIDUAL PAYMENTS',
                'batch_name': 'Batch',
                'department': 'Department',
                'employees': 'Employees',
                'gross_amount': 'Gross Amount',
                'deductions': 'Deductions',
                'net_amount': 'Net Amount',
                'employee': 'Employee',
                'total': 'Total'
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
            
            // Atualizar botão de detalhes
            if (detailsVisible) {
                document.querySelector('.detail-btn [data-i18n]').setAttribute('data-i18n', 'hide_details');
                document.querySelector('.detail-btn [data-i18n]').textContent = translations[lang]['hide_details'];
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            setLanguage(currentLang);
        });
        
        // Toggle de detalhes
        let detailsVisible = false;
        
        function toggleDetails() {
            detailsVisible = !detailsVisible;
            
            const batchDetails = document.getElementById('batchDetails');
            const individualDetails = document.getElementById('individualDetails');
            const button = document.querySelector('.detail-btn');
            
            if (detailsVisible) {
                if (batchDetails) batchDetails.style.display = 'block';
                if (individualDetails) individualDetails.style.display = 'block';
                const span = button.querySelector('[data-i18n]');
                span.setAttribute('data-i18n', 'hide_details');
                span.textContent = translations[currentLang]['hide_details'];
                button.classList.add('active');
            } else {
                if (batchDetails) batchDetails.style.display = 'none';
                if (individualDetails) individualDetails.style.display = 'none';
                const span = button.querySelector('[data-i18n]');
                span.setAttribute('data-i18n', 'toggle_details');
                span.textContent = translations[currentLang]['toggle_details'];
                button.classList.remove('active');
            }
        }
        
        // Atalho para imprimir (Ctrl+P)
        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.metaKey) && event.key === 'p') {
                event.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
