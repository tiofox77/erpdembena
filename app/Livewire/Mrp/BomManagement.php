<?php

namespace App\Livewire\Mrp;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Mrp\BomHeader;
use App\Models\Mrp\BomDetail;
use App\Models\SupplyChain\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;

class BomManagement extends Component
{
    use WithPagination;
    
    // Propriedades do componente
    public $search = '';
    public $sortField = 'bom_number';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $currentTab = 'header';
    
    // Propriedades compartilhadas com a view e modais
    public $products = [];
    public $components = [];
    public $unitTypes = [];
    
    // Propriedades para modal
    public $showModal = false;
    public $showDeleteModal = false;
    public $showComponentModal = false;
    public $showDeleteComponentModal = false;
    public $showBomDetailsModal = false;
    public $editMode = false;
    
    // Propriedades para o modal de detalhes da BOM
    public $selectedBomForDetails = null;
    public $bomDetailsComponents = [];
    
    // Propriedades do formulário de BOM Header
    public $bomHeader = [
        'product_id' => '',
        'bom_number' => '',
        'description' => '',
        'status' => 'draft',
        'effective_date' => '',
        'expiration_date' => '',
        'version' => 1,
        'uom' => 'unit',
        'notes' => ''
    ];
    
    // Propriedades do formulário de BOM Component (Detail)
    public $bomDetail = [
        'bom_header_id' => '',
        'component_id' => '',
        'quantity' => '',
        'uom' => 'unit',
        'position' => 0,
        'level' => 1,
        'scrap_percentage' => 0,
        'is_critical' => false,
        'notes' => ''
    ];
    
    // IDs para operações
    public $bomHeaderId = null;
    public $bomDetailId = null;
    
    // Lista de componentes da BOM atual para a BOM selecionada
    public $bomComponents = [];
    
    // Propriedades de filtro
    public $statusFilter = null;
    public $productFilter = null;
    
    /**
     * Regras de validação para BOM Header
     */
    protected function bomHeaderRules()
    {
        return [
            'bomHeader.product_id' => 'required|exists:sc_products,id',
            'bomHeader.bom_number' => [
                'required',
                'string',
                'max:50',
                $this->editMode
                    ? Rule::unique('mrp_bom_headers', 'bom_number')->ignore($this->bomHeaderId)
                    : Rule::unique('mrp_bom_headers', 'bom_number'),
            ],
            'bomHeader.description' => 'required|string|max:255',
            'bomHeader.status' => ['required', Rule::in(['draft', 'active', 'obsolete'])],
            'bomHeader.effective_date' => 'required|date',
            'bomHeader.expiration_date' => 'nullable|date|after_or_equal:bomHeader.effective_date',
            'bomHeader.version' => 'required|integer|min:1',
            'bomHeader.uom' => ['required', Rule::in(['unit', 'kg', 'l', 'g', 'ml', 'pcs'])],
            'bomHeader.notes' => 'nullable|string|max:1000',
        ];
    }
    
    /**
     * Regras de validação para BOM Component
     */
    protected function bomDetailRules()
    {
        // Obter as colunas reais da tabela para validação dinâmica
        $existingColumns = Schema::getColumnListing('mrp_bom_details');
        
        $rules = [
            'bomDetail.component_id' => [
                'required',
                'exists:sc_products,id',
                'different:selected_product_id',
                function ($attribute, $value, $fail) {
                    // Verificar se o componente já existe nesta BOM (incluindo os soft-deleted)
                    if (!$this->editMode) {
                        // Primeiramente verificar os registros ativos
                        $existsActive = BomDetail::where('bom_header_id', $this->bomHeaderId)
                            ->where('component_id', $value)
                            ->exists();
                        
                        // Depois verificar os registros excluídos (soft-deleted)
                        $existsDeleted = BomDetail::withTrashed()
                            ->where('bom_header_id', $this->bomHeaderId)
                            ->where('component_id', $value)
                            ->whereNotNull('deleted_at')
                            ->exists();
                        
                        // Obter detalhes do componente para o log (apenas quando existir)
                        if ($existsActive || $existsDeleted) {
                            $componentName = Product::where('id', $value)->value('name');
                            $logMessage = "Tentativa de adicionar componente duplicado: {$componentName} (ID: {$value})";
                            $logMessage .= " - Ativo: " . ($existsActive ? 'sim' : 'não');
                            $logMessage .= " - Excluído: " . ($existsDeleted ? 'sim' : 'não');
                            logger($logMessage);
                            
                            // Se estiver soft-deleted, oferecer uma mensagem mais clara
                            if ($existsDeleted && !$existsActive) {
                                $fail('Este componente já existe nesta BOM, mas está excluído. Restaure-o ou use outro componente.');
                            } else {
                                $fail('Este componente já existe nesta BOM.');
                            }
                        }
                    }
                },
            ],
            'bomDetail.quantity' => 'required|numeric|min:0.001',
            'bomDetail.uom' => ['required', Rule::in(['unit', 'kg', 'l', 'g', 'ml', 'pcs'])],
            'bomDetail.is_critical' => 'boolean',
            'bomDetail.notes' => 'nullable|string|max:1000',
        ];
        
        // Adicionar regras para campos opcionais somente se existirem na tabela
        if (in_array('position', $existingColumns)) {
            $rules['bomDetail.position'] = 'nullable|integer|min:0';
        }
        
        if (in_array('level', $existingColumns)) {
            $rules['bomDetail.level'] = 'nullable|integer|min:0';
        }
        
        if (in_array('scrap_percentage', $existingColumns)) {
            $rules['bomDetail.scrap_percentage'] = 'nullable|numeric|min:0|max:100';
        }
        
        return $rules;
    }
    
    /**
     * Filtra os dados para conter apenas colunas existentes na tabela especificada
     *
     * @param string $table Nome da tabela
     * @param array|Collection $data Dados a serem filtrados
     * @return array Dados filtrados
     */
    private function filterTableColumns(string $table, $data): array
    {
        // Obter as colunas que realmente existem na tabela
        $existingColumns = Schema::getColumnListing($table);
        
        // Converter para Collection se for array
        $collection = $data instanceof Collection ? $data : collect($data);
        
        // Filtrar apenas as colunas que existem
        return $collection
            ->filter(function ($value, $key) use ($existingColumns) {
                return in_array($key, $existingColumns);
            })
            ->toArray();
    }
    
    /**
     * Inicializar o componente
     */
    public function mount()
    {
        // Inicializar tipos de unidade a partir do modelo UnitType
        $unitTypes = \App\Models\UnitType::getActive();
        
        // Converter para array associativo [valor => rótulo]
        $this->unitTypes = [];
        foreach ($unitTypes as $unitType) {
            $this->unitTypes[$unitType->symbol] = $unitType->name;
        }
        
        // Se não houver tipos de unidade, usar valores padrão
        if (empty($this->unitTypes)) {
            $this->unitTypes = [
                'unit' => __('messages.unit'),
                'kg' => __('messages.kilogram'),
                'g' => __('messages.gram'),
                'l' => __('messages.liter'),
                'ml' => __('messages.milliliter'),
                'pcs' => __('messages.pieces')
            ];
        }
    }
    
    /**
     * Resetar paginação quando a busca mudar
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    /**
     * Ordenar por coluna
     */
    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortField = $field;
    }
    
    /**
     * Resetar filtros
     */
    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = null;
        $this->productFilter = null;
        $this->resetPage();
    }
    
    /**
     * Gerar número da BOM baseado no produto
     * Se o número gerado já existir, adiciona um timestamp para garantir unicidade
     */
    public function generateBomNumber($forceUnique = false)
    {
        if (empty($this->bomHeader['product_id'])) {
            return;
        }
        
        $product = Product::find($this->bomHeader['product_id']);
        if (!$product) {
            return;
        }
        
        // Contar quantas BOMs já existem para este produto
        $count = BomHeader::where('product_id', $product->id)->count();
        $nextVersion = $count + 1;
        
        // Se precisamos forçar um número único (após falha de validação)
        if ($forceUnique) {
            // Usar um timestamp para garantir unicidade
            $timestamp = time();
            $uniqueBomNumber = $product->sku . '-BOM-V' . $nextVersion . '-' . $timestamp;
            
            logger("Gerando número BOM forçadamente único com timestamp: {$uniqueBomNumber}");
            
            $this->bomHeader['bom_number'] = $uniqueBomNumber;
            $this->bomHeader['version'] = $nextVersion;
            $this->bomHeader['description'] = 'Lista de Materiais para ' . $product->name;
            
            return;
        }
        
        // Gerar número padrão da BOM: SKU-BOM-V{versão}
        $this->bomHeader['bom_number'] = $product->sku . '-BOM-V' . $nextVersion;
        $this->bomHeader['version'] = $nextVersion;
        $this->bomHeader['description'] = 'Lista de Materiais para ' . $product->name;
        
        logger("BOM number gerado sem forçar unicidade: {$this->bomHeader['bom_number']}");
    }
    
    /**
     * Mudar de aba
     */
    public function setTab($tab)
    {
        $this->currentTab = $tab;
        
        // Se estiver mudando para a aba de componentes
        if ($tab === 'components' && $this->bomHeaderId) {
            // Recarregar componentes
            $this->loadComponents(true);
        }
    }
    
    /**
     * Abrir modal para criar nova BOM
     */
    public function create()
    {
        $this->resetValidation();
        $this->reset('bomHeader');
        $this->bomHeader['status'] = 'draft';
        $this->bomHeader['effective_date'] = date('Y-m-d');
        $this->bomHeader['version'] = 1;
        $this->bomHeader['uom'] = 'unit';
        $this->editMode = false;
        $this->currentTab = 'header';
        $this->showModal = true;
    }
    
    /**
     * Carregar e abrir modal para editar BOM
     */
    public function edit($id)
    {
        $this->resetValidation();
        $this->bomHeaderId = $id;
        $bomHeader = BomHeader::findOrFail($id);
        
        $this->bomHeader = [
            'product_id' => $bomHeader->product_id,
            'bom_number' => $bomHeader->bom_number,
            'description' => $bomHeader->description,
            'status' => $bomHeader->status,
            'effective_date' => $bomHeader->effective_date->format('Y-m-d'),
            'expiration_date' => $bomHeader->expiration_date ? $bomHeader->expiration_date->format('Y-m-d') : null,
            'version' => $bomHeader->version,
            'uom' => $bomHeader->uom,
            'notes' => $bomHeader->notes
        ];
        
        $this->editMode = true;
        $this->currentTab = 'header';
        $this->showModal = true;
    }
    
    // Removidos métodos de diagnóstico e teste

    /**
     * Abrir tela de gerenciamento de componentes
     * 
     * @param int $id ID do BOM Header para visualizar componentes
     * @param bool $restoreSoftDeleted Se deve restaurar componentes excluídos
     */
    public function viewComponents($id, $restoreSoftDeleted = true)
    {
        $this->bomHeaderId = $id;
        
        // Restaurar componentes soft-deleted para garantir que sejam exibidos
        $this->loadComponents($restoreSoftDeleted);
        
        // Registrar no log a quantidade de componentes carregados 
        logger("viewComponents - Carregados " . count($this->bomComponents) . " componentes para o BOM #{$id}");
        
        // Mudar para a aba de componentes
        $this->currentTab = 'components';
    }
    
    
    /**
     * Carregar componentes da BOM atual, incluindo os soft-deleted para diagnóstico
     * 
     * @param bool $includeSoftDeleted Se deve incluir componentes soft-deleted
     */
    public function loadComponents($includeSoftDeleted = false)
    {
        if (!$this->bomHeaderId) {
            $this->bomComponents = [];
            return;
        }

        // INCLUI O RESTORE DE EVENTUAIS COMPONENTES SOFT-DELETED PARA TESTE
        if ($includeSoftDeleted) {
            // Restaurar componentes soft-deleted para teste (remover em produção)
            $restored = BomDetail::withTrashed()
                ->where('bom_header_id', $this->bomHeaderId)
                ->whereNotNull('deleted_at')
                ->restore();
                
            if ($restored) {
                logger("BomManagement - {$restored} componentes restaurados do soft-delete");
            }
        }
        
        // DIAGNÓSTICO: Verificar belt conveyor
        $conveyorSku = 'PRD0000013';
        $conveyorComponent = Product::where('sku', $conveyorSku)->first();
        if ($conveyorComponent) {
            $checkConveyor = BomDetail::withTrashed()
                ->where('bom_header_id', $this->bomHeaderId)
                ->where('component_id', $conveyorComponent->id)
                ->get();
                
            if ($checkConveyor->count() > 0) {
                logger("DIAGNÓSTICO - Encontrado Belt Conveyor na BOM #{$this->bomHeaderId}:");
                foreach ($checkConveyor as $item) {
                    logger("ID: {$item->id}, soft-deleted: " . ($item->trashed() ? 'SIM' : 'NÃO'));
                    
                    // Restaurar automaticamente para teste
                    if ($item->trashed() && $includeSoftDeleted) {
                        $item->restore();
                        logger("BELT CONVEYOR RESTAURADO AUTOMATICAMENTE");
                    }
                }
            } else {
                logger("DIAGNÓSTICO - Belt Conveyor NÃO encontrado na BOM #{$this->bomHeaderId}");
            }
        }
        
        // CARREGAMENTO PRINCIPAL DE COMPONENTES
        // Use uma variável temporária mais clara para rastrear
        $query = BomDetail::with(['component']);
        
        // Filtrar por BOM Header
        $query->where('bom_header_id', $this->bomHeaderId);
        
        // Executar a consulta e armazenar os resultados
        $loadedComponents = $query->get();
        
        // Log de diagnóstico
        logger("BomManagement - {$loadedComponents->count()} componentes encontrados");
        
        // Converter para array com estrutura exata esperada pela view
        $mappedComponents = $loadedComponents->map(function ($item) {
            logger("DIAGNÓSTICO DETALHADO - Mapeando componente ID: {$item->id}");
            
            // Dados do produto associado
            $productInfo = [
                'name' => $item->component ? $item->component->name : "Produto ID {$item->component_id}",
                'sku' => $item->component ? $item->component->sku : 'N/A',
            ];
            
            logger("DIAGNÓSTICO - Produto para componente {$item->id}: " . json_encode($productInfo));
            
            return [
                'id' => $item->id,
                'bom_header_id' => $item->bom_header_id,
                'component_id' => $item->component_id,
                'quantity' => $item->quantity,
                'uom' => $item->uom,
                'position' => $item->position ?? null,
                'level' => $item->level ?? null, 
                'scrap_percentage' => $item->scrap_percentage ?? 0,
                'is_critical' => (bool)$item->is_critical,
                'notes' => $item->notes,
                'component' => $productInfo
            ];
        })->toArray();
        
        // Apenas preencher bomComponents, deixando components intacto para uso na modal
        $this->bomComponents = $mappedComponents;
        // NÃO sobrescrever $this->components aqui, pois ele contém os raw materials para a modal
        
        // Log detalhado para ajudar a depurar a estrutura de dados
        logger("DIAGNÓSTICO - Componentes carregados (bomComponents): " . count($this->bomComponents));
        logger("DIAGNÓSTICO - Componentes carregados (components): " . count($this->components));
        logger("DIAGNÓSTICO - Dados para debug: " . json_encode(array_slice($mappedComponents, 0, 2)));  
    }
    
    /**
     * Abrir modal para adicionar novo componente
     */
    public function addComponent()
    {
        $this->resetValidation();
        $this->reset('bomDetail');
        $this->bomDetail['bom_header_id'] = $this->bomHeaderId;
        $this->bomDetail['level'] = 1;
        $this->bomDetail['position'] = 0;
        $this->bomDetail['scrap_percentage'] = 0;
        $this->bomDetail['is_critical'] = false;
        $this->bomDetail['uom'] = 'unit';
        $this->editMode = false;
        
        // Carrega componentes (raw_material) antes de abrir o modal
        try {
            logger("COMPONENTE MODAL - Carregando raw_material para o modal");
            $rawMaterials = Product::where('product_type', 'raw_material')->orderBy('name')->get();
            logger("COMPONENTE MODAL - Total de raw_material: " . $rawMaterials->count());
            
            // Debug para listar todos os componentes encontrados
            foreach ($rawMaterials as $component) {
                logger("COMPONENTE MODAL ITEM: ID={$component->id}, Nome={$component->name}, SKU={$component->sku}");
            }
            
            // Armazena na propriedade components para uso no modal
            $this->components = $rawMaterials;
            
        } catch (\Exception $e) {
            logger("COMPONENTE MODAL - Erro ao carregar raw_material: " . $e->getMessage());
            $this->components = collect([]);
        }
        
        $this->showComponentModal = true;
    }
    
    /**
     * Carregar e abrir modal para editar componente
     */
    public function editComponent($id)
    {
        $this->resetValidation();
        $this->bomDetailId = $id;
        $bomDetail = BomDetail::findOrFail($id);
        
        $this->bomDetail = [
            'bom_header_id' => $bomDetail->bom_header_id,
            'component_id' => $bomDetail->component_id,
            'quantity' => $bomDetail->quantity,
            'uom' => $bomDetail->uom,
            'position' => $bomDetail->position,
            'level' => $bomDetail->level,
            'scrap_percentage' => $bomDetail->scrap_percentage,
            'is_critical' => $bomDetail->is_critical,
            'notes' => $bomDetail->notes
        ];
        
        $this->editMode = true;
        $this->showComponentModal = true;
    }
    
    /**
     * Confirmar exclusão de BOM
     */
    public function confirmDelete($id)
    {
        $this->bomHeaderId = $id;
        $this->showDeleteModal = true;
    }
    
    /**
     * Confirmar exclusão de componente
     */
    public function confirmDeleteComponent($id)
    {
        $this->bomDetailId = $id;
        $this->showDeleteComponentModal = true;
    }
    
    /**
     * Abrir modal para visualizar detalhes completos da BOM
     * 
     * @param int $id ID da BOM para visualizar
     */
    public function viewBomDetails($id)
    {
        try {
            // Carregar os dados da BOM com o produto relacionado
            $this->selectedBomForDetails = BomHeader::with('product')->findOrFail($id);
            
            // Carregar os componentes relacionados a esta BOM
            $bomComponents = BomDetail::where('bom_header_id', $id)
                ->with('component')
                ->get();
            
            // Formatar os componentes para exibição
            $this->bomDetailsComponents = $bomComponents->map(function ($item) {
                return [
                    'id' => $item->id,
                    'component_id' => $item->component_id,
                    'quantity' => $item->quantity,
                    'uom' => $item->uom,
                    'level' => $item->level ?? 1,
                    'position' => $item->position,
                    'scrap_percentage' => $item->scrap_percentage ?? 0,
                    'is_critical' => $item->is_critical,
                    'notes' => $item->notes,
                    'component' => [
                        'name' => $item->component ? $item->component->name : 'N/A',
                        'sku' => $item->component ? $item->component->sku : 'N/A',
                    ]
                ];
            })->toArray();
            
            // Abrir o modal Alpine.js
            $this->showBomDetailsModal = true;
            
        } catch (\Exception $e) {
            $this->dispatch('notify', 
                type: 'error',
                title: 'Erro',
                message: 'Erro ao carregar detalhes da BOM: ' . $e->getMessage()
            );
        }
    }
    
    /**
     * Excluir BOM
     */
    public function delete()
    {
        DB::beginTransaction();
        
        try {
            $bomHeader = BomHeader::findOrFail($this->bomHeaderId);
            
            // Excluir todos os componentes primeiro
            BomDetail::where('bom_header_id', $this->bomHeaderId)->delete();
            
            // Excluir o cabeçalho da BOM
            $bomHeader->delete();
            
            DB::commit();
            
            $this->dispatch('notify',
                type: 'error', // error para exclusões conforme o padrão
                title: __('messages.bom_deleted_title'),
                message: __('messages.bom_deleted_message')
            );
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.delete_error_title'),
                message: __('messages.bom_delete_error_message', ['error' => $e->getMessage()])
            );
        }
        
        $this->showDeleteModal = false;
        $this->bomHeaderId = null;
        $this->currentTab = 'header';
    }
    
    /**
     * Excluir componente
     */
    public function deleteComponent()
    {
        try {
            $bomDetail = BomDetail::findOrFail($this->bomDetailId);
            $bomDetail->delete();
            
            $this->dispatch('notify',
                type: 'error', // error para exclusões conforme o padrão
                title: __('messages.component_deleted_title'),
                message: __('messages.component_deleted_message')
            );
            
            $this->loadComponents();
        } catch (\Exception $e) {
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.delete_error_title'),
                message: __('messages.component_delete_error_message', ['error' => $e->getMessage()])
            );
        }
        
        $this->showDeleteComponentModal = false;
        $this->bomDetailId = null;
    }
    
    /**
     * Fechar modais
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->showComponentModal = false;
        $this->showDeleteComponentModal = false;
    }
    
    /**
     * Salvar BOM (criar ou atualizar)
     */
    public function saveBomHeader()
    {
        try {
            // Tentar validar o formulário
            $this->validate($this->bomHeaderRules());
            
            // Se chegou aqui, a validação passou
            $validationPassed = true;
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Verificar se o erro é apenas relacionado ao bom_number
            $errors = $e->validator->errors()->messages();
            
            // Se o único erro for do campo bom_number e for sobre 'already been taken'
            if (isset($errors['bomHeader.bom_number']) && 
                count($errors) === 1 && 
                strpos(implode('', $errors['bomHeader.bom_number']), 'taken') !== false) {
                
                logger("Erro de validação do bom_number: " . implode(", ", $errors['bomHeader.bom_number']));
                logger("Gerando novo número BOM com força de unicidade");
                
                // Gerar um novo número de BOM GARANTIDAMENTE único com timestamp
                $this->generateBomNumber(true); // Usar parâmetro forceUnique = true
                
                logger("Novo número BOM gerado após erro: {$this->bomHeader['bom_number']}");
                
                // Agora tentar validar novamente
                $this->validate($this->bomHeaderRules());
                $validationPassed = true;
            } else {
                // Se houver outros erros além do bom_number, lançar a exceção novamente
                throw $e;
            }
        }
        
        // Ensure empty expiration_date is set to null, not empty string
        if (empty($this->bomHeader['expiration_date'])) {
            $this->bomHeader['expiration_date'] = null;
        }
        
        if ($this->editMode) {
            $bomHeader = BomHeader::findOrFail($this->bomHeaderId);
            $bomHeader->fill($this->bomHeader);
            $bomHeader->updated_by = Auth::id();
            $bomHeader->save();
            
            $this->dispatch('notify',
                type: 'warning', // warning para atualizações conforme o padrão
                title: __('messages.bom_updated_title'),
                message: __('messages.bom_updated_message')
            );
        } else {
            $bomHeader = new BomHeader($this->bomHeader);
            $bomHeader->created_by = Auth::id();
            $bomHeader->updated_by = Auth::id();
            $bomHeader->save();
            
            $this->bomHeaderId = $bomHeader->id;
            
            $this->dispatch('notify',
                type: 'success', // success para criação conforme o padrão
                title: __('messages.bom_created_title'),
                message: __('messages.bom_created_message')
            );
        }
        $this->showModal = false;
    }
    
    /**
     * Salvar componente
     */
    public function saveComponent()
    {
        $this->validate($this->bomDetailRules());
        
        try {
            DB::beginTransaction();
            
            if ($this->editMode) {
                $bomDetail = BomDetail::findOrFail($this->bomDetailId);
                
                // Usar o método filterTableColumns para garantir que apenas colunas existentes sejam usadas
                $bomDetailData = $this->filterTableColumns('mrp_bom_details', $this->bomDetail);
                
                // Adicionar atributos específicos para atualização
                $bomDetail->fill($bomDetailData);
                
                // Verificar se a coluna updated_by existe antes de atribuí-la
                $existingColumns = Schema::getColumnListing('mrp_bom_details');
                if (in_array('updated_by', $existingColumns)) {
                    $bomDetail->updated_by = Auth::id();
                }
                
                $bomDetail->save();
                
                $this->dispatch('notify',
                    type: 'warning', // warning para atualizações conforme o padrão
                    title: __('messages.component_updated_title'),
                    message: __('messages.component_updated_message')
                );
            } else {
                // Inicializar o bomDetail com o header atual
                $this->bomDetail['bom_header_id'] = $this->bomHeaderId;
                
                // Usar o método filterTableColumns para garantir que apenas colunas existentes sejam usadas
                $bomDetailData = $this->filterTableColumns('mrp_bom_details', $this->bomDetail);
                
                // Adicionar atributos específicos para criação
                $bomDetail = new BomDetail();
                $bomDetail->fill($bomDetailData);
                
                // Verificar se as colunas created_by e updated_by existem antes de atribuí-las
                $existingColumns = Schema::getColumnListing('mrp_bom_details');
                if (in_array('created_by', $existingColumns)) {
                    $bomDetail->created_by = Auth::id();
                }
                
                if (in_array('updated_by', $existingColumns)) {
                    $bomDetail->updated_by = Auth::id();
                }
                
                $bomDetail->save();
                
                $this->dispatch('notify',
                    type: 'success', // success para criação conforme o padrão
                    title: __('messages.component_added_title'),
                    message: __('messages.component_added_message')
                );
            }
            
            DB::commit();
            $this->showComponentModal = false;
            $this->loadComponents();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Log do erro para debug
            logger('Erro ao salvar componente BOM: ' . $e->getMessage());
            
            $this->dispatch('notify',
                type: 'error',
                title: __('messages.error_title'),
                message: __('messages.component_save_error')
            );
        }
    }
    
        
        // DIAGNÓSTICO: Verificar a classe Product
    /**
     * Carregar dados para a view
     */
    public function render()
    {
        logger("DIAGNÓSTICO - Iniciando render() do BomManagement");
        
        // DIAGNÓSTICO: Verificação da tabela de produtos
        try {
            $productTableExists = Schema::hasTable('sc_products');
            logger("DIAGNÓSTICO RENDER - Tabela sc_products existe? " . ($productTableExists ? 'SIM' : 'NÃO'));
            
            if ($productTableExists) {
                $columns = Schema::getColumnListing('sc_products');
                logger("DIAGNÓSTICO RENDER - Colunas em sc_products: " . json_encode($columns));
                
                // Verificar se a coluna type existe
                $hasTypeColumn = in_array('type', $columns);
                logger("DIAGNÓSTICO RENDER - Coluna 'type' existe? " . ($hasTypeColumn ? 'SIM' : 'NÃO'));
                
                // Verificar valores na coluna type
                if ($hasTypeColumn) {
                    $types = DB::table('sc_products')->select('type')->distinct()->get()->pluck('type');
                    logger("DIAGNÓSTICO RENDER - Valores distintos em 'type': " . json_encode($types));
                }
            }
            
            // DIAGNÓSTICO: Verificar a classe Product
            $productClass = new Product();
            $modelTable = $productClass->getTable();
            logger("DIAGNÓSTICO RENDER - Nome da tabela no modelo Product: " . $modelTable);
        } catch (\Exception $e) {
            logger("DIAGNÓSTICO RENDER - ERRO ao verificar tabela: " . $e->getMessage());
        }
        
        // Dados para seleção de produtos na BOM (apenas finished_product)
        try {
            logger("DIAGNÓSTICO RENDER - Iniciando busca de produtos tipo 'finished_product'");
            $productsQuery = Product::where('product_type', 'finished_product');
            $countFinished = $productsQuery->count();
            logger("DIAGNÓSTICO RENDER - Encontrados {$countFinished} produtos 'finished_product'");
            
            $this->products = $productsQuery->orderBy('name')->get();
            logger("DIAGNÓSTICO RENDER - Carregados {$this->products->count()} produtos para BOM");
        } catch (\Exception $e) {
            logger("DIAGNÓSTICO RENDER - ERRO ao carregar produtos: " . $e->getMessage());
            $this->products = collect([]);
        }
        
        // Dados para seleção de componentes - carregar apenas produtos do tipo 'raw_material' cadastrados na BD
        try {
            logger("DIAGNÓSTICO RENDER - Iniciando busca de produtos do tipo 'raw_material' para componentes");
            
            // Verificar todos os valores de product_type na tabela
            $allProductTypes = DB::table('sc_products')->select('product_type')->distinct()->get()->pluck('product_type');
            logger("DIAGNÓSTICO RENDER - Todos os valores de product_type: " . json_encode($allProductTypes));
            
            // Buscar APENAS produtos do tipo 'raw_material' com eager loading de inventoryItems
            // para calcular o estoque total
            $componentsQuery = Product::with('inventoryItems')
                ->where('product_type', 'raw_material');
            
            $countProducts = $componentsQuery->count();
            logger("DIAGNÓSTICO RENDER - Encontrados {$countProducts} produtos do tipo 'raw_material'");
            
            // Carregando produtos com suas informações
            $components = $componentsQuery->orderBy('name')->get();
            
            // Para cada produto, calcular o estoque total usando o accessor
            // ou somar manualmente se necessário
            $this->components = $components->map(function($product) {
                // Adicionar atributo total_stock para uso no template
                if (!isset($product->total_quantity)) {
                    // Se o accessor não estiver disponível, calcular manualmente
                    $product->total_quantity = $product->inventoryItems->sum('quantity_on_hand');
                }
                
                logger("COMPONENTE: ID={$product->id}, Nome={$product->name}, "
                     . "SKU={$product->sku}, Type={$product->product_type}, "
                     . "Estoque Total={$product->total_quantity}");
                     
                return $product;
            });
            
            logger("DIAGNÓSTICO RENDER - Carregados {$this->components->count()} componentes para seleção");
            
            if ($this->components->count() == 0) {
                logger("DIAGNÓSTICO RENDER - NENHUM produto do tipo 'raw_material' encontrado na base de dados");
            }
        } catch (\Exception $e) {
            logger("DIAGNÓSTICO RENDER - ERRO ao carregar componentes: " . $e->getMessage());
            $this->components = collect([]);
        }
        
        // Construir a consulta para a lista de BOMs
        $query = BomHeader::with(['product'])
            ->when($this->search, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('bom_number', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('product', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%");
                      });
                });
            })
            ->when($this->statusFilter, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($this->productFilter, function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->orderBy($this->sortField, $this->sortDirection);
        
        $bomHeaders = $query->paginate($this->perPage);
        
        // Valores para seleção nos campos de status e UOM
        $statuses = [
            'draft' => 'Rascunho',
            'active' => 'Ativo',
            'obsolete' => 'Obsoleto'
        ];
        
        $uoms = [
            'unit' => 'Unidade',
            'kg' => 'Quilograma',
            'l' => 'Litro',
            'g' => 'Grama',
            'ml' => 'Mililitro',
            'pcs' => 'Peças'
        ];
        
        // Carregar informações do cabeçalho da BOM atual se estiver na aba de componentes
        $currentBom = null;
        if ($this->currentTab === 'components' && $this->bomHeaderId) {
            $currentBom = BomHeader::with(['product'])->find($this->bomHeaderId);
        }
        
        // Para garantir que a view receba os componentes corretamente,
        // verificamos se há componentes e garantimos sua estrutura adequada
        if (empty($this->bomComponents) && $this->bomHeaderId) {
            // Se a lista de componentes está vazia, mas existe um bomHeaderId,
            // tentamos carregar novamente os componentes, incluindo soft-deleted
            $this->loadComponents(true);
            
            logger("Render - Recarregando componentes no render. " . 
                   "Total após recarga: " . count($this->bomComponents));
        }
        
        // Força conversão para array, mesmo que vazio, para evitar erros na view
        $bomComponentsArray = !empty($this->bomComponents) ? $this->bomComponents : [];
        
        // Debug para verificar componentes disponíveis para a modal
        logger("DIAGNÓSTICO - Componentes disponíveis para modal (raw_material): " . (is_object($this->components) && method_exists($this->components, 'count') ? $this->components->count() : count($this->components)));
        logger("DIAGNÓSTICO - Componentes na BOM atual: " . count($bomComponentsArray));
        
        return view('livewire.mrp.bom-management', [
            'bomHeaders' => $bomHeaders,
            'productsForBom' => $this->products,
            'componentsForBom' => $this->components, // Raw materials para o select da modal
            'statuses' => $statuses,
            'uoms' => $uoms,
            'currentBom' => $currentBom,
            'components' => $this->components, // Alterado para usar os raw materials no select
            'bomComponents' => $bomComponentsArray, // Componentes da BOM atual
        ])->layout('layouts.livewire', [
            'title' => 'Gestão de Lista de Materiais (BOM)'
        ]);
    }
}
