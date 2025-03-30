<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Out Receipt</title>
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
        .info-section {
            margin-bottom: 20px;
        }
        .info-heading {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #2563eb;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-row {
            display: flex;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        .info-value {
            flex: 1;
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
            font-size: 12px;
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
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 9999px;
            background-color: #fee2e2;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="header">
        <!-- Replace with your company logo -->
        <div style="text-align: center;">
            <h1>STOCK OUT RECEIPT</h1>
            <p class="subtitle">{{ config('app.name', 'ERP System') }}</p>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between;">
        <div class="info-section" style="width: 48%;">
            <div class="info-heading">Transaction Details</div>
            <div class="info-row">
                <div class="info-label">Reference Number:</div>
                <div class="info-value">{{ $stockOut->reference_number ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date:</div>
                <div class="info-value">{{ date('d M Y', strtotime($stockOut->date)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Reason:</div>
                <div class="info-value">{{ $stockOut->reason }}</div>
            </div>
        </div>
        
        <div class="info-section" style="width: 48%;">
            <div class="info-heading">User Details</div>
            <div class="info-row">
                <div class="info-label">Issued By:</div>
                <div class="info-value">
                    {{ $stockOut->user->first_name ?? '' }} {{ $stockOut->user->last_name ?? '' }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">ID:</div>
                <div class="info-value">{{ $stockOut->user->id ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Created At:</div>
                <div class="info-value">{{ $stockOut->created_at->format('d M Y H:i') }}</div>
            </div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-heading">Parts Information</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 30%;">Part Name</th>
                <th style="width: 20%;">Part Number</th>
                <th style="width: 20%;">BAC Code</th>
                <th style="width: 10%;">Quantity</th>
                <th style="width: 15%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($stockOut->items as $index => $item)
                @php 
                    $subtotal = $item->quantity; 
                    $total += $subtotal;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->equipmentPart->name ?? 'Part Removed' }}</td>
                    <td>{{ $item->equipmentPart->part_number ?? 'N/A' }}</td>
                    <td>{{ $item->equipmentPart->bac_code ?? 'N/A' }}</td>
                    <td>
                        <span class="badge">{{ $item->quantity }}</span>
                    </td>
                    <td>{{ $subtotal }} units</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">Total Items:</td>
                <td colspan="2">{{ $total }} units</td>
            </tr>
        </tbody>
    </table>

    @if($stockOut->notes)
    <div class="info-section">
        <div class="info-heading">Notes</div>
        <p>{{ $stockOut->notes }}</p>
    </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Authorized Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Received By</div>
        </div>
    </div>

    <div class="footer">
        <p>This is an official receipt of {{ config('app.name', 'ERP System') }}</p>
        <p>Generated on {{ now()->format('d M Y H:i:s') }}</p>
    </div>
</body>
</html>
