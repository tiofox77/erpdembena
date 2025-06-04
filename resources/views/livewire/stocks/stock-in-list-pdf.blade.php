<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock In Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .logo {
            max-height: 70px;
            max-width: 220px;
            display: block;
            margin: 0 auto 15px;
            object-fit: contain;
        }
        h1 {
            font-size: 22px;
            margin: 0;
            color: #2563eb;
        }
        .subtitle {
            font-size: 14px;
            margin: 5px 0 0;
            color: #666;
        }
        .report-info {
            margin-bottom: 20px;
        }
        .report-info-row {
            margin-bottom: 5px;
        }
        .report-label {
            font-weight: bold;
            display: inline-block;
            margin-right: 10px;
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }
        th {
            background-color: #f3f4f6;
            padding: 6px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            color: #374151;
            font-weight: bold;
        }
        td {
            padding: 6px;
            border-bottom: 1px solid #eee;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        .summary-section {
            margin-top: 20px;
            border-top: 1px dashed #ddd;
            padding-top: 10px;
        }
        .summary-heading {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2563eb;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td {
            padding: 5px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f8fafc;
        }
        .text-right {
            text-align: right;
        }
        .zebra-row:nth-child(even) {
            background-color: #f9fafb;
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- Company Logo -->
        @php
            $logoPath = \App\Models\Setting::get('company_logo');
            $logoFullPath = $logoPath ? public_path('storage/' . $logoPath) : public_path('img/logo.png');
        @endphp
        <img src="{{ $logoFullPath }}" alt="{{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} Logo" class="logo">
        <h1>STOCK IN REPORT</h1>
        <p class="subtitle">{{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} System</p>
    </div>

    <div class="report-info">
        <div class="report-info-row">
            <span class="report-label">Report Date:</span>
            {{ now()->format('d/m/Y H:i') }}
        </div>
        <div class="report-info-row">
            <span class="report-label">Total Transactions:</span>
            {{ $transactions->count() }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Part Name</th>
                <th>Part Number</th>
                <th>Equipment</th>
                <th>Quantity</th>
                <th>Unit Cost</th>
                <th>Total Value</th>
                <th>Supplier</th>
                <th>Invoice #</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalQuantity = 0;
                $totalValue = 0;
            @endphp
            
            @foreach ($transactions as $transaction)
                @php
                    $totalQuantity += $transaction->quantity;
                    $itemValue = $transaction->unit_cost ? $transaction->unit_cost * $transaction->quantity : 0;
                    $totalValue += $itemValue;
                @endphp
                <tr class="zebra-row">
                    <td>{{ $transaction->id }}</td>
                    <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $transaction->part->name }}</td>
                    <td>{{ $transaction->part->part_number ?: 'N/A' }}</td>
                    <td>{{ $transaction->part->equipment->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ $transaction->quantity }}</td>
                    <td class="text-right">{{ $transaction->unit_cost ? number_format($transaction->unit_cost, 2) : 'N/A' }}</td>
                    <td class="text-right">{{ $transaction->unit_cost ? number_format($itemValue, 2) : 'N/A' }}</td>
                    <td>{{ $transaction->supplier ?: 'N/A' }}</td>
                    <td>{{ $transaction->invoice_number ?: 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-section">
        <h2 class="summary-heading">Summary</h2>
        <table class="summary-table">
            <tr>
                <th>Total Transactions</th>
                <td>{{ $transactions->count() }}</td>
            </tr>
            <tr>
                <th>Total Quantity</th>
                <td>{{ $totalQuantity }}</td>
            </tr>
            <tr>
                <th>Total Value</th>
                <td>{{ number_format($totalValue, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This report was generated automatically by {{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} on {{ now()->format('d/m/Y H:i') }}</p>
        <p> {{ date('Y') }} {{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} - All rights reserved</p>
    </div>
</body>
</html>
