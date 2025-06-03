/**
 * Sistema de memória para configurações de paginação
 * Versão simplificada para garantir compatibilidade com Livewire
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Pagination memory script loaded v2');
    
    // Configuração básica
    const STORAGE_KEY = 'erpdembena_per_page';
    
    // Aplica o valor salvo diretamente no seletor
    function applyPerPageValue() {
        const perPageSelects = document.querySelectorAll('select#perPage');
        const savedValue = localStorage.getItem(STORAGE_KEY);
        
        console.log('Checking for perPage selects, found:', perPageSelects.length);
        console.log('Saved value:', savedValue);
        
        if (savedValue && perPageSelects.length > 0) {
            perPageSelects.forEach(select => {
                // Verifica se o valor existe nas opções
                const option = Array.from(select.options).find(opt => opt.value === savedValue);
                if (option) {
                    console.log('Found matching option, setting value to:', savedValue);
                    
                    // Simula uma seleção manual do usuário
                    select.value = savedValue;
                    
                    // Componente Livewire - acesso direto se possível
                    setTimeout(() => {
                        try {
                            // Verifica se existe um elemento pai com wire:id
                            const wireEl = select.closest('[wire\\:id]');
                            if (wireEl) {
                                const wireId = wireEl.getAttribute('wire:id');
                                console.log('Found Livewire component with ID:', wireId);
                                
                                // Para Livewire 3
                                if (window.Livewire && window.Livewire.find(wireId)) {
                                    // Usamos o $wire disponível no Livewire 3
                                    const component = window.Livewire.find(wireId);
                                    console.log('Setting perPage via Livewire 3');
                                    component.$wire.set('perPage', savedValue);
                                } 
                                // Para Livewire 2
                                else if (window.livewire && window.livewire.find(wireId)) {
                                    console.log('Setting perPage via Livewire 2');
                                    window.livewire.find(wireId).set('perPage', savedValue);
                                }
                            }
                        } catch (e) {
                            console.error('Error updating Livewire component:', e);
                        }
                    }, 100);
                }
            });
        }
    }
    
    // Salva o valor selecionado
    function savePerPageValue(value) {
        if (value) {
            console.log('Saving perPage value:', value);
            localStorage.setItem(STORAGE_KEY, value);
        }
    }
    
    // Event listeners
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'perPage') {
            savePerPageValue(e.target.value);
        }
    });
    
    // MutationObserver para detectar quando o select de paginação é adicionado ao DOM
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                setTimeout(applyPerPageValue, 200);
            }
        });
    });
    
    // Inicialização
    observer.observe(document.body, { childList: true, subtree: true });
    setTimeout(applyPerPageValue, 500); // Aplica após carregamento inicial com um delay seguro
    
    // Suporte para eventos Livewire
    window.addEventListener('livewire:load', function() {
        console.log('Livewire loaded, applying perPage value');
        setTimeout(applyPerPageValue, 200);
    });
    
    document.addEventListener('livewire:init', function() {
        console.log('Livewire initialized, applying perPage value');
        setTimeout(applyPerPageValue, 200);
    });
    
    // Hook para atualizações do Livewire
    if (window.Livewire) {
        window.Livewire.hook('message.processed', function() {
            console.log('Livewire message processed, checking perPage');
            setTimeout(applyPerPageValue, 200);
        });
    }
});
