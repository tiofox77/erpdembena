<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ __('messages.shipping_notes_report') }}</title>
    <style>
        @font-face {
            font-family: 'FontAwesome';
            src: url('{{ public_path('vendor/fontawesome-free/webfonts/fa-solid-900.ttf') }}');
            font-weight: normal;
            font-style: normal;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .header h2 {
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 18px;
        }
        .header h3 {
            color: #666;
            font-size: 14px;
            margin: 5px 0 0;
            padding: 0;
        }
        .info-section {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 20px;
        }
        .info-heading {
            color: #333;
            font-size: 14px;
            margin: 0 0 10px;
            padding: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .note-row {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            background: #fff;
        }
        .note-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .note-content {
            padding: 5px 0 5px 15px;
            border-left: 2px solid #ddd;
            color: #333;
            margin: 10px 0;
        }
        .note-form {
            padding: 8px;
            background: #f0f9ff;
            border: 1px solid #bde3ff;
            margin: 8px 0;
            border-radius: 4px;
        }
        .form-field {
            margin-bottom: 5px;
            display: flex;
        }
        .form-label {
            font-weight: bold;
            width: 150px;
        }
        .form-value {
            flex: 1;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid rgba(0,0,0,0.1);
        }
        /* Status colors */
        .status-pending { background-color: #f0f0f0; color: #666; }
        .status-order_placed { background-color: #e9ecef; color: #495057; }
        .status-proforma_invoice_received { background-color: #dce3f3; color: #5a5b9f; }
        .status-payment_completed { background-color: #dcedfc; color: #3182ce; }
        .status-du_in_process { background-color: #e0e7ff; color: #5a67d8; }
        .status-goods_acquired { background-color: #d5f5f6; color: #2c7a7b; }
        .status-shipped_to_port { background-color: #fef3c7; color: #92400e; }
        .status-shipping_line_booking_confirmed { background-color: #fef9c3; color: #854d0e; }
        .status-container_loaded { background-color: #ffedd5; color: #c2410c; }
        .status-on_board { background-color: #fee2e2; color: #b91c1c; }
        .status-arrived_at_port { background-color: #ffe4e6; color: #9d174d; }
        .status-customs_clearance { background-color: #fce7f3; color: #be185d; }
        .status-delivered { background-color: #dcfce7; color: #15803d; }
        .status-custom_form { background-color: #e0f2fe; color: #0369a1; }
        
        .meta {
            font-size: 10px;
            color: #555;
        }
        .attachment-info {
            padding: 4px 8px;
            background-color: #e0f2fe;
            border-radius: 5px;
            display: inline-block;
            margin-top: 8px;
            border: 1px solid #bae6fd;
            color: #0369a1;
            font-size: 11px;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .custom-form-field {
            margin-bottom: 12px;
        }
        
        .custom-form-field strong {
            font-weight: 600;
            color: #374151;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho com título -->
    <div class="header">
        <h2>{{ config('app.name') }}</h2>
        <h3>{{ __('messages.shipping_notes_report') }}</h3>
    </div>

    <!-- Informações da ordem -->
    <div class="info-section">
        <h4 class="info-heading">{{ __('messages.purchase_order_information') }}</h4>
        <div class="info-row">
            <span class="info-label">{{ __('messages.order_number') }}:</span>
            <span>{{ isset($order['order_number']) ? $order['order_number'] : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ __('messages.supplier') }}:</span>
            <span>{{ isset($order['supplier_name']) ? $order['supplier_name'] : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ __('messages.date') }}:</span>
            <span>{{ isset($order['order_date']) ? $order['order_date'] : 'N/A' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">{{ __('messages.status') }}:</span>
            <span>{{ isset($order['status']) ? __('messages.status_'.$order['status']) : 'N/A' }}</span>
        </div>
    </div>

    <!-- Shipping History -->
    <h4 style="margin-bottom: 10px;">{{ __('messages.shipping_history') }}</h4>
    
    @if(isset($shippingNotes) && count($shippingNotes) > 0)
        @foreach($shippingNotes as $note)
            <div class="note-row">
                <div class="note-header">
                    <div>
                        <span class="status-badge status-{{ $note['status'] }}">
                            @php
                            // Adiciona o ícone apropriado baseado no status, replicando o que é mostrado no modal
                            $statusIcon = '';
                            switch($note['status']) {
                                case 'order_placed':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf07a;</span>'; // shopping-cart
                                    break;
                                case 'proforma_invoice_received':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf570;</span>'; // file-invoice
                                    break;
                                case 'payment_completed':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf53d;</span>'; // money-check-alt
                                    break;
                                case 'du_in_process':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf6cf;</span>'; // file-contract
                                    break;
                                case 'goods_acquired':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf49e;</span>'; // box-open
                                    break;
                                case 'shipped_to_port':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf4de;</span>'; // truck-loading
                                    break;
                                case 'shipping_line_booking_confirmed':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf274;</span>'; // calendar-check
                                    break;
                                case 'container_loaded':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf4cd;</span>'; // truck-moving
                                    break;
                                case 'on_board':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf21a;</span>'; // ship
                                    break;
                                case 'arrived_at_port':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf13d;</span>'; // anchor
                                    break;
                                case 'customs_clearance':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf46c;</span>'; // clipboard-check
                                    break;
                                case 'delivered':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf058;</span>'; // check-circle
                                    break;
                                case 'custom_form':
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf15c;</span>'; // file-alt
                                    break;
                                default:
                                    $statusIcon = '<span style="display: inline-block; font-family: FontAwesome, Arial;">&#xf111;</span>'; // circle
                            }
                            @endphp
                            {!! $statusIcon !!} 
                            @if($note['status'] == 'custom_form' && isset($note['custom_form']['name']))
                                {{ $note['custom_form']['name'] }}
                            @else
                                {{ __('messages.shipping_status_'.str_replace(' ', '_', $note['status'])) }}
                            @endif
                        </span>
                        <span class="meta">{{ $note['created_at'] }}</span>
                    </div>
                    <div class="meta">
                        {{ __('messages.by') }}: {{ $note['created_by'] }}
                    </div>
                </div>
                
                @if(isset($note['note']) && !empty($note['note']))
                    <div class="note-content">
                        {{ $note['note'] }}
                    </div>
                @endif
                
                @if(isset($note['attachment']) && !empty($note['attachment']))
                    <div class="attachment-info">
                        <span style="display: inline-flex; align-items: center;">
                            <span style="display: inline-block; font-family: FontAwesome, Arial; margin-right: 3px;">&#xf0c6;</span>
                            {{ __('messages.attachment') }}: <strong style="margin-left: 3px;">{{ basename($note['attachment']) }}</strong>
                        </span>
                    </div>
                @endif
                
                @if(isset($note['custom_form']) && !empty($note['custom_form']))
                    <div class="note-form">
                        <h5 style="margin: 0 0 8px 0; padding: 0; color: #0369a1; border-bottom: 1px solid #bde3ff; padding-bottom: 4px;">
                            <svg style="display: inline-block; width: 14px; height: 14px; margin-right: 5px; vertical-align: middle;" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ __('messages.custom_form_data') }}: <strong>{{ $note['custom_form']['name'] }}</strong>
                        </h5>
                        
                        <div style="background-color: #ffffff; border: 1px solid #d1e9ff; border-radius: 3px; padding: 8px; margin-top: 8px;">
                            @if(!isset($note['custom_form']['fields']) || empty($note['custom_form']['fields']))
                                <div style="color: #ef4444; padding: 8px; background-color: #fee2e2; border-radius: 3px; font-weight: bold; font-size: 11px;">
                                    DEBUG: Nenhum campo encontrado neste formulário. Array fields não foi definido ou está vazio.
                                </div>
                            @else
                                <div style="color: #047857; padding: 8px; background-color: #ecfdf5; border-radius: 3px; margin-bottom: 8px; font-size: 11px;">
                                    DEBUG: {{ count($note['custom_form']['fields']) }} campo(s) encontrado(s) neste formulário.
                                </div>
                            
                                <table style="width: 100%; border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; padding: 4px 6px; border-bottom: 1px solid #e5e7eb; font-size: 11px; color: #6b7280; width: 40%;">{{ __('messages.field') }}</th>
                                            <th style="text-align: left; padding: 4px 6px; border-bottom: 1px solid #e5e7eb; font-size: 11px; color: #6b7280;">{{ __('messages.value') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($note['custom_form']['fields'] as $field)
                                            <tr>
                                                <td style="padding: 4px 6px; border-bottom: 1px solid #f3f4f6; font-size: 11px; font-weight: bold;">{{ $field['label'] }}:</td>
                                                <td style="padding: 4px 6px; border-bottom: 1px solid #f3f4f6; font-size: 11px;">
                                                    <!-- Debug do tipo e valor do campo -->
                                                    <div style="font-size: 10px; color: #6b7280; margin-bottom: 4px;">
                                                        Tipo: {{ $field['type'] }} | Valor: {{ is_string($field['value']) ? $field['value'] : json_encode($field['value']) }}
                                                    </div>
                                                @if($field['type'] == 'checkbox')
                                                    <div class="custom-form-field">
                                                        @if(is_array($field['value']) || is_object($field['value']) || (is_string($field['value']) && in_array(substr($field['value'], 0, 1), ['{', '['])))
                                                            <p><strong>{{ $field['label'] }}:</strong></p>
                                                            <div style="margin-top: 8px;">
                                                                @php
                                                                    $checkboxValues = is_string($field['value']) ? json_decode($field['value'], true) : (array)$field['value'];
                                                                @endphp
                                                                
                                                                @foreach($checkboxValues as $key => $checked)
                                                                    @if($checked === true || $checked === 1 || $checked === 'true' || $checked === '1')
                                                                        <span style="display: inline-block; margin-right: 8px; margin-bottom: 8px; padding: 4px 8px; background-color: #dcfce7; color: #15803d; border-radius: 16px; font-size: 11px; border: 1px solid #86efac;">
                                                                            <span style="color: #16a34a; margin-right: 3px;">✓</span> {{ $key }}
                                                                        </span>
                                                                    @else
                                                                        <span style="display: inline-block; margin-right: 8px; margin-bottom: 8px; padding: 4px 8px; background-color: #f3f4f6; color: #6b7280; border-radius: 16px; font-size: 11px; border: 1px solid #d1d5db;">
                                                                            <span style="color: #9ca3af; margin-right: 3px;">✕</span> {{ $key }}
                                                                        </span>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @else
                                                                <p>
                                                                    <strong>{{ $field['label'] }}:</strong>
                                                                    <span style="display: inline-block; margin-left: 8px;">
                                                                        @if($field['value'])
                                                                            <span style="display: inline-block; width: 16px; height: 16px; background-color: #3b82f6; border-radius: 4px; position: relative;">
                                                                                <span style="position: absolute; top: 2px; left: 3px; color: white; font-family: FontAwesome, Arial; font-size: 10px;">&#xf00c;</span>
                                                                            </span>
                                                                        @else
                                                                            <span style="display: inline-block; width: 16px; height: 16px; border: 1px solid #d1d5db; border-radius: 4px;"></span>
                                                                        @endif
                                                                        <span style="margin-left: 8px; font-size: 13px; color: #374151;">{{ $field['value'] ? __('messages.yes') : __('messages.no') }}</span>
                                                                    </span>
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @elseif($field['type'] == 'file' && !empty($field['value']))
                                                        <div class="custom-form-field">
                                                            <p><strong>{{ $field['label'] }}:</strong></p>
                                                            <div style="margin-top: 8px; border: 1px solid #93c5fd; border-radius: 6px; padding: 8px; background-color: #eff6ff; display: flex; justify-content: space-between; align-items: center;">
                                                                <div style="display: flex; align-items: center;">
                                                                    <span style="display: inline-block; font-family: FontAwesome, Arial; color: #3b82f6; margin-right: 8px;">&#xf15b;</span>
                                                                    <span style="font-size: 12px; color: #374151; max-width: 200px; overflow: hidden; text-overflow: ellipsis;">{{ basename($field['value']) }}</span>
                                                                </div>
                                                                <div>
                                                                    <div style="text-align: right; font-size: 11px; color: #3b82f6; background-color: white; padding: 4px 8px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                                                        <span style="display: inline-block; font-family: FontAwesome, Arial; margin-right: 4px;">&#xf56d;</span>
                                                                        {{ __('messages.download') }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @elseif($field['type'] == 'date' && !empty($field['value']))
                                                        <div class="custom-form-field">
                                                            <p>
                                                                <strong>{{ $field['label'] }}:</strong>
                                                                <span style="display: inline-block; margin-left: 8px;">
                                                                    <span style="display: inline-flex; align-items: center; font-size: 13px; color: #374151;">
                                                                        <span style="display: inline-block; font-family: FontAwesome, Arial; margin-right: 5px; color: #6b7280;">&#xf133;</span>
                                                                        {{ \Carbon\Carbon::parse($field['value'])->format('d/m/Y') }}
                                                                    </span>
                                                                </span>
                                                            </p>
                                                        </div>
                                                @elseif($field['type'] == 'select')
                                                    <div class="custom-form-field">
                                                        <p>
                                                            <strong>{{ $field['label'] }}:</strong>
                                                            <span style="display: inline-block; margin-left: 8px;">
                                                                @php
                                                                    $displayValue = $field['value'] ?? 'N/A';
                                                                    $options = $field['options'] ?? [];
                                                                    
                                                                    if (!empty($options)) {
                                                                        if (is_string($options)) {
                                                                            $options = @json_decode($options, true) ?: [];
                                                                        }
                                                                        
                                                                        foreach ($options as $option) {
                                                                            if (isset($option['value']) && $option['value'] == $field['value']) {
                                                                                $displayValue = $option['label'] ?? $option['value'];
                                                                                break;
                                                                            }
                                                                        }
                                                                    }
                                                                @endphp
                                                                <span style="font-size: 13px; color: #374151; padding: 4px 8px; background-color: #f3f4f6; border-radius: 4px; border: 1px solid #e5e7eb;">
                                                                    {{ $displayValue }}
                                                                </span>
                                                            </span>
                                                        </p>
                                                    </div>
                                                @else
                                                    <div class="custom-form-field">
                                                        <p>
                                                            <strong>{{ $field['label'] }}:</strong>
                                                            <span style="display: inline-block; margin-left: 8px; font-size: 13px; color: #374151;">
                                                                {{ $field['value'] }}
                                                            </span>
                                                        </p>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <p>{{ __('messages.no_shipping_notes_to_export') }}</p>
    @endif

    <!-- Rodapé -->
    <div class="footer">
        {{ __('messages.generated_on') }}: {{ isset($date_generated) ? $date_generated : date('d/m/Y H:i') }}
    </div>
</body>
</html>
