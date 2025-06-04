/**
 * Maintenance Dashboard JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');

            // Toggle icon
            const icon = sidebarToggle.querySelector('i');
            if (icon.classList.contains('fa-chevron-left')) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            }
        });
    }

    // Responsive sidebar for mobile
    function checkWindowSize() {
        if (window.innerWidth < 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');

            // Update toggle icon
            const icon = sidebarToggle.querySelector('i');
            if (icon.classList.contains('fa-chevron-left')) {
                icon.classList.remove('fa-chevron-left');
                icon.classList.add('fa-chevron-right');
            }

            // Em telas pequenas, mostramos o submenu somente ao passar o mouse
            const menuItems = document.querySelectorAll('.menu-has-children');
            menuItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    const submenu = this.querySelector('.submenu');
                    if (submenu) submenu.classList.add('show');
                    this.classList.add('open');
                });

                item.addEventListener('mouseleave', function() {
                    const submenu = this.querySelector('.submenu');
                    if (submenu) submenu.classList.remove('show');
                    this.classList.remove('open');
                });
            });
        } else {
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('expanded');

            // Reset toggle icon
            const icon = sidebarToggle.querySelector('i');
            if (icon.classList.contains('fa-chevron-right')) {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-left');
            }

            // Em telas maiores, removemos os eventos de mouse e voltamos ao comportamento normal
            const menuItems = document.querySelectorAll('.menu-has-children');
            menuItems.forEach(item => {
                item.removeEventListener('mouseenter', null);
                item.removeEventListener('mouseleave', null);
            });
        }
    }

    // Check on load and resize
    checkWindowSize();
    window.addEventListener('resize', checkWindowSize);

    // Notification bell dropdown
    const notificationBell = document.querySelector('.notification-bell');
    if (notificationBell) {
        notificationBell.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');

            // Se o menu de usuário estiver aberto, feche-o
            const userProfile = document.querySelector('.user-profile');
            if (userProfile && userProfile.classList.contains('active')) {
                userProfile.classList.remove('active');
            }
        });

        // Botão de marcar como lido em cada notificação
        const markAsReadButtons = document.querySelectorAll('.mark-as-read');
        markAsReadButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const notificationItem = this.closest('.notification-item');

                // Mudar o estilo visual para notificação lida
                notificationItem.style.backgroundColor = 'white';
                notificationItem.style.opacity = '0.7';

                // Aqui você enviaria uma requisição AJAX para marcar como lida no servidor
                console.log('Notification marked as read');

                // Atualizar contador de notificações
                updateNotificationCounter();
            });
        });

        // Botão de fechar (remover) notificação
        const closeButtons = document.querySelectorAll('.notification-close');
        closeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.stopPropagation();
                const notificationItem = this.closest('.notification-item');

                // Animar a remoção
                notificationItem.style.height = notificationItem.offsetHeight + 'px';
                notificationItem.style.transition = 'all 0.3s ease';

                setTimeout(() => {
                    notificationItem.style.height = '0';
                    notificationItem.style.padding = '0';
                    notificationItem.style.margin = '0';
                    notificationItem.style.opacity = '0';

                    setTimeout(() => {
                        notificationItem.remove();
                        updateNotificationCounter();
                    }, 300);
                }, 10);

                // Aqui você enviaria uma requisição AJAX para remover a notificação no servidor
                console.log('Notification removed');
            });
        });

        // Botão de marcar todas como lidas
        const markAllButton = document.querySelector('.mark-all-read');
        if (markAllButton) {
            markAllButton.addEventListener('click', function(e) {
                e.stopPropagation();

                // Marcar todas como lidas
                const notificationItems = document.querySelectorAll('.notification-item');
                notificationItems.forEach(item => {
                    item.style.backgroundColor = 'white';
                    item.style.opacity = '0.7';
                });

                // Atualizar contador para zero
                const badge = document.querySelector('.notification-bell .badge');
                if (badge) {
                    badge.textContent = '0';
                    badge.style.display = 'none';
                }

                // Aqui você enviaria uma requisição AJAX para marcar todas como lidas no servidor
                console.log('All notifications marked as read');
            });
        }

        // Função para atualizar o contador de notificações
        function updateNotificationCounter() {
            const unreadeNotifications = document.querySelectorAll('.notification-item:not([style*="opacity: 0.7"])');
            const badge = document.querySelector('.notification-bell .badge');

            if (badge) {
                const count = unreadeNotifications.length;
                badge.textContent = count;

                if (count === 0) {
                    badge.style.display = 'none';
                } else {
                    badge.style.display = 'flex';
                }
            }
        }
    }

    // User profile dropdown
    const userProfile = document.querySelector('.user-profile');
    if (userProfile) {
        userProfile.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
        });
    }

    // Close dropdowns when clicking elsewhere
    document.addEventListener('click', function() {
        if (notificationBell && notificationBell.classList.contains('active')) {
            notificationBell.classList.remove('active');
        }
        if (userProfile && userProfile.classList.contains('active')) {
            userProfile.classList.remove('active');
        }
    });

    // Search box functionality
    const searchBox = document.querySelector('.search-box input');
    if (searchBox) {
        searchBox.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });

        searchBox.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });

        searchBox.addEventListener('input', function() {
            // Here you would typically implement search functionality
            // For demonstration, we're just adding a class
            if (this.value.length > 0) {
                this.parentElement.classList.add('has-text');
            } else {
                this.parentElement.classList.remove('has-text');
            }
        });
    }

    // Mark alert as completed
    const checkIcons = document.querySelectorAll('.check-icon');
    checkIcons.forEach(icon => {
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const alertItem = this.closest('.alert-item');
            alertItem.style.opacity = '0.6';
            alertItem.style.transition = 'opacity 0.3s ease';

            // Here you would typically send an AJAX request to mark as complete
            // For demonstration, we're just changing the appearance
            this.innerHTML = '<i class="fas fa-check-circle"></i> Done';
            this.style.color = '#28a745';
        });
    });

    // Interactive metrics card animation
    const metricCards = document.querySelectorAll('.metric-card');
    metricCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.1)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 6px rgba(0, 0, 0, 0.05)';
        });
    });

    // Action cards hover effect
    const actionCards = document.querySelectorAll('.action-card');
    actionCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 20px rgba(0, 0, 0, 0.1)';
            this.style.borderColor = '#4a6cf7';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 2px 6px rgba(0, 0, 0, 0.05)';
            this.style.borderColor = '#e9ecef';
        });
    });

    // Menu dropdowns - Flowbite style
    const dropdownToggles = document.querySelectorAll('[data-collapse-toggle]');
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const dropdownId = this.getAttribute('data-collapse-toggle');
            const dropdown = document.getElementById(dropdownId);
            const parent = this.closest('.menu-has-children');

            if (dropdown) {
                dropdown.classList.toggle('show');
                parent.classList.toggle('open');

                // Set aria-expanded attribute
                const isExpanded = dropdown.classList.contains('show');
                this.setAttribute('aria-expanded', isExpanded);
                parent.setAttribute('aria-expanded', isExpanded);

                // Rotate arrow icon
                const icon = this.querySelector('.toggle-icon');
                if (icon) {
                    if (isExpanded) {
                        icon.style.transform = 'rotate(180deg)';
                    } else {
                        icon.style.transform = 'rotate(0deg)';
                    }
                }
            }
        });
    });

    // Corrige o problema de visualização no modo mobile
    function fixMobileMenuBehavior() {
        const windowWidth = window.innerWidth;
        const maintenanceMenu = document.getElementById('maintenanceMenu');
        const submenu = document.getElementById('maintenance-dropdown');

        if (windowWidth < 768) {
            // Modo mobile - submenu aparece à direita
            if (maintenanceMenu && submenu) {
                maintenanceMenu.addEventListener('mouseenter', function() {
                    submenu.classList.add('show');
                    maintenanceMenu.classList.add('open');
                });

                maintenanceMenu.addEventListener('mouseleave', function() {
                    submenu.classList.remove('show');
                    maintenanceMenu.classList.remove('open');
                });
            }
        } else {
            // Modo desktop - submenu aparece abaixo
            if (maintenanceMenu) {
                maintenanceMenu.removeEventListener('mouseenter', null);
                maintenanceMenu.removeEventListener('mouseleave', null);
            }
        }
    }

    // Execute quando a página carregar e quando a janela for redimensionada
    window.addEventListener('load', fixMobileMenuBehavior);
    window.addEventListener('resize', fixMobileMenuBehavior);

    // Open Maintenance menu by default on page load
    window.addEventListener('load', function() {
        const maintenanceToggle = document.querySelector('[data-collapse-toggle="maintenance-dropdown"]');
        if (maintenanceToggle) {
            setTimeout(function() {
                maintenanceToggle.click();
            }, 100);
        }
    });
});