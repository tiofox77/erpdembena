<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transferência de Armazém - {{ $transfer->request_number }}</title>
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
        .status-pending_approval {
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
        .status-in_transit {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
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
        h3 {
            margin-top: 10px; 
            margin-bottom: 5px; 
            font-size: 14px; 
            font-weight: bold;
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
        .alert-warning {
            background-color: #fff4bd;
            color: #856404;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            width: 40%;
            text-align: center;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }
        .text-center {
            text-align: center;
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
                    <h3>{{ __('messages.warehouse_info') }}</h3>
                    <div class="document-info" style="margin-top: 0;">
                        <table style="width: 100%;">
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px; width: 30%;">{{ __('messages.source_warehouse') }}:</th>
                                <td style="font-size: 9px;">{{ $transfer->sourceLocation->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px;">{{ __('messages.destination_warehouse') }}:</th>
                                <td style="font-size: 9px;">{{ $transfer->destinationLocation->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px;">{{ __('messages.responsible_person') }}:</th>
                                <td style="font-size: 9px;">{{ $transfer->requestedBy->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th style="text-align: left; padding-right: 5px; font-size: 9px;">{{ __('messages.contact') }}:</th>
                                <td style="font-size: 9px;">{{ $transfer->requestedBy->email ?? '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr style="border-top: 1px solid #ddd;">
                <td colspan="2" style="text-align: center; padding-top: 10px;">
                    <div class="document-title" style="margin: 0; font-size: 16px;">{{ __('messages.warehouse_transfer_request') }}</div>
                    <div style="font-size: 11px;">{{ __('messages.request_number') }}: {{ $transfer->request_number }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="document-info" style="margin-top: 10px;">
        <h3>{{ __('messages.transfer_details') }}</h3>
        <table style="width: 100%; font-size: 9px; border-collapse: collapse;">
            <tr>
                <th style="text-align: left; padding-right: 5px; white-space: nowrap;">{{ __('messages.request_date') }}:</th>
                <td style="padding-right: 15px; white-space: nowrap;">{{ date('d/m/Y', strtotime($transfer->requested_date)) }}</td>
                
                <th style="text-align: left; padding-right: 5px; white-space: nowrap;">{{ __('messages.required_date') }}:</th>
                <td style="padding-right: 15px; white-space: nowrap;">
                    {{ $transfer->required_date ? date('d/m/Y', strtotime($transfer->required_date)) : '-' }}
                </td>
                
                <th style="text-align: left; padding-right: 5px; white-space: nowrap;">{{ __('messages.status') }}:</th>
                <td style="white-space: nowrap;">
                    <span class="status-badge status-{{ $transfer->status }}" style="font-size: 9px; padding: 2px 4px;">
                        {{ __('messages.status_'.$transfer->status) }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    @if($transfer->notes)
    <div class="remarks">
        <div class="remarks-title">{{ __('messages.notes') }}:</div>
        <p>{{ $transfer->notes }}</p>
    </div>
    @endif

    <h3>{{ __('messages.transfer_items') }}</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">{{ __('messages.product') }}</th>
                <th style="width: 20%;">{{ __('messages.sku') }}</th>
                <th style="width: 15%;" class="text-right">{{ __('messages.quantity') }}</th>
                <th style="width: 15%;" class="text-right">{{ __('messages.unit') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfer->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ optional($item->product)->name ?? 'Product #'.$item->product_id }}</td>
                    <td>{{ optional($item->product)->sku ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->quantity_requested ?? 0, 2, ',', '.') }}</td>
                    <td class="text-right">{{ $item->unit ?? 'und' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($transfer->approvedBy || $transfer->status == 'approved' || $transfer->status == 'in_transit' || $transfer->status == 'completed')
    <div class="document-info">
        <h3>{{ __('messages.approval_details') }}</h3>
        <table style="width: 100%; font-size: 9px; border-collapse: collapse;">
            <tr>
                <th style="text-align: left; padding-right: 5px; white-space: nowrap;">{{ __('messages.approved_by') }}:</th>
                <td style="padding-right: 15px; white-space: nowrap;">{{ $transfer->approvedBy->name ?? '-' }}</td>
                
                <th style="text-align: left; padding-right: 5px; white-space: nowrap;">{{ __('messages.approval_date') }}:</th>
                <td style="padding-right: 15px; white-space: nowrap;">
                    {{ $transfer->approval_date ? date('d/m/Y H:i', strtotime($transfer->approval_date)) : '-' }}
                </td>
            </tr>
            @if($transfer->approval_notes)
            <tr>
                <th style="text-align: left; padding-right: 5px; white-space: nowrap;">{{ __('messages.approval_notes') }}:</th>
                <td colspan="3">{{ $transfer->approval_notes }}</td>
            </tr>
            @endif
        </table>
    </div>
    @endif

    <!-- Signature Section -->
    <table style="width: 100%; margin-top: 80px; border-collapse: collapse;">
        <tr>
            <td style="width: 45%; text-align: center; padding: 10px;">
                <div style="border-top: 1px solid #000; margin-bottom: 5px;"></div>
                <p>{{ __('messages.source_warehouse_signature') }}</p>
                <p>{{ $transfer->sourceLocation->name ?? '' }}</p>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%; text-align: center; padding: 10px;">
                <div style="border-top: 1px solid #000; margin-bottom: 5px;"></div>
                <p>{{ __('messages.destination_warehouse_signature') }}</p>
                <p>{{ $transfer->destinationLocation->name ?? '' }}</p>
            </td>
        </tr>
    </table>

    <div class="footer">
        <p>{{ __('messages.warehouse_transfer_generated') }} {{ date('d/m/Y H:i:s') }}</p>
        <p>{{ __('messages.document_for_internal_use') }}</p>
    </div>
</body>
</html>
