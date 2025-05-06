<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        @page {
            size: landscape;
            margin: 1cm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
            margin: 0;
            padding: 0;
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
        .pending {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .in-progress {
            background-color: #fef3c7;
            color: #92400e;
        }
        .completed {
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
        @endphp
        <table style="width: 100%">
            <tr>
                <td style="width: 20%; text-align: left; vertical-align: middle;">
                    <img src="{{ $logoFullPath }}" alt="{{ $companyName }} Logo" class="logo">
                </td>
                <td style="width: 80%; text-align: center; vertical-align: middle;">
                    <h1>{{ $title }}</h1>
                    <h2>{{ $month }}</h2>
                </td>
            </tr>
        </table>
    </div>

    <div class="filters-section">
        <div class="filter">
            <span class="filter-label">Plan Status Filter:</span>
            <span class="filter-value">{{ ucfirst($filters['planStatus']) }}</span>
        </div>
        <div class="filter">
            <span class="filter-label">Note Status Filter:</span>
            <span class="filter-value">{{ ucfirst($filters['noteStatus']) }}</span>
        </div>
        <div class="filter">
            <span class="filter-label">Generated:</span>
            <span class="filter-value">{{ $generatedAt }}</span>
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
                                        {{ $event['title'] }} ({{ $event['equipment'] }})
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
        <p>{{ $companyName }} &copy; {{ date('Y') }} - All Rights Reserved</p>
        <p>This document was generated automatically by the ERP DEMBENA system.</p>
    </div>
</body>
</html>
