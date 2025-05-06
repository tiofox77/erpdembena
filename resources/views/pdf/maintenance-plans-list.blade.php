<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('messages.maintenance_plans_list') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .logo {
            max-height: 70px;
            max-width: 220px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            color: #2563eb;
        }
        .filters-section {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9fafb;
            border-radius: 4px;
        }
        .filter {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 5px;
        }
        .filter-label {
            font-weight: bold;
            font-size: 11px;
            color: #6b7280;
        }
        .filter-value {
            font-size: 11px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.data-table th {
            background-color: #e5e7eb;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 11px;
        }
        table.data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-size: 11px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .summary {
            margin-top: 15px;
            font-size: 11px;
            color: #4b5563;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        @php
            $logoPath = \App\Models\Setting::get('company_logo');
            $logoFullPath = $logoPath ? public_path('storage/' . $logoPath) : public_path('img/logo.png');
            $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
        @endphp
        <img src="{{ $logoFullPath }}" alt="{{ $companyName }} Logo" class="logo">
        <h1>{{ __('messages.maintenance_plans_list') }}</h1>
    </div>

    <div class="filters-section">
        @if(isset($filters))
            @if(isset($filters['search']) && !empty($filters['search']))
            <div class="filter">
                <span class="filter-label">{{ __('messages.search') }}:</span>
                <span class="filter-value">{{ $filters['search'] }}</span>
            </div>
            @endif
            
            @if(isset($filters['equipment_id']) && !empty($filters['equipment_id']))
            <div class="filter">
                <span class="filter-label">{{ __('messages.equipment') }}:</span>
                <span class="filter-value">{{ $filters['equipment_name'] }}</span>
            </div>
            @endif
            
            @if(isset($filters['status']) && !empty($filters['status']))
            <div class="filter">
                <span class="filter-label">{{ __('messages.status') }}:</span>
                <span class="filter-value">{{ $filters['status'] }}</span>
            </div>
            @endif
        @endif
        
        <div class="filter">
            <span class="filter-label">{{ __('messages.generated_at') }}:</span>
            <span class="filter-value">{{ now() }}</span>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>{{ __('messages.id') }}</th>
                <th>{{ __('messages.task') }}</th>
                <th>{{ __('messages.equipment') }}</th>
                <th>{{ __('messages.area') }}</th>
                <th>{{ __('messages.line') }}</th>
                <th>{{ __('messages.frequency') }}</th>
                <th>{{ __('messages.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($plans as $plan)
            <tr>
                <td>{{ $plan->id }}</td>
                <td>
                    @if($plan->task)
                        {{ $plan->task->title }}
                    @else
                        {{ __('messages.not_specified') }}
                    @endif
                </td>
                <td>
                    @if($plan->equipment)
                        {{ $plan->equipment->name }} ({{ $plan->equipment->serial ?? 'N/A' }})
                    @else
                        {{ __('messages.not_specified') }}
                    @endif
                </td>
                <td>
                    @if($plan->equipment && $plan->equipment->area)
                        {{ $plan->equipment->area->name }}
                    @else
                        {{ __('messages.not_specified') }}
                    @endif
                </td>
                <td>
                    @if($plan->equipment && $plan->equipment->line)
                        {{ $plan->equipment->line->name }}
                    @else
                        {{ __('messages.not_specified') }}
                    @endif
                </td>
                <td>{{ $plan->frequency_type }} {{ $plan->frequency_interval ? '('.$plan->frequency_interval.')' : '' }}</td>
                <td><span class="status-badge status-{{ strtolower($plan->status) }}">{{ $plan->status }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center;">{{ __('messages.no_maintenance_plans_found') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <p>{{ __('messages.total_records') }}: {{ count($plans) }}</p>
    </div>

    <div class="footer">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - All Rights Reserved</p>
        <p>{{ __('messages.report_generated_by') }} ERP DEMBENA</p>
    </div>
</body>
</html>
