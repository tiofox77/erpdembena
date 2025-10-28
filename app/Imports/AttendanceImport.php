<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\HR\Attendance;
use App\Models\HR\Employee;
use App\Models\HR\HRSetting;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class AttendanceImport implements 
    ToModel, 
    WithHeadingRow, 
    WithValidation, 
    WithBatchInserts, 
    WithChunkReading,
    SkipsOnError
{
    use SkipsErrors;

    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $updatedCount = 0;
    protected $errors = [];
    protected $processedEmployeeDates = []; // Track processed employee+date combinations
    protected $timeConflicts = []; // Store time conflicts for user confirmation
    protected $notFoundEmployees = []; // Track employees not found in system
    protected $pendingRecords = []; // Para agrupar check-ins e check-outs do ZKTime
    protected $isZKTimeFormat = false; // Detecta se é formato ZKTime

    public function model(array $row): ?Attendance
    {
        \Log::info('Processing row', ['row' => $row]);
        
        // Detecta formato ZKTime (tem coluna "Time" e "Attendance State")
        if ($this->hasZKTimeColumns($row)) {
            $this->isZKTimeFormat = true;
            return $this->processZKTimeRow($row);
        }
        
        // Skip empty rows
        $empId = $this->getRowValue($row, ['emp_id', 'empid', 'employee_id']);
        if (empty($empId)) {
            \Log::warning('Skipping row - empty Emp ID');
            $this->skippedCount++;
            return null;
        }
        
        \Log::info('Found Emp ID', ['emp_id' => $empId]);

        // Find employee by biometric_id
        $employee = Employee::where('biometric_id', (string)$empId)->first();
        
        if (!$employee) {
            // Track employee not found
            if (!in_array($empId, $this->notFoundEmployees)) {
                $this->notFoundEmployees[] = $empId;
            }
            $this->errors[] = "Funcionário não encontrado (Emp ID: {$empId})";
            $this->skippedCount++;
            \Log::warning('Employee not found in system', ['emp_id' => $empId]);
            return null;
        }

        // Parse date
        $date = $this->parseDate($this->getRowValue($row, ['date', 'data', 'fecha']));
        if (!$date) {
            $this->errors[] = "Data inválida para Emp ID: {$empId}";
            $this->skippedCount++;
            return null;
        }

        // Check if we already processed this employee+date in this import
        $employeeDateKey = $employee->id . '_' . $date->format('Y-m-d');
        if (isset($this->processedEmployeeDates[$employeeDateKey])) {
            \Log::warning('Skipping duplicate row', [
                'employee' => $employee->full_name,
                'date' => $date->format('Y-m-d'),
                'emp_id' => $empId
            ]);
            $this->skippedCount++;
            return null;
        }
        
        // Mark as processed
        $this->processedEmployeeDates[$employeeDateKey] = true;

        // Check if attendance already exists in database
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $date)
            ->first();

        // Parse times - support multiple column name variations
        $checkInRaw = $this->getRowValue($row, ['check_in', 'checkin', 'check_in', 'entrada']);
        $checkOutRaw = $this->getRowValue($row, ['check_out', 'checkout', 'check_out', 'saida']);
        
        \Log::info('Parsing times for employee', [
            'emp_id' => $empId,
            'employee' => $employee->full_name,
            'check_in_raw' => $checkInRaw,
            'check_out_raw' => $checkOutRaw
        ]);
        
        // Detect multiple times (e.g., "12:09 17:10")
        $checkInTimes = $this->parseMultipleTimes($checkInRaw);
        $checkOutTimes = $this->parseMultipleTimes($checkOutRaw);
        
        \Log::info('Detected times', [
            'emp_id' => $empId,
            'check_in_times' => $checkInTimes,
            'check_out_times' => $checkOutTimes,
            'has_conflict' => (count($checkInTimes) > 1 || count($checkOutTimes) > 1)
        ]);
        
        // If multiple times detected, store conflict for user confirmation
        if ((count($checkInTimes) > 1 || count($checkOutTimes) > 1)) {
            \Log::warning('Time conflict detected', [
                'emp_id' => $empId,
                'employee' => $employee->full_name
            ]);
            
            $this->timeConflicts[] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'emp_id' => $empId,
                'date' => $date->format('Y-m-d'),
                'check_in_options' => $checkInTimes,
                'check_out_options' => $checkOutTimes,
                'check_in_raw' => $checkInRaw,
                'check_out_raw' => $checkOutRaw,
            ];
            $this->skippedCount++;
            return null; // Skip this row, will process after user confirmation
        }
        
        $checkIn = $checkInTimes[0] ?? null;
        $checkOut = $checkOutTimes[0] ?? null;

        // Determine status
        $status = 'present';
        if (!$checkIn && !$checkOut) {
            $absenceValue = $this->getRowValue($row, ['absence', 'ausencia']);
            // Se há valor na coluna Absence (como 9:00), mas não há Check-In/Check-Out, é ausência
            $status = 'absent';
        } elseif ($checkIn && !$checkOut) {
            $status = 'present'; // Entrou mas não registrou saída ainda
        }

        // Calculate hourly rate
        $baseSalary = $employee->base_salary ?? 0;
        $weeklyHours = (float) HRSetting::get('working_hours_per_week', 44);
        $monthlyHours = $weeklyHours * 4.33;
        $hourlyRate = $baseSalary > 0 ? round($baseSalary / $monthlyHours, 2) : 0.0;

        // Prepare attendance data (only fields that exist in database)
        $attendanceData = [
            'employee_id' => $employee->id,
            'date' => $date->format('Y-m-d'),
            'time_in' => $checkIn ? Carbon::parse($date->format('Y-m-d') . ' ' . $checkIn) : null,
            'time_out' => $checkOut ? Carbon::parse($date->format('Y-m-d') . ' ' . $checkOut) : null,
            'status' => $status,
            'hourly_rate' => $hourlyRate,
            'affects_payroll' => true,
            'remarks' => 'Importado do sistema biométrico',
        ];

        if ($existingAttendance) {
            // Update existing attendance
            $existingAttendance->update($attendanceData);
            $this->updatedCount++;
            return null;
        }

        // Create new attendance
        $this->importedCount++;
        \Log::info('Creating attendance', [
            'employee' => $employee->full_name,
            'date' => $date->format('Y-m-d'),
            'status' => $status,
            'data' => $attendanceData
        ]);
        
        // Create and explicitly save the model
        $attendance = new Attendance($attendanceData);
        try {
            $saved = $attendance->save();
            \Log::info('Attendance saved', ['success' => $saved, 'id' => $attendance->id]);
        } catch (\Exception $e) {
            \Log::error('Failed to save attendance', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw $e;
        }
        
        return null; // Return null because we already saved it
    }

    public function rules(): array
    {
        // Não aplicar validação aqui porque:
        // 1. Formato ZKTime não tem coluna 'date' (tem 'time')
        // 2. Formato tradicional tem 'date'
        // 3. Validação é feita manualmente no método model()
        return [];
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getUpdatedCount(): int
    {
        return $this->updatedCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getTimeConflicts(): array
    {
        return $this->timeConflicts;
    }

    public function hasTimeConflicts(): bool
    {
        return !empty($this->timeConflicts);
    }

    public function getNotFoundEmployees(): array
    {
        return $this->notFoundEmployees;
    }

    public function getNotFoundCount(): int
    {
        return count($this->notFoundEmployees);
    }

    public function getIsZKTimeFormat(): bool
    {
        return $this->isZKTimeFormat;
    }

    /**
     * Parse multiple times from a string (e.g., "12:09 17:10" -> ["12:09", "17:10"])
     */
    private function parseMultipleTimes(?string $timeValue): array
    {
        if (!$timeValue) {
            return [];
        }

        // Split by space, comma, or semicolon
        $times = preg_split('/[\s,;]+/', trim($timeValue));
        $validTimes = [];

        foreach ($times as $time) {
            $parsed = $this->parseTime($time);
            if ($parsed) {
                $validTimes[] = $parsed;
            }
        }

        return $validTimes;
    }

    /**
     * Parse date from various formats
     */
    private function parseDate($dateValue): ?Carbon
    {
        if (!$dateValue) {
            return null;
        }

        try {
            // Handle Excel date serial numbers
            if (is_numeric($dateValue)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue));
            }

            // Try multiple date formats
            $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y', 'Y/m/d'];
            
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $dateValue);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return Carbon::parse($dateValue);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse time from various formats
     */
    private function parseTime($timeValue): ?string
    {
        if (!$timeValue) {
            return null;
        }

        try {
            // Handle Excel time serial numbers (0.0 to 1.0)
            if (is_numeric($timeValue) && $timeValue >= 0 && $timeValue < 1) {
                $seconds = $timeValue * 86400; // Convert fraction to seconds
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                return sprintf('%02d:%02d', $hours, $minutes);
            }

            // Handle time formats like "10:30", "10:30:00", etc.
            if (is_string($timeValue)) {
                // Remove any leading/trailing whitespace
                $timeValue = trim($timeValue);
                
                // If it's already in HH:MM format
                if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $timeValue, $matches)) {
                    return sprintf('%02d:%02d', $matches[1], $matches[2]);
                }
            }

            // Try to parse as Carbon
            $time = Carbon::parse($timeValue);
            return $time->format('H:i');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Safely get value from row using multiple possible keys
     */
    private function getRowValue(array $row, array $keys)
    {
        // First try exact matches
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }
        
        // If no exact match, try fuzzy match (case insensitive, ignore special chars)
        foreach ($keys as $key) {
            $normalizedKey = strtolower(str_replace(['_', '-', ' '], '', $key));
            foreach (array_keys($row) as $rowKey) {
                $normalizedRowKey = strtolower(str_replace(['_', '-', ' '], '', $rowKey));
                if ($normalizedKey === $normalizedRowKey && $row[$rowKey] !== null && $row[$rowKey] !== '') {
                    return $row[$rowKey];
                }
            }
        }
        
        return null;
    }

    /**
     * Verifica se o arquivo tem colunas no formato ZKTime
     */
    private function hasZKTimeColumns(array $row): bool
    {
        $hasTime = $this->getRowValue($row, ['time', 'tempo', 'hora']) !== null;
        $hasAttendanceState = $this->getRowValue($row, ['attendance_state', 'attendancestate', 'estado', 'state']) !== null;
        
        return $hasTime && $hasAttendanceState;
    }

    /**
     * Processa uma linha no formato ZKTime
     * Formato: cada check-in/out é uma linha separada
     */
    private function processZKTimeRow(array $row): ?Attendance
    {
        $empId = $this->getRowValue($row, ['emp_id', 'empid', 'employee_id']);
        if (empty($empId)) {
            $this->skippedCount++;
            return null;
        }

        // Find employee
        $employee = Employee::where('biometric_id', (string)$empId)->first();
        if (!$employee) {
            if (!in_array($empId, $this->notFoundEmployees)) {
                $this->notFoundEmployees[] = $empId;
            }
            $this->errors[] = "Funcionário não encontrado (Emp ID: {$empId})";
            $this->skippedCount++;
            return null;
        }

        // Parse date and time from "Time" column (e.g., "28/10/2025 08:14:54")
        $timeValue = $this->getRowValue($row, ['time', 'tempo', 'hora']);
        if (!$timeValue) {
            $this->errors[] = "Tempo inválido para Emp ID: {$empId}";
            $this->skippedCount++;
            return null;
        }

        // Parse datetime
        try {
            $datetime = Carbon::createFromFormat('d/m/Y H:i:s', $timeValue);
            if (!$datetime) {
                // Try other formats
                $datetime = Carbon::parse($timeValue);
            }
        } catch (\Exception $e) {
            $this->errors[] = "Formato de data/hora inválido para Emp ID: {$empId} - {$timeValue}";
            $this->skippedCount++;
            return null;
        }

        $date = $datetime->format('Y-m-d');
        $time = $datetime->format('H:i');

        // Get attendance state (Check In or Check Out)
        $attendanceState = $this->getRowValue($row, ['attendance_state', 'attendancestate', 'estado', 'state']);
        $isCheckIn = stripos($attendanceState, 'check in') !== false || stripos($attendanceState, 'entrada') !== false;

        // Create key for grouping
        $key = $employee->id . '_' . $date;

        // Store in pending records
        if (!isset($this->pendingRecords[$key])) {
            $this->pendingRecords[$key] = [
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name,
                'emp_id' => $empId,
                'date' => $date,
                'check_ins' => [],
                'check_outs' => [],
            ];
        }

        if ($isCheckIn) {
            $this->pendingRecords[$key]['check_ins'][] = $time;
        } else {
            $this->pendingRecords[$key]['check_outs'][] = $time;
        }

        \Log::info('ZKTime row added to pending', [
            'emp_id' => $empId,
            'date' => $date,
            'time' => $time,
            'type' => $isCheckIn ? 'check_in' : 'check_out'
        ]);

        // Don't create attendance yet, will be processed in finalizePendingRecords()
        return null;
    }

    /**
     * Finaliza o processamento dos registos pendentes do ZKTime
     * Deve ser chamado após todas as linhas serem processadas
     */
    public function finalizePendingRecords(): void
    {
        if (empty($this->pendingRecords)) {
            return;
        }

        \Log::info('Finalizing pending ZKTime records', ['count' => count($this->pendingRecords)]);

        foreach ($this->pendingRecords as $key => $record) {
            try {
                $employee = Employee::find($record['employee_id']);
                if (!$employee) {
                    continue;
                }

                $totalRecords = count($record['check_ins']) + count($record['check_outs']);
                
                // Só mostrar conflito se houver 3 ou mais registros
                if ($totalRecords >= 3) {
                    $this->timeConflicts[] = [
                        'employee_id' => $record['employee_id'],
                        'employee_name' => $record['employee_name'],
                        'emp_id' => $record['emp_id'],
                        'date' => $record['date'],
                        'check_in_options' => $record['check_ins'],
                        'check_out_options' => $record['check_outs'],
                    ];
                    $this->skippedCount++;
                    continue;
                }

                // Se tiver exatamente 2 registros, usar lógica inteligente baseada no shift
                if ($totalRecords == 2 && (empty($record['check_ins']) || empty($record['check_outs']))) {
                    // Todos os registros estão como check-in ou check-out
                    // Precisamos determinar qual é entrada e qual é saída baseado no shift
                    $allTimes = array_merge($record['check_ins'], $record['check_outs']);
                    sort($allTimes);
                    
                    // Buscar shift do funcionário
                    $employeeShift = \App\Models\HR\ShiftAssignment::where('employee_id', $employee->id)
                        ->whereDate('start_date', '<=', $record['date'])
                        ->where(function($q) use ($record) {
                            $q->whereNull('end_date')
                              ->orWhereDate('end_date', '>=', $record['date']);
                        })
                        ->with('shift')
                        ->first();
                    
                    if ($employeeShift && $employeeShift->shift) {
                        $shiftStartTime = $employeeShift->shift->start_time->format('H:i');
                        
                        // Comparar qual horário está mais próximo do início do turno
                        $time1 = Carbon::parse($allTimes[0]);
                        $time2 = Carbon::parse($allTimes[1]);
                        $shiftStart = Carbon::parse($shiftStartTime);
                        
                        $diff1 = abs($time1->diffInMinutes($shiftStart));
                        $diff2 = abs($time2->diffInMinutes($shiftStart));
                        
                        // O horário mais próximo do início do turno é a entrada
                        if ($diff1 < $diff2) {
                            $checkIn = $allTimes[0];
                            $checkOut = $allTimes[1];
                        } else {
                            $checkIn = $allTimes[1];
                            $checkOut = $allTimes[0];
                        }
                        
                        \Log::info('Smart time assignment based on shift', [
                            'employee' => $record['employee_name'],
                            'shift_start' => $shiftStartTime,
                            'times' => $allTimes,
                            'assigned_in' => $checkIn,
                            'assigned_out' => $checkOut
                        ]);
                    } else {
                        // Sem shift, usar ordem cronológica
                        $checkIn = $allTimes[0];
                        $checkOut = $allTimes[1];
                    }
                } else {
                    // Caso normal: 1 check-in e 1 check-out, ou apenas 1 registro
                    $checkIn = $record['check_ins'][0] ?? null;
                    $checkOut = $record['check_outs'][0] ?? null;
                }

                // Calcular hourly rate
                $baseSalary = $employee->base_salary ?? 0;
                $weeklyHours = (float) HRSetting::get('working_hours_per_week', 44);
                $monthlyHours = $weeklyHours * 4.33;
                $hourlyRate = $baseSalary > 0 ? round($baseSalary / $monthlyHours, 2) : 0.0;

                // Verificar se já existe
                $existingAttendance = Attendance::where('employee_id', $record['employee_id'])
                    ->whereDate('date', $record['date'])
                    ->first();

                $attendanceData = [
                    'employee_id' => $record['employee_id'],
                    'date' => $record['date'],
                    'time_in' => $checkIn ? Carbon::parse($record['date'] . ' ' . $checkIn) : null,
                    'time_out' => $checkOut ? Carbon::parse($record['date'] . ' ' . $checkOut) : null,
                    'status' => ($checkIn || $checkOut) ? 'present' : 'absent',
                    'hourly_rate' => $hourlyRate,
                    'affects_payroll' => true,
                    'remarks' => 'Importado do ZKTime',
                ];

                if ($existingAttendance) {
                    $existingAttendance->update($attendanceData);
                    $this->updatedCount++;
                } else {
                    Attendance::create($attendanceData);
                    $this->importedCount++;
                }

                \Log::info('ZKTime attendance created/updated', [
                    'employee' => $record['employee_name'],
                    'date' => $record['date'],
                    'check_in' => $checkIn,
                    'check_out' => $checkOut
                ]);

            } catch (\Exception $e) {
                \Log::error('Error finalizing ZKTime record', [
                    'error' => $e->getMessage(),
                    'record' => $record
                ]);
                $this->errors[] = "Erro ao processar {$record['employee_name']} em {$record['date']}: " . $e->getMessage();
            }
        }

        // Limpar registos pendentes
        $this->pendingRecords = [];
    }
}
