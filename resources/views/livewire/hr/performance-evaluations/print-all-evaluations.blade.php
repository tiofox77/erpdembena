<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Avaliações de Desempenho</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
            background: #fff;
        }

        .page {
            width: 297mm;
            min-height: 210mm;
            margin: 0 auto;
            padding: 8mm;
            background: #fff;
        }

        /* Company Header */
        .company-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4f46e5;
        }

        .company-logo img {
            max-height: 50px;
            max-width: 150px;
            margin-right: 15px;
        }

        .company-info h2 {
            font-size: 16px;
            color: #1e40af;
            margin: 0;
        }

        .company-info p {
            margin: 2px 0;
            font-size: 9px;
            color: #4b5563;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding: 10px;
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border-radius: 6px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            color: white;
            margin: 0;
        }

        .header p {
            font-size: 10px;
            color: #e0e7ff;
            margin: 3px 0 0 0;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .data-table th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            color: #374151;
        }

        .data-table td {
            border: 1px solid #e5e7eb;
            padding: 8px 6px;
            vertical-align: middle;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .data-table tbody tr:hover {
            background: #f3f4f6;
        }

        /* Employee Cell */
        .employee-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .employee-avatar {
            width: 28px;
            height: 28px;
            background: #4f46e5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 11px;
        }

        .employee-info .name {
            font-weight: 600;
            color: #111827;
        }

        .employee-info .id {
            font-size: 9px;
            color: #6b7280;
        }

        /* Score Cell */
        .score-cell {
            text-align: center;
        }

        .score-value {
            font-size: 14px;
            font-weight: bold;
        }

        .score-percent {
            font-size: 9px;
            color: #6b7280;
        }

        .score-green { color: #059669; }
        .score-blue { color: #2563eb; }
        .score-yellow { color: #d97706; }
        .score-red { color: #dc2626; }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 500;
        }

        .badge-satisfactory { background: #fef3c7; color: #92400e; }
        .badge-good { background: #dbeafe; color: #1e40af; }
        .badge-very-good { background: #d1fae5; color: #065f46; }
        .badge-excellent { background: #dcfce7; color: #166534; }
        .badge-needs-improvement { background: #fee2e2; color: #991b1b; }

        .badge-yes { background: #d1fae5; color: #065f46; }
        .badge-no { background: #f3f4f6; color: #6b7280; }

        .badge-draft { background: #fef3c7; color: #92400e; }
        .badge-submitted { background: #dbeafe; color: #1e40af; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #6b7280;
        }

        @media print {
            body { background: #fff; }
            .page { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="page">
        {{-- Company Header --}}
        <div class="company-header">
            <div class="company-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.style.display='none'">
            </div>
            <div class="company-info">
                <h2>DEMBENA - Indústria e Comércio Lda</h2>
                <p>NIF: 5417202815 | Licença nº 0032</p>
                <p>Rua da Gabela, Km7, Viana, Luanda - Angola</p>
            </div>
        </div>

        {{-- Title Header --}}
        <div class="header">
            <h1>LISTA DE AVALIAÇÕES DE DESEMPENHO</h1>
            <p>Performance Appraisal List (Semestral/Especial) - Total: {{ $evaluations->count() }} avaliações</p>
        </div>

        {{-- Data Table --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 22%;">Funcionário</th>
                    <th style="width: 15%;">Departamento</th>
                    <th style="width: 13%;">Período</th>
                    <th style="width: 10%; text-align: center;">Média / %</th>
                    <th style="width: 14%; text-align: center;">Nível</th>
                    <th style="width: 8%; text-align: center;">Bónus</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evaluations as $evaluation)
                    @php
                        $percentage = $evaluation->average_score ? $evaluation->average_score * 20 : 0;
                        $scoreClass = $evaluation->average_score >= 4 ? 'score-green' : 
                                     ($evaluation->average_score >= 3 ? 'score-blue' : 
                                     ($evaluation->average_score >= 2 ? 'score-yellow' : 'score-red'));
                        
                        // Determine level badge
                        if ($percentage > 90) $levelBadge = 'badge-excellent';
                        elseif ($percentage >= 81) $levelBadge = 'badge-very-good';
                        elseif ($percentage >= 71) $levelBadge = 'badge-good';
                        elseif ($percentage >= 61) $levelBadge = 'badge-satisfactory';
                        else $levelBadge = 'badge-needs-improvement';
                        
                        // Determine level name
                        if ($percentage > 90) $levelName = 'Excellent';
                        elseif ($percentage >= 81) $levelName = 'Very Good';
                        elseif ($percentage >= 71) $levelName = 'Good';
                        elseif ($percentage >= 61) $levelName = 'Satisfactory';
                        else $levelName = 'Needs Improvement';
                        
                        // Status badge
                        $statusBadge = match($evaluation->status) {
                            'draft' => 'badge-draft',
                            'submitted' => 'badge-submitted',
                            'approved' => 'badge-approved',
                            'rejected' => 'badge-rejected',
                            default => 'badge-draft'
                        };
                        $statusName = match($evaluation->status) {
                            'draft' => 'Rascunho',
                            'submitted' => 'Submetido',
                            'approved' => 'Aprovado',
                            'rejected' => 'Rejeitado',
                            default => $evaluation->status
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="employee-cell">
                                <div class="employee-avatar">
                                    {{ substr($evaluation->employee->full_name ?? 'N', 0, 1) }}
                                </div>
                                <div class="employee-info">
                                    <div class="name">{{ $evaluation->employee->full_name ?? 'N/A' }}</div>
                                    <div class="id">ID: {{ $evaluation->employee->employee_id ?? $evaluation->employee->id ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $evaluation->department->name ?? $evaluation->employee->department->name ?? 'N/A' }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $evaluation->evaluation_quarter }} / {{ $evaluation->evaluation_year }}</div>
                            <div style="font-size: 9px; color: #6b7280;">
                                {{ $evaluation->period_start?->format('d/m') }} - {{ $evaluation->period_end?->format('d/m/Y') }}
                            </div>
                        </td>
                        <td class="score-cell">
                            @if($evaluation->average_score)
                                <div class="score-value {{ $scoreClass }}">{{ number_format($evaluation->average_score, 1) }}</div>
                                <div class="score-percent">({{ number_format($percentage, 0) }}%)</div>
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($evaluation->average_score)
                                <span class="badge {{ $levelBadge }}">{{ $levelName }}</span>
                            @else
                                <span style="color: #9ca3af;">-</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($evaluation->eligible_for_bonus)
                                <span class="badge badge-yes">✓ Sim</span>
                            @else
                                <span class="badge badge-no">Não</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            <span class="badge {{ $statusBadge }}">{{ $statusName }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: #6b7280;">
                            Nenhuma avaliação encontrada para os filtros selecionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <div>Gerado em: {{ now()->format('d/m/Y H:i') }}</div>
            <div>Total de registros: {{ $evaluations->count() }}</div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
