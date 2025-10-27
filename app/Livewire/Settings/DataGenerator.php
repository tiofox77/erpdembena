<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\Relation;

class DataGenerator extends Component
{
    use WithPagination;
    
    // Estado do gerador
    public $showGeneratorModal = false;
    public $currentStep = 1;
    public $totalSteps = 3;
    public $processing = false;
    public $generationOutput = '';
    
    // Configurações gerais
    public $truncateBeforeInsert = true;
    public $generateForeignKeys = true;
    
    // Tabelas e quantidades
    public $tables = [];
    public $filteredTables = [];
    public $selectedTables = [];
    public $quantities = [];
    
    // Filtros
    public $tableSearch = '';
    
    // Relações detectadas
    public $relationships = [];
    
    // Configurações de campos específicos
    public $fieldConfigs = [];
    
    /**
     * Define listeners para eventos
     */
    protected function getListeners()
    {
        return [
            'openDataGenerator' => 'openGeneratorModal',
        ];
    }
    
    /**
     * Inicializa o componente
     */
    public function mount()
    {
        $this->loadTableStructure();
    }
    
    /**
     * Abre o modal do gerador de dados
     */
    public function openGeneratorModal()
    {
        $this->resetData();
        $this->loadTableStructure();
        $this->showGeneratorModal = true;
        $this->currentStep = 1;
    }
    
    /**
     * Fecha o modal do gerador de dados
     */
    public function closeGeneratorModal()
    {
        $this->showGeneratorModal = false;
        $this->resetData();
    }
    
    /**
     * Reseta os dados do gerador
     */
    private function resetData()
    {
        $this->currentStep = 1;
        $this->selectedTables = [];
        $this->quantities = [];
        $this->tableSearch = '';
        $this->processing = false;
        $this->generationOutput = '';
        $this->fieldConfigs = [];
    }
    
    /**
     * Carrega a estrutura das tabelas do banco de dados
     */
    private function loadTableStructure()
    {
        try {
            // Obter todas as tabelas do banco de dados usando o método nativo do Laravel
            $tables = [];
            
            // Consulta para obter todas as tabelas do banco de dados
            $dbName = DB::connection()->getDatabaseName();
            $tableResults = DB::select("SHOW TABLES FROM `{$dbName}`");
            
            // O formato do resultado é específico do driver, então vamos pegar o primeiro valor de cada linha
            foreach ($tableResults as $tableResult) {
                $varName = "Tables_in_{$dbName}";
                if (isset($tableResult->$varName)) {
                    $tables[] = $tableResult->$varName;
                }
            }
            
            // Filtrar tabelas do sistema que não precisamos
            $excludeTables = ['migrations', 'password_resets', 'failed_jobs', 'personal_access_tokens'];
            $this->tables = array_filter($tables, function($table) use ($excludeTables) {
                return !in_array($table, $excludeTables);
            });
            
            // Inicializar arrays de configuração
            foreach ($this->tables as $table) {
                if (!isset($this->quantities[$table])) {
                    $this->quantities[$table] = 5; // Valor padrão
                }
            }
            
            // Carregar relacionamentos entre tabelas
            $this->detectRelationships();
            
            // Inicializar a lista filtrada de tabelas
            $this->filterTables();
            
        } catch (\Exception $e) {
            Log::error('Erro ao carregar estrutura das tabelas: ' . $e->getMessage());
        }
    }
    
    /**
     * Detecta relacionamentos entre tabelas baseado em chaves estrangeiras
     */
    private function detectRelationships()
    {
        $this->relationships = [];
        
        try {
            // Obter informações do banco de dados atual
            $dbName = DB::connection()->getDatabaseName();
            
            foreach ($this->tables as $table) {
                try {
                    // Consulta SQL para obter informações sobre chaves estrangeiras
                    $foreignKeys = DB::select("
                        SELECT
                            COLUMN_NAME as column_name,
                            REFERENCED_TABLE_NAME as foreign_table_name,
                            REFERENCED_COLUMN_NAME as foreign_column_name
                        FROM
                            INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                        WHERE
                            TABLE_SCHEMA = '{$dbName}' AND
                            TABLE_NAME = '{$table}' AND
                            REFERENCED_TABLE_NAME IS NOT NULL
                    ");
                    
                    foreach ($foreignKeys as $foreignKey) {
                        $localColumn = $foreignKey->column_name;
                        $foreignTable = $foreignKey->foreign_table_name;
                        $foreignColumn = $foreignKey->foreign_column_name;
                        
                        if (!isset($this->relationships[$table])) {
                            $this->relationships[$table] = [];
                        }
                        
                        $this->relationships[$table][] = [
                            'foreignTable' => $foreignTable,
                            'localColumn' => $localColumn,
                            'foreignColumn' => $foreignColumn
                        ];
                    }
                } catch (\Exception $e) {
                    // Ignorar tabelas com erros
                    Log::warning("Não foi possível carregar chaves estrangeiras para a tabela '{$table}': " . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('Erro ao detectar relacionamentos: ' . $e->getMessage());
        }
    }
    
    /**
     * Atualiza a lista de tabelas filtradas quando o termo de busca muda
     */
    public function updatedTableSearch()
    {
        $this->filterTables();
    }
    
    /**
     * Filtra as tabelas com base no termo de busca
     */
    private function filterTables()
    {
        if (empty($this->tableSearch)) {
            $this->filteredTables = $this->tables;
        } else {
            $this->filteredTables = array_filter($this->tables, function($table) {
                return stripos($table, $this->tableSearch) !== false;
            });
        }
    }
    
    /**
     * Seleciona/deseleciona todas as tabelas
     */
    public function toggleSelectAllTables()
    {
        if (count($this->selectedTables) === count($this->tables)) {
            $this->selectedTables = [];
        } else {
            $this->selectedTables = $this->tables;
        }
    }
    
    /**
     * Avança para o próximo passo
     */
    public function nextStep()
    {
        if ($this->currentStep === 1) {
            // Validar se pelo menos uma tabela foi selecionada
            if (empty($this->selectedTables)) {
                $this->dispatch('notify', 
                    type: 'error', 
                    message: 'Selecione pelo menos uma tabela'
                );
                return;
            }
            
            // Preparar configurações de campos para tabelas selecionadas
            $this->prepareFieldConfigurations();
        }
        
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }
    
    /**
     * Volta para o passo anterior
     */
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }
    
    /**
     * Prepara as configurações de campos para as tabelas selecionadas
     */
    private function prepareFieldConfigurations()
    {
        foreach ($this->selectedTables as $table) {
            if (!isset($this->fieldConfigs[$table])) {
                $columns = Schema::getColumnListing($table);
                $this->fieldConfigs[$table] = [];
                
                foreach ($columns as $column) {
                    $columnType = Schema::getColumnType($table, $column);
                    
                    // Configuração padrão para cada tipo de campo
                    $config = [
                        'type' => $columnType,
                        'faker_method' => $this->suggestFakerMethod($column, $columnType),
                        'parameters' => [],
                        'custom_value' => null,
                        'use_custom' => false,
                    ];
                    
                    // Detecção mais precisa para colunas de soft delete e timestamps
                    $isSoftDeleteColumn = $column === 'deleted_at';
                    $isTimestampColumn = in_array($column, ['created_at', 'updated_at']);
                    
                    // Configurações específicas por tipo e nome de coluna
                    switch ($columnType) {
                        case 'integer':
                        case 'bigint':
                            $config['parameters'] = ['min' => 1, 'max' => 1000];
                            break;
                            
                        case 'string':
                            // Tamanho variável baseado no tipo de dado esperado
                            $length = 10;
                            if (strpos($column, 'name') !== false) $length = 50;
                            if (strpos($column, 'description') !== false) $length = 150;
                            if (strpos($column, 'title') !== false) $length = 80;
                            
                            $config['parameters'] = ['length' => $length];
                            break;
                            
                        case 'datetime':
                        case 'timestamp':
                            // Configuração especial para soft deletes
                            if ($isSoftDeleteColumn) {
                                $config['faker_method'] = 'softDeleteTimestamp';
                                $config['parameters'] = [
                                    'nullProbability' => 70, // 70% de chance de ser null
                                    'start' => now()->subYear()->format('Y-m-d'),
                                    'end' => now()->format('Y-m-d')
                                ];
                            } else {
                                $config['parameters'] = [
                                    'start' => now()->subYear()->format('Y-m-d'),
                                    'end' => now()->format('Y-m-d')
                                ];
                            }
                            break;
                            
                        case 'date':
                            $config['parameters'] = [
                                'start' => now()->subYear()->format('Y-m-d'),
                                'end' => now()->format('Y-m-d')
                            ];
                            break;
                    }
                    
                    $this->fieldConfigs[$table][$column] = $config;
                }
            }
        }
    }
    
    /**
     * Verifica se uma coluna é usada para soft delete 
     */
    private function isSoftDeleteColumn($column)
    {
        return $column === 'deleted_at';
    }
    
    /**
     * Sugere um método Faker baseado no nome e tipo da coluna
     */
    private function suggestFakerMethod($column, $type)
    {
        // Tratamento especial para colunas de soft delete
        if ($this->isSoftDeleteColumn($column)) {
            return ['method' => 'dateTimeBetween', 'parameters' => ['start' => '-6 months', 'end' => 'now'], 'softDelete' => true];
        }
        
        // Mapeia nomes de colunas comuns para métodos Faker
        $nameToMethod = [
            'name' => 'name',
            'first_name' => 'firstName',
            'last_name' => 'lastName',
            'email' => 'email',
            'phone' => 'phoneNumber',
            'password' => 'password',
            'address' => 'address',
            'city' => 'city',
            'country' => 'country',
            'zip_code' => 'postcode',
            'description' => 'text',
            'title' => 'sentence',
            'url' => 'url',
            'image' => 'imageUrl',
            'created_at' => 'dateTime',
            'updated_at' => 'dateTime',
        ];
        
        // Verifica se o nome da coluna está no mapeamento
        foreach ($nameToMethod as $pattern => $method) {
            if (stripos($column, $pattern) !== false) {
                return $method;
            }
        }
        
        // Se não encontrou no mapeamento, sugere com base no tipo
        switch ($type) {
            case 'integer':
            case 'bigint':
                return 'numberBetween';
            case 'boolean':
                return 'boolean';
            case 'float':
            case 'decimal':
                return 'randomFloat';
            case 'string':
                return 'text';
            case 'datetime':
            case 'date':
                return 'dateTimeBetween';
            default:
                return 'word';
        }
    }
    
    /**
     * Gera os dados de teste
     */
    public function generateData()
    {
        $this->processing = true;
        $this->generationOutput = '';
        
        try {
            $this->addOutput("Iniciando geração de dados de teste...\n");
            
            // Ordenar tabelas para respeitar dependências
            $orderedTables = $this->getTablesInDependencyOrder();
            $this->addOutput("Ordem de geração: " . implode(', ', $orderedTables) . "\n\n");
            
            // Criar arquivo de seeder temporário
            $tempSeederClass = 'TempDataGenerator' . time();
            $tempSeederPath = database_path('seeders/' . $tempSeederClass . '.php');
            
            $this->createSeederFile($tempSeederClass, $tempSeederPath, $orderedTables);
            $this->addOutput("Arquivo de seeder criado: {$tempSeederClass}.php\n");
            
            // Executar o seeder
            Artisan::call('db:seed', [
                '--class' => $tempSeederClass,
                '--force' => true
            ]);
            
            $this->addOutput("\nSaída do seeder:\n" . Artisan::output());
            $this->addOutput("\nGeração de dados concluída com sucesso!");
            
            // Remover arquivo temporário
            if (file_exists($tempSeederPath)) {
                unlink($tempSeederPath);
                $this->addOutput("\nArquivo de seeder temporário removido.");
            }
            
            $this->dispatch('notify', 
                type: 'success', 
                message: 'Dados de teste gerados com sucesso'
            );
            
        } catch (\Exception $e) {
            Log::error('Erro na geração de dados: ' . $e->getMessage());
            $this->addOutput("\nERRO: " . $e->getMessage());
            
            $this->dispatch('notify', 
                type: 'error', 
                message: 'Erro na geração de dados: ' . $e->getMessage()
            );
        } finally {
            $this->processing = false;
        }
    }
    
    /**
     * Obtém as tabelas em ordem de dependência (tabelas dependentes após seus pais)
     */
    private function getTablesInDependencyOrder()
    {
        $tables = $this->selectedTables;
        $orderedTables = [];
        $visited = [];
        
        // Função recursiva para ordenação topológica
        $visit = function($table) use (&$visit, &$orderedTables, &$visited, &$tables) {
            if (isset($visited[$table])) {
                return;
            }
            
            $visited[$table] = true;
            
            // Visitar as tabelas dependentes primeiro
            if (isset($this->relationships[$table])) {
                foreach ($this->relationships[$table] as $relation) {
                    $foreignTable = $relation['foreignTable'];
                    if (in_array($foreignTable, $tables)) {
                        $visit($foreignTable);
                    }
                }
            }
            
            $orderedTables[] = $table;
        };
        
        // Visitar todas as tabelas selecionadas
        foreach ($tables as $table) {
            if (!isset($visited[$table])) {
                $visit($table);
            }
        }
        
        // Inverter a ordem para que as tabelas principais venham primeiro
        return array_reverse($orderedTables);
    }
    
    /**
     * Cria o arquivo de seeder temporário
     */
    private function createSeederFile($className, $filePath, $tables)
    {
        $content = "<?php\n\n";
        $content .= "namespace Database\\Seeders;\n\n";
        $content .= "use Illuminate\\Database\\Seeder;\n";
        $content .= "use Illuminate\\Support\\Facades\\DB;\n";
        $content .= "use Illuminate\\Support\\Str;\n";
        $content .= "use Faker\\Factory as Faker;\n\n";
        
        $content .= "class {$className} extends Seeder\n{\n";
        $content .= "    /**\n";
        $content .= "     * Run the database seeds.\n";
        $content .= "     */\n";
        $content .= "    public function run()\n    {\n";
        $content .= "        \$faker = Faker::create();\n\n";
        
        // Para cada tabela, gerar o código de seeding
        foreach ($tables as $table) {
            $content .= "        // Seeding table: {$table}\n";
            
            if ($this->truncateBeforeInsert) {
                $content .= "        DB::table('{$table}')->truncate();\n";
            }
            
            $content .= "        for (\$i = 0; \$i < {$this->quantities[$table]}; \$i++) {\n";
            $content .= "            DB::table('{$table}')->insert([\n";
            
            // Obter todas as colunas da tabela
            $columns = Schema::getColumnListing($table);
            $insertCode = [];
            
            foreach ($columns as $column) {
                // Para colunas automáticas de timestamps, pular a geração padrão
                if (in_array($column, ['id', 'created_at', 'updated_at'])) {
                    continue;
                }
                
                // Para colunas de soft delete, o tratamento é feito via configuração especial
                // que já é identificada no processo abaixo              
                
                $config = $this->fieldConfigs[$table][$column] ?? null;
                
                if (!$config) {
                    continue;
                }
                
                // Verificar se é uma chave estrangeira
                $isForeignKey = false;
                $foreignTableIds = [];
                
                if ($this->generateForeignKeys && isset($this->relationships[$table])) {
                    foreach ($this->relationships[$table] as $relation) {
                        if ($relation['localColumn'] === $column) {
                            $isForeignKey = true;
                            $foreignTable = $relation['foreignTable'];
                            
                            // Obter IDs existentes da tabela referenciada
                            $content .= "                // Referencia tabela {$foreignTable}\n";
                            $insertCode[] = "                '{$column}' => DB::table('{$foreignTable}')->inRandomOrder()->value('id') ?? 1";
                            break;
                        }
                    }
                }
                
                if (!$isForeignKey) {
                    // Gerar código para o valor usando Faker
                    if ($config['use_custom'] && $config['custom_value'] !== null) {
                        $insertCode[] = "                '{$column}' => '{$config['custom_value']}'";
                    } else {
                        $fakerMethod = $config['faker_method'];
                        
                        switch ($fakerMethod) {
                            case 'numberBetween':
                                $min = $config['parameters']['min'] ?? 1;
                                $max = $config['parameters']['max'] ?? 1000;
                                $insertCode[] = "                '{$column}' => \$faker->numberBetween({$min}, {$max})";
                                break;
                            case 'randomFloat':
                                $insertCode[] = "                '{$column}' => \$faker->randomFloat(2, 1, 1000)";
                                break;
                            case 'dateTimeBetween':
                                $start = $config['parameters']['start'] ?? '-1 year';
                                $end = $config['parameters']['end'] ?? 'now';
                                
                                // Verifica se é uma coluna de soft delete para tratamento especial
                                if (isset($config['softDelete']) && $config['softDelete']) {
                                    // 70% de chance de ser null (registro não deletado)
                                    $insertCode[] = "                '{$column}' => \$faker->boolean(70) ? null : \$faker->dateTimeBetween('{$start}', '{$end}')->format('Y-m-d H:i:s')";
                                } else {
                                    // Tratamento geral para datas
                                    $insertCode[] = "                '{$column}' => \$faker->dateTimeBetween('{$start}', '{$end}')->format('Y-m-d H:i:s')";
                                }
                                break;
                            case 'text':
                                $length = $config['parameters']['length'] ?? 100;
                                $insertCode[] = "                '{$column}' => \$faker->text({$length})";
                                break;
                            case 'sentence':
                                $insertCode[] = "                '{$column}' => \$faker->sentence()";
                                break;
                            case 'paragraph':
                                $insertCode[] = "                '{$column}' => \$faker->paragraph()";
                                break;
                            case 'word':
                                $insertCode[] = "                '{$column}' => \$faker->word()";
                                break;
                            case 'name':
                                $insertCode[] = "                '{$column}' => \$faker->name()";
                                break;
                            case 'firstName':
                                $insertCode[] = "                '{$column}' => \$faker->firstName()";
                                break;
                            case 'lastName':
                                $insertCode[] = "                '{$column}' => \$faker->lastName()";
                                break;
                            case 'email':
                                $insertCode[] = "                '{$column}' => \$faker->safeEmail()";
                                break;
                            case 'phoneNumber':
                                $insertCode[] = "                '{$column}' => \$faker->phoneNumber()";
                                break;
                            case 'address':
                                $insertCode[] = "                '{$column}' => \$faker->address()";
                                break;
                            case 'city':
                                $insertCode[] = "                '{$column}' => \$faker->city()";
                                break;
                            case 'country':
                                $insertCode[] = "                '{$column}' => \$faker->country()";
                                break;
                            case 'postcode':
                                $insertCode[] = "                '{$column}' => \$faker->postcode()";
                                break;
                            case 'url':
                                $insertCode[] = "                '{$column}' => \$faker->url()";
                                break;
                            case 'imageUrl':
                                $insertCode[] = "                '{$column}' => \$faker->imageUrl()";
                                break;
                            case 'boolean':
                                $insertCode[] = "                '{$column}' => \$faker->boolean()";
                                break;
                            case 'password':
                                $insertCode[] = "                '{$column}' => bcrypt('password')";
                                break;
                            default:
                                $insertCode[] = "                '{$column}' => \$faker->word()";
                                break;
                        }
                    }
                }
            }
            
            $content .= implode(",\n", $insertCode) . "\n";
            $content .= "            ]);\n";
            $content .= "        }\n\n";
        }
        
        $content .= "    }\n";
        $content .= "}\n";
        
        file_put_contents($filePath, $content);
    }
    
    /**
     * Adiciona texto à saída do gerador
     */
    private function addOutput($text)
    {
        $this->generationOutput .= $text;
    }
    
    /**
     * Renderiza o componente
     */
    public function render()
    {
        return view('livewire.settings.data-generator');
    }
}
