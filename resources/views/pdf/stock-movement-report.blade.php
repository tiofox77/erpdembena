<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.stock_movement_report') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
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
            max-height: 60px;
            max-width: 200px;
        }
        .document-title {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .filters-info {
            margin-bottom: 10px;
            font-size: 9px;
            background-color: #f9f9f9;
            padding: 5px;
            border-radius: 3px;
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
            font-weight: bold;
            width: 20%;
            background-color: #f5f5f5;
            padding: 5px;
            font-size: 9px;
        }
        .document-info td {
            padding: 5px;
            font-size: 9px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9px;
        }
        .items-table th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 5px;
            font-weight: bold;
            text-align: left;
            font-size: 9px;
        }
        .items-table td {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 8px;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .totals {
            margin-top: 20px;
            text-align: right;
            font-size: 13px;
            margin-bottom: 30px;
        }
        .totals-table {
            width: 350px;
            border-collapse: collapse;
            margin-left: auto;
            margin-bottom: 40px;
        }
        .totals-table th {
            text-align: left;
            padding: 5px;
        }
        .totals-table td {
            text-align: right;
            padding: 5px;
            font-weight: bold;
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
                <h2 style="margin: 0; padding: 0; font-size: 14px;">{{ $companyName }}</h2>
                <p style="margin: 2px 0; font-size: 9px;">{{ $companyAddress }}</p>
                <p style="margin: 2px 0; font-size: 9px;">Tel: {{ $companyPhone }} | Email: {{ $companyEmail }}</p>
                <p style="margin: 2px 0; font-size: 9px;">CNPJ: {{ $companyTaxId }} | {{ $companyWebsite }}</p>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <div class="document-title">{{ __('messages.stock_movement_report') }}</div>
            <div>{{ __('messages.report_date') }}: {{ \Carbon\Carbon::parse($date)->format('d/m/Y H:i') }}</div>
            <div>{{ __('messages.generated_by') }}: {{ $user->name }}</div>
        </div>
    </div>
    
    <!-- Filtros aplicados -->
    <div class="document-info">
        <h2>{{ __('messages.active_filters') }}</h2>
        <table>
            @if($filters['product'])
            <tr>
                <th>{{ __('messages.product') }}:</th>
                <td>{{ $filters['product']->name }} ({{ $filters['product']->sku }})</td>
            </tr>
            @endif
            
            @if($filters['location'])
            <tr>
                <th>{{ __('messages.location') }}:</th>
                <td>{{ $filters['location']->name }}</td>
            </tr>
            @endif
            
            @if($filters['transactionType'])
            <tr>
                <th>{{ __('messages.transaction_type') }}:</th>
                <td>{{ __('messages.' . $filters['transactionType']) }}</td>
            </tr>
            @endif
            
            @if($filters['startDate'] && $filters['endDate'])
            <tr>
                <th>{{ __('messages.date_range') }}:</th>
                <td>{{ \Carbon\Carbon::parse($filters['startDate'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['endDate'])->format('d/m/Y') }}</td>
            </tr>
            @elseif($filters['startDate'])
            <tr>
                <th>{{ __('messages.date_from') }}:</th>
                <td>{{ \Carbon\Carbon::parse($filters['startDate'])->format('d/m/Y') }}</td>
            </tr>
            @elseif($filters['endDate'])
            <tr>
                <th>{{ __('messages.date_to') }}:</th>
                <td>{{ \Carbon\Carbon::parse($filters['endDate'])->format('d/m/Y') }}</td>
            </tr>
            @endif
            
            @if($filters['search'])
            <tr>
                <th>{{ __('messages.search_term') }}:</th>
                <td>{{ $filters['search'] }}</td>
            </tr>
            @endif
        </table>
    </div>
    
    <!-- Transações -->
    <h2>{{ __('messages.stock_movement_transactions') }}</h2>
    <table class="items-table">
        <thead>
            <tr>
                <th>{{ __('messages.date') }}</th>
                <th>{{ __('messages.transaction_number') }}</th>
                <th>{{ __('messages.transaction_type') }}</th>
                <th>{{ __('messages.product') }}</th>
                <th>{{ __('messages.source_location') }}</th>
                <th>{{ __('messages.destination_location') }}</th>
                <th>{{ __('messages.quantity') }}</th>
                <th>{{ __('messages.unit_cost') }}</th>
                <th>{{ __('messages.total_cost') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $transaction->transaction_number }}</td>
                    <td>{{ __('messages.' . $transaction->transaction_type) }}</td>
                    <td>{{ $transaction->product ? $transaction->product->name . ' (' . $transaction->product->sku . ')' : '' }}</td>
                    <td>{{ $transaction->sourceLocation ? $transaction->sourceLocation->name : '-' }}</td>
                    <td>{{ $transaction->destinationLocation ? $transaction->destinationLocation->name : '-' }}</td>
                    <td align="right">{{ number_format($transaction->quantity, 2) }}</td>
                    <td align="right">{{ number_format($transaction->unit_cost, 2) }}</td>
                    <td align="right">{{ number_format($transaction->total_cost, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" align="center">{{ __('messages.no_records_found') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Totais -->
    <div class="totals">
        <table class="totals-table">
            <tr>
                <th>{{ __('messages.total_stock_in') }}:</th>
                <td>{{ number_format($totalIn, 2) }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.total_stock_out') }}:</th>
                <td>{{ number_format($totalOut, 2) }}</td>
            </tr>
            <tr>
                <th>{{ __('messages.net_movement') }}:</th>
                <td>{{ number_format($netMovement, 2) }}</td>
            </tr>
        </table>
    </div>
    
    <div style="margin-top: 20px;" class="document-info">
        <table>
            <tr>
                <th>{{ __('messages.notes') }}:</th>
                <td>{{ __('messages.document_for_internal_use') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer" style="margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; font-size: 10px; color: #6b7280;">
        <p>{{ $companyName }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
        <p>{{ __('messages.stock_movement_report') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
