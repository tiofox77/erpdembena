# Relatório de Folha de Pagamento - Batch Summary

## 📊 Visão Geral

O sistema gera um **relatório PDF completo** após o processamento de um batch de folha de pagamento, mostrando o resumo consolidado de todos os earnings e deductions.

---

## 🎯 Quando é Gerado

O relatório pode ser gerado quando:

✅ Batch está com status = **'completed'** (processado com sucesso)

O botão **"Gerar Relatório PDF"** aparece automaticamente no modal de visualização do batch quando ele está completo.

---

## 📄 Conteúdo do Relatório

### **1. Cabeçalho:**
- Nome do batch
- Período de pagamento
- Data do batch
- Departamento (se filtrado)
- Total de funcionários
- Criado por
- Data/hora de geração

### **2. GRAND TOTAL:**
Soma bruta de todos os earnings antes das deduções

### **3. Earnings Breakdown:**

| Item | Código | Descrição |
|------|--------|-----------|
| **BASIC SALARY** | BS | Salário base de todos os funcionários |
| **TRANSPORT** | TRNPT | Subsídio de transporte |
| **OVER TIME** | OT | Horas extras |
| **VACATION PAY** | VP | Subsídio de férias (50% salário) |
| **FOOD ALLOW** | FA | Subsídio de alimentação |
| **CHRISTMAS OFFER** | CO | Subsídio de Natal (50% salário) |
| **BUNUS** | BNS | Bónus adicionais |

**NET** = Soma de todos os earnings (destacado em vermelho)

### **4. Deductions Breakdown:**

| Item | Código | Descrição |
|------|--------|-----------|
| **SOCIAL SECURITY 3%** | ins 3% | INSS - 3% do salário |
| **IRT** | IRT | Imposto sobre Rendimento do Trabalho |
| **STAFF ADVANCE** | Staff Adv | Adiantamentos salariais |
| **ABSENT** | Absent | Deduções por faltas |
| **UNION FUND DEDUCTION** | ded | Desconto sindical (se aplicável) |
| **Union Deduction** | union | Dedução sindical adicional |
| **OTHER DEDUCTION** | DED | Outros descontos |
| **FOOD ALLOW** | FA | Alimentação em espécie (deduzida) |

**TOTAL DEDUCTIONS** = Soma de todas as deduções (destacado em vermelho)

### **5. TOTAL:**
**NET TOTAL** = Grand Total - Total Deductions = Valor líquido a pagar

---

## 🎨 Design do Relatório

### **Cores:**
- 🟠 **GRAND TOTAL** - Laranja (destaque principal)
- 🟡 **BUNUS** - Amarelo (destaque especial)
- 🔴 **NET e TOTAL DEDUCTIONS** - Vermelho (valores-chave)
- 🟤 **Cabeçalhos de seções** - Bege/Gold

### **Estrutura:**
- Layout A4 Portrait
- Fonte: DejaVu Sans (compatível PDF)
- Tabelas com bordas
- Cores consistentes com a imagem fornecida
- Footer com informações confidenciais

---

## 🔧 Implementação Técnica

### **Arquivos Criados:**

#### **1. Service:**
📁 `app/Services/PayrollBatchReportService.php`

```php
class PayrollBatchReportService
{
    public function generateBatchReport(PayrollBatch $batch)
    {
        // Calcula totais agregados
        $totals = $this->calculateBatchTotals($batch);
        
        // Gera PDF
        $pdf = Pdf::loadView('reports.payroll-batch-summary', $data);
        return $pdf->download($filename);
    }
}
```

#### **2. View:**
📁 `resources/views/reports/payroll-batch-summary.blade.php`

Template Blade que renderiza o relatório em HTML/CSS para conversão em PDF.

#### **3. Método Livewire:**
📁 `app/Livewire/HR/PayrollBatch.php`

```php
public function downloadBatchReport($batchId)
{
    $reportService = new PayrollBatchReportService();
    return $reportService->generateBatchReport($batch);
}
```

---

## 🚀 Como Usar

### **Via Interface:**

1. **Acesse** HR > Payroll Batch
2. **Visualize** um batch processado (status = completed)
3. **Clique** no botão **"Gerar Relatório PDF"** (ícone PDF azul)
4. **Download** automático do PDF

### **Programático:**

```php
use App\Services\PayrollBatchReportService;

$batch = PayrollBatch::find($batchId);
$reportService = new PayrollBatchReportService();
$pdf = $reportService->generateBatchReport($batch);
```

---

## 📊 Cálculos Realizados

### **Grand Total:**
```
Grand Total = Basic Salary + Transport + Overtime + Vacation Pay + 
              Food Allow + Christmas Offer + Bonus
```

### **Total Deductions:**
```
Total Deductions = INSS 3% + IRT + Staff Advance + Absent + 
                   Union Fund + Union Deduction + Other Deduction + Food Deduction
```

### **Net Total:**
```
Net Total = Grand Total - Total Deductions
```

---

## 🧪 Exemplo de Output

```
════════════════════════════════════════════════════════════
         RELATÓRIO DE FOLHA DE PAGAMENTO
         Batch Janeiro 2025
         Período: Janeiro 2025 | 31/01/2025
════════════════════════════════════════════════════════════

GRAND TOTAL                                    19,801,294.66

BASIC SALARY                    BS            14,266,549.90
TRANSPORT                       TRNPT          5,542,032.35
OVER TIME                       OT             2,354,082.66
VACATION PAY                    VP               545,242.93
FOOD ALLOW                      FA             6,439,000.00
CHRISTMAS OFFER                 CO                     0.00
BUNUS                           BNS              239,500.00
                                       NET   29,386,407.84

SOCIAL SECURITY 3%              ins 3%           853,467.97
IRT                             IRT              382,256.74
STAFF ADVANCE                   Staff Adv      1,303,866.04
ABSENT                          Absent           208,178.00
OTHER DEDUCTION                 DED                6,000.00
FOOD ALLOW                      FA             6,439,000.00
                          TOTAL DEDUCTIONS    9,667,430.75

TOTAL                                          19,718,977.09
════════════════════════════════════════════════════════════
```

---

## 🔒 Segurança

- ✅ **Acesso controlado** - Apenas usuários autorizados podem gerar
- ✅ **Dados confidenciais** - Footer indica confidencialidade
- ✅ **Auditoria** - Log de geração de relatórios
- ✅ **Timestamp** - Data/hora de geração no documento

---

## 📝 Notas Importantes

1. **Batch precisa estar completed** - Relatório só disponível após processamento
2. **Valores agregados** - Soma de todos os funcionários do batch
3. **Códigos padronizados** - BS, TRNPT, OT, etc. (consistente com folha manual)
4. **Food Allow aparece duas vezes** - Como earning (se benefício) e como deduction (se em espécie)

---

## 🎯 Casos de Uso

### **1. Aprovação Gerencial:**
Relatório usado para aprovar o batch antes do pagamento efetivo.

### **2. Auditoria Contábil:**
Documento de suporte para auditoria e conformidade fiscal.

### **3. Reconciliação Bancária:**
Confirmar valores antes de gerar transferências bancárias.

### **4. Arquivo Histórico:**
Manter registro físico/digital de cada período de pagamento.

---

## ✅ Checklist de Uso

- [ ] Batch processado (status = completed)
- [ ] Verificar valores antes de gerar
- [ ] Gerar relatório PDF
- [ ] Revisar totais (Grand Total vs Net Total)
- [ ] Aprovar batch
- [ ] Arquivar PDF para auditoria

---

## 🔄 Fluxo de Trabalho

```
1. Processar Batch
   └─> Status muda para 'completed'

2. Gerar Relatório
   └─> Botão "Gerar Relatório PDF" aparece

3. Download PDF
   └─> Arquivo: Payroll_Batch_[Nome]_[Data].pdf

4. Revisar Valores
   └─> Verificar Grand Total e Net Total

5. Aprovar/Rejeitar
   └─> Aprovar para pagamento ou corrigir

6. Arquivar
   └─> Salvar PDF para registros
```

---

**Última atualização:** 2025-10-12  
**Versão:** 1.0  
**Status:** ✅ Implementado e Funcional

