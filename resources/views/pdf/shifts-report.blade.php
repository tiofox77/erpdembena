<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 150px;
            max-height: 80px;
            margin-bottom: 10px;
        }
        h1 {
            color: #2563eb;
            font-size: 24px;
            margin: 0;
            margin-bottom: 5px;
        }
        .date {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 12px;
            font-weight: bold;
            color: #374151;
            border-bottom: 2px solid #ddd;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            text-align: center;
            color: #666;
        }
        .status {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .active {
            background-color: #dcfce7;
            color: #166534;
        }
        .inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .night-shift {
            background-color: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($hasLogo && $logoPath)
            <img src="{{ $logoPath }}" alt="{{ $companyName }} Logo" class="logo">
        @endif
        <h1>{{ $companyName }}</h1>
        <h2>{{ $title }}</h2>
        <p class="date">Generated on: {{ $date }}</p>
    </div>

    <main>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Break</th>
                    <th>Status</th>
                    <th>Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                    <tr>
                        <td>{{ $shift->name }}</td>
                        <td>{{ $shift->start_time->format('H:i') }}</td>
                        <td>{{ $shift->end_time->format('H:i') }}</td>
                        <td>{{ $shift->break_duration ?? 0 }} min</td>
                        <td>
                            <span class="status {{ $shift->is_active ? 'active' : 'inactive' }}">
                                {{ $shift->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <span class="status {{ $shift->is_night_shift ? 'night-shift' : '' }}">
                                {{ $shift->is_night_shift ? 'Night Shift' : 'Day Shift' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center;">No shifts found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>

    <div class="footer">
        <p> {{ date('Y') }} {{ $companyName }} - All rights reserved</p>
    </div>
</body>
</html>
