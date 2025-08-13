/**
 * Sistema de memória para configurações de paginação
 * Versão simplificada sem logs de debug
 * Updated: 2025-08-07 13:39
 */
// Cache buster: v3.0
document.addEventListener('DOMContentLoaded', function() {
    const STORAGE_KEY = 'erpdembena_per_page';
    let isProcessing = false;
    
    function applyPerPageValue() {
        if (isProcessing) return;
        isProcessing = true;
        
        const perPageSelects = document.querySelectorAll('select#perPage');
        const savedValue = localStorage.getItem(STORAGE_KEY);
        
        if (savedValue && perPageSelects.length > 0) {
            perPageSelects.forEach(select => {
                const option = Array.from(select.options).find(opt => opt.value === savedValue);
                if (option && select.value !== savedValue) {
                    select.value = savedValue;
                    
                    setTimeout(() => {
                        try {
                            const wireEl = select.closest('[wire\\:id]');
                            if (wireEl) {
                                const wireId = wireEl.getAttribute('wire:id');
                                if (window.Livewire && window.Livewire.find(wireId)) {
                                    window.Livewire.find(wireId).$wire.set('perPage', savedValue);
                                } else if (window.livewire && window.livewire.find(wireId)) {
                                    window.livewire.find(wireId).set('perPage', savedValue);
                                }
                            }
                        } catch (e) {}
                    }, 100);
                }
            });
        }
        
        setTimeout(() => { isProcessing = false; }, 300);
    }
    
    function savePerPageValue(value) {
        if (value) {
            localStorage.setItem(STORAGE_KEY, value);
        }
    }
    
    // Event listener para salvar
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'perPage') {
            savePerPageValue(e.target.value);
        }
    });
    
    // Aplicação inicial
    setTimeout(applyPerPageValue, 800);
    
    // Suporte básico para Livewire (sem loops)
    document.addEventListener('livewire:init', function() {
        setTimeout(applyPerPageValue, 300);
    });
});
