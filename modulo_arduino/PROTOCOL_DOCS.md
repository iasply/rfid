# Protocolo Arduino RFID v2

## Configuração
- **Baud Rate:** `9600`
- **Pinos:** SDA: 10, SCK: 13, MOSI: 11, MISO: 12, RST: 9
- **Atraso de Inicialização:** Aguarde **2s** após abrir a porta antes do primeiro comando.

---

## Comandos (Java -> Arduino)
Formato: `<COMANDO:ID>\n` ou `<COMANDO:ID:PAYLOAD>\n`

1. **Leitura**: `<READ:ID>\n` (Timeout: 2.5s)
2. **Gravação**: `<WRITE:ID:PAYLOAD>\n` (Payload fixo de 16 bytes)

---

## Respostas (Arduino -> Java)
Formato: `<RES:ID:STATUS:DADO:FW:VERSAO>`

### Status: OK
*   `<RES:ID:OK:CONTEUDO_LIDO:FW:92>`
*   `<RES:ID:OK:WROTE:FW:92>`

### Status: ERR (Erros Comuns)
*   `NO_TAG`: Nenhuma tag detectada.
*   `AUTH`: Falha de autenticação (Key A).
*   `READ_FAILED`: Erro na extração dos dados.
*   `WRITE_FAILED`: Falha na verificação pós-escrita.
*   `INVALID_CMD`: Comando mal formatado.

---

## Exemplo de Parser (Java)
```java
if (linha.startsWith("<RES:") && linha.endsWith(">")) {
    String[] parts = linha.substring(1, linha.length() - 1).split(":");
    if (parts[1].equals("OK")) {
        // Sucesso: ler parts[2]
    } else {
        // Erro: parts[2] contém o código
    }
}
```
