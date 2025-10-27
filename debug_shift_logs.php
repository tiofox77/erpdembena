<?php
/**
 * Script para debug dos logs do Shift Management em ambiente cPanel
 * Acesse via: https://softec.vip:2078/dembenaerp.softec.vip/debug_shift_logs.php
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Definir header para exibir como HTML
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Shift Assignment Logs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .log-entry { background: #f8f9fa; margin: 10px 0; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .info { border-left-color: #28a745; background: #d4edda; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px; }
        .stat-card { background: #e3f2fd; padding: 15px; border-radius: 5px; text-align: center; }
        .stat-number { font-size: 24px; font-weight: bold; color: #1976d2; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug Shift Assignment - cPanel Environment</h1>
        <p><strong>Servidor:</strong> https://softec.vip:2078/dembenaerp.softec.vip</p>
        
        <?php
        try {
            // Estatísticas básicas
            echo '<div class="stats">';
            
            $employeesCount = \App\Models\HR\Employee::where('employment_status', 'active')->count();
            echo '<div class="stat-card"><div class="stat-number">' . $employeesCount . '</div>Funcionários Ativos</div>';
            
            $shiftsCount = \App\Models\HR\Shift::where('is_active', true)->count();
            echo '<div class="stat-card"><div class="stat-number">' . $shiftsCount . '</div>Turnos Ativos</div>';
            
            $assignmentsCount = \App\Models\HR\ShiftAssignment::count();
            echo '<div class="stat-card"><div class="stat-number">' . $assignmentsCount . '</div>Atribuições Totais</div>';
            
            echo '</div>';
            
            // Botões de ação
            echo '<div style="margin: 20px 0;">';
            if (isset($_GET['test_create'])) {
                echo '<button class="btn" onclick="window.location.href=\'?\'" style="background: #28a745;">← Voltar</button>';
            } else {
                echo '<button class="btn" onclick="window.location.href=\'?test_create=1\'">🧪 Teste Criação Assignment</button>';
                echo '<button class="btn" onclick="window.location.href=\'?view_logs=1\'" style="background: #ffc107;">📋 Ver Logs Laravel</button>';
                echo '<button class="btn" onclick="window.location.href=\'?clear_logs=1\'" style="background: #dc3545;">🗑️ Limpar Logs</button>';
            }
            echo '</div>';
            
            // Processar ações
            if (isset($_GET['clear_logs'])) {
                $logPath = storage_path('logs/laravel.log');
                if (file_exists($logPath)) {
                    file_put_contents($logPath, '');
                    echo '<div class="log-entry info">✅ Logs limpos com sucesso!</div>';
                } else {
                    echo '<div class="log-entry error">❌ Arquivo de log não encontrado: ' . $logPath . '</div>';
                }
            }
            
            if (isset($_GET['test_create'])) {
                echo '<h2>🧪 Teste de Criação de Assignment</h2>';
                
                // Obter funcionário e shift para teste
                $employee = \App\Models\HR\Employee::where('employment_status', 'active')->first();
                $shift = \App\Models\HR\Shift::where('is_active', true)->first();
                
                if (!$employee || !$shift) {
                    echo '<div class="log-entry error">❌ Não há funcionários ou turnos disponíveis para teste</div>';
                } else {
                    echo '<div class="log-entry info">';
                    echo '<strong>Funcionário:</strong> ' . $employee->full_name . ' (ID: ' . $employee->id . ')<br>';
                    echo '<strong>Turno:</strong> ' . $shift->name . ' (ID: ' . $shift->id . ')';
                    echo '</div>';
                    
                    try {
                        // Simular criação como faria o Livewire
                        $assignmentData = [
                            'employee_id' => $employee->id,
                            'shift_id' => $shift->id,
                            'start_date' => \Carbon\Carbon::today(),
                            'end_date' => \Carbon\Carbon::today()->addDays(30),
                            'is_permanent' => false,
                            'rotation_pattern' => null,
                            'notes' => 'Teste debug cPanel - ' . date('Y-m-d H:i:s'),
                            'assigned_by' => $employee->id,
                        ];
                        
                        // Log do teste
                        \Log::info('=== TESTE CRIAÇÃO ASSIGNMENT (cPanel Debug) ===', $assignmentData);
                        
                        $assignment = \App\Models\HR\ShiftAssignment::create($assignmentData);
                        
                        echo '<div class="log-entry info">';
                        echo '✅ <strong>Assignment criado com sucesso!</strong><br>';
                        echo '<strong>ID:</strong> ' . $assignment->id . '<br>';
                        echo '<strong>Criado em:</strong> ' . $assignment->created_at . '<br>';
                        echo '</div>';
                        
                        \Log::info('=== TESTE CRIAÇÃO - SUCESSO ===', ['assignment_id' => $assignment->id]);
                        
                    } catch (\Exception $e) {
                        echo '<div class="log-entry error">';
                        echo '❌ <strong>Erro ao criar assignment:</strong><br>';
                        echo '<strong>Mensagem:</strong> ' . $e->getMessage() . '<br>';
                        echo '<strong>Arquivo:</strong> ' . $e->getFile() . '<br>';
                        echo '<strong>Linha:</strong> ' . $e->getLine();
                        echo '</div>';
                        
                        \Log::error('=== TESTE CRIAÇÃO - ERRO ===', [
                            'error' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                    }
                }
            }
            
            if (isset($_GET['view_logs']) || isset($_GET['test_create'])) {
                echo '<h2>📋 Logs Recentes (Últimas 50 linhas)</h2>';
                
                $logPath = storage_path('logs/laravel.log');
                if (file_exists($logPath)) {
                    $logContent = file_get_contents($logPath);
                    $lines = explode("\n", $logContent);
                    $recentLines = array_slice($lines, -50);
                    
                    foreach ($recentLines as $line) {
                        if (empty(trim($line))) continue;
                        
                        $class = 'log-entry';
                        if (strpos($line, 'ERROR') !== false) $class .= ' error';
                        elseif (strpos($line, 'WARNING') !== false) $class .= ' warning';
                        elseif (strpos($line, 'INFO') !== false) $class .= ' info';
                        
                        echo '<div class="' . $class . '">';
                        echo '<pre>' . htmlspecialchars($line) . '</pre>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="log-entry error">❌ Arquivo de log não encontrado: ' . $logPath . '</div>';
                }
            }
            
            // Mostrar assignments existentes
            if (!isset($_GET['test_create'])) {
                echo '<h2>📊 Assignments Atuais na Base de Dados</h2>';
                $assignments = \App\Models\HR\ShiftAssignment::with(['employee', 'shift'])->orderBy('created_at', 'desc')->take(10)->get();
                
                if ($assignments->count() > 0) {
                    echo '<table border="1" cellpadding="10" style="width: 100%; border-collapse: collapse;">';
                    echo '<tr style="background: #e3f2fd;">';
                    echo '<th>ID</th><th>Funcionário</th><th>Turno</th><th>Data Início</th><th>Data Fim</th><th>Permanente</th><th>Criado em</th>';
                    echo '</tr>';
                    
                    foreach ($assignments as $a) {
                        echo '<tr>';
                        echo '<td>' . $a->id . '</td>';
                        echo '<td>' . $a->employee->full_name . '</td>';
                        echo '<td>' . $a->shift->name . '</td>';
                        echo '<td>' . $a->start_date->format('d/m/Y') . '</td>';
                        echo '<td>' . ($a->end_date ? $a->end_date->format('d/m/Y') : '—') . '</td>';
                        echo '<td>' . ($a->is_permanent ? '✅' : '❌') . '</td>';
                        echo '<td>' . $a->created_at->format('d/m/Y H:i') . '</td>';
                        echo '</tr>';
                    }
                    
                    echo '</table>';
                } else {
                    echo '<div class="log-entry warning">⚠️ Nenhum assignment encontrado na base de dados</div>';
                }
            }
            
        } catch (\Exception $e) {
            echo '<div class="log-entry error">';
            echo '❌ <strong>Erro geral:</strong><br>';
            echo '<strong>Mensagem:</strong> ' . $e->getMessage() . '<br>';
            echo '<strong>Arquivo:</strong> ' . $e->getFile() . '<br>';
            echo '<strong>Linha:</strong> ' . $e->getLine();
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
            <strong>📌 Instruções:</strong>
            <ul>
                <li>Acesse a página de turnos: <a href="/hr/shift-management" target="_blank">/hr/shift-management</a></li>
                <li>Tente criar um novo assignment na aba "Assignments"</li>
                <li>Volte aqui e clique em "Ver Logs Laravel" para verificar os logs detalhados</li>
                <li>Se houver erro, os logs mostrarão exatamente onde está o problema</li>
            </ul>
        </div>
    </div>
    
    <script>
        // Auto-refresh a cada 30 segundos se estiver vendo logs
        <?php if (isset($_GET['view_logs'])): ?>
        setTimeout(function() {
            window.location.reload();
        }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>
