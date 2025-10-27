<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.purchase_orders_list') }}</title>
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
        .no-data {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .text-right {
            text-align: right;
        }
        .alert-info {
            background-color: #cce5ff;
            color: #004085;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-weight: bold;
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
            <div class="document-title">{{ __('messages.purchase_orders_list') }}</div>
        </div>
    </div>

    <div class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.generated_at') }}:</th>
                <td>{{ $generatedAt }}</td>
                <th>{{ __('messages.total_orders') }}:</th>
                <td>{{ count($orders) }}</td>
            </tr>
            @if($filters['status'] || $filters['supplier'] || $filters['search'])
            <tr>
                <th>{{ __('messages.filters_applied') }}:</th>
                <td colspan="3">
                    @if($filters['status'])
                        <strong>{{ __('messages.status') }}:</strong> {{ __('messages.status_'.$filters['status']) }}
                    @endif
                    @if($filters['supplier'])
                        @if($filters['status']) | @endif
                        <strong>{{ __('messages.supplier') }}:</strong> {{ $filters['supplier'] }}
                    @endif
                    @if($filters['search'])
                        @if($filters['status'] || $filters['supplier']) | @endif
                        <strong>{{ __('messages.search_term') }}:</strong> {{ $filters['search'] }}
                    @endif
                </td>
            </tr>
            @endif
        </table>
    </div>

    @if(count($orders) > 0)
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 15%;">{{ __('messages.order_number') }}</th>
                    <th style="width: 20%;">{{ __('messages.supplier') }}</th>
                    <th style="width: 12%;">{{ __('messages.order_date') }}</th>
                    <th style="width: 12%;">{{ __('messages.expected_delivery') }}</th>
                    <th style="width: 15%;">{{ __('messages.status') }}</th>
                    <th style="width: 15%;" class="text-right">{{ __('messages.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $index => $order)
                    @php
                        $isOverdue = $order->expected_delivery_date && 
                            in_array($order->status, ['ordered', 'approved', 'partially_received']) && 
                            strtotime($order->expected_delivery_date) < strtotime('now');
                            
                        $isApproaching = !$isOverdue && $order->expected_delivery_date && 
                            strtotime($order->expected_delivery_date) <= strtotime('+15 days') && 
                            strtotime($order->expected_delivery_date) >= strtotime('now');
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->supplier ? $order->supplier->name : '-' }}</td>
                        <td>{{ $order->order_date ? date('d/m/Y', strtotime($order->order_date)) : '-' }}</td>
                        <td>
                            {{ $order->expected_delivery_date ? date('d/m/Y', strtotime($order->expected_delivery_date)) : '-' }}
                            @if($isOverdue)
                                <span style="color: #721c24; font-weight: bold;"> (!)</span>
                            @elseif($isApproaching)
                                <span style="color: #856404; font-weight: bold;"> (*)</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ $order->status }}">
                                {{ __('messages.status_'.$order->status) }}
                            </span>
                        </td>
                        <td class="text-right">{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($orders->contains(function($order) {
            return $order->expected_delivery_date && 
                in_array($order->status, ['ordered', 'approved', 'partially_received']) && 
                strtotime($order->expected_delivery_date) < strtotime('now');
        }))
            <div class="alert-info">
                <span style="color: #721c24; font-weight: bold;">(!)</span> {{ __('messages.overdue_orders_legend') }}
            </div>
        @endif

        @if($orders->contains(function($order) {
            $isOverdue = $order->expected_delivery_date && 
                in_array($order->status, ['ordered', 'approved', 'partially_received']) && 
                strtotime($order->expected_delivery_date) < strtotime('now');
                
            return !$isOverdue && $order->expected_delivery_date && 
                strtotime($order->expected_delivery_date) <= strtotime('+15 days') && 
                strtotime($order->expected_delivery_date) >= strtotime('now');
        }))
            <div class="alert-info">
                <span style="color: #856404; font-weight: bold;">(*)</span> {{ __('messages.approaching_delivery_legend') }}
            </div>
        @endif
    @else
        <div class="no-data">
            {{ __('messages.no_purchase_orders_found') }}
        </div>
    @endif

    <div style="margin-top: 20px;" class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.notes') }}:</th>
                <td>{{ __('messages.document_for_internal_use') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer" style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; font-size: 10px; color: #6b7280;">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
        <p>{{ __('messages.purchase_orders_list_generated') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
