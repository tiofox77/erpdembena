# Documentação de Desenvolvimento - Módulo MRP

## Visão Geral
Este documento descreve o desenvolvimento do módulo MRP (Material Requirements Planning) para o sistema ERPDEMBENA. O módulo MRP permite o planejamento eficiente de materiais, produção e recursos.

## Estrutura do Módulo

### Fluxo de Trabalho
1. **Planejamento da Demanda**
   - Coleta de dados históricos
   - Previsão de demanda
   - Validação com setores de vendas/marketing

2. **Estruturação da Lista de Materiais (BOM)**
   - Estrutura detalhada dos produtos
   - Definição de componentes e matérias-primas
   - Gerenciamento de BOM no sistema

3. **Gestão de Estoques**
   - Avaliação de estoque disponível
   - Definição de níveis de estoque
   - Controle automatizado

4. **Master Production Schedule (MPS)**
   - Cronograma mestre de produção
   - Ajuste de capacidade produtiva
   - Integração com sistema MRP

5. **Planejamento de Compras e Fornecedores**
   - Avaliação de fornecedores
   - Programação de compras
   - Reposição automática

6. **Controle de Capacidade Produtiva**
   - Balanço de recursos
   - Otimização de capacidade

7. **Planejamento e Controle da Produção**
   - Ordens de produção
   - Monitoramento de execução
   - Ajustes em tempo real

8. **Análise e Controle Financeiro**
   - Monitoramento de custos
   - Relatórios financeiros integrados

## Estrutura de Dados

### Tabelas Principais
1. `mrp_demand_forecasts` - Previsões de demanda
2. `mrp_bom_headers` - Cabeçalhos de lista de materiais
3. `mrp_bom_details` - Detalhes de lista de materiais 
4. `mrp_inventory_levels` - Níveis de estoque para MRP
5. `mrp_production_schedules` - Cronogramas de produção
6. `mrp_purchase_plans` - Planos de compra
7. `mrp_capacity_plans` - Planos de capacidade
8. `mrp_production_orders` - Ordens de produção
9. `mrp_financial_reports` - Relatórios financeiros

## Componentes Livewire

### Dashboard
- `MrpDashboard` - Visão geral do sistema MRP

### Demanda
- `DemandForecasting` - Previsão e gestão de demanda

### BOM
- `BomManagement` - Gestão de listas de materiais

### Estoque
- `InventoryLevels` - Gestão de níveis de estoque

### Produção
- `ProductionScheduling` - Agendamento de produção
- `ProductionOrders` - Ordens de produção

### Compras
- `PurchasePlanning` - Planejamento de compras

### Capacidade
- `CapacityPlanning` - Planejamento de capacidade

### Relatórios
- `FinancialReporting` - Relatórios financeiros do MRP

## Boas Práticas de Desenvolvimento

### Consistência entre Componentes Livewire e Templates Blade

Para evitar erros de variáveis indefinidas e garantir que o componente Livewire e seu template Blade estejam sincronizados, siga estas orientações. Estas regras devem ser aplicadas a TODOS os componentes Livewire do módulo MRP sem exceção.

#### Regras Fundamentais de Consistência

1. **Análise prévia completa do template Blade**: 
   - Examine o arquivo .blade.php inteiro, incluindo todas as seções (HTML, JavaScript, e diretivas Blade)
   - **IMPORTANTE**: Verifique também todos os arquivos incluídos via `@include()` nos templates principais
   - Identifique todas as variáveis utilizadas em expressões {{ $variavel }}
   - Verifique diretivas como @if, @foreach, @forelse que usam variáveis
   - Examine scripts JavaScript que acessam variáveis via @json($variavel)
   - Verifique chamadas de método wire:click="método({{ $variavel }})" 

2. **Verificação sistemática com lista de checagem**:
   - Mantenha uma lista completa de todas as variáveis encontradas no template
   - Para cada variável, confirme a existência de uma propriedade pública correspondente no componente
   - **CRÍTICO**: Garanta que o nome seja EXATAMENTE igual no componente e no template (ex: `$boms` vs `$bomHeaders`)
   - Verifique se o tipo corresponde exatamente àquele esperado pelo template
   - Preste atenção especial a variáveis que são arrays ou objetos e verifique se todas as propriedades acessadas estão disponíveis

3. **Áreas de foco específicas**: Verifique com atenção redobrada:
   - Variáveis usadas em loops (@foreach, @forelse)
   - Variáveis usadas em condicionais (@if, @unless)
   - Variáveis passadas para componentes filhos
   - Variáveis usadas em scripts JavaScript (especialmente em @json())
   - Variáveis usadas em gráficos e visualizações de dados
   - **ATENÇÃO**: Templates incluídos via `@include()` que podem necessitar das mesmas variáveis do template principal

#### Procedimento de Verificação de Consistência

1. **Análise Estática**:
   - Execute `grep` ou busca similar para encontrar todos os usos de variáveis (`$variável`)
   - Execute busca por estruturas de controle (@foreach, @forelse, @if) para identificar variáveis usadas
   - Compile uma lista completa de todas as variáveis utilizadas no template e seus includes

2. **Verificação do Componente**:
   - Compare cada variável da lista com as propriedades públicas do componente
   - Verifique se o método `render()` retorna todas as variáveis necessárias com os nomes corretos
   - Garanta que variáveis de mesmo propósito tenham o mesmo nome em todo o componente
   - Evite ambiguidade entre nomes similares (ex: `$components` vs `$bomComponents`)

3. **Procedimento para Templates com Includes**:
   - Identifique todos os arquivos incluídos via `@include()`
   - Verifique cada arquivo incluído separadamente
   - Garanta que todas as variáveis usadas nos includes estejam disponíveis no componente principal
   - Considere o uso de propriedades públicas no componente para garantir acesso por todos os templates

#### Exemplo de Problemas Comuns e Soluções

1. **Problema**: Variável usada no template com nome diferente no componente.
   - **Exemplo**: `@foreach($bomHeaders as $bom)` no template, mas `$boms = $query->paginate($perPage)` no componente.
   - **Solução**: Padronizar os nomes, renomeando para `$bomHeaders` no componente ou adaptando o template.

2. **Problema**: Variável usada em template incluído via @include() não disponível.
   - **Exemplo**: `@foreach($products as $product)` em um modal incluído, mas variável não passada no retorno do método render().
   - **Solução**: Adicionar a variável ao array retornado pelo método render() ou criar propriedade pública.

3. **Problema**: Namespace incorreto para modelos usados.
   - **Exemplo**: `use App\Models\Product` quando deveria ser `use App\Models\SupplyChain\Product`.
   - **Solução**: Corrigir o namespace e verificar importações em todos os componentes relacionados.

4. **Desenvolvimento incremental**:
   - Desenvolva os componentes em pequenos passos, testando frequentemente
   - Adicione um conjunto limitado de variáveis de cada vez
   - Teste após cada adição significativa
   - Use a ferramenta de inspeção do Laravel Livewire para verificar os valores

5. **Documentação clara**:
   - Mantenha uma tabela no documento do componente que lista todas as variáveis
   - Para cada variável, documente seu propósito, tipo, formato e valor padrão
   - Documente quaisquer relacionamentos complexos entre variáveis

6. **Uso de valores padrão robustos**:
   - Inicialize todas as variáveis do componente com valores padrão apropriados
   - Use arrays vazios ([]) para variáveis que são iteradas em loops
   - Use strings vazias ('') ou valores nulos para texto
   - Use 0 para contadores numéricos
   - Forneça estruturas completas para dados de gráficos, mesmo que vazias

7. **Tratamento de exceções**:
   - Implemente tratamento de erros para evitar falhas quando os dados não estiverem disponíveis
   - Use blocos try/catch em métodos que carregam dados
   - Sempre tenha um plano B para quando os dados não estiverem disponíveis

8. **Testes completos**:
   - Teste o componente em diferentes estados: vazio, parcialmente carregado, totalmente carregado
   - Verifique o comportamento quando as tabelas de banco de dados não existem
   - Teste cenários de erro e recuperação

9. **Revisão completa de código**:
   - Use um processo de revisão estruturado que exige verificação de todas as variáveis
   - Mantenha uma lista de verificação para revisões de código
   - Use uma ferramenta de note-taking para rastrear variáveis entre o controlador e o template

10. **Checklist de Verificação Final**:
    - Todas as variáveis usadas no template estão definidas no componente?
    - Todas as variáveis têm valores padrão apropriados?
    - As estruturas de dados complexas (arrays, objetos) têm todos os campos necessários?
    - O componente lida adequadamente com situações de erro?
    - Variáveis usadas em scripts JavaScript estão corretamente serializadas com @json()?
    - Foram testados diferentes estados de dados (vazio, parcial, completo)?

### Padronização de Código

1. **PSR-12**: Siga as recomendações PSR-12 para estilo de código PHP.

2. **Comentários**: Documente adequadamente o código com comentários explicativos para métodos e propriedades.

3. **Nomenclatura**: Use nomes descritivos e consistentes para variáveis, métodos, classes e componentes.

4. **Reuso**: Aproveite traits e componentes compartilháveis para evitar duplicação de código.

### Padrão para Sistema de Notificações

#### Sintaxe Obrigatória (PHP 8+)

Todas as notificações no sistema ERPDEMBENA DEVEM utilizar o seguinte formato com named parameters:

```php
$this->dispatch('notify', 
    type: $tipoNotificacao, 
    title: __('messages.titulo_notificacao'), 
    message: __('messages.mensagem_notificacao')
);
```

#### Tipos de Notificação e Seus Usos

Existe um padrão de cores específico para cada tipo de operação. Utilize **SEMPRE** o tipo correto:

- **success** (verde): Exclusivamente para operações de CRIAÇÃO bem-sucedidas
- **warning** (amarelo): Exclusivamente para operações de EDIÇÃO/ATUALIZAÇÃO
- **error** (vermelho): Para exclusões e erros/problemas
- **info** (azul): Para informações neutras e notificações de sistema

#### Exemplos Corretos de Uso

##### Criação (success)
```php
$this->dispatch('notify', 
    type: 'success', 
    title: __('messages.bom_created_title'),
    message: __('messages.bom_created_message')
);
```

##### Edição (warning)
```php
$this->dispatch('notify', 
    type: 'warning', 
    title: __('messages.bom_updated_title'),
    message: __('messages.bom_updated_message')
);
```

##### Detecção automática de criação/edição
```php
$this->dispatch('notify', 
    type: $this->editMode ? 'warning' : 'success', 
    title: $this->editMode 
        ? __('messages.bom_updated_title') 
        : __('messages.bom_created_title'),
    message: $this->editMode 
        ? __('messages.bom_updated_message') 
        : __('messages.bom_created_message')
);
```

##### Exclusão (error)
```php
$this->dispatch('notify', 
    type: 'error', 
    title: __('messages.bom_deleted_title'),
    message: __('messages.bom_deleted_message')
);
```

##### Erro (error)
```php
$this->dispatch('notify', 
    type: 'error', 
    title: __('messages.error_title'),
    message: __('messages.error_message', ['error' => $e->getMessage()])
);
```

#### Regras de Implementação

1. **OBRIGATÓRIO**: Usar named parameters (não arrays) em todas as notificações
2. **OBRIGATÓRIO**: Todas as mensagens e títulos devem usar o helper `__()` para suporte a múltiplos idiomas
3. **OBRIGATÓRIO**: Seguir o padrão de cores por tipo de operação (success=criação, warning=edição, error=exclusão/erros)
4. **OBRIGATÓRIO**: As chaves de tradução devem existir em TODOS os arquivos de tradução (en/pt)
5. **RECOMENDADO**: Usar detecção automática de modo para componentes que alternam entre criação/edição

#### Nomenclatura para Chaves de Tradução

As chaves de tradução para notificações devem seguir o padrão:

- `[entidade]_[ação]_[title|message]`

Exemplos:
- `bom_created_title`, `bom_created_message`
- `component_updated_title`, `component_updated_message`
- `inventory_deleted_title`, `inventory_deleted_message`

### Verificação de Consistência entre Modelos e Banco de Dados

Uma fonte comum de erros no ERPDEMBENA é a inconsistência entre a definição dos modelos e a estrutura real das tabelas no banco de dados. Estes erros podem ocorrer durante o desenvolvimento quando:

1. Um modelo define propriedades `$fillable` que não existem na tabela correspondente
2. O código tenta ordenar por colunas inexistentes
3. Regras de validação se referem a tabelas/colunas incorretas

#### Padrão para Detecção de Colunas Inexistentes

Para prevenir erros do tipo `Unknown column X in 'field list'` ou `Column not found`, SEMPRE use o seguinte padrão ao salvar dados:

```php
// Obter apenas as colunas que realmente existem na tabela
$existingColumns = Schema::getColumnListing('nome_da_tabela');

// Filtrar os dados de entrada para conter apenas colunas existentes
$dataToSave = collect($inputData)
    ->filter(function ($value, $key) use ($existingColumns) {
        return in_array($key, $existingColumns);
    })
    ->toArray();
    
// Usar os dados filtrados para operações de inserção/atualização
$model->fill($dataToSave);
$model->save();
```

#### Checklist de Verificação de Banco de Dados

Antes de implementar qualquer novo componente, siga este checklist:

1. **Verificação de Tabelas**:
   - Verifique se o nome da tabela no modelo (`protected $table`) corresponde à tabela real no banco
   - Para modelos que usam nomes de tabela padrão (pluralização automática), confirme se a tabela existe

2. **Verificação de Colunas**:
   - Compare as colunas `$fillable` no modelo com as colunas reais da tabela
   - Use `Schema::getColumnListing('tabela')` para obter a lista real de colunas
   - Documente quaisquer discrepancias entre o modelo e o banco de dados

3. **Adaptação Defensiva**:
   - Implemente o padrão de filtragem de colunas mostrado acima
   - Evite referências diretas a colunas em operações de ordenação sem verificar sua existência
   - Para regras de validação `exists`, sempre use o nome correto da tabela

4. **Documentação de Discrepancias**:
   - Para cada tabela com discrepancias entre modelo e banco de dados, documente no arquivo do modelo:
   ```php
   /**
    * NOTA: Discrepancias entre modelo e banco de dados
    * - Coluna 'level': Presente no modelo, ausente na tabela
    * - Coluna 'position': Presente no modelo, ausente na tabela
    */
   ```

#### Exemplo de Classe Auxiliar para Verificação de Colunas

```php
class DatabaseHelper
{
    /**
     * Filtra os dados de entrada para conter apenas colunas existentes na tabela
     *
     * @param string $table Nome da tabela
     * @param array $data Dados a serem filtrados
     * @return array Dados filtrados contendo apenas colunas existentes
     */
    public static function filterNonExistentColumns(string $table, array $data): array
    {
        $existingColumns = Schema::getColumnListing($table);
        
        return collect($data)
            ->filter(function ($value, $key) use ($existingColumns) {
                return in_array($key, $existingColumns);
            })
            ->toArray();
    }
}
```

## Detalhes de Implementação

### 1. Modelo de Dados

#### Tabela: mrp_demand_forecasts
- **Objetivo**: Armazenar previsões de demanda para produtos
- **Campos principais**:
  - `id`: Identificador único
  - `product_id`: ID do produto (foreign key)
  - `forecast_date`: Data da previsão
  - `forecast_quantity`: Quantidade prevista
  - `actual_quantity`: Quantidade real (opcional)
  - `notes`: Observações adicionais
  - `created_by`, `updated_by`: Usuários responsáveis
  - `created_at`, `updated_at`: Timestamps

#### Tabela: mrp_bom_headers
- **Objetivo**: Armazenar cabeçalhos das listas de materiais
- **Campos principais**:
  - `id`: Identificador único
  - `product_id`: Produto final (foreign key)
  - `description`: Descrição da BOM
  - `version`: Versão da BOM
  - `status`: Status (active, draft, obsolete)
  - `effective_date`: Data de início de validade
  - `expiry_date`: Data de expiração (opcional)
  - `created_by`, `updated_by`: Usuários responsáveis
  - `created_at`, `updated_at`: Timestamps

#### Tabela: mrp_bom_details
- **Objetivo**: Armazenar detalhes dos componentes da BOM
- **Campos principais**:
  - `id`: Identificador único
  - `bom_header_id`: ID do cabeçalho da BOM (foreign key)
  - `component_id`: ID do componente (foreign key para products)
  - `quantity`: Quantidade necessária
  - `uom`: Unidade de medida
  - `position`: Posição na estrutura
  - `level`: Nível na estrutura
  - `is_critical`: Flag para componentes críticos
  - `created_by`, `updated_by`: Usuários responsáveis
  - `created_at`, `updated_at`: Timestamps

#### Tabela: mrp_production_orders
- **Objetivo**: Gerenciar ordens de produção
- **Campos principais**:
  - `id`: Identificador único
  - `order_number`: Número da ordem (único)
  - `product_id`: Produto a ser produzido (foreign key)
  - `bom_header_id`: BOM utilizada (foreign key, opcional)
  - `schedule_id`: Programação relacionada (foreign key, opcional)
  - `planned_start_date`: Data de início planejada
  - `planned_end_date`: Data de término planejada
  - `actual_start_date`: Data de início real (opcional)
  - `actual_end_date`: Data de término real (opcional)
  - `planned_quantity`: Quantidade planejada
  - `produced_quantity`: Quantidade produzida
  - `rejected_quantity`: Quantidade rejeitada
  - `status`: Status da ordem (draft, released, in_progress, completed, cancelled)
  - `priority`: Prioridade (low, medium, high, urgent)
  - `location_id`: Local de produção (foreign key)
  - `notes`: Observações
  - `created_by`, `updated_by`: Usuários responsáveis
  - `created_at`, `updated_at`: Timestamps

### 2. Componentes Implementados

#### 2.1 MrpDashboard
- **Função**: Fornecer visão geral do sistema MRP com indicadores principais
- **Características**:
  - Resumo de ordens de produção por status
  - Alertas de materiais com estoque baixo
  - Indicadores de demanda vs. capacidade
  - Gráficos de tendências

#### 2.2 DemandForecasting
- **Função**: Gerenciamento de previsões de demanda
- **Características**:
  - Entrada manual de previsões
  - Importação de dados históricos
  - Visualização de tendências
  - Comparação entre previsão e demanda real

#### 2.3 BomManagement
- **Função**: Gerenciamento de listas de materiais
- **Características**:
  - Criação e edição de BOMs
  - Versionamento de BOMs
  - Visualização da estrutura em árvore
  - Gerenciamento de componentes

#### 2.4 InventoryLevels
- **Função**: Configuração e monitoramento de níveis de estoque
- **Características**:
  - Definição de níveis mínimos e máximos
  - Alertas de estoque baixo
  - Visualização de tendências
  - Recomendações para reabastecimento

#### 2.5 ProductionScheduling
- **Função**: Criação e gerenciamento de programações de produção
- **Características**:
  - Definição de períodos de produção
  - Alocação de recursos
  - Visualização de calendário
  - Replanejamento e ajustes

#### 2.6 ProductionOrders
- **Função**: Gerenciamento de ordens de produção
- **Características**:
  - Criação automática/manual de ordens
  - Fluxo de trabalho definido (draft → released → in_progress → completed)
  - Monitoramento de progresso
  - Análise de materiais necessários
  - Integração com BOMs
  - Interface moderna seguindo padrão ERPDEMBENA

### 3. Relações entre Componentes

- **DemandForecasting → ProductionScheduling**: As previsões de demanda alimentam o planejamento de produção
- **BomManagement → ProductionOrders**: As BOMs são usadas pelas ordens de produção para calcular necessidades de materiais
- **ProductionScheduling → ProductionOrders**: As programações geram ordens de produção
- **InventoryLevels → ProductionOrders**: Níveis de estoque são considerados para verificar disponibilidade de materiais
- **ProductionOrders → PurchasePlanning**: Ordens de produção podem gerar necessidades de compra
- **ProductionOrders → CapacityPlanning**: Ordens de produção consomem capacidade produtiva

## Status de Desenvolvimento

| Componente | Migrations | Model | Controller | Livewire | Views | Testes | Status |
|------------|------------|-------|------------|----------|-------|--------|--------|
| Dashboard  | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | Concluído |
| Demanda    | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | Concluído |
| BOM        | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | Concluído |
| Estoque    | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | Concluído |
| Produção (Scheduling) | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | Concluído |
| Produção (Orders)     | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | Concluído |
| Compras    | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | Concluído |
| Capacidade | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | Concluído |
| Relatórios | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | Concluído |

## Padrões de UI/UX

### Estrutura de Interface
Todos os componentes do módulo MRP seguem o padrão de UI/UX definido para o sistema ERPDEMBENA. A implementação exemplar desses padrões pode ser encontrada no módulo de Programação de Produção (ProductionScheduling).

### Estrutura de Arquivos Views
A organização dos arquivos segue este padrão:

```
resources/views/livewire/mrp/
├── [componente-principal].blade.php         # View principal (ex: production-scheduling.blade.php)
└── [componente-principal]/                  # Pasta de modais do componente
    ├── create-edit-modal.blade.php          # Modal de criação/edição
    ├── delete-modal.blade.php               # Modal de confirmação de exclusão
    ├── view-modal.blade.php                 # Modal de visualização detalhada
    └── [outras-modais-específicas].blade.php # Modais adicionais conforme necessidade
```

### Componentes de Interface Padrão

1. **Cabeçalho Principal**
   - Título com ícone contextual em gradiente azul-escuro
   - Animação `animate__fadeIn` para elementos principais
   - Botão de ação principal com `transform hover:scale-105` e `transition-all duration-200`
   - Abas de navegação para alternar entre visualizações (lista/calendário)

   ```html
   <div class="flex justify-between items-center mb-4 animate__animated animate__fadeIn">
       <h2 class="text-xl font-semibold text-gray-800 flex items-center">
           <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
           {{ __('messages.production_scheduling') }}
       </h2>
       <button wire:click="openCreateModal" 
           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent 
           rounded-md font-semibold text-sm text-white hover:bg-blue-700 
           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 
           transition-all duration-200 transform hover:scale-105 shadow-sm">
           <i class="fas fa-plus mr-2"></i>
           {{ __('messages.add_new') }}
       </button>
   </div>
   ```

2. **Cartão de Filtros Responsivo**
   - Layout em grid que se adapta a diferentes tamanhos de tela: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4`
   - Animação sutil ao passar o mouse: `hover:shadow-md transition-all duration-300`
   - Ícones contextuais para cada filtro
   - Campo de busca com debounce para otimização de performance

   ```html
   <div class="bg-white rounded-lg shadow p-4 mb-4">
       <h3 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
           <i class="fas fa-filter text-blue-500 mr-2"></i>
           {{ __('messages.filters') }}
       </h3>
       <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
           <!-- Campo de busca com debounce -->
           <div class="relative">
               <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                   <i class="fas fa-search text-gray-400"></i>
               </div>
               <input wire:model.debounce.300ms="search" type="text" 
                   class="pl-10 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                   placeholder="{{ __('messages.search') }}...">
           </div>
           <!-- Outros filtros adaptados ao componente -->
       </div>
   </div>
   ```

3. **Tabela de Dados Responsiva**
   - Cabeçalho com gradiente: `bg-gradient-to-r from-blue-600 to-blue-800 text-white`
   - Animação de linhas com `hover:bg-blue-50 transition-all duration-150`
   - Ordenação interativa: `wire:click="sortBy('campo')"` com indicadores de direção
   - Status coloridos com ícones animados
   - Sistema responsivo para telas pequenas usando classes específicas

4. **Modais de Planos Diários com Rastreamento de Falhas**
   - Cabeçalho com gradiente azul e ícone de calendário
   - Agrupamento de informações em cartões com animação ao passar o mouse
   - Campos de breakdown com checkbox e inputs condicionais
   - Seleção de categoria de falha com dropdown simples
   - Seleção múltipla para causas raiz de falha
   - Animações suaves para transições entre estados

   ```html
   <!-- Botão para acessar planos diários -->
   <button wire:click="viewDailyPlans({{ $id }})" 
       class="text-teal-600 hover:text-teal-900 transition-colors duration-200" 
       title="{{ __('messages.view_daily_plans') }}">
       <i class="fas fa-calendar-check"></i>
   </button>
   
   <!-- Campo de breakdown no modal -->
   <div class="flex items-center space-x-2">
       <label class="inline-flex items-center cursor-pointer">
           <input type="checkbox" wire:model.defer="dailyPlans.{{ $index }}.has_breakdown" 
               class="form-checkbox h-5 w-5 text-red-600 transition duration-150 ease-in-out rounded">
           <span class="ml-2 text-sm text-gray-700">{{ __('messages.yes') }}</span>
       </label>
       <!-- Campos condicionais que aparecem quando has_breakdown está marcado -->
       <div x-data="{ show: false }" 
           x-show.transition.opacity="$wire.dailyPlans && $wire.dailyPlans[{{ $index }}] && $wire.dailyPlans[{{ $index }}].has_breakdown" 
           class="relative rounded-md shadow-sm flex-1 ml-2">
           <!-- Campos relacionados a falhas aqui -->
       </div>
   </div>
   ```

   ```html
   <div class="overflow-x-auto bg-white rounded-lg shadow overflow-hidden">
       <table class="min-w-full divide-y divide-gray-200">
           <thead class="bg-gradient-to-r from-blue-600 to-blue-800">
               <tr>
                   <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider cursor-pointer" wire:click="sortBy('campo')">
                       <div class="flex items-center space-x-1">
                           <span>{{ __('messages.column_title') }}</span>
                           @if($sortField === 'campo')
                               <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                           @else
                               <i class="fas fa-sort text-gray-300 ml-1"></i>
                           @endif
                       </div>
                   </th>
                   <!-- Outras colunas -->
               </tr>
           </thead>
           <tbody class="bg-white divide-y divide-gray-200">
               <!-- Linhas com hover e animações -->
           </tbody>
       </table>
   </div>
   ```

4. **Modais Avançados**
   - Transições suaves usando AlpineJS: 
     ```
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0 transform scale-95"
     x-transition:enter-end="opacity-100 transform scale-100"
     ```
   - Cabeçalhos com gradientes contextuais baseados no propósito
   - Conteúdo com scroll interno: `max-h-[90vh] overflow-y-auto`
   - Headers e footers fixos (sticky) para melhor usabilidade
   - Responsividade aprimorada em dispositivos móveis
   - Validação em tempo real com indicadores visuais

   ```html
   <div x-cloak
       class="fixed inset-0 z-30 flex items-center justify-center overflow-auto bg-gray-800 bg-opacity-75 transition-opacity"
       style="width: 100vw; height: 100vh;"
       x-show="$wire.showModal"
       @keydown.escape.window="$wire.closeModal()">
       <div class="relative w-[95%] max-h-[90vh] mx-auto my-4"
           x-show="$wire.showModal"
           x-transition:enter="ease-out duration-300"
           x-transition:enter-start="opacity-0 transform scale-95"
           x-transition:enter-end="opacity-100 transform scale-100"
           x-transition:leave="ease-in duration-200"
           x-transition:leave-start="opacity-100 transform scale-100"
           x-transition:leave-end="opacity-0 transform scale-95"
           @click.away="$wire.closeModal()">

           <div class="relative bg-white rounded-lg shadow-xl overflow-hidden w-full flex flex-col max-h-[90vh]" @click.stop>
               <!-- Header fixo (sticky) -->
               <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-3 py-3 sm:px-6 sm:py-4 flex justify-between items-center shadow-lg sticky top-0 z-10">
                   <!-- Título e botão de fechar -->
               </div>
               
               <!-- Conteúdo com scroll -->
               <div class="bg-white p-3 sm:p-6 overflow-y-auto flex-grow">
                   <!-- Conteúdo do modal -->
               </div>
               
               <!-- Footer fixo com ações -->
               <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-3 py-3 sm:px-6 flex flex-col sm:flex-row justify-between items-center gap-2 shadow-inner border-t border-gray-200 sticky bottom-0 z-10">
                   <!-- Botões de ação -->
               </div>
           </div>
       </div>
   </div>
   ```

5. **Cards de Informação com Animações**
   - Layout em grid com responsividade: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4`
   - Animações sutis de escala: `transform hover:scale-[1.02]`
   - Sombras que aumentam no hover: `hover:shadow-md`
   - Ícones contextuais coloridos para diferentes tipos de informação
   - Progressbarars animadas para indicar percentuais

   ```html
   <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 animate__animated animate__fadeIn">
       <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-all duration-300 transform hover:scale-[1.02]">
           <span class="text-sm font-medium text-gray-500 flex items-center">
               <i class="fas fa-box-open text-green-500 mr-2"></i> {{ __('messages.product') }}:
           </span>
           <p class="text-sm text-gray-900 mt-1 font-semibold">{{ $viewingSchedule->product->name }}</p>
           <p class="text-xs text-gray-500 italic">{{ $viewingSchedule->product->sku }}</p>
       </div>
       <!-- Mais cards com diferentes indicadores -->
   </div>
   ```

### Recursos de UI/UX Avançados

1. **Visualizações Alternativas de Dados**
   - Troca dinâmica entre visualização de tabela e calendário
   - Sistema de abas com interfaces distintas: `currentTab === 'list' || 'calendar'`
   - Persistência do estado de visualização entre sessões
   - Animações de transição durante a troca de visualizações

2. **Calendário Interativo para Agendamento**
   - Visual por dia, semana ou mês com navegação intuitiva
   - Indicadores visuais de eventos agendados com cores distintas por status
   - Interatividade: clique para visualizar/editar agendamentos existentes
   - Suporte para adicionar novos agendamentos diretamente no calendário

3. **Indicadores Visuais de Status**
   - Badges coloridos com ícones animados para status diferentes
   - Cores contextuais: azul (confirmado), amarelo (em progresso), verde (completo), vermelho (cancelado)
   - Ícones com animações para status ativos: `animate__pulse animate__infinite`

   ```html
   <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full animate__animated animate__fadeIn
       @if($status == 'draft') bg-gray-100 text-gray-800
       @elseif($status == 'confirmed') bg-blue-100 text-blue-800
       @elseif($status == 'in_progress') bg-yellow-100 text-yellow-800
       @elseif($status == 'completed') bg-green-100 text-green-800
       @elseif($status == 'cancelled') bg-red-100 text-red-800
       @endif">
       <i class="mr-1 fas 
           @if($status == 'draft') fa-pencil-alt
           @elseif($status == 'confirmed') fa-check
           @elseif($status == 'in_progress') fa-spinner fa-spin
           @elseif($status == 'completed') fa-check-double
           @elseif($status == 'cancelled') fa-ban
           @endif"></i>
       {{ __('messages.status_' . $status) }}
   </span>
   ```

4. **Alertas Interativos com Detalhes Expansíveis**
   - Alertas contextuais para avisos e erros com animações de entrada: `animate__fadeIn`
   - Seções de detalhes expansíveis usando Alpine.js: `x-data="{showDetails: false}"`
   - Ícones pulsantes para alertas importantes: `animate__pulse animate__infinite`
   - Ações contextuais dentro dos alertas

   ```html
   <div class="p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-md shadow-md animate__animated animate__fadeIn">
       <div class="flex items-start">
           <div class="flex-shrink-0 pt-0.5">
               <i class="fas fa-exclamation-triangle text-yellow-500 text-xl animate__animated animate__pulse animate__infinite"></i>
           </div>
           <div class="ml-3 flex-1">
               <p class="text-sm font-medium text-yellow-800">{{ __('messages.warning_message') }}</p>
               
               <div x-data="{showDetails: false}" class="mt-2">
                   <button @click="showDetails = !showDetails" type="button" 
                       class="text-xs px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded-md font-medium flex items-center transition-all duration-200 hover:scale-105">
                       <i class="fas" :class="showDetails ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                       <span class="ml-1">{{ __('messages.view_details') }}</span>
                   </button>
                   
                   <div x-show="showDetails" x-transition:enter="transition ease-out duration-300" 
                       x-transition:enter-start="opacity-0 transform -translate-y-4" 
                       x-transition:enter-end="opacity-100 transform translate-y-0"
                       class="mt-3 text-xs text-gray-700 bg-white p-3 rounded-md border border-gray-200 shadow-sm">
                       <!-- Conteúdo detalhado expansível -->
                   </div>
               </div>
           </div>
       </div>
   </div>
   ```

### Sistema de Notificações Toastr Aprimorado

1. **Implementação com Named Parameters (PHP 8+)**

Usando o novo padrão com named parameters para maior clareza e legibilidade:

```php
$this->dispatch('notify', 
    type: 'success', // success, warning, error, info
    title: __('messages.schedule_created_title'),
    message: __('messages.schedule_created_message')
);
```

2. **Tipos de Notificações e Usos Específicos**

   - **Success**: Para operações bem-sucedidas (criação, atualização)
     ```php
     $this->dispatch('notify', 
         type: 'success',
         title: $this->editMode 
             ? __('messages.schedule_updated_title') 
             : __('messages.schedule_created_title'),
         message: $this->editMode 
             ? __('messages.schedule_updated_message') 
             : __('messages.schedule_created_message')
     );
     ```

   - **Error**: Para erros e exclusões
     ```php
     $this->dispatch('notify', 
         type: 'error',
         title: __('messages.schedule_deleted_title'),
         message: __('messages.schedule_deleted_message')
     );
     ```

   - **Warning**: Para avisos e alertas
     ```php
     $this->dispatch('notify', 
         type: 'warning',
         title: __('messages.insufficient_components_title'),
         message: __('messages.insufficient_components_warning')
     );
     ```

   - **Info**: Para informações e lembretes
     ```php
     $this->dispatch('notify', 
         type: 'info',
         title: __('messages.item_quantity_updated'),
         message: __('messages.review_bom_components')
     );
     ```

3. **Customizações Visuais do Toastr**

   - Posicionamento personalizado (canto superior direito)
    - Duração ajustável (5 segundos padrão)
    - Animações de entrada e saída

## Sistema de Rastreamento de Falhas na Produção

### Visão Geral
O sistema de rastreamento de falhas foi integrado aos planos diários de produção, permitindo o registro detalhado de paradas e falhas durante a execução de tarefas de produção. Esta funcionalidade permite:

- Identificar quando ocorrem paradas (breakdowns) na produção
- Registrar a duração exata das paradas em minutos
- Categorizar as falhas usando um sistema padronizado
- Associar múltiplas causas raiz para análises mais profundas

### Estrutura de Dados

#### Tabelas e Modelos

1. **Planos Diários de Produção** (`mrp_production_daily_plans`):
   - `has_breakdown`: Booleano indicando se houve parada
   - `breakdown_minutes`: Inteiro com a duração da parada em minutos
   - `failure_category_id`: Chave estrangeira para categoria de falha
   - `failure_root_causes`: Campo JSON para armazenar múltiplas causas raiz

2. **Categorias de Falha** (`mrp_failure_categories`):
   - Mantidas no namespace `App\Models\Mrp`
   - Propriedades principais: `name`, `description`, `color`, `is_active`

3. **Causas Raiz de Falha** (`mrp_failure_root_causes`):
   - Mantidas no namespace `App\Models\Mrp`
   - Cada causa raiz é associada a uma categoria de falha

> **Importante**: Observe que as tabelas seguem o padrão de nomenclatura do sistema onde as tabelas do módulo MRP não usam prefixo específico, ao contrário das tabelas de Supply Chain que usam o prefixo 'sc_'.

#### Modelo de Dados e Relacionamentos

O modelo `ProductionDailyPlan` foi atualizado para incluir os seguintes relacionamentos:

```php
// Relacionamento com categoria de falha (many-to-one)
public function failureCategory()
{
    return $this->belongsTo(FailureCategory::class, 'failure_category_id');
}

// Método para acessar as causas raiz como objetos
public function getFailureRootCausesObjectsAttribute()
{
    if (!$this->failure_root_causes) {
        return collect([]);
    }
    
    return FailureRootCause::whereIn('id', $this->failure_root_causes)
        ->orderBy('name')
        ->get();
}
```

### Interface do Usuário

#### Modal de Planos Diários

O modal de planos diários foi aprimorado com componentes para rastreamento de falhas seguindo os padrões de UI/UX existentes:

1. **Indicador de Parada**:
   - Checkbox para marcar ocorrência de parada
   - Layout responsivo com classes `flex items-center space-x-2`

2. **Campos de Falha Condicionais**:
   - Usando Alpine.js para exibição condicional
   - Duração em minutos com validação numérica
   - Exibidos apenas quando `has_breakdown` está ativo

3. **Categorização de Falhas**:
   - Dropdown para seleção da categoria principal
   - Design consistente com outros componentes do sistema
   - Destaque visual usando as cores definidas para cada categoria

4. **Seleção de Causas Raiz**:
   - Campo de seleção múltipla para escolha de causas raiz
   - Filtragem automática baseada na categoria selecionada
   - Instruções claras para o usuário

#### Exemplo de Implementação

```html
<!-- Campo para indicar parada -->
<div class="flex items-center space-x-2">
    <label class="inline-flex items-center cursor-pointer">
        <input type="checkbox" wire:model.defer="dailyPlans.{{ $index }}.has_breakdown" 
            class="form-checkbox h-5 w-5 text-red-600 transition duration-150 ease-in-out rounded">
        <span class="ml-2 text-sm text-gray-700">{{ __('messages.yes') }}</span>
    </label>
</div>

<!-- Campos para detalhes da falha (exibidos condicionalmente) -->
<div x-show.transition.opacity="$wire.dailyPlans && $wire.dailyPlans[{{ $index }}] && $wire.dailyPlans[{{ $index }}].has_breakdown">
    <!-- Campo para duração da parada -->
    <div class="relative rounded-md shadow-sm mb-2">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-clock text-gray-400"></i>
        </div>
        <input type="number" wire:model.defer="dailyPlans.{{ $index }}.breakdown_minutes" 
            class="pl-9 block w-full py-2 text-sm border-gray-300 focus:ring-red-500 focus:border-red-500 rounded-md" 
            placeholder="{{ __('messages.minutes') }}">
    </div>
    
    <!-- Seleção de categoria de falha -->
    <div class="relative rounded-md shadow-sm mb-2">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-tag text-orange-400"></i>
        </div>
        <select wire:model.defer="dailyPlans.{{ $index }}.failure_category_id" 
            class="pl-9 block w-full py-2 text-sm border-gray-300 focus:ring-orange-500 focus:border-orange-500 rounded-md">
            <option value="">{{ __('messages.select_failure_category') }}</option>
            @foreach($failureCategories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    
    <!-- Seleção múltipla de causas raiz -->
    <div class="relative rounded-md shadow-sm">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-list text-blue-400"></i>
        </div>
        <select wire:model.defer="dailyPlans.{{ $index }}.failure_root_causes" 
            class="pl-9 block w-full py-2 text-sm border-gray-300 focus:ring-blue-500 focus:border-blue-500 rounded-md"
            multiple size="2">
            @foreach($failureRootCauses as $rootCause)
                <option value="{{ $rootCause->id }}">{{ $rootCause->name }}</option>
            @endforeach
        </select>
        <div class="text-xs text-gray-500 mt-1 italic">{{ __('messages.select_multiple') }}</div>
    </div>
</div>
```

### Implementação no Controller

O controller `ProductionScheduling` foi atualizado para gerenciar o rastreamento de falhas:

1. **Carregamento de Dados para a View**:
   ```php
   // No método render()
   $failureCategories = FailureCategory::where('is_active', true)
       ->orderBy('name')
       ->get();
       
   $failureRootCauses = FailureRootCause::where('is_active', true)
       ->orderBy('name')
       ->get();
   ```

2. **Validação para Campos de Falha**:
   ```php
   // No método updateDailyPlan()
   $this->validateOnly("dailyPlans.{$index}", [
       "dailyPlans.{$index}.planned_quantity" => 'required|numeric|min:0',
       // ... outros campos ...
       "dailyPlans.{$index}.breakdown_minutes" => 'nullable|numeric|min:0',
       "dailyPlans.{$index}.failure_category_id" => 'nullable|exists:mrp_failure_categories,id',
       "dailyPlans.{$index}.failure_root_causes" => 'nullable|array',
   ]);
   ```

3. **Lógica de Salvamento Condicional**:
   ```php
   // Campos de breakdown e falhas
   $plan->has_breakdown = $this->dailyPlans[$index]['has_breakdown'] ?? false;
   
   // Só atualizar esses campos se houver breakdown
   if ($plan->has_breakdown) {
       $plan->breakdown_minutes = $this->dailyPlans[$index]['breakdown_minutes'] ?? null;
       $plan->failure_category_id = $this->dailyPlans[$index]['failure_category_id'] ?? null;
       $plan->failure_root_causes = $this->dailyPlans[$index]['failure_root_causes'] ?? null;
   } else {
       // Limpar os campos de falha se não houver breakdown
       $plan->breakdown_minutes = null;
       $plan->failure_category_id = null;
       $plan->failure_root_causes = null;
   }
   ```

4. **Notificações ao Usuário**:
   Seguindo o padrão estabelecido para notificações do sistema:
   ```php
   $this->dispatch('notify',
       type: 'success',
       title: __('messages.success'),
       message: __('messages.daily_plan_updated')
   );
   ```

### Análise e Relatórios

As informações de falhas podem ser utilizadas para:

1. **Cálculo de Indicadores**:
   - OEE (Overall Equipment Effectiveness)
   - MTBF (Mean Time Between Failures)
   - MTTR (Mean Time To Repair)

2. **Análise de Pareto**:
   - Identificação das causas mais frequentes de falhas
   - Priorização de ações corretivas

3. **Integração com Manutenção**:
   - Possibilidade de integração com módulos de manutenção preventiva
   - Alertas automatizados baseados em padrões de falha

### Boas Práticas

1. **Confiabilidade dos Dados**:
   - Mantenha categorias e causas raiz bem definidas
   - Estabeleça processos claros para registro de paradas
   - Treine os operadores para uso consistente do sistema

2. **Revisão Periódica das Categorias**:
   - Avalie periodicamente se as categorias e causas raiz cobrem todos os cenários
   - Evite criar categorias muito genéricas ou excessivamente específicas

3. **Visualização de Dados**:
   - Implemente gráficos para visualização de tendências
   - Use cores consistentes para categorias em toda a interface

### Futuras Melhorias

1. **Integração com Equipamentos**:
   - Associação direta entre falhas e equipamentos específicos
   - Histórico de falhas por equipamento

2. **Dashboard de Falhas**:
   - Visão consolidada das principais falhas por período
   - Métricas de impacto na produção

3. **Ações Corretivas**:
   - Registro e acompanhamento de ações corretivas
   - Vinculação com sistema de tickets
   - Opção de fechar manualmente
   - Ícones contextuais por tipo de notificação

4. **Implementação JavaScript para Dispatch em Livewire**

{{ ... }}
document.addEventListener('livewire:init', () => {
    Livewire.on('notify', (params) => {
        toastr[params.type](params.message, params.title, {
            positionClass: "toast-top-right",
            timeOut: 5000,
            closeButton: true,
            newestOnTop: true,
            progressBar: true,
            preventDuplicates: true,
            showMethod: "fadeIn",
            hideMethod: "fadeOut"
        });
    });
});
```

### Recursos de UX Avançados para Forms

1. **Verificação Inteligente de Disponibilidade de Componentes**
   - Verificação automática de disponibilidade de materiais ao editar quantidade
   - Alertas visuais com detalhes expandíveis sobre componentes insuficientes
   - Cálculo da quantidade máxima possível baseada no inventário disponível
   - Sistema não bloqueante: permite continuar apesar dos avisos

2. **Validação em Tempo Real**
   - Feedback imediato de validação usando `wire:model.live`
   - Indicadores visuais de campos válidos/inválidos
   - Mensagens de erro contextuais
   - Desabilitação condicional do botão de envio

3. **Campos de Input Aprimorados**
   - Prefixos visuais com ícones: `<div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">`
   - Formatação automática de datas e números
   - States visuais (focus, error, disabled)
   - Tooltips de ajuda para campos complexos

4. **Estados de Carregamento**
   - Indicadores de carregamento específicos por botão: `wire:loading.attr="disabled"`
   - Texto alternativo durante carregamento: `wire:loading.class.remove="hidden"`
   - Spinners visuais: `<i class="fas fa-spinner fa-spin mr-2" wire:loading wire:target="save"></i>`
   - Desabilitação de interação durante processamento

5. **Animações e Transições**

O sistema utiliza duas bibliotecas principais para animações:

- **Animate.css**: Animações predefinidas como `animate__fadeIn`, `animate__pulse`
   ```html
   <div class="animate__animated animate__fadeIn">Conteúdo animado</div>
   ```

- **Alpine.js Transitions**: Transições customizadas para elementos dinâmicos
   ```html
   <div x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-90"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-90">
        Conteúdo com transição
   </div>
   ```

- **Tailwind Transitions**: Transições nativas do Tailwind
   ```html
   <button class="transition-all duration-200 transform hover:scale-105">Botão com transição</button>
   ```

## Navegação do Sistema

O módulo MRP está integrado ao menu de navegação principal do sistema, posicionado logo após o módulo de Manutenção. A estrutura de navegação é a seguinte:

```
Dashboard
└── ...
Manutenção
└── ...
MRP (Material Requirements Planning)
├── Dashboard
├── Previsão de Demanda
├── Gestão de BOM
├── Níveis de Estoque
├── Programação de Produção
├── Ordens de Produção
├── Planejamento de Compras
├── Planejamento de Capacidade
└── Relatórios Financeiros
```

### Implementação da Navegação

Os links do módulo MRP são definidos no arquivo `resources/views/layouts/navigation.blade.php`, utilizando o componente `x-nav-dropdown` com os seguintes parâmetros:

- **label**: Nome do módulo ou submenu
- **icon**: Ícone FontAwesome para identificação visual
- **active**: Condição para destacar o menu ativo
- **permission**: Permissão necessária para visualizar o menu

Cada submenu é implementado como um componente `x-nav-link` com seus respectivos atributos e rotas.

## Pendências e Próximos Passos
- Implementar testes automatizados para todos os componentes
- Melhorar a documentação para usuários finais
