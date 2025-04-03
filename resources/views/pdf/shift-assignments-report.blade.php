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
        .permanent {
            background-color: #dcfce7;
            color: #166534;
        }
        .temporary {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .summary h3 {
            margin-top: 0;
            color: #374151;
            font-size: 18px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #ddd;
        }
        .summary-item-label {
            font-weight: bold;
            color: #4b5563;
        }
    </style>
</head>
<body>
    <div class="header">
        @if($hasLogo)
            <img src="{{ $logoPath }}" alt="{{ $companyName }} Logo" class="logo">
        @endif
        <h1>{{ $companyName }}</h1>
        <h2>{{ $title }}</h2>
        <div class="date">Generated on: {{ $date }}</div>
    </div>

    <main>
        <table>
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Shift</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Type</th>
                    <th>Pattern</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->employee->full_name }}</td>
                        <td>{{ $assignment->employee->department->name ?? 'N/A' }}</td>
                        <td>{{ $assignment->shift->name }}</td>
                        <td>{{ $assignment->start_date->format('d/m/Y') }}</td>
                        <td>{{ $assignment->end_date ? $assignment->end_date->format('d/m/Y') : 'Ongoing' }}</td>
                        <td>
                            <span class="status {{ $assignment->is_permanent ? 'permanent' : 'temporary' }}">
                                {{ $assignment->is_permanent ? 'Permanent' : 'Temporary' }}
                            </span>
                        </td>
                        <td>{{ $assignment->rotation_pattern ?? 'N/A' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center;">No shift assignments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary Section -->
        @if(count($assignments) > 0)
            <div class="summary">
                <h3>Assignment Summary</h3>
                <div class="summary-item">
                    <span class="summary-item-label">Total Assignments:</span>
                    <span>{{ count($assignments) }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item-label">Permanent Assignments:</span>
                    <span>{{ $assignments->where('is_permanent', true)->count() }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item-label">Temporary Assignments:</span>
                    <span>{{ $assignments->where('is_permanent', false)->count() }}</span>
                </div>
                <div class="summary-item">
                    <span class="summary-item-label">Active (Ongoing) Assignments:</span>
                    <span>{{ $assignments->whereNull('end_date')->count() }}</span>
                </div>
            </div>
        @endif
    </main>

    <div class="footer">
        <p>© {{ date('Y') }} {{ $companyName }} - All rights reserved</p>
    </div>
</body>
</html>
