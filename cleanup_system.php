<?php
/**
 * Sistema de Limpeza - ERP DEMBENA
 * Remove arquivos temporários, debug e testes
 * Seguro para usar no cPanel
 */

// Configuração
$basePath = __DIR__;
$dryRun = isset($_GET['dryrun']) ? true : false; // Modo preview
$confirm = isset($_GET['confirm']) ? $_GET['confirm'] === 'yes' : false;

// Arrays de arquivos e padrões a remover
$filesToRemove = [
    // Scripts de debug/teste no root
    'add_attendance_employee_7.php',
    'add_missing_permission.php',
    'analyze_complete_roles_system.php',
    'analyze_module_separation.php',
    'analyze_permissions_categorization.php',
    'analyze_sidebar_permissions.php',
    'analyze_super_admin_permissions.php',
    'audit_all_permissions.php',
    'audit_roles_layout_permissions.php',
    'backup_permissions_*.json',
    'calculate_inss.php',
    'check-excel-cpanel.php',
    'check-fix.php',
    'check_abel.php',
    'check_existing_roles.php',
    'check_log.php',
    'check_maintenance_permissions.php',
    'check_payrolls.php',
    'check_sidebar_permissions.php',
    'clean_all_roles_except_super_admin.php',
    'cleanup_root_junk.php',
    'cleanup_unused_files.php',
    'clear_permissions_cache.php',
    'compare_users_permissions.php',
    'configure_employee_rotation.php',
    'create-maintenance-role.php',
    'create_complete_modular_system.php',
    'create_modular_area_roles.php',
    'create_modular_roles.php',
    'create_modular_roles_system.php',
    'create_simple_test_discounts.php',
    'create_test_discounts.php',
    'debug_absence_calculation.php',
    'debug_access_denied.php',
    'debug_approval_filter_conditions.php',
    'debug_attendance.php',
    'debug_attendance_ana.php',
    'debug_complete_receipt_data.php',
    'debug_current_session.php',
    'debug_current_user_session.php',
    'debug_emp_id.php',
    'debug_employee.php',
    'debug_live_permissions.php',
    'debug_maintenance_manager_role.php',
    'debug_maintenance_menu.php',
    'debug_maintenance_menu_access.php',
    'debug_maintenance_plan_access.php',
    'debug_menu_permissions.php',
    'debug_overtime_ana.php',
    'debug_overtime_detailed.php',
    'debug_payroll_approval_logic.php',
    'debug_receipt_controller.php',
    'debug_receipt_data.php',
    'debug_receipt_view.php',
    'debug_salary_advance_processing.php',
    'debug_salary_discounts.php',
    'debug_shift_logs.php',
    'debug_stock_access.php',
    'debug_template_data.php',
    'definitive_clean_roles.php',
    'definitive_roles_cleanup.php',
    'diagnose-subsidy.php',
    'final_roles_elimination.php',
    'find-composer-server.sh',
    'find_menu_errors.php',
    'fix_goods_receipts.php',
    'fix_irt_brackets.php',
    'fix_maintenance_manager_permissions.php',
    'fix_maintenance_menu.php',
    'fix_mrp_migrations.php',
    'fix_preventive_permission.php',
    'fix_stock_middleware.php',
    'fix_storage_directories.php',
    'fix_syntax.php',
    'force_clean_all_roles.php',
    'import_debug.txt',
    'remove_test_discounts.php',
    'reorganize_permissions_by_modules.php',
    'reorganize_permissions_system.php',
    'standardize_roles_permissions.php',
    'stop_auto_role_creation.php',
    'sync_biometric_ids.php',
    'tatus',
    'temp_modal.blade.php',
    'test-abel.php',
    'test_automatic_installment_processing.php',
    'test_calendar_final.php',
    'test_calendar_fix.php',
    'test_checkbox_livewire.php',
    'test_clean_advance_processing.php',
    'test_complete_checkbox_flow.php',
    'test_db_table_fix.php',
    'test_logged_user.php',
    'test_shift_assignment.php',
    'ultimate_clean_roles.php',
    'uniformize_all_areas_permissions.php',
    'updateDailyPlan.php',
    'update_irt_brackets.php',
    'update_password.php',
    'verify_module_permissions_views.php',
    'shift_report.txt',
    'clear_and_fix.php',
    'change_password.php',
    '8.2)',
    '-i',
];

// Arquivos .md de documentação temporária
$mdDocsToRemove = [
    'CLEAR-BROWSER-CACHE.txt',
    'CPANEL-EXCEL-TROUBLESHOOTING.md',
    'DEBUG_ERROR_500_SUBSIDY.md',
    'DEBUG_SUBSIDIO_BASE_SALARY_DIRETO.md',
    'DEBUG_SUBSIDIO_CHECKBOXES.md',
    'DEPLOY_STRATEGY.md',
    'ENABLE-JIT-QUICK.txt',
    'ENABLE-JIT.md',
    'FIX_CHECKBOX_MARCADO_VALOR_ZERO.md',
    'FIX_CHECKBOX_SYNC_ISSUE.md',
    'FIX_ERROR_500_SELECT_EMPLOYEE.md',
    'FIX_LOADING_OVERLAY_CHECKBOXES.md',
    'FIX_SUBSIDIO_NOVA_LOGICA.md',
    'FIX_SUBSIDIO_ZERO_VALUE.md',
    'FOOD_BENEFIT_BUSINESS_RULE.md',
    'FOOD_BENEFIT_CORRECTION_SUMMARY.md',
    'HELPER_CRITICAL_FIX_ABSENCE_DEDUCTION.md',
    'HELPER_FIX_INSS_BASE_CALCULATION.md',
    'HELPER_FIX_TOTAL_DEDUCTIONS_DISPLAY.md',
    'HELPER_VS_MODAL_MAPPING.md',
    'HR_MODULE_COMPLETE_GUIDE.md',
    'HR_REACT_TYPESCRIPT_GUIDE.md',
    'HR_SETTINGS_IMPLEMENTATION_SUMMARY.md',
    'HR_SETTINGS_PAYROLL_AUDIT.md',
    'IMPORT-NUMBER-FORMATS.md',
    'INSTRUCOES_PAYROLL_BATCH.md',
    'INTEGRATION_PAYROLL_BATCH.md',
    'MANUAL-SISTEMA-MODAIS.md',
    'MODAL_COMPARISON.md',
    'MODAL_COMPARISON_HELPER_USAGE.md',
    'OPCACHE-README.md',
    'PARTNERSHIP_CONTRACT.md',
    'PAYROLL_BATCH_MODAL_COMPLETE.md',
    'PAYROLL_BATCH_TODO.md',
    'PAYROLL_BREAKDOWN_CORRECTED.md',
    'PAYROLL_CALCULATION_LOGIC.md',
    'PAYROLL_FINAL_STATUS.md',
    'PAYROLL_HELPER_COMPLETE.md',
    'PAYROLL_HELPER_SUMMARY.md',
    'PAYROLL_INDIVIDUAL_COMPLETE_REFERENCE.md',
    'PAYROLL_REFACTORING_SESSION_COMPLETE.md',
    'PAYROLL_SCREENSHOT_ANALYSIS.md',
    'PRODUCTION-FIX-EXCEL.md',
    'REFACTORING_COMPLETE_HELPER_ONLY.md',
    'REFACTORING_FINAL_FIXES.md',
    'SECURITY-EMPLOYEE-EXPORT-IMPORT.md',
    'SUBSIDIO_FIX_FINAL_COMPLETE.md',
    'SUBSIDIO_FIX_SUCCESS.md',
    'UPDATE-FLOW-WITH-OPCACHE.txt',
    'laravel-livewire-crud-guide.md',
    'memoria.md',
    'memoria_filters.md',
    'memoria_final.md',
    'memoria_notifications.md',
    'memoria_pdf.md',
    'memoria_tables.md',
    'instrutions Ia.txt',
];

// Arquivos importantes que NÃO devem ser removidos
$protectedFiles = [
    'artisan',
    'composer.json',
    'composer.lock',
    'package.json',
    'package-lock.json',
    'phpunit.xml',
    'README.md',
    '.env',
    '.env.example',
    '.gitignore',
    '.gitattributes',
    '.editorconfig',
    'vite.config.js',
    'preload.php',
    'deploy-production.sh',
    'deploy-with-opcache.sh',
    'enable-jit.ps1',
    'opcache-config-recomendado.ini',
    'opcache.env.example',
];

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Limpeza - ERP DEMBENA</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 28px; margin-bottom: 10px; }
        .header p { opacity: 0.9; }
        .content { padding: 30px; }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .file-list {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            max-height: 400px;
            overflow-y: auto;
        }
        .file-item {
            padding: 8px;
            margin: 5px 0;
            background: white;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .file-item.exists { border-left: 3px solid #28a745; }
        .file-item.not-exists { border-left: 3px solid #6c757d; opacity: 0.6; }
        .file-item.deleted { border-left: 3px solid #dc3545; }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .btn-success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .actions {
            text-align: center;
            margin: 30px 0;
        }
        .actions a, .actions button {
            margin: 0 10px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card h3 { font-size: 32px; margin-bottom: 5px; }
        .stat-card p { opacity: 0.9; }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-secondary { background: #6c757d; color: white; }
        .badge-danger { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧹 Sistema de Limpeza</h1>
            <p>ERP DEMBENA - Remover arquivos temporários e de debug</p>
        </div>
        
        <div class="content">
            <?php
            // Contadores
            $existingFiles = [];
            $missingFiles = [];
            $deletedFiles = [];
            $errors = [];
            
            // Verificar quais arquivos existem
            $allFiles = array_merge($filesToRemove, $mdDocsToRemove);
            
            foreach ($allFiles as $file) {
                $filePath = $basePath . '/' . $file;
                
                // Suporte a wildcards
                if (strpos($file, '*') !== false) {
                    $matches = glob($filePath);
                    foreach ($matches as $match) {
                        $relativePath = str_replace($basePath . '/', '', $match);
                        if (file_exists($match) && !in_array($relativePath, $protectedFiles)) {
                            $existingFiles[] = $relativePath;
                        }
                    }
                } else {
                    if (file_exists($filePath) && !in_array($file, $protectedFiles)) {
                        $existingFiles[] = $file;
                    } else {
                        $missingFiles[] = $file;
                    }
                }
            }
            
            // Processar remoção se confirmado
            if ($confirm && !$dryRun) {
                echo '<div class="info"><strong>🗑️ Removendo arquivos...</strong></div>';
                
                foreach ($existingFiles as $file) {
                    $filePath = $basePath . '/' . $file;
                    try {
                        if (unlink($filePath)) {
                            $deletedFiles[] = $file;
                        } else {
                            $errors[] = "Falha ao remover: $file";
                        }
                    } catch (Exception $e) {
                        $errors[] = "Erro ao remover $file: " . $e->getMessage();
                    }
                }
                
                // Mostrar resultados
                if (!empty($deletedFiles)) {
                    echo '<div class="success">';
                    echo '<strong>✅ Sucesso!</strong><br>';
                    echo count($deletedFiles) . ' arquivos removidos com sucesso!';
                    echo '</div>';
                }
                
                if (!empty($errors)) {
                    echo '<div class="error">';
                    echo '<strong>❌ Erros:</strong><br>';
                    foreach ($errors as $error) {
                        echo '• ' . htmlspecialchars($error) . '<br>';
                    }
                    echo '</div>';
                }
            }
            
            // Mostrar estatísticas
            ?>
            
            <div class="stats">
                <div class="stat-card">
                    <h3><?php echo count($existingFiles); ?></h3>
                    <p>Arquivos Encontrados</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo count($missingFiles); ?></h3>
                    <p>Já Removidos</p>
                </div>
                <?php if (!empty($deletedFiles)): ?>
                <div class="stat-card">
                    <h3><?php echo count($deletedFiles); ?></h3>
                    <p>Removidos Agora</p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!$confirm && !empty($existingFiles)): ?>
                <div class="warning">
                    <strong>⚠️ Atenção!</strong><br>
                    Os seguintes arquivos serão removidos permanentemente.<br>
                    Esta ação não pode ser desfeita.
                </div>
                
                <h3>📁 Arquivos a Remover (<?php echo count($existingFiles); ?>):</h3>
                <div class="file-list">
                    <?php foreach ($existingFiles as $file): ?>
                        <div class="file-item exists">
                            <span>📄 <?php echo htmlspecialchars($file); ?></span>
                            <span class="badge badge-success">Existe</span>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="actions">
                    <a href="?dryrun=1" class="btn btn-primary">👁️ Pré-visualizar</a>
                    <a href="?confirm=yes" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja remover <?php echo count($existingFiles); ?> arquivos?')">
                        🗑️ Confirmar Remoção
                    </a>
                </div>
                
            <?php elseif (!$confirm && empty($existingFiles)): ?>
                <div class="success">
                    <strong>✨ Sistema Limpo!</strong><br>
                    Não há arquivos temporários para remover.
                </div>
            <?php endif; ?>
            
            <?php if ($confirm): ?>
                <div class="actions">
                    <a href="?" class="btn btn-success">🔄 Verificar Novamente</a>
                    <a href="/" class="btn btn-primary">🏠 Voltar ao Sistema</a>
                </div>
            <?php endif; ?>
            
            <hr style="margin: 30px 0; border: none; border-top: 1px solid #dee2e6;">
            
            <p style="text-align: center; color: #6c757d; font-size: 14px;">
                <strong>Sistema de Limpeza ERP DEMBENA</strong><br>
                Desenvolvido para manter o sistema organizado e seguro<br>
                Arquivos protegidos: <?php echo count($protectedFiles); ?> | Total verificados: <?php echo count($allFiles); ?>
            </p>
        </div>
    </div>
</body>
</html>
