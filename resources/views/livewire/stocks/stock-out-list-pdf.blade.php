<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Outs Report</title>
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
            max-height: 60px;
            display: block;
            margin: 0 auto 15px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 8px;
            font-size: 12px;
            font-weight: bold;
            color: #374151;
            border-bottom: 1px solid #ddd;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 9999px;
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .page-break {
            page-break-after: always;
        }
        .report-summary {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9fafb;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div style="text-align: center;">
            <h1>STOCK OUTS REPORT</h1>
            <p class="subtitle">{{ config('app.name', 'ERP System') }}</p>
        </div>
    </div>

    <div class="report-summary">
        <p><strong>Report Date:</strong> {{ now()->format('d M Y') }}</p>
        <p><strong>Total Records:</strong> {{ count($stockOuts) }}</p>
        <p><strong>Total Items Issued:</strong> {{ $stockOuts->sum(function($stockOut) { return $stockOut->items->sum('quantity'); }) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Reference</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 15%;">User</th>
                <th style="width: 25%;">Parts</th>
                <th style="width: 10%;">Qty</th>
                <th style="width: 20%;">Reason</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockOuts as $index => $stockOut)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $stockOut->reference_number }}</td>
                    <td>{{ date('d/m/Y', strtotime($stockOut->date)) }}</td>
                    <td>{{ $stockOut->user->first_name ?? '' }} {{ $stockOut->user->last_name ?? '' }}</td>
                    <td>
                        @foreach($stockOut->items as $item)
                            <div style="margin-bottom: 3px;">
                                {{ $item->equipmentPart->name ?? 'Part Removed' }}
                                <small>({{ $item->equipmentPart->part_number ?? 'N/A' }})</small>
                            </div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($stockOut->items as $item)
                            <div style="margin-bottom: 3px;">
                                <span class="badge">{{ $item->quantity }}</span>
                            </div>
                        @endforeach
                    </td>
                    <td>{{ \Illuminate\Support\Str::limit($stockOut->reason, 30) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is an official report of {{ config('app.name', 'ERP System') }}</p>
        <p>Generated on {{ now()->format('d M Y H:i:s') }}</p>
        <p>Page 1 of 1</p>
    </div>
</body>
</html>
