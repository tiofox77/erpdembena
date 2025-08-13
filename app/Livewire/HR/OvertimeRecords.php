<?php

declare(strict_types=1);

namespace App\Livewire\HR;

use App\Models\HR\Employee;
use App\Models\HR\OvertimeRecord;
use App\Models\HR\HRSetting;
use App\Models\HR\ShiftAssignment;
use App\Models\HR\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class OvertimeRecords extends Component
{
    use WithPagination;
    
    // Propriedades do formulário
    public ?int $overtime_id = null;
    public ?int $employee_id = null;
    public ?int $employee_shift_id = null; // ID do turno atribuído ao funcionário
    public ?string $employee_shift_name = null;
    public ?string $date = null;
    public ?string $start_time = null;
    public ?string $end_time = null;
    public ?float $rate = null; // Taxa horária para horas extras (com multiplicador)
    public ?float $hourly_rate = null; // Taxa horária regular
    public ?float $hours = null; // Horas calculadas
    public ?float $amount = null; // Valor calculado
    public ?string $description = null;
    public string $status = 'pending';
    public ?float $direct_hours = null; // Horas diretas (para input_type 'daily' ou 'monthly')
    public string $input_type = 'time_range'; // 'time_range', 'daily', 'monthly'
    public string $period_type = 'day';
    public float $additionalHoursMultiplier = 1.375; // Multiplicador para horas extras adicionais em dia útil
    public bool $is_night_shift = false; // Indica se é turno noturno (checkbox)
    public ?float $night_shift_multiplier = null; // Multiplicador para turno noturno
    public ?string $created_at = null; // Data de criação para a modal de visualização
    public ?string $updated_at = null; // Data de atualização para a modal de visualização
    public ?string $approver_name = null; // Nome do aprovador para a modal de visualização
    
    // Limites para horas extras
    public float $dailyLimit = 2.0; // Padrão: 2 horas por dia
    public float $monthlyLimit = 48.0; // Padrão: 48 horas por mês
    public float $yearlyLimit = 200.0; // Padrão: 200 horas por ano
    
    // Estados da modal
    public bool $showModal = false;
    public bool $showViewModal = false;
    public bool $showDeleteModal = false;
    public bool $isEditing = false;
    public float $employee_salary = 0.0;
    public string $employee_name = '';
    
    // Filtros
    public array $filters = [
        'search' => '',
        'status' => '',
        'date_from' => '',
        'date_to' => '',
        'employee_id' => '',
    ];
    
    // Ordenação
    public string $sortField = 'date';
    public string $sortDirection = 'desc';
    
    // Proteção contra mass assignment
    protected $listeners = [
        'refreshOvertimeRecords' => '$refresh',
        'approveOvertime' => 'approve',
        'rejectOvertime' => 'reject'
    ];

    /**
     * Aprova um registro de horas extras
     */
    public function approve(int $id): void
    {
        try {
            $record = OvertimeRecord::findOrFail($id);
            
            // Verificar se o registo ainda está pendente
            if ($record->status !== 'pending') {
                session()->flash('error', __('messages.overtime_already_processed'));
                return;
            }
            
            $record->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            
            session()->flash('message', __('messages.overtime_approved_successfully'));
            
        } catch (\Exception $e) {
            session()->flash('error', __('messages.error_approving_overtime'));
        }
    }

    /**
     * Recusa um registro de horas extras
     */
    public function reject(int $id): void
    {
        try {
            $record = OvertimeRecord::findOrFail($id);
            
            // Verificar se o registo ainda está pendente
            if ($record->status !== 'pending') {
                session()->flash('error', __('messages.overtime_already_processed'));
                return;
            }
            
            $record->update([
                'status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);
            
            session()->flash('message', __('messages.overtime_rejected_successfully'));
            
        } catch (\Exception $e) {
            session()->flash('error', __('messages.error_rejecting_overtime'));
        }
    }
    
    /**
     * Método de inicialização do componente
     */
    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
        $this->loadHRSettings();
    }
    
    /**
     * Carrega configurações de RH incluindo limites legais de horas extras
     */
    private function loadHRSettings(): void
    {
        // Carregar limites de horas extras das configurações
        $this->dailyLimit = (float) HRSetting::get('overtime_daily_limit', 2.0);
        $this->monthlyLimit = (float) HRSetting::get('overtime_monthly_limit', 48.0);
        $this->yearlyLimit = (float) HRSetting::get('overtime_yearly_limit', 200.0);
    }
    
    /**
     * Regras de validação para o formulário
     */
    protected function rules(): array
    {
        $rules = [
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'rate' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,rejected',
            'input_type' => 'required|in:time_range,daily,monthly',
        ];
        
        if ($this->input_type === 'time_range') {
            $rules['start_time'] = 'required';
            $rules['end_time'] = 'required|after:start_time';
        } else {
            $rules['direct_hours'] = 'required|numeric|min:0.01|max:744'; // Max horas mensais (31 dias * 24h)
            $rules['period_type'] = 'required|in:day,month';
        }
        
        return $rules;
    }
    
    /**
     * Define nomes amigáveis para os atributos na validação
     */
    protected function validationAttributes(): array
    {
        return [
            'employee_id' => __('messages.employee'),
            'date' => __('messages.date'),
            'start_time' => __('messages.start_time'),
            'end_time' => __('messages.end_time'),
            'rate' => __('messages.hourly_rate'),
            'description' => __('messages.description'),
            'status' => __('messages.status'),
        ];
    }
    
    /**
     * Método para renderizar o componente
     */
    public function render()
    {
        $employees = Employee::orderBy('full_name')->get();
        
        $overtimeRecordsQuery = OvertimeRecord::with(['employee', 'approver'])
            ->when($this->filters['search'] ?? false, function ($query, $search) {
                return $query->whereHas('employee', function ($subquery) use ($search) {
                    $subquery->where('full_name', 'like', '%' . $search . '%');
                });
            })
            ->when($this->filters['employee_id'] ?? false, function ($query, $employeeId) {
                return $query->where('employee_id', $employeeId);
            })
            ->when($this->filters['status'] ?? false, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($this->filters['date_from'] ?? false, function ($query, $dateFrom) {
                return $query->where('date', '>=', $dateFrom);
            })
            ->when($this->filters['date_to'] ?? false, function ($query, $dateTo) {
                return $query->where('date', '<=', $dateTo);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $overtimeRecords = $overtimeRecordsQuery->paginate(10);
        
        return view('livewire.hr.overtime-records', [
            'overtimeRecords' => $overtimeRecords,
            'employees' => $employees,
        ]);
    }
    
    /**
     * Método para ordenar os registos
     */
    public function sortBy($field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }
    
    /**
     * Cria um novo registo de hora extra
     */
    public function create(): void
    {
        $this->reset(['overtime_id', 'employee_id', 'date', 'start_time', 'end_time', 'rate', 'hours', 'amount', 'description', 'status']);
        $this->status = 'pending';
        $this->date = date('Y-m-d');
        $this->isEditing = false;
        $this->showModal = true;
    }
    
    /**
     * Edita um registo de hora extra existente
     */
    public function edit(int $id): void
    {
        $this->overtime_id = $id;
        $this->isEditing = true;
        
        $overtime = OvertimeRecord::findOrFail($id);
        $this->employee_id = $overtime->employee_id;
        $this->date = $overtime->date->format('Y-m-d');
        $this->start_time = $overtime->start_time;
        $this->end_time = $overtime->end_time;
        $this->hours = (float) $overtime->hours;
        $this->rate = (float) $overtime->rate;
        $this->amount = (float) $overtime->amount;
        $this->description = $overtime->description;
        $this->status = $overtime->status;
        $this->input_type = $overtime->input_type ?? 'time_range';
        $this->period_type = $overtime->period_type ?? 'day';
        $this->direct_hours = (float) $overtime->direct_hours;
        $this->is_night_shift = (bool) ($overtime->is_night_shift ?? false);
        
        // Buscar informações do funcionário
        if ($this->employee_id) {
            $this->loadEmployeeShift();
            $this->loadHourlyRate();
            $this->night_shift_multiplier = (float) HRSetting::get('night_shift_multiplier', 1.25);
        }
        
        $this->showModal = true;
    }
    
    /**
     * Visualiza os detalhes de um registo de hora extra
     */
    public function view(int $id): void
    {
        $this->overtime_id = $id;
        
        $overtime = OvertimeRecord::with(['employee', 'approver'])->findOrFail($id);
        
        // Dados básicos
        $this->employee_id = $overtime->employee_id;
        $this->employee_name = $overtime->employee->full_name ?? '';
        $this->date = $overtime->date->format('Y-m-d');
        $this->start_time = $overtime->start_time;
        $this->end_time = $overtime->end_time;
        $this->hours = (float) $overtime->hours;
        $this->rate = (float) $overtime->rate;
        $this->hourly_rate = (float) $overtime->hourly_rate;
        $this->amount = (float) $overtime->amount;
        $this->description = $overtime->description;
        $this->status = $overtime->status;
        
        // Busca dados de turno e tipo de período
        $this->input_type = $overtime->input_type ?? 'time_range';
        $this->period_type = $overtime->period_type ?? 'day';
        $this->direct_hours = (float) ($overtime->direct_hours ?? 0);
        $this->is_night_shift = (bool) ($overtime->is_night_shift ?? false);
        
        // Dados de turno
        if ($overtime->employee && $overtime->employee->activeShift) {
            $this->employee_shift_id = $overtime->employee->activeShift->shift_id;
            $this->employee_shift_name = $overtime->employee->activeShift->shift->name ?? 'N/A';
        }
        
        // Dados de aprovação
        $this->approver_name = $overtime->approver->full_name ?? null;
        $this->created_at = $overtime->created_at ? $overtime->created_at->format('Y-m-d H:i:s') : null;
        $this->updated_at = $overtime->updated_at ? $overtime->updated_at->format('Y-m-d H:i:s') : null;
        
        // Multiplicadores para exibição
        $this->night_shift_multiplier = (float) HRSetting::get('night_shift_multiplier', 1.25);
        
        $this->showViewModal = true;
    }
    
    /**
     * Calcula horas e valor total baseado no tipo de entrada selecionado
     * Considera configurações de HR, turnos, fim de semana e turnos noturnos
     */
    public function calculateHoursAndAmount(): void
    {
        try {
            // Limpa erros anteriores
            $this->resetErrorBag(['time_diff', 'direct_hours', 'start_time', 'end_time', 'legal_limit']);
            
            // Verifica se temos os dados básicos necessários
            if (!$this->employee_id || !$this->date) {
                return;
            }
            
            // Carrega a taxa horária automática se não estiver definida
            if (!$this->rate) {
                $this->loadHourlyRate();
            }
            
            // Buscar configurações HR relevantes para cálculos
            $minOvertimeMinutes = (int) HRSetting::get('min_overtime_minutes', 15);
            $roundToNearest = (int) HRSetting::get('round_to_nearest_minutes', 15);
            $allowPartialHours = (bool) HRSetting::get('allow_partial_hours', true);
            $dailyWorkHours = (float) HRSetting::get('daily_work_hours', 8);
            $monthlyWorkHours = (float) HRSetting::get('monthly_work_hours', 176); // 22 dias x 8 horas
            $weekendMultiplier = (float) HRSetting::get('weekend_multiplier', 1.5);
            $holidayMultiplier = (float) HRSetting::get('holiday_multiplier', 2.0);
            $nightShiftMultiplier = (float) HRSetting::get('night_shift_multiplier', 1.25);
            
            // Guardar o multiplicador para exibição na interface
            $this->night_shift_multiplier = $nightShiftMultiplier;
            
            // Busca limites legais de horas extras
            $dailyOvertimeLimit = (float) HRSetting::get('overtime_daily_limit', 2.0);
            $monthlyOvertimeLimit = (float) HRSetting::get('overtime_monthly_limit', 48.0);
            $yearlyOvertimeLimit = (float) HRSetting::get('overtime_yearly_limit', 200.0);
            
            $isWeekend = $this->isWeekend();
            $calculatedHours = 0;
            
            // Cálculo depende do método de entrada selecionado
            switch ($this->input_type) {
                case 'time_range':
                    $calculatedHours = $this->calculateFromTimeRange($minOvertimeMinutes, $roundToNearest, $allowPartialHours);
                    // Se o checkbox não estiver marcado manualmente, tenta detectar automaticamente
                    if (!$this->is_night_shift) {
                        $this->is_night_shift = $this->isNightShift(); // Detecta automaticamente apenas se não estiver marcado manualmente
                    }
                    break;
                    
                case 'daily':
                    if (!$this->direct_hours || $this->direct_hours <= 0) {
                        return;
                    }
                    
                    // Para horas diárias, considera diretamente as horas extras inseridas
                    // Já que o utilizador está a inserir apenas as horas extras (não o total)
                    $calculatedHours = (float) $this->direct_hours;
                    
                    if ($calculatedHours <= 0) {
                        session()->flash('error_hours_below_daily_minimum', __('messages.hours_below_daily_minimum', ['hours' => '0']));
                        $this->addError('direct_hours', __('messages.hours_below_daily_minimum', ['hours' => '0']));
                        return;
                    }
                    break;
                    
                case 'monthly':
                    if (!$this->direct_hours || $this->direct_hours <= 0) {
                        return;
                    }
                    
                    // Para horas mensais, consideramos diretamente as horas extras inseridas
                    // Já que o utilizador está a inserir apenas as horas extras (não o total)
                    $calculatedHours = (float) $this->direct_hours;
                    
                    if ($calculatedHours <= 0) {
                        $this->addError('direct_hours', __('messages.hours_below_daily_minimum', ['hours' => '0']));
                        return;
                    }
                    break;
            }
            
            // Se não há horas calculadas, sair
            if ($calculatedHours <= 0) {
                $this->hours = 0;
                $this->amount = 0;
                return;
            }
            
            // Aplica arredondamento se necessário
            if (!$allowPartialHours) {
                $calculatedHours = ceil($calculatedHours);
            }
            
            $this->hours = $calculatedHours;
            
            // Cálculo especial para Angola conforme legislação trabalhista
            // Em dias úteis: 1ª hora +25%, horas adicionais +37,5%
            // Em feriados/descanso: +50% (ou conforme configuração)
            
            $totalAmount = 0;
            
            // Verifica se é feriado ou fim de semana
            $isHoliday = $this->isHoliday();
            
            // Para feriados e fins de semana, todas as horas têm o mesmo multiplicador
            if ($isHoliday || $isWeekend) {
                // Já calculamos a taxa com o multiplicador correto no loadHourlyRate()
                $totalAmount = $calculatedHours * (float)$this->rate;
            } else {
                // Para dias úteis, aplicamos regra especial: primeira hora com um multiplicador, demais com outro
                if ($calculatedHours <= 1) {
                    // Se é apenas uma hora ou menos, usa multiplicador da primeira hora
                    $totalAmount = $calculatedHours * (float)$this->rate;
                } else {
                    // Se é mais de uma hora, calcula separado
                    // Primeira hora com multiplicador padrão
                    $firstHourAmount = 1 * (float)$this->rate;
                    
                    // Horas adicionais com multiplicador específico
                    $additionalHours = $calculatedHours - 1;
                    $additionalHoursRate = $this->hourly_rate * (float)$this->additionalHoursMultiplier;
                    $additionalAmount = $additionalHours * $additionalHoursRate;
                    
                    $totalAmount = $firstHourAmount + $additionalAmount;
                }
            }
            
            // Aplica multiplicador de turno noturno (adicional)
            if ($this->is_night_shift) {
                $totalAmount *= $nightShiftMultiplier;
            }
            
            // Verificação dos limites legais de horas extras
            if (!$isWeekend && !$isHoliday) { // Só aplica limites em dias úteis
                // Verifica limite diário - APENAS para o modo 'daily' ou 'time_range'
                if ($this->input_type !== 'monthly' && $calculatedHours > $dailyOvertimeLimit) {
                    $this->addError('legal_limit', __('messages.overtime_daily_limit_exceeded', [
                        'hours' => number_format($dailyOvertimeLimit, 1),
                        'requested' => number_format($calculatedHours, 1)
                    ]));
                }
                
                // Verifica total mensal (implementação básica)
                $employeeId = $this->employee_id;
                $currentMonth = \Carbon\Carbon::parse($this->date)->format('Y-m');
                
                $monthlyTotal = OvertimeRecord::where('employee_id', $employeeId)
                    ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$currentMonth])
                    ->where('id', '!=', $this->overtime_id ?: 0) // Exclui o registro atual se for edição
                    ->sum('hours');
                
                $projectedMonthlyTotal = $monthlyTotal + $calculatedHours;
                
                if ($projectedMonthlyTotal > $monthlyOvertimeLimit) {
                    $this->addError('legal_limit', __('messages.overtime_monthly_limit_exceeded', [
                        'hours' => number_format($monthlyOvertimeLimit, 1),
                        'current' => number_format($monthlyTotal, 1),
                        'projected' => number_format($projectedMonthlyTotal, 1)
                    ]));
                }
            }
            
            $this->amount = round($totalAmount, 2);
            
        } catch (\Exception $e) {
            $this->addError('calculation', __('messages.overtime_calculation_error') . ': ' . $e->getMessage());
        }
    }
    
    /**
     * Calcula horas extras baseado no intervalo de tempo
     */
    private function calculateFromTimeRange(int $minOvertimeMinutes, int $roundToNearest, bool $allowPartialHours): float
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }
        
        try {
            $start = new \DateTime($this->start_time);
            $end = new \DateTime($this->end_time);
            
            // Se o horário de fim é antes do início, assume que é no dia seguinte
            if ($end <= $start) {
                $end->add(new \DateInterval('P1D'));
            }
            
            // Calcula a diferença total em minutos
            $interval = $start->diff($end);
            $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
            
            // Verifica o mínimo de minutos
            if ($totalMinutes < $minOvertimeMinutes) {
                $this->addError('time_diff', __('messages.overtime_below_minimum_minutes', ['minutes' => $minOvertimeMinutes]));
                return 0;
            }
            
            // Arredonda para o intervalo mais próximo se configurado
            if ($roundToNearest > 0) {
                $totalMinutes = round($totalMinutes / $roundToNearest) * $roundToNearest;
            }
            
            // Converte para horas
            $hours = $totalMinutes / 60;
            
            // Considera apenas as horas extras (acima do horário normal de trabalho)
            $weeklyHours = (float) HRSetting::get('working_hours_per_week', 44);
            $dailyWorkHours = $weeklyHours / 5;
            $overtimeHours = max(0, $hours - $dailyWorkHours);
            
            return $allowPartialHours ? round($overtimeHours, 2) : ceil($overtimeHours);
            
        } catch (\Exception $e) {
            $this->addError('time_range', __('messages.invalid_time_range'));
            return 0;
        }
    }
    
    /**
     * Verifica se é feriado
     */
    private function isHoliday(): bool
    {
        // Implementação simples - pode ser expandida com uma tabela de feriados
        if (!$this->date) {
            return false;
        }
        
        $date = Carbon::parse($this->date);
        
        // Verifica feriados fixos comuns em Angola
        $holidays = [
            '01-01', // Ano Novo
            '02-04', // Dia da Paz
            '03-08', // Dia Internacional da Mulher
            '04-04', // Dia da Paz e Reconciliação Nacional
            '05-01', // Dia do Trabalhador
            '09-17', // Dia dos Heróis Nacionais
            '11-02', // Dia dos Finados
            '11-11', // Dia da Independência Nacional
            '12-25', // Natal
        ];
        
        $dateString = $date->format('m-d');
        return in_array($dateString, $holidays);
    }
    
    /**
     * Verifica se a data é fim de semana
     */
    private function isWeekend(): bool
    {
        if (!$this->date) {
            return false;
        }
        
        $date = Carbon::parse($this->date);
        return $date->isWeekend();
    }
    
    /**
     * Verifica se o horário é turno noturno
     */
    private function isNightShift(): bool
    {
        if (!$this->start_time) {
            return false;
        }
        
        // Considera turno noturno entre 22:00 e 06:00
        $nightStart = (int) HRSetting::get('night_shift_start_hour', 22);
        $nightEnd = (int) HRSetting::get('night_shift_end_hour', 6);
        
        $hour = (int) date('H', strtotime($this->start_time));
        
        return $hour >= $nightStart || $hour <= $nightEnd;
    }
    
    /**
     * Carrega a taxa horária automática baseada no funcionário e nas configurações HR
     */
    private function loadHourlyRate(): void
    {
        if (!$this->employee_id) {
            return;
        }
        
        try {
            $employee = Employee::find($this->employee_id);
            
            if ($employee) {
                // Buscar configurações HR para cálculos
                $weeklyHours = (float) HRSetting::get('working_hours_per_week', 44); // Padrão: 44 horas semanais
                $monthlyHours = $weeklyHours * 4.33; // 4.33 semanas por mês em média
                
                // Buscar multiplicadores conforme a lei angolana
                $overtimeFirstHourWeekday = (float) HRSetting::get('overtime_first_hour_weekday', 0.0); // 1ª hora: +25%
                if ($overtimeFirstHourWeekday <= 0) {
                    // Fallback para chave unificada
                    $overtimeFirstHourWeekday = (float) HRSetting::get('overtime_multiplier_weekday', 1.25);
                    if ($overtimeFirstHourWeekday <= 0) {
                        $overtimeFirstHourWeekday = 1.25;
                    }
                }
                $overtimeAdditionalHoursWeekday = (float) HRSetting::get('overtime_additional_hours_weekday', 0.0); // Demais horas: +37,5%
                if ($overtimeAdditionalHoursWeekday <= 0) {
                    // Se não existir configuração específica, usar o mesmo multiplicador da primeira hora
                    $overtimeAdditionalHoursWeekday = $overtimeFirstHourWeekday;
                }
                // Suporte a chaves legadas e novas
                $overtimeMultiplierWeekend = (float) HRSetting::get('overtime_multiplier_weekend', HRSetting::get('weekend_multiplier', 2.0)); // Fins de semana: +100%
                $overtimeMultiplierHoliday = (float) HRSetting::get('overtime_multiplier_holiday', HRSetting::get('holiday_multiplier', 2.5)); // Feriados: +150%
                
                // Tenta buscar a taxa horária do funcionário se já estiver definida
                if (isset($employee->hourly_rate) && $employee->hourly_rate > 0) {
                    $this->hourly_rate = (float) $employee->hourly_rate;
                } else {
                    // Se não tem taxa definida, calcula baseado no salário base
                    if (isset($employee->base_salary) && $employee->base_salary > 0) {
                        // Cálculo da taxa horária normal (sem multiplicador)
                        $this->hourly_rate = round($employee->base_salary / $monthlyHours, 2);
                    } else {
                        // Taxa horária mínima padrão
                        $this->hourly_rate = (float) HRSetting::get('default_hourly_rate', 10.00);
                    }
                }
                
                // Determinar qual multiplicador usar baseado no dia da semana e feriado
                $isHoliday = $this->isHoliday();
                $isWeekend = $this->isWeekend();
                
                if ($isHoliday) {
                    $this->rate = round($this->hourly_rate * $overtimeMultiplierHoliday, 2);
                } elseif ($isWeekend) {
                    $this->rate = round($this->hourly_rate * $overtimeMultiplierWeekend, 2);
                } else {
                    // Em dia útil, a primeira hora tem multiplicador diferente das demais
                    // Por padrão, usamos o multiplicador da primeira hora
                    // O ajuste para horas adicionais é feito no cálculo final
                    $this->rate = round($this->hourly_rate * $overtimeFirstHourWeekday, 2);
                    
                    // Guardamos o multiplicador para horas adicionais para usar no cálculo
                    $this->additionalHoursMultiplier = $overtimeAdditionalHoursWeekday;
                }
            }
        } catch (\Exception $e) {
            // Em caso de erro, usa taxa padrão
            $this->hourly_rate = (float) HRSetting::get('default_hourly_rate', 10.00);
            $this->rate = (float) HRSetting::get('default_overtime_rate', 15.00);
        }
    }
    
    /**
     * Hooks para cálculo automático quando campos relevantes são alterados
     */
    public function updatedEmployeeId(): void
    {
        if ($this->employee_id) {
            $employee = Employee::with(['shiftAssignments.shift'])->find($this->employee_id);
            
            if ($employee) {
                $this->employee_name = $employee->full_name ?? '';
                $this->employee_salary = (float) ($employee->base_salary ?? 0.0);
                
                // Buscar o turno actual do funcionário
                $currentShift = $employee->shiftAssignments()
                    ->with('shift')
                    ->where(function($query) {
                        $today = now()->toDateString();
                        $query->where('start_date', '<=', $today)
                              ->where(function($q) use ($today) {
                                  $q->where('end_date', '>=', $today)
                                    ->orWhereNull('end_date');
                              });
                    })
                    ->first();
                    
                if ($currentShift && $currentShift->shift) {
                    $this->employee_shift_id = $currentShift->shift->id;
                    $this->employee_shift_name = $currentShift->shift->name ?? '';
                } else {
                    $this->employee_shift_id = 0; // Usar 0 em vez de null para evitar problemas de tipo
                    $this->employee_shift_name = 'Sem turno atribuído';
                }
            }
        } else {
            $this->employee_name = '';
            $this->employee_salary = 0.0;
            $this->employee_shift_id = 0; // Usar 0 em vez de null para evitar problemas de tipo
            $this->employee_shift_name = '';
        }
        
        $this->loadHourlyRate();
        $this->calculateHoursAndAmount();
    }
    public function updatedDate(): void
    {
        // Recarregar a taxa para refletir alteração entre dia útil/fim de semana/feriado
        $this->loadHourlyRate();
        $this->calculateHoursAndAmount();
    }
    
    public function updatedInputType(): void
    {
        // Reset campos quando tipo de entrada muda
        $this->start_time = '';
        $this->end_time = '';
        $this->direct_hours = null;
        $this->hours = null;
        $this->amount = null;
        
        $this->calculateHoursAndAmount();
    }
    
    public function updatedStartTime(): void
    {
        $this->calculateHoursAndAmount();
    }
    
    public function updatedEndTime(): void
    {
        $this->calculateHoursAndAmount();
    }
    
    public function updatedDirectHours(): void
    {
        $this->calculateHoursAndAmount();
    }
    
    public function updatedRate(): void
    {
        $this->calculateHoursAndAmount();
    }
    
    /**
     * Hook para recalcular valores quando o toggle de turno noturno é alterado
     */
    public function updatedIsNightShift(): void
    {
        $this->calculateHoursAndAmount();
    }
    
    /**
     * Salva o registo de hora extra
     */
    public function save(): void
    {
        // Valida e calcula valores antes da validação do formulário
        $this->validateAndCalculate();
        $this->validate();
        
        if ($this->overtime_id) {
            $overtime = OvertimeRecord::findOrFail($this->overtime_id);
        } else {
            $overtime = new OvertimeRecord();
        }
        
        $overtime->employee_id = $this->employee_id;
        $overtime->date = $this->date;
        $overtime->hours = $this->hours;
        $overtime->rate = $this->rate;
        $overtime->amount = $this->amount;
        $overtime->description = $this->description;
        $overtime->status = $this->status;
        $overtime->input_type = $this->input_type;
        $overtime->is_night_shift = $this->is_night_shift;
        
        // Salva os dados específicos dependendo do tipo de entrada
        if ($this->input_type === 'time_range') {
            $overtime->start_time = $this->start_time;
            $overtime->end_time = $this->end_time;
            $overtime->period_type = null;
        } else {
            // Para entrada direta, limpa os campos de hora e armazena o tipo de período
            $overtime->start_time = null;
            $overtime->end_time = null;
            $overtime->period_type = $this->period_type;
        }
        
        if ($this->status === 'approved' && $overtime->approved_by === null) {
            $overtime->approved_by = Auth::id();
            $overtime->approved_at = now();
        }
        
        $overtime->save();
        
        $this->showModal = false;
        $this->resetErrorBag();
        $this->reset(['overtime_id', 'employee_id', 'date', 'start_time', 'end_time', 'rate', 'hours', 'amount', 'description', 'status', 'direct_hours', 'input_type', 'period_type', 'is_night_shift']);
        $this->input_type = 'time_range';
        $this->period_type = 'day';
        
        if ($this->isEditing) {
            session()->flash('message', __('messages.overtime_updated'));
        } else {
            session()->flash('message', __('messages.overtime_created'));
        }
    }
    
    /**
     * Carrega informações sobre o turno atribuído ao funcionário
     * e preenche os horários de início/fim com base no turno, se estiver no modo time_range
     */
    private function loadEmployeeShift(): void
    {
        if (!$this->employee_id) {
            return;
        }
        
        try {
            // Buscar o funcionário com seu turno associado
            $employee = Employee::with('shift')->find($this->employee_id);
            
            if ($employee && $employee->shift) {
                $this->employee_shift_name = $employee->shift->name;
                $this->employee_shift_id = $employee->shift->id;
                
                // Se estiver no modo de intervalo de tempo, sugere os horários com base no turno
                if ($this->input_type === 'time_range' && !$this->isEditing) {
                    // Apenas sugere se os campos estiverem vazios (para não sobrescrever valores já inseridos)
                    if (empty($this->start_time)) {
                        $this->start_time = $employee->shift->end_time;
                    }
                    
                    // Estima um tempo de fim para 2 horas depois do fim do turno regular
                    if (empty($this->end_time) && $this->start_time) {
                        try {
                            $endTime = new \DateTime($this->start_time);
                            $endTime->modify('+2 hours');
                            $this->end_time = $endTime->format('H:i');
                        } catch (\Exception $e) {
                            // Ignora erro na manipulação de hora
                        }
                    }
                }
                
                // Emite um evento para mostrar informações sobre o turno na UI
                $this->dispatch('shift-loaded', [
                    'name' => $employee->shift->name,
                    'start_time' => $employee->shift->start_time,
                    'end_time' => $employee->shift->end_time,
                    'is_night_shift' => $employee->shift->is_night_shift
                ]);
            }
        } catch (\Exception $e) {
            \Log::warning('Erro ao carregar turno do funcionário: ' . $e->getMessage());
        }
    }
    
    /**
     * Método para validar e calcular antes de salvar
     */
    private function validateAndCalculate(): void
    {
        if ($this->input_type === 'time_range') {
            $this->calculateHoursAndAmount();
        } elseif ($this->input_type === 'daily' || $this->input_type === 'monthly') {
            // Para entrada direta, transfere as horas inseridas manualmente
            if ($this->direct_hours !== null) {
                $this->hours = $this->direct_hours;
                // Calcula o valor total multiplicando a taxa horária pelas horas
                if ($this->rate !== null && $this->hours !== null) {
                    $this->amount = round($this->rate * $this->hours, 2);
                }
            }
        }
    }
    
    /**
     * Fecha a modal de adição/edição
     */
    public function closeModal(): void
    {
        $this->showModal = false;
        $this->isEditing = false;
        $this->resetErrorBag();
        $this->reset(['overtime_id', 'employee_id', 'employee_shift_id', 'employee_shift_name', 'date', 'start_time', 'end_time', 'rate', 'hours', 'amount', 'description', 'status', 'direct_hours', 'input_type', 'period_type']);
        $this->input_type = 'time_range';
        $this->period_type = 'day';
    }
    
    /**
     * Fecha a modal de visualização
     */
    public function closeViewModal(): void
    {
        $this->showViewModal = false;
    }
    
    /**
     * Método para confirmar a exclusão de um registro
     */
    public function confirmDelete(int $id): void
    {
        $this->overtime_id = $id;
        $this->showDeleteModal = true;
    }
    
    /**
     * Método para excluir um registro
     */
    public function delete(): void
    {
        OvertimeRecord::destroy($this->overtime_id);
        $this->showDeleteModal = false;
        $this->overtime_id = null;
        session()->flash('message', __('messages.overtime_deleted'));
    }
}
