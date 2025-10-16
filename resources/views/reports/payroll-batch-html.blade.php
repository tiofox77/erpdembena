<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Folha de Pagamento - {{ $batchName }}</title>
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
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }
        
        .header h1 {
            font-size: 22px;
            margin-bottom: 8px;
            color: #000;
            font-weight: bold;
        }
        
        .header p {
            font-size: 12px;
            color: #666;
            margin: 3px 0;
        }
        
        .period-info {
            background: #f9f9f9;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid #4CAF50;
        }
        
        .period-info table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .period-info td {
            padding: 5px 8px;
            font-size: 11px;
        }
        
        .period-info strong {
            color: #000;
            font-weight: 600;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .summary-table th,
        .summary-table td {
            padding: 10px 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .summary-table th {
            background-color: #2563eb;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            padding: 12px;
        }
        
        .summary-table td.code {
            width: 100px;
            text-align: center;
            font-weight: bold;
            color: #555;
            font-size: 10px;
        }
        
        .summary-table td.amount {
            text-align: right;
            font-weight: bold;
            font-size: 12px;
        }
        
        .grand-total {
            background-color: #FFA500 !important;
            font-weight: bold;
            font-size: 13px;
        }
        
        .grand-total td {
            color: #000;
        }
        
        .section-header {
            background-color: #f0f0f0;
            font-weight: 600;
            color: #000;
        }
        
        .bonus-row {
            background-color: #FFFF99 !important;
        }
        
        .net-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        
        .net-row td {
            color: #dc2626 !important;
            font-weight: bold;
        }
        
        .total-row {
            background-color: #FFE5B4;
            font-weight: bold;
            font-size: 12px;
            border-top: 2px solid #000 !important;
        }
        
        .total-row td {
            color: #dc2626 !important;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #ddd;
            padding-top: 15px;
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
        
        .back-btn {
            background: linear-gradient(135deg, #9E9E9E 0%, #757575 100%);
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(158, 158, 158, 0.4);
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
            
            .header {
                margin-bottom: 8px;
                padding-bottom: 5px;
            }
            
            .header h1 {
                font-size: 16px;
                margin-bottom: 3px;
            }
            
            .header p {
                font-size: 9px;
                margin: 1px 0;
            }
            
            .header img {
                max-height: 40px !important;
                margin-bottom: 3px !important;
            }
            
            .header h2 {
                font-size: 14px !important;
                margin: 3px 0 !important;
            }
            
            .header > div {
                margin-bottom: 5px !important;
            }
            
            .header > div > div {
                margin-bottom: 5px !important;
            }
            
            .period-info {
                padding: 8px;
                margin-bottom: 8px;
            }
            
            .period-info td {
                padding: 2px 4px;
                font-size: 9px;
            }
            
            .summary-table {
                margin-bottom: 10px;
                font-size: 8px;
            }
            
            .summary-table th,
            .summary-table td {
                padding: 4px 6px;
            }
            
            .summary-table th {
                padding: 5px;
                font-size: 8px;
            }
            
            h3 {
                font-size: 11px;
                margin-bottom: 6px;
                padding-bottom: 3px;
            }
            
            .footer {
                margin-top: 10px;
                padding-top: 5px;
                font-size: 7px;
            }
            
            .footer p {
                margin: 2px 0 !important;
                line-height: 1.2;
            }
            
            /* Resumo financeiro compacto na impressão */
            div[style*="linear-gradient"] {
                padding: 6px !important;
                margin-bottom: 6px !important;
                box-shadow: none !important;
                border-radius: 4px !important;
            }
            
            div[style*="linear-gradient"] h3 {
                font-size: 10px !important;
                margin: 0 0 4px 0 !important;
            }
            
            div[style*="linear-gradient"] > div {
                gap: 6px !important;
            }
            
            div[style*="linear-gradient"] > div > div {
                padding: 0 !important;
            }
            
            div[style*="linear-gradient"] p {
                font-size: 8px !important;
                margin: 1px 0 !important;
            }
            
            div[style*="linear-gradient"] p[style*="font-size: 20px"] {
                font-size: 12px !important;
            }
            
            /* Prevenir quebras de página */
            @page {
                size: A4;
                margin: 10mm;
            }
            
            .header,
            .period-info,
            .summary-table {
                page-break-inside: avoid;
            }
            
            .summary-table tbody tr {
                page-break-inside: avoid;
            }
            
            /* Reduzir espaçadores vazios */
            .summary-table tr[style*="height: 8px"],
            .summary-table tr[style*="height: 12px"] {
                display: none;
            }
            
            /* Ajustar espaçamentos para caber em 1 página */
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
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
        <button class="action-btn print-btn" onclick="window.print()">
            <i class="fas fa-print"></i>
            <span data-i18n="print">Imprimir</span>
        </button>
    </div>
    
    <div class="container">
        {{-- Header com Logo e Informações da Empresa --}}
        <div class="header">
            @php
                $logoPath = \App\Models\Setting::get('company_logo');
                $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
                $companyAddress = \App\Models\Setting::get('company_address', '');
                $companyPhone = \App\Models\Setting::get('company_phone', '');
                $companyEmail = \App\Models\Setting::get('company_email', '');
                $companyWebsite = \App\Models\Setting::get('company_website', '');
                $companyTaxId = \App\Models\Setting::get('company_tax_id', '');
            @endphp
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="margin-bottom: 15px;">
                    @if($logoPath)
                        <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $companyName }} Logo" style="max-height: 100px; max-width: 250px;">
                    @else
                        <img src="{{ asset('img/logo.png') }}" alt="Logo" style="max-height: 100px; max-width: 250px;">
                    @endif
                </div>
                <div>
                    <h2 style="margin: 8px 0; padding: 0; font-size: 20px; font-weight: bold; color: #2563eb;">{{ $companyName }}</h2>
                    @if($companyAddress)
                        <p style="margin: 4px 0; font-size: 11px; color: #666;">{{ $companyAddress }}</p>
                    @endif
                    @if($companyPhone || $companyEmail)
                        <p style="margin: 4px 0; font-size: 11px; color: #666;">
                            @if($companyPhone)Tel: {{ $companyPhone }}@endif
                            @if($companyPhone && $companyEmail) | @endif
                            @if($companyEmail)Email: {{ $companyEmail }}@endif
                        </p>
                    @endif
                    @if($companyTaxId || $companyWebsite)
                        <p style="margin: 4px 0; font-size: 11px; color: #666;">
                            @if($companyTaxId)NIF: {{ $companyTaxId }}@endif
                            @if($companyTaxId && $companyWebsite) | @endif
                            @if($companyWebsite){{ $companyWebsite }}@endif
                        </p>
                    @endif
                </div>
            </div>
            <div style="border-top: 2px solid #2563eb; padding-top: 15px; margin-bottom: 20px;">
                <h1 style="margin: 0; font-size: 22px; color: #2563eb; font-weight: bold;" data-i18n="payroll_report">RELATÓRIO DE FOLHA DE PAGAMENTO</h1>
                <p style="margin: 5px 0; font-size: 14px; color: #333;"><strong>{{ $batchName }}</strong></p>
                <p style="margin: 3px 0; font-size: 12px; color: #666;"><span data-i18n="period">Período</span>: {{ $periodName }} | {{ $batchDate }}</p>
            </div>
        </div>
        
        {{-- Informações do Período --}}
        <div class="period-info" style="background: #f3f4f6; padding: 15px; margin-bottom: 25px; border-left: 4px solid #2563eb; border-radius: 4px;">
            <table style="width: 100%;">
                <tr>
                    <td width="50%" style="padding: 5px;"><strong style="color: #2563eb;" data-i18n="department">Departamento</strong>: {{ $departmentName }}</td>
                    <td width="50%" style="padding: 5px;"><strong style="color: #2563eb;" data-i18n="total_employees">Total Funcionários</strong>: {{ $totalEmployees }}</td>
                </tr>
                <tr>
                    <td style="padding: 5px;"><strong style="color: #2563eb;" data-i18n="created_by">Criado por</strong>: {{ $creatorName }}</td>
                    <td style="padding: 5px;"><strong style="color: #2563eb;" data-i18n="generation_date">Data de Geração</strong>: {{ $generatedAt }}</td>
                </tr>
            </table>
        </div>
        
        {{-- Seção de Resumo com Destaque --}}
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h3 style="margin: 0 0 10px 0; font-size: 18px; font-weight: bold; text-align: center;">
                📊 <span data-i18n="financial_summary">RESUMO FINANCEIRO DO PERÍODO</span>
            </h3>
            <div style="display: flex; justify-content: space-around; text-align: center;">
                <div>
                    <p style="margin: 5px 0; font-size: 12px; opacity: 0.9;" data-i18n="total_gross">Total Bruto</p>
                    <p style="margin: 0; font-size: 20px; font-weight: bold;">{{ number_format($totals['grand_total'], 2, ',', '.') }} AOA</p>
                </div>
                <div>
                    <p style="margin: 5px 0; font-size: 12px; opacity: 0.9;" data-i18n="total_deductions">Deduções</p>
                    <p style="margin: 0; font-size: 20px; font-weight: bold;">{{ number_format($totals['total_deductions'], 2, ',', '.') }} AOA</p>
                </div>
                <div>
                    <p style="margin: 5px 0; font-size: 12px; opacity: 0.9;" data-i18n="total_net">Total Líquido</p>
                    <p style="margin: 0; font-size: 20px; font-weight: bold;">{{ number_format($totals['net_total'], 2, ',', '.') }} AOA</p>
                </div>
            </div>
        </div>
        
        {{-- Summary Table --}}
        <h3 style="margin-bottom: 15px; color: #2563eb; font-size: 16px; border-bottom: 2px solid #2563eb; padding-bottom: 8px;">
            📋 <span data-i18n="complete_breakdown">DETALHAMENTO COMPLETO DA FOLHA DE PAGAMENTO</span>
        </h3>
        <table class="summary-table">
            <thead>
                <tr>
                    <th colspan="2" data-i18n="description">DESCRIÇÃO</th>
                    <th style="width: 100px; text-align: center;" data-i18n="code">CÓDIGO</th>
                    <th style="width: 150px; text-align: right;" data-i18n="value_aoa">VALOR (AOA)</th>
                </tr>
            </thead>
            <tbody>
                {{-- Grand Total --}}
                <tr class="grand-total">
                    <td colspan="3"><strong data-i18n="grand_total">GRAND TOTAL</strong></td>
                    <td class="amount">{{ number_format($totals['grand_total'], 2, ',', '.') }}</td>
                </tr>
                
                <tr><td colspan="4" style="height: 8px; border: none; background: white;"></td></tr>
                
                {{-- Earnings Breakdown --}}
                <tr>
                    <td colspan="2" class="section-header" data-i18n="basic_salary">SALÁRIO BASE</td>
                    <td class="code">BS</td>
                    <td class="amount">{{ number_format($totals['earnings']['basic_salary'], 2, ',', '.') }}</td>
                </tr>
                
                <tr>
                    <td colspan="2" class="section-header" data-i18n="transport">TRANSPORTE</td>
                    <td class="code">TRNPT</td>
                    <td class="amount">{{ number_format($totals['earnings']['transport'], 2, ',', '.') }}</td>
                </tr>
                
                <tr>
                    <td colspan="2" class="section-header" data-i18n="over_time">HORAS EXTRAS</td>
                    <td class="code">OT</td>
                    <td class="amount">{{ number_format($totals['earnings']['overtime'], 2, ',', '.') }}</td>
                </tr>
                
                <tr>
                    <td colspan="2" class="section-header" data-i18n="vacation_pay">SUBSÍDIO DE FÉRIAS</td>
                    <td class="code">VP</td>
                    <td class="amount">{{ number_format($totals['earnings']['vacation_pay'], 2, ',', '.') }}</td>
                </tr>
                
                <tr>
                    <td colspan="2" class="section-header" data-i18n="food_allow">SUBSÍDIO DE ALIMENTAÇÃO</td>
                    <td class="code">FA</td>
                    <td class="amount">{{ number_format($totals['earnings']['food_allow'], 2, ',', '.') }}</td>
                </tr>
                
                <tr>
                    <td colspan="2" class="section-header" data-i18n="christmas_offer">SUBSÍDIO DE NATAL</td>
                    <td class="code">CO</td>
                    <td class="amount">{{ number_format($totals['earnings']['christmas_offer'], 2, ',', '.') }}</td>
                </tr>
                
                <tr class="bonus-row">
                    <td colspan="2" class="section-header" data-i18n="bonus">BÓNUS</td>
                    <td class="code">BNS</td>
                    <td class="amount">{{ number_format($totals['earnings']['bonus'], 2, ',', '.') }}</td>
                </tr>
                
                <tr class="net-row">
                    <td colspan="3"><strong data-i18n="net">LÍQUIDO</strong></td>
                    <td class="amount">{{ number_format($totals['net_before_deductions'], 2, ',', '.') }}</td>
                </tr>
                
                <tr><td colspan="4" style="height: 12px; border: none; background: white;"></td></tr>
                
                {{-- Deductions Breakdown --}}
                <tr>
                    <td colspan="2" class="section-header" data-i18n="social_security_3">SEGURANÇA SOCIAL 3%</td>
                    <td class="code">INSS 3%</td>
                    <td class="amount">{{ number_format($totals['deductions']['inss_3_percent'], 2, ',', '.') }}</td>
                </tr>
                
                <tr>
                    <td colspan="2" class="section-header" data-i18n="irt">IRT</td>
                    <td class="code" data-i18n="irt">IRT</td>
                    <td class="amount">{{ number_format($totals['deductions']['irt'], 2, ',', '.') }}</td>
                </tr>
                
                <tr>
                    <td colspan="2" class="section-header" data-i18n="staff_advance">ADIANTAMENTO</td>
                    <td class="code">Staff Adv</td>
                    <td class="amount">{{ number_format($totals['deductions']['staff_advance'], 2, ',', '.') }}</td>
                </tr>
                
                <tr>
                    <td colspan="2" class="section-header" data-i18n="absent">FALTAS</td>
                    <td class="code">Absent</td>
                    <td class="amount">{{ number_format($totals['deductions']['absent'], 2, ',', '.') }}</td>
                </tr>
                
                @if($totals['deductions']['union_fund'] > 0)
                <tr>
                    <td colspan="2" class="section-header">UNION FUND DEDUCTION</td>
                    <td class="code">ded</td>
                    <td class="amount">{{ number_format($totals['deductions']['union_fund'], 2, ',', '.') }}</td>
                </tr>
                @endif
                
                @if($totals['deductions']['union_deduction'] > 0)
                <tr>
                    <td colspan="2" class="section-header">Union Deduction</td>
                    <td class="code">union</td>
                    <td class="amount">{{ number_format($totals['deductions']['union_deduction'], 2, ',', '.') }}</td>
                </tr>
                @endif
                
                <tr>
                    <td colspan="2" class="section-header" data-i18n="other_deduction">OUTRAS DEDUÇÕES</td>
                    <td class="code">DED</td>
                    <td class="amount">{{ number_format($totals['deductions']['other_deduction'], 2, ',', '.') }}</td>
                </tr>
                
                @if($totals['deductions']['food_allow_deduction'] > 0)
                <tr>
                    <td colspan="2" class="section-header">FOOD ALLOW</td>
                    <td class="code">FA</td>
                    <td class="amount">{{ number_format($totals['deductions']['food_allow_deduction'], 2, ',', '.') }}</td>
                </tr>
                @endif
                
                <tr class="total-row">
                    <td colspan="3" style="text-align: right;"><strong><span data-i18n="total_deductions">Deduções</span>:</strong></td>
                    <td class="amount">{{ number_format($totals['total_deductions'], 2, ',', '.') }}</td>
                </tr>
                
                <tr><td colspan="4" style="height: 8px; border: none; background: white;"></td></tr>
                
                {{-- Final Total --}}
                <tr class="grand-total" style="background-color: #FFE5B4 !important;">
                    <td colspan="3"><strong data-i18n="total">TOTAL</strong></td>
                    <td class="amount">{{ number_format($totals['net_total'], 2, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
        
        {{-- Footer Profissional --}}
        <div class="footer" style="margin-top: 40px; border-top: 2px solid #e5e7eb; padding-top: 15px; text-align: center;">
            @php
                $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
            @endphp
            <p style="margin: 5px 0; font-size: 11px; color: #374151;">
                <strong>{{ $companyName }}</strong> &copy; {{ date('Y') }} - <span data-i18n="all_rights_reserved">Todos os direitos reservados</span>
            </p>
            <p style="margin: 5px 0; font-size: 10px; color: #6b7280;">
                <span data-i18n="report_generated">Relatório gerado por</span> ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ $generatedAt }}
            </p>
            <p style="margin: 5px 0; font-size: 9px; color: #9ca3af; font-style: italic;" data-i18n="confidential">
                Este documento é confidencial e destinado exclusivamente ao Departamento de Recursos Humanos
            </p>
        </div>
    </div>
    
    <script>
        // Traduções
        const translations = {
            pt: {
                'back': 'Voltar',
                'print': 'Imprimir',
                'payroll_report': 'RELATÓRIO DE FOLHA DE PAGAMENTO',
                'period': 'Período',
                'department': 'Departamento',
                'total_employees': 'Total Funcionários',
                'created_by': 'Criado por',
                'generation_date': 'Data de Geração',
                'financial_summary': 'RESUMO FINANCEIRO DO PERÍODO',
                'total_gross': 'Total Bruto',
                'total_deductions': 'Deduções',
                'total_net': 'Total Líquido',
                'complete_breakdown': 'DETALHAMENTO COMPLETO DA FOLHA DE PAGAMENTO',
                'description': 'DESCRIÇÃO',
                'code': 'CÓDIGO',
                'value_aoa': 'VALOR (AOA)',
                'grand_total': 'GRAND TOTAL',
                'basic_salary': 'SALÁRIO BASE',
                'transport': 'TRANSPORTE',
                'over_time': 'HORAS EXTRAS',
                'vacation_pay': 'SUBSÍDIO DE FÉRIAS',
                'food_allow': 'SUBSÍDIO DE ALIMENTAÇÃO',
                'christmas_offer': 'SUBSÍDIO DE NATAL',
                'bonus': 'BÓNUS',
                'net': 'LÍQUIDO',
                'social_security_3': 'SEGURANÇA SOCIAL 3%',
                'irt': 'IRT',
                'staff_advance': 'ADIANTAMENTO',
                'absent': 'FALTAS',
                'other_deduction': 'OUTRAS DEDUÇÕES',
                'total': 'TOTAL',
                'all_rights_reserved': 'Todos os direitos reservados',
                'report_generated': 'Relatório gerado por',
                'confidential': 'Este documento é confidencial e destinado exclusivamente ao Departamento de Recursos Humanos'
            },
            en: {
                'back': 'Back',
                'print': 'Print',
                'payroll_report': 'PAYROLL REPORT',
                'period': 'Period',
                'department': 'Department',
                'total_employees': 'Total Employees',
                'created_by': 'Created by',
                'generation_date': 'Generation Date',
                'financial_summary': 'PERIOD FINANCIAL SUMMARY',
                'total_gross': 'Gross Total',
                'total_deductions': 'Deductions',
                'total_net': 'Net Total',
                'complete_breakdown': 'COMPLETE PAYROLL BREAKDOWN',
                'description': 'DESCRIPTION',
                'code': 'CODE',
                'value_aoa': 'VALUE (AOA)',
                'grand_total': 'GRAND TOTAL',
                'basic_salary': 'BASIC SALARY',
                'transport': 'TRANSPORT',
                'over_time': 'OVER TIME',
                'vacation_pay': 'VACATION PAY',
                'food_allow': 'FOOD ALLOW',
                'christmas_offer': 'CHRISTMAS OFFER',
                'bonus': 'BONUS',
                'net': 'NET',
                'social_security_3': 'SOCIAL SECURITY 3%',
                'irt': 'IRT',
                'staff_advance': 'STAFF ADVANCE',
                'absent': 'ABSENT',
                'other_deduction': 'OTHER DEDUCTION',
                'total': 'TOTAL',
                'all_rights_reserved': 'All rights reserved',
                'report_generated': 'Report generated by',
                'confidential': 'This document is confidential and intended exclusively for the Human Resources Department'
            }
        };
        
        // Carregar idioma salvo ou usar português como padrão
        let currentLang = localStorage.getItem('reportLang') || 'pt';
        
        function setLanguage(lang) {
            currentLang = lang;
            localStorage.setItem('reportLang', lang);
            
            // Atualizar todos os elementos com data-i18n
            document.querySelectorAll('[data-i18n]').forEach(element => {
                const key = element.getAttribute('data-i18n');
                if (translations[lang][key]) {
                    element.textContent = translations[lang][key];
                }
            });
            
            // Atualizar botões de idioma
            document.getElementById('langPT').classList.toggle('active', lang === 'pt');
            document.getElementById('langEN').classList.toggle('active', lang === 'en');
        }
        
        // Aplicar idioma ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            setLanguage(currentLang);
        });
        
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
