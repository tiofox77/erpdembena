<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ __('messages.maintenance_plan') }} #{{ $plan->id }}</title>
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
        h2 {
            font-size: 16px;
            margin: 0 0 10px 0;
            color: #374151;
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
        @endphp
        <img src="{{ $logoFullPath }}" alt="{{ $companyName }} Logo" class="logo">
        <h1>{{ __('messages.maintenance_plan') }}</h1>
        <h2>{{ $plan->title }}</h2>
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
                <div class="info-value">{{ $plan->created_at }}</div>
            </div>
        </div>
    </div>

    @if($plan->tasks && count($plan->tasks) > 0)
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

    @if($plan->parts && count($plan->parts) > 0)
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

    @if($plan->notes && count($plan->notes) > 0)
    <div class="section">
        <div class="section-title">{{ __('messages.maintenance_notes') }}</div>
        
        @foreach($plan->notes as $note)
        <div class="maintenance-note">
            <div class="note-header">
                <div>{{ $note->created_at }} - {{ $note->user->name ?? __('messages.unknown_user') }}</div>
                <div><span class="status-badge status-{{ strtolower($note->status) }}">{{ $note->status }}</span></div>
            </div>
            <div class="note-content">{{ $note->notes }}</div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - All Rights Reserved</p>
        <p>{{ __('messages.generated_at') }}: {{ now() }}</p>
    </div>
</body>
</html>
