<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Salário - {{ $employee['name'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 7px;
            line-height: 1.1;
            color: #000;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        
        .page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 5mm 10mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .payslip {
            width: 190mm;
            height: 135mm;
            border: 1px solid #000;
            margin-bottom: 2mm;
            padding: 3mm;
            position: relative;
            margin-left: auto;
            margin-right: auto;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
            margin-bottom: 2mm;
        }
        
        .document-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 0.5mm;
        }
        
        .company-name {
            font-size: 8px;
            font-weight: bold;
        }
        
        .sr-number {
            position: absolute;
            top: 1.5mm;
            right: 3mm;
            font-size: 7px;
            font-weight: bold;
        }
        
        .employee-info {
            border: 1px solid #000;
            margin-bottom: 2mm;
        }
        
        .info-row {
            display: table;
            width: 100%;
            border-bottom: 1px solid #000;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-cell {
            display: table-cell;
            padding: 1mm;
            border-right: 1px solid #000;
            font-size: 7px;
            vertical-align: middle;
        }
        
        .info-cell:last-child {
            border-right: none;
        }
        
        .info-label {
            font-weight: bold;
            width: 15%;
        }
        
        .info-value {
            width: 35%;
        }
        
        .attendance-section {
            margin-bottom: 2mm;
        }
        
        .attendance-row {
            margin-bottom: 0.5mm;
            font-size: 7px;
        }
        
        .attendance-label {
            display: inline-block;
            width: 100px;
            font-weight: bold;
        }
        
        .attendance-input {
            border: 1px solid #000;
            display: inline-block;
            width: 25px;
            height: 10px;
            text-align: center;
            margin-right: 15px;
            vertical-align: top;
        }
        
        .main-content {
            border: 1px solid #000;
            margin-bottom: 2mm;
        }
        
        .section-headers {
            display: table;
            width: 100%;
            border-bottom: 1px solid #000;
            background-color: #f0f0f0;
        }
        
        .section-header {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 1mm;
            font-weight: bold;
            font-size: 8px;
            border-right: 1px solid #000;
        }
        
        .section-header:last-child {
            border-right: none;
        }
        
        .content-sections {
            display: table;
            width: 100%;
            min-height: 45mm;
        }
        
        .content-section {
            display: table-cell;
            width: 50%;
            padding: 1.5mm;
            border-right: 1px solid #000;
            vertical-align: top;
        }
        
        .content-section:last-child {
            border-right: none;
        }
        
        .line-item {
            display: table;
            width: 100%;
            margin-bottom: 0.5mm;
            font-size: 6px;
        }
        
        .item-description {
            display: table-cell;
            width: 70%;
            padding-right: 1mm;
        }
        
        .item-amount {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        
        .total-line {
            border-top: 1px solid #000;
            font-weight: bold;
            padding-top: 0.5mm;
            margin-top: 1mm;
        }
        
        .net-salary {
            border: 1px solid #000;
            text-align: center;
            padding: 1.5mm;
            margin: 2mm 0;
            font-size: 8px;
            font-weight: bold;
        }
        
        .payment-info {
            margin-bottom: 2mm;
        }
        
        .payment-method {
            font-size: 7px;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .bank-section {
            border: 1px solid #000;
            padding: 1mm;
            margin-bottom: 2mm;
        }
        
        .bank-row {
            margin-bottom: 0.5mm;
            font-size: 7px;
        }
        
        .bank-label {
            display: inline-block;
            width: 70px;
            font-weight: bold;
        }
        
        .signature-section {
            text-align: center;
            margin-top: 5mm;
        }
        
        .signature-line {
            border-top: 1px solid #000;
            width: 50%;
            margin: 0 auto;
            padding-top: 0.5mm;
            font-size: 7px;
        }
        
        .cut-line {
            text-align: center;
            font-size: 6px;
            margin: 2mm 0;
            border-top: 1px dashed #000;
            padding-top: 1mm;
            font-weight: bold;
            width: 190mm;
            margin-left: auto;
            margin-right: auto;
        }
        
        @media print {
            body { margin: 0; padding: 0; }
            .page { 
                margin: 0 auto; 
                padding: 5mm 10mm;
                width: 210mm;
                height: 297mm;
            }
            .payslip { 
                page-break-inside: avoid;
                width: 190mm;
                margin-left: auto;
                margin-right: auto;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- PRIMEIRA VIA -->
        <div class="payslip">
            <div class="sr-number">Sr. No.: {{ $period['name'] ?? date('m') }}</div>
            
            <!-- Header -->
            <div class="header">
                <div class="document-title">RECIBO DE SALÁRIO</div>
                <div class="company-name">{{ $company['name'] }}</div>
            </div>
            
            <!-- Employee Information -->
            <div class="employee-info">
                <div class="info-row">
                    <div class="info-cell info-label">Nome :</div>
                    <div class="info-cell info-value">{{ strtoupper($employee['name']) }}</div>
                    <div class="info-cell info-label">ID :</div>
                    <div class="info-cell info-value">{{ $employee['id'] }}</div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">Mês :</div>
                    <div class="info-cell info-value">{{ $period['name'] ?? date('F Y') }}</div>
                    <div class="info-cell info-label">Data de referência :</div>
                    <div class="info-cell info-value">{{ $period['end_date'] ?? date('d/m/Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">Categoria :</div>
                    <div class="info-cell info-value">{{ $employee['position'] }}</div>
                    <div class="info-cell info-label">Período de referência :</div>
                    <div class="info-cell info-value">{{ $period['start_date'] ?? '01/'.date('m/Y') }} - {{ $period['end_date'] ?? date('d/m/Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">ID Emp #</div>
                    <div class="info-cell info-value">{{ $employee['id'] }}</div>
                    <div class="info-cell info-label"></div>
                    <div class="info-cell info-value"></div>
                </div>
            </div>
            
            <!-- Attendance -->
            <div class="attendance-section">
                <div class="attendance-row">
                    <span class="attendance-label">Dias trabalhados</span>
                    <span class="attendance-input">{{ $attendance_days ?? '31' }}</span>
                    <span class="attendance-label">Total de ausências</span>
                    <span class="attendance-input">{{ $absence_days ?? '0' }}</span>
                </div>
                <div class="attendance-row">
                    <span class="attendance-label">Horas extras</span>
                    <span class="attendance-input">{{ $overtime_hours ?? '0' }}</span>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <div class="section-headers">
                    <div class="section-header">Remuneração</div>
                    <div class="section-header">Desconto</div>
                </div>
                
                <div class="content-sections">
                    <!-- Earnings Section -->
                    <div class="content-section">
                        <!-- Base Salary -->
                        <div class="line-item">
                            <div class="item-description">Vencimento Base</div>
                            <div class="item-amount">{{ number_format($totals['base_salary'] ?? $totals['earnings'], 0, ',', '.') }}</div>
                        </div>
                        
                        <!-- Transport Allowance -->
                        <div class="line-item">
                            <div class="item-description">Subsídio Transporte</div>
                            <div class="item-amount">{{ number_format($totals['transport_allowance'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <!-- Food Allowance -->
                        <div class="line-item">
                            <div class="item-description">Subsídio De Férias</div>
                            <div class="item-amount">{{ number_format($totals['vacation_allowance'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <!-- Other allowances -->
                        <div class="line-item">
                            <div class="item-description">Subsídio De Férias</div>
                            <div class="item-amount">{{ number_format($totals['holiday_allowance'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Subsídio Alimentação</div>
                            <div class="item-amount">{{ number_format($totals['food_allowance'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Outros / Telephone / Mobile)</div>
                            <div class="item-amount">{{ number_format($totals['other_allowance'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Prémio</div>
                            <div class="item-amount">{{ number_format($totals['bonus'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Gratilat</div>
                            <div class="item-amount">{{ number_format($totals['gratuity'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item total-line">
                            <div class="item-description">Total Remunerações</div>
                            <div class="item-amount">{{ number_format($totals['earnings'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    
                    <!-- Deductions Section -->
                    <div class="content-section">
                        <!-- Tax -->
                        <div class="line-item">
                            <div class="item-description">IRT</div>
                            <div class="item-amount">{{ number_format($tax_irt ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <!-- Social Security -->
                        <div class="line-item">
                            <div class="item-description">Segurança social Tax 3%</div>
                            <div class="item-amount">{{ number_format($social_security ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <!-- Other deductions -->
                        <div class="line-item">
                            <div class="item-description">Faltas</div>
                            <div class="item-amount">{{ number_format($absence_deduction ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Adiantamento</div>
                            <div class="item-amount">{{ number_format($advance_deduction ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Subsídio Alimentação</div>
                            <div class="item-amount">{{ number_format($food_deduction ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Outras Deduções</div>
                            <div class="item-amount">{{ number_format($other_deductions ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Sindicato De Trabalhadores</div>
                            <div class="item-amount">{{ number_format($union_fee ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item total-line">
                            <div class="item-description">Total Descontos</div>
                            <div class="item-amount">{{ number_format($totals['deductions'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Net Salary -->
            <div class="net-salary">
                Vencimento líquido : {{ number_format($totals['net_salary'], 0, ',', '.') }}
            </div>
            
            <!-- Payment Information -->
            <div class="payment-info">
                <div class="payment-method">
                    Modo de pagamento (numerário / transferência bancária) : <strong>CASH</strong>
                </div>
                
                <div class="bank-section">
                    <div class="bank-row">
                        <span class="bank-label">Nome do banco :</span>
                        <span>{{ $bank_name ?? '' }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">Numero de conta :</span>
                        <span>{{ $bank_account ?? '' }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">DADOS BANCÁRIOS :</span>
                    </div>
                </div>
            </div>
            
            <!-- Signature -->
            <div class="signature-section">
                <div class="signature-line">
                    Assinatura do trabalhador
                </div>
            </div>
        </div>
        
        <!-- Cut Line -->
        <div class="cut-line">--- CORTE AQUI ---</div>
        
        <!-- SEGUNDA VIA - DUPLICA TODO O CONTEÚDO ACIMA -->
        <div class="payslip">
            <div class="sr-number">Sr. No.: {{ $period['name'] ?? date('m') }}</div>
            
            <!-- Header -->
            <div class="header">
                <div class="document-title">RECIBO DE SALÁRIO</div>
                <div class="company-name">{{ $company['name'] }}</div>
            </div>
            
            <!-- Employee Information -->
            <div class="employee-info">
                <div class="info-row">
                    <div class="info-cell info-label">Nome :</div>
                    <div class="info-cell info-value">{{ strtoupper($employee['name']) }}</div>
                    <div class="info-cell info-label">ID :</div>
                    <div class="info-cell info-value">{{ $employee['id'] }}</div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">Mês :</div>
                    <div class="info-cell info-value">{{ $period['name'] ?? date('F Y') }}</div>
                    <div class="info-cell info-label">Data de referência :</div>
                    <div class="info-cell info-value">{{ $period['end_date'] ?? date('d/m/Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">Categoria :</div>
                    <div class="info-cell info-value">{{ $employee['position'] }}</div>
                    <div class="info-cell info-label">Período de referência :</div>
                    <div class="info-cell info-value">{{ $period['start_date'] ?? '01/'.date('m/Y') }} - {{ $period['end_date'] ?? date('d/m/Y') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">ID Emp #</div>
                    <div class="info-cell info-value">{{ $employee['id'] }}</div>
                    <div class="info-cell info-label"></div>
                    <div class="info-cell info-value"></div>
                </div>
            </div>
            
            <!-- Attendance -->
            <div class="attendance-section">
                <div class="attendance-row">
                    <span class="attendance-label">Dias trabalhados</span>
                    <span class="attendance-input">{{ $attendance_days ?? '31' }}</span>
                    <span class="attendance-label">Total de ausências</span>
                    <span class="attendance-input">{{ $absence_days ?? '0' }}</span>
                </div>
                <div class="attendance-row">
                    <span class="attendance-label">Horas extras</span>
                    <span class="attendance-input">{{ $overtime_hours ?? '0' }}</span>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="main-content">
                <div class="section-headers">
                    <div class="section-header">Remuneração</div>
                    <div class="section-header">Desconto</div>
                </div>
                
                <div class="content-sections">
                    <!-- Earnings Section -->
                    <div class="content-section">
                        <!-- Base Salary -->
                        <div class="line-item">
                            <div class="item-description">Vencimento Base</div>
                            <div class="item-amount">{{ number_format($totals['base_salary'] ?? $totals['earnings'], 0, ',', '.') }}</div>
                        </div>
                        
                        <!-- Transport Allowance -->
                        <div class="line-item">
                            <div class="item-description">Subsídio Transporte</div>
                            <div class="item-amount">{{ number_format($totals['transport_allowance'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <!-- Food Allowance -->
                        <div class="line-item">
                            <div class="item-description">Subsídio De Férias</div>
                            <div class="item-amount">{{ number_format($totals['vacation_allowance'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <!-- Other allowances -->
                        <div class="line-item">
                            <div class="item-description">Subsídio De Férias</div>
                            <div class="item-amount">{{ number_format($totals['holiday_allowance'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Subsídio Alimentação</div>
                            <div class="item-amount">{{ number_format($totals['food_allowance'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Outros / Telephone / Mobile)</div>
                            <div class="item-amount">{{ number_format($totals['other_allowance'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Prémio</div>
                            <div class="item-amount">{{ number_format($totals['bonus'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Gratilat</div>
                            <div class="item-amount">{{ number_format($totals['gratuity'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item total-line">
                            <div class="item-description">Total Remunerações</div>
                            <div class="item-amount">{{ number_format($totals['earnings'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                    
                    <!-- Deductions Section -->
                    <div class="content-section">
                        <!-- Tax -->
                        <div class="line-item">
                            <div class="item-description">IRT</div>
                            <div class="item-amount">{{ number_format($tax_irt ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <!-- Social Security -->
                        <div class="line-item">
                            <div class="item-description">Segurança social Tax 3%</div>
                            <div class="item-amount">{{ number_format($social_security ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <!-- Other deductions -->
                        <div class="line-item">
                            <div class="item-description">Faltas</div>
                            <div class="item-amount">{{ number_format($absence_deduction ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Adiantamento</div>
                            <div class="item-amount">{{ number_format($advance_deduction ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Subsídio Alimentação</div>
                            <div class="item-amount">{{ number_format($food_deduction ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Outras Deduções</div>
                            <div class="item-amount">{{ number_format($other_deductions ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item">
                            <div class="item-description">Sindicato De Trabalhadores</div>
                            <div class="item-amount">{{ number_format($union_fee ?? 0, 0, ',', '.') }}</div>
                        </div>
                        
                        <div class="line-item total-line">
                            <div class="item-description">Total Descontos</div>
                            <div class="item-amount">{{ number_format($totals['deductions'], 0, ',', '.') }}</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Net Salary -->
            <div class="net-salary">
                Vencimento líquido : {{ number_format($totals['net_salary'], 0, ',', '.') }}
            </div>
            
            <!-- Payment Information -->
            <div class="payment-info">
                <div class="payment-method">
                    Modo de pagamento (numerário / transferência bancária) : <strong>CASH</strong>
                </div>
                
                <div class="bank-section">
                    <div class="bank-row">
                        <span class="bank-label">Nome do banco :</span>
                        <span>{{ $bank_name ?? '' }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">Numero de conta :</span>
                        <span>{{ $bank_account ?? '' }}</span>
                    </div>
                    <div class="bank-row">
                        <span class="bank-label">DADOS BANCÁRIOS :</span>
                    </div>
                </div>
            </div>
            
            <!-- Signature -->
            <div class="signature-section">
                <div class="signature-line">
                    Assinatura do trabalhador
                </div>
            </div>
        </div>
    </div>
</body>
</html>
