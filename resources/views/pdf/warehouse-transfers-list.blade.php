<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.warehouse_transfer_list') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: left;
            margin-bottom: 10px;
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
        }
        .filters {
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
            padding: 10px;
            background-color: #f9fafb;
            border-radius: 5px;
            font-size: 8pt;
        }
        .filters-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .filter-item {
            display: inline-block;
            margin-right: 15px;
            margin-bottom: 5px;
        }
        .filter-label {
            font-weight: bold;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            font-size: 8pt;
        }
        td {
            border-bottom: 1px solid #e5e7eb;
            padding: 6px 8px;
            font-size: 8pt;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .priority-low {
            color: #10b981;
        }
        .priority-normal {
            color: #3b82f6;
        }
        .priority-high {
            color: #f59e0b;
        }
        .priority-urgent {
            color: #ef4444;
            font-weight: bold;
        }
        .status-draft {
            color: #6b7280;
        }
        .status-submitted {
            color: #3b82f6;
        }
        .status-approved {
            color: #10b981;
        }
        .status-rejected {
            color: #ef4444;
        }
        .status-processing {
            color: #8b5cf6;
        }
        .status-completed {
            color: #059669;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 8pt;
            text-align: center;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
            <td style="width: 55%; vertical-align: top; padding-right: 10px;">
                    @php
                        $logoPath = \App\Models\Setting::get('company_logo');
                        $logoFullPath = $logoPath ? 'storage/' . $logoPath : 'img/logo.png';
                        $logoUrl = asset($logoFullPath);
                        $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
                        $companyAddress = \App\Models\Setting::get('company_address', '');
                        $companyPhone = \App\Models\Setting::get('company_phone', '');
                        $companyEmail = \App\Models\Setting::get('company_email', '');
                        $companyWebsite = \App\Models\Setting::get('company_website', '');
                        $companyTaxId = \App\Models\Setting::get('company_tax_id', '');
                    @endphp
                    <div style="display: flex; align-items: flex-start;">
                        <div style="margin-right: 20px; flex-shrink: 0;">
                            <img src="{{ $logoUrl }}" alt="{{ $companyName }} Logo" class="logo">
                        </div>
                        <div>
                            <h2 style="margin: 0; padding: 0; font-size: 16px;">{{ $companyName }}</h2>
                            <p style="margin: 2px 0; font-size: 9px;">{{ $companyAddress }}</p>
                            <p style="margin: 2px 0; font-size: 9px;">Tel: {{ $companyPhone }} | Email: {{ $companyEmail }}</p>
                            <p style="margin: 2px 0; font-size: 9px;">VAT/NIF: {{ $companyTaxId }} | {{ $companyWebsite }}</p>
                        </div>
                    </div>
                </td>
                <td style="width: 45%; vertical-align: top; padding-left: 10px; padding-top: 6%;">
                    <h3 class="document-title">{{ __('messages.warehouse_transfer_list') }}</h3>
                    <div class="document-info" style="margin-top: 0;">
                        <table style="width: 100%;">
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px; width: 30%;">{{ __('messages.date_generated') }}:</th>
                                <td style="font-size: 9px;">{{ date('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px; width: 30%;">{{ __('messages.total_transfers') }}:</th>
                                <td style="font-size: 9px;">{{ $transfers->count() }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Seção de Filtros -->
    <div class="filters">
        <div class="filters-title">{{ __('messages.applied_filters') }}:</div>
        @if($filters['search'])
            <div class="filter-item">
                <span class="filter-label">{{ __('messages.search') }}:</span> {{ $filters['search'] }}
            </div>
        @endif
        @if($filters['status'])
            <div class="filter-item">
                <span class="filter-label">{{ __('messages.status') }}:</span> {{ __('messages.status_'.$filters['status']) }}
            </div>
        @endif
        @if($filters['priority'])
            <div class="filter-item">
                <span class="filter-label">{{ __('messages.priority') }}:</span> {{ __('messages.priority_'.$filters['priority']) }}
            </div>
        @endif
        @if($filters['dateFrom'])
            <div class="filter-item">
                <span class="filter-label">{{ __('messages.date_from') }}:</span> {{ date('d/m/Y', strtotime($filters['dateFrom'])) }}
            </div>
        @endif
        @if($filters['dateTo'])
            <div class="filter-item">
                <span class="filter-label">{{ __('messages.date_to') }}:</span> {{ date('d/m/Y', strtotime($filters['dateTo'])) }}
            </div>
        @endif
    </div>

    <!-- Tabela de Transferências -->
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.request_number') }}</th>
                <th>{{ __('messages.requested_date') }}</th>
                <th>{{ __('messages.required_date') }}</th>
                <th>{{ __('messages.source_warehouse') }}</th>
                <th>{{ __('messages.destination_warehouse') }}</th>
                <th>{{ __('messages.status') }}</th>
                <th>{{ __('messages.priority') }}</th>
                <th>{{ __('messages.requested_by') }}</th>
                <th>{{ __('messages.items') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transfers as $transfer)
                <tr>
                    <td>{{ $transfer->request_number }}</td>
                    <td>{{ date('d/m/Y', strtotime($transfer->requested_date)) }}</td>
                    <td>{{ $transfer->required_date ? date('d/m/Y', strtotime($transfer->required_date)) : '-' }}</td>
                    <td>{{ $transfer->sourceLocation->name }}</td>
                    <td>{{ $transfer->destinationLocation->name }}</td>
                    <td class="status-{{ $transfer->status }}">{{ __('messages.status_' . $transfer->status) }}</td>
                    <td class="priority-{{ $transfer->priority }}">{{ __('messages.priority_' . $transfer->priority) }}</td>
                    <td>{{ $transfer->requestedBy->name }}</td>
                    <td>{{ $transfer->items->count() }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">{{ __('messages.no_transfers_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>{{ __('messages.warehouse_transfer_generated') }} {{ date('d/m/Y H:i:s') }}</p>
        <p>{{ __('messages.document_for_internal_use') }}</p>
    </div>
</body>
</html>
