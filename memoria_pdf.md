# Sistema de Geração de PDFs

## 1. Estrutura do Método de Geração de PDFs

O sistema usa o pacote Laravel DomPDF para gerar relatórios em PDF tanto para registros individuais quanto para listas de registros com filtros aplicados.

### 1.1 Método Padrão de Geração de PDF

```php
public function generatePdf($id = null)
{
    try {
        // Para PDFs individuais (com ID específico)
        if ($id) {
            $transaction = StockTransaction::with(['part', 'createdBy'])
                ->where('type', 'stock_in') // ou 'stock_out' dependendo do componente
                ->findOrFail($id);
            $pdf = Pdf::loadView('livewire.stocks.stock-in-pdf', [
                'transaction' => $transaction
            ]);
            return $pdf->download('stock_in_' . $id . '.pdf');
        } 
        // Para relatórios de múltiplos registros (filtrados)
        else {
            // Aplicar filtros na consulta
            $query = StockTransaction::with(['part', 'part.equipment', 'createdBy'])
                ->when($this->search, function ($query, $search) {
                    return $query->where('reference_number', 'like', '%'.$search.'%')
                        ->orWhereHas('part', function ($query) use ($search) {
                            $query->where('name', 'like', '%'.$search.'%');
                        });
                })
                ->when($this->status, function ($query, $status) {
                    return $query->where('status', $status);
                })
                ->orderBy($this->sortField, $this->sortDirection);
            
            $transactions = $query->get();
            
            // Dados adicionais para o relatório
            $data = [
                'transactions' => $transactions,
                'filters' => [
                    'dateFrom' => $this->dateFrom,
                    'dateTo' => $this->dateTo,
                    'search' => $this->search,
                    'status' => $this->status
                ],
                'reportTitle' => 'Relatório de Estoque',
                'companyName' => Setting::get('company_name', 'ERP DEMBENA'),
                'companyAddress' => Setting::get('company_address', ''),
                'companyPhone' => Setting::get('company_phone', ''),
                'companyEmail' => Setting::get('company_email', ''),
                'generatedBy' => auth()->user()->name,
                'generatedAt' => now()->format('d/m/Y H:i:s')
            ];
            
            $pdf = Pdf::loadView('livewire.stocks.stock-in-list-pdf', $data);
            return $pdf->download('stock_in_report_' . date('Y-m-d') . '.pdf');
        }
    } catch (\Exception $e) {
        $this->dispatch('notify', type: 'error', message: 'Erro ao gerar PDF: ' . $e->getMessage());
    }
}
```

## 2. Templates PDF

### 2.1 Estrutura do Template PDF

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle ?? 'Relatório' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .logo {
            max-height: 70px;
            max-width: 220px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
            padding: 8px;
        }
        td {
            padding: 8px;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-label {
            font-weight: bold;
        }
        .page-number {
            position: absolute;
            bottom: 10px;
            right: 10px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <!-- Cabeçalho com logo da empresa -->
    <div class="header">
        @php
            $logoPath = \App\Models\Setting::get('company_logo');
            $logoFullPath = $logoPath ? public_path('storage/' . $logoPath) : public_path('img/logo.png');
            $companyName = \App\Models\Setting::get('company_name', 'ERP DEMBENA');
        @endphp
        <img src="{{ $logoFullPath }}" alt="{{ $companyName }} Logo" class="logo">
        <h1>{{ $reportTitle ?? 'Detalhes do Registro' }}</h1>
    </div>

    <!-- Informações do documento -->
    <div class="info-section">
        <p><span class="info-label">Data de emissão:</span> {{ now()->format('d/m/Y H:i') }}</p>
        <p><span class="info-label">Gerado por:</span> {{ auth()->user()->name ?? 'Sistema' }}</p>
        @if(isset($filters))
        <p><span class="info-label">Filtros aplicados:</span> 
            @if($filters['search'])
            Busca: {{ $filters['search'] }}, 
            @endif
            @if($filters['status'])
            Status: {{ $statusOptions[$filters['status']] ?? $filters['status'] }}, 
            @endif
            @if(isset($filters['dateFrom']) && $filters['dateFrom'])
            De: {{ $filters['dateFrom'] }}, 
            @endif
            @if(isset($filters['dateTo']) && $filters['dateTo'])
            Até: {{ $filters['dateTo'] }}
            @endif
        </p>
        @endif
    </div>

    <!-- Conteúdo principal - varia conforme o tipo de relatório -->
    @yield('content')

    <!-- Rodapé com informações de contato -->
    <div class="footer">
        <p>{{ $companyName }} | {{ \App\Models\Setting::get('company_address', '') }}</p>
        <p>Tel: {{ \App\Models\Setting::get('company_phone', '') }} | Email: {{ \App\Models\Setting::get('company_email', '') }}</p>
        <p>Documento gerado em {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="page-number">Página 1</div>
</body>
</html>
```

### 2.2 Exemplo de Conteúdo do PDF - Registro Individual

```blade
@extends('livewire.pdf.layout')

@section('content')
    <!-- Detalhes do registro -->
    <div class="info-section">
        <h2>Detalhes da Transação</h2>
        <table>
            <tr>
                <th width="30%">Campo</th>
                <th width="70%">Valor</th>
            </tr>
            <tr>
                <td class="info-label">Referência</td>
                <td>{{ $transaction->reference_number }}</td>
            </tr>
            <tr>
                <td class="info-label">Nome da Peça</td>
                <td>{{ $transaction->part->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="info-label">Quantidade</td>
                <td>{{ $transaction->quantity }}</td>
            </tr>
            <tr>
                <td class="info-label">Unidade</td>
                <td>{{ $transaction->unit }}</td>
            </tr>
            <tr>
                <td class="info-label">Status</td>
                <td>{{ ucfirst($transaction->status) }}</td>
            </tr>
            <tr>
                <td class="info-label">Observações</td>
                <td>{{ $transaction->notes ?? 'Nenhuma observação' }}</td>
            </tr>
        </table>
    </div>
@endsection
```

### 2.3 Exemplo de Conteúdo do PDF - Lista de Registros

```blade
@extends('livewire.pdf.layout')

@section('content')
    <table>
        <thead>
            <tr>
                <th>Ref.</th>
                <th>Peça</th>
                <th>Quantidade</th>
                <th>Data</th>
                <th>Status</th>
                <th>Criado por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->reference_number }}</td>
                    <td>{{ $transaction->part->name ?? 'N/A' }}</td>
                    <td>{{ $transaction->quantity }} {{ $transaction->unit }}</td>
                    <td>{{ $transaction->created_at->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($transaction->status) }}</td>
                    <td>{{ $transaction->createdBy->name ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Nenhum registro encontrado</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Resumo -->
    <div class="info-section" style="margin-top: 20px;">
        <h3>Resumo</h3>
        <p>Total de registros: <strong>{{ $transactions->count() }}</strong></p>
        <p>Quantidade total: <strong>{{ $transactions->sum('quantity') }}</strong></p>
    </div>
@endsection
```
