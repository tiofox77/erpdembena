<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.purchase_orders_list') }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }
        .container {
            width: 100%;
            padding: 10px;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #3b82f6;
            font-size: 22px;
            margin: 0;
            padding: 0;
        }
        .header .company {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header .info {
            font-size: 10px;
            color: #666;
        }
        .filters {
            background-color: #f3f4f6;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .filters h3 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #4b5563;
        }
        .filters-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .filter-item {
            flex: 1;
            min-width: 100px;
        }
        .filter-label {
            font-weight: bold;
            font-size: 10px;
            color: #6b7280;
        }
        .filter-value {
            font-size: 11px;
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
            <div class="company">ERP DEMBENA</div>
            <h1>{{ __('messages.purchase_orders_list') }}</h1>
            <div class="info">
                {{ __('messages.generated_by') }}: {{ $user->name }} | {{ __('messages.date') }}: {{ $currentDate }}
            </div>
        </div>

        <div class="filters">
            <h3>{{ __('messages.applied_filters') }}</h3>
            <div class="filters-grid">
                <div class="filter-item">
                    <div class="filter-label">{{ __('messages.search') }}:</div>
                    <div class="filter-value">{{ $filters['search'] ?: __('messages.none') }}</div>
                </div>
                <div class="filter-item">
                    <div class="filter-label">{{ __('messages.status') }}:</div>
                    <div class="filter-value">{{ $filters['status'] }}</div>
                </div>
                <div class="filter-item">
                    <div class="filter-label">{{ __('messages.supplier') }}:</div>
                    <div class="filter-value">{{ $filters['supplier'] }}</div>
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
