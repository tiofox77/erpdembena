<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.raw_material_report') }}</title>
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
            width: 20%;
            background-color: #f5f5f5;
            font-size: 9px;
        }
        .document-info td {
            padding: 5px;
            font-size: 9px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-critical {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-low {
            background-color: #fff4bd;
            color: #856404;
        }
        .status-normal {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-good {
            background-color: #d4edda;
            color: #155724;
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
            font-size: 8px;
            color: #666;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
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
            <div class="document-title">{{ __('messages.raw_material_report') }}</div>
            <div>{{ __('messages.generated_at') }}: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <div class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.generated_on') }}:</th>
                <td>{{ now()->format('d/m/Y H:i') }}</td>
                <th>{{ __('messages.filter_criteria') }}:</th>
                <td>
                    @if($search)
                        {{ __('messages.search_term') }}: {{ $search }} <br>
                    @endif
                    @if($startDate || $endDate)
                        {{ __('messages.period') }}:
                        {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : __('messages.from_start') }}
                        -
                        {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : __('messages.to_present') }}
                    @else
                        {{ __('messages.all_time') }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <h3 style="font-size: 12px;">{{ __('messages.materials') }}</h3>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 20%;">{{ __('messages.material') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.current_stock') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.min_stock') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.max_stock') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.safety_stock') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.required_qty') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.on_order_qty') }}</th>
                <th style="width: 10%;" class="text-right">{{ __('messages.planned_qty') }}</th>
                <th style="width: 10%;" class="text-center">{{ __('messages.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materials as $material)
                <tr>
                    <td>{{ $material->name }} ({{ $material->sku }})</td>
                    <td class="text-right">{{ number_format($material->current_stock, 2) }} {{ $material->unit_of_measure }}</td>
                    <td class="text-right">{{ number_format($material->min_stock_level, 2) }} {{ $material->unit_of_measure }}</td>
                    <td class="text-right">{{ number_format($material->max_stock_level, 2) }} {{ $material->unit_of_measure }}</td>
                    <td class="text-right">{{ number_format($material->safety_stock, 2) }} {{ $material->unit_of_measure }}</td>
                    <td class="text-right">{{ number_format($material->required_quantity, 2) }} {{ $material->unit_of_measure }}</td>
                    <td class="text-right">{{ number_format($material->on_order_quantity, 2) }} {{ $material->unit_of_measure }}</td>
                    <td class="text-right">{{ number_format($material->planned_quantity, 2) }} {{ $material->unit_of_measure }}</td>
                    <td class="text-center">
                        @php
                            $statusClass = 'status-normal';
                            $statusText = __('messages.normal');
                            
                            if ($material->current_stock < $material->safety_stock) {
                                $statusClass = 'status-critical';
                                $statusText = __('messages.critical');
                            } elseif ($material->current_stock < $material->min_stock_level) {
                                $statusClass = 'status-low';
                                $statusText = __('messages.low');
                            } elseif ($material->current_stock > $material->max_stock_level * 0.8) {
                                $statusClass = 'status-good';
                                $statusText = __('messages.good');
                            }
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            {{ $statusText }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">{{ __('messages.no_materials_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;" class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.notes') }}:</th>
                <td>{{ __('messages.document_for_internal_use') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
        <p>{{ __('messages.raw_material_report_generated') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
