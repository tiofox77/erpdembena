<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\HR\HRSetting;
use Illuminate\Console\Command;

class SetupTaxSettings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hr:setup-tax-settings';

    /**
     * The console command description.
     */
    protected $description = 'Setup default HR tax settings for payroll calculations';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Setting up default HR tax settings...');

        $defaultSettings = [
            [
                'key' => 'irt_rate',
                'value' => '6.5',
                'group' => 'tax',
                'description' => 'Taxa de IRT (Imposto sobre Rendimento do Trabalho) em percentagem',
                'is_system' => true
            ],
            [
                'key' => 'inss_rate', 
                'value' => '3.0',
                'group' => 'tax',
                'description' => 'Taxa de INSS (Instituto Nacional de Segurança Social) em percentagem',
                'is_system' => true
            ],
            [
                'key' => 'irt_min_salary',
                'value' => '70000',
                'group' => 'tax',
                'description' => 'Salário mínimo isento de IRT em AOA',
                'is_system' => true
            ],
            [
                'key' => 'inss_max_salary',
                'value' => '0',
                'group' => 'tax', 
                'description' => 'Salário máximo para cálculo de INSS (0 = sem limite)',
                'is_system' => true
            ],
            [
                'key' => 'tax_calculation_base',
                'value' => 'gross',
                'group' => 'tax',
                'description' => 'Base de cálculo de impostos: gross (salário bruto) ou base (salário base)',
                'is_system' => true
            ]
        ];

        $created = 0;
        $updated = 0;

        foreach ($defaultSettings as $setting) {
            $existing = HRSetting::where('key', $setting['key'])
                ->where('group', $setting['group'])
                ->first();

            if ($existing) {
                // Update existing setting if different
                if ($existing->value !== $setting['value'] || $existing->description !== $setting['description']) {
                    $existing->update([
                        'value' => $setting['value'],
                        'description' => $setting['description']
                    ]);
                    $updated++;
                    $this->line("Updated: {$setting['key']} = {$setting['value']}");
                } else {
                    $this->line("Exists: {$setting['key']} = {$setting['value']}");
                }
            } else {
                // Create new setting
                HRSetting::create($setting);
                $created++;
                $this->info("Created: {$setting['key']} = {$setting['value']}");
            }
        }

        $this->info("Tax settings setup complete!");
        $this->info("Created: $created | Updated: $updated | Total: " . count($defaultSettings));
        
        // Display current settings
        $this->newLine();
        $this->info('Current HR Tax Settings:');
        $this->table(
            ['Key', 'Value', 'Description'],
            HRSetting::where('group', 'tax')
                ->get(['key', 'value', 'description'])
                ->toArray()
        );
    }
}
