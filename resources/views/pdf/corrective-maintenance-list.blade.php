<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            margin: 0 auto;
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
            color: #EF4444;
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
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table thead th {
            background-color: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 11px;
        }
        .items-table tbody td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-size: 11px;
            vertical-align: top;
        }
        .items-table tfoot td {
            border-top: 2px solid #d1d5db;
            padding: 8px;
            font-weight: bold;
            background-color: #f9fafb;
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
            <div class="document-title">{{ __('messages.corrective_maintenance_list') }}</div>
        </div>
    </div>

    <div class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.report_date') }}:</th>
                <td>{{ \Carbon\Carbon::parse($generatedAt)->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.generated_by') }}:</th>
                <td>{{ auth()->user()->name ?? __('messages.system') }}</td>
            </tr>
            @if(!empty($filters['equipment_name']))
            <tr>
                <th>{{ __('messages.equipment') }}:</th>
                <td>{{ $filters['equipment_name'] }}</td>
            </tr>
            @endif
            @if(!empty($filters['status']))
            <tr>
                <th>{{ __('messages.status') }}:</th>
                <td>{{ $filters['status'] }}</td>
            </tr>
            @endif
            @if(!empty($filters['year']))
            <tr>
                <th>{{ __('messages.year') }}:</th>
                <td>{{ $filters['year'] }}</td>
            </tr>
            @endif
            @if(!empty($filters['month']))
            <tr>
                <th>{{ __('messages.month') }}:</th>
                <td>{{ $filters['month'] }}</td>
            </tr>
            @endif
        </table>
    </div>
    @if(!empty($filters['search']))
        <div class="filter-item">
            <span class="filter-label">{{ __('messages.search') }}:</span>
            <span class="filter-value">{{ $filters['search'] }}</span>
        </div>
    @endif
    </div>

    <table class="items-table">
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
                <td>{{ ($plan->start_time && is_object($plan->start_time)) ? $plan->start_time->format(\App\Models\Setting::getSystemDateTimeFormat()) : __('messages.not_set') }}</td>
                <td>{{ ($plan->end_time && is_object($plan->end_time)) ? $plan->end_time->format(\App\Models\Setting::getSystemDateTimeFormat()) : __('messages.not_set') }}</td>
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

    <table class="items-table">
        <tfoot>
            <tr>
                <td colspan="9" style="text-align: right;"><strong>{{ __('messages.total_corrective_plans') }}:</strong></td>
                <td><strong>{{ count($corrective_plans) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px;" class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.notes') }}:</th>
                <td>{{ __('messages.corrective_maintenance_notes') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer" style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; font-size: 10px; color: #6b7280;">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
        <p>{{ __('messages.report_generated_by') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ now()->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</p>
    </div>
    </div>
</body>
</html>
