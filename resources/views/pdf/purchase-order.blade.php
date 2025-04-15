<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $order->order_number }}</title>
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
        .status-draft {
            background-color: #f3f4f6;
            color: #4b5563;
        }
        .status-pending_approval {
            background-color: #fff4bd;
            color: #856404;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-ordered {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-partially_received {
            background-color: #e0cfef;
            color: #5a3b76;
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
                <h2 style="margin: 0; padding: 0; font-size: 16px;">{{ $companyName }}</h2>
                <p style="margin: 2px 0; font-size: 9px;">{{ $companyAddress }}</p>
                <p style="margin: 2px 0; font-size: 9px;">Tel: {{ $companyPhone }} | Email: {{ $companyEmail }}</p>
                <p style="margin: 2px 0; font-size: 9px;">CNPJ: {{ $companyTaxId }} | {{ $companyWebsite }}</p>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <div class="document-title">{{ __('messages.purchase_order') }}</div>
            <div>{{ __('messages.order_number') }}: {{ $order->order_number }}</div>
        </div>
    </div>
    
    @php
        $isOverdue = false;
        $isApproaching = false;
        
        if ($order->expected_delivery_date) {
            $isOverdue = $order->expected_delivery_date && 
                in_array($order->status, ['ordered', 'approved', 'partially_received']) && 
                strtotime($order->expected_delivery_date) < strtotime('now');
                
            $isApproaching = !$isOverdue && 
                strtotime($order->expected_delivery_date) <= strtotime('+15 days') && 
                strtotime($order->expected_delivery_date) >= strtotime('now');
        }
    @endphp

    <div class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.order_number') }}:</th>
                <td>{{ $order->order_number }}</td>
                <th>{{ __('messages.status') }}:</th>
                <td>
                    <span class="status-badge status-{{ $order->status }}">
                        {{ __('messages.status_'.$order->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>{{ __('messages.supplier') }}:</th>
                <td>{{ $order->supplier->name }}</td>
                <th>{{ __('messages.created_by') }}:</th>
                <td>{{ $order->createdBy ? $order->createdBy->name : '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.order_date') }}:</th>
                <td>{{ date('d/m/Y', strtotime($order->order_date)) }}</td>
                <th>{{ __('messages.expected_delivery') }}:</th>
                <td>
                    {{ $order->expected_delivery_date ? date('d/m/Y', strtotime($order->expected_delivery_date)) : '-' }}
                    @if($isOverdue)
                        <span style="color: #721c24; font-weight: bold;"> ({{ __('messages.overdue') }})</span>
                    @elseif($isApproaching)
                        <span style="color: #856404; font-weight: bold;"> ({{ __('messages.approaching') }})</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
    
    @if($isOverdue)
        <div class="alert-danger">
            {{ __('messages.order_is_overdue') }}
        </div>
    @elseif($isApproaching)
        <div class="alert-warning">
            {{ __('messages.delivery_within_15_days') }}
        </div>
    @endif

    @if($order->notes)
    <div class="remarks">
        <div class="remarks-title">{{ __('messages.notes') }}:</div>
        <p>{{ $order->notes }}</p>
    </div>
    @endif

    <h3>{{ __('messages.supplier_info') }}</h3>
    <div class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.supplier_name') }}:</th>
                <td>{{ $order->supplier->name }}</td>
                <th>{{ __('messages.contact_person') }}:</th>
                <td>{{ $order->supplier->contact_person ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.address') }}:</th>
                <td>{{ $order->supplier->address ?? '-' }}</td>
                <th>{{ __('messages.phone') }}:</th>
                <td>{{ $order->supplier->phone ?? '-' }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.email') }}:</th>
                <td>{{ $order->supplier->contact_email ?? '-' }}</td>
                <th>{{ __('messages.tax_id') }}:</th>
                <td>{{ $order->supplier->tax_id ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <h3>{{ __('messages.order_items') }}</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">{{ __('messages.product') }}</th>
                <th style="width: 30%;">{{ __('messages.description') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.quantity') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.unit_price') }}</th>
                <th style="width: 15%;" class="text-right">{{ __('messages.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($order->items as $index => $item)
                @php 
                    $lineTotal = $item->quantity * $item->unit_price;
                    $total += $lineTotal; 
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ optional($item->product)->name ?? 'Product #'.$item->product_id }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }} {{ $item->unit_of_measure ?? 'und' }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">{{ number_format($lineTotal, 2) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4"></td>
                <td class="text-right"><strong>{{ __('messages.total') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($total, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>{{ __('messages.purchase_order_generated') }} {{ date('d/m/Y H:i:s') }}</p>
        <p>{{ __('messages.document_for_internal_use') }}</p>
    </div>
</body>
</html>
