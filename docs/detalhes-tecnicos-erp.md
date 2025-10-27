# Detalhes Técnicos do Sistema ERP DEMBENA

## Arquitetura e Stack Tecnológico

O ERP DEMBENA é construído sobre uma arquitetura moderna utilizando as seguintes tecnologias:

- **Framework Backend**: Laravel 10.x
- **Frontend/UI**: 
  - Livewire 3.x para componentes dinâmicos
  - Alpine.js para interações JavaScript leves
  - TailwindCSS para estilização
- **Banco de Dados**: MySQL 8.x
- **Bibliotecas Adicionais**:
  - Font Awesome para ícones
  - Flatpickr para seletores de data
  - Chart.js para gráficos
  - PDF.js para geração de PDFs
  - DataTables para tabelas interativas

## Estrutura do Projeto

### Organização dos Diretórios

O projeto segue a estrutura padrão do Laravel com algumas organizações adicionais:

```
ERPDEMBENA/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   ├── Livewire/
│   │   ├── Components/
│   │   ├── Dashboard/
│   │   ├── HR/
│   │   ├── Maintenance/
│   │   ├── MRP/
│   │   ├── Settings/
│   │   ├── Stocks/
│   │   ├── SupplyChain/
│   ├── Models/
│   │   ├── HR/
│   │   ├── Maintenance/
│   │   ├── MRP/
│   │   ├── SupplyChain/
│   ├── Services/
│   ├── Traits/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
├── public/
│   ├── css/
│   ├── js/
│   ├── images/
├── resources/
│   ├── js/
│   ├── lang/
│   │   ├── en/
│   │   ├── pt/
│   ├── views/
│   │   ├── components/
│   │   ├── layouts/
│   │   ├── livewire/
│   │   │   ├── hr/
│   │   │   ├── maintenance/
│   │   │   ├── stocks/
│   │   │   ├── supply-chain/
├── routes/
```

### Organização por Módulos

Cada módulo é organizado seguindo uma estrutura similar:

- **Modelos** em `app/Models/[ModuleName]/`
- **Componentes Livewire** em `app/Livewire/[ModuleName]/`
- **Views** em `resources/views/livewire/[module-name]/`
- **Traduções** em `resources/lang/[language]/[module-name].php`

## Interface do Usuário (UI/UX)

### Sistema de Design

O ERP DEMBENA utiliza um sistema de design consistente em todo o aplicativo, garantindo uma experiência coesa para o usuário:

#### Paleta de Cores
- **Primária**: Tons de azul (#3B82F6, #1D4ED8)
- **Secundária**: Tons de cinza (#F3F4F6, #E5E7EB, #D1D5DB)
- **Acentos**:
  - Sucesso: Verde (#10B981)
  - Alerta: Amarelo (#F59E0B)
  - Erro: Vermelho (#EF4444)
  - Informação: Azul claro (#60A5FA)

#### Tipografia
- **Fonte Principal**: Inter (sans-serif)
- **Tamanhos de Fonte**:
  - Títulos: 1.5rem, 1.25rem, 1.125rem
  - Corpo: 1rem, 0.875rem
  - Pequeno: 0.75rem

#### Componentes UI Reutilizáveis

O sistema utiliza um conjunto de componentes reutilizáveis para manter a consistência:

1. **Botões**:
   - Primário: Fundo azul, texto branco
   - Secundário: Borda cinza, texto cinza escuro
   - Terciário: Apenas texto, sem fundo ou borda
   - De perigo: Fundo vermelho, texto branco

2. **Cards**:
   - Cabeçalho com fundo gradiente
   - Corpo com padding consistente
   - Opções de sombra para diferentes níveis de elevação

3. **Formulários**:
   - Inputs com estilo consistente
   - Labels com posicionamento padrão
   - Mensagens de erro com cor vermelha e ícone

4. **Tabelas**:
   - Cabeçalhos com fundo cinza claro
   - Linhas alternadas para melhor legibilidade
   - Estados de hover para indicar interatividade

### Modais

O sistema utiliza modais para várias interações, garantindo que o usuário permaneça no contexto atual. Implementação utilizando combinação de Alpine.js e Livewire:

#### Tipos de Modais:

1. **Modais de Formulário**: Para adicionar ou editar registros
   ```html
   <div x-data="{ open: @entangle('showModal') }" 
        x-show="open" 
        x-cloak 
        class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50">
       <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
           <div class="relative bg-white rounded-lg shadow-xl">
               <!-- Cabeçalho do Modal -->
               <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-t-lg px-4 py-3">
                   <h3 class="text-lg font-medium text-white">{{ $modalTitle }}</h3>
               </div>
               
               <!-- Corpo do Modal -->
               <div class="p-6">
                   <!-- Conteúdo do formulário -->
               </div>
               
               <!-- Rodapé do Modal -->
               <div class="bg-gray-50 px-4 py-3 rounded-b-lg flex justify-end space-x-3">
                   <button wire:click="closeModal" class="btn-secondary">Cancelar</button>
                   <button wire:click="save" class="btn-primary">Salvar</button>
               </div>
           </div>
       </div>
   </div>
   ```

2. **Modais de Confirmação**: Para confirmar ações potencialmente destrutivas
   ```html
   <div x-data="{ open: @entangle('showConfirmationModal') }" 
        x-show="open" 
        x-cloak 
        class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50">
       <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
           <h3 class="text-lg font-medium text-red-600 mb-4">Confirmar Exclusão</h3>
           <p class="text-gray-500 mb-4">Tem certeza que deseja excluir este item? Esta ação não pode ser desfeita.</p>
           <div class="flex justify-end space-x-3">
               <button wire:click="cancelDelete" class="btn-secondary">Cancelar</button>
               <button wire:click="confirmDelete" class="btn-danger">Excluir</button>
           </div>
       </div>
   </div>
   ```

3. **Modais de Detalhes**: Para visualizar informações detalhadas
   ```html
   <div x-data="{ open: @entangle('showDetailsModal') }" 
        x-show="open" 
        x-cloak 
        class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50">
       <!-- Conteúdo similar ao modal de formulário, mas sem botão de salvar -->
   </div>
   ```

4. **Modais de Busca**: Para pesquisar e selecionar itens
   ```html
   <div x-data="{ open: @entangle('showSearchModal') }" 
        x-show="open" 
        x-cloak 
        class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50">
       <div class="relative top-20 mx-auto p-1 w-full max-w-4xl">
           <!-- Estrutura similar, com campo de busca e lista de resultados -->
       </div>
   </div>
   ```

#### Transições e Animações:

Todos os modais utilizam transições suaves para melhor experiência do usuário:

```html
x-transition:enter="transition ease-out duration-300" 
x-transition:enter-start="opacity-0" 
x-transition:enter-end="opacity-100" 
x-transition:leave="transition ease-in duration-200" 
x-transition:leave-start="opacity-100" 
x-transition:leave-end="opacity-0"
```

### Sistema de Notificações

O sistema utiliza Toastr para notificações toast, com tipos diferentes para diferentes situações:

1. **Notificação de Sucesso**:
   ```javascript
   toastr.success('Registro salvo com sucesso!', 'Sucesso');
   ```

2. **Notificação de Erro**:
   ```javascript
   toastr.error('Não foi possível completar a operação.', 'Erro');
   ```

3. **Notificação de Aviso**:
   ```javascript
   toastr.warning('Esta ação pode causar problemas.', 'Atenção');
   ```

4. **Notificação Informativa**:
   ```javascript
   toastr.info('A operação está em andamento.', 'Informação');
   ```

#### Implementação em Livewire:

As notificações são disparadas a partir de eventos do Livewire:

```php
// No componente Livewire
$this->dispatch('notify', [
    'type' => 'success',
    'message' => __('messages.record_saved')
]);
```

```javascript
// No JavaScript
document.addEventListener("livewire:initialized", () => {
    @this.on('notify', (data) => {
        toastr[data.type](data.message, data.type === 'error' ? 'Erro' : 
            (data.type === 'success' ? 'Sucesso' : 
            (data.type === 'warning' ? 'Atenção' : 'Informação')));
    });
});
```

## Padrões e Lógicas Importantes

### Validação de Dados

A validação é realizada em dois níveis:

1. **Frontend** utilizando validação em tempo real com Livewire:
   ```php
   protected function rules()
   {
       return [
           'product.name' => 'required|string|max:255',
           'product.sku' => 'required|string|max:50|unique:products,sku,' . ($this->product->id ?? ''),
           'product.price' => 'required|numeric|min:0',
       ];
   }
   ```

2. **Backend** com validadores do Laravel para garantir integridade:
   ```php
   public function store(Request $request)
   {
       $validated = $request->validate([
           'name' => 'required|string|max:255',
           'sku' => 'required|string|max:50|unique:products',
           'price' => 'required|numeric|min:0',
       ]);
       
       // Processo de salvamento
   }
   ```

### Padrão de Repositório

Para operações complexas de banco de dados, o sistema utiliza o padrão de repositório:

```php
// Interface
interface ProductRepositoryInterface
{
    public function all();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}

// Implementação
class ProductRepository implements ProductRepositoryInterface
{
    public function all()
    {
        return Product::all();
    }
    
    public function find($id)
    {
        return Product::findOrFail($id);
    }
    
    // Outros métodos
}
```

### Políticas de Autorização

O controle de acesso é gerenciado através de políticas do Laravel:

```php
class ProductPolicy
{
    public function viewAny(User $user)
    {
        return $user->can('product.view');
    }
    
    public function view(User $user, Product $product)
    {
        return $user->can('product.view');
    }
    
    public function create(User $user)
    {
        return $user->can('product.create');
    }
    
    // Outros métodos
}
```

### Sistema de Internacionalização

O sistema suporta múltiplos idiomas através do sistema de tradução do Laravel:

```php
// Em um arquivo de tradução (resources/lang/pt/messages.php)
return [
    'welcome' => 'Bem-vindo ao ERP DEMBENA',
    'product_saved' => 'Produto salvo com sucesso!',
    'product_deleted' => 'Produto excluído com sucesso!',
];

// Uso
echo __('messages.welcome');
```

### Filtros e Pesquisa

Os componentes de listagem incluem filtros avançados e pesquisa em tempo real:

```php
// No componente Livewire
public $search = '';
public $perPage = 10;
public $sortField = 'created_at';
public $sortDirection = 'desc';
public $filters = [
    'category' => '',
    'status' => '',
    'date_range' => [],
];

public function render()
{
    $query = Product::query();
    
    // Aplicar pesquisa
    if ($this->search) {
        $query->where(function($q) {
            $q->where('name', 'like', '%' . $this->search . '%')
              ->orWhere('sku', 'like', '%' . $this->search . '%');
        });
    }
    
    // Aplicar filtros
    if ($this->filters['category']) {
        $query->where('category_id', $this->filters['category']);
    }
    
    if ($this->filters['status']) {
        $query->where('status', $this->filters['status']);
    }
    
    // Aplicar ordenação
    $query->orderBy($this->sortField, $this->sortDirection);
    
    // Paginar resultados
    $products = $query->paginate($this->perPage);
    
    return view('livewire.products', [
        'products' => $products,
    ]);
}
```

## Funcionalidades Específicas

### Gestão de Estoque (Stocks)

#### Entrada de Estoque (Stock In)

Processo para registrar entrada de novas peças no estoque:

1. **Seleção de Peça**: Modal dedicada para busca e seleção de peças
   - Busca por nome, número da peça ou código de barras
   - Visualização de detalhes da peça (estoque atual, preço unitário)

2. **Detalhes da Entrada**:
   - Quantidade
   - Data
   - Número de referência
   - Razão da entrada (compra, devolução, ajuste)
   - Notas adicionais

3. **Processamento**:
   - Validação de dados
   - Atualização do estoque da peça
   - Registro da transação no histórico
   - Notificação de sucesso

#### Saída de Estoque (Stock Out)

Processo similar ao Stock In, mas para registrar saídas:

1. **Seleção de Peça**: Modal de busca com indicação de estoque disponível
2. **Detalhes da Saída**:
   - Quantidade (com validação para não exceder o estoque disponível)
   - Data
   - Razão da saída (uso em manutenção, venda, dano)
   - Notas

### Cadeia de Suprimentos (Supply Chain)

#### Transferência de Estoque

Processo para mover produtos entre localizações:

1. **Filtros Avançados**:
   - Busca por nome ou SKU
   - Filtro por tipo de produto
   - Opção para mostrar apenas produtos com estoque

2. **Seleção de Produtos**:
   - Visualização clara de níveis de estoque
   - Indicadores visuais (verde para estoque normal, amarelo para baixo, vermelho para esgotado)
   - Informações de produto (SKU, tipo, quantidade disponível)

3. **Origem e Destino**:
   - Seleção de localização de origem
   - Seleção de localização de destino
   - Quantidades a transferir por produto

4. **Processamento**:
   - Validação de quantidades
   - Atualização dos registros de inventário em ambas as localizações
   - Registro da transferência no histórico

#### Ajuste de Estoque

Permite corrigir discrepâncias no estoque:

1. **Tipos de Ajuste**:
   - Adicionar estoque
   - Remover estoque
   - Definir estoque para um valor específico

2. **Detalhes do Ajuste**:
   - Produtos e quantidades
   - Razão do ajuste (contagem física, dano, perda)
   - Documentação (notas, referências)

### Integração entre Módulos

#### Manutenção e Estoque

Quando uma manutenção é registrada, peças podem ser automaticamente retiradas do estoque:

1. O técnico seleciona as peças necessárias no registro de manutenção
2. O sistema verifica a disponibilidade no estoque
3. Ao concluir a manutenção, o sistema:
   - Gera uma saída de estoque (Stock Out)
   - Atualiza o inventário
   - Associa as peças à manutenção para rastreabilidade

## Considerações de Performance

### Lazy Loading

Para melhorar a performance, o sistema utiliza lazy loading para carregar dados apenas quando necessário:

```php
// No componente Livewire
public function loadProducts()
{
    $this->products = Product::where('category_id', $this->selectedCategory)
        ->take(20)
        ->get();
}
```

### Eager Loading

Para evitar o problema N+1 em relações, o sistema utiliza eager loading:

```php
// Carregamento eficiente de relações
$orders = Order::with(['customer', 'items.product', 'paymentMethod'])
    ->orderBy('created_at', 'desc')
    ->paginate(10);
```

### Paginação com Livewire

Para lidar com grandes conjuntos de dados:

```php
use Livewire\WithPagination;

class ProductsList extends Component
{
    use WithPagination;
    
    protected $paginationTheme = 'tailwind';
    
    public function render()
    {
        return view('livewire.products-list', [
            'products' => Product::paginate(10)
        ]);
    }
}
```

## Segurança

### CSRF Protection

Todas as requisições POST incluem tokens CSRF para prevenir ataques:

```html
@csrf
```

### Validação de Entrada

Todos os inputs são validados tanto no frontend quanto no backend:

```php
// Validação no componente Livewire
protected $rules = [
    'user.name' => 'required|min:3|max:255',
    'user.email' => 'required|email|unique:users,email',
    'user.password' => 'required|min:8',
];
```

### Sanitização de Saída

Para prevenir XSS, todas as saídas são escapadas por padrão:

```html
{{ $variable }} <!-- Escapado automaticamente -->
{!! $htmlContent !!} <!-- Não escapado, usar com cuidado -->
```

## Conclusão

Esta documentação técnica detalha os aspectos mais importantes da implementação do ERP DEMBENA, incluindo a arquitetura, estrutura, padrões de UI/UX, modais, notificações e lógicas específicas de cada módulo. Ela serve como referência para desenvolvedores que trabalham no projeto, garantindo consistência e aderência aos padrões estabelecidos.
