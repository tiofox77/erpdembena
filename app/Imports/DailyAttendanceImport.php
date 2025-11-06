<?php

namespace App\Imports;

use App\Models\HR\Attendance;
use App\Models\HR\Employee;
use App\Models\HR\HRSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\IOFactory;

class DailyAttendanceImport implements ToCollection
{
    protected $importedCount = 0;
    protected $skippedCount = 0;
    protected $updatedCount = 0;
    protected $errors = [];
    protected $notFoundEmployees = [];
    protected $summary = [];
    protected $shiftMismatches = [];
    protected $filePath = null;

    public function __construct($filePath = null)
    {
        $this->filePath = $filePath;
    }

    public function collection(Collection $rows)
    {
        // This method won't be used directly
        // We'll process sheets manually in processFile()
    }

    /**
     * Process all sheets from the Excel file
     */
    public function processFile($filePath)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetNames = $spreadsheet->getSheetNames();

            \Log::info('Processing daily attendance file', [
                'sheets_count' => count($sheetNames),
                'sheets' => $sheetNames
            ]);

            foreach ($sheetNames as $sheetName) {
                $this->processSheet($spreadsheet->getSheetByName($sheetName), $sheetName);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Error processing daily attendance file', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Process a single sheet
     */
    protected function processSheet($sheet, $sheetName)
    {
        $sheetImported = 0;
        $sheetUpdated = 0;
        $sheetSkipped = 0;

        $highestRow = $sheet->getHighestRow();
        
        \Log::info("Processing sheet: {$sheetName}", ['rows' => $highestRow]);

        // Start from row 2 (skip header)
        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                $biometricId = $sheet->getCell('A' . $row)->getValue();
                $name = $sheet->getCell('B' . $row)->getValue();
                $dateValue = $sheet->getCell('C' . $row)->getValue();
                $checkIn = $sheet->getCell('D' . $row)->getValue();
                $checkOut = $sheet->getCell('E' . $row)->getValue();

                // Skip empty rows
                if (empty($biometricId) || empty($name)) {
                    continue;
                }

                // Buscar funcionário pelo Biometric ID
                $employee = Employee::where('biometric_id', $biometricId)->first();

                if (!$employee) {
                    $this->addNotFoundEmployee($biometricId, $name);
                    $sheetSkipped++;
                    continue;
                }

                // Converter data do Excel serial para Carbon
                $date = null;
                if (is_numeric($dateValue)) {
                    $date = Date::excelToDateTimeObject($dateValue)->format('Y-m-d');
                } elseif (!empty($dateValue)) {
                    try {
                        $date = Carbon::parse($dateValue)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $sheetSkipped++;
                        continue;
                    }
                }

                if (!$date) {
                    $sheetSkipped++;
                    continue;
                }

                // Parse horários
                $timeIn = $this->parseTime($checkIn, $date);
                $timeOut = $this->parseTime($checkOut, $date);

                // Validar compatibilidade com turno do funcionário
                if ($timeIn) {
                    $employeeShiftCheck = \App\Models\HR\ShiftAssignment::where('employee_id', $employee->id)
                        ->whereDate('start_date', '<=', $date)
                        ->where(function($q) use ($date) {
                            $q->whereNull('end_date')
                              ->orWhereDate('end_date', '>=', $date);
                        })
                        ->with('shift')
                        ->first();

                    if ($employeeShiftCheck && $employeeShiftCheck->shift) {
                        $shiftStart = Carbon::parse($employeeShiftCheck->shift->start_time->format('H:i'));
                        $shiftEnd = Carbon::parse($employeeShiftCheck->shift->end_time->format('H:i'));
                        $checkInCarbon = Carbon::parse($timeIn);
                        
                        // Detectar turno noturno (atravessa meia-noite)
                        $isNightShift = $shiftStart->gt($shiftEnd);
                        
                        $isCompatible = false;
                        
                        if ($isNightShift) {
                            // Turno noturno: verificar se está dentro do período com tolerância de 1 hora
                            $shiftStartWithTolerance = $shiftStart->copy()->subHours(1);
                            $shiftEndWithTolerance = $shiftEnd->copy()->addHours(1);
                            
                            if ($checkInCarbon->gte($shiftStartWithTolerance) || 
                                $checkInCarbon->lte($shiftEndWithTolerance)) {
                                $isCompatible = true;
                            }
                        } else {
                            // Turno normal: verificar com tolerância de 1 hora
                            $diffFromStart = abs($checkInCarbon->diffInMinutes($shiftStart));
                            $diffFromEnd = abs($checkInCarbon->diffInMinutes($shiftEnd));
                            
                            if ($diffFromStart <= 60 || $diffFromEnd <= 60) {
                                $isCompatible = true;
                            }
                        }
                        
                        // Se NÃO é compatível, adicionar aos mismatches e pular
                        if (!$isCompatible) {
                            $diffFromStart = abs($checkInCarbon->diffInMinutes($shiftStart));
                            $diffFromEnd = abs($checkInCarbon->diffInMinutes($shiftEnd));
                            $minDiff = min($diffFromStart, $diffFromEnd);
                            
                            $this->shiftMismatches[] = [
                                'employee_id' => $employee->id,
                                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                                'emp_id' => $employee->biometric_id,
                                'date' => $date,
                                'check_in' => $checkIn,
                                'check_out' => $checkOut,
                                'shift_name' => $employeeShiftCheck->shift->name,
                                'shift_start' => $employeeShiftCheck->shift->start_time->format('H:i'),
                                'shift_end' => $employeeShiftCheck->shift->end_time->format('H:i'),
                                'time_difference_minutes' => $minDiff,
                            ];
                            $this->skippedCount++;
                            $sheetSkipped++;
                            \Log::warning('Daily import: Shift mismatch detected', [
                                'employee' => $employee->first_name . ' ' . $employee->last_name,
                                'check_in' => $checkIn,
                                'shift' => $employeeShiftCheck->shift->name,
                                'sheet' => $sheetName
                            ]);
                            continue;
                        }
                    }
                }

                // Calcular hourly rate
                $baseSalary = $employee->base_salary ?? 0;
                $weeklyHours = (float) HRSetting::get('working_hours_per_week', 44);
                $monthlyHours = $weeklyHours * 4.33;
                $hourlyRate = $baseSalary > 0 ? round($baseSalary / $monthlyHours, 2) : 0.0;

                // Verificar se já existe
                $existingAttendance = Attendance::where('employee_id', $employee->id)
                    ->whereDate('date', $date)
                    ->first();

                $attendanceData = [
                    'employee_id' => $employee->id,
                    'date' => $date,
                    'time_in' => $timeIn,
                    'time_out' => $timeOut,
                    'status' => ($timeIn || $timeOut) ? 'present' : 'absent',
                    'hourly_rate' => $hourlyRate,
                    'affects_payroll' => true,
                    'remarks' => 'Importado de atendimento diário',
                ];

                if ($existingAttendance) {
                    $existingAttendance->update($attendanceData);
                    $this->updatedCount++;
                    $sheetUpdated++;
                } else {
                    Attendance::create($attendanceData);
                    $this->importedCount++;
                    $sheetImported++;
                }

            } catch (\Exception $e) {
                $this->addError([
                    'sheet' => $sheetName,
                    'row' => $row,
                    'error' => $e->getMessage()
                ]);
                $this->skippedCount++;
                $sheetSkipped++;
            }
        }

        // Add sheet summary
        if ($sheetImported > 0 || $sheetUpdated > 0) {
            $this->addToSummary($sheetName, $sheetImported, $sheetUpdated, $sheetSkipped);
        }
    }

    /**
     * Parse time from various formats
     */
    private function parseTime($timeValue, $date)
    {
        if (empty($timeValue)) {
            return null;
        }

        try {
            // Se já está em formato HH:MM
            if (preg_match('/^\s*(\d{1,2}):(\d{2})\s*$/', trim($timeValue), $matches)) {
                return Carbon::parse($date . ' ' . trim($timeValue));
            }

            // Se é decimal do Excel (0.3125 = 07:30)
            if (is_numeric($timeValue) && $timeValue < 1) {
                $totalSeconds = round($timeValue * 86400);
                $hours = floor($totalSeconds / 3600);
                $minutes = floor(($totalSeconds % 3600) / 60);
                return Carbon::parse($date)->setTime($hours, $minutes);
            }

            // Tentar parse direto
            return Carbon::parse($date . ' ' . $timeValue);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function addImported()
    {
        $this->importedCount++;
    }

    public function addUpdated()
    {
        $this->updatedCount++;
    }

    public function addSkipped()
    {
        $this->skippedCount++;
    }

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    public function addNotFoundEmployee($empId, $name)
    {
        if (!isset($this->notFoundEmployees[$empId])) {
            $this->notFoundEmployees[$empId] = $name;
        }
    }

    public function addToSummary($sheetName, $imported, $updated, $skipped)
    {
        $this->summary[] = [
            'sheet' => $sheetName,
            'imported' => $imported,
            'updated' => $updated,
            'skipped' => $skipped
        ];
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

    public function getNotFoundEmployees(): array
    {
        return $this->notFoundEmployees;
    }

    public function getSummary(): array
    {
        return $this->summary;
    }

    public function getShiftMismatches(): array
    {
        return $this->shiftMismatches;
    }

    /**
     * Generate preview of what will be imported
     */
    public static function generatePreview($filePath, $maxRowsPerSheet = 5)
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheetNames = $spreadsheet->getSheetNames();
            $preview = [
                'sheets' => [],
                'total_sheets' => count($sheetNames),
                'total_records' => 0,
                'employees_found' => 0,
                'employees_not_found' => 0,
                'warnings' => []
            ];

            foreach ($sheetNames as $sheetName) {
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $highestRow = $sheet->getHighestRow();
                
                $sheetData = [
                    'name' => $sheetName,
                    'total_rows' => $highestRow - 1, // Subtract header
                    'sample_rows' => [],
                    'found_count' => 0,
                    'not_found_count' => 0
                ];

                // Sample first N rows
                $rowsToSample = min($maxRowsPerSheet, $highestRow - 1);
                for ($row = 2; $row <= $rowsToSample + 1; $row++) {
                    $biometricId = $sheet->getCell('A' . $row)->getValue();
                    $name = $sheet->getCell('B' . $row)->getValue();
                    $dateValue = $sheet->getCell('C' . $row)->getValue();
                    $checkIn = $sheet->getCell('D' . $row)->getValue();
                    $checkOut = $sheet->getCell('E' . $row)->getValue();

                    if (empty($biometricId)) {
                        continue;
                    }

                    // Check if employee exists
                    $employee = Employee::where('biometric_id', $biometricId)->first();
                    $found = !is_null($employee);

                    if ($found) {
                        $sheetData['found_count']++;
                    } else {
                        $sheetData['not_found_count']++;
                    }

                    // Convert date
                    $date = null;
                    if (is_numeric($dateValue)) {
                        $date = Date::excelToDateTimeObject($dateValue)->format('d/m/Y');
                    }

                    $sheetData['sample_rows'][] = [
                        'biometric_id' => $biometricId,
                        'name' => $name,
                        'date' => $date,
                        'check_in' => $checkIn,
                        'check_out' => $checkOut,
                        'found' => $found
                    ];
                }

                $preview['sheets'][] = $sheetData;
                $preview['total_records'] += $sheetData['total_rows'];
                $preview['employees_found'] += $sheetData['found_count'];
                $preview['employees_not_found'] += $sheetData['not_found_count'];
            }

            return $preview;
        } catch (\Exception $e) {
            \Log::error('Error generating preview', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
