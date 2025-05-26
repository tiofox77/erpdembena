/**
 * Atualizar um plano diário de produção
 *
 * @param int $index Índice do plano a ser atualizado
 * @param array $data Dados atualizados
 */
public function updateDailyPlan($index, $data = null)
{
    try {
        // Verificar se o plano existe
        if (!isset($this->filteredDailyPlans[$index])) {
            throw new \Exception(__('messages.daily_plan_not_found'));
        }

        // Se não foram passados dados específicos, usar os dados do formulário
        if (!$data) {
            $data = $this->filteredDailyPlans[$index];
        }

        // Assegurar que o shift_id está definido
        if (!isset($data['shift_id']) && $this->selectedShiftId) {
            $data['shift_id'] = $this->selectedShiftId;
        }

        \Illuminate\Support\Facades\Log::info('Atualizando plano diário', [
            'index' => $index,
            'data' => $data,
            'shift_id' => $data['shift_id'] ?? $this->selectedShiftId ?? null
        ]);

        // Se o plano já existe no banco, atualizar
        if (isset($this->filteredDailyPlans[$index]['id']) && $this->filteredDailyPlans[$index]['id']) {
            $plan = ProductionDailyPlan::findOrFail($this->filteredDailyPlans[$index]['id']);
            
            // Salvar a quantidade atual produzida antes da atualização
            $previousActualQuantity = $plan->actual_quantity ?? 0;
            
            // Atualizar os campos do plano
            $plan->production_date = $data['production_date'] ?? $this->filteredDailyPlans[$index]['production_date'];
            $plan->start_time = $data['start_time'] ?? $this->filteredDailyPlans[$index]['start_time'];
            $plan->end_time = $data['end_time'] ?? $this->filteredDailyPlans[$index]['end_time'];
            $plan->planned_quantity = $data['planned_quantity'] ?? $this->filteredDailyPlans[$index]['planned_quantity'];
            $plan->actual_quantity = $data['actual_quantity'] ?? $this->filteredDailyPlans[$index]['actual_quantity'] ?? 0;
            $plan->defect_quantity = $data['defect_quantity'] ?? $this->filteredDailyPlans[$index]['defect_quantity'] ?? 0;
            $plan->has_breakdown = $data['has_breakdown'] ?? $this->filteredDailyPlans[$index]['has_breakdown'] ?? false;
            $plan->breakdown_minutes = $data['breakdown_minutes'] ?? $this->filteredDailyPlans[$index]['breakdown_minutes'] ?? 0;
            $plan->failure_category_id = $data['failure_category_id'] ?? $this->filteredDailyPlans[$index]['failure_category_id'] ?? null;
            $plan->failure_root_causes = $data['failure_root_causes'] ?? $this->filteredDailyPlans[$index]['failure_root_causes'] ?? null;
            $plan->status = $data['status'] ?? $this->filteredDailyPlans[$index]['status'] ?? 'pending';
            $plan->notes = $data['notes'] ?? $this->filteredDailyPlans[$index]['notes'] ?? '';

            // Garantir que o turno selecionado seja salvo
            $plan->shift_id = $data['shift_id'] ?? $this->selectedShiftId ?? $this->filteredDailyPlans[$index]['shift_id'] ?? null;

            $plan->save();
            
            // Processar o consumo de materiais e adição de produto final ao estoque
            // apenas se a quantidade foi alterada e aumentou
            if ($plan->actual_quantity > $previousActualQuantity) {
                // Processar o consumo de matérias-primas
                $materialResult = $this->processMaterialConsumption($plan, $previousActualQuantity);
                
                // Adicionar o produto acabado ao estoque
                $stockResult = $this->addFinishedProductToStock($plan, $previousActualQuantity);
                
                \Illuminate\Support\Facades\Log::info('Processamento de estoque para plano diário concluído', [
                    'plan_id' => $plan->id,
                    'material_success' => $materialResult['success'],
                    'stock_success' => $stockResult['success']
                ]);
            }

            // Atualizar o objeto na memória
            $this->filteredDailyPlans[$index] = array_merge($this->filteredDailyPlans[$index], $data);
            $this->filteredDailyPlans[$index]['id'] = $plan->id;
            $this->filteredDailyPlans[$index]['shift_id'] = $plan->shift_id;

            \Illuminate\Support\Facades\Log::info('Plano diário atualizado com sucesso', [
                'id' => $plan->id,
                'shift_id' => $plan->shift_id
            ]);
        } else {
            // Plano novo, criar no banco de dados
            $schedule = ProductionSchedule::findOrFail($this->viewingSchedule->id);

            $plan = new ProductionDailyPlan();
            $plan->schedule_id = $schedule->id;
            $plan->production_date = $data['production_date'] ?? $this->filteredDailyPlans[$index]['production_date'];
            $plan->start_time = $data['start_time'] ?? $this->filteredDailyPlans[$index]['start_time'];
            $plan->end_time = $data['end_time'] ?? $this->filteredDailyPlans[$index]['end_time'];
            $plan->planned_quantity = $data['planned_quantity'] ?? $this->filteredDailyPlans[$index]['planned_quantity'];
            $plan->actual_quantity = $data['actual_quantity'] ?? $this->filteredDailyPlans[$index]['actual_quantity'] ?? 0;
            $plan->defect_quantity = $data['defect_quantity'] ?? $this->filteredDailyPlans[$index]['defect_quantity'] ?? 0;
            $plan->has_breakdown = $data['has_breakdown'] ?? $this->filteredDailyPlans[$index]['has_breakdown'] ?? false;
            $plan->breakdown_minutes = $data['breakdown_minutes'] ?? $this->filteredDailyPlans[$index]['breakdown_minutes'] ?? 0;
            $plan->failure_category_id = $data['failure_category_id'] ?? $this->filteredDailyPlans[$index]['failure_category_id'] ?? null;
            $plan->failure_root_causes = $data['failure_root_causes'] ?? $this->filteredDailyPlans[$index]['failure_root_causes'] ?? null;
            $plan->status = $data['status'] ?? $this->filteredDailyPlans[$index]['status'] ?? 'pending';
            $plan->notes = $data['notes'] ?? $this->filteredDailyPlans[$index]['notes'] ?? '';

            // Garantir que o turno selecionado seja salvo
            $plan->shift_id = $data['shift_id'] ?? $this->selectedShiftId ?? null;

            $plan->save();
            
            // Para planos novos, processar o estoque se houver quantidade atual
            if ($plan->actual_quantity > 0) {
                // Processar o consumo de matérias-primas
                $materialResult = $this->processMaterialConsumption($plan, 0);
                
                // Adicionar o produto acabado ao estoque
                $stockResult = $this->addFinishedProductToStock($plan, 0);
                
                \Illuminate\Support\Facades\Log::info('Processamento de estoque para novo plano diário concluído', [
                    'plan_id' => $plan->id,
                    'material_success' => $materialResult['success'],
                    'stock_success' => $stockResult['success']
                ]);
            }

            // Atualizar o objeto na memória
            $this->dailyPlans[$index] = array_merge($this->dailyPlans[$index], $data);
            $this->dailyPlans[$index]['id'] = $plan->id;

            \Illuminate\Support\Facades\Log::info('Novo plano diário criado', [
                'id' => $plan->id
            ]);
        }

        // Recalcular o impacto das falhas
        $this->calculateFailureImpact();

        // Após atualizar um plano, devemos recarregar todos os planos do banco de dados
        // para garantir que estamos com os dados atualizados
        $reloadedPlans = ProductionDailyPlan::where('schedule_id', $this->scheduleId)
            ->orderBy('production_date')
            ->orderBy('start_time')
            ->get();

        // Resetar os planos diários em memória
        $this->dailyPlans = [];

        // Reconstruir o array com os dados atualizados
        foreach ($reloadedPlans as $i => $plan) {
            $this->dailyPlans[$i] = [
                'id' => $plan->id,
                'production_date' => $plan->production_date->format('Y-m-d'),
                'start_time' => $plan->start_time,
                'end_time' => $plan->end_time,
                'planned_quantity' => $plan->planned_quantity,
                'actual_quantity' => $plan->actual_quantity,
                'defect_quantity' => $plan->defect_quantity,
                'has_breakdown' => $plan->has_breakdown,
                'breakdown_minutes' => $plan->breakdown_minutes,
                'failure_category_id' => $plan->failure_category_id,
                'failure_root_causes' => $plan->failure_root_causes,
                'status' => $plan->status,
                'notes' => $plan->notes,
                'shift_id' => $plan->shift_id,
            ];
        }

        \Illuminate\Support\Facades\Log::info('Planos diários recarregados do banco de dados', [
            'total_plans' => count($this->dailyPlans)
        ]);

        // Re-aplicar o filtro de turno se existir um turno selecionado
        if (!empty($this->selectedShiftId)) {
            // Re-filtrar os planos diários pelo turno selecionado
            if (isset($this->dailyPlans) && count($this->dailyPlans) > 0) {
                $this->filteredDailyPlans = collect($this->dailyPlans)
                    ->filter(function ($plan) {
                        // Logar para debug o turno de cada plano
                        \Illuminate\Support\Facades\Log::debug('Verificando turno do plano', [
                            'plan_shift_id' => $plan['shift_id'] ?? 'null',
                            'selected_shift_id' => $this->selectedShiftId
                        ]);

                        // Incluir planos que ainda não têm turno definido E planos do turno selecionado
                        // Isso garante que não vamos esconder planos que ainda precisam ser configurados
                        return empty($plan['shift_id']) || $plan['shift_id'] == $this->selectedShiftId;
                    })
                    ->toArray();

                \Illuminate\Support\Facades\Log::info('Planos diários re-filtrados após atualização', [
                    'shift_id' => $this->selectedShiftId,
                    'filtered_count' => count($this->filteredDailyPlans),
                    'total_plans' => count($this->dailyPlans)
                ]);
            }
        } else {
            // Se não há turno selecionado, mostrar todos os planos
            $this->filteredDailyPlans = $this->dailyPlans;
        }

        $this->dispatch('notify',
            type: 'success',
            title: __('messages.success'),
            message: __('messages.daily_plan_updated'));

        // Disparar evento para atualizar os gráficos
        $this->dispatch('dailyPlansUpdated');

        return true;
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Erro ao atualizar plano diário', [
            'index' => $index,
            'erro' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        $this->dispatch('notify',
            type: 'error',
            title: __('messages.error'),
            message: __('messages.failed_to_update_daily_plan') . ": {$e->getMessage()}");

        return false;
    }
}
