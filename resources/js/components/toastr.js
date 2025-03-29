// Toastr notification handler
document.addEventListener('DOMContentLoaded', () => {
    // Configuração padrão do toastr
    toastr.options = {
        "closeButton": true,
        "progressBar": true,
        "positionClass": "toast-top-right",
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000"
    };

    // Ouvinte para o Livewire 3
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('notify', (data) => {
            const { type, title, message } = data;

            switch (type) {
                case 'info':
                    toastr.info(message, title);
                    break;
                case 'success':
                    toastr.success(message, title);
                    break;
                case 'warning':
                    toastr.warning(message, title);
                    break;
                case 'error':
                    toastr.error(message, title);
                    break;
                default:
                    toastr.info(message, title);
            }
        });
    });
});
