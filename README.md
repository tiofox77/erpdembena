<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[WebReinvent](https://webreinvent.com/)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Jump24](https://jump24.co.uk)**
-   **[Redberry](https://redberry.international/laravel/)**
-   **[Active Logic](https://activelogic.com)**
-   **[byte5](https://byte5.de)**
-   **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Livewire v3 CRUD com Modal - Guia de Implementação

Este guia explica como implementar operações CRUD usando Livewire v3 com modais, sem depender de bibliotecas como Alpine.js. O padrão demonstrado aqui pode ser reutilizado em diversos componentes da aplicação.

## Estrutura do CRUD

### Componente Livewire

Para criar um componente CRUD completo com modal:

```php
<?php

namespace App\Livewire;

use App\Models\SeuModel;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Log;

class SeuComponente extends Component
{
    use WithPagination;

    // Propriedades para o formulário
    public $showModal = false;
    public $itemId = null;

    #[Validate('required|min:3')]
    public $titulo = '';

    #[Validate('nullable')]
    public $descricao = '';

    // Propriedades para listagem/filtro
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;

    // Listener para tecla ESC
    protected function getListeners()
    {
        return [
            'escape-pressed' => 'closeModal'
        ];
    }

    // Métodos para controle da modal
    public function openModal()
    {
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function createItem()
    {
        $this->resetForm();
        $this->openModal();
    }

    public function editItem($id)
    {
        $item = SeuModel::findOrFail($id);
        $this->itemId = $item->id;
        $this->titulo = $item->titulo;
        $this->descricao = $item->descricao;
        $this->openModal();
    }

    public function resetForm()
    {
        $this->reset(['itemId', 'titulo', 'descricao']);
        $this->resetValidation();
    }

    // Método para salvar os dados
    public function save()
    {
        $this->validate();

        try {
            if ($this->itemId) {
                $item = SeuModel::findOrFail($this->itemId);
                $item->update([
                    'titulo' => $this->titulo,
                    'descricao' => $this->descricao
                ]);
                $message = 'Item atualizado com sucesso.';
            } else {
                SeuModel::create([
                    'titulo' => $this->titulo,
                    'descricao' => $this->descricao
                ]);
                $message = 'Item criado com sucesso.';
            }

            $this->closeModal();
            $this->dispatch('notify', type: 'success', message: $message);
        } catch (\Exception $e) {
            Log::error('Erro ao salvar: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Ocorreu um erro ao salvar o item.');
        }
    }

    public function delete($id)
    {
        try {
            $item = SeuModel::findOrFail($id);
            $item->delete();

            $this->dispatch('notify', type: 'success', message: 'Item excluído com sucesso.');
        } catch (\Exception $e) {
            $this->dispatch('notify', type: 'error', message: 'Ocorreu um erro ao excluir o item.');
        }
    }

    public function render()
    {
        $items = SeuModel::query()
            ->when($this->search, function ($query) {
                $query->where('titulo', 'like', '%' . $this->search . '%')
                    ->orWhere('descricao', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.seu-componente', [
            'items' => $items
        ]);
    }

    // Adicione este método para depuração
    public function testAreaModal()
    {
        // Forçar abertura do modal e registro do estado
        $this->isAreaModalOpen = true;
        $this->js("console.log('Modal state: " . ($this->isAreaModalOpen ? 'true' : 'false') . "')");
    }
}
```

### View Blade

A estrutura da view blade para o CRUD com modal:

```blade
<div>
    <!-- Parte principal - listagem -->
    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- Título e botão "Adicionar" -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-semibold">Gerenciamento de Itens</h1>
                    <button
                        wire:click="createItem"
                        type="button"
                        class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-medium py-2 px-4 rounded flex items-center"
                    >
                        <svg class="w-5 h-5 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Adicionar Item
                    </button>
                </div>

                <!-- Campo de busca -->
                <div class="mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input wire:model.live="search" type="text" placeholder="Buscar itens..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white">
                    </div>
                </div>

                <!-- Tabela de registros -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <!-- Cabeçalho da tabela -->
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Título
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Descrição
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Criado em
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <!-- Corpo da tabela -->
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $item->titulo }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ $item->descricao }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $item->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button wire:click="editItem({{ $item->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="delete({{ $item->id }})" wire:confirm="Tem certeza que deseja excluir este item?" class="text-red-600 hover:text-red-900">
                                            <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                        Nenhum item encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginação -->
                <div class="mt-4">
                    {{ $items->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>

        <!-- Modal content -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <!-- Botão fechar -->
                <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                    <button
                        type="button"
                        class="rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none"
                        wire:click="closeModal"
                    >
                        <span class="sr-only">Fechar</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal header e form -->
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-xl font-semibold leading-6 text-gray-900">
                                {{ $itemId ? 'Editar Item' : 'Criar Novo Item' }}
                            </h3>
                            <div class="mt-4">
                                <form wire:submit="save">
                                    <div class="space-y-4">
                                        <!-- Campo Título -->
                                        <div>
                                            <label for="titulo" class="block text-sm font-medium text-gray-700">Título <span class="text-red-500">*</span></label>
                                            <input
                                                type="text"
                                                id="titulo"
                                                wire:model="titulo"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Digite o título"
                                            >
                                            @error('titulo') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                        </div>

                                        <!-- Campo Descrição -->
                                        <div>
                                            <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição</label>
                                            <textarea
                                                id="descricao"
                                                wire:model="descricao"
                                                rows="4"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Digite a descrição"
                                            ></textarea>
                                        </div>
                                    </div>

                                    <!-- Rodapé do modal -->
                                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                        <button
                                            type="submit"
                                            class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50 cursor-not-allowed"
                                        >
                                            <span wire:loading.remove wire:target="save">{{ $itemId ? 'Atualizar' : 'Criar' }}</span>
                                            <span wire:loading wire:target="save">
                                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Processando...
                                            </span>
                                        </button>
                                        <button
                                            type="button"
                                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                            wire:click="closeModal"
                                        >
                                            Cancelar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- JavaScript para notificações e tecla ESC -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Tratamento de notificações
            Livewire.on('notify', (params) => {
                const { type, message } = params;

                const notificationElement = document.createElement('div');
                notificationElement.className = `fixed top-4 right-4 z-50 p-4 rounded-md ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white max-w-xs shadow-lg transition-opacity duration-500`;
                notificationElement.innerHTML = message;
                document.body.appendChild(notificationElement);

                // Remover notificação após 3 segundos
                setTimeout(() => {
                    notificationElement.style.opacity = '0';
                    setTimeout(() => {
                        document.body.removeChild(notificationElement);
                    }, 500);
                }, 3000);
            });

            // Fechar modal com tecla ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    Livewire.dispatch('escape-pressed');
                }
            });
        });
    </script>
</div>
```

## Funcionamento

1. **Fluxo de Criação**:

    - Usuário clica em "Adicionar Item"
    - Método `createItem()` é chamado, que:
        - Limpa o formulário com `resetForm()`
        - Abre a modal com `openModal()`
    - Usuário preenche o formulário e submete
    - Método `save()` é chamado, que:
        - Valida os dados
        - Cria o registro no banco
        - Fecha a modal
        - Exibe notificação de sucesso

2. **Fluxo de Edição**:

    - Usuário clica no botão de edição
    - Método `editItem($id)` é chamado, que:
        - Carrega os dados do registro
        - Preenche o formulário
        - Abre a modal
    - Usuário edita o formulário e submete
    - Método `save()` é chamado, que:
        - Valida os dados
        - Atualiza o registro no banco
        - Fecha a modal
        - Exibe notificação de sucesso

3. **Fluxo de Exclusão**:
    - Usuário clica no botão de exclusão
    - Modal de confirmação nativa do Livewire é mostrada
    - Usuário confirma a exclusão
    - Método `delete($id)` é chamado, que:
        - Exclui o registro do banco
        - Exibe notificação de sucesso

## Benefícios desta Implementação

1. **Simplicidade**: Não depende de bibliotecas JavaScript externas como Alpine.js
2. **Controle Total**: Todo o estado é gerenciado pelo Livewire
3. **Experiência Completa**: Inclui:
    - Paginação
    - Busca em tempo real
    - Ordenação por colunas
    - Modal responsiva
    - Notificações
    - Animações de carregamento
    - Acessibilidade (fechar com ESC, clique fora)
4. **Validação de Dados**: Tanto no servidor quanto no cliente
5. **Código Limpo**: Separação clara entre lógica e apresentação

## Como Adaptar para Outros Componentes

1. Substitua o nome da classe `SeuComponente` pelo nome apropriado
2. Substitua `SeuModel` pela classe do modelo correto
3. Ajuste as propriedades e validações conforme necessário
4. Modifique os campos do formulário e da tabela
5. Personalize as mensagens de sucesso e erro

## Problemas Comuns e Soluções

1. **Modal não abrindo**: Verifique se `$showModal` está sendo setado corretamente
2. **Formulário não sendo resetado**: Verifique se `resetForm()` está sendo chamado corretamente
3. **Validação não funcionando**: Verifique as regras de validação e se os nomes dos campos correspondem
4. **Modal fechando sozinha**: Evite loops onde um método A chama B que chama A novamente

---

Este padrão de implementação foi desenvolvido para o Sistema ERP DEMBENA como uma referência para criação de operações CRUD com modal usando Livewire v3.
