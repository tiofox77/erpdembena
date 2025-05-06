<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #333;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .logo {
            max-width: 150px;
            max-height: 60px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 16pt;
            margin: 5px 0;
            color: #333;
        }
        h2 {
            font-size: 14pt;
            margin: 5px 0 15px;
            color: #666;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
            color: #333;
        }
        .filters {
            background-color: #f9f9f9;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #eee;
        }
        .filter-item {
            margin-bottom: 5px;
        }
        .filter-label {
            font-weight: bold;
            display: inline-block;
            min-width: 100px;
        }
        .filter-value {
            display: inline-block;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 7pt;
            table-layout: fixed;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: left;
            padding: 4px;
            border: 1px solid #ddd;
            font-weight: bold;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        .data-table td {
            padding: 3px 4px;
            border: 1px solid #ddd;
            vertical-align: top;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        .data-table th:nth-child(1), .data-table td:nth-child(1) { width: 5%; } /* ID */
        .data-table th:nth-child(2), .data-table td:nth-child(2) { width: 15%; } /* Equipment */
        .data-table th:nth-child(3), .data-table td:nth-child(3) { width: 10%; } /* Area */
        .data-table th:nth-child(4), .data-table td:nth-child(4) { width: 10%; } /* Line */
        .data-table th:nth-child(5), .data-table td:nth-child(5) { width: 15%; } /* Failure Mode */
        .data-table th:nth-child(6), .data-table td:nth-child(6) { width: 15%; } /* Failure Cause */
        .data-table th:nth-child(7), .data-table td:nth-child(7) { width: 8%; } /* Start Time */
        .data-table th:nth-child(8), .data-table td:nth-child(8) { width: 8%; } /* End Time */
        .data-table th:nth-child(9), .data-table td:nth-child(9) { width: 8%; } /* Duration */
        .data-table th:nth-child(10), .data-table td:nth-child(10) { width: 6%; } /* Status */
        .data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary {
            margin-top: 20px;
            text-align: right;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9pt;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
        }
        .status-pending { background-color: #FEF3C7; color: #92400E; }
        .status-in_progress { background-color: #DBEAFE; color: #1E40AF; }
        .status-completed { background-color: #D1FAE5; color: #065F46; }
        .status-cancelled { background-color: #FEE2E2; color: #B91C1C; }
        .page-break {
            page-break-after: always;
        }
        @page {
            size: portrait;
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
        @endphp
        <img src="{{ $logoFullPath }}" alt="{{ $companyName }} Logo" class="logo">
        <h1>{{ __('messages.corrective_maintenance_list') }}</h1>
    </div>

    <div class="filters">
        <div class="filter-item">
            <span class="filter-label">{{ __('messages.generated_at') }}:</span>
            <span class="filter-value">{{ $generatedAt }}</span>
        </div>
        
        @if(!empty($filters['equipment_name']))
        <div class="filter-item">
            <span class="filter-label">{{ __('messages.equipment') }}:</span>
            <span class="filter-value">{{ $filters['equipment_name'] }}</span>
        </div>
        @endif
        
        @if(!empty($filters['status']))
        <div class="filter-item">
            <span class="filter-label">{{ __('messages.status') }}:</span>
            <span class="filter-value">{{ $filters['status'] }}</span>
        </div>
        @endif
        
        @if(!empty($filters['year']))
        <div class="filter-item">
            <span class="filter-label">{{ __('messages.year') }}:</span>
            <span class="filter-value">{{ $filters['year'] }}</span>
        </div>
        @endif
        
        @if(!empty($filters['month']))
        <div class="filter-item">
            <span class="filter-label">{{ __('messages.month') }}:</span>
            <span class="filter-value">{{ $filters['month'] }}</span>
        </div>
        @endif
        
        @if(!empty($filters['search']))
        <div class="filter-item">
            <span class="filter-label">{{ __('messages.search') }}:</span>
            <span class="filter-value">{{ $filters['search'] }}</span>
        </div>
        @endif
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>{{ __('messages.id') }}</th>
                <th>{{ __('messages.equipment') }}</th>
                <th>{{ __('messages.area') }}</th>
                <th>{{ __('messages.line') }}</th>
                <th>{{ __('messages.failure_mode') }}</th>
                <th>{{ __('messages.failure_cause') }}</th>
                <th>{{ __('messages.start_time') }}</th>
                <th>{{ __('messages.end_time') }}</th>
                <th>{{ __('messages.duration') }}</th>
                <th>{{ __('messages.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($corrective_plans as $plan)
            <tr>
                <td>{{ $plan->id }}</td>
                <td>
                    @if($plan->equipment && is_object($plan->equipment))
                        {{ $plan->equipment->name }} ({{ $plan->equipment->serial ?? 'N/A' }})
                    @else
                        {{ __('messages.not_specified') }}
                    @endif
                </td>
                <td>
                    @if($plan->equipment && is_object($plan->equipment) && $plan->equipment->area && is_object($plan->equipment->area))
                        {{ $plan->equipment->area->name }}
                    @else
                        {{ __('messages.not_specified') }}
                    @endif
                </td>
                <td>
                    @if($plan->equipment && is_object($plan->equipment) && $plan->equipment->line && is_object($plan->equipment->line))
                        {{ $plan->equipment->line->name }}
                    @else
                        {{ __('messages.not_specified') }}
                    @endif
                </td>
                <td>{{ ($plan->failure_mode && is_object($plan->failure_mode)) ? $plan->failure_mode->name : __('messages.not_specified') }}</td>
                <td>{{ ($plan->failure_cause && is_object($plan->failure_cause)) ? $plan->failure_cause->name : __('messages.not_specified') }}</td>
                <td>{{ ($plan->start_time && is_object($plan->start_time)) ? $plan->start_time->format('Y-m-d H:i') : __('messages.not_set') }}</td>
                <td>{{ ($plan->end_time && is_object($plan->end_time)) ? $plan->end_time->format('Y-m-d H:i') : __('messages.not_set') }}</td>
                <td>{{ $plan->duration ?? __('messages.not_calculated') }}</td>
                <td><span class="status-badge status-{{ strtolower($plan->status) }}">{{ $plan->status }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center;">{{ __('messages.no_maintenance_plans_found') }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <p>{{ __('messages.total_records') }}: {{ count($corrective_plans) }}</p>
    </div>

    <div class="footer">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - All Rights Reserved</p>
        <p>{{ __('messages.report_generated_by') }} ERP DEMBENA</p>
    </div>
    </div>
</body>
</html>
