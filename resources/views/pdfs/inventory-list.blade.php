<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.inventory_list') }}</title>
    <style>
        @page {
            margin: 1cm;
        }
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
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white;
            padding: 15px;
            border-radius: 8px;
        }
        .logo {
            max-height: 70px;
            max-width: 220px;
        }
        .page {
            page-break-after: auto;
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
        .status-in_stock {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-low_stock {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-out_of_stock {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .status-unknown {
            background-color: #e5e7eb;
            color: #4b5563;
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
                <div style="font-size: 18px; font-weight: bold; margin: 10px 0; color: white;">
                    {{ __('messages.inventory_list') }}
                </div>
                <div>
                    {{ __('messages.generated_by') }}: {{ $user->name ?? 'Sistema' }} | {{ __('messages.date') }}: {{ $currentDate }}
                </div>
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
                    <div class="filter-label">{{ __('messages.location') }}:</div>
                    <div class="filter-value">{{ $filters['location'] }}</div>
                </div>
                <div class="filter-item">
                    <div class="filter-label">{{ __('messages.category') }}:</div>
                    <div class="filter-value">{{ $filters['category'] }}</div>
                </div>
                <div class="filter-item">
                    <div class="filter-label">{{ __('messages.stock_level') }}:</div>
                    <div class="filter-value">{{ $filters['stock_level'] }}</div>
                </div>
                <div class="filter-item">
                    <div class="filter-label">{{ __('messages.product_type') }}:</div>
                    <div class="filter-value">{{ $filters['product_type'] }}</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ __('messages.product') }}</th>
                    <th>{{ __('messages.sku') }}</th>
                    <th>{{ __('messages.category') }}</th>
                    <th>{{ __('messages.location') }}</th>
                    <th class="text-right">{{ __('messages.quantity') }}</th>
                    <th class="text-right">{{ __('messages.unit_cost') }}</th>
                    <th class="text-right">{{ __('messages.total_value') }}</th>
                    <th>{{ __('messages.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventoryItems as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->name ?? __('messages.unknown_product') }}</td>
                        <td>{{ $item->product->sku ?? 'N/A' }}</td>
                        <td>{{ $item->product->category->name ?? __('messages.uncategorized') }}</td>
                        <td>{{ $item->location->name ?? __('messages.unknown_location') }}</td>
                        <td class="text-right">{{ number_format($item->quantity_on_hand, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->unit_cost ?? 0, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format(($item->quantity_on_hand * ($item->unit_cost ?? 0)), 2, ',', '.') }}</td>
                        <td>
                            <div class="status status-{{ $item->stock_status }}">
                                {{ __('messages.' . $item->stock_status) }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">{{ __('messages.no_inventory_items_found') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="totals">
            <h3>{{ __('messages.summary') }}</h3>
            <div class="total-item">
                <span class="total-label">{{ __('messages.total_items') }}:</span>
                <span>{{ $totalItems }}</span>
            </div>
            <div class="total-item">
                <span class="total-label">{{ __('messages.total_quantity') }}:</span>
                <span>{{ number_format($totalQuantity, 2, ',', '.') }}</span>
            </div>
            <div class="total-item">
                <span class="total-label">{{ __('messages.total_value') }}:</span>
                <span>{{ number_format($totalValue, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Rodapé -->
        <div class="footer">
            <p>{{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
            <p>{{ __('messages.report_generated_by') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ $currentDate }}</p>
            <p>{{ __('messages.confidential_document') }} - {{ __('messages.page') }} <span class="page">1</span></p>
        </div>
        
        <div class="page-number">
            {{ __('messages.page') }} <span class="page">1</span>
        </div>
    </div>
</body>
</html>
