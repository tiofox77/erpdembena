# 🚀 Como Processar Lotes de Folha de Pagamento

## ✅ **SITUAÇÃO ATUAL:**

Você criou o lote com sucesso! Agora precisa **processar** para calcular os salários.

## 📋 **PRÓXIMOS PASSOS:**

### **1. 🔧 Configure a Queue (se ainda não configurou):**

```bash
# No terminal, dentro da pasta do projeto:
php artisan queue:table
php artisan migrate
```

### **2. 🏃‍♂️ Inicie o Worker da Queue:**

```bash
# Execute este comando em um terminal separado:
php artisan queue:work
```

**💡 IMPORTANTE:** Deixe este comando rodando em um terminal separado enquanto usa o sistema.

### **3. 🎯 Processe o Lote:**

1. **Feche a modal atual**
2. **Clique no botão "Ver" (👁️)** do lote que você criou 
3. **Na modal que abrir, clique em "Processar Lote"** (botão verde)
4. **Aguarde o processamento** - você verá o progresso na tela

## 🔍 **Porque os valores estão zerados:**

- Status **"Rascunho"** = Lote criado mas não processado
- Os cálculos de salário só acontecem quando você clica **"Processar Lote"**
- Valores só aparecem após o processamento completo

## 📊 **Fluxo Completo:**

1. **✅ FEITO:** Criar Lote (Status: Rascunho)
2. **👉 AGORA:** Processar Lote (calcula salários)
3. **Depois:** Aprovar Lote
4. **Final:** Marcar como Pago

## 🐛 **Se não aparecer o botão "Processar Lote":**

1. Atualize a página
2. Clique novamente no botão "Ver" do lote
3. Verifique se o lote tem funcionários selecionados

## 📞 **Suporte:**

Se ainda tiver problemas, me informe qual mensagem aparece nos logs quando você tenta processar!
