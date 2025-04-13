<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Part Request - {{ $request->reference_number }}</title>
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
        .document-info {
            margin-bottom: 20px;
        }
        .document-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .document-info th {
            text-align: left;
            padding: 5px;
            width: 30%;
            background-color: #f5f5f5;
        }
        .document-info td {
            padding: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 11px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
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
        .remarks {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .remarks-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .images-section {
            margin-bottom: 20px;
        }
        .image-gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .image-container {
            border: 1px solid #ddd;
            padding: 5px;
            width: 30%;
        }
        .image-container img {
            max-width: 100%;
            height: auto;
        }
        .image-caption {
            font-size: 10px;
            text-align: center;
            margin-top: 5px;
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
            <div class="document-title">PART REQUEST DETAILS</div>
            <div>Reference Number: {{ $request->reference_number }}</div>
        </div>
    </div>
    
    <div class="document-info">
        <table>
            <tr>
                <th>Reference Number:</th>
                <td>{{ $request->reference_number }}</td>
                <th>Status:</th>
                <td>
                    <span class="status-badge status-{{ $request->status }}">
                        {{ ucfirst($request->status) }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Requested By:</th>
                <td>{{ $request->requester->name ?? 'N/A' }}</td>
                <th>Created Date:</th>
                <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th>Suggested Vendor:</th>
                <td>{{ $request->suggested_vendor ?: 'N/A' }}</td>
                <th>Delivery Date:</th>
                <td>{{ $request->delivery_date ? $request->delivery_date->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            @if($request->status === 'approved')
            <tr>
                <th>Approved By:</th>
                <td>{{ $request->approver->name ?? 'N/A' }}</td>
                <th>Approved At:</th>
                <td>{{ $request->approved_at ? $request->approved_at->format('d/m/Y H:i') : 'N/A' }}</td>
            </tr>
            @endif
        </table>
    </div>

    @if($request->remarks)
    <div class="remarks">
        <div class="remarks-title">Remarks:</div>
        <div>{{ $request->remarks }}</div>
    </div>
    @endif

    <h3>Requested Items</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>Part Number</th>
                <th>Part Name</th>
                <th>Supplier Reference</th>
                <th>Quantity</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($request->items as $item)
            <tr>
                <td>{{ $item->part->part_number ?? 'N/A' }}</td>
                <td>{{ $item->part->name ?? 'N/A' }}</td>
                <td>{{ $item->supplier_reference ?: 'N/A' }}</td>
                <td>{{ $item->quantity_required }}</td>
                <td>{{ $item->unit }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($request->images->count() > 0)
    <div class="images-section">
        <h3>Images ({{ $request->images->count() }})</h3>
        <div class="image-gallery">
            @foreach($request->images as $image)
            <div class="image-container">
                <img src="{{ public_path('storage/' . $image->image_path) }}" alt="Request Image">
                <div class="image-caption">
                    {{ $image->caption ?: 'No caption' }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="footer">
        <p>Generated by {{ auth()->user()->name }} on {{ now()->format('d/m/Y H:i') }}</p>
        <p>{{ $companyName }} &copy; {{ date('Y') }} - All Rights Reserved</p>
    </div>
</body>
</html>
