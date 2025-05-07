<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
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
            <div>{{ __('messages.generated_at') }}: {{ $date }}</div>
        </div>
    </div>

    <main>
        <table class="items-table">
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

    <div style="margin-top: 20px;" class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.notes') }}:</th>
                <td>{{ __('messages.shifts_report_notes') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer" style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; font-size: 10px; color: #6b7280;">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
        <p>{{ __('messages.report_generated_by') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
