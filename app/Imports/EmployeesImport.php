<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\HR\Employee;
use App\Models\HR\Department;
use App\Models\HR\JobPosition;
use App\Models\HR\Bank;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EmployeesImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    public function model(array $row): ?Employee
    {
        // Skip empty rows
        if (empty($row['nome_completo']) && empty($row['full_name'])) {
            return null;
        }

        // Get full name from either Portuguese or English header
        $fullName = $this->getRowValue($row, ['nome_completo', 'full_name']);
        if (!$fullName) {
            return null;
        }

        // Check if employee already exists (priority: ID, then email, ID card, or tax number)
        $existingEmployee = null;
        
        // First, try to find by ID if provided
        $employeeId = $this->getRowValue($row, ['id', 'ID']);
        if (!empty($employeeId) && is_numeric($employeeId)) {
            $existingEmployee = Employee::find($employeeId);
        }
        
        // If not found by ID, search by unique fields
        if (!$existingEmployee) {
            $existingEmployee = Employee::where(function ($query) use ($row) {
                $email = $this->getRowValue($row, ['email', 'endereco_de_email']);
                if (!empty($email)) {
                    $query->where('email', $email);
                }
                $idCard = $this->getRowValue($row, ['bilhete_de_identidade', 'id_card']);
                if (!empty($idCard)) {
                    $query->orWhere('id_card', $idCard);
                }
                $taxNumber = $this->getRowValue($row, ['numero_contribuinte', 'tax_number']);
                if (!empty($taxNumber)) {
                    $query->orWhere('tax_number', $taxNumber);
                }
            })->first();
        }

        // Prepare employee data
        $employeeData = [
            'full_name' => $fullName,
            'id_card' => $this->convertToString($row['bilhete_de_identidade'] ?? $row['id_card'] ?? null),
            'biometric_id' => $this->convertToString($row['id_biometrico'] ?? $row['biometric_id'] ?? null),
            'tax_number' => $this->convertToString($row['numero_contribuinte'] ?? $row['tax_number'] ?? null),
            'email' => $this->getRowValue($row, ['email', 'endereco_de_email']),
            'phone' => $this->convertToString($row['telefone'] ?? $row['phone'] ?? null),
            'date_of_birth' => $this->parseDate($row['data_de_nascimento'] ?? $row['date_of_birth'] ?? null),
            'gender' => $this->parseGender($row['genero'] ?? $row['gender'] ?? null),
            'marital_status' => $this->parseMaritalStatus($row['estado_civil'] ?? $row['marital_status'] ?? null),
            'dependents' => (int)($row['dependentes'] ?? $row['dependents'] ?? 0),
            'address' => $this->convertToString($row['endereco'] ?? $row['address'] ?? null),
            'department_id' => $this->getDepartmentId($row['departamento'] ?? $row['department'] ?? null),
            'position_id' => $this->getPositionId($row['posicao'] ?? $row['position'] ?? null),
            'hire_date' => $this->parseDate($row['data_de_contratacao'] ?? $row['hire_date'] ?? null),
            'employment_status' => $this->parseEmploymentStatus($row['estado_do_emprego'] ?? $row['employment_status'] ?? 'active'),
            'bank_id' => $this->getBankId($row['nome_do_banco'] ?? $row['bank_name'] ?? null),
            'bank_account' => $this->convertToString($row['conta_bancaria'] ?? $row['bank_account'] ?? null),
            'bank_iban' => $this->convertToString($row['iban_do_banco'] ?? $row['bank_iban'] ?? null),
            'inss_number' => $this->convertToString($row['numero_inss'] ?? $row['inss_number'] ?? null),
            'base_salary' => $this->parseDecimal($row['salario_base'] ?? $row['base_salary'] ?? null),
            'food_benefit' => $this->parseDecimal($row['subsidio_de_alimentacao'] ?? $row['food_benefit'] ?? null),
            'transport_benefit' => $this->parseDecimal($row['subsidio_de_transporte'] ?? $row['transport_benefit'] ?? null),
            'bonus_amount' => $this->parseDecimal($row['valor_do_bonus'] ?? $row['bonus_amount'] ?? null),
        ];

        // Remove null values to avoid overriding existing data with null
        $employeeData = array_filter($employeeData, function($value) {
            return $value !== null && $value !== '';
        });

        if ($existingEmployee) {
            // Update existing employee with non-null values only
            $existingEmployee->update($employeeData);
            return null; // Don't create new model
        } else {
            // Create new employee
            return new Employee($employeeData);
        }
    }

    public function rules(): array
    {
        return [
            'nome_completo' => 'nullable|max:255',
            'full_name' => 'nullable|max:255',
            'email' => 'nullable|email',
            'endereco_de_email' => 'nullable|email',
            'bilhete_de_identidade' => 'nullable|max:50',
            'id_card' => 'nullable|max:50',
            'numero_contribuinte' => 'nullable|max:50',
            'tax_number' => 'nullable|max:50',
            'telefone' => 'nullable|max:20',
            'phone' => 'nullable|max:20',
            'endereco' => 'nullable|max:500',
            'address' => 'nullable|max:500',
            'conta_bancaria' => 'nullable|max:50',
            'bank_account' => 'nullable|max:50',
            'iban_do_banco' => 'nullable|max:50',
            'bank_iban' => 'nullable|max:50',
            'numero_inss' => 'nullable|max:50',
            'inss_number' => 'nullable|max:50',
        ];
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function parseDate(?string $date): ?Carbon
    {
        if (!$date) {
            return null;
        }

        try {
            // Try multiple date formats
            $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'm/d/Y'];
            
            foreach ($formats as $format) {
                try {
                    return Carbon::createFromFormat($format, $date);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return Carbon::parse($date);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseGender(?string $gender): ?string
    {
        if (!$gender) {
            return null;
        }

        $gender = strtolower(trim($gender));
        
        return match(true) {
            in_array($gender, ['masculino', 'male', 'm']) => 'male',
            in_array($gender, ['feminino', 'female', 'f']) => 'female',
            in_array($gender, ['outro', 'other', 'o']) => 'other',
            default => null,
        };
    }

    private function parseMaritalStatus(?string $status): ?string
    {
        if (!$status) {
            return null;
        }

        $status = strtolower(trim($status));
        
        return match(true) {
            in_array($status, ['solteiro', 'single', 's']) => 'single',
            in_array($status, ['casado', 'married', 'm']) => 'married',
            in_array($status, ['divorciado', 'divorced', 'd']) => 'divorced',
            in_array($status, ['viuvo', 'viúvo', 'widowed', 'w']) => 'widowed',
            default => null,
        };
    }

    private function parseEmploymentStatus(?string $status): string
    {
        if (!$status) {
            return 'active';
        }

        $status = strtolower(trim($status));
        
        return match(true) {
            in_array($status, ['ativo', 'active']) => 'active',
            in_array($status, ['de_licenca', 'on_leave', 'licenca']) => 'on_leave',
            in_array($status, ['demitido', 'terminated']) => 'terminated',
            in_array($status, ['suspenso', 'suspended']) => 'suspended',
            in_array($status, ['aposentado', 'retired']) => 'retired',
            default => 'active',
        };
    }

    private function parseDecimal(string|int|float|null $value): ?float
    {
        // Se for null ou vazio, retorna null
        if ($value === null || $value === '') {
            return null;
        }

        // Se já for numérico (int ou float), converte diretamente
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Se for string, processa diferentes formatos
        $value = trim($value);
        
        // Remove símbolos de moeda e espaços
        // Mantém apenas dígitos, vírgulas, pontos e sinais negativos
        $value = preg_replace('/[^\d,.-]/', '', $value);
        
        if (empty($value)) {
            return null;
        }

        // Detecta o formato baseado na posição de vírgula e ponto
        // Formato PT/BR: 100.000,00 ou 1.000.000,00
        // Formato US: 100,000.00 ou 1,000,000.00
        // Formato simples: 100000 ou 100000.00
        
        $hasComma = strpos($value, ',') !== false;
        $hasDot = strpos($value, '.') !== false;
        
        if ($hasComma && $hasDot) {
            // Tem ambos - detectar qual é o separador decimal
            $lastCommaPos = strrpos($value, ',');
            $lastDotPos = strrpos($value, '.');
            
            if ($lastCommaPos > $lastDotPos) {
                // Formato PT/BR: 100.000,00
                // Remove pontos (separadores de milhares) e converte vírgula para ponto
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                // Formato US: 100,000.00
                // Remove vírgulas (separadores de milhares)
                $value = str_replace(',', '', $value);
            }
        } elseif ($hasComma) {
            // Só tem vírgula
            // Detectar se é separador decimal ou de milhares
            $commaPos = strrpos($value, ',');
            $afterComma = substr($value, $commaPos + 1);
            
            if (strlen($afterComma) <= 2) {
                // Provavelmente é separador decimal: 100,00
                $value = str_replace(',', '.', $value);
            } else {
                // Provavelmente são milhares: 1,000 ou 10,000
                $value = str_replace(',', '', $value);
            }
        } elseif ($hasDot) {
            // Só tem ponto
            // Detectar se é separador decimal ou de milhares
            $dotPos = strrpos($value, '.');
            $afterDot = substr($value, $dotPos + 1);
            
            if (strlen($afterDot) <= 2 && strlen($afterDot) > 0) {
                // Provavelmente é separador decimal: 100.00
                // Já está no formato correto
            } else {
                // Provavelmente são milhares: 1.000 ou 10.000
                $value = str_replace('.', '', $value);
            }
        }
        // Se não tem vírgula nem ponto, já está no formato correto: 100000
        
        return is_numeric($value) ? (float) $value : null;
    }

    private function getDepartmentId(?string $departmentName): ?int
    {
        if (!$departmentName) {
            return null;
        }

        $department = Department::where('name', 'like', '%' . trim($departmentName) . '%')
            ->where('is_active', true)
            ->first();

        return $department?->id;
    }

    private function getPositionId(?string $positionTitle): ?int
    {
        if (!$positionTitle) {
            return null;
        }

        $position = JobPosition::where('title', 'like', '%' . trim($positionTitle) . '%')
            ->where('is_active', true)
            ->first();

        return $position?->id;
    }

    private function getBankId(?string $bankName): ?int
    {
        if (!$bankName) {
            return null;
        }

        $bank = Bank::where('name', 'like', '%' . trim($bankName) . '%')
            ->where('is_active', true)
            ->first();

        return $bank?->id;
    }

    /**
     * Convert any value to string, handling Excel numeric values
     */
    private function convertToString($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        return (string) $value;
    }

    /**
     * Safely get value from row using multiple possible keys
     */
    private function getRowValue(array $row, array $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row) && $row[$key] !== null && $row[$key] !== '') {
                return $row[$key];
            }
        }
        return null;
    }
}
