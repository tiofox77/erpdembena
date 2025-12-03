<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quarterly Performance Appraisal - {{ $evaluation->employee->full_name ?? 'N/A' }}</title>
    <style>
        @page {
            size: A4;
            margin: 10mm 12mm;
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
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 8mm 12mm;
            background: #fff;
        }

        /* Company Header */
        .company-header {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2563eb;
        }

        .company-logo {
            margin-right: 15px;
        }

        .company-logo img {
            max-height: 60px;
            max-width: 180px;
        }

        .company-info {
            flex: 1;
        }

        .company-info h2 {
            margin: 0;
            padding: 0;
            font-size: 16px;
            color: #1e40af;
        }

        .company-info p {
            margin: 2px 0;
            font-size: 9px;
            color: #4b5563;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            padding: 8px;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 4px;
        }

        .header h1 {
            font-size: 15px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 3px;
        }

        .header .subtitle {
            font-size: 11px;
            color: #dbeafe;
        }

        .section {
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            background: #f0f0f0;
            padding: 5px 8px;
            border: 1px solid #333;
            border-bottom: none;
        }

        .section-content {
            border: 1px solid #333;
            padding: 8px;
        }

        /* Employee Details Table */
        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table td {
            padding: 4px 8px;
            border: 1px solid #ccc;
        }

        .details-table .label {
            background: #f5f5f5;
            font-weight: bold;
            width: 25%;
        }

        .details-table .value {
            width: 25%;
        }

        /* Criteria Table */
        .criteria-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }

        .criteria-table th,
        .criteria-table td {
            border: 1px solid #333;
            padding: 5px 6px;
            text-align: left;
        }

        .criteria-table th {
            background: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }

        .criteria-table .num {
            width: 25px;
            text-align: center;
        }

        .criteria-table .criteria {
            width: 25%;
        }

        .criteria-table .description {
            width: 35%;
            font-size: 9px;
        }

        .criteria-table .rating {
            width: 60px;
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }

        .criteria-table .remarks {
            width: 25%;
            font-size: 9px;
        }

        /* Rating colors */
        .rating-5 { background: #c8e6c9; }
        .rating-4 { background: #dcedc8; }
        .rating-3 { background: #fff9c4; }
        .rating-2 { background: #ffe0b2; }
        .rating-1 { background: #ffcdd2; }

        /* Summary Section */
        .summary-grid {
            display: flex;
            gap: 10px;
        }

        .summary-box {
            flex: 1;
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }

        .summary-box .label {
            font-size: 10px;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-box .value {
            font-size: 18px;
            font-weight: bold;
        }

        .summary-box .level {
            padding: 3px 10px;
            border: 1px solid #333;
            display: inline-block;
            font-weight: bold;
        }

        .checkbox-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 5px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .checkbox {
            width: 14px;
            height: 14px;
            border: 1px solid #333;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .checkbox.checked::after {
            content: "✓";
            font-weight: bold;
        }

        /* Comments */
        .comments-box {
            border: 1px solid #333;
            padding: 8px;
            min-height: 60px;
            margin-bottom: 10px;
        }

        .comments-box .title {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 5px;
            color: #333;
        }

        .comments-box .content {
            font-size: 10px;
            white-space: pre-wrap;
        }

        /* Signatures */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .signatures-table th,
        .signatures-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
        }

        .signatures-table th {
            background: #e0e0e0;
            font-weight: bold;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            height: 30px;
            margin: 10px 20px;
        }

        /* Rating Scale Legend */
        .rating-scale {
            font-size: 9px;
            margin-top: 8px;
            padding: 5px;
            background: #f9f9f9;
            border: 1px dashed #999;
        }

        .rating-scale span {
            margin-right: 15px;
        }

        /* Print styles */
        @media print {
            body {
                background: #fff;
            }

            .page {
                margin: 0;
                padding: 0;
                width: 100%;
                min-height: auto;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Print button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4f46e5;
            color: white;
            border: none;
            padding: 12px 24px;
            font-size: 14px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .print-button:hover {
            background: #4338ca;
        }

        .level-excellent { background: #c8e6c9; }
        .level-good { background: #dcedc8; }
        .level-satisfactory { background: #fff9c4; }
        .level-needs_improvement { background: #ffcdd2; }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
        </svg>
        Imprimir
    </button>

    <div class="page">
        {{-- Company Header with Logo --}}
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
        
        <div class="company-header">
            <div class="company-logo">
                @if(file_exists($logoFullPath))
                    <img src="{{ $logoFullPath }}" alt="{{ $companyName }}">
                @else
                    <img src="{{ asset('img/logo.png') }}" alt="{{ $companyName }}">
                @endif
            </div>
            <div class="company-info">
                <h2>{{ $companyName }}</h2>
                @if($companyAddress)
                    <p>{{ $companyAddress }}</p>
                @endif
                <p>
                    @if($companyPhone)Tel: {{ $companyPhone }}@endif
                    @if($companyPhone && $companyEmail) | @endif
                    @if($companyEmail)Email: {{ $companyEmail }}@endif
                </p>
                @if($companyTaxId || $companyWebsite)
                    <p>
                        @if($companyTaxId)NIF: {{ $companyTaxId }}@endif
                        @if($companyTaxId && $companyWebsite) | @endif
                        @if($companyWebsite){{ $companyWebsite }}@endif
                    </p>
                @endif
            </div>
        </div>

        {{-- Document Title --}}
        <div class="header">
            <h1>Quarterly Performance Appraisal Paper</h1>
            <div class="subtitle">Avaliação de Desempenho Trimestral</div>
        </div>

        {{-- Section 1: Employee Details --}}
        <div class="section">
            <div class="section-title">1. Employee Details / Dados do Funcionário</div>
            <div class="section-content">
                <table class="details-table">
                    <tr>
                        <td class="label">Employee Name</td>
                        <td class="value" colspan="3">{{ $evaluation->employee->full_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Employee ID</td>
                        <td class="value">{{ $evaluation->employee->id_card ?? 'N/A' }}</td>
                        <td class="label">Department / Line</td>
                        <td class="value">{{ $evaluation->department->name ?? $evaluation->employee->department->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Job Title</td>
                        <td class="value">{{ $evaluation->employee->position->title ?? 'N/A' }}</td>
                        <td class="label">Supervisor</td>
                        <td class="value">{{ $evaluation->supervisor->full_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Evaluation Period</td>
                        <td class="value">{{ $evaluation->evaluation_quarter }} / {{ $evaluation->evaluation_year }}</td>
                        <td class="label">Evaluation Date</td>
                        <td class="value">{{ $evaluation->evaluation_date?->format('d/m/Y') ?? now()->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Section 2: Performance Criteria --}}
        <div class="section">
            <div class="section-title">2. Performance Criteria / Critérios de Desempenho</div>
            <div class="section-content" style="padding: 0;">
                <table class="criteria-table">
                    <thead>
                        <tr>
                            <th class="num">#</th>
                            <th class="criteria">Criteria</th>
                            <th class="description">Description</th>
                            <th class="rating">Rating<br>(1-5)</th>
                            <th class="remarks">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $criteriaList = [
                                ['key' => 'productivity_output', 'name' => 'Productivity / Output', 'desc' => 'Meets or exceeds daily/weekly on production targets.'],
                                ['key' => 'quality_of_work', 'name' => 'Quality of Work', 'desc' => 'Produces work that meets quality standards with minimal rework or defects.'],
                                ['key' => 'attendance_punctuality', 'name' => 'Attendance & Punctuality', 'desc' => 'Reports to work on time; follows shift schedules and break times.'],
                                ['key' => 'safety_compliance', 'name' => 'Safety Compliance', 'desc' => 'Follows all safety procedures, uses PPE correctly, reports hazards.'],
                                ['key' => 'machine_operation_skills', 'name' => 'Machine Operation Skills', 'desc' => 'Efficient in operating assigned machines/equipment.'],
                                ['key' => 'teamwork_cooperation', 'name' => 'Teamwork & Cooperation', 'desc' => 'Works well with team members, supports others, communicates effectively.'],
                                ['key' => 'adaptability_learning', 'name' => 'Adaptability & Learning', 'desc' => 'Responds positively to new tasks, instructions, and training.'],
                                ['key' => 'housekeeping_5s', 'name' => 'Housekeeping (5S)', 'desc' => 'Keeps workstation clean and organized, follows 5S principles.'],
                                ['key' => 'discipline_attitude', 'name' => 'Discipline & Attitude', 'desc' => 'Follows company rules, shows positive attitude and respects.'],
                                ['key' => 'initiative_responsibility', 'name' => 'Initiative & Responsibility', 'desc' => 'Takes ownership of tasks, suggests improvements.'],
                            ];
                        @endphp
                        @foreach($criteriaList as $index => $criterion)
                            @php 
                                $rating = $evaluation->{$criterion['key']} ?? null;
                                $remarks = $evaluation->{$criterion['key'] . '_remarks'} ?? '';
                            @endphp
                            <tr>
                                <td class="num">{{ $index + 1 }}</td>
                                <td class="criteria">{{ $criterion['name'] }}</td>
                                <td class="description">{{ $criterion['desc'] }}</td>
                                <td class="rating {{ $rating ? 'rating-' . $rating : '' }}">{{ $rating ?? '-' }}</td>
                                <td class="remarks">{{ $remarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="rating-scale">
                    <strong>Rating Scale:</strong>
                    <span><strong>1</strong> = Poor</span>
                    <span><strong>2</strong> = Fair</span>
                    <span><strong>3</strong> = Satisfactory</span>
                    <span><strong>4</strong> = Good</span>
                    <span><strong>5</strong> = Excellent</span>
                </div>
            </div>
        </div>

        {{-- Section 3: Overall Performance Summary --}}
        <div class="section">
            <div class="section-title">3. Overall Performance Summary / Resumo do Desempenho</div>
            <div class="section-content">
                <div class="summary-grid">
                    <div class="summary-box">
                        <div class="label">Average Score / Média</div>
                        <div class="value">{{ $evaluation->average_score ? number_format($evaluation->average_score, 2) : '-' }}</div>
                    </div>
                    <div class="summary-box">
                        <div class="label">Performance Level / Nível</div>
                        <div>
                            @php
                                $levels = [
                                    'needs_improvement' => 'Needs Improvement',
                                    'satisfactory' => 'Satisfactory',
                                    'good' => 'Good',
                                    'excellent' => 'Excellent'
                                ];
                            @endphp
                            @foreach($levels as $key => $level)
                                <div style="display: inline-flex; align-items: center; margin: 2px 8px;">
                                    <span class="checkbox {{ $evaluation->performance_level === $key ? 'checked' : '' }}"></span>
                                    <span style="margin-left: 4px; font-size: 10px;">{{ $level }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="summary-box">
                        <div class="label">Eligible for Bonus / Promotion</div>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <span class="checkbox {{ $evaluation->eligible_for_bonus ? 'checked' : '' }}"></span>
                                <span>Yes</span>
                            </div>
                            <div class="checkbox-item">
                                <span class="checkbox {{ !$evaluation->eligible_for_bonus ? 'checked' : '' }}"></span>
                                <span>No</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Supervisor's Comments --}}
        <div class="section">
            <div class="section-title">4. Supervisor's Comments (Summarize key strengths, areas for improvement, and recommendations)</div>
            <div class="comments-box">
                <div class="content">{{ $evaluation->supervisor_comments ?? '' }}</div>
            </div>
        </div>

        {{-- Section 5: Employee's Comments --}}
        <div class="section">
            <div class="section-title">5. Employee's Comments (Employee can share feedback or concerns)</div>
            <div class="comments-box">
                <div class="content">{{ $evaluation->employee_comments ?? '' }}</div>
            </div>
        </div>

        {{-- Section 6: Signatures --}}
        <div class="section">
            <div class="section-title">6. Signatures / Assinaturas</div>
            <div class="section-content" style="padding: 0;">
                <table class="signatures-table">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Evaluated By</th>
                            <th style="width: 50%;">Signature</th>
                            <th style="width: 25%;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Supervisor</td>
                            <td>
                                <div class="signature-line"></div>
                            </td>
                            <td>{{ $evaluation->supervisor_signed_at?->format('d/m/Y') ?? '____/____/________' }}</td>
                        </tr>
                        <tr>
                            <td>Department Head</td>
                            <td>
                                <div class="signature-line"></div>
                            </td>
                            <td>{{ $evaluation->department_head_signed_at?->format('d/m/Y') ?? '____/____/________' }}</td>
                        </tr>
                        <tr>
                            <td>Employee</td>
                            <td>
                                <div class="signature-line"></div>
                            </td>
                            <td>{{ $evaluation->employee_signed_at?->format('d/m/Y') ?? '____/____/________' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer --}}
        <div style="margin-top: 15px; font-size: 9px; color: #666; text-align: center; border-top: 1px solid #2563eb; padding-top: 8px;">
            <p style="margin: 2px 0;">{{ $companyName }} &copy; {{ date('Y') }} - Todos os direitos reservados</p>
            <p style="margin: 2px 0;">Documento gerado em {{ now()->format('d/m/Y H:i') }} | ERP DEMBENA v{{ config('app.version', '1.0') }}</p>
        </div>
    </div>

    <script>
        // Auto print on load (optional - uncomment if needed)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
