<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.maintenance_corrective_report') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .logo {
            max-height: 70px;
            max-width: 220px;
        }
        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            color: #2563eb;
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
        .report-info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px;
        }
        .report-info p {
            margin: 5px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table thead th {
            background-color: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #d1d5db;
            font-size: 11px;
        }
        .items-table tbody td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            font-size: 11px;
            vertical-align: top;
        }
        .items-table tfoot td {
            border-top: 2px solid #d1d5db;
            padding: 8px;
            font-weight: bold;
            background-color: #f9fafb;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .status-badge, .type-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-open {
            background-color: #FEF3C7;
            color: #92400E;
        }
        .status-in_progress {
            background-color: #DBEAFE;
            color: #1E40AF;
        }
        .status-resolved {
            background-color: #D1FAE5;
            color: #065F46;
        }
        .status-closed {
            background-color: #E5E7EB;
            color: #1F2937;
        }
        .details-section {
            margin-top: 15px;
            padding: 15px;
            background-color: #F9FAFB;
            border-radius: 4px;
            border: 1px solid #E5E7EB;
        }
        .detail-title {
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 5px;
            color: #4B5563;
        }
        .detail-content {
            margin-left: 15px;
            white-space: pre-wrap;
        }
        .no-data {
            padding: 20px;
            background-color: #f5f5f5;
            border-radius: 4px;
            margin-top: 15px;
            text-align: center;
        }
        hr {
            border: none;
            border-top: 1px dashed #ddd;
            margin: 15px 0;
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
            <div class="document-title">{{ __('messages.maintenance_corrective_report') }}</div>
            <div>{{ __('messages.generated_at') }}: {{ \Carbon\Carbon::parse($generatedAt)->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</div>
        </div>
    </div>

    @if(isset($isSingleReport) && $isSingleReport)
        {{-- Exibir informações detalhadas de uma única manutenção corretiva --}}
        <div class="document-info">
            <table>
                <tr>
                    <th>{{ __('messages.equipment') }}:</th>
                    <td>{{ $corrective->equipment ? $corrective->equipment->name : 'N/A' }}</td>
                    <th>{{ __('messages.status') }}:</th>
                    <td>
                        <span class="status-badge status-{{ $corrective->status }}">
                            {{ \App\Models\MaintenanceCorrective::getStatuses()[$corrective->status] ?? $corrective->status }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <th>{{ __('messages.start_time') }}:</th>
                    <td>{{ $corrective->start_time->format(\App\Models\Setting::getSystemDateTimeFormat()) }}</td>
                    <th>{{ __('messages.end_time') }}:</th>
                    <td>{{ $corrective->end_time ? $corrective->end_time->format(\App\Models\Setting::getSystemDateTimeFormat()) : 'N/A' }}</td>
                </tr>
                <tr>
                    <th>{{ __('messages.downtime') }}:</th>
                    <td>{{ $corrective->formatted_downtime }}</td>
                    <th>{{ __('messages.system_process') }}:</th>
                    <td>{{ $corrective->system_process ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <th>{{ __('messages.failure_mode') }}:</th>
                    <td>
                        @if(is_object($corrective->failureMode))
                            {{ $corrective->failureMode->name }}
                        @elseif(is_string($corrective->failureMode))
                            {{ $corrective->failureMode }}
                        @else
                            N/A
                        @endif
                    </td>
                    <th>{{ __('messages.failure_cause') }}:</th>
                    <td>
                        @if(is_object($corrective->failureCause))
                            {{ $corrective->failureCause->name }}
                        @elseif(is_string($corrective->failureCause))
                            {{ $corrective->failureCause }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>{{ __('messages.reported_by') }}:</th>
                    <td>
                        @if(is_object($corrective->reporter))
                            {{ $corrective->reporter->name }}
                        @elseif(is_string($corrective->reporter))
                            {{ $corrective->reporter }}
                        @else
                            N/A
                        @endif
                    </td>
                    <th>{{ __('messages.resolved_by') }}:</th>
                    <td>
                        @if(is_object($corrective->resolver))
                            {{ $corrective->resolver->name }}
                        @elseif(is_string($corrective->resolver))
                            {{ $corrective->resolver }}
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="details-section">
            <div class="detail-title">{{ __('messages.description') }}:</div>
            <div class="detail-content">{{ $corrective->description ?: __('messages.no_description_provided') }}</div>
            
            <hr>
            
            <div class="detail-title">{{ __('messages.actions_taken') }}:</div>
            <div class="detail-content">{{ $corrective->actions_taken ?: __('messages.no_actions_recorded') }}</div>
        </div>

    @else
        {{-- Relatório de múltiplas manutenções corretivas --}}
        <div class="report-info">
            <p><strong>{{ __('messages.report_period') }}:</strong> {{ $periodTitle }}</p>
            <p><strong>{{ __('messages.generated_on') }}:</strong> {{ $generatedAt }}</p>
            <p><strong>{{ __('messages.total_records') }}:</strong> {{ count($correctives) }}</p>
        </div>
        
        @if(count($correctives) > 0)
            <h3 style="margin-top: 20px; margin-bottom: 15px; color: #2563eb; text-align: center; border-bottom: 1px solid #e5e7eb; padding-bottom: 5px;">
                {{ __('messages.corrective_maintenance_records') }} - {{ $periodTitle }}
            </h3>
            
            @php
                // Organizar registros por status
                $correctivesByStatus = [];
                
                // Labels de status para exibição
                $statusLabels = \App\Models\MaintenanceCorrective::getStatuses();
                
                // Definir a ordem de exibição dos status
                $statusOrder = [
                    'open' => 1,
                    'in_progress' => 2,
                    'resolved' => 3, 
                    'closed' => 4
                ];
                
                // Agrupar registros por status
                foreach ($correctives as $record) {
                    if (!isset($correctivesByStatus[$record->status])) {
                        $correctivesByStatus[$record->status] = [];
                    }
                    
                    $correctivesByStatus[$record->status][] = $record;
                }
                
                // Ordenar os status conforme a ordem definida
                uksort($correctivesByStatus, function($a, $b) use ($statusOrder) {
                    $orderA = $statusOrder[$a] ?? 999;
                    $orderB = $statusOrder[$b] ?? 999;
                    return $orderA <=> $orderB;
                });
            @endphp
            
            @foreach($correctivesByStatus as $status => $statusRecords)
                @php
                    $statusLabel = $statusLabels[$status] ?? ucfirst($status);
                @endphp
                
                <div class="status-section" style="margin-bottom: 25px;">
                    <h4 style="margin-top: 20px; margin-bottom: 10px; color: #1E40AF; font-size: 15px; background-color: #DBEAFE; padding: 10px; border-radius: 4px; text-transform: uppercase;">
                        {{ $statusLabel }} - {{ count($statusRecords) }} {{ __('messages.records') }}
                    </h4>
                    
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th>{{ __('messages.id') }}</th>
                                <th>{{ __('messages.equipment') }}</th>
                                <th>{{ __('messages.failure_mode') }}</th>
                                <th>{{ __('messages.start_time') }}</th>
                                <th>{{ __('messages.end_time') }}</th>
                                <th>{{ __('messages.downtime') }}</th>
                                <th>{{ __('messages.resolved_by') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statusRecords as $record)
                                <tr>
                                    <td>{{ $record->id }}</td>
                                    <td>
                                        {{ $record->equipment->name ?? 'N/A' }}
                                        @if($record->equipment && $record->equipment->code)
                                            <br><small>({{ $record->equipment->code }})</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if(is_object($record->failureMode))
                                            {{ $record->failureMode->name }}
                                        @elseif(is_string($record->failureMode))
                                            {{ $record->failureMode }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $record->start_time->format(\App\Models\Setting::getSystemDateFormat()) }}<br>
                                        <small>{{ $record->start_time->format(\App\Models\Setting::getSystemTimeFormat()) }}</small>
                                    </td>
                                    <td>
                                        @if($record->end_time)
                                            {{ $record->end_time->format(\App\Models\Setting::getSystemDateFormat()) }}<br>
                                            <small>{{ $record->end_time->format(\App\Models\Setting::getSystemTimeFormat()) }}</small>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $record->formatted_downtime }}</td>
                                    <td>
                                        @if(is_object($record->resolver))
                                            {{ $record->resolver->name }}
                                        @elseif(is_string($record->resolver))
                                            {{ $record->resolver }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="7" style="text-align: right; font-weight: bold;">
                                    {{ __('messages.total_records') }}: {{ count($statusRecords) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                    
                    @if(!$loop->last)
                        <div style="page-break-after: always;"></div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="no-data">
                <p>{{ __('messages.no_corrective_maintenance_records_found') }}</p>
            </div>
        @endif
    @endif

    {{-- Seção de assinaturas --}}
    <div style="margin-top: 60px;">
        @if(isset($isSingleReport) && $isSingleReport)
            {{-- Campo de assinatura para relatório único --}}
            <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                <div style="text-align: center; width: 40%;">
                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 40px;"></div>
                    <p>{{ __('messages.technician_signature') }}</p>
                    <p style="font-size: 10px;">
                        @if(is_object($corrective->resolver))
                            {{ $corrective->resolver->name }}
                        @elseif(is_string($corrective->resolver))
                            {{ $corrective->resolver }}
                        @endif
                    </p>
                </div>
                
                <div style="text-align: center; width: 40%;">
                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 40px;"></div>
                    <p>{{ __('messages.supervisor_signature') }}</p>
                </div>
            </div>
        @else
            {{-- Campo de assinatura para relatório múltiplo --}}
            <div style="display: flex; justify-content: space-between; margin-top: 40px;">
                <div style="text-align: center; width: 40%;">
                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 40px;"></div>
                    <p>{{ __('messages.prepared_by') }}</p>
                </div>
                
                <div style="text-align: center; width: 40%;">
                    <div style="border-bottom: 1px solid #000; margin-bottom: 5px; height: 40px;"></div>
                    <p>{{ __('messages.approved_by') }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="footer" style="margin-top: 30px;">
        <p>{{ __('messages.report_footer_text', ['company' => $companyName]) }}</p>
        <p>{{ __('messages.page') }} 1</p>
    </div>
</body>
</html>
