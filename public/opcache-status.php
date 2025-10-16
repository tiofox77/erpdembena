<?php
/**
 * VERIFICADOR DE STATUS DO OPCACHE
 * Acesse: http://erpdembena.test/opcache-status.php
 */

// Verificar se OPcache está disponível
if (!function_exists('opcache_get_status')) {
    die('❌ OPcache não está instalado ou habilitado!');
}

// Obter status
$status = opcache_get_status();
$config = opcache_get_configuration();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OPcache Status - ERPDEMBENA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-lg shadow-lg p-6 mb-6">
            <h1 class="text-3xl font-bold text-white flex items-center">
                <i class="fas fa-tachometer-alt mr-3 animate-pulse"></i>
                OPcache Status - ERPDEMBENA
            </h1>
            <p class="text-white text-sm mt-2">PHP <?php echo PHP_VERSION; ?> - OPcache v<?php echo phpversion('Zend OPcache'); ?></p>
        </div>

        <!-- Status Geral -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 <?php echo $status['opcache_enabled'] ? 'border-green-500' : 'border-red-500'; ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="text-2xl font-bold <?php echo $status['opcache_enabled'] ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $status['opcache_enabled'] ? '✅ ATIVO' : '❌ INATIVO'; ?>
                        </p>
                    </div>
                    <i class="fas fa-power-off text-4xl <?php echo $status['opcache_enabled'] ? 'text-green-500' : 'text-red-500'; ?>"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Uso de Memória</p>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php echo round($status['memory_usage']['used_memory'] / 1024 / 1024, 2); ?>MB
                        </p>
                        <p class="text-xs text-gray-500">
                            de <?php echo round($config['directives']['opcache.memory_consumption']); ?>MB
                        </p>
                    </div>
                    <i class="fas fa-memory text-4xl text-blue-500"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Arquivos Cached</p>
                        <p class="text-2xl font-bold text-purple-600">
                            <?php echo number_format($status['opcache_statistics']['num_cached_scripts']); ?>
                        </p>
                        <p class="text-xs text-gray-500">
                            max: <?php echo number_format($config['directives']['opcache.max_accelerated_files']); ?>
                        </p>
                    </div>
                    <i class="fas fa-file-code text-4xl text-purple-500"></i>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                Estatísticas de Performance
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <p class="text-sm text-gray-600">Hits</p>
                    <p class="text-xl font-bold text-green-600">
                        <?php echo number_format($status['opcache_statistics']['hits']); ?>
                    </p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                    <p class="text-sm text-gray-600">Misses</p>
                    <p class="text-xl font-bold text-red-600">
                        <?php echo number_format($status['opcache_statistics']['misses']); ?>
                    </p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <p class="text-sm text-gray-600">Hit Rate</p>
                    <p class="text-xl font-bold text-blue-600">
                        <?php echo round($status['opcache_statistics']['opcache_hit_rate'], 2); ?>%
                    </p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg border border-yellow-200">
                    <p class="text-sm text-gray-600">Blacklist Misses</p>
                    <p class="text-xl font-bold text-yellow-600">
                        <?php echo number_format($status['opcache_statistics']['blacklist_misses']); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Configurações JIT -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-bolt text-yellow-600 mr-2"></i>
                JIT (Just-In-Time Compiler)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">JIT Enabled</p>
                    <p class="text-lg font-semibold <?php echo $config['directives']['opcache.jit_buffer_size'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $config['directives']['opcache.jit_buffer_size'] > 0 ? '✅ SIM' : '❌ NÃO'; ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">JIT Buffer Size</p>
                    <p class="text-lg font-semibold text-blue-600">
                        <?php echo $config['directives']['opcache.jit_buffer_size']; ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">JIT Mode</p>
                    <p class="text-lg font-semibold text-purple-600">
                        <?php echo $config['directives']['opcache.jit']; ?>
                    </p>
                </div>
            </div>
            
            <?php if ($config['directives']['opcache.jit_buffer_size'] == 0): ?>
                <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-1 mr-3"></i>
                        <div>
                            <p class="font-bold text-yellow-800">⚠️ JIT Desabilitado</p>
                            <p class="text-sm text-yellow-700 mt-1">
                                O JIT pode melhorar significativamente o desempenho do PHP 8.x. 
                                Recomendado ativar com: opcache.jit_buffer_size=128M
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Configurações Principais -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-cog text-gray-600 mr-2"></i>
                Configurações Principais
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Configuração</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Atual</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recomendado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">opcache.memory_consumption</td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo $config['directives']['opcache.memory_consumption']; ?>MB</td>
                            <td class="px-6 py-4 text-sm text-green-600 font-semibold">256MB</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">opcache.interned_strings_buffer</td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo $config['directives']['opcache.interned_strings_buffer']; ?>MB</td>
                            <td class="px-6 py-4 text-sm text-green-600 font-semibold">16MB</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">opcache.max_accelerated_files</td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo number_format($config['directives']['opcache.max_accelerated_files']); ?></td>
                            <td class="px-6 py-4 text-sm text-green-600 font-semibold">20000</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">opcache.revalidate_freq</td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo $config['directives']['opcache.revalidate_freq']; ?>s</td>
                            <td class="px-6 py-4 text-sm text-green-600 font-semibold">2s (dev) / 60s (prod)</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">opcache.jit_buffer_size</td>
                            <td class="px-6 py-4 text-sm text-gray-500"><?php echo $config['directives']['opcache.jit_buffer_size']; ?></td>
                            <td class="px-6 py-4 text-sm text-green-600 font-semibold">128M</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ações -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-tools text-blue-600 mr-2"></i>
                Ações Recomendadas
            </h2>
            <div class="space-y-3">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <p class="font-semibold text-gray-800">📄 1. Editar php.ini</p>
                    <p class="text-sm text-gray-600 mt-1">Laragon Menu → PHP → php.ini</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <p class="font-semibold text-gray-800">⚙️ 2. Aplicar Configurações</p>
                    <p class="text-sm text-gray-600 mt-1">Use o arquivo: <code class="bg-gray-100 px-2 py-1 rounded">opcache-config-recomendado.ini</code></p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <p class="font-semibold text-gray-800">🔄 3. Reiniciar Serviços</p>
                    <p class="text-sm text-gray-600 mt-1">Laragon Menu → Stop All → Start All</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <p class="font-semibold text-gray-800">🧹 4. Limpar Cache Laravel</p>
                    <code class="text-sm bg-gray-100 px-2 py-1 rounded block mt-2">php artisan optimize:clear && php artisan optimize</code>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Última atualização: <?php echo date('d/m/Y H:i:s'); ?></p>
            <p class="mt-2">
                <a href="?refresh=1" class="text-blue-600 hover:text-blue-800 underline">
                    <i class="fas fa-sync-alt mr-1"></i>Atualizar Status
                </a>
            </p>
        </div>
    </div>
</body>
</html>
