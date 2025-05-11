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
Todos os componentes do módulo MRP seguem o padrão de UI/UX definido para o sistema ERPDEMBENA, com:
### Estrutura dos arquivos views
primeiro criar a pasta da do componente depois criar as modais dentro da pasta e fora da pasta ou dentro da pasta mrp cria o blade principal de listagem
1. **Cabeçalho Principal**
   - Título com ícone contextual em azul
   - Botão de ação principal com animação

2. **Cartão de Filtros**
   - Cabeçalho com gradiente azul suave
   - Layout responsivo para filtros
   - Campo de busca com ícone

3. **Tabela de Dados**
   - Cabeçalho com gradiente azul vibrante
   - Colunas ordenáveis 
   - Status coloridos com ícones
   - Ações com efeito hover

4. **Modais**
   - Cabeçalhos com cores contextuais
   - Animações de transição
   - Validação em tempo real
   - Botões com estados de carregamento

### Sistema de Notificações
O módulo utiliza o sistema de notificações padronizado:
```php
$this->dispatch('notify', [
    'type' => 'success|warning|error|info',
    'title' => 'Título da notificação',
    'message' => 'Mensagem da notificação'
]);
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
