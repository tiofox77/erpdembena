<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.maintenance_plan_report') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
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
        .report-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }
        .report-info p {
            margin: 5px 0;
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
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .status-badge, .type-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-pending {
            background-color: #FEF9C3;
            color: #854D0E;
        }
        .status-in-progress {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        .status-completed {
            background-color: #DCFCE7;
            color: #166534;
        }
        .status-cancelled {
            background-color: #F3F4F6;
            color: #4B5563;
        }
        .status-schedule {
            background-color: #E0E7FF;
            color: #4338CA;
        }
        .type-preventive {
            background-color: #DCFCE7;
            color: #166534;
        }
        .type-predictive {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        .type-conditional {
            background-color: #FFEDD5;
            color: #9A3412;
        }
        .type-other {
            background-color: #F3F4F6;
            color: #4B5563;
        }
        .page-break {
            page-break-after: always;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 4px;
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
            <div class="document-title">{{ __('messages.maintenance_plan_report') }}</div>
            <div>{{ __('messages.generated_at') }}: {{ \Carbon\Carbon::parse($generatedAt)->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</div>
        </div>
    </div>

    <div class="report-info">
        <p><strong>{{ __('messages.report_period') }}:</strong> {{ $monthTitle }}</p>
        <p><strong>{{ __('messages.generated_on') }}:</strong> {{ $generatedAt }}</p>
        <p><strong>{{ __('messages.total_plans') }}:</strong> {{ count($plans) }}</p>
    </div>
    
    @php
        // Agrupar planos por dia dentro do mês selecionado
        $plansByDay = [];
        
        foreach ($plans as $plan) {
            foreach ($plan->occurrences as $occurrence) {
                $dayKey = $occurrence->format('Y-m-d');
                if (!isset($plansByDay[$dayKey])) {
                    $plansByDay[$dayKey] = [];
                }
                $plansByDay[$dayKey][] = $plan;
            }
        }
        
        // Ordenar os dias
        ksort($plansByDay);
        
        // Labels de frequência para exibição
        $frequencyLabels = [
            'daily' => __('messages.daily'),
            'weekly' => __('messages.weekly'),
            'monthly' => __('messages.monthly'),
            'yearly' => __('messages.yearly'),
            'custom' => __('messages.custom'),
            'once' => __('messages.one_time'),
        ];
    @endphp

    @if(count($plans) > 0)
        <h3 style="margin-top: 20px; margin-bottom: 15px; color: #2563eb; text-align: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">
            {{ __('messages.maintenance_plan_schedule') }} - {{ $monthTitle }}
        </h3>

        @foreach($plansByDay as $day => $dayPlans)
            @php
                $dayDate = \Carbon\Carbon::createFromFormat('Y-m-d', $day);
                $dateFormat = \App\Models\Setting::getSystemDateFormat();
                $dayLabel = $dayDate->format($dateFormat) . ' (' . $dayDate->translatedFormat('l') . ')'; // Formato configurado + (Nome do dia da semana)
                
                // Agrupar planos do dia por tipo de frequência
                $dayPlansByFrequency = collect($dayPlans)->groupBy('frequency_type');
            @endphp
            
            <div class="day-section">
                <h4 style="margin-top: 20px; margin-bottom: 10px; color: #4b5563; font-size: 14px; background-color: #f3f4f6; padding: 8px; border-radius: 4px;">
                    {{ $dayLabel }} - {{ count($dayPlans) }} {{ __('messages.plans') }}
                </h4>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>{{ __('messages.frequency') }}</th>
                            <th>{{ __('messages.task') }}</th>
                            <th>{{ __('messages.equipment') }}</th>
                            <th>{{ __('messages.area_line') }}</th>
                            <th>{{ __('messages.type') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.assigned_to') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dayPlans as $plan)
                            <tr>
                                <td>
                                    {{ $frequencyLabels[$plan->frequency_type] ?? $plan->frequency_type }}
                                    @if($plan->frequency_type == 'custom' && $plan->custom_days)
                                        ({{ $plan->custom_days }} {{ __('messages.days') }})
                                    @endif
                                </td>
                                <td>{{ $plan->task ? $plan->task->title : __('messages.no_task') }}</td>
                                <td>{{ $plan->equipment ? $plan->equipment->name : __('messages.no_equipment') }}</td>
                                <td>
                                    @if($plan->area)
                                        {{ __('messages.area') }}: {{ $plan->area->name }}
                                    @endif
                                    
                                    @if($plan->line)
                                        <br>{{ __('messages.line') }}: {{ $plan->line->name }}
                                    @endif
                                </td>
                                <td>
                                    @switch($plan->type)
                                        @case('preventive')
                                            {{ __('messages.preventive') }}
                                            @break
                                        @case('predictive')
                                            {{ __('messages.predictive') }}
                                            @break
                                        @case('conditional')
                                            {{ __('messages.conditional') }}
                                            @break
                                        @default
                                            {{ $plan->type }}
                                    @endswitch
                                </td>
                                <td>
                                    @switch($plan->status)
                                        @case('pending')
                                            {{ __('messages.pending') }}
                                            @break
                                        @case('in_progress')
                                            {{ __('messages.in_progress') }}
                                            @break
                                        @case('completed')
                                            {{ __('messages.completed') }}
                                            @break
                                        @case('cancelled')
                                            {{ __('messages.cancelled') }}
                                            @break
                                        @case('schedule')
                                            {{ __('messages.schedule') }}
                                            @break
                                        @default
                                            {{ $plan->status }}
                                    @endswitch
                                </td>
                                <td>{{ $plan->assignedTo ? $plan->assignedTo->name : __('messages.unassigned') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if(!$loop->last && $loop->index % 3 == 2)
                <div class="page-break"></div>
            @endif
        @endforeach
    @else
        <div class="no-data" style="text-align: center; padding: 30px; background-color: #f9fafb; border-radius: 8px; margin: 20px 0;">
            <p style="font-size: 14px; color: #6b7280;">
                {{ __('messages.no_maintenance_plans_found') }}
            </p>
        </div>
    @endif

    <div style="margin-top: 20px;" class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.notes') }}:</th>
                <td>{{ __('messages.maintenance_plan_report_notes') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer" style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; font-size: 10px; color: #6b7280;">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
        <p>{{ __('messages.report_generated_by') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
