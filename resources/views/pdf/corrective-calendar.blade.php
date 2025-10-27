<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        @page {
            size: landscape;
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
            color: #dc2626;
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
            font-size: 16px;
            margin: 0 0 5px 0;
            color: #374151;
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
        .calendar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            table-layout: fixed;
        }
        .calendar th {
            background-color: #e5e7eb;
            padding: 4px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 10px;
        }
        .calendar td {
            border: 1px solid #d1d5db;
            padding: 4px;
            vertical-align: top;
            height: 60px;
            width: 14.28%;
            overflow: hidden;
        }
        .day-number {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 12px;
        }
        .other-month {
            background-color: #f3f4f6;
            color: #9ca3af;
        }
        .today {
            background-color: #dbeafe;
        }
        .weekend {
            background-color: #f3f4f6;
        }
        .holiday {
            background-color: #fee2e2;
        }
        .event {
            margin-bottom: 2px;
            padding: 1px 2px;
            border-radius: 2px;
            font-size: 8px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }
        .open {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .in_progress {
            background-color: #fef3c7;
            color: #92400e;
        }
        .resolved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .cancelled {
            background-color: #f3f4f6;
            color: #6b7280;
            text-decoration: line-through;
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
            <div class="document-title">{{ $title }}</div>
            <div>{{ __('messages.report_period') }}: {{ $filters['period'] ?? __('messages.all_time') }}</div>
        </div>
    </div>
    
    <div class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.report_date') }}:</th>
                <td>{{ now()->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.generated_by') }}:</th>
                <td>{{ auth()->user()->name ?? __('messages.system') }}</td>
            </tr>
        </table>
    </div>

    <div class="filters-section">
        <div class="filter">
            <span class="filter-label">Generated:</span>
            <span class="filter-value">{{ \Carbon\Carbon::parse($generatedAt)->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</span>
        </div>
    </div>

    <table class="calendar">
        <thead>
            <tr>
                <th>Sunday</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
                <th>Saturday</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentDay = 0;
                $calendarDaysByWeek = array_chunk($calendarDays, 7);
            @endphp

            @foreach($calendarDaysByWeek as $week)
                <tr>
                    @foreach($week as $day)
                        @php
                            $classes = [];
                            if (!$day['isCurrentMonth']) $classes[] = 'other-month';
                            if ($day['isToday']) $classes[] = 'today';
                            if ($day['isWeekend']) $classes[] = 'weekend';
                            if ($day['isHoliday']) $classes[] = 'holiday';
                            $classString = !empty($classes) ? implode(' ', $classes) : '';
                        @endphp
                        <td class="{{ $classString }}">
                            <div class="day-number">{{ $day['day'] }}</div>
                            
                            @if(isset($events[$day['date']]))
                                @foreach($events[$day['date']] as $event)
                                    <div class="event {{ $event['status'] }}">
                                        {{ $event['equipment'] }} ({{ $event['start_time'] }})
                                    </div>
                                @endforeach
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
        <p>{{ __('messages.report_generated_by') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ now()->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</p>
    </div>
</body>
</html>
