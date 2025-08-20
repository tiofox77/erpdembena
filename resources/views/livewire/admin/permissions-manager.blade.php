<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Gerenciador de Permissões e Roles
                    </h4>
                    <small>Gestão completa do sistema de permissões - Executar scripts localmente ou transferir para outro servidor</small>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Roles</h6>
                                    <h2 class="mb-0">{{ $stats['total_roles'] }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users-cog fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Total Permissões</h6>
                                    <h2 class="mb-0">{{ $stats['total_permissions'] }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-key fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Usuários com Roles</h6>
                                    <h2 class="mb-0">{{ $stats['users_with_roles'] }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-check fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6 class="card-title">Sem Roles</h6>
                                    <h2 class="mb-0">{{ $stats['users_without_roles'] }}</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-user-times fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações Seguras -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-shield-check me-2"></i>
                                Ações Seguras - Recomendadas
                            </h5>
                            <small>Estas ações são seguras e não causam problemas no sistema</small>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($safeActions as $key => $action)
                                    <div class="col-md-6 mb-3">
                                        <button wire:click="executeQuickAction('{{ $key }}')"
                                                class="btn {{ $action['class'] }} w-100 h-100 d-flex flex-column justify-content-center"
                                                @if($isExecuting) disabled @endif
                                                style="min-height: 80px;">
                                            <i class="fas fa-{{ $action['icon'] }} fa-2x mb-2"></i>
                                            <strong>{{ $action['label'] }}</strong>
                                        </button>
                                        <small class="text-success d-block mt-2 fw-bold">{{ $action['description'] }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Ações de Manutenção -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="fas fa-tools me-2"></i>
                                Ações de Manutenção - Usar com Cuidado
                            </h5>
                            <small><i class="fas fa-exclamation-triangle me-1"></i>Estas ações modificam o sistema - confirmação obrigatória</small>
                        </div>
                        <div class="card-body bg-light">
                            <div class="row">
                                @foreach($maintenanceActions as $key => $action)
                                    <div class="col-md-6 mb-3">
                                        <button wire:click="executeQuickAction('{{ $key }}')"
                                                class="btn {{ $action['class'] }} w-100 h-100 d-flex flex-column justify-content-center position-relative"
                                                @if($isExecuting) disabled @endif
                                                style="min-height: 80px;">
                                            <i class="fas fa-{{ $action['icon'] }} fa-2x mb-2"></i>
                                            <strong>{{ $action['label'] }}</strong>
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                <i class="fas fa-exclamation" style="font-size: 0.7rem;"></i>
                                            </span>
                                        </button>
                                        <small class="text-warning d-block mt-2 fw-bold">{{ $action['description'] }}</small>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Script Executor -->
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-download me-2"></i>
                                Download de Scripts (Uso Avançado)
                            </h5>
                            <small>Para transferir e executar noutros servidores</small>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Recomendação:</strong> Use as "Ações Seguras" acima para execução online imediata.
                                Esta secção é apenas para download de scripts para outros servidores.
                            </div>
                            
                            <!-- Script Selection -->
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="scriptSelect" class="form-label">Script para Download:</label>
                                    <select wire:model="selectedScript" id="scriptSelect" class="form-select">
                                        <option value="">-- Escolha um script --</option>
                                        @foreach($availableScripts as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button wire:click="downloadScript"
                                            class="btn btn-outline-primary w-100"
                                            @if(empty($selectedScript)) disabled @endif>
                                        <i class="fas fa-download me-1"></i>
                                        Download Script
                                    </button>
                                </div>
                            </div>

                            <!-- Script Description -->
                            @if($selectedScript)
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>{{ $availableScripts[$selectedScript] ?? 'Script Selecionado' }}</strong>
                                    <br>
                                    @switch($selectedScript)
                                        @case('definitive_roles_cleanup')
                                            <span class="text-danger">ATENÇÃO:</span> Script de limpeza que remove roles. Use apenas se souber o que está a fazer.
                                            @break
                                        @case('create_modular_roles_system')
                                            <span class="text-success">SEGURO:</span> Script que cria 5 roles modulares (maintenance-manager, mrp-manager, supplychain-manager, hr-manager, system-admin).
                                            @break
                                        @default
                                            Script para download e execução em servidor remoto.
                                    @endswitch
                                </div>
                            @endif

                            <!-- Output -->
                            @if($showOutput)
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6>Resultado da Execução:</h6>
                                        <button wire:click="clearOutput" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-trash"></i> Limpar
                                        </button>
                                    </div>
                                    <div class="bg-dark text-light p-3 rounded" style="font-family: monospace; white-space: pre-wrap; max-height: 400px; overflow-y: auto;">{{ $scriptOutput }}</div>
                                </div>
                            @endif

                            <!-- Instructions for Remote Server -->
                            <div class="mt-4">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-cloud-upload-alt me-2"></i>Execução Online vs. Servidor Remoto:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>💻 Online (Recomendado):</strong>
                                            <ul class="mb-0 mt-1">
                                                <li>Use as "Ações Rápidas" acima</li>
                                                <li>Execução imediata e segura</li>
                                                <li>Interface visual com feedback</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>🚀 Servidor Remoto:</strong>
                                            <ul class="mb-0 mt-1">
                                                <li>Download do script selecionado</li>
                                                <li>Transferência via FTP/SSH</li>
                                                <li>Terminal: <code>php script.php</code></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Roles -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>
                                Roles Actuais
                            </h5>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            @forelse($roles as $role)
                                <div class="border rounded p-2 mb-2 @if($role->name === 'super-admin') bg-light border-primary @endif">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $role->name }}</strong>
                                            @if($role->name === 'super-admin')
                                                <span class="badge bg-primary ms-1">ADMIN</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $role->permissions->count() }} perms</small>
                                    </div>
                                    <small class="text-muted">ID: {{ $role->id }}</small>
                                </div>
                            @empty
                                <div class="text-center text-muted">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                                    <p>Nenhuma role encontrada</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Users Without Roles -->
                    @if($stats['users_without_roles'] > 0)
                        <div class="card mt-3">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Usuários sem Role
                                </h6>
                            </div>
                            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                @foreach($recentUsers as $user)
                                    <div class="border-bottom py-1">
                                        <small>
                                            <strong>{{ $user->name }}</strong><br>
                                            <span class="text-muted">{{ $user->email }}</span>
                                        </small>
                                    </div>
                                @endforeach
                                @if($stats['users_without_roles'] > 10)
                                    <div class="text-center mt-2">
                                        <small class="text-muted">
                                            ... e mais {{ $stats['users_without_roles'] - 10 }} usuários
                                        </small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif
</div>
