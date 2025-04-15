<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Receipt - {{ $receipt->receipt_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
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
            max-height: 70px;
            max-width: 220px;
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        .document-info {
            margin-bottom: 20px;
        }
        .document-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .document-info th {
            text-align: left;
            padding: 5px;
            width: 30%;
            background-color: #f5f5f5;
        }
        .document-info td {
            padding: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 11px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
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
        .remarks {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .remarks-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .page-break {
            page-break-after: always;
        }
        .text-right {
            text-align: right;
        }
        .alert-warning {
            background-color: #fff4bd;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-weight: bold;
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
                <h2 style="margin: 0; padding: 0; font-size: 16px;">{{ $companyName }}</h2>
                <p style="margin: 2px 0; font-size: 9px;">{{ $companyAddress }}</p>
                <p style="margin: 2px 0; font-size: 9px;">Tel: {{ $companyPhone }} | Email: {{ $companyEmail }}</p>
                <p style="margin: 2px 0; font-size: 9px;">CNPJ: {{ $companyTaxId }} | {{ $companyWebsite }}</p>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <div class="document-title">{{ __('messages.goods_receipt') }}</div>
            <div>{{ __('messages.receipt_number') }}: {{ $receipt->receipt_number }}</div>
        </div>
    </div>

    <div class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.receipt_number') }}:</th>
                <td>{{ $receipt->receipt_number }}</td>
                <th>{{ __('messages.status') }}:</th>
                <td>
                    <span class="status-badge status-{{ $receipt->status }}">
                        {{ __('messages.status_'.$receipt->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>{{ __('messages.supplier') }}:</th>
                <td>{{ $receipt->supplier ? $receipt->supplier->name : '-' }}</td>
                <th>{{ __('messages.received_by') }}:</th>
                <td>{{ $receipt->receiver ? $receipt->receiver->name : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.receipt_date') }}:</th>
                <td>{{ $receipt->receipt_date ? date('d/m/Y', strtotime($receipt->receipt_date)) : '-' }}</td>
                <th>{{ __('messages.location') }}:</th>
                <td>{{ $receipt->location ? $receipt->location->name : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.purchase_order') }}:</th>
                <td colspan="3">
                    {{ $receipt->purchaseOrder ? $receipt->purchaseOrder->order_number : __('messages.no_purchase_order') }}
                </td>
            </tr>
        </table>
    </div>

    @if($receipt->notes)
    <div class="remarks">
        <div class="remarks-title">{{ __('messages.notes') }}:</div>
        <p>{{ $receipt->notes }}</p>
    </div>
    @endif

    <h3>{{ __('messages.receipt_items') }}</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">{{ __('messages.product') }}</th>
                <th style="width: 25%;">{{ __('messages.description') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.expected_quantity') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.accepted_quantity') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.rejected_quantity') }}</th>
                <th style="width: 15%;" class="text-right">{{ __('messages.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($receipt->items as $index => $item)
                @php 
                    $lineTotal = $item->accepted_quantity * $item->unit_cost;
                    $total += $lineTotal; 
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ optional($item->product)->name ?? 'Product #'.$item->product_id }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->expected_quantity }}</td>
                    <td class="text-right">{{ $item->accepted_quantity }}</td>
                    <td class="text-right">{{ $item->rejected_quantity }}</td>
                    <td class="text-right">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5"></td>
                <td class="text-right"><strong>{{ __('messages.total') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($total, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>{{ __('messages.goods_receipt_generated') }} {{ date('d/m/Y H:i:s') }}</p>
        <p>{{ __('messages.document_for_internal_use') }}</p>
    </div>
</body>
</html>
