<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.maintenance_plan') }} #{{ $plan->id }}</title>
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
        .section {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9fafb;
            border-radius: 4px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #1f2937;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 4px 10px 4px 0;
            width: 30%;
            color: #4b5563;
            vertical-align: top;
        }
        .info-value {
            display: table-cell;
            padding: 4px 0;
            vertical-align: top;
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
        .maintenance-note {
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
        }
        .note-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 10px;
            color: #6b7280;
        }
        .note-content {
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
        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-cancelled {
            background-color: #f3f4f6;
            color: #6b7280;
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
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <img src="{{ $logoFullPath }}" alt="{{ $companyName }} Logo" class="logo">
                <h2 style="margin: 5px 0; padding: 0; font-size: 14px;">{{ $companyName }}</h2>
                <p style="margin: 2px 0; font-size: 9px;">{{ $companyAddress }}</p>
                <p style="margin: 2px 0; font-size: 9px;">Tel: {{ $companyPhone }} | Email: {{ $companyEmail }}</p>
                <p style="margin: 2px 0; font-size: 9px;">CNPJ: {{ $companyTaxId }} | {{ $companyWebsite }}</p>
            </div>
            <div style="text-align: right;">
                <div class="document-title">{{ __('messages.maintenance_plan') }}</div>
                <div style="font-size: 11px; margin-top: 5px;">{{ __('messages.generated_at') }}: {{ \Carbon\Carbon::parse($generatedAt)->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">{{ __('messages.plan_information') }}</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">{{ __('messages.id') }}:</div>
                <div class="info-value">{{ $plan->id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.title') }}:</div>
                <div class="info-value">{{ $plan->title }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.description') }}:</div>
                <div class="info-value">{{ $plan->description }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.equipment') }}:</div>
                <div class="info-value">
                    @if($plan->equipment)
                        {{ $plan->equipment->name }} ({{ $plan->equipment->serial ?? 'N/A' }})
                    @else
                        {{ __('messages.not_specified') }}
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.frequency') }}:</div>
                <div class="info-value">{{ $plan->frequency_type }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.interval') }}:</div>
                <div class="info-value">{{ $plan->frequency_interval }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.start_date') }}:</div>
                <div class="info-value">{{ $plan->start_date }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.status') }}:</div>
                <div class="info-value">
                    <span class="status-badge status-{{ strtolower($plan->status) }}">{{ $plan->status }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.created_by') }}:</div>
                <div class="info-value">{{ $plan->assignedTo->name ?? __('messages.not_assigned') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">{{ __('messages.created_at') }}:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($plan->created_at)->format(\App\Models\Setting::get('date_format', 'd/m/Y').' H:i') }}</div>
            </div>
        </div>
    </div>

    @if($plan->tasks && is_countable($plan->tasks) && count($plan->tasks) > 0)
    <div class="section">
        <div class="section-title">{{ __('messages.maintenance_tasks') }}</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('messages.task') }}</th>
                    <th>{{ __('messages.description') }}</th>
                    <th>{{ __('messages.estimated_time') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plan->tasks as $task)
                <tr>
                    <td>{{ $task->name }}</td>
                    <td>{{ $task->description }}</td>
                    <td>{{ $task->estimated_time }} {{ __('messages.minutes') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($plan->parts && is_countable($plan->parts) && count($plan->parts) > 0)
    <div class="section">
        <div class="section-title">{{ __('messages.required_parts') }}</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>{{ __('messages.part') }}</th>
                    <th>{{ __('messages.quantity') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plan->parts as $part)
                <tr>
                    <td>{{ $part->part_name }}</td>
                    <td>{{ $part->quantity }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($plan->notes && is_countable($plan->notes) && count($plan->notes) > 0)
    <div class="section">
        <div class="section-title">{{ __('messages.maintenance_notes') }}</div>
        
        @foreach($plan->notes as $note)
        <div class="maintenance-note">
            <div class="note-header">
                <div>{{ \Carbon\Carbon::parse($note->created_at)->format(\App\Models\Setting::getSystemDateTimeFormat()) }} - {{ $note->user->name ?? __('messages.unknown_user') }}</div>
                <div><span class="status-badge status-{{ strtolower($note->status) }}">{{ $note->status }}</span></div>
            </div>
            <div class="note-content">{{ $note->notes }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Seção para datas de manutenção calculadas - Formato compacto por mes -->
    <div class="section">
        <div class="section-title">{{ __('messages.calculated_maintenance_dates') }}</div>
        <p class="mb-2" style="font-size: 10px; color: #4b5563;">{{ __('messages.maintenance_dates_description') }} <strong>({{ count($maintenanceDates) }} {{ __('messages.occurrences') }})</strong></p>
        
        @if(count($maintenanceDates) > 0)
            @php
                // Agrupar por mês para economizar espaço
                $datesByMonth = [];
                $yearMonths = [];
                
                foreach($maintenanceDates as $date) {
                    $carbonDate = \Carbon\Carbon::parse($date);
                    $yearMonth = $carbonDate->format('Y-m');
                    $monthName = $carbonDate->format('M Y');
                    
                    if (!isset($datesByMonth[$yearMonth])) {
                        $datesByMonth[$yearMonth] = [];
                        $yearMonths[$yearMonth] = $monthName;
                    }
                    
                    $datesByMonth[$yearMonth][] = [
                        'date' => $carbonDate->format(\App\Models\Setting::getSystemDateFormat()),
                        'isToday' => $carbonDate->isToday(),
                        'isPast' => $carbonDate->isPast(),
                        'isFuture' => $carbonDate->isFuture(),
                        'day' => $carbonDate->format('d')
                    ];
                }
                
                // Limitar a exibição para economizar espaço
                $maxMonthsToShow = 12; // Mostrar no máximo 12 meses
                $monthCount = 0;
                
                // Ordenar por mês
                ksort($datesByMonth);
            @endphp
            
            <!-- Lista horizontal de datas com formato correto -->
            <table style="width: 100%; border-collapse: collapse; font-size: 9px;">
                <tbody>
                    @php
                        $counter = 0;
                        $datesPerRow = 6; // 6 datas por linha
                        $dateFormat = \App\Models\Setting::get('date_format', 'd/m/Y'); // Obtém o formato configurado
                        $allDates = collect();
                        
                        // Aplanar todas as datas em uma coleção
                        foreach($maintenanceDates as $date) {
                            $carbonDate = \Carbon\Carbon::parse($date);
                            $allDates->push([
                                'date' => $carbonDate->format($dateFormat),
                                'isToday' => $carbonDate->isToday(),
                                'isPast' => $carbonDate->isPast(),
                                'isFuture' => $carbonDate->isFuture()
                            ]);
                        }
                        
                        // Ordenar as datas
                        $allDates = $allDates->sortBy(function($item) {
                            return \Carbon\Carbon::createFromFormat(\App\Models\Setting::get('date_format', 'd/m/Y'), $item['date'])->timestamp;
                        })->values();
                        
                        // Mostrar todas as datas sem limitação
                        $maxDates = $allDates->count();
                    @endphp
                    
                    @for($i = 0; $i < $allDates->count(); $i += $datesPerRow)
                        <tr>
                            @for($j = 0; $j < $datesPerRow; $j++)
                                @if($i + $j < $allDates->count())
                                    @php
                                        $dateInfo = $allDates[$i + $j];
                                        $styleClass = $dateInfo['isToday'] ? 'background-color: #3b82f6; color: white;' : 
                                                    ($dateInfo['isPast'] ? 'background-color: #e5e7eb; color: #6b7280;' : 
                                                    'background-color: #dbeafe; color: #1e40af;');
                                    @endphp
                                    <td style="padding: 2px; text-align: center; width: 16.66%;">
                                        <span style="{{ $styleClass }} display: inline-block; padding: 2px 5px; border-radius: 3px; width: 100%;">
                                            {{ $dateInfo['date'] }}
                                            @if($dateInfo['isToday'])
                                                <span style="font-size: 7px; background-color: white; color: #3b82f6; border-radius: 2px; padding: 0 2px; margin-left: 2px;">{{ __('messages.today') }}</span>
                                            @endif
                                        </span>
                                    </td>
                                @else
                                    <td style="width: 16.66%;"></td>
                                @endif
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
            
            <!-- Exibindo todas as datas planejadas sem limitação -->
            
            <div style="font-size: 9px; color: #6b7280; margin-top: 5px; border-top: 1px solid #e5e7eb; padding-top: 3px;">
                <p>{{ __('messages.frequency_type') }}: <strong>{{ __('messages.' . $plan->frequency_type) }}</strong> | 
                {{ __('messages.excluding_holidays_and_sundays') }}</p>
            </div>
        @else
            <div style="font-size: 10px; color: #6b7280; text-align: center; padding: 10px; background-color: #f9fafb; border-radius: 4px; margin-top: 5px;">
                {{ __('messages.no_upcoming_maintenance_dates') }}
            </div>
        @endif
    </div>

    @if(!empty($plan->notes))
    <div style="margin-top: 20px;" class="section">
        <div class="section-title">{{ __('messages.notes') }}</div>
        <div style="padding: 10px; background-color: #f9fafb; border-radius: 4px;">
            {{ $plan->notes }}
        </div>
    </div>
    @endif
    
    @php
        // Verificar se existem notas na variável maintenanceNotes
        $hasNotes = isset($maintenanceNotes) && $maintenanceNotes->count() > 0;
    @endphp
    
    @if($hasNotes)
    <div style="margin-top: 20px;" class="section">
        <div class="section-title">{{ __('messages.maintenance_plan_notes') }}</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 20%;">{{ __('messages.date') }}</th>
                    <th style="width: 20%;">{{ __('messages.status') }}</th>
                    <th style="width: 60%;">{{ __('messages.notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($maintenanceNotes as $note)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($note->created_at)->format(\App\Models\Setting::get('date_format', 'd/m/Y').' H:i') }}</td>
                    <td>
                        @php
                            $statusColor = '#6b7280';
                            if ($note->status == 'completed') {
                                $statusColor = '#10b981';
                            } elseif ($note->status == 'pending') {
                                $statusColor = '#f59e0b';
                            } elseif ($note->status == 'in_progress') {
                                $statusColor = '#3b82f6';
                            }
                        @endphp
                        <span style="color: {{ $statusColor }};">{{ __('messages.'.$note->status) }}</span>
                    </td>
                    <td>{{ $note->notes }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer" style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; font-size: 10px; color: #6b7280;">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
        <p>{{ __('messages.report_generated_by') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ now()->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</p>
    </div>
</body>
</html>
