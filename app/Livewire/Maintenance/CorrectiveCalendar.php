<?php

namespace App\Livewire\Maintenance;

use Livewire\Component;
use Carbon\Carbon;
use App\Models\MaintenanceCorrective;
use App\Models\Holiday;

class CorrectiveCalendar extends Component
{
    public $currentMonth;
    public $currentYear;
    public $calendarTitle;
    public $calendarDays = [];
    public $events = [];
    public $selectedDate;
    public $selectedDateEvents = [];
    public $holidays = [];

    // Array of color classes for events
    private $eventColors = [
        'open' => 'bg-red-100 text-red-800',
        'in_progress' => 'bg-yellow-100 text-yellow-800',
        'resolved' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-gray-100 text-gray-800',
    ];

    public function mount()
    {
        // Initialize with current month
        $this->currentMonth = Carbon::now()->month;
        $this->currentYear = Carbon::now()->year;
        $this->selectedDate = Carbon::now()->format('Y-m-d');
        
        // Load holidays for the current year
        $this->loadHolidays();
        
        // Generate calendar and load events
        $this->generateCalendar();
        $this->loadEvents();
    }

    // Format date for consistency
    private function formatDate($date)
    {
        return Carbon::parse($date)->format('Y-m-d');
    }

    // Load holidays for the current year
    private function loadHolidays()
    {
        $this->holidays = [];
        $holidays = Holiday::where('is_active', true)
            ->where(function($query) {
                $query->whereYear('date', $this->currentYear)
                    ->orWhere('is_recurring', true);
            })
            ->get();

        foreach ($holidays as $holiday) {
            try {
                if ($holiday->is_recurring) {
                    // Para feriados recorrentes, garantir que o formato da data esteja correto
                    $dateStr = is_string($holiday->date) ? substr($holiday->date, 0, 10) : $holiday->date;
                    $date = Carbon::parse($dateStr)->startOfDay();
                    $dateInCurrentYear = Carbon::createFromDate($this->currentYear, $date->month, $date->day)->format('Y-m-d');
                    $this->holidays[$dateInCurrentYear] = $holiday->title;
                } else {
                    // Para feriados não recorrentes
                    $dateStr = is_string($holiday->date) ? substr($holiday->date, 0, 10) : $holiday->date;
                    $formattedDate = Carbon::parse($dateStr)->format('Y-m-d');
                    $this->holidays[$formattedDate] = $holiday->title;
                }
            } catch (\Exception $e) {
                // Se ocorrer um erro no processamento de uma data, pule para o próximo feriado
                continue;
            }
        }
    }

    // Generate calendar grid for the current month
    public function generateCalendar()
    {
        $this->calendarTitle = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->format('F Y');
        $this->calendarDays = [];
        
        // First day of the month
        $firstDayOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1);
        
        // Get the day of the week for the first day (0 = Sunday, 6 = Saturday)
        $firstDayOfWeek = $firstDayOfMonth->dayOfWeek;
        
        // Last day of the month
        $lastDayOfMonth = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->endOfMonth();
        
        // Previous month days to show
        $prevMonthLastDay = $firstDayOfMonth->copy()->subDay();
        $prevMonthStart = $prevMonthLastDay->copy()->subDays($firstDayOfWeek - 1);
        
        // Generate previous month days
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $date = $prevMonthStart->copy()->addDays($i);
            $this->calendarDays[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->day,
                'isCurrentMonth' => false,
                'isToday' => $date->isToday(),
                'isWeekend' => $date->isWeekend(),
                'isHoliday' => isset($this->holidays[$date->format('Y-m-d')])
            ];
        }
        
        // Generate current month days
        for ($i = 1; $i <= $lastDayOfMonth->day; $i++) {
            $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, $i);
            $this->calendarDays[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->day,
                'isCurrentMonth' => true,
                'isToday' => $date->isToday(),
                'isWeekend' => $date->isWeekend(),
                'isHoliday' => isset($this->holidays[$date->format('Y-m-d')])
            ];
        }
        
        // Next month days to fill up the grid
        $remainingDays = 42 - count($this->calendarDays); // 6 rows * 7 columns = 42
        $nextMonthFirstDay = $lastDayOfMonth->copy()->addDay();
        
        for ($i = 0; $i < $remainingDays; $i++) {
            $date = $nextMonthFirstDay->copy()->addDays($i);
            $this->calendarDays[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $date->day,
                'isCurrentMonth' => false,
                'isToday' => $date->isToday(),
                'isWeekend' => $date->isWeekend(),
                'isHoliday' => isset($this->holidays[$date->format('Y-m-d')])
            ];
        }
    }

    // Get color class for a specific status
    private function getEventColor($status)
    {
        return $this->eventColors[$status] ?? 'bg-blue-100 text-blue-800';
    }

    // Load corrective maintenance events for the current month
    public function loadEvents()
    {
        // Define the first and last day of the month to fetch events
        $startDate = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Fetch maintenance corrective events
        try {
            $correctives = MaintenanceCorrective::with(['equipment'])
                ->whereBetween('start_time', [$startDate, $endDate])
                ->get();

            // Clear existing events
            $this->events = [];

            foreach ($correctives as $corrective) {
                try {
                    // Garantir que start_time é um objeto Carbon válido
                    $startTime = $corrective->start_time;
                    if (!($startTime instanceof Carbon)) {
                        $startTime = Carbon::parse($startTime);
                    }
                    
                    $formattedDate = $startTime->format('Y-m-d');
                    
                    // Get color for this event
                    $colorClass = $this->getEventColor($corrective->status);

                    $this->events[$formattedDate][] = [
                        'id' => $corrective->id,
                        'title' => $corrective->equipment ? $corrective->equipment->name : 'Equipment',
                        'equipment' => $corrective->equipment ? $corrective->equipment->name : 'Unknown',
                        'status' => $corrective->status,
                        'start_time' => $startTime->format('H:i'),
                        'description' => $corrective->notes ?? 'No description',
                        'color' => $colorClass,
                    ];
                } catch (\Exception $innerException) {
                    // Se houver erro ao processar um evento específico, apenas pule para o próximo
                    continue;
                }
            }

            // Load events for the selected date
            $this->updateSelectedDateEvents();

        } catch (\Exception $e) {
            // Log error but don't display to user
            // Log::error('Error loading events: ' . $e->getMessage());
        }
    }

    // Update events for the selected date
    public function updateSelectedDateEvents()
    {
        $this->selectedDateEvents = $this->events[$this->selectedDate] ?? [];
    }

    // Navigate to the previous month
    public function previousMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->subMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        
        $this->generateCalendar();
        $this->loadEvents();
    }

    // Navigate to the next month
    public function nextMonth()
    {
        $date = Carbon::createFromDate($this->currentYear, $this->currentMonth, 1)->addMonth();
        $this->currentMonth = $date->month;
        $this->currentYear = $date->year;
        
        $this->generateCalendar();
        $this->loadEvents();
    }

    // Select a specific date
    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->updateSelectedDateEvents();
    }

    // Reset to the current month
    public function resetToday()
    {
        $now = Carbon::now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
        $this->selectedDate = $now->format('Y-m-d');
        
        $this->generateCalendar();
        $this->loadEvents();
        $this->updateSelectedDateEvents();
    }

    public function render()
    {
        return view('livewire.maintenance.corrective-calendar');
    }
    
    /**
     * Generate PDF of the corrective maintenance calendar
     */
    public function generatePdf()
    {
        try {
            // Prepare the data for the PDF
            $data = [
                'title' => __('messages.corrective_maintenance_calendar'),
                'month' => $this->calendarTitle,
                'calendarDays' => $this->calendarDays,
                'events' => $this->events,
                'holidays' => $this->holidays,
                'generatedAt' => now()->format('Y-m-d H:i:s'),
            ];
            
            // Load the PDF view
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('pdf.corrective-calendar', $data);
            
            $filename = 'corrective_maintenance_calendar_' . $this->currentYear . '_' . $this->currentMonth . '.pdf';
            
            $this->dispatch('notify', 
                type: 'success', 
                message: __('messages.pdf_generated_successfully')
            );
            
            return response()->streamDownload(
                fn () => print($pdf->output()),
                $filename,
                ['Content-Type' => 'application/pdf']
            );
        } catch (\Exception $e) {
            \Log::error('Error generating corrective maintenance calendar PDF: ' . $e->getMessage());
            $this->dispatch('notify', 
                type: 'error', 
                message: __('messages.pdf_generation_failed')
            );
            return null;
        }
    }
}
