<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.goods_receipts_list') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }
        .logo {
            max-height: 60px;
            max-width: 200px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .filters-info {
            margin-bottom: 10px;
            font-size: 9px;
            background-color: #f9f9f9;
            padding: 5px;
            border-radius: 3px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        .items-table th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
            font-size: 9px;
        }
        .items-table td {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 8px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fff4bd;
            color: #856404;
        }
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            color: #666;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .page-break {
            page-break-after: always;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
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
        <div style="display: flex; align-items: flex-start;">
            <div style="margin-right: 20px;">
                <img src="{{ $logoFullPath }}" alt="{{ $companyName }} Logo" class="logo">
            </div>
            <div>
                <h2 style="margin: 0; padding: 0; font-size: 14px;">{{ $companyName }}</h2>
                <p style="margin: 2px 0; font-size: 9px;">{{ $companyAddress }}</p>
                <p style="margin: 2px 0; font-size: 9px;">Tel: {{ $companyPhone }} | Email: {{ $companyEmail }}</p>
                <p style="margin: 2px 0; font-size: 9px;">CNPJ: {{ $companyTaxId }} | {{ $companyWebsite }}</p>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <div class="document-title">{{ __('messages.goods_receipts_list') }}</div>
            <div>{{ __('messages.generated_at') }}: {{ $generatedAt }}</div>
        </div>
    </div>

    @if(!empty($filters))
    <div class="filters-info">
        <strong>{{ __('messages.filters_applied') }}:</strong>
        <ul style="margin: 5px 0; padding-left: 20px;">
            @if(!empty($filters['status']))
                <li>{{ __('messages.status') }}: {{ __('messages.status_' . $filters['status']) }}</li>
            @endif
            @if(!empty($filters['supplier']))
                <li>{{ __('messages.supplier') }}: {{ $filters['supplier'] }}</li>
            @endif
            @if(!empty($filters['location']))
                <li>{{ __('messages.location') }}: {{ $filters['location'] }}</li>
            @endif
            @if(!empty($filters['search']))
                <li>{{ __('messages.search') }}: {{ $filters['search'] }}</li>
            @endif
        </ul>
    </div>
    @endif

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 12%;">{{ __('messages.receipt_number') }}</th>
                <th style="width: 20%;">{{ __('messages.supplier') }}</th>
                <th style="width: 12%;">{{ __('messages.receipt_date') }}</th>
                <th style="width: 12%;">{{ __('messages.purchase_order') }}</th>
                <th style="width: 12%;">{{ __('messages.location') }}</th>
                <th style="width: 12%;">{{ __('messages.received_by') }}</th>
                <th style="width: 10%;">{{ __('messages.status') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receipts as $receipt)
                @php
                    $total = $receipt->items->sum(function($item) {
                        return $item->accepted_quantity * $item->unit_cost;
                    });
                @endphp
                <tr>
                    <td>{{ $receipt->receipt_number }}</td>
                    <td>{{ optional($receipt->supplier)->name }}</td>
                    <td>{{ $receipt->receipt_date ? date('d/m/Y', strtotime($receipt->receipt_date)) : '-' }}</td>
                    <td>{{ optional($receipt->purchaseOrder)->order_number }}</td>
                    <td>{{ optional($receipt->location)->name }}</td>
                    <td>{{ optional($receipt->receiver)->name }}</td>
                    <td>
                        <span class="status-badge status-{{ $receipt->status }}">
                            {{ __('messages.status_'.$receipt->status) }}
                        </span>
                    </td>
                    <td class="text-right">{{ number_format($total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">
                        {{ __('messages.no_goods_receipts_found') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>{{ __('messages.goods_receipts_list_generated') }} {{ date('d/m/Y H:i:s') }}</p>
        <p>{{ __('messages.document_for_internal_use') }}</p>
        <p>{{ __('messages.total_items') }}: {{ $receipts->count() }}</p>
    </div>
</body>
</html>
