# Layout de Tabelas e Componentes Visuais

## 1. Estrutura Padronizada de Tabelas

O ERP DEMBENA utiliza um layout consistente para tabelas de dados em todos os módulos, garantindo uma experiência de usuário coesa.

### 1.1 Template Padrão para Tabelas

```blade
<!-- Table -->
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <!-- Exemplo de coluna com ordenação -->
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <div class="flex items-center cursor-pointer" wire:click="sortBy('reference_number')">
                        ID/Referência
                        @if($sortField === 'reference_number')
                            @if($sortDirection === 'asc')
                                <i class="fas fa-sort-up ml-1"></i>
                            @else
                                <i class="fas fa-sort-down ml-1"></i>
                            @endif
                        @else
                            <i class="fas fa-sort ml-1 text-gray-300"></i>
                        @endif
                    </div>
                </th>
                
                <!-- Coluna normal -->
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Descrição
                </th>
                
                <!-- Outras colunas... -->
                
                <!-- Coluna de status com cores -->
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                </th>
                
                <!-- Coluna de ações -->
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ações
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($records as $record)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $record->reference_number }}
                    </td>
                    
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $record->description }}
                    </td>
                    
                    <!-- Outras colunas... -->
                    
                    <!-- Status com badges coloridos -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $record->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                               ($record->status === 'approved' ? 'bg-green-100 text-green-800' : 
                               ($record->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                               ($record->status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                'bg-purple-100 text-purple-800'))) }}">
                            {{ $statusOptions[$record->status] ?? 'Unknown' }}
                        </span>
                    </td>
                    
                    <!-- Ações -->
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            <!-- Botão de visualização -->
                            <button wire:click="viewDetails({{ $record->id }})" class="text-blue-600 hover:text-blue-900" title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            <!-- Botão de PDF -->
                            <button wire:click="generatePDF({{ $record->id }})" class="text-red-600 hover:text-red-900" title="Gerar PDF">
                                <i class="fas fa-file-pdf"></i>
                            </button>
                            
                            <!-- Botão de edição -->
                            <button wire:click="edit({{ $record->id }})" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            
                            <!-- Botões de aprovação/rejeição condicionais -->
                            @if($record->status === 'pending')
                                <button wire:click="changeStatus({{ $record->id }}, 'approved')" class="text-green-600 hover:text-green-900" title="Aprovar">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button wire:click="changeStatus({{ $record->id }}, 'rejected')" class="text-red-600 hover:text-red-900" title="Rejeitar">
                                    <i class="fas fa-times"></i>
                                </button>
                            @endif
                            
                            <!-- Botão de exclusão -->
                            <button wire:click="confirmDelete({{ $record->id }})" class="text-red-600 hover:text-red-900" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        Nenhum registro encontrado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
    {{ $records->links() }}
</div>
```

## 2. Cabeçalhos de Listagem

Os cabeçalhos das listagens seguem um padrão consistente em todo o sistema:

```blade
<div class="p-4 sm:px-6 flex flex-col sm:flex-row justify-between sm:items-center border-b border-gray-200">
    <h1 class="text-lg font-medium text-gray-900 flex items-center">
        <i class="fas fa-clipboard-list mr-3 text-gray-500"></i> Título do Módulo
    </h1>
    <div class="mt-3 sm:mt-0 flex space-x-2">
        <!-- Botão de exportação PDF -->
        <button 
            type="button" 
            wire:click="generateListPDF"
            class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <i class="fas fa-file-pdf mr-2 text-red-500"></i> Exportar Lista
        </button>
        
        <!-- Botão de novo registro -->
        <button 
            type="button" 
            wire:click="openModal"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <i class="fas fa-plus-circle mr-2"></i> Novo Registro
        </button>
    </div>
</div>
```

## 3. Indicadores Visuais e Cores de Status

O sistema utiliza um esquema de cores consistente para indicar diferentes status:

| Status | Classe CSS | Cor |
|--------|------------|-----|
| Pendente | `bg-yellow-100 text-yellow-800` | Amarelo |
| Aprovado | `bg-green-100 text-green-800` | Verde |
| Rejeitado | `bg-red-100 text-red-800` | Vermelho |
| Em Processamento | `bg-blue-100 text-blue-800` | Azul |
| Finalizado | `bg-purple-100 text-purple-800` | Roxo |

## 4. Modal de Detalhes

Modal padrão para visualização de detalhes:

```blade
<!-- View Details Modal -->
@if($showViewModal)
    <div class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                <!-- Modal header -->
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 sm:px-6 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Detalhes do Registro
                    </h3>
                    <button 
                        type="button" 
                        wire:click="closeViewModal" 
                        class="bg-white rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
                    >
                        <span class="sr-only">Fechar</span>
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <!-- Modal content -->
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <!-- Detalhes -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Seção de informações gerais -->
                        <div class="col-span-2">
                            <h4 class="text-md font-semibold text-gray-700 mb-2 pb-2 border-b">
                                Informações Gerais
                            </h4>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Referência:</p>
                            <p class="font-medium">{{ $viewRecord->reference_number ?? 'N/A' }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Status:</p>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $viewRecord->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($viewRecord->status === 'approved' ? 'bg-green-100 text-green-800' : 
                                   ($viewRecord->status === 'rejected' ? 'bg-red-100 text-red-800' : 
                                   ($viewRecord->status === 'processing' ? 'bg-blue-100 text-blue-800' : 
                                    'bg-purple-100 text-purple-800'))) }}">
                                {{ $statusOptions[$viewRecord->status] ?? 'Unknown' }}
                            </span>
                        </div>
                        
                        <!-- Mais campos... -->
                        
                        <!-- Observações -->
                        <div class="col-span-2">
                            <p class="text-sm text-gray-500">Observações:</p>
                            <p>{{ $viewRecord->notes ?? 'Nenhuma observação' }}</p>
                        </div>
                        
                        <!-- Data de criação e usuário -->
                        <div>
                            <p class="text-sm text-gray-500">Criado em:</p>
                            <p>{{ $viewRecord->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500">Criado por:</p>
                            <p>{{ $viewRecord->createdBy->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Modal footer -->
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button 
                        type="button" 
                        wire:click="generatePDF({{ $viewRecord->id ?? null }})"
                        class="mr-2 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        <i class="fas fa-file-pdf mr-2"></i> Gerar PDF
                    </button>
                    <button 
                        type="button" 
                        wire:click="closeViewModal" 
                        class="mt-3 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
```
