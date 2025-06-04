<?php

namespace App\Livewire\HR;

use Livewire\Component;

class PayrollGuide extends Component
{
    public $exampleData;
    public $exemploCalculado;
    public $tabelaIRT;

    public function mount()
    {
        // Atualização da tabela de IRT de Angola para exibição
        $this->tabelaIRT = [
            ['faixa' => 'Até 100.000 Kz', 'percentual' => '0%', 'valor_fixo' => '0'],
            ['faixa' => '100.001 - 110.000 Kz', 'percentual' => 'Valor Fixo', 'valor_fixo' => '870,87'],
            ['faixa' => '110.001 - 120.000 Kz', 'percentual' => 'Valor Fixo', 'valor_fixo' => '2.131,87'],
            ['faixa' => '120.001 - 150.000 Kz', 'percentual' => 'Valor Fixo', 'valor_fixo' => '5.914,87'],
            ['faixa' => '150.001 - 175.000 Kz', 'percentual' => 'Valor Fixo', 'valor_fixo' => '15.659,84'],
            ['faixa' => '175.001 - 200.000 Kz', 'percentual' => 'Valor Fixo', 'valor_fixo' => '19.539,84'],
            ['faixa' => '200.001 - 250.000 Kz', 'percentual' => 'Valor Fixo', 'valor_fixo' => '38.899,82'],
            ['faixa' => '250.001 - 350.000 Kz', 'percentual' => 'Valor Fixo', 'valor_fixo' => '56.754,81'],
            ['faixa' => 'Acima de 350.000 Kz', 'percentual' => '19%', 'valor_fixo' => '56.754,81 + 19% do excedente'],
        ];

        // Exemplo de dados para exemplificar os cálculos (caso mais realista)
        $this->exampleData = [
            'salarioBase' => 175000,
            'subsidios' => 50000,
            'horasExtras' => 25000,
            'bonificacoes' => 15000,
        ];

        // Cálculo dos exemplos
        $salarioBruto = $this->exampleData['salarioBase'] + $this->exampleData['subsidios'] + 
                        $this->exampleData['horasExtras'] + $this->exampleData['bonificacoes'];
        
        // Base de cálculo para INSS (salário base + subsídios)
        $baseINSS = $this->exampleData['salarioBase'] + $this->exampleData['subsidios'];
        
        // INSS (3% da base de cálculo - salário base + subsídios)
        $inss = $baseINSS * 0.03;
        
        // Base tributável para IRT (após dedução do INSS)
        $baseTributavelIRT = $baseINSS - $inss;
        
        // IRT (usando a tabela atual de Angola)
        $irt = $this->calcularIRT($baseTributavelIRT);
        
        // Descontos totais (IRT + INSS + outros descontos se houver)
        $descontosTotais = $irt + $inss;
        
        // Salário líquido (inclui horas extras e bonificações que não entram na base do INSS/IRT)
        $salarioLiquido = $salarioBruto - $descontosTotais;
        
        // Dados de exemplo calculados
        $this->exemploCalculado = [
            'salarioBruto' => $salarioBruto,
            'baseINSS' => $baseINSS,
            'inss' => $inss,
            'baseTributavelIRT' => $baseTributavelIRT,
            'irt' => $irt,
            'descontosTotais' => $descontosTotais,
            'salarioLiquido' => $salarioLiquido
        ];
    }

    public function render()
    {
        return view('livewire.hr.payroll-guide')
            ->layout('layouts.livewire', ['title' => 'Guia de Cálculo de Salários']);
    }

    /**
     * Calcular o IRT com base na tabela progressiva de Angola
     *
     * @param float $rendimentoTributavel
     * @return float
     */
    private function calcularIRT($rendimentoTributavel)
    {
        // Tabela IRT Angola atualizada
        if ($rendimentoTributavel <= 100000) {
            return 0; // Isento
        } elseif ($rendimentoTributavel <= 110000) {
            return 870.87;
        } elseif ($rendimentoTributavel <= 120000) {
            return 2131.87;
        } elseif ($rendimentoTributavel <= 150000) {
            return 5914.87;
        } elseif ($rendimentoTributavel <= 175000) {
            return 15659.84;
        } elseif ($rendimentoTributavel <= 200000) {
            return 19539.84;
        } elseif ($rendimentoTributavel <= 250000) {
            return 38899.82;
        } elseif ($rendimentoTributavel <= 350000) {
            return 56754.81;
        } else {
            return ($rendimentoTributavel - 350000) * 0.19 + 56754.81;
        }
    }
}
