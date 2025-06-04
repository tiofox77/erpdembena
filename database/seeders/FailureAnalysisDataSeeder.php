<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\MaintenanceArea;
use App\Models\MaintenanceLine;
use App\Models\MaintenanceEquipment;
use App\Models\MaintenanceCorrective;
use App\Models\FailureCause;
use App\Models\FailureMode;
use App\Models\FailureCauseCategory;
use App\Models\FailureModeCategory;
use App\Models\User;

class FailureAnalysisDataSeeder extends Seeder
{
    /**
     * Executa o seeder para gerar dados de falhas para o relatório de análise
     * 
     * @return void
     */
    public function run()
    {
        $this->command->info('Criando dados de análise de falhas...');
        
        // Criar categorias de causas de falha
        $causeCategoryIds = $this->createFailureCauseCategories();
        
        // Criar categorias de modos de falha
        $modeCategoryIds = $this->createFailureModeCategories();
        
        // Criar causas de falha
        $causeIds = $this->createFailureCauses($causeCategoryIds);
        
        // Criar modos de falha
        $modeIds = $this->createFailureModes($modeCategoryIds);
        
        // Garantir que existam áreas para testes
        $areaIds = $this->ensureAreas();
        
        // Garantir que existam linhas para testes
        $lineIds = $this->ensureLines($areaIds);
        
        // Garantir que existam equipamentos para testes
        $equipmentIds = $this->ensureEquipment($areaIds, $lineIds);
        
        // Garantir que existam usuários para testes
        $userIds = User::pluck('id')->toArray();
        if (empty($userIds)) {
            $userIds = [1]; // Usar ID 1 como fallback se não existirem usuários
        }
        
        // Criar registros de falhas (manutenções corretivas)
        $this->createFailureRecords($equipmentIds, $causeIds, $modeIds, $userIds);
        
        $this->command->info('Dados de análise de falhas criados com sucesso!');
    }
    
    /**
     * Cria categorias de causas de falha
     */
    private function createFailureCauseCategories()
    {
        $categories = [
            'Operacional',
            'Mecânica',
            'Elétrica',
            'Hidráulica',
            'Pneumática'
        ];
        
        $categoryIds = [];
        
        foreach ($categories as $category) {
            $exists = FailureCauseCategory::where('name', $category)->first();
            if (!$exists) {
                $categoryIds[] = FailureCauseCategory::create([
                    'name' => $category,
                    'description' => 'Categoria de causa de falha: ' . $category
                ])->id;
            } else {
                $categoryIds[] = $exists->id;
            }
        }
        
        return $categoryIds;
    }
    
    /**
     * Cria categorias de modos de falha
     */
    private function createFailureModeCategories()
    {
        $categories = [
            'Desgaste',
            'Quebra',
            'Vazamento',
            'Elétrico',
            'Controle'
        ];
        
        $categoryIds = [];
        
        foreach ($categories as $category) {
            $exists = FailureModeCategory::where('name', $category)->first();
            if (!$exists) {
                $categoryIds[] = FailureModeCategory::create([
                    'name' => $category,
                    'description' => 'Categoria de modo de falha: ' . $category
                ])->id;
            } else {
                $categoryIds[] = $exists->id;
            }
        }
        
        return $categoryIds;
    }
    
    /**
     * Cria causas de falha
     */
    private function createFailureCauses($categoryIds)
    {
        $causes = [
            [
                'name' => 'Erro de Operação',
                'description' => 'Falha causada por erro do operador',
                'category_index' => 0
            ],
            [
                'name' => 'Sobrecarga',
                'description' => 'Falha causada por sobrecarga operacional',
                'category_index' => 0
            ],
            [
                'name' => 'Desgaste de Componente',
                'description' => 'Falha causada por desgaste natural de componente',
                'category_index' => 1
            ],
            [
                'name' => 'Fadiga de Material',
                'description' => 'Falha causada por fadiga de material',
                'category_index' => 1
            ],
            [
                'name' => 'Lubrificação Inadequada',
                'description' => 'Falha causada por lubrificação insuficiente ou inadequada',
                'category_index' => 1
            ],
            [
                'name' => 'Manutenção Atrasada',
                'description' => 'Falha causada por manutenção preventiva atrasada',
                'category_index' => 1
            ],
            [
                'name' => 'Dimensionamento Incorreto',
                'description' => 'Falha causada por dimensionamento incorreto de componente',
                'category_index' => 1
            ],
            [
                'name' => 'Variação de Energia',
                'description' => 'Falha causada por variação na energia elétrica',
                'category_index' => 2
            ],
            [
                'name' => 'Contaminação',
                'description' => 'Falha causada por contaminação de fluido',
                'category_index' => 3
            ],
            [
                'name' => 'Falha de Vedação',
                'description' => 'Falha causada por problema em vedação',
                'category_index' => 3
            ],
            [
                'name' => 'Pressão Inadequada',
                'description' => 'Falha causada por pressão operacional incorreta',
                'category_index' => 4
            ],
            [
                'name' => 'Obstrução',
                'description' => 'Falha causada por obstrução em tubulação ou filtro',
                'category_index' => 4
            ],
            [
                'name' => 'Falha de Software',
                'description' => 'Falha causada por bug ou erro de software',
                'category_index' => 2
            ],
            [
                'name' => 'Sobretemperatura',
                'description' => 'Falha causada por temperatura excessiva',
                'category_index' => 2
            ],
            [
                'name' => 'Umidade',
                'description' => 'Falha causada por umidade excessiva',
                'category_index' => 2
            ]
        ];
        
        $causeIds = [];
        
        foreach ($causes as $cause) {
            $exists = FailureCause::where('name', $cause['name'])->first();
            if (!$exists) {
                $causeIds[] = FailureCause::create([
                    'name' => $cause['name'],
                    'description' => $cause['description'],
                    'category_id' => $categoryIds[$cause['category_index']] ?? null
                ])->id;
            } else {
                $causeIds[] = $exists->id;
            }
        }
        
        return $causeIds;
    }
    
    /**
     * Cria modos de falha
     */
    private function createFailureModes($categoryIds)
    {
        $modes = [
            [
                'name' => 'Quebra de Engrenagem',
                'description' => 'Quebra de dente ou falha total em engrenagem',
                'category_index' => 1
            ],
            [
                'name' => 'Falha de Rolamento',
                'description' => 'Desgaste ou quebra de rolamento',
                'category_index' => 0
            ],
            [
                'name' => 'Curto-Circuito',
                'description' => 'Curto-circuito em sistema elétrico',
                'category_index' => 3
            ],
            [
                'name' => 'Sobreaquecimento de Motor',
                'description' => 'Motor aquecendo além do limite operacional',
                'category_index' => 3
            ],
            [
                'name' => 'Vazamento Hidráulico',
                'description' => 'Vazamento de fluido hidráulico',
                'category_index' => 2
            ],
            [
                'name' => 'Bomba Hidráulica Danificada',
                'description' => 'Dano interno em bomba hidráulica',
                'category_index' => 2
            ],
            [
                'name' => 'Vazamento de Ar',
                'description' => 'Vazamento no sistema pneumático',
                'category_index' => 2
            ],
            [
                'name' => 'Falha de Sensor',
                'description' => 'Sensor com leitura incorreta ou ausente',
                'category_index' => 4
            ],
            [
                'name' => 'Falha de PLC',
                'description' => 'Controlador lógico programável com falha',
                'category_index' => 4
            ],
            [
                'name' => 'Contaminação de Fluido',
                'description' => 'Fluido hidráulico contaminado',
                'category_index' => 2
            ],
            [
                'name' => 'Travamento Mecânico',
                'description' => 'Componente mecânico travado',
                'category_index' => 1
            ],
            [
                'name' => 'Falha de Calibração',
                'description' => 'Equipamento fora de calibração',
                'category_index' => 4
            ],
            [
                'name' => 'Quebra de Correia',
                'description' => 'Ruptura de correia de transmissão',
                'category_index' => 1
            ],
            [
                'name' => 'Obstrução de Filtro',
                'description' => 'Filtro entupido causando restrição',
                'category_index' => 0
            ],
            [
                'name' => 'Desalinhamento',
                'description' => 'Componentes desalinhados',
                'category_index' => 0
            ]
        ];
        
        $modeIds = [];
        
        foreach ($modes as $mode) {
            $exists = FailureMode::where('name', $mode['name'])->first();
            if (!$exists) {
                $modeIds[] = FailureMode::create([
                    'name' => $mode['name'],
                    'description' => $mode['description'],
                    'category_id' => $categoryIds[$mode['category_index']] ?? null
                ])->id;
            } else {
                $modeIds[] = $exists->id;
            }
        }
        
        return $modeIds;
    }
    /**
     * Garantir que existam áreas para testes
     */
    private function ensureAreas()
    {
        // Usar áreas de manutenção existentes ou criar IDs fictícios para teste
        $areaIds = [];
        $areas = MaintenanceArea::all();
        
        if ($areas->count() > 0) {
            foreach ($areas as $area) {
                $areaIds[] = $area->id;
            }
        } else {
            // Criar IDs fictícios para testes se não houver áreas
            $areaIds = [1, 2, 3, 4, 5];
        }
        
        return $areaIds;
    }
    
    /**
     * Garantir que existam linhas para testes
     */
    private function ensureLines($areaIds)
    {
        // Usar linhas existentes ou criar IDs fictícios para teste
        $lineIds = [];
        $lines = MaintenanceLine::all();
        
        if ($lines->count() > 0) {
            foreach ($lines as $line) {
                $lineIds[] = $line->id;
            }
        } else {
            // Criar IDs fictícios para testes se não houver linhas
            $lineIds = [1, 2, 3, 4, 5, 6, 7, 8];
        }
        
        return $lineIds;
    }
    
    /**
     * Garantir que existam equipamentos para testes
     */
    private function ensureEquipment($areaIds, $lineIds)
    {
        $equipment = [
            [
                'name' => 'Torno CNC',
                'type' => 'Máquina de Usinagem',
                'model' => 'TC-5000'
            ],
            [
                'name' => 'Fresadora',
                'type' => 'Máquina de Usinagem',
                'model' => 'FR-2000'
            ],
            [
                'name' => 'Prensa Hidráulica',
                'type' => 'Conformação',
                'model' => 'PH-400'
            ],
            [
                'name' => 'Esteira Transportadora',
                'type' => 'Transporte',
                'model' => 'ET-100'
            ],
            [
                'name' => 'Robô de Solda',
                'type' => 'Soldagem',
                'model' => 'RS-750'
            ],
            [
                'name' => 'Máquina de Injeção',
                'type' => 'Moldagem',
                'model' => 'MI-800'
            ],
            [
                'name' => 'Empacotadora Automática',
                'type' => 'Embalagem',
                'model' => 'EA-250'
            ],
            [
                'name' => 'Robô Manipulador',
                'type' => 'Manipulação',
                'model' => 'RM-300'
            ],
            [
                'name' => 'Máquina de Corte a Laser',
                'type' => 'Corte',
                'model' => 'CL-1200'
            ],
            [
                'name' => 'Centro de Usinagem',
                'type' => 'Usinagem',
                'model' => 'CU-800'
            ]
        ];
        
        $equipmentIds = [];
        
        foreach ($equipment as $index => $equip) {
            $exists = MaintenanceEquipment::where('name', $equip['name'] . ' ' . $equip['model'])->first();
            if (!$exists) {
                $equipmentIds[] = MaintenanceEquipment::create([
                    'name' => $equip['name'] . ' ' . $equip['model'],
                    'area_id' => $areaIds[$index % count($areaIds)],
                    'line_id' => $lineIds[$index % count($lineIds)],
                    'serial_number' => 'SN' . str_pad($index + 1, 5, '0', STR_PAD_LEFT),
                    'status' => 'active',
                    'last_maintenance' => Carbon::now()->subMonths(rand(1, 6)),
                    'notes' => 'Equipamento ' . $equip['name'] . ' modelo ' . $equip['model'] . ' tipo ' . $equip['type']
                ])->id;
            } else {
                $equipmentIds[] = $exists->id;
            }
        }
        
        return $equipmentIds;
    }
    /**
     * Criar registros de falhas (manutenção corretiva)
     */
    private function createFailureRecords($equipmentIds, $causeIds, $modeIds, $userIds)
    {
        $now = Carbon::now();
        $oneYearAgo = Carbon::now()->subYear(); // Expandir para 1 ano de histórico
        
        // Definir pesos para distribuir realisticamente as falhas
        $equipmentWeights = [
            0 => 8,  // Torno CNC terá muitas falhas
            1 => 6,  // Fresadora terá algumas falhas
            2 => 7,  // Prensa Hidráulica terá várias falhas
            3 => 3,  // Esteira terá poucas falhas
            4 => 4,  // Robô de Solda terá algumas falhas
            5 => 5,  // Máquina de Injeção terá várias falhas
            6 => 3,  // Empacotadora terá poucas falhas
            7 => 4,  // Robô Manipulador terá algumas falhas
            8 => 5,  // Máquina de Corte a Laser terá várias falhas
            9 => 6   // Centro de Usinagem terá algumas falhas
        ];
        
        $causeWeights = [
            0 => 6,  // Erro de Operação será muito comum
            1 => 4,  // Sobrecarga terá algumas ocorrências
            2 => 8,  // Desgaste de Componente será extremamente comum
            3 => 5,  // Fadiga de Material será comum
            4 => 7,  // Lubrificação Inadequada será muito comum
            5 => 5,  // Manutenção Atrasada terá várias ocorrências
            6 => 2,  // Dimensionamento Incorreto será raro
            7 => 4,  // Variação de Energia terá algumas ocorrências
            8 => 3,  // Contaminação terá algumas ocorrências
            9 => 4,  // Falha de Vedação terá algumas ocorrências
            10 => 3, // Pressão Inadequada terá algumas ocorrências
            11 => 3, // Obstrução terá algumas ocorrências
            12 => 2, // Falha de Software será rara
            13 => 5, // Sobretemperatura será comum
            14 => 2  // Umidade será rara
        ];
        
        $modeWeights = [
            0 => 6,  // Quebra de Engrenagem será comum
            1 => 8,  // Falha de Rolamento será muito comum
            2 => 4,  // Curto-Circuito terá algumas ocorrências
            3 => 7,  // Sobreaquecimento de Motor será comum
            4 => 5,  // Vazamento Hidráulico será comum
            5 => 4,  // Bomba Hidráulica Danificada terá algumas ocorrências
            6 => 3,  // Vazamento de Ar terá algumas ocorrências
            7 => 6,  // Falha de Sensor será comum
            8 => 2,  // Falha de PLC será rara
            9 => 4,  // Contaminação de Fluido terá algumas ocorrências
            10 => 5, // Travamento Mecânico será comum
            11 => 3, // Falha de Calibração terá algumas ocorrências
            12 => 4, // Quebra de Correia terá algumas ocorrências
            13 => 3, // Obstrução de Filtro terá algumas ocorrências
            14 => 5  // Desalinhamento será comum
        ];
        
        // Distribuição de status - variação mais realista
        $statuses = [
            'open' => 10,           // 10% aberto
            'in_progress' => 15,     // 15% em andamento
            'resolved' => 35,        // 35% resolvido
            'closed' => 40           // 40% fechado
        ];
        
        // Criar função para selecionar com base em pesos
        $weightedRandom = function($weights) {
            $sum = array_sum($weights);
            $rand = mt_rand(1, $sum);
            $runningSum = 0;
            
            foreach ($weights as $key => $weight) {
                $runningSum += $weight;
                if ($rand <= $runningSum) {
                    return $key;
                }
            }
            
            return array_key_first($weights); // Fallback
        };

        // Escolher status com base em pesos
        $selectStatus = function() use ($statuses) {
            $rand = mt_rand(1, 100);
            $cumulative = 0;
            
            foreach ($statuses as $status => $probability) {
                $cumulative += $probability;
                if ($rand <= $cumulative) {
                    return $status;
                }
            }
            
            return 'closed'; // Fallback
        };
        
        $numRecords = 200; // Aumentando para 200 registros para ter mais dados
        $this->command->info("Criando {$numRecords} registros de falhas de equipamentos...");
        
        // Distribuir falhas ao longo do ano - mais recentes têm mais ocorrências
        $timeSegments = [
            0 => ['start' => $oneYearAgo, 'end' => $oneYearAgo->copy()->addMonths(3), 'weight' => 15], // 9-12 meses atrás (15%)
            1 => ['start' => $oneYearAgo->copy()->addMonths(3), 'end' => $oneYearAgo->copy()->addMonths(6), 'weight' => 20], // 6-9 meses atrás (20%)
            2 => ['start' => $oneYearAgo->copy()->addMonths(6), 'end' => $oneYearAgo->copy()->addMonths(9), 'weight' => 30], // 3-6 meses atrás (30%)
            3 => ['start' => $oneYearAgo->copy()->addMonths(9), 'end' => $now, 'weight' => 35]  // 0-3 meses atrás (35%)
        ];
        
        // Pesos para distribuição de tempo de inatividade (em horas)
        $downtimeRanges = [
            ['min' => 1, 'max' => 4, 'weight' => 30],     // 1-4 horas (30%)
            ['min' => 4, 'max' => 12, 'weight' => 40],    // 4-12 horas (40%)
            ['min' => 12, 'max' => 24, 'weight' => 20],   // 12-24 horas (20%)
            ['min' => 24, 'max' => 72, 'weight' => 8],    // 24-72 horas (8%)
            ['min' => 72, 'max' => 168, 'weight' => 2],   // 72-168 horas (3-7 dias) (2%)
        ];
        
        // Função para selecionar um segmento de tempo com base nos pesos
        $selectTimeSegment = function() use ($timeSegments) {
            $weights = array_column($timeSegments, 'weight');
            $sum = array_sum($weights);
            $rand = mt_rand(1, $sum);
            $runningSum = 0;
            
            foreach ($weights as $key => $weight) {
                $runningSum += $weight;
                if ($rand <= $runningSum) {
                    return $key;
                }
            }
            
            return 0; // Fallback para o primeiro segmento
        };
        
        // Função para selecionar um tempo de inatividade com base nos pesos
        $selectDowntimeRange = function() use ($downtimeRanges) {
            $weights = array_column($downtimeRanges, 'weight');
            $sum = array_sum($weights);
            $rand = mt_rand(1, $sum);
            $runningSum = 0;
            
            foreach ($weights as $key => $weight) {
                $runningSum += $weight;
                if ($rand <= $runningSum) {
                    return $downtimeRanges[$key];
                }
            }
            
            return $downtimeRanges[0]; // Fallback para o primeiro intervalo
        };
        
        for ($i = 0; $i < $numRecords; $i++) {
            // Selecionar segmento de tempo com base nos pesos
            $segmentIndex = $selectTimeSegment();
            $segment = $timeSegments[$segmentIndex];
            
            // Selecionar equipamento com base nos pesos
            $equipmentIndex = $weightedRandom($equipmentWeights);
            // Garantir que o índice não exceda o tamanho do array
            $equipmentIndex = min($equipmentIndex, count($equipmentIds) - 1);
            $equipmentId = $equipmentIds[$equipmentIndex];
            
            // Selecionar causa e modo com base nos pesos
            $causeIndex = $weightedRandom($causeWeights);
            // Garantir que o índice não exceda o tamanho do array
            $causeIndex = min($causeIndex, count($causeIds) - 1);
            $causeId = $causeIds[$causeIndex];
            
            $modeIndex = $weightedRandom($modeWeights);
            // Garantir que o índice não exceda o tamanho do array
            $modeIndex = min($modeIndex, count($modeIds) - 1);
            $modeId = $modeIds[$modeIndex];
            
            // Gerar data de início aleatória no segmento de tempo correto
            $startTime = Carbon::createFromTimestamp(
                mt_rand($segment['start']->timestamp, $segment['end']->timestamp)
            );
            
            // Status - probabilidade de fechado aumenta para falhas mais antigas
            $statusAdjustment = 4 - $segmentIndex; // 1 para recentes, 4 para mais antigas
            if ($statusAdjustment > 1) {
                // Aumentar probabilidade de resolved/closed para falhas mais antigas
                $adjustedStatuses = $statuses;
                $adjustedStatuses['open'] = max(1, $statuses['open'] - ($statusAdjustment * 2));
                $adjustedStatuses['in_progress'] = max(1, $statuses['in_progress'] - $statusAdjustment);
                $adjustedStatuses['resolved'] += $statusAdjustment;
                $adjustedStatuses['closed'] += $statusAdjustment;
                
                $status = $selectStatus();
            } else {
                $status = $selectStatus();
            }
            
            // Calcular data de término e tempo de inatividade
            $endTime = null;
            $downtimeLength = null;
            
            if ($status === 'resolved' || $status === 'closed') {
                // Selecionar intervalo de tempo de inatividade com base nos pesos
                $downtimeRange = $selectDowntimeRange();
                $hoursToFix = mt_rand($downtimeRange['min'], $downtimeRange['max']);
                $endTime = $startTime->copy()->addHours($hoursToFix);
                
                // Formato de downtime em horas decimais
                $minutesExtra = mt_rand(0, 59);
                $downtimeLength = $hoursToFix + ($minutesExtra / 60);
            }
            
            // Descrições mais variadas
            $descriptions = [
                'Equipamento parou durante operação',
                'Ruído anormal detectado durante ciclo de trabalho',
                'Vibração excessiva observada em componentes rotativos',
                'Falha durante ciclo de operação, parada não programada',
                'Componente com desgaste visível além dos limites aceitáveis',
                'Alarme de erro ativado no painel de controle principal',
                'Vazamento de fluido hidráulico identificado na base',
                'Perda de precisão nas operações de alta tolerância',
                'Queda significativa de desempenho produtivo',
                'Falha intermitente durante ciclos específicos',
                'Superaquecimento detectado em componentes elétricos',
                'Curto-circuito no sistema de controle',
                'Erro de comunicação com outros equipamentos',
                'Trancamento mecânico durante operação',
                'Vazamento de ar no sistema pneumático',
                'Falha no sistema de segurança',
                'Quebra de componente estrutural',
                'Parada emergencial acionada por operador',
                'Falha de calibração detectada em sensores',
                'Perda de pressão hidráulica durante ciclo'
            ];
            
            // Ações tomadas mais detalhadas
            $actions = [
                'Substituição completa do componente danificado e recalibração do sistema',
                'Realinhamento do conjunto de peças e ajuste de tolerâncias mecânicas',
                'Lubrificação de componentes móveis e limpeza dos sistemas de transmissão',
                'Reaperto de todas as conexões elétricas e mecânicas conforme especificação',
                'Calibração de sensores e verificação de todo o sistema de controle',
                'Limpeza do sistema hidráulico e substituição de fluido contaminado',
                'Atualização de software de controle para nova versão com correções',
                'Ajuste de parâmetros operacionais conforme manual do fabricante',
                'Reinicialização forçada do sistema eletrônico e reset de memória',
                'Reparo de fiação danificada e isolamento de circuitos expostos',
                'Desmontagem completa do conjunto para inspeção e remontagem',
                'Substituição de selos e vedações hidráulicas desgastadas',
                'Troca preventiva de componentes associados ao ponto de falha',
                'Correção de problema estrutural por soldagem e reforço',
                'Alinhamento laser de componentes rotativos e verificação de balanceamento',
                'Treinamento do operador para evitar recorrência do problema',
                'Instalação de sistema de monitoramento para diagnóstico precoce',
                'Aplicação de modificação recomendada pelo fabricante',
                'Instalação de componentes de maior capacidade/resistência',
                'Implementação de modificação aprovada pela engenharia'
            ];
            
            // Criar o registro de falha
            MaintenanceCorrective::create([
                'year' => $startTime->year,
                'month' => $startTime->month,
                'week' => $startTime->weekOfYear,
                'system_process' => 'Produção',
                'equipment_id' => $equipmentId,
                'failure_mode_id' => $modeId,
                'failure_cause_id' => $causeId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'downtime_length' => $downtimeLength,
                'description' => $descriptions[array_rand($descriptions)],
                'actions_taken' => ($status === 'resolved' || $status === 'closed') ? $actions[array_rand($actions)] : null,
                'reported_by' => $userIds[array_rand($userIds)],
                'resolved_by' => ($status === 'resolved' || $status === 'closed') ? $userIds[array_rand($userIds)] : null,
                'status' => $status,
            ]);
        }
    }
}
