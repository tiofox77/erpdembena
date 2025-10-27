<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock In Receipt</title>
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
            color: #555;
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
            padding: 8px;
            text-align: left;
            font-size: 12px;
            border-bottom: 1px solid #ddd;
            color: #374151;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-size: 11px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        .total-section {
            margin-top: 20px;
            text-align: right;
        }
        .total-row {
            margin-bottom: 5px;
            font-size: 12px;
        }
        .total-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
            text-align: right;
            margin-right: 10px;
        }
        .total-value {
            font-weight: bold;
            display: inline-block;
            width: 100px;
            text-align: right;
        }
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 45%;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #ddd;
            margin-top: 50px;
            padding-top: 5px;
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
        <h1>STOCK IN RECEIPT</h1>
        <p class="subtitle">{{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} System</p>
    </div>

    <div class="info-section">
        <h2 class="info-heading">Transaction Information</h2>
        <div class="info-row">
            <div class="info-label">Transaction ID:</div>
            <div class="info-value">{{ $transaction->id }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Date:</div>
            <div class="info-value">{{ $transaction->transaction_date->format('d/m/Y H:i') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Supplier:</div>
            <div class="info-value">{{ $transaction->supplier ?: 'Not specified' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Invoice Number:</div>
            <div class="info-value">{{ $transaction->invoice_number ?: 'Not specified' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Received By:</div>
            <div class="info-value">{{ $transaction->createdBy->name ?? 'Unknown' }}</div>
        </div>
    </div>

    <div class="info-section">
        <h2 class="info-heading">Part Information</h2>
        <div class="info-row">
            <div class="info-label">Part Name:</div>
            <div class="info-value">{{ $transaction->part->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Part Number:</div>
            <div class="info-value">{{ $transaction->part->part_number ?: 'Not specified' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Barcode:</div>
            <div class="info-value">{{ $transaction->part->bar_code ?: 'Not specified' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Equipment:</div>
            <div class="info-value">{{ $transaction->part->equipment->name ?? 'Not assigned' }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Quantity</th>
                <th>Unit Cost</th>
                <th>Total Value</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $transaction->quantity }}</td>
                <td>{{ $transaction->unit_cost ? number_format($transaction->unit_cost, 2) : 'Not specified' }}</td>
                <td>{{ $transaction->unit_cost ? number_format($transaction->unit_cost * $transaction->quantity, 2) : 'Not calculated' }}</td>
            </tr>
        </tbody>
    </table>

    @if($transaction->notes)
    <div class="info-section">
        <h2 class="info-heading">Notes</h2>
        <p>{{ $transaction->notes }}</p>
    </div>
    @endif

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">Received By</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Authorized By</div>
        </div>
    </div>

    <div class="footer">
        <p>This document was generated automatically by {{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} on {{ now()->format('d/m/Y H:i') }}</p>
        <p> {{ date('Y') }} {{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} - All rights reserved</p>
    </div>
</body>
</html>
