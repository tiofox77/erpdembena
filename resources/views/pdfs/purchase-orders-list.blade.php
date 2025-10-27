<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.purchase_orders_list') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            padding: 10px;
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
        .filters {
            background-color: #ffffff;
            padding: 10px 12px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
        }
        .filters h3 {
            margin: 0 0 8px 0;
            font-size: 11px;
            color: #1a1a1a;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e2e8f0;
        }
        .filters-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 20px;
        }
        .filter-item {
            display: flex;
            align-items: center;
            margin: 2px 0;
        }
        .filter-label {
            font-size: 10px;
            font-weight: 600;
            color: #1a1a1a;
            margin-right: 5px;
            white-space: nowrap;
        }
        .filter-value {
            font-size: 10px;
            color: #1a1a1a;
            font-weight: 400;
        }
        .filter-item {
            display: flex;
            align-items: center;
            margin: 0;
            line-height: 1.2;
            white-space: nowrap;
        }
        .filter-label {
            font-weight: 600;
            font-size: 9px;
            color: #4b5563;
            margin-right: 4px;
        }
        .filter-value {
            font-size: 9px;
            color: #1f2937;
            font-weight: 400;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #e5e7eb;
            text-align: left;
            padding: 8px;
            font-size: 11px;
            border-bottom: 1px solid #d1d5db;
            color: #374151;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .status {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-align: center;
        }
        .status-draft {
            background-color: #e5e7eb;
            color: #4b5563;
        }
        .status-pending_approval {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-ordered {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-partially_received {
            background-color: #e0e7ff;
            color: #3730a3;
        }
        .status-completed {
            background-color: #a7f3d0;
            color: #065f46;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .totals {
            margin-top: 20px;
            background-color: #f9fafb;
            padding: 10px;
            border-radius: 4px;
        }
        .totals h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #4b5563;
        }
        .total-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #e5e7eb;
        }
        .total-item:last-child {
            border-bottom: none;
        }
        .total-label {
            font-weight: bold;
        }
        .page-number {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 10px;
            color: #9ca3af;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="container">
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
                    <p style="margin: 2px 0; font-size: 9px;">NIF: {{ $companyTaxId }} | {{ $companyWebsite }}</p>
                </div>
            </div>
            <div style="margin-top: 15px; text-align: center;">
                <h1 style="margin: 10px 0; font-size: 18px; color: #3b82f6;">{{ strtoupper(__('messages.purchase_orders_list')) }}</h1>
                <div style="font-size: 9px; color: #666;">
                    {{ __('messages.generated_by') }}: {{ $user->name }} | {{ __('messages.date') }}: {{ $currentDate }}
                </div>
            </div>
        </div>

        <div class="filters" style="background-color: #ffffff; padding: 10px; border: 1px solid #e0e0e0; margin-bottom: 10px; border-radius: 4px;">
            <h3 style="color: #000000; font-size: 12px; font-weight: bold; margin: 0 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #e0e0e0;">
                {{ __('messages.applied_filters') }}
            </h3>
            
            <div style="display: flex; flex-wrap: wrap; gap: 10px 20px;">
                {{-- Search Filter --}}
                @if(!empty(trim($search ?? '')))
                <div style="display: flex; align-items: center;">
                    <span style="font-weight: bold; margin-right: 5px; color: #000000; font-size: 10px;">{{ __('messages.search') }}:</span>
                    <span style="color: #000000; font-size: 10px;">{{ $search }}</span>
                </div>
                @endif
                
                {{-- Status Filter --}}
                <div style="display: flex; align-items: center;">
                    <span style="font-weight: bold; margin-right: 5px; color: #000000; font-size: 10px;">{{ __('messages.status') }}:</span>
                    <span style="color: #000000; font-size: 10px;">
                        {{ !empty($statusFilter) && $statusFilter !== 'all' ? __('messages.' . $statusFilter) : __('messages.all_statuses') }}
                    </span>
                </div>
                
                {{-- Supplier Filter --}}
                @if(!empty($supplierFilter) && $supplierFilter !== 'all' && !empty($filters['supplier']))
                <div style="display: flex; align-items: center;">
                    <span style="font-weight: bold; margin-right: 5px; color: #000000; font-size: 10px;">{{ __('messages.supplier') }}:</span>
                    <span style="color: #000000; font-size: 10px;">{{ $filters['supplier'] }}</span>
                </div>
                @endif
                
                {{-- Date Field --}}
                <div style="display: flex; align-items: center;">
                    <span style="font-weight: bold; margin-right: 5px; color: #000000; font-size: 10px;">{{ __('messages.date_field') }}:</span>
                    <span style="color: #000000; font-size: 10px;">
                        {{ $dateField === 'order_date' ? __('messages.order_date') : __('messages.expected_delivery') }}
                    </span>
                </div>
                
                {{-- Period Filter --}}
                <div style="display: flex; align-items: center;">
                    <span style="font-weight: bold; margin-right: 5px; color: #000000; font-size: 10px;">{{ __('messages.period') }}:</span>
                    <span style="color: #000000; font-size: 10px;">
                        @if(!empty($monthFilter) || !empty($yearFilter))
                            @if(!empty($monthFilter))
                                {{ date('F', mktime(0, 0, 0, $monthFilter, 10)) }}
                            @endif
                            @if(!empty($monthFilter) && !empty($yearFilter)), @endif
                            @if(!empty($yearFilter))
                                {{ $yearFilter }}
                            @endif
                        @else
                            {{ __('messages.all_periods') }}
                        @endif
                    </span>
                </div>
            </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.order_number') }}</th>
                    <th>{{ __('messages.supplier') }}</th>
                    <th>{{ __('messages.order_date') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.items') }}</th>
                    <th class="text-right">{{ __('messages.total_value') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchaseOrders as $index => $order)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $order->order_number }}</td>
                        <td>{{ $order->supplier->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</td>
                        <td>
                            <div class="status status-{{ $order->status }}">
                                {{ __('messages.' . $order->status) }}
                            </div>
                        </td>
                        <td>{{ $order->items->count() }}</td>
                        <td class="text-right">{{ number_format($order->total_value, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('messages.no_purchase_orders_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals">
            <h3>{{ __('messages.summary') }}</h3>
            <div class="total-item">
                <span class="total-label">{{ __('messages.total_orders') }}:</span>
                <span>{{ $totalOrders }}</span>
            </div>
            <div class="total-item">
                <span class="total-label">{{ __('messages.total_value') }}:</span>
                <span>{{ number_format($totalValue, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="footer">
            {{ __('messages.confidential_document') }} | {{ __('messages.erp_dembena') }} &copy; {{ date('Y') }}
        </div>
        
        <div class="page-number">
            {{ __('messages.page') }} <span class="page">1</span>
        </div>
    </div>
</body>
</html>
