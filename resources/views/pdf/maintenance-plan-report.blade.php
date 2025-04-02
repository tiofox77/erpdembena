<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Plan Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }
        h1 {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        .header {
            margin-bottom: 20px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .company-logo {
            max-height: 60px;
            max-width: 180px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
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
        <div class="header-content">
            @if(isset($companyLogo) && $companyLogo)
                <img src="{{ $companyLogo }}" alt="Company Logo" class="company-logo">
            @endif
            @if(isset($companyName) && $companyName)
                <div class="company-name">{{ $companyName }}</div>
            @endif
            <h1>Maintenance Plan Report</h1>
        </div>
    </div>

    <div class="report-info">
        <p><strong>Date Range:</strong> {{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }} to {{ \Carbon\Carbon::parse($endDate)->format('Y-m-d') }}</p>
        <p><strong>Generated On:</strong> {{ $generatedAt }}</p>
        <p><strong>Total Plans:</strong> {{ count($plans) }}</p>
    </div>

    @if(count($plans) > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Task</th>
                    <th>Equipment</th>
                    <th>Area/Line</th>
                    <th>Frequency</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                </tr>
            </thead>
            <tbody>
                @foreach($plans as $plan)
                    <tr>
                        <td>{{ $plan->scheduled_date->format('Y-m-d') }}</td>
                        <td>{{ $plan->task ? $plan->task->title : 'No Task' }}</td>
                        <td>{{ $plan->equipment ? $plan->equipment->name : 'No Equipment' }}</td>
                        <td>
                            @if($plan->area)
                                Area: {{ $plan->area->name }}
                            @endif
                            @if($plan->line)
                                <br>Line: {{ $plan->line->name }}
                            @endif
                        </td>
                        <td>
                            @switch($plan->frequency_type)
                                @case('once')
                                    Once
                                    @break
                                @case('daily')
                                    Daily
                                    @break
                                @case('weekly')
                                    Weekly
                                    @break
                                @case('monthly')
                                    Monthly
                                    @break
                                @case('yearly')
                                    Yearly
                                    @break
                                @case('custom')
                                    Every {{ $plan->custom_days }} days
                                    @break
                                @default
                                    {{ ucfirst($plan->frequency_type) }}
                            @endswitch
                        </td>
                        <td>
                            @switch($plan->type)
                                @case('preventive')
                                    <span class="type-badge type-preventive">Preventive</span>
                                    @break
                                @case('predictive')
                                    <span class="type-badge type-predictive">Predictive</span>
                                    @break
                                @case('conditional')
                                    <span class="type-badge type-conditional">Conditional</span>
                                    @break
                                @default
                                    <span class="type-badge type-other">{{ ucfirst($plan->type) }}</span>
                            @endswitch
                        </td>
                        <td>
                            @switch($plan->status)
                                @case('pending')
                                    <span class="status-badge status-pending">Pending</span>
                                    @break
                                @case('in_progress')
                                    <span class="status-badge status-in-progress">In Progress</span>
                                    @break
                                @case('completed')
                                    <span class="status-badge status-completed">Completed</span>
                                    @break
                                @case('cancelled')
                                    <span class="status-badge status-cancelled">Cancelled</span>
                                    @break
                                @case('schedule')
                                    <span class="status-badge status-schedule">Schedule</span>
                                    @break
                                @default
                                    <span class="status-badge">{{ ucfirst($plan->status) }}</span>
                            @endswitch
                        </td>
                        <td>{{ $plan->assignedTo ? $plan->assignedTo->name : 'Unassigned' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <p>No maintenance plans found matching the specified criteria.</p>
        </div>
    @endif

    <div class="footer">
        <p>Report generated from Maintenance Management System ({{ now()->format('Y-m-d H:i:s') }})</p>
    </div>
</body>
</html>
