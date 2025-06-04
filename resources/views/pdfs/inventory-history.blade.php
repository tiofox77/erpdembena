<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.inventory_transaction_history') }}</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            margin-bottom: 20px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white;
            padding: 15px;
            border-radius: 8px;
        }
        .logo {
            max-height: 70px;
            max-width: 220px;
        }
        .info-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .info-card h2 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
            color: #2563eb;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #64748b;
            font-size: 11px;
            display: block;
            margin-bottom: 3px;
        }
        .info-value {
            color: #0f172a;
        }
        .summary-boxes {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
        }
        .summary-box {
            flex: 1;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .summary-box.additions {
            background-color: #dcfce7;
            border: 1px solid #86efac;
        }
        .summary-box.removals {
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
        }
        .summary-box.net {
            background-color: #dbeafe;
            border: 1px solid #93c5fd;
        }
        .summary-label {
            font-size: 11px;
            font-weight: bold;
            color: #64748b;
            margin-bottom: 5px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
        }
        .summary-value.positive {
            color: #16a34a;
        }
        .summary-value.negative {
            color: #dc2626;
        }
        .summary-value.neutral {
            color: #2563eb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 10px;
            border: 1px solid #cbd5e1;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .tag {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .tag-adjustment-add { background-color: #dcfce7; color: #166534; }
        .tag-adjustment-remove { background-color: #fee2e2; color: #991b1b; }
        .tag-transfer { background-color: #dbeafe; color: #1e40af; }
        .tag-purchase { background-color: #fef3c7; color: #92400e; }
        .tag-sales { background-color: #fecdd3; color: #9f1239; }
        .tag-production { background-color: #e0e7ff; color: #4338ca; }
        .tag-other { background-color: #f3f4f6; color: #374151; }
        .positive { color: #16a34a; }
        .negative { color: #dc2626; }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #64748b;
            text-align: center;
        }
        .page-number {
            text-align: right;
            font-size: 9px;
            color: #64748b;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho Padrão do ERP -->
        <div class="header">
            @php
                $logoPath = \App\Models\Setting::get('company_logo');
                $logoFullPath = $logoPath ? public_path('storage/' . $logoPath) : public_path('img/logo.png');
                $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
                $companyAddress = \App\Models\Setting::get('company_address', '');
                $companyPhone = \App\Models\Setting::get('company_phone', '');
                $companyEmail = \App\Models\Setting::get('company_email', '');
                $companyWebsite = \App\Models\Setting::get('company_website', '');
                $companyTaxId = \App\Models\Setting::get('company_tax_id', '');
            @endphp
            
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="width: 30%;">
                    <img class="logo" src="{{ $logoFullPath }}" alt="Logo" style="max-height: 70px; max-width: 100%;">
                </div>
                <div style="width: 70%; text-align: right;">
                    <p style="margin: 2px 0; font-size: 14px; font-weight: bold;">{{ $companyName }}</p>
                    <p style="margin: 2px 0; font-size: 10px;">{{ $companyAddress }}</p>
                    <p style="margin: 2px 0; font-size: 10px;">Tel: {{ $companyPhone }} | Email: {{ $companyEmail }}</p>
                    <p style="margin: 2px 0; font-size: 10px;">CNPJ: {{ $companyTaxId }} | {{ $companyWebsite }}</p>
                </div>
            </div>
            
            <div style="margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 10px;">
                <div style="font-size: 20px; font-weight: bold; margin: 5px 0; color: white;">
                    {{ __('messages.inventory_transaction_history') }}
                </div>
                <div style="font-size: 11px; color: rgba(255,255,255,0.9);">
                    {{ __('messages.generated_by') }}: {{ $user->name }} | {{ __('messages.date') }}: {{ $currentDate }}
                </div>
            </div>
        </div>
        
        <!-- Informações do Item -->
        <div class="info-card">
            <h2><i class="fas fa-info-circle"></i> {{ __('messages.item_information') }}</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">{{ __('messages.product') }}</span>
                    <div class="info-value">{{ $inventoryItem->product->name }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('messages.sku') }}</span>
                    <div class="info-value">{{ $inventoryItem->product->sku }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('messages.category') }}</span>
                    <div class="info-value">{{ $inventoryItem->product->category->name ?? __('messages.not_categorized') }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('messages.location') }}</span>
                    <div class="info-value">{{ $inventoryItem->location->name }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('messages.current_stock') }}</span>
                    <div class="info-value">{{ number_format($inventoryItem->quantity, 2) }}</div>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('messages.unit_cost') }}</span>
                    <div class="info-value">$ {{ number_format($inventoryItem->unit_cost, 2) }}</div>
                </div>
            </div>
        </div>
        
        <!-- Resumo das Transações -->
        <div class="summary-boxes">
            <div class="summary-box additions">
                <div class="summary-label">{{ __('messages.total_additions') }}</div>
                <div class="summary-value positive">+{{ number_format($totalIn, 2) }}</div>
            </div>
            <div class="summary-box removals">
                <div class="summary-label">{{ __('messages.total_removals') }}</div>
                <div class="summary-value negative">{{ number_format($totalOut, 2) }}</div>
            </div>
            <div class="summary-box net">
                <div class="summary-label">{{ __('messages.net_change') }}</div>
                <div class="summary-value {{ $netChange > 0 ? 'positive' : ($netChange < 0 ? 'negative' : 'neutral') }}">
                    {{ $netChange > 0 ? '+' : '' }}{{ number_format($netChange, 2) }}
                </div>
            </div>
        </div>
        
        <!-- Tabela de Transações -->
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.date') }}</th>
                    <th>{{ __('messages.type') }}</th>
                    <th>{{ __('messages.quantity') }}</th>
                    <th>{{ __('messages.details') }}</th>
                    <th>{{ __('messages.user') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions as $transaction)
                <tr>
                    <td>
                        {{ $transaction->created_at->format('d/m/Y') }}<br>
                        <small>{{ $transaction->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        @if($transaction->transaction_type === 'adjustment')
                            @if($transaction->quantity > 0)
                                <span class="tag tag-adjustment-add">{{ __('messages.stock_added') }}</span>
                            @else
                                <span class="tag tag-adjustment-remove">{{ __('messages.stock_removed') }}</span>
                            @endif
                        @elseif($transaction->transaction_type === 'transfer')
                            <span class="tag tag-transfer">{{ __('messages.stock_transfer') }}</span>
                        @elseif(in_array($transaction->transaction_type, ['purchase_receipt', 'raw_production']))
                            <span class="tag tag-purchase">{{ __('messages.' . $transaction->transaction_type) }}</span>
                        @elseif(in_array($transaction->transaction_type, ['sales_issue']))
                            <span class="tag tag-sales">{{ __('messages.' . $transaction->transaction_type) }}</span>
                        @elseif(in_array($transaction->transaction_type, ['production', 'production_receipt', 'production_issue', 'production_order', 'daily_production', 'daily_production_fg']))
                            <span class="tag tag-production">{{ __('messages.' . $transaction->transaction_type) }}</span>
                        @else
                            <span class="tag tag-other">{{ $transaction->transaction_type }}</span>
                        @endif
                    </td>
                    <td class="{{ $transaction->quantity > 0 ? 'positive' : 'negative' }}">
                        {{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format($transaction->quantity, 2) }}
                    </td>
                    <td>
                        @if($transaction->source_location_id && $transaction->destination_location_id)
                            <strong>{{ $transaction->sourceLocation->name }}</strong> → <strong>{{ $transaction->destinationLocation->name }}</strong><br>
                        @endif
                        {{ $transaction->notes ?? '-' }}
                    </td>
                    <td>{{ $transaction->creator->name }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">
                        {{ __('messages.no_transaction_history') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        
        <!-- Rodapé -->
        <div class="footer">
            <div style="margin-bottom: 5px;">
                <strong>{{ __('messages.notes') }}:</strong> {{ __('messages.inventory_history_report_note') }}
            </div>
            <div style="border-top: 1px dashed #cbd5e1; padding-top: 5px; margin-top: 5px;">
                <p>{{ $companyName }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
                <p>{{ __('messages.report_generated_by') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ $currentDate }}</p>
                <p>{{ __('messages.confidential_document') }} - {{ __('messages.page') }} <span class="page-number">1</span></p>
            </div>
        </div>
    </div>
</body>
</html>
