<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.maintenance_plans_list') }}</title>
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
            color: #2563eb;
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
            <div class="document-title">{{ __('messages.maintenance_plans_list') }}</div>
            <div>{{ __('messages.generated_at') }}: {{ now()->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</div>
        </div>
    </div>
    
    <div class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.report_period') }}:</th>
                <td>{{ isset($filters['dateFrom']) && isset($filters['dateTo']) ? $filters['dateFrom'] . ' - ' . $filters['dateTo'] : __('messages.all_time') }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.total_records') }}:</th>
                <td>{{ $plans->count() }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.generated_by') }}:</th>
                <td>{{ auth()->user()->name ?? __('messages.system') }}</td>
            </tr>
        </table>
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
            <span class="filter-value">{{ now()->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</span>
        </div>
    </div>

    <table class="items-table">
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
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: right;"><strong>{{ __('messages.total_maintenance_plans') }}:</strong></td>
                <td><strong>{{ count($plans) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 20px;" class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.notes') }}:</th>
                <td>{{ __('messages.maintenance_plans_notes') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer" style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; font-size: 10px; color: #6b7280;">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
        <p>{{ __('messages.report_generated_by') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ now()->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</p>
    </div>
</body>
</html>
