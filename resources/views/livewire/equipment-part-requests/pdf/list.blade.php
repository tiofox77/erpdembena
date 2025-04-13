<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Part Requests Report</title>
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
            margin-bottom: 20px;
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
        .filter-info {
            margin-bottom: 15px;
            background-color: #f9f9f9;
            padding: 8px;
            border-radius: 4px;
            font-size: 11px;
        }
        .filter-label {
            font-weight: bold;
            margin-right: 5px;
        }
        .filter-value {
            font-style: italic;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 10px;
        }
        .items-table td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fff4bd;
            color: #856404;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-ordered {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-received {
            background-color: #e0cfef;
            color: #5a3b76;
        }
        .summary {
            margin-top: 20px;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 15px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        .page-break {
            page-break-after: always;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
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
            <div class="document-title">PART REQUESTS REPORT</div>
            <div>Generated on: {{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>
    
    <div class="filter-info">
        <span class="filter-label">Filters Applied:</span>
        <span class="filter-value">
            Search: "{{ $filters['search'] ?: 'None' }}" |
            Status: "{{ $filters['status'] ? ucfirst($filters['status']) : 'All' }}"
        </span>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Reference</th>
                <th>Requested By</th>
                <th>Status</th>
                <th>Created Date</th>
                <th>Items</th>
                <th>Suggested Vendor</th>
                <th>Delivery Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $request)
            <tr>
                <td>{{ $request->reference_number }}</td>
                <td>{{ $request->requester->name ?? 'N/A' }}</td>
                <td class="text-center">
                    <span class="status-badge status-{{ $request->status }}">
                        {{ ucfirst($request->status) }}
                    </span>
                </td>
                <td>{{ $request->created_at->format('d/m/Y') }}</td>
                <td class="text-center">{{ $request->items->count() }}</td>
                <td>{{ $request->suggested_vendor ?: 'N/A' }}</td>
                <td>{{ $request->delivery_date ? $request->delivery_date->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No requests found matching the criteria.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-title">Summary:</div>
        <div>
            <div class="summary-item">Total Requests: <strong>{{ $requests->count() }}</strong></div>
            
            @php
                $pendingCount = $requests->where('status', 'pending')->count();
                $approvedCount = $requests->where('status', 'approved')->count();
                $rejectedCount = $requests->where('status', 'rejected')->count();
                $orderedCount = $requests->where('status', 'ordered')->count();
                $receivedCount = $requests->where('status', 'received')->count();
            @endphp
            
            <div class="summary-item">Pending: <strong>{{ $pendingCount }}</strong></div>
            <div class="summary-item">Approved: <strong>{{ $approvedCount }}</strong></div>
            <div class="summary-item">Rejected: <strong>{{ $rejectedCount }}</strong></div>
            <div class="summary-item">Ordered: <strong>{{ $orderedCount }}</strong></div>
            <div class="summary-item">Received: <strong>{{ $receivedCount }}</strong></div>
        </div>
    </div>

    <div class="footer">
        <p>Generated by {{ auth()->user()->name }} on {{ now()->format('d/m/Y H:i') }}</p>
        <p>{{ $companyName }} &copy; {{ date('Y') }} - All Rights Reserved</p>
    </div>
</body>
</html>
