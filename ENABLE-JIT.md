# 🚀 Como Habilitar JIT (Just-In-Time Compiler) no Laragon

O JIT é uma feature poderosa do PHP 8+ que compila código PHP diretamente para código de máquina, aumentando significativamente a performance.

## 📍 Passo 1: Localizar o php.ini

### Opção A - Via Laragon (Recomendado):
```
1. Abra o Laragon
2. Clique com botão direito no ícone do Laragon (system tray)
3. Menu > PHP > php.ini
```

### Opção B - Caminho Manual:
```
C:\laragon\bin\php\php-8.2.29\php.ini
```

## ⚙️ Passo 2: Configurar JIT

Adicione ou modifique estas linhas no **php.ini**:

```ini
; ========================================
; JIT Configuration (PHP 8.x)
; ========================================

; Enable OPcache (required for JIT)
opcache.enable=1
opcache.enable_cli=1

; JIT Configuration
opcache.jit_buffer_size=128M
opcache.jit=1255

; JIT Debug (opcional - apenas para desenvolvimento)
; opcache.jit_debug=0

; Outras configurações OPcache recomendadas
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=1
opcache.revalidate_freq=2
opcache.save_comments=1
opcache.enable_file_override=0
```

## 🎯 Explicação dos Valores JIT:

### **opcache.jit_buffer_size**
- `128M` = 128 MB de buffer para JIT
- Valores comuns: `64M`, `128M`, `256M`
- Quanto maior, mais código pode ser compilado

### **opcache.jit**
O valor `1255` é o mais comum e significa:
- **1**: CPU-specific optimization level
- **2**: Optimization level
- **5**: Optimization trigger (on hot code)
- **5**: Additional optimization flags

**Outros valores populares:**
- `1205` = JIT mais conservador (menos agressivo)
- `1255` = JIT balanceado (recomendado) ⭐
- `1275` = JIT mais agressivo (máxima performance)

## 🔄 Passo 3: Reiniciar o Laragon

Após salvar o php.ini:

```
1. Laragon > Stop All
2. Aguarde 3 segundos
3. Laragon > Start All
```

## ✅ Passo 4: Verificar se JIT está Ativo

### Opção A - Via Settings (Web):
```
http://erpdembena.test/maintenance/settings?activeTab=opcache
```
✅ Deve mostrar "JIT Status: ON" em amarelo

### Opção B - Via Dashboard OPcache:
```
http://erpdembena.test/opcache-status.php
```
✅ Procure por "JIT" na seção de configuração

### Opção C - Via Command Line:
```bash
php -i | findstr jit
```
✅ Deve mostrar: `opcache.jit_buffer_size => 128M`

## 🎨 Configurações por Ambiente

### 🔧 Desenvolvimento (Laragon):
```ini
opcache.jit_buffer_size=128M
opcache.jit=1255
opcache.validate_timestamps=1
opcache.revalidate_freq=2
```

### 🚀 Produção:
```ini
opcache.jit_buffer_size=256M
opcache.jit=1255
opcache.validate_timestamps=0
opcache.revalidate_freq=0
```

## 📊 Performance Esperada

Com JIT habilitado, você deve ver:

- ✅ **Hit Rate**: 95-99%
- ✅ **JIT Status**: ON (amarelo)
- ✅ **Response Time**: 20-40% mais rápido
- ✅ **CPU Usage**: Redução de 15-30%

## ⚠️ Troubleshooting

### "JIT ainda aparece como OFF"
1. Certifique-se que salvou o php.ini correto
2. Reinicie o Laragon completamente
3. Limpe o cache do navegador (Ctrl+F5)
4. Execute: `php -i | findstr jit` para confirmar

### "Warning: Failed to load opcache"
- Verifique se a extensão está habilitada:
```ini
extension=opcache
```

### "JIT buffer size é 0"
- Certifique-se que o valor está correto:
```ini
opcache.jit_buffer_size=128M  ; (sem espaços extras)
```

## 🔬 Testar Performance

Antes e depois de habilitar JIT, teste com:

```bash
# Via Artisan (Terminal)
cd C:\laragon\www\ERPDEMBENA
php artisan opcache:optimize --status
```

Você verá:
- Memory usage
- Hit rate
- JIT status
- Scripts cached

## 📚 Referências

- [PHP JIT Documentation](https://www.php.net/manual/en/opcache.configuration.php#ini.opcache.jit)
- [PHP 8 Performance](https://php.watch/articles/jit-in-depth)
- OPCACHE-README.md (neste projeto)

## ✨ Benefícios do JIT

✅ **Performance**: 20-40% mais rápido em operações matemáticas e loops  
✅ **CPU**: Menos uso de CPU para processar requests  
✅ **Escalabilidade**: Pode lidar com mais requisições simultâneas  
✅ **Memória**: Uso mais eficiente de RAM  

---

**🎉 Depois de configurar, recarregue a página de Settings para ver JIT ativo!**
