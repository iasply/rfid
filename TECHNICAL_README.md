# 🛠️ Documentação Técnica - Sistema Cattle RFID

Bem-vindo ao **README Técnico**! Este documento foi elaborado para desenvolvedores, arquitetos de software e engenheiros que buscam compreender as minúcias, as tecnologias empregadas e o fluxo de dados em todo o ecossistema do **Cattle RFID System**.

---

## 🧱 Arquitetura e Stack Tecnológico

O sistema é dividido em três microssistemas heterogêneos que se comunicam através de protocolos padronizados.

### 1. `modulo_web` (Back-end & Dashboard)
Responsável por persistir dados, fornecer a API e renderizar a interface administrativa.

- **Linguagem:** PHP 8.2+
- **Framework:** Laravel 12.0
- **Banco de Dados:** Suporta nativamente SQLite/MySQL/Postgres(Desenvolvido usando SQLite) (configurado via `.env`). O sistema utiliza o Eloquent ORM e migrations nativas do Laravel para garantir integridade referencial ("NOT NULL constraints", limites de 16 caracteres únicos para RFID).
- **Autenticação:** Laravel Sanctum (para API via tokens) e controle de sessão web.
- **Testes:** PHPUnit. O sistema conta com vasta cobertura de testes (`Feature/AdminWebTest.php`, etc).
- **Frontend nativo:** Vanilla CSS (Custom Variables) + Vite e componentes Blade.

### 2. `modulo_desktop` (Terminal de Operação)
Responsável pela interface do operador in-loco (fazenda/curral) e ponta de integração Serial-HTTP.

- **Linguagem:** Java 21
- **Gerenciador de Dependências:** Maven
- **Interface Gráfica (GUI):** UI construída com pacote `Swing` usando o moderno **FlatLaf** (Flat Light Look and Feel).
- **Comunicação Serial:** Biblioteca `jSerialComm` v2.10.4.
- **Processamento JSON:** Google Gson v2.10.1.
- **Testes (TDD):** JUnit 5 e Mockito. Configurado com _Jacoco_ para relatórios de cobertura de código.

### 3. `modulo_arduino` (Hardware & Firmware)
Responsável pelo sensor físico. O código é focado em alta velocidade de detecção, estabilidade e prevenção de ruídos "bouncing".

- **Plataforma:** Arduino Uno / Nano / Mega (C++)
- **Sensor:** MFRC522v2 (Kit RFID 13.56 MHz)
- **Bibliotecas Base:** `SPI.h`, `MFRC522v2.h`
- **Protocolo Customizado Baseado em Texto:**
  - O Software Java envia: `<READ>` ou `<WRITE:XXXXXXXX_PAYLOAD>` (até 16 bytes).
  - O Arduino responde: `<RES:OK:MENSAGEM:FW:VERSAO>` ou `<RES:ERR:ERRO:FW:VERSAO>`.
  - A Chave MIFARE utilizada é o Autenticador Padrão (Factory Default) bloqueado na key `A` (0xFF FF FF FF FF FF) usando o Bloco `4`.

---

## 🔄 Fluxos de Comunicação (End-to-End)

1. **Leitura Física:** Um animal, portando um brinco RFID (Tag MIFARE Classic), se aproxima da antena do leitor.
2. **Gatilho de Interface:** O operador clica em "Ler RFID" na interface Java (`modulo_desktop`).
3. **Ponte Serial:** O Java envia uma string Serial terminada em `\n` contendo `<READ>`.
4. **Firmware Arduino:** O Arduino executa `mfrc522.PCD_Authenticate()`, lê os 16 bytes do bloco 4 da tag, substitui caracteres não imprimíveis por espaços, e envia pela interface de volta: `<RES:OK:C1234567890     :FW:92>`.
5. **Autenticação de Dados:** O Java extrai a string, verifica pelo JSON se possui até 16 caracteres, instanciando os DTOs.
6. **Sincronização Nuvem (API):** O Client HTTP em Java faz um `POST /api/rfid/validate` enviando a hash do brinco juntamente com o Token Bearer.
7. **Resposta Web:** O back-end Laravel busca o ID no banco, processa lógicas de negócio e retorna o status em `VaccineResponse.php` etc.

---

## ⚙️ Configuração do Ambiente de Desenvolvimento

### Módulo Web
```bash
cd modulo_web
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve

# Para rodar testes E2E (Playwright)
cd e2e
npm install
npx playwright install --with-deps # Se necessário
npm test
```

### Módulo Desktop
Requisitos: JDK 21+ instalado.
```bash
cd modulo_desktop

# Compilar o código fonte
mvn clean compile

# Iniciar a aplicação no Windows / macOS (executável java padrão configurado no path)
mvn exec:java

# Iniciar a aplicação no Linux / Ubuntu (Usa um profile customizado injetando o caminho absoluto e prevenindo ambiente headless)
mvn exec:exec
```

### Módulo Arduino
1. Conecte seu Arduino via porta USB.
2. Abra `modulo_arduino/src/rfid/rfid.ino` em sua Arduino IDE.
3. Instale a biblioteca genérica `MFRC522v2` pelo Library Manager.
4. Carregue o código. Certifique-se de configurar o baudate local em `9600`.

---

## 🔒 Regras de Integridade Críticas
- **RFID Limite:** Toda validação (Banco de dados de Gado, Laravel e Java Desktop) rejeitará RFIDs com mais de 16 caracteres. O próprio layout do bloco MIFARE comporta apenas 16 bytes úteis contínuos, garantindo compatibilidade entre as barreiras de hardware e software.
- **Tratamento de Strings (NULLs):** O padrão adotado nos schemas é evitar `NULLs` quando um valor tiver significância arquitetural (ex: foreign keys em restrição de segurança), suportado por configurações estritas de banco de dados.

## 🧪 Estratégia de Testes

- **Testes Backend (`modulo_web`):**
  - **Unitários/Feature:** Execute os comandos `php artisan test`. O sistema utiliza PHPUnit/Pest para testar a cadeia de autorização e integridade.
  - **E2E (End-to-End):** Utiliza **Playwright** com TypeScript. Localizados em `modulo_web/e2e`. Estes testes validam fluxos críticos do usuário (como o Login) simulando interações reais em navegadores (Chromium, Firefox, Webkit). Utilize o comando `npm test` dentro do diretório `e2e`.
- **Testes Desktop (`modulo_desktop`):** Executados através do Maven phase test (`mvn test`). Serviços dependentes são mockados com `Mockito` para rodar sem necessidade da placa serial espetada na porta USB.

---
## 🔗 Fontes e Referências
- Conceitos de hardware e bibliotecas RFID: [RoboCore - Leitura e Escrita com RFID Mifare MFRC522](https://www.robocore.net/tutoriais/leitura-escrita-com-rfid-mifare-mfrc522)
- Frameworks: [Laravel Documentation](https://laravel.com/docs), [OpenJDK](https://openjdk.org/)

