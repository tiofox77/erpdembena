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
            margin-bottom: 10px;
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
        h3 {
            margin-top: 10px; 
            margin-bottom: 5px; 
            font-size: 14px; 
            font-weight: bold;
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
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 55%; vertical-align: top; padding-right: 10px;">
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
                        <div style="margin-right: 20px; flex-shrink: 0;">
                            <img src="{{ $logoFullPath }}" alt="{{ $companyName }} Logo" class="logo">
                        </div>
                        <div>
                            <h2 style="margin: 0; padding: 0; font-size: 16px;">{{ $companyName }}</h2>
                            <p style="margin: 2px 0; font-size: 9px;">{{ $companyAddress }}</p>
                            <p style="margin: 2px 0; font-size: 9px;">Tel: {{ $companyPhone }} | Email: {{ $companyEmail }}</p>
                            <p style="margin: 2px 0; font-size: 9px;">VAT/NIF: {{ $companyTaxId }} | {{ $companyWebsite }}</p>
                        </div>
                    </div>
                </td>
                <td style="width: 45%; vertical-align: top; padding-left: 10px; padding-top: 6%;">
                    <h3>{{ __('messages.supplier_info') }}</h3>
                    <div class="document-info" style="margin-top: 0;">
                        <table style="width: 100%;">
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px; width: 30%;">{{ __('messages.supplier_name') }}:</th>
                                <td style="font-size: 9px;">{{ $order->supplier->name }}</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px;">{{ __('messages.contact_person') }}:</th>
                                <td style="font-size: 9px;">{{ $order->supplier->contact_person ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px;">{{ __('messages.address') }}:</th>
                                <td style="font-size: 9px;">{{ $order->supplier->address ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px;">{{ __('messages.phone') }}:</th>
                                <td style="font-size: 9px;">{{ $order->supplier->phone ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px;">{{ __('messages.email') }}:</th>
                                <td style="font-size: 9px;">{{ $order->supplier->contact_email ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px;">{{ __('messages.tax_id') }}:</th>
                                <td style="font-size: 9px;">{{ $order->supplier->tax_id ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr style="border-top: 1px solid #ddd;">
                <td colspan="2" style="text-align: center; padding-top: 10px;">
                    <div class="document-title" style="margin: 0; font-size: 16px;">{{ __('messages.purchase_order') }}</div>
                    <div style="font-size: 11px;">{{ __('messages.order_number') }}: {{ $order->order_number }}</div>
                </td>
            </tr>
        </table>
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

    <div class="document-info" style="margin-top: 10px;">
        <h3>{{ __('messages.order_details') }}</h3>
        <table style="width: 100%; font-size: 9px; border-collapse: collapse;">
            <tr>
                <th style="text-align: left; padding-right: 5px; white-space: nowrap;">{{ __('messages.order_date') }}:</th>
                <td style="padding-right: 15px; white-space: nowrap;">{{ date('d/m/Y', strtotime($order->order_date)) }}</td>
                
                <th style="text-align: left; padding-right: 5px; white-space: nowrap;">{{ __('messages.expected_delivery') }}:</th>
                <td style="padding-right: 15px; white-space: nowrap;">
                    {{ $order->expected_delivery_date ? date('d/m/Y', strtotime($order->expected_delivery_date)) : '-' }}
                    @if($isOverdue)
                        <span style="color: #721c24; font-weight: bold;"> ({{ __('messages.overdue') }})</span>
                    @elseif($isApproaching)
                        <span style="color: #856404; font-weight: bold;"> ({{ __('messages.approaching') }})</span>
                    @endif
                </td>
                
                <th style="text-align: left; padding-right: 5px; white-space: nowrap;">{{ __('messages.status') }}:</th>
                <td style="white-space: nowrap;">
                    <span class="status-badge status-{{ $order->status }}" style="font-size: 9px; padding: 2px 4px;">
                        {{ __('messages.status_'.$order->status) }}
                    </span>
                </td>
            </tr>
        </table>
    </div>
    
    @if($isOverdue)
    @elseif($isApproaching)
    @endif

    @if($order->notes)
    <div class="remarks">
        <div class="remarks-title">{{ __('messages.notes') }}:</div>
        <p>{{ $order->notes }}</p>
    </div>
    @endif

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
        <p>{{ __('messages.document_not_an_invoice') }}</p>
    </div>
</body>
</html>
