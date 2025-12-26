<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Funcionários</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2563eb;
        }
        
        .header h1 {
            font-size: 18px;
            color: #1e40af;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 14px;
            color: #64748b;
            font-weight: normal;
        }
        
        .meta-info {
            margin-bottom: 15px;
            font-size: 9px;
            color: #64748b;
        }
        
        .meta-info span {
            margin-right: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table thead {
            background-color: #f1f5f9;
        }
        
        table th {
            padding: 8px 6px;
            text-align: left;
            font-size: 9px;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 2px solid #cbd5e1;
            text-transform: uppercase;
        }
        
        table td {
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        table tbody tr:hover {
            background-color: #f1f5f9;
        }
        
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 600;
        }
        
        .badge-active {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .badge-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .badge-male {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .badge-female {
            background-color: #fce7f3;
            color: #9f1239;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
            padding: 10px 0;
            border-top: 1px solid #e2e8f0;
        }
        
        .page-number:after {
            content: counter(page);
        }
        
        .total-employees {
            background-color: #eff6ff;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-weight: 600;
            color: #1e40af;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <h2>{{ __('messages.employees_list') }}</h2>
    </div>

    {{-- Meta Info --}}
    <div class="meta-info">
        <span><strong>{{ __('messages.generated_at') }}:</strong> {{ $generatedAt }}</span>
        <span><strong>{{ __('messages.generated_by') }}:</strong> {{ $generatedBy }}</span>
    </div>

    {{-- Total Count --}}
    <div class="total-employees">
        {{ __('messages.total_employees') }}: {{ $employees->count() }}
    </div>

    {{-- Employees Table --}}
    <table>
        <thead>
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 25%">{{ __('messages.full_name') }}</th>
                <th style="width: 20%">{{ __('messages.department') }}</th>
                <th style="width: 20%">{{ __('messages.position') }}</th>
                <th style="width: 10%">{{ __('messages.gender') }}</th>
                <th style="width: 10%">{{ __('messages.phone') }}</th>
                <th style="width: 10%">{{ __('messages.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $employee)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $employee->full_name }}</strong>
                    @if($employee->email)
                        <br><span style="color: #64748b; font-size: 8px;">{{ $employee->email }}</span>
                    @endif
                </td>
                <td>{{ $employee->department->name ?? '-' }}</td>
                <td>{{ $employee->position->name ?? '-' }}</td>
                <td>
                    @if($employee->gender === 'M')
                        <span class="badge badge-male">{{ __('messages.male') }}</span>
                    @elseif($employee->gender === 'F')
                        <span class="badge badge-female">{{ __('messages.female') }}</span>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $employee->phone ?? '-' }}</td>
                <td>
                    @if($employee->employment_status === 'active')
                        <span class="badge badge-active">{{ __('messages.active') }}</span>
                    @else
                        <span class="badge badge-inactive">{{ __('messages.inactive') }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>{{ __('messages.page') }} <span class="page-number"></span> | {{ config('app.name') }} - {{ now()->format('Y') }}</p>
    </div>
</body>
</html>
