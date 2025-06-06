<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('messages.raw_material_report') }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #1a365d;
            font-size: 18px;
            margin-bottom: 5px;
        }
        .report-info {
            margin-bottom: 20px;
            font-size: 10px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }
        td {
            border: 1px solid #d1d5db;
            padding: 8px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ __('messages.raw_material_report') }}</h1>
        <div class="report-info">
            {{ __('messages.generated_on') }}: {{ now()->format('d/m/Y H:i') }}
            @if($startDate || $endDate)
                <br>
                {{ __('messages.period') }}: 
                {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d/m/Y') : __('messages.start_date') }} 
                - 
                {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d/m/Y') : __('messages.end_date') }}
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('messages.material') }}</th>
                <th class="text-right">{{ __('messages.current_stock') }}</th>
                <th class="text-right">{{ __('messages.min_stock') }}</th>
                <th class="text-right">{{ __('messages.max_stock') }}</th>
                <th class="text-right">{{ __('messages.safety_stock') }}</th>
                <th class="text-right">{{ __('messages.required_qty') }}</th>
                <th class="text-right">{{ __('messages.planned_qty') }}</th>
                <th class="text-center">{{ __('messages.status') }}</th>
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
                    <td class="text-right">{{ number_format($material->planned_quantity, 2) }} {{ $material->unit_of_measure }}</td>
                    <td class="text-center">
                        @php
                            $statusClass = 'bg-gray-100 text-gray-800';
                            if ($material->current_stock < $material->safety_stock) {
                                $statusClass = 'bg-red-100 text-red-800';
                            } elseif ($material->current_stock < $material->min_stock_level) {
                                $statusClass = 'bg-yellow-100 text-yellow-800';
                            } elseif ($material->current_stock > $material->max_stock_level * 0.8) {
                                $statusClass = 'bg-green-100 text-green-800';
                            }
                        @endphp
                        <span style="{{ $statusClass }}" class="px-2 py-1 rounded-full text-xs">
                            @if($material->current_stock < $material->safety_stock)
                                {{ __('messages.critical') }}
                            @elseif($material->current_stock < $material->min_stock_level)
                                {{ __('messages.low') }}
                            @elseif($material->current_stock > $material->max_stock_level * 0.8)
                                {{ __('messages.good') }}
                            @else
                                {{ __('messages.normal') }}
                            @endif
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">{{ __('messages.no_materials_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        {{ config('app.name') }} - {{ __('messages.page') }} {PAGENO} / {nbpg}
    </div>
</body>
</html>
