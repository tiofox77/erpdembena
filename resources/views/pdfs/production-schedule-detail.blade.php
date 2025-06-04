<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.production_schedule_details') }} - {{ $schedule->schedule_number }}</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
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
        .container {
            width: 100%;
            padding: 20px;
        }
        .header {
            margin-bottom: 20px;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: white;
            padding: 15px;
            border-radius: 8px;
        }
        .header h1 {
            color: white;
            font-size: 22px;
            margin: 0;
            padding: 0;
        }
        .header .info {
            font-size: 12px;
            margin-top: 5px;
        }
        .badge-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            gap: 15px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-draft {
            background-color: #e5e7eb;
            color: #4b5563;
        }
        .badge-confirmed {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-in_progress {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-completed {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-cancelled {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .badge-low {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-medium {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-high {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-urgent {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .section {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .section-header {
            font-size: 16px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .section-header i {
            color: #3b82f6;
            margin-right: 8px;
        }
        dl {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        dt {
            font-size: 12px;
            font-weight: 500;
            color: #6b7280;
            margin-bottom: 4px;
        }
        dd {
            font-size: 13px;
            color: #111827;
            margin: 0;
            margin-top: 2px;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        .progress-bar-container {
            width: 100%;
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 4px;
        }
        .progress-bar {
            height: 100%;
            background-color: #3b82f6;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }
        table th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 8px;
            font-size: 11px;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .page-number {
            position: absolute;
            bottom: 20px;
            right: 20px;
            font-size: 10px;
            color: #9ca3af;
        }
        .production-result {
            background-color: #f0fdf4;
            border: 1px solid #dcfce7;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
        }
        .production-result-title {
            font-weight: bold;
            color: #166534;
            margin-bottom: 8px;
            font-size: 13px;
        }
        .result-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .result-item {
            background-color: white;
            border: 1px solid #d1fae5;
            border-radius: 6px;
            padding: 8px;
        }
        .result-label {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 4px;
        }
        .result-value {
            font-size: 12px;
            font-weight: bold;
            display: flex;
            justify-content: space-between;
        }
        .tab-content {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Cabeçalho Padrão do ERP -->
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
                <div style="font-size: 18px; font-weight: bold; margin: 10px 0; color: #2563eb;">
                    {{ __('messages.production_schedule_details') }} - {{ $schedule->schedule_number }}
                </div>
                <div>
                    {{ __('messages.generated_by') }}: {{ $user->name }} | {{ __('messages.date') }}: {{ $currentDate }}
                </div>
            </div>
        </div>

        <!-- Badges de Status e Prioridade -->
        <div class="badge-container">
            <span class="badge badge-{{ $schedule->status }}">
                {{ $statuses[$schedule->status] }}
            </span>
            
            <span class="badge badge-{{ $schedule->priority }}">
                {{ $priorities[$schedule->priority] }}
            </span>
        </div>

        <!-- Grid Principal -->
        <div class="grid">
            <!-- Coluna 1: Informações da Programação -->
            <div class="section">
                <div class="section-header">
                    <i class="fas fa-info-circle"></i>
                    {{ __('messages.schedule_information') }}
                </div>
                
                <dl>
                    <div>
                        <dt>{{ __('messages.product') }}:</dt>
                        <dd>{{ $schedule->product->name }}</dd>
                    </div>

                    <div>
                        <dt>{{ __('messages.schedule_number') }}:</dt>
                        <dd>{{ $schedule->schedule_number }}</dd>
                    </div>
                    
                    <div>
                        <dt>{{ __('messages.status') }}:</dt>
                        <dd>
                            <span class="status-badge" style="background-color: {{ $schedule->status === 'draft' ? '#e5e7eb' : 
                            ($schedule->status === 'confirmed' ? '#dbeafe' : 
                            ($schedule->status === 'in_progress' ? '#fef3c7' : 
                            ($schedule->status === 'completed' ? '#d1fae5' : '#fee2e2'))) }}; 
                            color: {{ $schedule->status === 'draft' ? '#4b5563' : 
                            ($schedule->status === 'confirmed' ? '#1e40af' : 
                            ($schedule->status === 'in_progress' ? '#92400e' : 
                            ($schedule->status === 'completed' ? '#065f46' : '#b91c1c'))) }}">
                                {{ $statuses[$schedule->status] }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt>{{ __('messages.period') }}:</dt>
                        <dd>
                            {{ \Carbon\Carbon::parse($schedule->start_date)->format('d/m/Y') }} {{ $schedule->start_time ?? '00:00' }} - 
                            {{ \Carbon\Carbon::parse($schedule->end_date)->format('d/m/Y') }} {{ $schedule->end_time ?? '23:59' }}
                        </dd>
                    </div>
                    
                    <div>
                        <dt>{{ __('messages.completion') }}:</dt>
                        <dd>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: {{ $completionPercentage }}%"></div>
                            </div>
                            <span style="font-size: 10px;">{{ $completionPercentage }}%</span>
                        </dd>
                    </div>

                    <div>
                        <dt>{{ __('messages.location') }}:</dt>
                        <dd>{{ $schedule->location->name ?? __('messages.not_assigned') }}</dd>
                    </div>

                    <div>
                        <dt>{{ __('messages.line') }}:</dt>
                        <dd>{{ $schedule->line->name ?? __('messages.not_assigned') }}</dd>
                    </div>

                    <div>
                        <dt>{{ __('messages.priority') }}:</dt>
                        <dd>
                            <span class="status-badge" style="background-color: {{ $schedule->priority === 'low' ? '#dbeafe' : 
                            ($schedule->priority === 'medium' ? '#d1fae5' : 
                            ($schedule->priority === 'high' ? '#fef3c7' : '#fee2e2')) }}; 
                            color: {{ $schedule->priority === 'low' ? '#1e40af' : 
                            ($schedule->priority === 'medium' ? '#065f46' : 
                            ($schedule->priority === 'high' ? '#92400e' : '#b91c1c')) }}">
                                {{ $priorities[$schedule->priority] }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt>{{ __('messages.responsible') }}:</dt>
                        <dd>{{ $schedule->responsible->name ?? __('messages.not_assigned') }}</dd>
                    </div>
                </dl>

                @if($schedule->status === 'completed')
                <div class="production-result">
                    <div class="production-result-title">{{ __('messages.production_results') }}:</div>
                    <div class="result-grid">
                        <!-- Quantidade Planejada vs Real -->
                        <div class="result-item">
                            <div class="result-label">{{ __('messages.planned_vs_actual') }}</div>
                            <div class="result-value">
                                <span>{{ number_format($schedule->planned_quantity, 2) }}</span>
                                <span>{{ number_format($totalProducedQuantity, 2) }}</span>
                            </div>
                            <div style="text-align: center; font-size: 10px; margin-top: 4px;">
                                {{ $completionPercentage }}%
                            </div>
                        </div>
                        
                        <!-- Status de Atraso -->
                        <div class="result-item">
                            <div class="result-label">{{ __('messages.completion_status') }}</div>
                            <div style="text-align: center; margin-top: 4px;">
                                @if($schedule->is_delayed)
                                    <span style="color: #dc2626; font-weight: bold;">
                                        {{ __('messages.delayed') }}
                                    </span>
                                @else
                                    <span style="color: #16a34a; font-weight: bold;">
                                        {{ __('messages.on_time') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            
            <!-- Coluna 2: Detalhes de Produção -->
            <div class="section">
                <div class="section-header">
                    <i class="fas fa-chart-line"></i>
                    {{ __('messages.production_details') }}
                </div>
                
                <dl>
                    <div>
                        <dt>{{ __('messages.planned_quantity') }}:</dt>
                        <dd><strong>{{ number_format($schedule->planned_quantity, 0, ',', '.') }}</strong></dd>
                    </div>
                    
                    <div>
                        <dt>{{ __('messages.produced_quantity') }}:</dt>
                        <dd><strong>{{ number_format($totalProducedQuantity, 0, ',', '.') }}</strong></dd>
                    </div>
                    
                    <div>
                        <dt>{{ __('messages.rejected_quantity') }}:</dt>
                        <dd><strong>{{ number_format($totalRejectedQuantity, 0, ',', '.') }}</strong></dd>
                    </div>
                    
                    <div>
                        <dt>{{ __('messages.efficiency') }}:</dt>
                        <dd><strong>{{ number_format($totalEfficiency, 1, ',', '.') }}%</strong></dd>
                    </div>
                    
                    <div>
                        <dt>{{ __('messages.remaining_quantity') }}:</dt>
                        <dd><strong>{{ number_format(max(0, $schedule->planned_quantity - $totalProducedQuantity), 0, ',', '.') }}</strong></dd>
                    </div>
                    
                    <div>
                        <dt>{{ __('messages.total_shifts') }}:</dt>
                        <dd><strong>{{ $schedule->shifts->count() }}</strong></dd>
                    </div>
                </dl>
                
                <!-- Turnos Designados -->
                <div style="margin-top: 15px;">
                    <h3 style="font-size: 14px; margin-bottom: 8px;">{{ __('messages.assigned_shifts') }}</h3>
                    @if($schedule->shifts->count() > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('messages.name') }}</th>
                                <th>{{ __('messages.start_time') }}</th>
                                <th>{{ __('messages.end_time') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($schedule->shifts as $shift)
                            <tr>
                                <td><strong>{{ $shift->name }}</strong></td>
                                <td>{{ $shift->start_time }}</td>
                                <td>{{ $shift->end_time }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <p style="text-align: center; color: #6b7280; font-style: italic;">
                        {{ __('messages.no_shifts_assigned') }}
                    </p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Seção de Tabs no PDF-->
        <div class="tab-content">
            <!-- Tab de Planos Diários -->
            <div class="section">
                <div class="section-header">
                    <i class="fas fa-calendar-check"></i>
                    {{ __('messages.daily_plans') }}
                </div>
                
                @if($schedule->dailyPlans->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('messages.date') }}</th>
                            <th>{{ __('messages.shift') }}</th>
                            <th>{{ __('messages.responsible') }}</th>
                            <th class="text-right">{{ __('messages.planned_quantity') }}</th>
                            <th class="text-right">{{ __('messages.actual_quantity') }}</th>
                            <th class="text-right">{{ __('messages.rejected_quantity') }}</th>
                            <th class="text-right">{{ __('messages.efficiency') }}</th>
                            <th>{{ __('messages.status') }}</th>
                            <th>{{ __('messages.start_time') }} - {{ __('messages.end_time') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedule->dailyPlans as $plan)
                        <tr>
                            <td>{{ $plan->production_date ? \Carbon\Carbon::parse($plan->production_date)->format('d/m/Y') : '-' }}</td>
                            <td>{{ $plan->shift && $plan->shift->name ? $plan->shift->name : __('messages.not_assigned') }}</td>
                            <td>{{ $plan->responsible && $plan->responsible->name ? $plan->responsible->name : __('messages.not_assigned') }}</td>
                            <td class="text-right">{{ isset($plan->planned_quantity) ? number_format($plan->planned_quantity, 0, ',', '.') : '0' }}</td>
                            <td class="text-right">{{ isset($plan->actual_quantity) ? number_format($plan->actual_quantity, 0, ',', '.') : '0' }}</td>
                            <td class="text-right">{{ isset($plan->rejected_quantity) ? number_format($plan->rejected_quantity, 0, ',', '.') : '0' }}</td>
                            <td class="text-right">{{ isset($plan->efficiency) ? number_format($plan->efficiency, 1, ',', '.') : '0,0' }}%</td>
                            <td>
                                <span class="status-badge" style="background-color: {{ $plan->status === 'pending' ? '#e5e7eb' : 
                                ($plan->status === 'in_progress' ? '#fef3c7' : 
                                ($plan->status === 'completed' ? '#d1fae5' : '#fee2e2')) }}; 
                                color: {{ $plan->status === 'pending' ? '#4b5563' : 
                                ($plan->status === 'in_progress' ? '#92400e' : 
                                ($plan->status === 'completed' ? '#065f46' : '#b91c1c')) }}">
                                    {{ isset($plan->status) ? __('messages.' . $plan->status) : __('messages.pending') }}
                                </span>
                            </td>
                            <td>
                                {{ isset($plan->start_time) ? $plan->start_time : '00:00' }} - {{ isset($plan->end_time) ? $plan->end_time : '23:59' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <!-- Detalhes dos planos diários com quebras e notas -->
                @foreach($schedule->dailyPlans as $plan)
                    @if(isset($plan->notes) && !empty(trim($plan->notes)) || (isset($plan->has_breakdown) && $plan->has_breakdown))
                    <div style="margin-top: 15px; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; background-color: #f9fafb;">
                        <h4 style="font-size: 14px; margin: 0 0 10px 0;">
                            {{ __('messages.details_for_date') }}: {{ $plan->production_date ? \Carbon\Carbon::parse($plan->production_date)->format('d/m/Y') : '-' }}
                        </h4>
                        
                        @if(isset($plan->has_breakdown) && $plan->has_breakdown)
                        <div style="margin-bottom: 10px;">
                            <h5 style="font-size: 12px; font-weight: bold; margin: 0 0 5px 0; color: #b91c1c;">
                                {{ __('messages.breakdown_information') }}:
                            </h5>
                            <ul style="margin: 0; padding-left: 20px; font-size: 12px;">
                                <li>{{ __('messages.breakdown_minutes') }}: {{ $plan->breakdown_minutes ?? 0 }} {{ __('messages.minutes') }}</li>
                                <li>{{ __('messages.failure_category') }}: {{ $plan->failureCategory ? $plan->failureCategory->name : __('messages.not_specified') }}</li>
                            </ul>
                        </div>
                        @endif
                        
                        @if(isset($plan->notes) && !empty(trim($plan->notes)))
                        <div>
                            <h5 style="font-size: 12px; font-weight: bold; margin: 0 0 5px 0; color: #1e40af;">
                                {{ __('messages.notes') }}:
                            </h5>
                            <div style="white-space: pre-line; font-size: 12px; padding: 5px; background-color: white; border-radius: 4px; border: 1px solid #e5e7eb;">
                                {{ $plan->notes }}
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                @endforeach
                @else
                <p style="text-align: center; color: #6b7280; font-style: italic; padding: 20px;">
                    {{ __('messages.no_daily_plans') }}
                </p>
                @endif
            </div>
            
            <!-- Tab de Ordens de Produção -->
            <div class="section">
                <div class="section-header">
                    <i class="fas fa-clipboard-list"></i>
                    {{ __('messages.production_orders') }}
                </div>
                
                @if($schedule->productionOrders->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('messages.order_number') }}</th>
                            <th>{{ __('messages.due_date') }}</th>
                            <th class="text-right">{{ __('messages.planned_quantity') }}</th>
                            <th class="text-right">{{ __('messages.produced_quantity') }}</th>
                            <th>{{ __('messages.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($schedule->productionOrders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number ?? __('messages.not_available') }}</strong></td>
                            <td>{{ isset($order->due_date) ? \Carbon\Carbon::parse($order->due_date)->format('d/m/Y') : '-' }}</td>
                            <td class="text-right">{{ isset($order->planned_quantity) ? number_format($order->planned_quantity, 0, ',', '.') : '0' }}</td>
                            <td class="text-right">{{ isset($order->produced_quantity) ? number_format($order->produced_quantity, 0, ',', '.') : '0' }}</td>
                            <td>
                                @if(isset($order->status))
                                <span class="status-badge" style="background-color: {{ $order->status === 'draft' ? '#e5e7eb' : 
                                ($order->status === 'released' ? '#dbeafe' : 
                                ($order->status === 'in_progress' ? '#fef3c7' : 
                                ($order->status === 'completed' ? '#d1fae5' : '#fee2e2'))) }}; 
                                color: {{ $order->status === 'draft' ? '#4b5563' : 
                                ($order->status === 'released' ? '#1e40af' : 
                                ($order->status === 'in_progress' ? '#92400e' : 
                                ($order->status === 'completed' ? '#065f46' : '#b91c1c'))) }}">
                                    {{ __('messages.' . $order->status) }}
                                </span>
                                @else
                                <span class="status-badge" style="background-color: #e5e7eb; color: #4b5563;">
                                    {{ __('messages.draft') }}
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p style="text-align: center; color: #6b7280; font-style: italic; padding: 20px;">
                    {{ __('messages.no_production_orders') }}
                </p>
                @endif
            </div>
            
            <!-- Notas -->
            @if($schedule->notes)
            <div class="section">
                <div class="section-header">
                    <i class="fas fa-sticky-note"></i>
                    {{ __('messages.notes') }}
                </div>
                
                <div style="white-space: pre-line; background-color: #f9fafb; padding: 10px; border-radius: 6px; border-left: 3px solid #3b82f6;">
                    {{ $schedule->notes }}
                </div>
            </div>
            @endif
        </div>

        <div class="footer">
            <p>{{ \App\Models\Setting::get('company_name', 'ERP DEMBENA') }} &copy; {{ date('Y') }} - {{ __('messages.all_rights_reserved') }}</p>
            <p>{{ __('messages.report_generated_by') }} ERP DEMBENA v{{ config('app.version', '1.0') }} | {{ $currentDate }}</p>
            <p>{{ __('messages.confidential_document') }} - {{ __('messages.page') }} <span class="page">1</span></p>
        </div>
    </div>
</body>
</html>
