<div>
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <!-- Cabeçalho da Página -->
        <div class="bg-blue-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Guia de Cálculo de Salários</h1>
                    <p class="mt-2 text-blue-100">Como são calculados os pagamentos e descontos no sistema ERP DEMBENA</p>
                </div>
                <div class="hidden md:block">
                    <i class="fas fa-calculator text-5xl text-blue-200"></i>
                </div>
            </div>
        </div>
        
        <!-- Introdução -->
        <div class="p-6 border-b">
            <div class="prose max-w-none">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    Visão Geral do Sistema de Folha de Pagamento
                </h2>
                <p class="mt-4">
                    A folha de pagamento no sistema ERP DEMBENA é processada através de um fluxo estruturado que garante precisão e conformidade 
                    com a legislação angolana. O sistema calcula automaticamente os componentes do salário, incluindo rendimentos, deduções 
                    obrigatórias (IRT e INSS) e outros descontos.
                </p>
                <div class="mt-6 bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                    <h3 class="font-semibold text-blue-800">Fluxo básico do processamento da folha:</h3>
                    <ol class="list-decimal pl-5 mt-2 space-y-1">
                        <li>Criação de períodos de pagamento (mensal, quinzenal)</li>
                        <li>Geração de folhas para todos funcionários ativos</li>
                        <li>Cálculo automático dos descontos legais (IRT e INSS)</li>
                        <li>Revisão e aprovação das folhas de pagamento</li>
                        <li>Processamento do pagamento e emissão de contracheques</li>
                    </ol>
                </div>
            </div>
        </div>
        
        <!-- Composição do Salário -->
        <div class="p-6 border-b bg-gray-50">
            <div class="prose max-w-none">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                    Composição do Salário
                </h2>
                
                <div class="mt-4 grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-700">Componentes do Salário Bruto</h3>
                        <div class="mt-2 space-y-3">
                            <div class="flex items-start p-3 bg-white rounded-lg shadow-sm">
                                <i class="fas fa-coins text-yellow-500 mt-1 mr-3"></i>
                                <div>
                                    <h4 class="font-semibold">Salário Base</h4>
                                    <p class="text-sm text-gray-600">Valor fixo determinado pelo cargo/função do funcionário.</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-3 bg-white rounded-lg shadow-sm">
                                <i class="fas fa-plus-circle text-blue-500 mt-1 mr-3"></i>
                                <div>
                                    <h4 class="font-semibold">Subsídios</h4>
                                    <p class="text-sm text-gray-600">Valores adicionais como subsídio de transporte, alimentação, etc.</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-3 bg-white rounded-lg shadow-sm">
                                <i class="fas fa-clock text-orange-500 mt-1 mr-3"></i>
                                <div>
                                    <h4 class="font-semibold">Horas Extras</h4>
                                    <p class="text-sm text-gray-600">Pagamento pelas horas trabalhadas além da jornada normal.</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start p-3 bg-white rounded-lg shadow-sm">
                                <i class="fas fa-award text-purple-500 mt-1 mr-3"></i>
                                <div>
                                    <h4 class="font-semibold">Bonificações</h4>
                                    <p class="text-sm text-gray-600">Prêmios e bonificações por desempenho ou metas atingidas.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold text-gray-700">Cálculo do Salário Bruto</h3>
                        <div class="mt-2 p-4 bg-white rounded-lg shadow-sm">
                            <div class="font-mono bg-gray-100 p-3 rounded text-sm">
                                <p><strong>Salário Bruto</strong> = Salário Base + Subsídios + Horas Extras + Bonificações</p>
                            </div>
                            
                            <div class="mt-4">
                                <h4 class="font-semibold text-gray-700">Exemplo:</h4>
                                <table class="min-w-full mt-2 border border-gray-200">
                                    <tr class="bg-gray-50">
                                        <td class="p-2 border">Salário Base</td>
                                        <td class="p-2 border text-right">{{ number_format($exampleData['salarioBase'], 2, ',', '.') }} Kz</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 border">Subsídios</td>
                                        <td class="p-2 border text-right">{{ number_format($exampleData['subsidios'], 2, ',', '.') }} Kz</td>
                                    </tr>
                                    <tr class="bg-gray-50">
                                        <td class="p-2 border">Horas Extras</td>
                                        <td class="p-2 border text-right">{{ number_format($exampleData['horasExtras'], 2, ',', '.') }} Kz</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 border">Bonificações</td>
                                        <td class="p-2 border text-right">{{ number_format($exampleData['bonificacoes'], 2, ',', '.') }} Kz</td>
                                    </tr>
                                    <tr class="bg-green-50 font-semibold">
                                        <td class="p-2 border">Salário Bruto Total</td>
                                        <td class="p-2 border text-right">{{ number_format($exemploCalculado['salarioBruto'], 2, ',', '.') }} Kz</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Descontos Obrigatórios -->
        <div class="p-6 border-b">
            <div class="prose max-w-none">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-percentage text-red-500 mr-2"></i>
                    Descontos Obrigatórios
                </h2>
                
                <div class="mt-6 grid md:grid-cols-2 gap-6">
                    <!-- IRT (Imposto sobre Rendimento do Trabalho) -->
                    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                                <i class="fas fa-file-invoice-dollar text-red-600 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">IRT (Imposto sobre Rendimento do Trabalho)</h3>
                        </div>
                        
                        <div class="mt-4">
                            <p>O IRT é calculado com base na tabela progressiva estabelecida pela legislação angolana. O sistema aplica automaticamente as alíquotas corretas conforme o rendimento tributável do funcionário.</p>
                            
                            <div class="mt-3 p-3 bg-red-50 rounded-lg">
                                <h4 class="font-semibold text-red-800">Características:</h4>
                                <ul class="list-disc pl-5 mt-1 space-y-1 text-sm">
                                    <li>Alíquotas progressivas que variam de 0% a 21%</li>
                                    <li>Os primeiros 34.450 Kz são isentos</li>
                                    <li>Incide sobre: Salário Base + Subsídios + Bonificações</li>
                                    <li>Na maioria dos casos, as horas extras não entram no cálculo do IRT</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- INSS (Segurança Social) -->
                    <div class="bg-white p-5 rounded-lg shadow-sm border border-gray-200">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                <i class="fas fa-shield-alt text-blue-600 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800">INSS (Segurança Social)</h3>
                        </div>
                        
                        <div class="mt-4">
                            <p>A contribuição para o INSS é calculada aplicando-se uma taxa fixa sobre o salário base e subsídios do funcionário.</p>
                            
                            <div class="mt-3 p-3 bg-blue-50 rounded-lg">
                                <h4 class="font-semibold text-blue-800">Características:</h4>
                                <ul class="list-disc pl-5 mt-1 space-y-1 text-sm">
                                    <li>Taxa fixa de 3% sobre o salário base + subsídios (contribuição do funcionário)</li>
                                    <li>A empresa contribui com 8% adicionais (não é descontado do funcionário)</li>
                                    <li>Não incide sobre horas extras e bonificações</li>
                                    <li>Contribuição obrigatória para todos os funcionários</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Exemplo de Cálculo Completo -->
        <div class="p-6 border-b bg-gray-50">
            <div class="prose max-w-none">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-calculator text-indigo-500 mr-2"></i>
                    Exemplo de Cálculo Completo
                </h2>
                
                <div class="mt-4">
                    <p>
                        Vamos ver um exemplo completo de como o sistema calcula o salário líquido a partir do salário bruto, 
                        aplicando todos os descontos obrigatórios:
                    </p>
                    
                    <div class="mt-4 bg-white p-5 rounded-lg shadow-sm">
                        <h3 class="font-semibold text-gray-800 mb-3">Passo a Passo do Cálculo</h3>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full border border-gray-200">
                                <tr class="bg-gray-100">
                                    <th class="p-3 border text-left">Etapa</th>
                                    <th class="p-3 border text-left">Cálculo</th>
                                    <th class="p-3 border text-right">Valor (Kz)</th>
                                </tr>
                                
                                <tr>
                                    <td class="p-3 border font-semibold">1. Salário Base + Subsídios</td>
                                    <td class="p-3 border">
                                        <p>Salário Base + Subsídios</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ number_format($exampleData['salarioBase'], 2, ',', '.') }} + {{ number_format($exampleData['subsidios'], 2, ',', '.') }}
                                        </p>
                                    </td>
                                    <td class="p-3 border text-right">{{ number_format($exemploCalculado['baseINSS'], 2, ',', '.') }}</td>
                                </tr>
                                
                                <tr class="bg-gray-50">
                                    <td class="p-3 border font-semibold">2. Horas Extras + Bonificações</td>
                                    <td class="p-3 border">
                                        <p>Horas Extras + Bonificações</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ number_format($exampleData['horasExtras'], 2, ',', '.') }} + {{ number_format($exampleData['bonificacoes'], 2, ',', '.') }}
                                        </p>
                                    </td>
                                    <td class="p-3 border text-right">{{ number_format($exampleData['horasExtras'] + $exampleData['bonificacoes'], 2, ',', '.') }}</td>
                                </tr>
                                
                                <tr class="bg-blue-50">
                                    <td class="p-3 border font-semibold">3. Cálculo do INSS</td>
                                    <td class="p-3 border">
                                        <p>3% do Salário Base + Subsídios</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ number_format($exemploCalculado['baseINSS'], 2, ',', '.') }} × 0,03
                                        </p>
                                    </td>
                                    <td class="p-3 border text-right">{{ number_format($exemploCalculado['inss'], 2, ',', '.') }}</td>
                                </tr>
                                
                                <tr class="bg-red-50">
                                    <td class="p-3 border font-semibold">4. Cálculo do IRT</td>
                                    <td class="p-3 border">
                                        <p>Base do IRT = Salário Base + Subsídios - INSS</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ number_format($exemploCalculado['baseINSS'], 2, ',', '.') }} - {{ number_format($exemploCalculado['inss'], 2, ',', '.') }} = {{ number_format($exemploCalculado['baseTributavelIRT'], 2, ',', '.') }}
                                        </p>
                                        <p class="mt-1">Valor do IRT conforme tabela:</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            Faixa: 
                                            @if($exemploCalculado['baseTributavelIRT'] <= 100000)
                                                Até 100.000 Kz (Isento)
                                            @elseif($exemploCalculado['baseTributavelIRT'] <= 110000)
                                                100.001 - 110.000 Kz (Valor fixo)
                                            @elseif($exemploCalculado['baseTributavelIRT'] <= 120000)
                                                110.001 - 120.000 Kz (Valor fixo)
                                            @elseif($exemploCalculado['baseTributavelIRT'] <= 150000)
                                                120.001 - 150.000 Kz (Valor fixo)
                                            @elseif($exemploCalculado['baseTributavelIRT'] <= 175000)
                                                150.001 - 175.000 Kz (Valor fixo)
                                            @elseif($exemploCalculado['baseTributavelIRT'] <= 200000)
                                                175.001 - 200.000 Kz (Valor fixo)
                                            @elseif($exemploCalculado['baseTributavelIRT'] <= 250000)
                                                200.001 - 250.000 Kz (Valor fixo)
                                            @elseif($exemploCalculado['baseTributavelIRT'] <= 350000)
                                                250.001 - 350.000 Kz (Valor fixo)
                                            @else
                                                Acima de 350.000 Kz (19% do excedente + valor fixo)
                                            @endif
                                        </p>
                                    </td>
                                    <td class="p-3 border text-right">{{ number_format($exemploCalculado['irt'], 2, ',', '.') }}</td>
                                </tr>
                                
                                <tr>
                                    <td class="p-3 border font-semibold">5. Total Descontos</td>
                                    <td class="p-3 border">
                                        <p>INSS + IRT</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ number_format($exemploCalculado['inss'], 2, ',', '.') }} + {{ number_format($exemploCalculado['irt'], 2, ',', '.') }}
                                        </p>
                                    </td>
                                    <td class="p-3 border text-right">{{ number_format($exemploCalculado['descontosTotais'], 2, ',', '.') }}</td>
                                </tr>
                                
                                <tr class="bg-green-100">
                                    <td class="p-3 border font-semibold">6. Salário Líquido</td>
                                    <td class="p-3 border">
                                        <p>Salário Bruto - Total de Descontos</p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ number_format($exemploCalculado['salarioBruto'], 2, ',', '.') }} - {{ number_format($exemploCalculado['descontosTotais'], 2, ',', '.') }}
                                        </p>
                                    </td>
                                    <td class="p-3 border text-right font-bold">{{ number_format($exemploCalculado['salarioLiquido'], 2, ',', '.') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabela IRT -->
        <div class="p-6 border-b">
            <div class="prose max-w-none">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-table text-yellow-500 mr-2"></i>
                    Tabela Progressiva do IRT
                </h2>
                
                <div class="mt-4">
                    <p>
                        O cálculo do Imposto sobre Rendimento do Trabalho (IRT) segue uma tabela progressiva, onde a alíquota 
                        aumenta conforme o rendimento tributável do funcionário. Abaixo está a tabela utilizada pelo sistema:
                    </p>
                    
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full border border-gray-200">
                            <thead>
                                <tr class="bg-yellow-50">
                                    <th class="p-3 border">Faixa de Rendimento (Kz)</th>
                                    <th class="p-3 border">Percentual</th>
                                    <th class="p-3 border">Valor Fixo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tabelaIRT as $faixa)
                                <tr>
                                    <td class="p-3 border">{{ $faixa['faixa'] }}</td>
                                    <td class="p-3 border">{{ $faixa['percentual'] }}</td>
                                    <td class="p-3 border">{{ $faixa['valor_fixo'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 bg-yellow-50 p-4 rounded-lg border-l-4 border-yellow-400">
                        <h4 class="font-semibold text-yellow-800">Notas sobre o cálculo do IRT:</h4>
                        <ul class="list-disc pl-5 mt-2 space-y-1 text-sm">
                            <li>A legislação de Angola estabelece valores fixos para determinadas faixas de rendimento</li>
                            <li>Para rendimentos acima de 350.000 Kz, aplica-se o valor fixo mais 19% sobre o valor excedente</li>
                            <li>O IRT é calculado após a dedução do INSS (3% sobre o salário base + subsídios)</li>
                            <li>Rendimentos até 100.000 Kz são isentos de IRT</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Fluxo de Processamento -->
        <div class="p-6 border-b bg-gray-50">
            <div class="prose max-w-none">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-project-diagram text-purple-500 mr-2"></i>
                    Fluxo de Processamento no Sistema
                </h2>
                
                <div class="mt-4">
                    <p>
                        O processamento da folha de pagamento no sistema ERP DEMBENA segue um fluxo estruturado que garante 
                        precisão e eficiência. Abaixo estão as etapas principais:
                    </p>
                    
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Etapa 1 -->
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                                    <span class="font-bold text-purple-600">1</span>
                                </div>
                                <h3 class="font-semibold text-gray-800">Criação do Período</h3>
                            </div>
                            <p class="mt-2 text-sm">
                                O administrador cria um novo período de folha de pagamento, definindo as datas de início e fim.
                            </p>
                        </div>
                        
                        <!-- Etapa 2 -->
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                                    <span class="font-bold text-purple-600">2</span>
                                </div>
                                <h3 class="font-semibold text-gray-800">Geração de Registros</h3>
                            </div>
                            <p class="mt-2 text-sm">
                                O sistema gera automaticamente registros de folha para todos os funcionários ativos, com valores padrão.
                            </p>
                        </div>
                        
                        <!-- Etapa 3 -->
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                                    <span class="font-bold text-purple-600">3</span>
                                </div>
                                <h3 class="font-semibold text-gray-800">Ajustes e Edições</h3>
                            </div>
                            <p class="mt-2 text-sm">
                                RH faz ajustes nos valores (horas extras, bonificações, etc.) conforme necessário para cada funcionário.
                            </p>
                        </div>
                        
                        <!-- Etapa 4 -->
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                                    <span class="font-bold text-purple-600">4</span>
                                </div>
                                <h3 class="font-semibold text-gray-800">Cálculo Automático</h3>
                            </div>
                            <p class="mt-2 text-sm">
                                O sistema calcula automaticamente IRT, INSS e salário líquido com base nos valores inseridos.
                            </p>
                        </div>
                        
                        <!-- Etapa 5 -->
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                                    <span class="font-bold text-purple-600">5</span>
                                </div>
                                <h3 class="font-semibold text-gray-800">Aprovação</h3>
                            </div>
                            <p class="mt-2 text-sm">
                                Gestor autorizado revisa e aprova as folhas de pagamento para processamento.
                            </p>
                        </div>
                        
                        <!-- Etapa 6 -->
                        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                                    <span class="font-bold text-purple-600">6</span>
                                </div>
                                <h3 class="font-semibold text-gray-800">Emissão de Recibos</h3>
                            </div>
                            <p class="mt-2 text-sm">
                                O sistema gera contracheques em PDF que podem ser baixados e distribuídos aos funcionários.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Considerações Finais -->
        <div class="p-6">
            <div class="prose max-w-none">
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-bookmark text-green-500 mr-2"></i>
                    Considerações Finais
                </h2>
                
                <div class="mt-4">
                    <p>
                        O módulo de folha de pagamento do ERP DEMBENA foi desenvolvido para simplificar o processo de 
                        cálculo e processamento de salários, garantindo conformidade com a legislação angolana e 
                        proporcionando uma experiência eficiente para gestores e funcionários.
                    </p>
                    
                    <div class="mt-6 bg-blue-50 p-5 rounded-lg shadow-sm">
                        <h3 class="font-semibold text-blue-800 mb-3">Recursos Principais:</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                <p class="text-sm">Cálculo automático de impostos e contribuições conforme a legislação vigente</p>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                <p class="text-sm">Geração de contracheques padronizados em PDF</p>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                <p class="text-sm">Histórico completo de pagamentos por funcionário e período</p>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                <p class="text-sm">Fluxo de aprovação configurável para maior controle</p>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                <p class="text-sm">Relatórios detalhados para análise financeira</p>
                            </div>
                            
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                <p class="text-sm">Integração com o módulo de gestão de recursos humanos</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 text-center">
                        <a href="{{ route('hr.payroll') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            <i class="fas fa-arrow-right mr-2"></i>
                            Ir para o Módulo de Folha de Pagamento
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
