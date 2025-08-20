<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibos de Salário - {{ str_pad($month, 2, '0', STR_PAD_LEFT) }}/{{ $year }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .receipt-container {
            width: 100%;
            margin-bottom: 30px;
            border: 2px solid #2563eb;
            border-radius: 8px;
            overflow: hidden;
            background: white;
        }
        
        .receipt-header {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .receipt-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            border-bottom: 2px solid white;
            padding-bottom: 10px;
        }
        
        .employee-info {
            font-size: 14px;
        }
        
        .receipt-body {
            padding: 20px;
        }
        
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
        }
        
        .info-group {
            flex: 1;
        }
        
        .info-group h4 {
            font-size: 12px;
            color: #64748b;
            margin: 0 0 8px 0;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .info-group p {
            margin: 0;
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
        }
        
        .salary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .salary-section {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
        }
        
        .salary-section h3 {
            background: #f1f5f9;
            color: #334155;
            margin: 0;
            padding: 12px 15px;
            font-size: 13px;
            font-weight: 600;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .earnings {
            background: #dcfce7;
        }
        
        .earnings h3 {
            background: #16a34a;
            color: white;
        }
        
        .deductions {
            background: #fef2f2;
        }
        
        .deductions h3 {
            background: #dc2626;
            color: white;
        }
        
        .salary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .salary-item:last-child {
            border-bottom: none;
            font-weight: 600;
            background: rgba(0, 0, 0, 0.05);
        }
        
        .salary-item span:first-child {
            color: #475569;
        }
        
        .salary-item span:last-child {
            font-weight: 500;
            color: #1e293b;
        }
        
        .net-salary {
            background: #dbeafe;
            border: 2px solid #3b82f6;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
        }
        
        .net-salary h3 {
            margin: 0 0 5px 0;
            color: #1d4ed8;
            font-size: 14px;
        }
        
        .net-salary .amount {
            font-size: 20px;
            font-weight: bold;
            color: #1e40af;
        }
        
        .payment-info {
            background: #f8fafc;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
        }
        
        .payment-info h4 {
            margin: 0 0 10px 0;
            color: #374151;
            font-size: 13px;
        }
        
        .payment-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .payment-item {
            display: flex;
            justify-content: space-between;
        }
        
        .payment-item span:first-child {
            color: #6b7280;
            font-size: 12px;
        }
        
        .payment-item span:last-child {
            font-weight: 500;
            color: #1f2937;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }
        
        .receipt-number {
            float: right;
            background: #fee2e2;
            color: #991b1b;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        
        @media print {
            .receipt-container {
                margin-bottom: 0;
            }
            
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    @foreach($receiptData as $index => $receipt)
        @if($index > 0)
            <div class="page-break"></div>
        @endif
        
        <div class="receipt-container">
            <!-- Cabeçalho -->
            <div class="receipt-header">
                <div class="company-name">{{ $receipt['companyName'] ?? 'DEMBENA INDÚSTRIA E COMÉRCIO LDA' }}</div>
                <div class="receipt-title">RECIBO DE VENCIMENTO</div>
                <div class="employee-info">{{ $receipt['employeeName'] ?? 'N/A' }}</div>
                <div class="receipt-number">{{ $receipt['receiptNumber'] ?? 'REC-' . now()->format('Y-m-d') . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT) }}</div>
            </div>
            
            <!-- Corpo do Recibo -->
            <div class="receipt-body">
                <!-- Informações Gerais -->
                <div class="info-section">
                    <div class="info-group">
                        <h4>Período de Referência</h4>
                        <p>{{ $receipt['referencePeriod'] ?? $receipt['month'] ?? 'N/A' }}</p>
                    </div>
                    <div class="info-group">
                        <h4>Categoria</h4>
                        <p>{{ $receipt['category'] ?? 'N/A' }}</p>
                    </div>
                    <div class="info-group">
                        <h4>Dias Trabalhados</h4>
                        <p>{{ $receipt['workedDays'] ?? 0 }}</p>
                    </div>
                    <div class="info-group">
                        <h4>Ausências</h4>
                        <p>{{ $receipt['absences'] ?? 0 }}</p>
                    </div>
                </div>
                
                <!-- Remunerações e Descontos -->
                <div class="salary-grid">
                    <!-- Remunerações -->
                    <div class="salary-section earnings">
                        <h3>💰 REMUNERAÇÕES</h3>
                        <div class="salary-item">
                            <span>Salário Base</span>
                            <span>{{ number_format($receipt['baseSalary'] ?? 0, 0, ',', '.') }} KZ</span>
                        </div>
                        @if(($receipt['transportSubsidy'] ?? 0) > 0)
                        <div class="salary-item">
                            <span>Subsídio de Transporte</span>
                            <span>{{ number_format($receipt['transportSubsidy'], 0, ',', '.') }} KZ</span>
                        </div>
                        @endif
                        @if(($receipt['foodSubsidy'] ?? 0) > 0)
                        <div class="salary-item">
                            <span>Subsídio de Alimentação</span>
                            <span>{{ number_format($receipt['foodSubsidy'], 0, ',', '.') }} KZ</span>
                        </div>
                        @endif
                        @if(($receipt['holidaySubsidy'] ?? 0) > 0)
                        <div class="salary-item">
                            <span>Subsídio de Férias</span>
                            <span>{{ number_format($receipt['holidaySubsidy'], 0, ',', '.') }} KZ</span>
                        </div>
                        @endif
                        @if(($receipt['bonus'] ?? 0) > 0)
                        <div class="salary-item">
                            <span>Prémios/Bónus</span>
                            <span>{{ number_format($receipt['bonus'], 0, ',', '.') }} KZ</span>
                        </div>
                        @endif
                        @if(($receipt['extraHours'] ?? 0) > 0)
                        <div class="salary-item">
                            <span>Horas Extras</span>
                            <span>{{ number_format($receipt['extraHours'], 0, ',', '.') }} KZ</span>
                        </div>
                        @endif
                        <div class="salary-item">
                            <span><strong>TOTAL REMUNERAÇÕES</strong></span>
                            <span><strong>{{ number_format($receipt['totalEarnings'] ?? 0, 0, ',', '.') }} KZ</strong></span>
                        </div>
                    </div>
                    
                    <!-- Descontos -->
                    <div class="salary-section deductions">
                        <h3>📉 DESCONTOS</h3>
                        @if(($receipt['irt'] ?? 0) > 0)
                        <div class="salary-item">
                            <span>IRT</span>
                            <span>{{ number_format($receipt['irt'], 0, ',', '.') }} KZ</span>
                        </div>
                        @endif
                        @if(($receipt['socialSecurity'] ?? 0) > 0)
                        <div class="salary-item">
                            <span>Segurança Social</span>
                            <span>{{ number_format($receipt['socialSecurity'], 0, ',', '.') }} KZ</span>
                        </div>
                        @endif
                        @if(($receipt['absenceDeduction'] ?? 0) > 0)
                        <div class="salary-item">
                            <span>Desconto por Faltas</span>
                            <span>{{ number_format($receipt['absenceDeduction'], 0, ',', '.') }} KZ</span>
                        </div>
                        @endif
                        @if(($receipt['advance'] ?? 0) > 0)
                        <div class="salary-item">
                            <span>Adiantamentos</span>
                            <span>{{ number_format($receipt['advance'], 0, ',', '.') }} KZ</span>
                        </div>
                        @endif
                        @if(($receipt['foodSubsidyDeduction'] ?? 0) > 0)
                        <div class="salary-item">
                            <span>Desconto Sub. Alimentação</span>
                            <span>{{ number_format($receipt['foodSubsidyDeduction'], 0, ',', '.') }} KZ</span>
                        </div>
                        @endif
                        @if(is_array($receipt['salaryDiscounts'] ?? null))
                            @foreach($receipt['salaryDiscounts'] as $discountType => $discountAmount)
                            <div class="salary-item">
                                <span>{{ ucfirst($discountType) }}</span>
                                <span>{{ number_format($discountAmount, 0, ',', '.') }} KZ</span>
                            </div>
                            @endforeach
                        @elseif(($receipt['otherDeductions'] ?? 0) > 0)
                        <div class="salary-item">
                            <span>Outros Descontos</span>
                            <span>{{ number_format($receipt['otherDeductions'], 0, ',', '.') }} KZ</span>
                        </div>
                        @endif
                        <div class="salary-item">
                            <span><strong>TOTAL DESCONTOS</strong></span>
                            <span><strong>{{ number_format($receipt['totalDeductions'] ?? 0, 0, ',', '.') }} KZ</strong></span>
                        </div>
                    </div>
                </div>
                
                <!-- Salário Líquido -->
                <div class="net-salary">
                    <h3>💳 SALÁRIO LÍQUIDO A RECEBER</h3>
                    <div class="amount">{{ number_format($receipt['netSalary'] ?? 0, 0, ',', '.') }} KZ</div>
                </div>
                
                <!-- Informações de Pagamento -->
                <div class="payment-info">
                    <h4>📋 INFORMAÇÕES DE PAGAMENTO</h4>
                    <div class="payment-grid">
                        <div class="payment-item">
                            <span>Banco:</span>
                            <span>{{ $receipt['bankName'] ?? 'N/A' }}</span>
                        </div>
                        <div class="payment-item">
                            <span>Conta:</span>
                            <span>{{ $receipt['accountNumber'] ?? 'N/A' }}</span>
                        </div>
                        <div class="payment-item">
                            <span>Método:</span>
                            <span>{{ $receipt['paymentMethod'] ?? 'N/A' }}</span>
                        </div>
                        <div class="payment-item">
                            <span>Data:</span>
                            <span>{{ now()->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Rodapé -->
                <div class="footer">
                    <p><strong>Este recibo foi gerado automaticamente pelo sistema ERP DEMBENA</strong></p>
                    <p>Gerado em: {{ $generatedAt }} | Total de recibos: {{ $totalReceipts }}</p>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>
