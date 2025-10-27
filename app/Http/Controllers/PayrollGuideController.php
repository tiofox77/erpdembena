<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HR\PayrollPeriod;

class PayrollGuideController extends Controller
{
    /**
     * Show the payroll calculation guide page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Exemplo de dados para exemplificar os cálculos
        $exampleData = [
            'salarioBase' => 100000,
            'subsidios' => 20000,
            'horasExtras' => 15000,
            'bonificacoes' => 10000,
        ];

        // Cálculo dos exemplos
        $salarioBruto = $exampleData['salarioBase'] + $exampleData['subsidios'] + 
                        $exampleData['horasExtras'] + $exampleData['bonificacoes'];
        
        // IRT (usando a tabela actual de Angola)
        $irt = $this->calcularIRT($salarioBruto);
        
        // INSS (3% do salário total)
        $inss = $salarioBruto * 0.03;
        
        // Descontos totais
        $descontosTotais = $irt + $inss;
        
        // Salário líquido
        $salarioLiquido = $salarioBruto - $descontosTotais;
        
        // Dados de exemplo calculados
        $exemploCalculado = [
            'salarioBruto' => $salarioBruto,
            'irt' => $irt,
            'inss' => $inss,
            'descontosTotais' => $descontosTotais,
            'salarioLiquido' => $salarioLiquido
        ];

        return view('hr.payroll-guide', [
            'exampleData' => $exampleData,
            'exemploCalculado' => $exemploCalculado,
            'title' => 'Guia de Cálculo de Salários' // Para o título no layout
        ]);
    }

    /**
     * Calcular o IRT com base na tabela progressiva de Angola
     *
     * @param float $rendimentoTributavel
     * @return float
     */
    private function calcularIRT($rendimentoTributavel)
    {
        // Tabela IRT Angola (simplificada para o exemplo)
        if ($rendimentoTributavel <= 34450) {
            return 0; // isento
        } elseif ($rendimentoTributavel <= 35000) {
            return ($rendimentoTributavel - 34450) * 0.07;
        } elseif ($rendimentoTributavel <= 40000) {
            return (($rendimentoTributavel - 35000) * 0.08) + 38.5;
        } elseif ($rendimentoTributavel <= 45000) {
            return (($rendimentoTributavel - 40000) * 0.09) + 438.5;
        } elseif ($rendimentoTributavel <= 50000) {
            return (($rendimentoTributavel - 45000) * 0.10) + 888.5;
        } elseif ($rendimentoTributavel <= 70000) {
            return (($rendimentoTributavel - 50000) * 0.11) + 1388.5;
        } elseif ($rendimentoTributavel <= 90000) {
            return (($rendimentoTributavel - 70000) * 0.13) + 3588.5;
        } elseif ($rendimentoTributavel <= 110000) {
            return (($rendimentoTributavel - 90000) * 0.16) + 6188.5;
        } elseif ($rendimentoTributavel <= 140000) {
            return (($rendimentoTributavel - 110000) * 0.18) + 9388.5;
        } elseif ($rendimentoTributavel <= 170000) {
            return (($rendimentoTributavel - 140000) * 0.19) + 14788.5;
        } elseif ($rendimentoTributavel <= 200000) {
            return (($rendimentoTributavel - 170000) * 0.20) + 20488.5;
        } else {
            return (($rendimentoTributavel - 200000) * 0.21) + 26488.5;
        }
    }
}
