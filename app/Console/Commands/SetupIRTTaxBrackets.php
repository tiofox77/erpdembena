<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\HR\IRTTaxBracket;
use Illuminate\Console\Command;

class SetupIRTTaxBrackets extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'hr:setup-irt-brackets';

    /**
     * The console command description.
     */
    protected $description = 'Setup Angola IRT (Income Tax) progressive tax brackets';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Setting up Angola IRT tax brackets...');

        $brackets = [
            [
                'bracket_number' => 1,
                'min_income' => 0.00,
                'max_income' => 100000.00,
                'fixed_amount' => 0.00,
                'tax_rate' => 0.00,
                'description' => 'Escalão 1: até 100.000 AKZ - Isento',
                'is_active' => true
            ],
            [
                'bracket_number' => 2,
                'min_income' => 100001.00,
                'max_income' => 150000.00,
                'fixed_amount' => 0.00,
                'tax_rate' => 13.00,
                'description' => 'Escalão 2: 100.001 - 150.000 AKZ - 0 AKZ + 13% sobre excedente',
                'is_active' => true
            ],
            [
                'bracket_number' => 3,
                'min_income' => 150001.00,
                'max_income' => 200000.00,
                'fixed_amount' => 12500.00,
                'tax_rate' => 16.00,
                'description' => 'Escalão 3: 150.001 - 200.000 AKZ - 12.500 AKZ + 16% sobre excedente',
                'is_active' => true
            ],
            [
                'bracket_number' => 4,
                'min_income' => 200001.00,
                'max_income' => 300000.00,
                'fixed_amount' => 31250.00,
                'tax_rate' => 18.00,
                'description' => 'Escalão 4: 200.001 - 300.000 AKZ - 31.250 AKZ + 18% sobre excedente',
                'is_active' => true
            ],
            [
                'bracket_number' => 5,
                'min_income' => 300001.00,
                'max_income' => 500000.00,
                'fixed_amount' => 49259.00,
                'tax_rate' => 19.00,
                'description' => 'Escalão 5: 300.001 - 500.000 AKZ - 49.259 AKZ + 19% sobre excedente',
                'is_active' => true
            ],
            [
                'bracket_number' => 6,
                'min_income' => 500001.00,
                'max_income' => 1000000.00,
                'fixed_amount' => 87250.00,
                'tax_rate' => 20.00,
                'description' => 'Escalão 6: 500.001 - 1.000.000 AKZ - 87.250 AKZ + 20% sobre excedente',
                'is_active' => true
            ],
            [
                'bracket_number' => 7,
                'min_income' => 1000001.00,
                'max_income' => 1500000.00,
                'fixed_amount' => 187249.00,
                'tax_rate' => 21.00,
                'description' => 'Escalão 7: 1.000.001 - 1.500.000 AKZ - 187.249 AKZ + 21% sobre excedente',
                'is_active' => true
            ],
            [
                'bracket_number' => 8,
                'min_income' => 1500001.00,
                'max_income' => 2000000.00,
                'fixed_amount' => 292249.00,
                'tax_rate' => 22.00,
                'description' => 'Escalão 8: 1.500.001 - 2.000.000 AKZ - 292.249 AKZ + 22% sobre excedente',
                'is_active' => true
            ],
            [
                'bracket_number' => 9,
                'min_income' => 2000001.00,
                'max_income' => 2500000.00,
                'fixed_amount' => 402249.00,
                'tax_rate' => 23.00,
                'description' => 'Escalão 9: 2.000.001 - 2.500.000 AKZ - 402.249 AKZ + 23% sobre excedente',
                'is_active' => true
            ],
            [
                'bracket_number' => 10,
                'min_income' => 2500001.00,
                'max_income' => 5000000.00,
                'fixed_amount' => 517249.00,
                'tax_rate' => 24.00,
                'description' => 'Escalão 10: 2.500.001 - 5.000.000 AKZ - 517.249 AKZ + 24% sobre excedente',
                'is_active' => true
            ],
            [
                'bracket_number' => 11,
                'min_income' => 5000001.00,
                'max_income' => 10000000.00,
                'fixed_amount' => 1117249.00,
                'tax_rate' => 24.50,
                'description' => 'Escalão 11: 5.000.001 - 10.000.000 AKZ - 1.117.249 AKZ + 24,5% sobre excedente',
                'is_active' => true
            ],
            [
                'bracket_number' => 12,
                'min_income' => 10000001.00,
                'max_income' => null, // No upper limit
                'fixed_amount' => 2342248.00,
                'tax_rate' => 25.00,
                'description' => 'Escalão 12: acima de 10.000.000 AKZ - 2.342.248 AKZ + 25% sobre excedente',
                'is_active' => true
            ]
        ];

        $created = 0;
        $updated = 0;

        foreach ($brackets as $bracketData) {
            $existing = IRTTaxBracket::where('bracket_number', $bracketData['bracket_number'])->first();

            if ($existing) {
                $existing->update($bracketData);
                $updated++;
                $this->line("Updated: Escalão {$bracketData['bracket_number']}");
            } else {
                IRTTaxBracket::create($bracketData);
                $created++;
                $this->info("Created: Escalão {$bracketData['bracket_number']}");
            }
        }

        $this->info("IRT tax brackets setup complete!");
        $this->info("Created: $created | Updated: $updated | Total: " . count($brackets));
        
        // Display current brackets
        $this->newLine();
        $this->info('Current IRT Tax Brackets:');
        $this->table(
            ['Escalão', 'Min (AKZ)', 'Max (AKZ)', 'Parcela Fixa (AKZ)', 'Taxa (%)', 'Description'],
            IRTTaxBracket::where('is_active', true)
                ->orderBy('bracket_number')
                ->get(['bracket_number', 'min_income', 'max_income', 'fixed_amount', 'tax_rate', 'description'])
                ->map(function($bracket) {
                    return [
                        $bracket->bracket_number,
                        number_format($bracket->min_income, 2),
                        $bracket->max_income ? number_format($bracket->max_income, 2) : 'Sem limite',
                        number_format($bracket->fixed_amount, 2),
                        $bracket->tax_rate . '%',
                        $bracket->description
                    ];
                })
                ->toArray()
        );
        
        // Test calculations for sample incomes
        $this->newLine();
        $this->info('Sample IRT Calculations:');
        $testIncomes = [50000, 120000, 180000, 350000, 1200000, 15000000];
        
        foreach ($testIncomes as $income) {
            $tax = IRTTaxBracket::calculateIRT($income);
            $bracket = IRTTaxBracket::getBracketForIncome($income);
            $this->line(sprintf(
                'Income: %s AKZ -> IRT: %s AKZ (Escalão %d)', 
                number_format($income, 2),
                number_format($tax, 2),
                $bracket ? $bracket->bracket_number : 0
            ));
        }
    }
}
