# 📊 Formatos de Números Suportados no Import

## ✅ Função `parseDecimal()` Atualizada

A função agora reconhece **TODOS** os formatos de números automaticamente!

---

## 🌍 Formatos Suportados

### **1. Formato Simples (Sem Separadores)**
```
100000 → 100000.00
50000 → 50000.00
1234567 → 1234567.00
```

### **2. Formato Português/Brasileiro**
```
100.000,00 → 100000.00
1.000.000,00 → 1000000.00
40.600,00 → 40600.00
65.300,00 → 65300.00
120.000,00 → 120000.00
```

### **3. Formato Americano/Inglês**
```
100,000.00 → 100000.00
1,000,000.00 → 1000000.00
40,600.00 → 40600.00
65,300.00 → 65300.00
120,000.00 → 120000.00
```

### **4. Formato Misto (Só Vírgula)**
```
100,00 → 100.00 (decimal)
1000,50 → 1000.50 (decimal)
10,000 → 10000 (milhares)
```

### **5. Formato Misto (Só Ponto)**
```
100.00 → 100.00 (decimal)
1000.50 → 1000.50 (decimal)
10.000 → 10000 (milhares)
```

### **6. Números Inteiros do Excel**
```
40600 → 40600.00
65300 → 65300.00
100000 → 100000.00
```

---

## 🧠 Lógica de Detecção

A função **detecta automaticamente** o formato analisando:

1. **Tem vírgula E ponto?**
   - Vírgula depois do ponto → PT/BR (100.000,00)
   - Ponto depois da vírgula → US (100,000.00)

2. **Só tem vírgula?**
   - 2 dígitos após → Decimal (100,00)
   - 3+ dígitos após → Milhares (1,000)

3. **Só tem ponto?**
   - 2 dígitos após → Decimal (100.00)
   - 3+ dígitos após → Milhares (1.000)

4. **Sem vírgula nem ponto?**
   - Número inteiro (100000)

---

## 💻 Tipos Aceitos

```php
private function parseDecimal(string|int|float|null $value): ?float
```

### **Aceita:**
- ✅ `string` - "100.000,00", "100,000.00", "100000"
- ✅ `int` - 100000, 50000
- ✅ `float` - 100000.50
- ✅ `null` - Retorna null

---

## 📝 Exemplos Reais do Excel

### **Valores do seu arquivo:**

| Excel | Detectado Como | Resultado |
|-------|----------------|-----------|
| 100.000,00 | PT/BR | 100000.00 |
| 40.600,00 | PT/BR | 40600.00 |
| 65.300,00 | PT/BR | 65300.00 |
| 120.000,00 | PT/BR | 120000.00 |
| 29.000,00 | PT/BR | 29000.00 |
| 20.000,00 | PT/BR | 20000.00 |
| 30.000,00 | PT/BR | 30000.00 |
| 0,00 | PT/BR | 0.00 |

---

## 🔧 Campos Afetados

Esta função é usada para converter:

- ✅ `base_salary` - Salário Base
- ✅ `food_benefit` - Subsídio de Alimentação
- ✅ `transport_benefit` - Subsídio de Transporte
- ✅ `bonus_amount` - Valor de Bónus
- ✅ `hourly_rate` - Taxa Horária
- ✅ Qualquer outro campo numérico decimal

---

## ⚠️ Tratamento de Erros

### **Valores Inválidos:**
```php
null → null
"" → null
"abc" → null
"R$ xyz" → null (após limpeza)
```

### **Símbolos Removidos:**
```
R$ 100.000,00 → 100000.00
$ 100,000.00 → 100000.00
€ 100.000,00 → 100000.00
£ 100,000.00 → 100000.00
```

---

## ✅ Resultado

**Agora você pode importar valores em QUALQUER formato!**

🎉 **Não precisa mais converter manualmente!**

---

## 🧪 Testes

### **Para testar, use valores assim no Excel:**

```
100000
100.000,00
100,000.00
40.600,00
40,600.00
40600
```

**Todos serão convertidos corretamente para o banco de dados!** ✅
