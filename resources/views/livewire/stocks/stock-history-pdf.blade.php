<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock History Report</title>
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
        .filter-section {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9fafb;
            border-radius: 5px;
        }
        .filter-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #2563eb;
        }
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 5px;
        }
        .filter-label {
            font-weight: bold;
            width: 120px;
            color: #555;
        }
        .filter-value {
            color: #000;
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
        .transaction-in {
            color: #059669;
            font-weight: bold;
        }
        .transaction-out {
            color: #dc2626;
            font-weight: bold;
        }
        .transaction-adjustment {
            color: #6366f1;
            font-weight: bold;
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
        <h1>{{ $reportTitle ?? 'STOCK TRANSACTION HISTORY' }}</h1>
        <p class="subtitle">{{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} System</p>
    </div>

    <div class="filter-section">
        <div class="filter-title">Applied Filters</div>
        @if($filters['search'] || $filters['equipmentId'] || $filters['partId'] || $filters['transactionType'] || $filters['dateFrom'] || $filters['dateTo'])
            @if($filters['search'])
                <div class="filter-row">
                    <div class="filter-label">Search Term:</div>
                    <div class="filter-value">{{ $filters['search'] }}</div>
                </div>
            @endif
            @if($filters['equipmentId'])
                <div class="filter-row">
                    <div class="filter-label">Equipment:</div>
                    <div class="filter-value">{{ $filters['equipmentId'] }}</div>
                </div>
            @endif
            @if($filters['partId'])
                <div class="filter-row">
                    <div class="filter-label">Part:</div>
                    <div class="filter-value">{{ $filters['partId'] }}</div>
                </div>
            @endif
            @if($filters['transactionType'])
                <div class="filter-row">
                    <div class="filter-label">Transaction Type:</div>
                    <div class="filter-value">
                        @switch($filters['transactionType'])
                            @case('stock_in')
                                Stock In
                                @break
                            @case('stock_out')
                                Stock Out
                                @break
                            @case('adjustment')
                                Adjustment
                                @break
                            @default
                                {{ ucfirst($filters['transactionType']) }}
                        @endswitch
                    </div>
                </div>
            @endif
            @if($filters['dateFrom'])
                <div class="filter-row">
                    <div class="filter-label">Date From:</div>
                    <div class="filter-value">{{ $filters['dateFrom'] }}</div>
                </div>
            @endif
            @if($filters['dateTo'])
                <div class="filter-row">
                    <div class="filter-label">Date To:</div>
                    <div class="filter-value">{{ $filters['dateTo'] }}</div>
                </div>
            @endif
        @else
            <div class="filter-row">
                <div class="filter-value">No filters applied - showing all transactions</div>
            </div>
        @endif
    </div>

    <div class="report-info">
        <div class="report-info-row">
            <span class="report-label">Report Generated:</span>
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
                <th>Type</th>
                <th>Part Name</th>
                <th>Part Number</th>
                <th>Equipment</th>
                <th>Quantity</th>
                <th>Unit Cost</th>
                <th>Total Value</th>
                <th>Reference</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalIn = 0;
                $totalOut = 0;
                $totalAdjustment = 0;
                $totalValue = 0;
            @endphp
            
            @foreach ($transactions as $transaction)
                @php
                    $itemValue = $transaction->unit_cost ? $transaction->unit_cost * $transaction->quantity : 0;
                    $totalValue += $itemValue;
                    
                    if ($transaction->type == 'stock_in') {
                        $totalIn += $transaction->quantity;
                    } elseif ($transaction->type == 'stock_out') {
                        $totalOut += $transaction->quantity;
                    } elseif ($transaction->type == 'adjustment') {
                        $totalAdjustment += $transaction->quantity;
                    }
                    
                    $typeClass = '';
                    $typeLabel = '';
                    
                    switch ($transaction->type) {
                        case 'stock_in':
                            $typeClass = 'transaction-in';
                            $typeLabel = 'IN';
                            break;
                        case 'stock_out':
                            $typeClass = 'transaction-out';
                            $typeLabel = 'OUT';
                            break;
                        case 'adjustment':
                            $typeClass = 'transaction-adjustment';
                            $typeLabel = 'ADJ';
                            break;
                        default:
                            $typeLabel = strtoupper($transaction->type);
                    }
                    
                    // Get reference (invoice number for stock in, work order for stock out)
                    $reference = '';
                    if ($transaction->type == 'stock_in' && $transaction->invoice_number) {
                        $reference = 'INV: ' . $transaction->invoice_number;
                    } elseif ($transaction->type == 'stock_out' && $transaction->work_order_id) {
                        $reference = 'WO: ' . $transaction->work_order_id;
                    }
                @endphp
                <tr class="zebra-row">
                    <td>{{ $transaction->id }}</td>
                    <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                    <td class="{{ $typeClass }}">{{ $typeLabel }}</td>
                    <td>{{ $transaction->part->name ?? 'N/A' }}</td>
                    <td>{{ $transaction->part->part_number ?? 'N/A' }}</td>
                    <td>{{ $transaction->part->equipment->name ?? 'N/A' }}</td>
                    <td class="text-right">{{ $transaction->quantity }}</td>
                    <td class="text-right">{{ $transaction->unit_cost ? number_format($transaction->unit_cost, 2) : 'N/A' }}</td>
                    <td class="text-right">{{ $transaction->unit_cost ? number_format($itemValue, 2) : 'N/A' }}</td>
                    <td>{{ $reference ?: 'N/A' }}</td>
                    <td>{{ $transaction->createdBy->name ?? 'System' }}</td>
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
                <th>Total Stock In</th>
                <td class="transaction-in">{{ $totalIn }}</td>
            </tr>
            <tr>
                <th>Total Stock Out</th>
                <td class="transaction-out">{{ $totalOut }}</td>
            </tr>
            @if($totalAdjustment != 0)
            <tr>
                <th>Total Adjustments</th>
                <td class="transaction-adjustment">{{ $totalAdjustment }}</td>
            </tr>
            @endif
            <tr>
                <th>Total Value</th>
                <td>{{ number_format($totalValue, 2) }}</td>
            </tr>
            <tr>
                <th>Net Stock Change</th>
                <td>{{ $totalIn - $totalOut + $totalAdjustment }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>This report was generated automatically by {{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} on {{ now()->format('d/m/Y H:i') }}</p>
        <p> {{ date('Y') }} {{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} - All rights reserved</p>
    </div>
</body>
</html>
